<?php

namespace App\Modules\AiAssistant\Services;

use App\Modules\AiAgents\Registry\AgentRegistry;
use App\Modules\AiAssistant\Erp\AiRequestLogger;
use App\Modules\AiAssistant\Erp\AiResponseGenerator;
use App\Modules\AiAssistant\Erp\ErpQueryExecutor;
use App\Modules\AiAssistant\Erp\ErpToolRegistry;
use App\Modules\AiAssistant\Erp\QueryPlanner;
use App\Modules\Exams\Services\ExamService;
use App\Modules\Homework\Services\HomeworkService;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Transport\Services\TransportService;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates the full AI pipeline for "Ask ERP":
 *
 *   question -> plan (structured tool request) -> authorize -> execute tool
 *   -> validate result -> generate answer -> log
 *
 * Action intents (payroll.generate, attendance.notify, ...) still require
 * explicit confirmation before execution.
 */
class AIService
{
    public function __construct(
        private readonly QueryPlanner $planner,
        private readonly ErpToolRegistry $toolRegistry,
        private readonly ErpQueryExecutor $executor,
        private readonly AiResponseGenerator $responseGenerator,
        private readonly AiRequestLogger $requestLogger,
        private readonly AgentRegistry $agentRegistry,
        private readonly AgentRouter $agentRouter,
        private readonly RoleDataScoper $roleScoper,
        private readonly ExamService $examService,
        private readonly HomeworkService $homeworkService,
        private readonly NotificationService $notificationService,
        private readonly TransportService $transportService,
        private readonly PendingActionService $pendingActions,
        private readonly ConfirmationClassifier $confirmationClassifier,
    ) {}

    public function ask(string $question, bool $confirmed = false): array
    {
        $startTime = microtime(true);
        $trimmed = trim($question);

        if ($trimmed === '') {
            return [
                'success' => false,
                'answer' => 'Please enter a question.',
            ];
        }

        // 0. PENDING-ACTION FIRST. If an action is awaiting confirmation for
        //    this user + school, resolve it BEFORE the query planner runs.
        //    "Sure", "Yes", "Cancel", "No" etc. are answered from the trusted
        //    server-side pending state — they never fall through to planning.
        $pending = $this->pendingActions->getPending();

        if ($pending && $pending->isPending()) {
            return $this->handlePendingMessage($pending, $trimmed, $confirmed, $startTime);
        }

        if ($pending && $pending->status === 'executing') {
            // A previous confirmation is mid-flight — never execute twice.
            return [
                'success' => false,
                'answer' => 'That action is already being processed. Please wait a moment.',
                'intent' => $pending->tool,
            ];
        }

        // 1. Understand the question -> structured tool request.
        $plan = $this->planner->plan($trimmed);

        $this->logDebug('Planned', [
            'query' => $trimmed,
            'intent' => $plan['intent'],
            'parameters' => $plan['parameters'],
            'confidence' => $plan['confidence'],
            'source' => $plan['source'] ?? 'unknown',
        ]);

        // 2. Authorize.
        if (!$this->isIntentAllowed($plan['intent'])) {
            $response = [
                'success' => false,
                'answer' => $this->roleScoper->getErrorMessage(auth()->user()),
                'intent' => $plan['intent'],
            ];
            $this->requestLogger->log([
                'intent' => $plan['intent'],
                'question' => $trimmed,
                'parameters' => $plan['parameters'],
                'response' => $response['answer'],
                'status' => 'denied',
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 1),
            ]);
            return $response;
        }

        // 3. Unknown intent -> friendly message listing supported areas.
        if ($plan['intent'] === 'unknown') {
            $response = [
                'success' => false,
                'answer' => "I couldn't understand your question. Try asking about:\n" . $this->getSupportedPreview(),
                'intent' => 'unknown',
                'confidence' => 0.0,
            ];
            $this->requestLogger->log([
                'intent' => 'unknown',
                'question' => $trimmed,
                'parameters' => [],
                'response' => $response['answer'],
                'status' => 'failed',
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 1),
            ]);
            return $response;
        }

        // 4. Action intents require confirmation.
        if ($this->toolRegistry->isAction($plan['intent'])) {
            try {
                // Persist the action server-side BEFORE asking for
                // confirmation so a follow-up "Sure"/"Yes"/"Cancel" resolves
                // against trusted state instead of being re-planned.
                if (! $confirmed) {
                    $pending = $this->pendingActions->create(
                        $plan['intent'],
                        $plan['parameters'],
                        $trimmed
                    );

                    $response = $this->handleActionIntent($plan, $trimmed, false);
                    $response['pending_action_id'] = $pending->getKey();
                    $response['pending_action'] = true;
                } else {
                    $response = $this->handleActionIntent($plan, $trimmed, true);
                }
            } catch (\Throwable $e) {
                Log::error('AI action handling failed', [
                    'intent' => $plan['intent'],
                    'error' => $e->getMessage(),
                ]);

                $response = [
                    'success' => false,
                    'answer' => "I wasn't able to process that action. Please try again.",
                    'intent' => $plan['intent'],
                ];
            }
            $this->requestLogger->log([
                'intent' => $plan['intent'],
                'question' => $trimmed,
                'parameters' => $plan['parameters'],
                'response' => $response['answer'] ?? null,
                'status' => $confirmed ? ($response['success'] ? 'action_executed' : 'action_error') : 'confirmation_pending',
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 1),
            ]);
            return $response;
        }

        // 5. Execute the validated ERP query.
        try {
            $result = $this->executor->execute($plan['intent'], $plan['parameters']);

            $this->logDebug('Executed', [
                'intent' => $plan['intent'],
                'success' => $result['success'],
                'count' => $result['count'] ?? null,
                'result_type' => $result['result_type'] ?? null,
            ]);

            // 6. Generate the answer strictly from validated result data.
            $answer = $this->responseGenerator->generate($result, $trimmed);

            $response = [
                'success' => $result['success'],
                'answer' => $answer,
                'intent' => $plan['intent'],
                'confidence' => $this->getConfidence($plan['intent'], $plan['confidence']),
                'result' => $result,
            ];

            $this->requestLogger->log([
                'intent' => $plan['intent'],
                'question' => $trimmed,
                'parameters' => $plan['parameters'],
                'response' => $answer,
                'status' => $result['success'] ? 'success' : 'error',
                'result_count' => $result['count'] ?? null,
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 1),
            ]);

            return $response;
        } catch (\Throwable $e) {
            Log::error('AI execution failed', [
                'intent' => $plan['intent'],
                'error' => $e->getMessage(),
            ]);

            $response = [
                'success' => false,
                'answer' => "I wasn't able to retrieve that information at the moment. Please try again.",
                'intent' => $plan['intent'],
            ];

            $this->requestLogger->log([
                'intent' => $plan['intent'],
                'question' => $trimmed,
                'parameters' => $plan['parameters'],
                'response' => $response['answer'],
                'status' => 'error',
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 1),
            ]);

            return $response;
        }
    }

    /**
     * A pending action is awaiting confirmation for this user + school.
     * Resolve the follow-up message BEFORE the query planner runs:
     *
     *   confirm    -> claim + execute the stored action (server-side params)
     *   cancel     -> discard the pending action
     *   ambiguous  -> do NOT execute; ask the user to confirm explicitly
     *   other      -> a new/modified request; supersede the old action and
     *                 re-plan this message normally
     */
    private function handlePendingMessage(
        \App\Modules\AiAssistant\Models\AiPendingAction $pending,
        string $message,
        bool $confirmed,
        float $startTime,
    ): array {
        // Explicit confirmation flag (legacy client button) also confirms.
        $decision = $confirmed
            ? ConfirmationClassifier::CONFIRM
            : $this->confirmationClassifier->classify($message);

        if ($decision === ConfirmationClassifier::CANCEL) {
            $this->pendingActions->markCancelled($pending);

            $response = [
                'success' => true,
                'answer' => $this->cancelledMessage($pending),
                'intent' => $pending->tool,
                'pending_action_id' => $pending->getKey(),
                'cancelled' => true,
                'confirmation_required' => false,
            ];

            $this->requestLogger->log([
                'intent' => $pending->tool,
                'question' => $message,
                'parameters' => $pending->parameters,
                'response' => $response['answer'],
                'status' => 'cancelled',
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 1),
            ]);

            return $response;
        }

        if ($decision === ConfirmationClassifier::AMBIGUOUS) {
            return [
                'success' => true,
                'answer' => $this->ambiguousMessage($pending),
                'intent' => $pending->tool,
                'pending_action_id' => $pending->getKey(),
                'confirmation_required' => true,
                'parameters' => $pending->parameters,
            ];
        }

        if ($decision === ConfirmationClassifier::CONFIRM) {
            return $this->executePendingAction($pending, $message, $startTime);
        }

        // OTHER: the user sent a new / modified request. The old pending
        // action must never fire. Supersede it and re-plan this message.
        $this->pendingActions->markCancelled($pending);

        return $this->ask($message);
    }

    /**
     * Claim + execute a confirmed pending action using the trusted server-side
     * parameters. Atomic claim prevents double submission.
     */
    private function executePendingAction(
        \App\Modules\AiAssistant\Models\AiPendingAction $pending,
        string $message,
        float $startTime,
    ): array {
        $user = auth()->user();

        // Re-verify ownership/school AND current authorization before running.
        if ($pending->user_id !== $user?->getKey()) {
            $this->pendingActions->markCancelled($pending);

            return [
                'success' => false,
                'answer' => "I can't find a pending action for your account.",
                'intent' => $pending->tool,
            ];
        }

        if (! $this->isIntentAllowed($pending->tool)) {
            $this->pendingActions->markCancelled($pending);

            return [
                'success' => false,
                'answer' => $this->roleScoper->getErrorMessage($user),
                'intent' => $pending->tool,
            ];
        }

        if (! $this->pendingActions->claimForExecution($pending)) {
            return [
                'success' => false,
                'answer' => 'That action was already processed. Please ask again if you need it.',
                'intent' => $pending->tool,
            ];
        }

        $plan = [
            'intent' => $pending->tool,
            'parameters' => $pending->parameters,
            'confidence' => 0.9,
        ];

        try {
            $response = $this->handleActionIntent($plan, $pending->question, true);

            if ($response['success']) {
                $this->pendingActions->markCompleted($pending);
            } else {
                // Execution failed — do NOT mark completed. Allow a safe retry.
                $this->pendingActions->markCancelled($pending);
            }

            $response['pending_action_id'] = $pending->getKey();
            $response['executed_from_pending'] = true;

            $this->requestLogger->log([
                'intent' => $pending->tool,
                'question' => $message,
                'parameters' => $pending->parameters,
                'response' => $response['answer'] ?? null,
                'status' => $response['success'] ? 'success' : 'error',
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 1),
            ]);

            return $response;
        } catch (\Throwable $e) {
            Log::error('AI pending action execution failed', [
                'intent' => $pending->tool,
                'error' => $e->getMessage(),
            ]);

            $this->pendingActions->markCancelled($pending);

            return [
                'success' => false,
                'answer' => "I wasn't able to complete that action. Nothing was changed — please try again.",
                'intent' => $pending->tool,
            ];
        }
    }

    private function cancelledMessage(\App\Modules\AiAssistant\Models\AiPendingAction $pending): string
    {
        return match ($pending->tool) {
            'notification.send' => 'Okay, I cancelled the notification. Nothing was sent.',
            'homework.create' => 'Okay, I cancelled the homework creation. No homework was created.',
            'exam.publish' => 'Okay, I cancelled publishing the exam results. Results were not published.',
            'transport.assign' => 'Okay, I cancelled the transport assignment. No students were assigned.',
            'payroll.generate' => 'Okay, I cancelled the payroll run. Payroll was not generated.',
            'attendance.notify' => 'Okay, I cancelled the attendance notification. No notifications were sent.',
            'fee.send_reminders' => 'Okay, I cancelled the fee reminders. No reminders were sent.',
            default => 'Okay, I cancelled that action.',
        };
    }

    private function ambiguousMessage(\App\Modules\AiAssistant\Models\AiPendingAction $pending): string
    {
        return "Please confirm whether you want me to proceed with this action. Reply \"Yes\" to continue or \"No\" to cancel.";
    }

    private function handleActionIntent(array $plan, string $question, bool $confirmed): array
    {
        $intent = $plan['intent'];
        $params = $plan['parameters'];

        $route = $this->agentRouter->route($intent);

        if (!$route) {
            return [
                'success' => false,
                'answer' => "I can't perform that action right now.",
                'intent' => $intent,
            ];
        }

        // Build a preview/confirmation message.
        if ($route['confirmation_required'] && !$confirmed) {
            $message = $this->buildActionConfirmation($intent, $params);

            return [
                'success' => true,
                'answer' => $message,
                'intent' => $intent,
                'confidence' => $plan['confidence'],
                'confirmation_required' => true,
                'parameters' => $params,
            ];
        }

        if ($route['type'] === 'agent') {
            return $this->executeActionAgent($intent, $route, $params, $confirmed);
        }

        if ($route['type'] === 'service') {
            return $this->executeActionService($intent, $route, $params);
        }

        return [
            'success' => false,
            'answer' => "I can't perform that action right now.",
            'intent' => $intent,
        ];
    }

    private function executeActionAgent(string $intent, array $route, array $params, bool $confirmed): array
    {
        $agent = $this->agentRegistry->get($route['agent']);

        if (!$agent) {
            return [
                'success' => false,
                'answer' => "That action isn't available right now.",
                'intent' => $intent,
            ];
        }

        $agentParams = $agent->validateParams($params);
        $preview = $agent->preview($agentParams);

        if ($route['confirmation_required'] && !$confirmed) {
            return [
                'success' => true,
                'answer' => $this->buildAgentPreviewMessage($route['label'], $preview, $intent),
                'intent' => $intent,
                'confidence' => $this->getConfidence($intent, 0.9),
                'confirmation_required' => true,
                'parameters' => $agentParams,
                'preview' => $preview,
            ];
        }

        $execution = $agent->execute($agentParams);
        $execution['records_processed'] = $execution['records_processed'] ?? $execution['total_employees'] ?? 0;

        return [
            'success' => true,
            'answer' => $this->summarizeActionResult($intent, $execution),
            'intent' => $intent,
            'confidence' => $this->getConfidence($intent, 0.9),
            'execution' => $execution,
            'confirmation_required' => false,
        ];
    }

    private function executeActionService(string $intent, array $route, array $params): array
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
                'answer' => "That action isn't available right now.",
                'intent' => $intent,
            ];
        }

        try {
            // exam.publish expects an Exam model, not the raw params array.
            if ($route['service'] === 'exam' && $route['method'] === 'publish') {
                $examId = (int) ($params['exam_id'] ?? 0);
                $exam = \App\Modules\Exams\Models\Exam::query()
                    ->where('school_id', app(\App\Core\Tenant\SchoolContext::class)->id())
                    ->find($examId);

                if (!$exam) {
                    return [
                        'success' => false,
                        'answer' => "I couldn't find that exam to publish. Please try again.",
                        'intent' => $intent,
                    ];
                }

                $result = $service->publish($exam);

                return [
                    'success' => true,
                    'answer' => $this->summarizeActionResult($intent, ['records_processed' => 1, 'exam' => $result->exam_name]),
                    'intent' => $intent,
                    'confidence' => $this->getConfidence($intent, 0.9),
                    'confirmation_required' => false,
                ];
            }

            if (!method_exists($service, $route['method'])) {
                return [
                    'success' => false,
                    'answer' => "That action isn't available right now.",
                    'intent' => $intent,
                ];
            }

            // notification.send creates an in-app announcement. Fill required
            // Notification columns so the DB insert cannot fail on NOT NULL.
            if ($intent === 'notification.send') {
                $params = array_merge([
                    'type' => 'announcement',
                    'priority' => 'medium',
                    'status' => 'sent',
                    'channel' => 'in_app',
                ], $params);
            }

            $result = $service->{$route['method']}($params);

            $summary = is_array($result) ? $result : ['data' => $result];

            // Report actual recipient count for notifications.
            if ($intent === 'notification.send' && ($summary['data'] ?? null) instanceof \App\Modules\Notifications\Models\Notification) {
                $notification = $summary['data'];
                $summary['notifications_created'] = $notification->users()->count();
            }

            return [
                'success' => true,
                'answer' => $this->summarizeActionResult($intent, $summary),
                'intent' => $intent,
                'confidence' => $this->getConfidence($intent, 0.9),
                'confirmation_required' => false,
            ];
        } catch (\Throwable $e) {
            Log::error('AI action execution failed', [
                'intent' => $intent,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'answer' => "I wasn't able to complete that action. Please try again.",
                'intent' => $intent,
            ];
        }
    }

    private function buildActionConfirmation(string $intent, array $params): string
    {
        $messages = [
            'payroll.generate' => function () use ($params) {
                $month = (int) ($params['month'] ?? now()->format('n'));
                $year = (int) ($params['year'] ?? now()->format('Y'));
                $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F');

                return "Generate payroll for {$monthName} {$year}?\n\nThis will create the payroll run, generate payslips, and notify employees. This action cannot be undone.";
            },
            'attendance.notify' => function () use ($params) {
                $date = $params['date'] ?? now()->format('Y-m-d');

                return "Send absence notifications to parents for {$date}?\n\nThis will notify parents of all students marked absent on this date.";
            },
            'fee.send_reminders' => function () use ($params) {
                $days = $params['days'] ?? 30;

                return "Send fee reminders to students with overdue fees ({$days}+ days)?\n\nThis will send in-app notifications to affected students and parents.";
            },
            'exam.publish' => function () use ($params) {
                $examId = $params['exam_id'] ?? 'N/A';

                return "Publish exam results (Exam #{$examId})?\n\nStudents and parents will be able to view the published results.";
            },
            'notification.send' => function () use ($params) {
                $title = $params['title'] ?? 'General notification';
                $target = $params['target_type'] ?? 'all';
                $message = $params['message'] ?? '';

                $lines = ['### Notification Ready', ''];
                $lines[] = '**Recipients:** '.ucfirst($target);
                $lines[] = '**Title:** '.$title;
                if ($message !== '') {
                    $lines[] = '**Message:** '.$message;
                }
                $lines[] = '';
                $lines[] = 'This notification will be sent immediately after you confirm.';

                return implode("\n", $lines);
            },
        ];

        if (isset($messages[$intent])) {
            return ($messages[$intent])();
        }

        return "Please confirm you want to perform this action.";
    }

    private function buildAgentPreviewMessage(string $label, array $preview, string $intent): string
    {
        $ready = $preview['ready'] ?? true;

        if (!$ready) {
            $errors = implode(', ', $preview['errors'] ?? ['Unknown issues']);
            return "{$label}: Cannot proceed. {$errors}";
        }

        $lines = ["{$label} - Ready to Process", ""];

        if ($intent === 'attendance.notify') {
            $lines[] = "**Date**: " . ($preview['date'] ?? now()->format('Y-m-d'));
            $lines[] = "**Students Marked**: " . ($preview['total_students'] ?? 0);
            $lines[] = "**Absent Students**: " . ($preview['absent_count'] ?? 0);
            $lines[] = "";
            $lines[] = "This will send absence notifications to parents.";
        } elseif ($intent === 'fee.send_reminders') {
            $lines[] = "**Overdue Period**: " . ($preview['days'] ?? 30) . "+ days";
            $lines[] = "**Affected Students**: " . ($preview['affected_students'] ?? 0);
            $lines[] = "**Total Outstanding**: \u{20B9}" . number_format($preview['total_outstanding'] ?? 0, 2);
            $lines[] = "";
            $lines[] = "This will send fee reminders.";
        } else {
            $lines[] = json_encode($preview, JSON_PRETTY_PRINT);
        }

        return implode("\n", $lines);
    }

    private function summarizeActionResult(string $intent, array $result): string
    {
        $lines = ['Action Complete', ''];

        $lines[] = 'Status: ' . (($result['success'] ?? true) ? 'Success' : 'Failed');
        $lines[] = '';

        if (isset($result['records_processed'])) {
            $lines[] = "Records Processed: {$result['records_processed']}";
        }

        if (isset($result['notifications_created'])) {
            $lines[] = "Notifications Sent: {$result['notifications_created']}";
        }

        if (isset($result['total_gross'])) {
            $lines[] = "Total Gross: \u{20B9}" . number_format($result['total_gross'], 2);
        }

        // notification.send -> Notification model wrapped as ['data' => $model].
        $data = $result['data'] ?? null;
        if ($data instanceof \App\Modules\Notifications\Models\Notification) {
            $lines[] = "Title: " . ($data->title ?? '');
            $lines[] = "Audience: " . ($data->target_label ?? $data->target_type ?? '');
        }

        return implode("\n", $lines);
    }

    private function isIntentAllowed(string $intent): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $this->roleScoper->isIntentAllowed($user, $intent);
    }

    private function getConfidence(string $intent, float $fallback): float
    {
        return max(0.0, min(1.0, $fallback));
    }

    private function getSupportedPreview(): string
    {
        $groups = [];

        foreach ($this->toolRegistry->all() as $tool => $config) {
            $module = $config['module'] ?? 'general';
            if (!isset($groups[$module])) {
                $groups[$module] = [];
            }
            if (!empty($config['keywords'])) {
                $groups[$module][] = $config['keywords'][0];
            }
        }

        $lines = [];
        foreach ($groups as $module => $questions) {
            $lines[] = '• ' . ucfirst($module) . ': ' . implode(', ', array_slice($questions, 0, 3));
        }

        return implode("\n", $lines);
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (!app()->environment('local', 'development')) {
            return;
        }

        Log::channel('daily')->debug("[AI Pipeline] {$message}", $context);
    }
}
