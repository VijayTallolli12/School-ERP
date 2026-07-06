<?php

namespace App\Modules\AiAssistant\Services;

class AgentRouter
{
    private const DESTRUCTIVE_INTENTS = [
        'payroll.generate',
        'attendance.notify',
        'fee.send_reminders',
        'exam.publish',
        'notification.send',
    ];

    private const ROUTES = [
        'student.total' => [
            'type' => 'handler',
            'handler' => 'StudentQueryHandler',
            'method' => 'totalStudents',
        ],
        'student.admitted_this_month' => [
            'type' => 'handler',
            'handler' => 'StudentQueryHandler',
            'method' => 'admittedThisMonth',
        ],
        'student.by_class' => [
            'type' => 'handler',
            'handler' => 'StudentQueryHandler',
            'method' => 'studentsByClass',
        ],
        'attendance.absent_today' => [
            'type' => 'handler',
            'handler' => 'AttendanceQueryHandler',
            'method' => 'absentToday',
        ],
        'attendance.monthly_percentage' => [
            'type' => 'handler',
            'handler' => 'AttendanceQueryHandler',
            'method' => 'monthlyPercentage',
        ],
        'attendance.below_75' => [
            'type' => 'handler',
            'handler' => 'AttendanceQueryHandler',
            'method' => 'studentsBelow75',
        ],
        'fee.outstanding' => [
            'type' => 'handler',
            'handler' => 'FeeQueryHandler',
            'method' => 'totalOutstanding',
        ],
        'fee.pending_above' => [
            'type' => 'handler',
            'handler' => 'FeeQueryHandler',
            'method' => 'studentsWithPendingAbove',
            'param_method' => true,
        ],
        'fee.today_collection' => [
            'type' => 'handler',
            'handler' => 'FeeQueryHandler',
            'method' => 'todayCollection',
        ],
        'fee.top_defaulters' => [
            'type' => 'handler',
            'handler' => 'FeeQueryHandler',
            'method' => 'topDefaulters',
        ],
        'transport.route_occupancy' => [
            'type' => 'handler',
            'handler' => 'TransportQueryHandler',
            'method' => 'routeOccupancy',
        ],
        'transport.students_on_route' => [
            'type' => 'handler',
            'handler' => 'TransportQueryHandler',
            'method' => 'studentsOnRoute',
        ],
        'transport.vehicle_assignments' => [
            'type' => 'handler',
            'handler' => 'TransportQueryHandler',
            'method' => 'vehicleAssignments',
        ],
        'library.books_issued' => [
            'type' => 'handler',
            'handler' => 'LibraryQueryHandler',
            'method' => 'booksIssued',
        ],
        'library.overdue_books' => [
            'type' => 'handler',
            'handler' => 'LibraryQueryHandler',
            'method' => 'overdueBooks',
        ],
        'library.fine_collection' => [
            'type' => 'handler',
            'handler' => 'LibraryQueryHandler',
            'method' => 'fineCollection',
        ],
        'payroll.latest_run' => [
            'type' => 'handler',
            'handler' => 'PayrollQueryHandler',
            'method' => 'latestRun',
        ],
        'payroll.locked_runs' => [
            'type' => 'handler',
            'handler' => 'PayrollQueryHandler',
            'method' => 'lockedRuns',
        ],
        'payroll.highest_salary' => [
            'type' => 'handler',
            'handler' => 'PayrollQueryHandler',
            'method' => 'highestSalaryEmployees',
        ],
        'payroll.generated_this_month' => [
            'type' => 'handler',
            'handler' => 'PayrollQueryHandler',
            'method' => 'generatedThisMonth',
        ],
        'payroll.generate' => [
            'type' => 'agent',
            'agent' => 'payroll',
            'label' => 'Payroll Agent',
            'params' => ['month', 'year'],
        ],
        'attendance.notify' => [
            'type' => 'agent',
            'agent' => 'attendance',
            'label' => 'Attendance Agent',
            'params' => ['date'],
        ],
        'fee.send_reminders' => [
            'type' => 'agent',
            'agent' => 'fee_collection',
            'label' => 'Fee Collection Agent',
            'params' => ['days'],
        ],
        'exam.publish' => [
            'type' => 'service',
            'service' => 'exam',
            'method' => 'publish',
            'params' => ['exam_id'],
        ],
        'notification.send' => [
            'type' => 'service',
            'service' => 'notification',
            'method' => 'create',
            'params' => ['title', 'message', 'target_type'],
        ],
        'homework.create' => [
            'type' => 'service',
            'service' => 'homework',
            'method' => 'create',
            'params' => ['class_section_id', 'subject_id', 'title', 'due_date'],
        ],
        'transport.assign' => [
            'type' => 'service',
            'service' => 'transport',
            'method' => 'assignStudents',
            'params' => ['route_id', 'student_ids'],
        ],
        'school.summary' => [
            'type' => 'handler',
            'handler' => 'SchoolSummaryHandler',
            'method' => 'getExecutiveSummary',
        ],
    ];

    public function route(string $intent): ?array
    {
        if (!array_key_exists($intent, self::ROUTES)) {
            return null;
        }

        $route = self::ROUTES[$intent];
        $route['confirmation_required'] = in_array($intent, self::DESTRUCTIVE_INTENTS, true);

        return $route;
    }

    public function isDestructive(string $intent): bool
    {
        return in_array($intent, self::DESTRUCTIVE_INTENTS, true);
    }

    public static function getSupportedIntents(): array
    {
        $intents = [];
        foreach (self::ROUTES as $key => $route) {
            $intents[$key] = [
                'type' => $route['type'],
                'destructive' => in_array($key, self::DESTRUCTIVE_INTENTS, true),
            ];
        }
        return $intents;
    }
}
