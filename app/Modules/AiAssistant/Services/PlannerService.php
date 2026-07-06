<?php

namespace App\Modules\AiAssistant\Services;

class PlannerService
{
    private const EXECUTIVE_INTENTS = [
        'school.summary' => [
            'modules' => ['attendance', 'fees', 'transport', 'homework', 'exams', 'leave', 'notifications', 'library'],
            'priority' => 'high',
            'description' => 'Full school executive summary',
        ],
        'school.health' => [
            'modules' => ['attendance', 'fees', 'transport', 'library'],
            'priority' => 'high',
            'description' => 'School health assessment',
        ],
        'school.performance' => [
            'modules' => ['attendance', 'fees', 'exams'],
            'priority' => 'medium',
            'description' => 'Academic and financial performance',
        ],
    ];

    private const MODULE_HANDLERS = [
        'attendance' => [
            'handler' => 'AttendanceQueryHandler',
            'methods' => ['absentToday', 'monthlyPercentage', 'studentsBelow75'],
            'primary_method' => 'absentToday',
            'label' => 'Attendance',
        ],
        'fees' => [
            'handler' => 'FeeQueryHandler',
            'methods' => ['totalOutstanding', 'todayCollection', 'topDefaulters'],
            'primary_method' => 'totalOutstanding',
            'label' => 'Fees',
        ],
        'transport' => [
            'handler' => 'TransportQueryHandler',
            'methods' => ['routeOccupancy', 'studentsOnRoute', 'vehicleAssignments'],
            'primary_method' => 'routeOccupancy',
            'label' => 'Transport',
        ],
        'homework' => [
            'handler' => 'SchoolSummaryHandler',
            'methods' => ['getExecutiveSummary'],
            'primary_method' => 'getExecutiveSummary',
            'sub_key' => 'homework',
            'label' => 'Homework',
        ],
        'exams' => [
            'handler' => 'SchoolSummaryHandler',
            'methods' => ['getExecutiveSummary'],
            'primary_method' => 'getExecutiveSummary',
            'sub_key' => 'exams',
            'label' => 'Exams',
        ],
        'leave' => [
            'handler' => 'SchoolSummaryHandler',
            'methods' => ['getExecutiveSummary'],
            'primary_method' => 'getExecutiveSummary',
            'sub_key' => 'leave',
            'label' => 'Leave',
        ],
        'notifications' => [
            'handler' => 'SchoolSummaryHandler',
            'methods' => ['getExecutiveSummary'],
            'primary_method' => 'getExecutiveSummary',
            'sub_key' => 'notifications',
            'label' => 'Notifications',
        ],
        'library' => [
            'handler' => 'LibraryQueryHandler',
            'methods' => ['booksIssued', 'overdueBooks', 'fineCollection'],
            'primary_method' => 'booksIssued',
            'label' => 'Library',
        ],
        'payroll' => [
            'handler' => 'PayrollQueryHandler',
            'methods' => ['latestRun', 'lockedRuns', 'highestSalaryEmployees'],
            'primary_method' => 'latestRun',
            'label' => 'Payroll',
        ],
        'students' => [
            'handler' => 'StudentQueryHandler',
            'methods' => ['totalStudents', 'admittedThisMonth', 'studentsByClass'],
            'primary_method' => 'totalStudents',
            'label' => 'Students',
        ],
    ];

    public function plan(string $intent, array $parameters = []): array
    {
        $intentConfig = self::EXECUTIVE_INTENTS[$intent] ?? null;

        if ($intentConfig) {
            return $this->buildExecutivePlan($intentConfig, $intent, $parameters);
        }

        return $this->buildSingleModulePlan($intent, $parameters);
    }

    private function buildExecutivePlan(array $intentConfig, string $intent, array $parameters): array
    {
        $tasks = [];

        foreach ($intentConfig['modules'] as $moduleName) {
            $moduleConfig = self::MODULE_HANDLERS[$moduleName] ?? null;

            if (!$moduleConfig) {
                continue;
            }

            $tasks[] = [
                'module' => $moduleName,
                'handler' => $moduleConfig['handler'],
                'method' => $moduleConfig['primary_method'],
                'sub_key' => $moduleConfig['sub_key'] ?? null,
                'label' => $moduleConfig['label'],
                'priority' => $intentConfig['priority'],
                'params' => $parameters,
            ];
        }

        return [
            'type' => 'executive',
            'intent' => $intent,
            'description' => $intentConfig['description'],
            'tasks' => $tasks,
            'parallel' => true,
            'aggregate' => true,
        ];
    }

    private function buildSingleModulePlan(string $intent, array $parameters): array
    {
        $parts = explode('.', $intent);
        $module = $parts[0] ?? '';

        $moduleMap = [
            'student' => 'students',
            'attendance' => 'attendance',
            'fee' => 'fees',
            'transport' => 'transport',
            'library' => 'library',
            'payroll' => 'payroll',
            'exam' => 'exams',
            'homework' => 'homework',
            'notification' => 'notifications',
            'school' => 'students',
        ];

        $moduleName = $moduleMap[$module] ?? $module;
        $moduleConfig = self::MODULE_HANDLERS[$moduleName] ?? null;

        if (!$moduleConfig) {
            return [
                'type' => 'single',
                'intent' => $intent,
                'description' => 'Single module query',
                'tasks' => [],
                'parallel' => false,
                'aggregate' => false,
            ];
        }

        $methodName = $parts[1] ?? null;
        $method = $this->resolveMethod($moduleName, $methodName);

        return [
            'type' => 'single',
            'intent' => $intent,
            'description' => $moduleConfig['label'] . ' query',
            'tasks' => [
                [
                    'module' => $moduleName,
                    'handler' => $moduleConfig['handler'],
                    'method' => $method,
                    'sub_key' => $moduleConfig['sub_key'] ?? null,
                    'label' => $moduleConfig['label'],
                    'priority' => 'high',
                    'params' => $parameters,
                ],
            ],
            'parallel' => false,
            'aggregate' => false,
        ];
    }

    private function resolveMethod(string $moduleName, ?string $subAction): string
    {
        $moduleConfig = self::MODULE_HANDLERS[$moduleName] ?? null;

        if (!$moduleConfig) {
            return 'index';
        }

        if (!$subAction) {
            return $moduleConfig['primary_method'];
        }

        $methodMap = [
            'total' => 'totalStudents',
            'admitted_this_month' => 'admittedThisMonth',
            'by_class' => 'studentsByClass',
            'absent_today' => 'absentToday',
            'monthly_percentage' => 'monthlyPercentage',
            'below_75' => 'studentsBelow75',
            'outstanding' => 'totalOutstanding',
            'pending_above' => 'studentsWithPendingAbove',
            'today_collection' => 'todayCollection',
            'top_defaulters' => 'topDefaulters',
            'route_occupancy' => 'routeOccupancy',
            'students_on_route' => 'studentsOnRoute',
            'vehicle_assignments' => 'vehicleAssignments',
            'books_issued' => 'booksIssued',
            'overdue_books' => 'overdueBooks',
            'fine_collection' => 'fineCollection',
            'latest_run' => 'latestRun',
            'locked_runs' => 'lockedRuns',
            'highest_salary' => 'highestSalaryEmployees',
            'generated_this_month' => 'generatedThisMonth',
            'summary' => 'getExecutiveSummary',
        ];

        return $methodMap[$subAction] ?? $moduleConfig['primary_method'];
    }

    public function isExecutiveIntent(string $intent): bool
    {
        return isset(self::EXECUTIVE_INTENTS[$intent]);
    }

    public function getExecutiveIntents(): array
    {
        return array_keys(self::EXECUTIVE_INTENTS);
    }

    public function getModuleHandlers(): array
    {
        return self::MODULE_HANDLERS;
    }
}
