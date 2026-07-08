<?php

namespace App\Modules\Dashboard\Services;

use App\Models\User;

class SidebarBuilder
{
    public function build(User $user): array
    {
        if ($user->hasRole('Teacher')) {
            return $this->buildForTeacher($user);
        }

        if ($user->hasRole('Principal')) {
            return $this->buildForPrincipal($user);
        }

        if ($user->hasRole('HR')) {
            return $this->buildForHR($user);
        }

        if ($user->hasRole('Parent')) {
            return $this->buildForParent($user);
        }

        if ($user->hasRole('Student')) {
            return $this->buildForStudent($user);
        }

        if ($user->hasRole('Accountant')) {
            return $this->buildForAccountant($user);
        }

        if ($user->hasRole('Librarian')) {
            return $this->buildForLibrarian($user);
        }

        if ($user->hasRole('Receptionist')) {
            return $this->buildForReceptionist($user);
        }

        if ($user->hasRole('Staff')) {
            return $this->buildForStaff($user);
        }

        $sections = [];

        if ($user->can('dashboard.view')) {
            $sections[] = [
                'header' => 'Operations',
                'items' => $this->buildOperations($user),
            ];
        }

        $academics = $this->buildAcademics($user);
        if (!empty($academics)) {
            $sections[] = [
                'header' => 'Academics',
                'items' => $academics,
            ];
        }

        $finance = $this->buildFinance($user);
        if (!empty($finance)) {
            $sections[] = [
                'header' => 'Finance',
                'items' => $finance,
            ];
        }

        if ($user->can('notifications.view')) {
            $sections[] = [
                'header' => 'Communication',
                'items' => [
                    ['label' => 'Notifications', 'route' => 'admin.notifications.index', 'icon' => 'bell', 'permission' => 'notifications.view'],
                ],
            ];
        }

        $sections[] = [
            'header' => 'AI Workspace',
            'items' => [
                ['label' => 'Ask ERP', 'route' => '#askErpModal', 'icon' => 'message', 'modal' => true],
                $this->item('Executive Copilot', 'admin.ai.dashboard', 'sparkles', null, $user),
                $this->item('AI Agents', 'admin.agents.index', 'robot', null, $user),
                $this->item('Execution History', 'admin.agents.history', 'clock', null, $user),
            ],
        ];

        $accessControl = array_filter([
            $this->item('Roles', 'admin.roles.index', 'id-badge', 'roles.view', $user),
            $this->item('Permissions', 'admin.permissions.index', 'key', 'permissions.view', $user),
        ]);
        if (!empty($accessControl)) {
            $sections[] = [
                'header' => 'Access Control',
                'items' => $accessControl,
            ];
        }

        $leaveMgmt = array_filter([
            $this->item('Leave Types', 'admin.leave-types.index', 'category', 'leave_management.view', $user),
            $this->item('Leave Requests', 'admin.leave-requests.index', 'list-check', 'leave_management.view', $user),
        ]);
        if (!empty($leaveMgmt)) {
            $sections[] = [
                'header' => 'Leave Management',
                'items' => $leaveMgmt,
            ];
        }

        $admin = $this->buildAdministration($user);
        if (!empty($admin)) {
            $sections[] = [
                'header' => 'Administration',
                'items' => $admin,
            ];
        }

        return $sections;
    }

    public function buildForTeacher(User $user): array
    {
        return [
            [
                'header' => 'Teacher',
                'items' => array_filter([
                    $this->item('Dashboard', 'admin.dashboard', 'gauge', 'dashboard.view', $user),
                    $this->item('My Timetable', 'admin.timetable.index', 'table', 'timetable.view', $user),
                    $this->item('Attendance', 'admin.attendance.index', 'calendar-check', 'attendance.view', $user),
                    $this->item('Homework', 'admin.homework.index', 'books', 'homework.view', $user),
                    $this->item('My Students', 'admin.students.index', 'school', 'students.view', $user),
                    $this->item('Marks', 'admin.exams.index', 'chart-arrows-vertical', 'exams.view', $user),
                    $this->item('Leave', 'admin.leave-requests.index', 'calendar-minus', null, $user),
                    $this->item('My Documents', 'admin.teacher-documents.index', 'file-text', null, $user),
                    $this->item('My Payslips', 'admin.payroll.payslips.my', 'cash', null, $user),
                    $this->item('Notifications', 'admin.notifications.index', 'bell', 'notifications.view', $user),
                    $this->item('Calendar', 'admin.calendar.index', 'calendar-event', 'academic_calendar.view', $user),
                    ['label' => 'Ask ERP', 'route' => '#askErpModal', 'icon' => 'message', 'modal' => true],
                ]),
            ],
        ];
    }

    public function buildForParent(User $user): array
    {
        return [
            [
                'header' => 'Parent Portal',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'parent-portal.dashboard', 'icon' => 'gauge', 'permission' => 'dashboard.view'],
                    ['label' => 'Attendance', 'route' => 'parent-portal.attendance', 'icon' => 'calendar-check', 'permission' => 'attendance.view'],
                    ['label' => 'Fees', 'route' => 'parent-portal.fees', 'icon' => 'receipt', 'permission' => 'fees.view'],
                    ['label' => 'Exam Results', 'route' => 'parent-portal.exam-results', 'icon' => 'chart-arrows-vertical', 'permission' => 'exams.view'],
                    ['label' => 'Timetable', 'route' => 'parent-portal.timetable', 'icon' => 'table', 'permission' => 'timetable.view'],
                    ['label' => 'Homework', 'route' => 'parent-portal.homework', 'icon' => 'books', 'permission' => 'homework.view'],
                    ['label' => 'Notifications', 'route' => 'parent-portal.notifications', 'icon' => 'bell', 'permission' => 'notifications.view'],
                ],
            ],
        ];
    }

    public function buildForStudent(User $user): array
    {
        return [
            [
                'header' => 'Student',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'gauge', 'permission' => 'dashboard.view'],
                    ['label' => 'Attendance', 'route' => 'admin.attendance.index', 'icon' => 'calendar-check', 'permission' => 'attendance.view'],
                    ['label' => 'Timetable', 'route' => 'admin.timetable.index', 'icon' => 'table', 'permission' => 'timetable.view'],
                    ['label' => 'Exams', 'route' => 'admin.exams.index', 'icon' => 'chart-arrows-vertical', 'permission' => 'exams.view'],
                ],
            ],
        ];
    }

    public function buildForAccountant(User $user): array
    {
        return [
            [
                'header' => 'Finance',
                'items' => array_filter([
                    $this->item('Dashboard', 'admin.dashboard', 'gauge', 'dashboard.view', $user),
                    $this->item('Fees', 'admin.fees.index', 'receipt', 'fees.view', $user),
                    $this->item('Transport', 'admin.transport.index', 'bus', 'transport.view', $user),
                    $this->item('Reports', 'reports.fees.index', 'chart-bar', 'fees.reports', $user),
                    $this->item('Notifications', 'admin.notifications.index', 'bell', 'notifications.view', $user),
                ]),
            ],
        ];
    }

    public function buildForLibrarian(User $user): array
    {
        return [
            [
                'header' => 'Library',
                'items' => array_filter([
                    $this->item('Dashboard', 'admin.dashboard', 'gauge', 'dashboard.view', $user),
                    $this->item('Library', 'admin.library.index', 'books', 'library.view', $user),
                    $this->item('Reports', 'reports.attendance.index', 'chart-bar', 'reports.view', $user),
                    $this->item('Notifications', 'admin.notifications.index', 'bell', 'notifications.view', $user),
                ]),
            ],
        ];
    }

    public function buildForReceptionist(User $user): array
    {
        return [
            [
                'header' => 'Reception',
                'items' => array_filter([
                    $this->item('Dashboard', 'admin.dashboard', 'gauge', 'dashboard.view', $user),
                    $this->item('Students', 'admin.students.index', 'school', 'students.view', $user),
                    $this->item('Parents', 'admin.parents.index', 'users', 'parents.view', $user),
                    $this->item('Notifications', 'admin.notifications.index', 'bell', 'notifications.view', $user),
                ]),
            ],
        ];
    }

    public function buildForStaff(User $user): array
    {
        return [
            [
                'header' => 'Staff',
                'items' => array_filter([
                    $this->item('Dashboard', 'admin.dashboard', 'gauge', 'dashboard.view', $user),
                    $this->item('Timetable', 'admin.timetable.index', 'table', 'timetable.view', $user),
                    $this->item('Attendance', 'admin.attendance.index', 'calendar-check', 'attendance.view', $user),
                    $this->item('Notifications', 'admin.notifications.index', 'bell', 'notifications.view', $user),
                ]),
            ],
        ];
    }

    public function buildForHR(User $user): array
    {
        return [
            [
                'header' => 'HR',
                'items' => array_filter([
                    $this->item('Dashboard', 'admin.dashboard', 'gauge', 'dashboard.view', $user),
                    $this->item('Employees', 'admin.hr.employees.index', 'users', 'hr.view', $user),
                    $this->item('Documents', 'admin.hr.documents.index', 'file-text', 'hr.view', $user),
                    $this->item('Notifications', 'admin.notifications.index', 'bell', 'notifications.view', $user),
                    ['label' => 'Ask ERP', 'route' => '#askErpModal', 'icon' => 'message', 'modal' => true],
                ]),
            ],
        ];
    }

    public function buildForPrincipal(User $user): array
    {
        return [
            [
                'header' => 'Principal',
                'items' => array_filter([
                    $this->item('Dashboard', 'admin.dashboard', 'gauge', 'dashboard.view', $user),
                    $this->item('Attendance', 'admin.attendance.index', 'calendar-check', 'attendance.view', $user),
                    $this->item('Timetable', 'admin.timetable.index', 'table', 'timetable.view', $user),
                    $this->item('Exams', 'admin.exams.index', 'chart-arrows-vertical', 'exams.view', $user),
                    $this->item('Students', 'admin.students.index', 'school', 'students.view', $user),
                    $this->item('Teachers', 'admin.teachers.index', 'presentation', 'teachers.view', $user),
                    $this->item('Homework', 'admin.homework.index', 'books', 'homework.view', $user),
                    $this->item('Calendar', 'admin.calendar.index', 'calendar-event', 'academic_calendar.view', $user),
                    $this->item('Fees', 'admin.fees.index', 'receipt', 'fees.view', $user),
                    $this->item('Reports', 'reports.attendance.index', 'chart-bar', 'reports.view', $user),
                    $this->item('Leave Approvals', 'admin.leave-requests.index', 'list-check', 'leave_management.view', $user),
                    $this->item('Notifications', 'admin.notifications.index', 'bell', 'notifications.view', $user),
                    ['label' => 'Ask ERP', 'route' => '#askErpModal', 'icon' => 'message', 'modal' => true],
                    $this->item('Executive Copilot', 'admin.ai.dashboard', 'sparkles', null, $user),
                ]),
            ],
        ];
    }

    private function buildOperations(User $user): array
    {
        return array_filter([
            $this->item('Dashboard', 'admin.dashboard', 'gauge', 'dashboard.view', $user),
            $this->item('Attendance', 'admin.attendance.index', 'calendar-check', 'attendance.view', $user),
            $this->item('Timetable', 'admin.timetable.index', 'table', 'timetable.view', $user),
            $this->item('Academic Calendar', 'admin.calendar.index', 'calendar-event', 'academic_calendar.view', $user),
            $this->item('Student Documents', 'admin.documents.index', 'file-text', 'student_documents.view', $user),
            $this->item('Transportation', 'admin.transport.index', 'bus', 'transport.view', $user),
        ]);
    }

    private function buildAcademics(User $user): array
    {
        return array_filter([
            $this->item('Students', 'admin.students.index', 'school', 'students.view', $user),
            $this->item('Parents', 'admin.parents.index', 'users', 'parents.view', $user),
            $this->item('Teachers', 'admin.teachers.index', 'presentation', 'teachers.view', $user),
            $this->item('Exams', 'admin.exams.index', 'chart-arrows-vertical', 'exams.view', $user),
            $this->item('Homework', 'admin.homework.index', 'books', 'homework.view', $user),
            $this->item('Academic', 'admin.academics.index', 'book-2', 'academics.view', $user),
            $this->item('Library', 'admin.library.index', 'books', 'library.view', $user),
        ]);
    }

    private function buildFinance(User $user): array
    {
        return array_filter([
            $this->item('Fees', 'admin.fees.index', 'receipt', 'fees.view', $user),
            $this->item('Payroll', 'admin.payroll.index', 'cash', 'payroll.view', $user),
        ]);
    }

    private function buildAdministration(User $user): array
    {
        return array_filter([
            $this->item('Users', 'admin.users.index', 'users-group', 'users.view', $user),
            $this->item('Settings', 'admin.settings.index', 'settings', 'settings.view', $user),
        ]);
    }

    private function item(string $label, string $route, string $icon, ?string $permission, User $user): ?array
    {
        if ($permission && !$user->can($permission)) {
            return null;
        }

        return [
            'label' => $label,
            'route' => $route,
            'icon' => $icon,
        ];
    }
}
