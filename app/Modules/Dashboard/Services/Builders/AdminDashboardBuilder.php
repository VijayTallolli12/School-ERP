<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Models\LoginActivity;
use App\Modules\Dashboard\Services\DataCollectors\AttendanceCollector;
use App\Modules\Dashboard\Services\DataCollectors\CalendarCollector;
use App\Modules\Dashboard\Services\DataCollectors\FeeCollector;
use App\Modules\Dashboard\Services\DataCollectors\StudentCollector;
use App\Modules\Dashboard\Services\DataCollectors\TeacherCollector;
use App\Modules\Documents\Services\DocumentService;
use App\Modules\Exams\Models\Exam;
use Spatie\Permission\Models\Role;

class AdminDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'Admin';
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
        $feeCollector = app(FeeCollector::class);

        $students = $studentCollector->totalCount($this->schoolId);
        $teachers = $teacherCollector->totalCount($this->schoolId);
        $attendanceRate = $attendanceCollector->todayAttendanceRate($this->schoolId);
        $totalCollected = $feeCollector->totalCollected($this->schoolId);

        return [
            $this->statCard('Total Students', $students, 'users', 'primary', 'up', null, route('admin.students.index')),
            $this->statCard('Teachers', $teachers, 'graduation-cap', 'success', 'up', null, route('admin.teachers.index')),
            $this->statCard('Attendance Rate', $attendanceRate.'%', 'check-circle', 'info'),
            $this->statCard('Total Collected', 'Rs '.number_format($totalCollected, 0), 'rupee-sign', 'warning', null, null, route('admin.fees.index')),
        ];
    }

    protected function buildWidgets(): array
    {
        $widgets = [];

        $calendarCollector = app(CalendarCollector::class);
        $studentCollector = app(StudentCollector::class);
        $teacherCollector = app(TeacherCollector::class);

        if ($this->can('attendance.view')) {
            $widgets[] = $this->widget(
                'attendance_today',
                "Today's Attendance",
                'donut',
                ['rate' => app(AttendanceCollector::class)->todayAttendanceRate($this->schoolId)],
                'calendar-check',
                'info',
                4, 2,
                route('admin.attendance.index'),
            );
        }

        if ($this->can('fees.view')) {
            $widgets[] = $this->widget(
                'fee_summary',
                'Fee Summary',
                'donut',
                app(FeeCollector::class)->dashboardStats($this->schoolId),
                'money-bill-wave',
                'warning',
                4, 2,
                route('admin.fees.index'),
            );
        }

        if ($this->can('academic_calendar.view')) {
            $widgets[] = $this->widget(
                'upcoming_events',
                'Upcoming Events',
                'list',
                $calendarCollector->upcomingEvents($this->schoolId),
                'calendar',
                'info',
                4, 2,
                route('admin.calendar.index'),
                'No upcoming events',
            );
        }

        if ($this->can('student_documents.view')) {
            $docService = app(DocumentService::class);
            $widgets[] = $this->widget(
                'document_alerts',
                'Document Alerts',
                'alerts',
                [
                    'expiring_count' => $docService->getExpiringCount(30),
                    'pending_count' => $docService->getPendingCount(),
                ],
                'file-alt',
                'danger',
                4, 1,
                route('admin.documents.index'),
            );
        }

        $widgets[] = $this->widget(
            'quick_stats',
            'Platform Overview',
            'stats_grid',
            [
                'active_classes' => $calendarCollector->activeClassCount($this->schoolId),
                'exams' => Exam::query()->count(),
                'today_schedules' => $calendarCollector->todaySchedulesCount($this->schoolId),
                'logins_today' => LoginActivity::query()->withoutGlobalScopes()->whereDate('created_at', today())->count(),
                'roles' => Role::query()->when($this->schoolId, fn ($q) => $q->where('school_id', $this->schoolId))->count(),
                'new_students' => $studentCollector->newAdmissions($this->schoolId),
            ],
            'chart-bar',
            'secondary',
            8, 1,
        );

        return $widgets;
    }

    protected function buildQuickActions(): array
    {
        return [
            $this->quickAction('Browse Students', route('admin.students.index'), 'user-plus', 'primary', 'students.view'),
            $this->quickAction('Browse Teachers', route('admin.teachers.index'), 'chalkboard-teacher', 'success', 'teachers.view'),
            $this->quickAction('Manage Fees', route('admin.fees.index'), 'money-bill', 'warning', 'fees.view'),
            $this->quickAction('View Reports', route('reports.students.index'), 'chart-line', 'info', 'reports.view'),
            $this->quickAction('Manage Users', route('admin.users.index'), 'users-cog', 'secondary', 'users.view'),
        ];
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
            ->limit(8)
            ->get()
            ->toArray();
    }
}
