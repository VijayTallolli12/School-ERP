<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Modules\Dashboard\Services\DataCollectors\AttendanceCollector;
use App\Modules\Dashboard\Services\DataCollectors\TeacherDashboardCollector;
use App\Modules\Teachers\Models\Teacher;

class TeacherDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'Teacher';
    }

    public function getLayout(): string
    {
        return 'admin';
    }

    protected function buildStatCards(): array
    {
        $collector = app(TeacherDashboardCollector::class);
        $teacher = Teacher::query()->where('user_id', $this->user->getKey())->first();

        if (!$teacher) {
            return [];
        }

        $classSectionIds = $teacher->classSections()->pluck('class_section_id')->toArray();

        return [
            $this->statCard("Today's Classes", $collector->todayClassesCount($teacher->id, $this->schoolId), 'chalkboard', 'primary', null, null, route('admin.timetable.index')),
            $this->statCard('Pending Homework', $collector->pendingHomeworkCount($teacher->id, $classSectionIds), 'book', 'warning', null, null, route('admin.homework.index')),
            $this->statCard('Upcoming Exams', $collector->upcomingExamsCount($classSectionIds), 'file-alt', 'danger', null, null, route('admin.exams.index')),
            $this->statCard('Attendance Pending', $collector->attendancePendingCount($classSectionIds), 'check-circle', 'info', null, null, route('admin.attendance.index')),
        ];
    }

    protected function buildWidgets(): array
    {
        $widgets = [];
        $collector = app(TeacherDashboardCollector::class);
        $teacher = Teacher::query()->where('user_id', $this->user->getKey())->first();

        if (!$teacher) {
            return $widgets;
        }

        if ($this->can('academic_calendar.view')) {
            $widgets[] = $this->widget(
                'today_schedule',
                "Today's Schedule",
                'list',
                $collector->todaySchedule($teacher->id),
                'clock',
                'primary',
                4, 2,
                route('admin.timetable.index'),
                'No classes scheduled today',
            );
        }

        if ($this->can('attendance.view')) {
            $widgets[] = $this->widget(
                'attendance',
                'Student Attendance',
                'donut',
                ['rate' => app(AttendanceCollector::class)->todayAttendanceRate($this->schoolId)],
                'calendar-check',
                'info',
                4, 1,
                route('admin.attendance.index'),
            );
        }

        $leaveBalance = $collector->leaveBalance($this->user->getKey());

        $widgets[] = $this->widget(
            'leave_balance',
            'Leave Overview',
            'summary',
            $leaveBalance,
            'calendar-minus',
            'secondary',
            4, 1,
            route('admin.leave-requests.index'),
        );

        return $widgets;
    }

    protected function buildQuickActions(): array
    {
        return [
            $this->quickAction('Record Attendance', route('admin.attendance.index'), 'clipboard-check', 'primary', 'attendance.view'),
            $this->quickAction('Manage Homework', route('admin.homework.index'), 'book-open', 'success', 'homework.view'),
            $this->quickAction('View Timetable', route('admin.timetable.index'), 'clock', 'info', 'timetable.view'),
            $this->quickAction('View Exams', route('admin.exams.index'), 'upload', 'warning', 'exams.view'),
            $this->quickAction('Apply Leave', route('admin.leave-requests.index'), 'calendar-plus', 'secondary'),
        ];
    }

    protected function buildInsights(): array
    {
        return [
            [
                'type' => 'info',
                'title' => 'Students Requiring Attention',
                'message' => 'Review attendance records for students with below 75% attendance this month.',
                'action' => ['label' => 'View', 'route' => route('admin.attendance.index')],
            ],
            [
                'type' => 'tip',
                'title' => 'Homework Reminder',
                'message' => 'You have pending homework assignments to review and grade.',
                'action' => ['label' => 'Review', 'route' => route('admin.homework.index')],
            ],
        ];
    }

    protected function buildCharts(): array
    {
        return [];
    }

    protected function buildRecentActivity(): array
    {
        return [];
    }
}
