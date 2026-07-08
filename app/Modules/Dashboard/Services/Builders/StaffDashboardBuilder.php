<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Models\LoginActivity;
use App\Modules\Dashboard\Services\DataCollectors\AttendanceCollector;
use App\Modules\Dashboard\Services\DataCollectors\CalendarCollector;
use App\Modules\Leave\Models\LeaveRequest;

class StaffDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'Staff';
    }

    public function getLayout(): string
    {
        return 'admin';
    }

    protected function buildStatCards(): array
    {
        $calendarCollector = app(CalendarCollector::class);
        $attendanceCollector = app(AttendanceCollector::class);
        $pendingCount = LeaveRequest::query()->where('status', 'pending')->count();

        return [
            $this->statCard("Today's Schedule", $calendarCollector->todaySchedulesCount($this->schoolId), 'calendar-day', 'primary', null, null, route('admin.timetable.index')),
            $this->statCard('Attendance Rate', $attendanceCollector->todayAttendanceRate($this->schoolId).'%', 'check-circle', 'info'),
            $this->statCard('Pending Requests', $pendingCount, 'clipboard-list', 'warning', null, null, route('admin.leave-requests.index')),
            $this->statCard('Active Classes', $calendarCollector->activeClassCount($this->schoolId), 'chalkboard', 'success', null, null, route('admin.timetable.index')),
        ];
    }

    protected function buildWidgets(): array
    {
        $widgets = [];
        $pendingCount = LeaveRequest::query()->where('status', 'pending')->count();

        $widgets[] = $this->widget(
            'leave_requests',
            'Leave Requests',
            'summary',
            [
                'pending' => $pendingCount,
                'approved_today' => LeaveRequest::query()->where('status', 'approved')->whereDate('created_at', today())->count(),
            ],
            'calendar-minus',
            'warning',
            4, 2,
            route('admin.leave-requests.index'),
        );

        $widgets[] = $this->widget(
            'upcoming_events',
            'Upcoming Events',
            'list',
            app(CalendarCollector::class)->upcomingEvents($this->schoolId, 4),
            'calendar-alt',
            'info',
            4, 2,
            route('admin.calendar.index'),
            'No upcoming events',
        );

        $widgets[] = $this->widget(
            'attendance',
            "Today's Attendance",
            'donut',
            ['rate' => app(AttendanceCollector::class)->todayAttendanceRate($this->schoolId)],
            'calendar-check',
            'info',
            4, 1,
            route('admin.attendance.index'),
        );

        return $widgets;
    }

    protected function buildQuickActions(): array
    {
        return [
            $this->quickAction('Track Attendance', route('admin.attendance.index'), 'clipboard-check', 'primary', 'attendance.view'),
            $this->quickAction('Manage Leave', route('admin.leave-requests.index'), 'calendar-minus', 'warning'),
            $this->quickAction('Check Timetable', route('admin.timetable.index'), 'clock', 'info', 'timetable.view'),
        ];
    }

    protected function buildRecentActivity(): array
    {
        return LoginActivity::query()
            ->withoutGlobalScopes()
            ->with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->toArray();
    }
}
