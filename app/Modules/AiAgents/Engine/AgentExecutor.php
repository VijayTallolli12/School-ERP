<?php

namespace App\Modules\AiAgents\Engine;

use App\Modules\AiAgents\Agents\AgentInterface;
use App\Modules\AiAgents\Models\AgentExecution;
use App\Modules\AiAgents\Registry\AgentRegistry;

class AgentExecutor
{
    private AgentInterface $agent;

    private AgentExecution $execution;

    public function __construct(
        private readonly AgentRegistry $registry,
    ) {}

    public function load(string $agentName): self
    {
        $this->agent = $this->registry->get($agentName);

        return $this;
    }

    public function validateParams(array $params): array
    {
        return $this->agent->validateParams($params);
    }

    public function preview(array $params): array
    {
        $validated = $this->agent->validateParams($params);

        return $this->agent->preview($validated);
    }

    public function execute(array $params): array
    {
        $validated = $this->agent->validateParams($params);
        $now = now();
        $userId = auth()->id();

        $this->execution = AgentExecution::query()->create([
            'agent_name' => $this->agent->name(),
            'executed_by' => $userId,
            'status' => 'running',
            'started_at' => $now,
            'input_params' => $validated,
        ]);

        try {
            $result = $this->agent->execute($validated);

            $recordsProcessed = $result['records_processed'] ?? ($result['student_count'] ?? count($result['results'] ?? []));

            $this->execution->update([
                'status' => 'completed',
                'completed_at' => now(),
                'records_processed' => $recordsProcessed,
                'result_summary' => $this->buildSummary($result),
                'output_data' => $result,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->event('agent_executed')
                ->withProperties([
                    'agent' => $this->agent->name(),
                    'execution_id' => $this->execution->id,
                    'records_processed' => $recordsProcessed,
                    'status' => 'completed',
                    'executed_at' => $now->toDateTimeString(),
                ])
                ->log("Agent '{$this->agent->name()}' executed: {$recordsProcessed} records processed");

            $result['execution_id'] = $this->execution->id;

            return $result;
        } catch (\Throwable $e) {
            $this->execution->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->event('agent_executed')
                ->withProperties([
                    'agent' => $this->agent->name(),
                    'execution_id' => $this->execution->id,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'executed_at' => $now->toDateTimeString(),
                ])
                ->log("Agent '{$this->agent->name()}' failed: {$e->getMessage()}");

            throw $e;
        }
    }

    private function buildSummary(array $result): string
    {
        $parts = [];

        if (isset($result['student_count'])) {
            $parts[] = "{$result['student_count']} students";
        }
        if (isset($result['total_outstanding'])) {
            $parts[] = '₹' . number_format($result['total_outstanding'], 2) . ' outstanding';
        }
        if (isset($result['notifications_created'])) {
            $parts[] = "{$result['notifications_created']} notifications";
        }
        if (isset($result['records_processed'])) {
            $parts[] = "{$result['records_processed']} records";
        }

        return !empty($parts) ? implode(', ', $parts) : 'Completed';
    }
}
