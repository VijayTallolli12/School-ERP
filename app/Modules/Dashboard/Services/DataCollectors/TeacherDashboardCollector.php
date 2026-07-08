<?php

namespace App\Modules\Dashboard\Services\DataCollectors;

use App\Modules\Academics\Models\ClassSection;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Exams\Models\Exam;
use App\Modules\Homework\Models\Homework;
use App\Modules\Leave\Models\LeaveRequest;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Timetable\Models\TimetableSlot;
use App\Modules\Timetable\Services\TimetableService;
use Illuminate\Support\Facades\Cache;

class TeacherDashboardCollector
{
    public function todayClassesCount(int $teacherId, int $schoolId): int
    {
        return Cache::remember("dashboard.teacher.today_classes.{$teacherId}.{$schoolId}", 60, function () use ($teacherId, $schoolId) {
            $teacher = Teacher::query()->find($teacherId);

            if (!$teacher) {
                return 0;
            }

            $classSectionIds = $teacher->classSections()->pluck('class_section_id')->toArray();

            if (empty($classSectionIds)) {
                return 0;
            }

            return app(TimetableService::class)->todaySchedulesCount();
        });
    }

    public function pendingHomeworkCount(int $teacherId, array $classSectionIds): int
    {
        $cacheKey = 'dashboard.teacher.pending_homework.' . $teacherId . '.' . md5(implode(',', $classSectionIds));

        return Cache::remember($cacheKey, 120, function () use ($classSectionIds) {
            if (empty($classSectionIds)) {
                return 0;
            }

            return Homework::query()
                ->whereIn('class_section_id', $classSectionIds)
                ->where('due_date', '>=', now())
                ->count();
        });
    }

    public function upcomingExamsCount(array $classSectionIds): int
    {
        $cacheKey = 'dashboard.teacher.upcoming_exams.' . md5(implode(',', $classSectionIds));

        return Cache::remember($cacheKey, 180, function () use ($classSectionIds) {
            if (empty($classSectionIds)) {
                return 0;
            }

            return Exam::query()
                ->whereIn('class_section_id', $classSectionIds)
                ->where('exam_date', '>=', now())
                ->count();
        });
    }

    public function attendancePendingCount(array $classSectionIds): int
    {
        $cacheKey = 'dashboard.teacher.attendance_pending.' . md5(implode(',', $classSectionIds));

        return Cache::remember($cacheKey, 60, function () use ($classSectionIds) {
            if (empty($classSectionIds)) {
                return 0;
            }

            $today = today();

            $totalStudents = ClassSection::query()
                ->whereIn('id', $classSectionIds)
                ->withCount('studentSessions')
                ->get()
                ->sum('student_sessions_count');

            $markedToday = Attendance::query()
                ->whereIn('class_section_id', $classSectionIds)
                ->whereDate('attendance_date', $today)
                ->count();

            return max(0, $totalStudents - $markedToday);
        });
    }

    public function leaveBalance(int $userId): array
    {
        return Cache::remember("dashboard.teacher.leave_balance.{$userId}", 300, function () use ($userId) {
            return [
                'approved' => LeaveRequest::query()
                    ->where('user_id', $userId)
                    ->where('status', 'approved')
                    ->count(),
                'pending' => LeaveRequest::query()
                    ->where('user_id', $userId)
                    ->where('status', 'pending')
                    ->count(),
            ];
        });
    }

    public function todaySchedule(int $teacherId): array
    {
        return Cache::remember("dashboard.teacher.today_schedule.{$teacherId}." . now()->format('Y-m-d'), 60, function () use ($teacherId) {
            $academicYear = app(TimetableService::class)->activeAcademicYear();

            if (!$academicYear) {
                return [];
            }

            $dayOfWeek = now()->dayOfWeekIso;

            return TimetableSlot::query()
                ->with(['classSection', 'subject'])
                ->where('teacher_id', $teacherId)
                ->where('academic_year_id', $academicYear->id)
                ->where('day_of_week', $dayOfWeek)
                ->where('status', 'active')
                ->orderBy('period_number')
                ->get()
                ->toArray();
        });
    }
}
