<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceQueryHandler
{
    public function __construct(
        private readonly SchoolContext $schoolContext
    ) {}

    public function absentToday(): string
    {
        $today = Carbon::today()->toDateString();
        $count = Attendance::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('attendance_date', $today)
            ->where('status', 'absent')
            ->count();

        $totalMarked = Attendance::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('attendance_date', $today)
            ->count();

        return "Students absent today ({$today}): {$count}" . ($totalMarked > 0 ? " (out of {$totalMarked} marked)" : '');
    }

    public function monthlyPercentage(): string
    {
        $now = Carbon::now();
        $schoolId = $this->schoolContext->id();

        $totals = Attendance::query()
            ->where('school_id', $schoolId)
            ->whereYear('attendance_date', $now->year)
            ->whereMonth('attendance_date', $now->month)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_day_count,
                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_count
            ")
            ->first();

        $total = (int) ($totals->total ?? 0);
        $presentLike = (int) ($totals->present_count ?? 0)
            + (int) ($totals->late_count ?? 0)
            + (int) ($totals->half_day_count ?? 0)
            + (int) ($totals->excused_count ?? 0);

        $percentage = $total > 0 ? round(($presentLike / $total) * 100, 1) : 0;

        return "Monthly attendance percentage ({$now->format('F Y')}): {$percentage}% ({$presentLike} present-like out of {$total} total records)";
    }

    public function studentsBelow75(): string
    {
        $schoolId = $this->schoolContext->id();
        $academicYearId = $this->getActiveAcademicYearId();

        $students = Student::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->get();

        $belowThreshold = [];

        foreach ($students as $student) {
            $totalMarked = Attendance::query()
                ->where('school_id', $schoolId)
                ->where('student_id', $student->id)
                ->whereYear('attendance_date', now()->year)
                ->count();

            if ($totalMarked === 0) {
                continue;
            }

            $presentCount = Attendance::query()
                ->where('school_id', $schoolId)
                ->where('student_id', $student->id)
                ->whereIn('status', ['present', 'late', 'half_day', 'excused'])
                ->whereYear('attendance_date', now()->year)
                ->count();

            $percentage = round(($presentCount / $totalMarked) * 100, 1);

            if ($percentage < 75) {
                $belowThreshold[] = "{$student->full_name} (Admission No: {$student->admission_no}) - {$percentage}%";
            }
        }

        if (empty($belowThreshold)) {
            return 'No students with attendance below 75%.';
        }

        $count = count($belowThreshold);
        return "Students below 75% attendance ({$count}):\n" . implode("\n", array_slice($belowThreshold, 0, 50));
    }

    private function getActiveAcademicYearId(): ?int
    {
        return \App\Models\AcademicYear::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('is_active', true)
            ->value('id');
    }
}
