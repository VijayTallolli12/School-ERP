<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Teachers\Models\TeacherAttendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceRealtimeController extends ApiBaseController
{
    public function status(Request $request): JsonResponse
    {
        $schoolId = app(SchoolContext::class)->id();
        $date = $request->input('date', now()->toDateString());
        $carbonDate = Carbon::parse($date);

        $academicYear = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        // Student attendance summary for the date
        $studentQuery = Attendance::query()
            ->where('school_id', $schoolId)
            ->whereDate('attendance_date', $carbonDate);

        if ($academicYear) {
            $studentQuery->where('academic_year_id', $academicYear->id);
        }

        $studentRecords = $studentQuery->get();
        $studentTotal = $studentRecords->count();
        $studentSummary = [
            'present' => $studentRecords->where('status', 'present')->count(),
            'absent' => $studentRecords->where('status', 'absent')->count(),
            'late' => $studentRecords->where('status', 'late')->count(),
            'half_day' => $studentRecords->where('status', 'half_day')->count(),
            'excused' => $studentRecords->where('status', 'excused')->count(),
        ];

        // Teacher attendance summary for the date
        $teacherRecords = TeacherAttendance::query()
            ->whereDate('attendance_date', $carbonDate)
            ->get();
        $teacherTotal = $teacherRecords->count();
        $teacherSummary = [
            'present' => $teacherRecords->where('status', 'present')->count(),
            'absent' => $teacherRecords->where('status', 'absent')->count(),
            'late' => $teacherRecords->where('status', 'late')->count(),
            'half_day' => $teacherRecords->where('status', 'half_day')->count(),
            'excused' => $teacherRecords->where('status', 'excused')->count(),
        ];

        // Recent activity (last 20 student attendance marks)
        $recentActivity = Attendance::query()
            ->where('school_id', $schoolId)
            ->whereDate('attendance_date', $carbonDate)
            ->with(['student:id,first_name,last_name', 'markedBy:id,name'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'student_name' => $a->student?->full_name ?? 'Unknown',
                'status' => $a->status,
                'status_label' => $a->status_label,
                'marked_by' => $a->markedBy?->name ?? 'Unknown',
                'marked_at' => $a->created_at?->format('h:i A'),
                'date' => $a->attendance_date?->toDateString(),
            ]);

        return $this->success([
            'date' => $date,
            'student_attendance' => [
                'total' => $studentTotal,
                'summary' => $studentSummary,
            ],
            'teacher_attendance' => [
                'total' => $teacherTotal,
                'summary' => $teacherSummary,
            ],
            'recent_activity' => $recentActivity,
        ], 'Real-time attendance status retrieved.');
    }
}
