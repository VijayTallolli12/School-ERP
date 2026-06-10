<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Resources\Api\V1\ExamResultResource;
use App\Http\Resources\Api\V1\ParentListResource;
use App\Http\Resources\Api\V1\ParentResource;
use App\Http\Resources\Api\V1\StudentFeeResource;
use App\Http\Resources\Api\V1\StudentListResource;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Parents\Repositories\ParentRepositoryInterface;
use App\Modules\Parents\Services\ParentService;
use App\Modules\Students\Models\Student;
use App\Modules\Timetable\Models\TimetableSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentApiController extends ApiBaseController
{
    public function __construct(
        private readonly ParentRepositoryInterface $parentRepo,
        private readonly ParentService $parentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'sometimes|nullable|string|max:100',
            'status' => 'sometimes|nullable|in:active,inactive',
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $query = Guardian::query()->withCount('students');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $paginator = $query->orderBy('first_name')->paginate($request->integer('per_page', 15));

        return $this->paginated(
            paginator: $paginator->through(fn (Guardian $g) => new ParentListResource($g)),
            message: 'Parents retrieved.'
        );
    }

    public function show(string $uuid): JsonResponse
    {
        $parent = Guardian::query()
            ->where('uuid', $uuid)
            ->with(['students', 'user'])
            ->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        return $this->success(new ParentResource($parent), 'Parent retrieved.');
    }

    public function children(string $uuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $students = $parent->students()
            ->with(['currentSession.classSection.schoolClass', 'currentSession.classSection.section'])
            ->get();

        return $this->success(
            StudentListResource::collection($students),
            'Linked children retrieved.'
        );
    }

    public function childAttendance(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $month = request()->integer('month', (int) now()->month);
        $year = request()->integer('year', (int) now()->year);

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
        ], 'Child attendance retrieved.');
    }

    public function childFees(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $fees = StudentFee::query()
            ->where('student_id', $student->id)
            ->with(['academicYear', 'items.feeCategory', 'items.paymentItems'])
            ->orderByDesc('id')
            ->get();

        return $this->success(
            StudentFeeResource::collection($fees),
            'Child fees retrieved.'
        );
    }

    public function childExamResults(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
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
        ], 'Child exam results retrieved.');
    }

    public function childTimetable(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $currentSession = $student->currentSession()->first();

        if (! $currentSession) {
            return $this->success(['timetable' => []], 'No active session.');
        }

        $slots = TimetableSlot::query()
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

        return $this->success(['timetable' => $slots], 'Child timetable retrieved.');
    }

    public function dashboard(string $uuid, Request $request): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->with('students')->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $childUuid = $request->query('child_uuid');

        $data = $this->parentService->getParentDashboardData($parent, $childUuid);

        return $this->success($data, 'Parent dashboard retrieved.');
    }
}