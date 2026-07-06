<?php

namespace App\Modules\AiAssistant\Services;

use App\Modules\AiAgents\Registry\AgentRegistry;
use App\Modules\AiAssistant\Handlers\AttendanceQueryHandler;
use App\Modules\AiAssistant\Handlers\FeeQueryHandler;
use App\Modules\AiAssistant\Handlers\LibraryQueryHandler;
use App\Modules\AiAssistant\Handlers\PayrollQueryHandler;
use App\Modules\AiAssistant\Handlers\SchoolSummaryHandler;
use App\Modules\AiAssistant\Handlers\StudentQueryHandler;
use App\Modules\AiAssistant\Handlers\TransportQueryHandler;
use App\Modules\Exams\Services\ExamService;
use App\Modules\Homework\Services\HomeworkService;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Transport\Services\TransportService;
use Illuminate\Support\Facades\Log;

class AIService
{
    private array $handlers;

    private const INTENT_AGENT_MAP = [
        'fee' => [
            'agent' => 'fee_collection',
            'label' => 'Fee Collection Agent',
            'params' => ['days' => 30],
        ],
        'attendance' => [
            'agent' => 'attendance',
            'label' => 'Attendance Agent',
            'params' => [],
        ],
        'library' => [
            'agent' => 'library',
            'label' => 'Library Agent',
            'params' => ['days' => 1],
        ],
        'payroll' => [
            'agent' => 'payroll',
            'label' => 'Payroll Agent',
            'params' => [],
        ],
    ];

    private const MONTH_NAMES = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];

    public function __construct(
        private readonly AIIntentService $aiIntentService,
        private readonly AgentRouter $agentRouter,
        private readonly AIResponseFormatter $responseFormatter,
        private readonly IntentResolver $resolver,
        private readonly AgentRegistry $agentRegistry,
        private readonly PlannerService $plannerService,
        private readonly OrchestratorService $orchestratorService,
        StudentQueryHandler $studentHandler,
        AttendanceQueryHandler $attendanceHandler,
        FeeQueryHandler $feeHandler,
        TransportQueryHandler $transportHandler,
        LibraryQueryHandler $libraryHandler,
        PayrollQueryHandler $payrollHandler,
        private readonly ?ExamService $examService = null,
        private readonly ?HomeworkService $homeworkService = null,
        private readonly ?NotificationService $notificationService = null,
        private readonly ?TransportService $transportService = null,
        private readonly ?SchoolSummaryHandler $schoolSummaryHandler = null,
    ) {
        $this->handlers = [
            'StudentQueryHandler' => $studentHandler,
            'AttendanceQueryHandler' => $attendanceHandler,
            'FeeQueryHandler' => $feeHandler,
            'TransportQueryHandler' => $transportHandler,
            'LibraryQueryHandler' => $libraryHandler,
            'PayrollQueryHandler' => $payrollHandler,
            'SchoolSummaryHandler' => $schoolSummaryHandler,
        ];
    }

    public function ask(string $question, bool $confirmed = false): array
    {
        $startTime = microtime(true);

        $trimmed = trim($question);

        if (empty($trimmed)) {
            return [
                'success' => false,
                'answer' => 'Please enter a question.',
            ];
        }

        $intentResult = $this->aiIntentService->resolve($trimmed);

        $this->logDebug('Intent resolved', [
            'query' => $trimmed,
            'intent' => $intentResult['intent'],
            'parameters' => $intentResult['parameters'],
            'confidence' => $intentResult['confidence'] ?? null,
            'action' => $intentResult['action'] ?? 'unknown',
            'source' => $intentResult['source'] ?? 'gemini',
        ]);

        if ($intentResult['intent'] === 'unknown') {
            return [
                'success' => false,
                'answer' => "I couldn't understand your question. Try asking about:\n" . $this->getSupportedPreview(),
                'intent' => 'unknown',
                'confidence' => 0.0,
            ];
        }

        $route = $this->agentRouter->route($intentResult['intent']);

        if (!$route) {
            return [
                'success' => false,
                'answer' => 'Internal error: route not found for intent.',
                'intent' => $intentResult['intent'],
            ];
        }

        $this->logDebug('Route selected', [
            'intent' => $intentResult['intent'],
            'route_type' => $route['type'],
            'handler' => $route['handler'] ?? null,
            'agent' => $route['agent'] ?? null,
            'service' => $route['service'] ?? null,
            'confirmation_required' => $route['confirmation_required'],
        ]);

        if ($route['confirmation_required'] && !$confirmed) {
            $response = $this->buildConfirmationResponse($intentResult, $route);
            $this->logDebug('Confirmation required', [
                'intent' => $intentResult['intent'],
                'params' => $intentResult['parameters'],
            ]);
            return $response;
        }

        try {
            $result = $this->executeRoute($intentResult, $route, $confirmed);

            $this->logDebug('Execution complete', [
                'intent' => $intentResult['intent'],
                'success' => $result['success'] ?? false,
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 1),
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('AI execution failed', [
                'intent' => $intentResult['intent'],
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'answer' => 'An error occurred while processing your request: ' . $e->getMessage(),
                'intent' => $intentResult['intent'],
            ];
        }
    }

    private function executeRoute(array $intentResult, array $route, bool $confirmed): array
    {
        $intent = $intentResult['intent'];
        $params = $intentResult['parameters'];

        if ($this->plannerService->isExecutiveIntent($intent)) {
            $this->logDebug('Executive intent detected, using copilot pipeline', [
                'intent' => $intent,
            ]);
            return $this->executeExecutivePlan($intent, $params);
        }

        if ($route['type'] === 'handler') {
            return $this->executeHandler($intent, $route, $params);
        }

        if ($route['type'] === 'agent') {
            return $this->executeAgent($intent, $route, $params, $confirmed);
        }

        if ($route['type'] === 'service') {
            return $this->executeService($intent, $route, $params);
        }

        return [
            'success' => false,
            'answer' => 'Unknown route type.',
            'intent' => $intent,
        ];
    }

    private function executeExecutivePlan(string $intent, array $params): array
    {
        $plan = $this->plannerService->plan($intent, $params);

        $this->logDebug('Executive plan created', [
            'intent' => $intent,
            'tasks_count' => count($plan['tasks'] ?? []),
            'parallel' => $plan['parallel'] ?? false,
        ]);

        $orchestratorOutput = $this->orchestratorService->execute($plan);

        $this->logDebug('Executive orchestration complete', [
            'intent' => $intent,
            'successful' => $orchestratorOutput['stats']['successful'] ?? 0,
            'failed' => $orchestratorOutput['stats']['failed'] ?? 0,
        ]);

        $formatted = $this->responseFormatter->formatExecutiveReport($orchestratorOutput);

        return [
            'success' => $orchestratorOutput['success'] ?? false,
            'answer' => $formatted,
            'intent' => $intent,
            'confidence' => $this->getConfidence($intent),
            'orchestrator_output' => $orchestratorOutput,
        ];
    }

    private function executeHandler(string $intent, array $route, array $params): array
    {
        $handlerClass = $route['handler'];
        $method = $route['method'];

        if (!isset($this->handlers[$handlerClass])) {
            return [
                'success' => false,
                'answer' => 'Internal error: handler not found.',
                'intent' => $intent,
            ];
        }

        $handler = $this->handlers[$handlerClass];

        if (!method_exists($handler, $method)) {
            return [
                'success' => false,
                'answer' => 'Internal error: query method not found.',
                'intent' => $intent,
            ];
        }

        $answer = !empty($params) && ($route['param_method'] ?? false)
            ? $handler->{$method}($params['amount'] ?? '1000')
            : $handler->{$method}();

        $formatted = $this->responseFormatter->format($intent, $answer, [
            'intent' => $intent,
            'raw_answer' => $answer,
            'parameters' => $params,
        ]);

        $response = [
            'success' => true,
            'answer' => $formatted,
            'intent' => $intent,
            'confidence' => $intent['confidence'] ?? $this->getConfidence($intent),
        ];

        $recommendation = $this->getAgentRecommendation($intent);
        if ($recommendation) {
            $response['agent_recommendation'] = $recommendation;
        }

        return $response;
    }

    private function executeAgent(string $intent, array $route, array $params, bool $confirmed): array
    {
        $agent = $this->agentRegistry->get($route['agent']);

        if (!$agent) {
            return [
                'success' => false,
                'answer' => "Agent '{$route['agent']}' not found.",
                'intent' => $intent,
            ];
        }

        $agentParams = $agent->validateParams($params);

        $preview = $agent->preview($agentParams);

        $response = [
            'success' => true,
            'intent' => $intent,
            'confidence' => $this->getConfidence($intent),
        ];

        if ($route['confirmation_required'] && !$confirmed) {
            $response['confirmation_required'] = true;
            $response['parameters'] = $agentParams;
            $response['preview'] = $preview;

            if ($intent === 'payroll.generate') {
                $response['answer'] = $this->buildPayrollConfirmation($agentParams, $preview);
            } else {
                $response['answer'] = $this->buildAgentPreviewMessage($route['label'], $preview, $intent);
            }

            return $response;
        }

        $execution = $agent->execute($agentParams);

        $execution['records_processed'] = $execution['records_processed'] ?? $execution['total_employees'] ?? 0;

        $formatted = $this->responseFormatter->formatActionResult($intent, $execution, $agentParams);

        $response['answer'] = $formatted;
        $response['execution'] = $execution;
        $response['confirmation_required'] = false;

        return $response;
    }

    private function executeService(string $intent, array $route, array $params): array
    {
        $serviceMap = [
            'exam' => $this->examService,
            'homework' => $this->homeworkService,
            'notification' => $this->notificationService,
            'transport' => $this->transportService,
        ];

        $service = $serviceMap[$route['service']] ?? null;

        if (!$service) {
            return [
                'success' => false,
                'answer' => "Service '{$route['service']}' not available.",
                'intent' => $intent,
            ];
        }

        $method = $route['method'];

        if (!method_exists($service, $method)) {
            return [
                'success' => false,
                'answer' => "Method '{$method}' not found on service.",
                'intent' => $intent,
            ];
        }

        $result = $service->{$method}($params);

        return [
            'success' => true,
            'answer' => $this->responseFormatter->formatActionResult($intent, $result, $params),
            'intent' => $intent,
            'confidence' => $this->getConfidence($intent),
            'confirmation_required' => false,
        ];
    }

    private function buildPayrollConfirmation(array $params, array $preview): string
    {
        $monthNum = (int) ($params['month'] ?? now()->format('n'));
        $year = (int) ($params['year'] ?? now()->format('Y'));
        $monthName = self::MONTH_NAMES[$monthNum] ?? 'Unknown';
        $employeeCount = $preview['total_employees'] ?? 0;
        $estimatedGross = $preview['estimated_gross'] ?? 0;

        $lines = [
            "Payroll Execution Required",
            "",
            "**Period**: {$monthName} {$year}",
            "**Eligible Employees**: {$employeeCount}",
            "**Estimated Gross Salary**: \u{20B9}" . number_format($estimatedGross, 2),
            "",
            "This action will:",
            "  \u{2714}  Generate payroll for all eligible employees",
            "  \u{2714}  Lock the payroll run",
            "  \u{2714}  Generate individual payslips",
            "  \u{2714}  Notify employees of payslip availability",
            "",
            "**Important**: This action cannot be undone once confirmed.",
        ];

        return implode("\n", $lines);
    }

    private function buildAgentPreviewMessage(string $label, array $preview, string $intent): string
    {
        $ready = $preview['ready'] ?? true;

        if (!$ready) {
            $errors = implode(', ', $preview['errors'] ?? ['Unknown issues']);
            return "{$label}: Cannot proceed. {$errors}";
        }

        $lines = ["**{$label}** - Ready to Process", ""];

        if ($intent === 'attendance.notify') {
            $absentCount = $preview['absent_count'] ?? 0;
            $totalStudents = $preview['total_students'] ?? 0;
            $date = $preview['date'] ?? now()->format('Y-m-d');
            $lines[] = "**Date**: {$date}";
            $lines[] = "**Students Marked**: {$totalStudents}";
            $lines[] = "**Absent Students**: {$absentCount}";
            $lines[] = "";
            $lines[] = "This will send absence notifications to parents of {$absentCount} students.";
        } elseif ($intent === 'fee.send_reminders') {
            $totalOutstanding = $preview['total_outstanding'] ?? 0;
            $affectedStudents = $preview['affected_students'] ?? 0;
            $days = $preview['days'] ?? 30;
            $lines[] = "**Overdue Period**: {$days}+ days";
            $lines[] = "**Affected Students**: {$affectedStudents}";
            $lines[] = "**Total Outstanding**: \u{20B9}" . number_format($totalOutstanding, 2);
            $lines[] = "";
            $lines[] = "This will send fee reminders to {$affectedStudents} students.";
        } else {
            $lines[] = json_encode($preview, JSON_PRETTY_PRINT);
        }

        return implode("\n", $lines);
    }

    private function buildConfirmationResponse(array $intentResult, array $route): array
    {
        $intent = $intentResult['intent'];
        $params = $intentResult['parameters'];
        $paramNames = $route['params'] ?? [];

        $previewParams = [];
        foreach ($paramNames as $name) {
            $previewParams[$name] = $params[$name] ?? 'N/A';
        }

        $messages = [
            'payroll.generate' => fn () => $this->buildPayrollConfirmation($params, []),
            'attendance.notify' => function () use ($params) {
                $date = $params['date'] ?? now()->format('Y-m-d');
                return "Send absence notifications to parents for {$date}.\n\nThis will notify parents of all students marked absent on this date.";
            },
            'fee.send_reminders' => function () use ($params) {
                $days = $params['days'] ?? 30;
                return "Send fee reminders to students with overdue fees ({$days}+ days).\n\nThis will send in-app notifications to affected students and parents.";
            },
            'exam.publish' => function () use ($params) {
                $examId = $params['exam_id'] ?? 'N/A';
                return "Publish exam results (Exam #{$examId}).\n\nStudents and parents will be able to view the published results.";
            },
            'notification.send' => function () use ($params) {
                $title = $params['title'] ?? 'General notification';
                $target = $params['target_type'] ?? 'all';
                return "Send notification: \"{$title}\" to {$target}.\n\nThis will be delivered immediately.";
            },
        ];

        if (isset($messages[$intent])) {
            $message = ($messages[$intent])();
        } else {
            $paramStr = implode(', ', array_map(
                fn ($k, $v) => ucfirst(str_replace('_', ' ', $k)) . ": {$v}",
                array_keys($previewParams),
                $previewParams
            ));
            $message = "This action requires confirmation before execution.\n\nParameters: {$paramStr}";
        }

        return [
            'success' => true,
            'answer' => $message,
            'intent' => $intent,
            'confidence' => $intentResult['confidence'],
            'confirmation_required' => true,
            'parameters' => $params,
            'agent_recommendation' => $this->getAgentRecommendation($intent),
        ];
    }

    private function getAgentRecommendation(?string $intentKey): ?array
    {
        if (!$intentKey) {
            return null;
        }

        $category = explode('.', $intentKey)[0];

        if (!isset(self::INTENT_AGENT_MAP[$category])) {
            return null;
        }

        $map = self::INTENT_AGENT_MAP[$category];

        $params = $map['params'];

        if ($category === 'attendance') {
            $params['date'] = now()->format('Y-m-d');
        }

        if ($category === 'payroll') {
            $params['month'] = (int) now()->format('n');
            $params['year'] = (int) now()->format('Y');
        }

        return [
            'agent' => $map['agent'],
            'label' => $map['label'],
            'params' => $params,
        ];
    }

    private function getConfidence(string $intent): float
    {
        $category = explode('.', $intent)[0] ?? '';

        $confidenceMap = [
            'student' => 0.95,
            'attendance' => 0.92,
            'fee' => 0.93,
            'transport' => 0.90,
            'library' => 0.91,
            'payroll' => 0.94,
            'exam' => 0.88,
            'notification' => 0.87,
            'homework' => 0.86,
            'school' => 0.95,
        ];

        return $confidenceMap[$category] ?? 0.85;
    }

    private function getSupportedPreview(): string
    {
        $groups = $this->resolver->getSupportedQuestions();
        $lines = [];
        foreach ($groups as $category => $questions) {
            $lines[] = '• ' . ucfirst($category) . ': ' . implode(', ', array_slice($questions, 0, 3));
        }
        $lines[] = '• Reports: today\'s school summary';
        return implode("\n", $lines);
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (!app()->environment('local', 'development')) {
            return;
        }

        Log::channel('daily')->debug("[AI Copilot] {$message}", $context);
    }
}
