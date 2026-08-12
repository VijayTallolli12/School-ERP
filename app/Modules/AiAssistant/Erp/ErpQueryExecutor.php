<?php

namespace App\Modules\AiAssistant\Erp;

use App\Core\Tenant\SchoolContext;
use App\Modules\AiAssistant\Handlers\AttendanceQueryHandler;
use App\Modules\AiAssistant\Handlers\ExamQueryHandler;
use App\Modules\AiAssistant\Handlers\FeeQueryHandler;
use App\Modules\AiAssistant\Handlers\HomeworkQueryHandler;
use App\Modules\AiAssistant\Handlers\LeaveQueryHandler;
use App\Modules\AiAssistant\Handlers\LibraryQueryHandler;
use App\Modules\AiAssistant\Handlers\PayrollQueryHandler;
use App\Modules\AiAssistant\Handlers\SchoolSummaryHandler;
use App\Modules\AiAssistant\Handlers\StudentQueryHandler;
use App\Modules\AiAssistant\Handlers\TeacherQueryHandler;
use App\Modules\AiAssistant\Handlers\TransportQueryHandler;
use App\Modules\AiAssistant\Services\RoleDataScoper;
use Illuminate\Support\Facades\Log;

/**
 * Validates a structured query, applies tenant + role scoping, dispatches to
 * the ERP domain handler and returns a validated result envelope.
 *
 *   {
 *     "success": true,
 *     "tool": "exam.search",
 *     "result_type": "list",
 *     "count": 1,
 *     "records": [...],
 *     "summary": null,
 *     "filters": {...}
 *   }
 */
class ErpQueryExecutor
{
    private const HANDLER_MAP = [
        'StudentQueryHandler' => StudentQueryHandler::class,
        'AttendanceQueryHandler' => AttendanceQueryHandler::class,
        'FeeQueryHandler' => FeeQueryHandler::class,
        'TransportQueryHandler' => TransportQueryHandler::class,
        'LibraryQueryHandler' => LibraryQueryHandler::class,
        'PayrollQueryHandler' => PayrollQueryHandler::class,
        'HomeworkQueryHandler' => HomeworkQueryHandler::class,
        'SchoolSummaryHandler' => SchoolSummaryHandler::class,
        'ExamQueryHandler' => ExamQueryHandler::class,
        'TeacherQueryHandler' => TeacherQueryHandler::class,
        'LeaveQueryHandler' => LeaveQueryHandler::class,
    ];

    public function __construct(
        private readonly ErpToolRegistry $registry,
        private readonly SchoolContext $schoolContext,
        private readonly RoleDataScoper $roleScoper,
    ) {}

    /**
     * @return array{success: bool, tool: string, result_type: string, count: int, records: array, summary: mixed, filters: array, error?: string}
     */
    public function execute(string $tool, array $parameters): array
    {
        $config = $this->registry->get($tool);

        if (!$config) {
            return $this->error($tool, 'tool_not_found', 'The requested operation is not supported.');
        }

        if (empty($config['result_type'])) {
            return $this->error($tool, 'action_not_supported', 'This operation requires explicit confirmation before execution.');
        }

        // 1. Tenant boundary is enforced by SchoolContext inside each handler;
        //    additionally drop any caller-supplied school id just in case.
        unset($parameters['school_id'], $parameters['school']);

        // 2. Resolve entity names (class, subject) to IDs BEFORE whitelisting,
        //    because the resolver consumes natural-language values (e.g. "Class 3").
        $parameters = $this->resolveEntities($tool, $parameters);

        // 3. Whitelist parameters to the tool schema (drops raw entity names).
        $allowed = $config['params'] ?? [];
        $parameters = array_intersect_key($parameters, array_flip($allowed));

        // 4. Apply role scope filters (class_section_ids, student_ids, teacher_id...).
        $parameters = $this->applyRoleScope($parameters);

        $handler = $this->resolveHandler($config['handler'] ?? null);
        $method = $config['method'] ?? null;

        if (!$handler || !$method || !method_exists($handler, $method)) {
            return $this->error($tool, 'handler_missing', 'The data service for this operation is not available.');
        }

        try {
            $start = microtime(true);
            $result = $handler->{$method}($parameters);
            $elapsed = round((microtime(true) - $start) * 1000, 1);

            if (!is_array($result)) {
                $result = ['records' => $result];
            }

            $result = $this->normalizeResult($tool, $config, $parameters, $result);

            Log::debug('[AI Executor] query executed', [
                'tool' => $tool,
                'school_id' => $this->schoolContext->id(),
                'duration_ms' => $elapsed,
                'count' => $result['count'],
            ]);

            return $result;
        } catch (\Throwable $e) {
            Log::error('[AI Executor] query failed', [
                'tool' => $tool,
                'school_id' => $this->schoolContext->id(),
                'error' => $e->getMessage(),
            ]);

            return $this->error($tool, 'execution_failed', 'An error occurred while fetching the data.');
        }
    }

    private function applyRoleScope(array $parameters): array
    {
        $user = auth()->user();

        if (!$user) {
            return $parameters;
        }

        $filters = $this->roleScoper->getScopeFilters($user);

        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '' && !array_key_exists($key, $parameters)) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    private function resolveEntities(string $tool, array $parameters): array
    {
        $config = $this->registry->get($tool);
        $allowed = $config['params'] ?? [];

        if (!in_array('class_section_id', $allowed, true)) {
            unset($parameters['class'], $parameters['section']);
        }

        if (empty($parameters['class_section_id'])) {
            $resolver = app(\App\Modules\AiAssistant\Services\ParameterResolver::class);
            $parameters = $resolver->resolve($parameters);
        }

        return $parameters;
    }

    private function normalizeResult(string $tool, array $config, array $parameters, array $result): array
    {
        $resultType = $config['result_type'] ?? 'list';

        // Handler returned a raw domain payload (e.g. SchoolSummaryHandler) —
        // wrap the whole payload as the summary and derive a count.
        $isRawPayload = !array_key_exists('records', $result)
            && !array_key_exists('summary', $result)
            && !array_key_exists('count', $result);

        if ($isRawPayload) {
            $count = isset($result['total']) && is_numeric($result['total'])
                ? (int) $result['total']
                : count($result);

            return [
                'success' => true,
                'tool' => $tool,
                'result_type' => $resultType,
                'count' => $count,
                'records' => [],
                'summary' => $result,
                'filters' => $parameters,
                'message' => null,
            ];
        }

        $normalized = [
            'success' => true,
            'tool' => $tool,
            'result_type' => $resultType,
            'count' => (int) ($result['count'] ?? 0),
            'records' => $result['records'] ?? [],
            'summary' => $result['summary'] ?? null,
            'filters' => $parameters,
            'message' => $result['message'] ?? null,
        ];

        // For count tools the count may be the primary value.
        if ($resultType === 'count' && isset($result['count'])) {
            $normalized['count'] = (int) $result['count'];
        }

        if ($resultType === 'single' && isset($result['record'])) {
            $normalized['records'] = $result['record'] ? [$result['record']] : [];
            $normalized['count'] = $result['record'] ? 1 : 0;
        }

        return $normalized;
    }

    private function resolveHandler(?string $name): ?object
    {
        if (!$name || !isset(self::HANDLER_MAP[$name])) {
            return null;
        }

        return app(self::HANDLER_MAP[$name]);
    }

    /**
     * Validate every registered query tool structurally and against its handler.
     *
     * @return array<int, string> list of validation problems (empty when valid)
     */
    public function validateTools(): array
    {
        $problems = [];

        foreach ($this->registry->all() as $tool => $config) {
            if (empty($config['description'])) {
                $problems[] = "{$tool}: missing description";
            }
            if (!isset($config['params']) || !is_array($config['params'])) {
                $problems[] = "{$tool}: params must be an array";
            }
            if (empty($config['result_type'])) {
                $problems[] = "{$tool}: missing result_type";
            }
            if (!in_array($config['result_type'] ?? null, ['list', 'count', 'single', 'summary'], true)) {
                $rt = $config['result_type'] ?? '';
                $problems[] = "{$tool}: invalid result_type '{$rt}'";
            }
            if (empty($config['handler']) || !isset(self::HANDLER_MAP[$config['handler']])) {
                $handler = $config['handler'] ?? '';
                $problems[] = "{$tool}: unknown handler '{$handler}'";
                continue;
            }
            if (empty($config['method'])) {
                $problems[] = "{$tool}: missing method";
                continue;
            }
            $class = self::HANDLER_MAP[$config['handler']];
            if (!method_exists($class, $config['method'])) {
                $problems[] = "{$tool}: method '{$config['method']}' not found on {$class}";
            }
        }

        return $problems;
    }

    public static function getHandlerMap(): array
    {
        return self::HANDLER_MAP;
    }

    private function error(string $tool, string $code, string $message): array
    {
        return [
            'success' => false,
            'tool' => $tool,
            'result_type' => 'error',
            'count' => 0,
            'records' => [],
            'summary' => null,
            'filters' => [],
            'error_code' => $code,
            'error' => $message,
        ];
    }
}
