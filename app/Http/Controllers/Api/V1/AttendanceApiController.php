<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\AttendanceResource;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Modules\Attendance\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttendanceApiController extends ApiBaseController
{
    public function __construct(
        private readonly AttendanceRepositoryInterface $attendanceRepo,
        private readonly AttendanceService $attendanceService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'sometimes|nullable|date_format:Y-m-d',
            'class_section_id' => 'sometimes|nullable|integer|exists:class_section,id',
            'student_id' => 'sometimes|nullable|integer|exists:students,id',
            'status' => 'sometimes|nullable|in:' . implode(',', array_keys(Attendance::getStatuses())),
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $query = Attendance::query()
            ->with(['student:id,first_name,last_name,admission_no,uuid', 'classSection.schoolClass', 'classSection.section', 'markedBy:id,name']);

        if ($date = $request->input('date')) {
            $query->whereDate('attendance_date', $date);
        }

        if ($classSectionId = $request->integer('class_section_id')) {
            $query->where('class_section_id', $classSectionId);
        }

        if ($studentId = $request->integer('student_id')) {
            $query->where('student_id', $studentId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $paginator = $query->orderByDesc('attendance_date')->paginate($request->integer('per_page', 15));

        return $this->paginated(
            paginator: $paginator->through(fn (Attendance $a) => new AttendanceResource($a)),
            message: 'Attendance records retrieved.'
        );
    }

    public function daily(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'class_section_id' => 'required|integer|exists:class_section,id',
        ]);

        $date = $request->input('date');
        $classSectionId = $request->integer('class_section_id');

        $records = Attendance::query()
            ->where('class_section_id', $classSectionId)
            ->whereDate('attendance_date', $date)
            ->with(['student:id,first_name,last_name,admission_no,uuid,roll_no', 'markedBy:id,name'])
            ->orderBy('created_at')
            ->get();

        return $this->success(
            AttendanceResource::collection($records),
            "Daily attendance for {$date} retrieved."
        );
    }

    public function monthly(Request $request): JsonResponse
    {
        $request->validate([
            'class_section_id' => 'required|integer|exists:class_section,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $classSectionId = $request->integer('class_section_id');
        $month = $request->integer('month');
        $year = $request->integer('year');

        $report = $this->attendanceRepo->getMonthlyReport($classSectionId, $month, $year);
        $breakdown = $this->attendanceRepo->getMonthlyStudentBreakdown($classSectionId, $month, $year);

        return $this->success([
            'report' => $report,
            'student_breakdown' => $breakdown,
        ], 'Monthly attendance report retrieved.');
    }

    public function statistics(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'sometimes|nullable|date_format:Y-m-d',
            'to' => 'sometimes|nullable|date_format:Y-m-d|after_or_equal:from',
            'class_section_id' => 'sometimes|nullable|integer|exists:class_section,id',
        ]);

        $stats = $this->attendanceRepo->getStatistics($request->only(['from', 'to', 'class_section_id']));

        return $this->success($stats, 'Attendance statistics retrieved.');
    }
}