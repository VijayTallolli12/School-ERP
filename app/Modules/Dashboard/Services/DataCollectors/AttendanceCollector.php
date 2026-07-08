<?php

namespace App\Modules\Dashboard\Services\DataCollectors;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Teachers\Models\TeacherAttendance;
use App\Modules\Reports\Services\AbsentStudentReportService;
use Illuminate\Support\Facades\Cache;

class AttendanceCollector
{
    public function todayAbsentCount(int $schoolId): int
    {
        return Cache::remember("dashboard.attendance.absent.{$schoolId}", 60, fn () =>
            app(AbsentStudentReportService::class)->getTodayAbsentCount($schoolId)
        );
    }

    public function todayAttendanceRate(int $schoolId): float
    {
        return Cache::remember("dashboard.attendance.rate.{$schoolId}", 60, function () use ($schoolId) {
            $total = Attendance::query()->whereDate('attendance_date', today())->count();
            if ($total === 0) {
                return 0.0;
            }
            $present = Attendance::query()
                ->whereDate('attendance_date', today())
                ->where('status', 'present')
                ->count();

            return round(($present / $total) * 100, 1);
        });
    }

    public function teacherAttendanceToday(int $schoolId): int
    {
        return Cache::remember("dashboard.teacher.attendance.today.{$schoolId}", 60, fn () =>
            TeacherAttendance::query()->whereDate('attendance_date', today())->count()
        );
    }

    public function weeklyAttendanceTrend(int $schoolId): array
    {
        return Cache::remember("dashboard.attendance.weekly.{$schoolId}", 300, function () use ($schoolId) {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $day = now()->subDays($i)->format('D');
                $total = Attendance::query()->whereDate('attendance_date', $date)->count();
                $present = Attendance::query()->whereDate('attendance_date', $date)->where('status', 'present')->count();
                $data[] = [
                    'day' => $day,
                    'date' => $date,
                    'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                ];
            }

            return $data;
        });
    }
}
