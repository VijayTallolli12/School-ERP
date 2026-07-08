<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Models\LoginActivity;
use App\Modules\Dashboard\Services\DataCollectors\AttendanceCollector;
use App\Modules\Dashboard\Services\DataCollectors\CalendarCollector;
use App\Modules\Dashboard\Services\DataCollectors\FeeCollector;
use App\Modules\Dashboard\Services\DataCollectors\StudentCollector;
use App\Modules\Dashboard\Services\DataCollectors\TeacherCollector;
use App\Modules\Exams\Models\Exam;
use App\Modules\Leave\Models\LeaveRequest;
use App\Modules\Teachers\Models\TeacherAttendance;

class PrincipalDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'Principal';
    }

    public function getLayout(): string
    {
        return 'admin';
    }

    protected function buildStatCards(): array
    {
        $studentCollector = app(StudentCollector::class);
        $teacherCollector = app(TeacherCollector::class);
        $attendanceCollector = app(AttendanceCollector::class);

        $students = $studentCollector->totalCount($this->schoolId);
        $teachers = $teacherCollector->totalCount($this->schoolId);
        $attendanceRate = $attendanceCollector->todayAttendanceRate($this->schoolId);
        $pendingLeaves = LeaveRequest::query()->where('status', 'pending')->count();
        $teacherAttendanceToday = $attendanceCollector->teacherAttendanceToday($this->schoolId);

        return [
            $this->statCard('Total Students', $students, 'users', 'primary', null, null, route('admin.students.index')),
            $this->statCard('Teachers', $teachers, 'graduation-cap', 'success', null, null, route('admin.teachers.index')),
            $this->statCard("Today's Attendance", $attendanceRate.'%', 'check-circle', 'info'),
            $this->statCard('Pending Leaves', $pendingLeaves, 'calendar-clock', 'warning', null, null, route('admin.leave-requests.index')),
        ];
    }

    protected function buildWidgets(): array
    {
        $widgets = [];
        $calendarCollector = app(CalendarCollector::class);

        if ($this->can('attendance.view')) {
            $widgets[] = $this->widget(
                'attendance_today',
                "Today's Attendance",
                'donut',
                ['rate' => app(AttendanceCollector::class)->todayAttendanceRate($this->schoolId)],
                'calendar-check',
                'info',
                4, 2,
            );
        }

        if ($this->can('fees.view')) {
            $feeStats = app(FeeCollector::class)->dashboardStats($this->schoolId);
            $widgets[] = $this->widget(
                'fee_collection',
                'Fee Collection',
                'summary',
                [
                    'collected' => $feeStats['total_collected'] ?? 0,
                    'pending' => $feeStats['pending_fees'] ?? 0,
                ],
                'money-bill-wave',
                'warning',
                4, 2,
                route('admin.fees.index'),
            );
        }

        if ($this->can('leave_management.approve')) {
            $widgets[] = $this->widget(
                'pending_approvals',
                'Pending Leave Approvals',
                'list',
                LeaveRequest::query()->with(['student', 'leaveType', 'user'])->where('status', 'pending')->limit(5)->get()->toArray(),
                'check-double',
                'success',
                4, 2,
                route('admin.leave-requests.index'),
                'No pending leave approvals',
            );
        }

        if ($this->can('academic_calendar.view')) {
            $widgets[] = $this->widget(
                'upcoming_events',
                'Academic Calendar',
                'list',
                $calendarCollector->upcomingEvents($this->schoolId),
                'calendar-alt',
                'info',
                4, 2,
                route('admin.calendar.index'),
                'No upcoming events',
            );
        }

        $widgets[] = $this->widget(
            'school_stats',
            'School Overview',
            'stats_grid',
            [
                'active_classes' => $calendarCollector->activeClassCount($this->schoolId),
                'exams' => Exam::query()->count(),
                'today_schedules' => $calendarCollector->todaySchedulesCount($this->schoolId),
                'exams_published' => Exam::query()->where('is_published', 1)->count(),
            ],
            'school',
            'primary',
            8, 1,
        );

        return $widgets;
    }

    protected function buildQuickActions(): array
    {
        $actions = [
            $this->quickAction('Approve Leave', route('admin.leave-requests.index'), 'check-double', 'success', 'leave_management.view'),
            $this->quickAction('View Timetable', route('admin.timetable.index'), 'clock', 'info', 'timetable.view'),
            $this->quickAction('View Reports', route('reports.attendance.index'), 'chart-line', 'primary', 'reports.view'),
        ];

        return $actions;
    }

    protected function buildCharts(): array
    {
        $weeklyTrend = app(AttendanceCollector::class)->weeklyAttendanceTrend($this->schoolId);

        return [
            $this->chart(
                'weekly_attendance',
                'Weekly Attendance Trend',
                'line',
                collect($weeklyTrend)->pluck('day')->toArray(),
                [
                    [
                        'label' => 'Attendance %',
                        'data' => collect($weeklyTrend)->pluck('rate')->toArray(),
                        'borderColor' => '#4f46e5',
                        'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                        'fill' => true,
                    ],
                ],
                250,
            ),
        ];
    }

    protected function buildRecentActivity(): array
    {
        return LoginActivity::query()
            ->withoutGlobalScopes()
            ->with('user')
            ->latest()
            ->limit(6)
            ->get()
            ->toArray();
    }
}
