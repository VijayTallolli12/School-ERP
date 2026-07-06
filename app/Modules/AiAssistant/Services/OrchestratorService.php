<?php

namespace App\Modules\AiAssistant\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

class OrchestratorService
{
    private array $handlers;

    public function __construct(
        private readonly PlannerService $planner,
        private readonly InsightGenerator $insightGenerator,
    ) {
        $this->handlers = [
            'AttendanceQueryHandler' => app(\App\Modules\AiAssistant\Handlers\AttendanceQueryHandler::class),
            'FeeQueryHandler' => app(\App\Modules\AiAssistant\Handlers\FeeQueryHandler::class),
            'TransportQueryHandler' => app(\App\Modules\AiAssistant\Handlers\TransportQueryHandler::class),
            'LibraryQueryHandler' => app(\App\Modules\AiAssistant\Handlers\LibraryQueryHandler::class),
            'PayrollQueryHandler' => app(\App\Modules\AiAssistant\Handlers\PayrollQueryHandler::class),
            'StudentQueryHandler' => app(\App\Modules\AiAssistant\Handlers\StudentQueryHandler::class),
            'SchoolSummaryHandler' => app(\App\Modules\AiAssistant\Handlers\SchoolSummaryHandler::class),
        ];
    }

    public function execute(array $plan): array
    {
        $startTime = microtime(true);

        $tasks = $plan['tasks'] ?? [];
        $parallel = $plan['parallel'] ?? false;
        $aggregate = $plan['aggregate'] ?? false;

        if (empty($tasks)) {
            return $this->buildEmptyResult($plan);
        }

        $results = $parallel
            ? $this->executeParallel($tasks)
            : $this->executeSequential($tasks);

        $output = $this->aggregateResults($results, $plan);

        if ($aggregate) {
            $output['insights'] = $this->insightGenerator->generate($output);
        }

        $elapsed = round((microtime(true) - $startTime) * 1000, 1);

        $this->logDebug('Orchestration complete', [
            'intent' => $plan['intent'] ?? 'unknown',
            'tasks_count' => count($tasks),
            'successful' => count(array_filter($results, fn ($r) => $r['success'])),
            'failed' => count(array_filter($results, fn ($r) => !$r['success'])),
            'duration_ms' => $elapsed,
        ]);

        return $output;
    }

    private function executeParallel(array $tasks): array
    {
        $results = [];

        foreach ($tasks as $index => $task) {
            $results[$index] = $this->executeTask($task);
        }

        return $results;
    }

    private function executeSequential(array $tasks): array
    {
        $results = [];

        foreach ($tasks as $index => $task) {
            $results[$index] = $this->executeTask($task);
        }

        return $results;
    }

    private function executeTask(array $task): array
    {
        $handlerName = $task['handler'];
        $method = $task['method'];
        $params = $task['params'] ?? [];
        $subKey = $task['sub_key'] ?? null;

        $handler = $this->handlers[$handlerName] ?? null;

        if (!$handler) {
            return [
                'success' => false,
                'module' => $task['module'],
                'label' => $task['label'],
                'error' => "Handler not found: {$handlerName}",
                'data' => null,
            ];
        }

        if (!method_exists($handler, $method)) {
            return [
                'success' => false,
                'module' => $task['module'],
                'label' => $task['label'],
                'error' => "Method not found: {$handlerName}::{$method}",
                'data' => null,
            ];
        }

        try {
            $data = $handler->{$method}();

            if ($subKey && is_array($data)) {
                $data = $data[$subKey] ?? null;
            }

            return [
                'success' => true,
                'module' => $task['module'],
                'label' => $task['label'],
                'data' => $data,
            ];
        } catch (Throwable $e) {
            $this->logDebug('Task failed', [
                'module' => $task['module'],
                'handler' => $handlerName,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'module' => $task['module'],
                'label' => $task['label'],
                'error' => $e->getMessage(),
                'data' => null,
            ];
        }
    }

    private function aggregateResults(array $results, array $plan): array
    {
        $sections = [];
        $successful = 0;
        $failed = 0;

        foreach ($results as $result) {
            if ($result['success']) {
                $successful++;
                $sections[$result['module']] = [
                    'label' => $result['label'],
                    'status' => 'ok',
                    'data' => $result['data'],
                ];
            } else {
                $failed++;
                $sections[$result['module']] = [
                    'label' => $result['label'],
                    'status' => 'unavailable',
                    'error' => $result['error'],
                ];
            }
        }

        return [
            'success' => $successful > 0,
            'type' => $plan['type'] ?? 'single',
            'intent' => $plan['intent'] ?? 'unknown',
            'description' => $plan['description'] ?? '',
            'sections' => $sections,
            'stats' => [
                'total' => count($results),
                'successful' => $successful,
                'failed' => $failed,
            ],
        ];
    }

    private function buildEmptyResult(array $plan): array
    {
        return [
            'success' => false,
            'type' => $plan['type'] ?? 'single',
            'intent' => $plan['intent'] ?? 'unknown',
            'description' => $plan['description'] ?? '',
            'sections' => [],
            'stats' => [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
            ],
        ];
    }

    public function getHandler(string $name): ?object
    {
        return $this->handlers[$name] ?? null;
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (!app()->environment('local', 'development')) {
            return;
        }

        Log::channel('daily')->debug("[AI Orchestrator] {$message}", $context);
    }
}
