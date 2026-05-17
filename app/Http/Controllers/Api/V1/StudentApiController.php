<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Resources\Api\V1\ExamResultResource;
use App\Http\Resources\Api\V1\StudentFeeResource;
use App\Http\Resources\Api\V1\StudentListResource;
use App\Http\Resources\Api\V1\StudentResource;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Fees\Repositories\FeeRepositoryInterface;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Repositories\StudentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentApiController extends ApiBaseController
{
    public function __construct(
        private readonly StudentRepositoryInterface $studentRepo,
        private readonly AttendanceRepositoryInterface $attendanceRepo,
        private readonly FeeRepositoryInterface $feeRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'sometimes|nullable|string|max:100',
            'status' => 'sometimes|nullable|in:active,inactive,graduated,transferred',
            'class_section_id' => 'sometimes|nullable|integer|exists:class_section,id',
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $query = Student::query()
            ->with(['currentSession.academicYear', 'currentSession.classSection.schoolClass', 'currentSession.classSection.section']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($classSectionId = $request->integer('class_section_id')) {
            $query->whereHas('currentSession', fn ($q) => $q->where('class_section_id', $classSectionId));
        }

        $paginator = $query->orderBy('first_name')->paginate($request->integer('per_page', 15));

        return $this->paginated(
            paginator: $paginator->through(fn (Student $s) => new StudentListResource($s)),
            message: 'Students retrieved.'
        );
    }

    public function show(string $uuid): JsonResponse
    {
        $student = Student::query()
            ->where('uuid', $uuid)
            ->with([
                'user',
                'currentSession.academicYear',
                'currentSession.classSection.schoolClass',
                'currentSession.classSection.section',
                'guardians',
                'documents',
            ])
            ->first();

        if (! $student) {
            return $this->notFound('Student not found.');
        }

        return $this->success(new StudentResource($student), 'Student retrieved.');
    }

    public function attendanceSummary(string $uuid, Request $request): JsonResponse
    {
        $student = Student::query()->where('uuid', $uuid)->first();

        if (! $student) {
            return $this->notFound('Student not found.');
        }

        $request->validate([
            'month' => 'sometimes|integer|min:1|max:12',
            'year' => 'sometimes|integer|min:2000|max:2100',
        ]);

        $month = $request->integer('month', (int) now()->month);
        $year = $request->integer('year', (int) now()->year);

        $records = Attendance::query()
            ->where('student_id', $student->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->with(['classSection.schoolClass', 'classSection.section', 'markedBy:id,name'])
            ->orderBy('attendance_date')
            ->get();

        $statuses = Attendance::getStatuses();
        $counts = [];
        foreach ($statuses as $key => $label) {
            $counts[$key] = $records->where('status', $key)->count();
        }

        return $this->success([
            'student' => new StudentListResource($student),
            'month' => $month,
            'year' => $year,
            'summary' => [
                'total_days' => $records->count(),
                'counts' => $counts,
            ],
            'records' => AttendanceResource::collection($records),
        ], 'Attendance summary retrieved.');
    }

    public function feesSummary(string $uuid): JsonResponse
    {
        $student = Student::query()->where('uuid', $uuid)->first();

        if (! $student) {
            return $this->notFound('Student not found.');
        }

        $studentFees = StudentFee::query()
            ->where('student_id', $student->id)
            ->with(['academicYear', 'items.feeCategory', 'items.paymentItems'])
            ->orderByDesc('id')
            ->get();

        return $this->success(
            StudentFeeResource::collection($studentFees),
            'Fees summary retrieved.'
        );
    }

    public function examSummary(string $uuid): JsonResponse
    {
        $student = Student::query()->where('uuid', $uuid)->first();

        if (! $student) {
            return $this->notFound('Student not found.');
        }

        $results = ExamResult::query()
            ->where('student_id', $student->id)
            ->with(['exam.subject', 'exam.classSection.schoolClass', 'exam.classSection.section'])
            ->orderByDesc('id')
            ->get()
            ->groupBy('exam.academic_year_id');

        return $this->success([
            'student' => new StudentListResource($student),
            'results_by_academic_year' => $results->map(fn ($group) => ExamResultResource::collection($group)),
        ], 'Exam summary retrieved.');
    }

    public function timetable(string $uuid): JsonResponse
    {
        $student = Student::query()->where('uuid', $uuid)->with('currentSession')->first();

        if (! $student) {
            return $this->notFound('Student not found.');
        }

        $currentSession = $student->currentSession->first();

        if (! $currentSession) {
            return $this->success(['timetable' => []], 'No active session found.');
        }

        $slots = \App\Modules\Timetable\Models\TimetableSlot::query()
            ->where('class_section_id', $currentSession->class_section_id)
            ->where('academic_year_id', $currentSession->academic_year_id)
            ->with(['subject:id,name,code', 'teacher.user:id,name'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(fn ($daySlots) => $daySlots->map(fn ($slot) => [
                'id' => $slot->id,
                'day_of_week' => $slot->day_of_week,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'subject' => $slot->subject ? ['id' => $slot->subject->id, 'name' => $slot->subject->name] : null,
                'teacher' => $slot->teacher?->user ? ['id' => $slot->teacher->user->id, 'name' => $slot->teacher->user->name] : null,
                'room' => $slot->room,
            ]));

        return $this->success(['timetable' => $slots], 'Timetable retrieved.');
    }
}