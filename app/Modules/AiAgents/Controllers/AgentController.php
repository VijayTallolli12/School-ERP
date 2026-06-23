<?php

namespace App\Modules\AiAgents\Controllers;

use App\Modules\AiAgents\Models\AgentExecution;
use App\Modules\AiAgents\Registry\AgentRegistry;
use App\Modules\AiAgents\Engine\AgentExecutor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;

class AgentController extends Controller
{
    public function __construct(
        private readonly AgentRegistry $registry,
        private readonly AgentExecutor $executor,
    ) {}

    public function index()
    {
        $agents = $this->registry->definitions();

        $executions = AgentExecution::query()
            ->selectRaw('agent_name, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as success_count, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failure_count, MAX(completed_at) as last_run, SUM(records_processed) as total_records', ['completed', 'failed'])
            ->groupBy('agent_name')
            ->get()
            ->keyBy('agent_name');

        return view('modules.ai-agents.index', compact('agents', 'executions'));
    }

    public function history(): \Illuminate\View\View
    {
        return view('modules.ai-agents.history');
    }

    public function historyData(): JsonResponse
    {
        $query = AgentExecution::query()->with('executor');

        return DataTables::of($query)
            ->addColumn('executor_name', fn (AgentExecution $e) => $e->executor?->name ?? 'System')
            ->addColumn('status_badge', fn (AgentExecution $e) => view('modules.ai-agents.partials._status-badge', ['status' => $e->status])->render())
            ->addColumn('duration', fn (AgentExecution $e) => $e->started_at && $e->completed_at ? $e->started_at->diffInSeconds($e->completed_at) . 's' : '-')
            ->addColumn('actions', fn (AgentExecution $e) => view('modules.ai-agents.partials._execution-actions', ['execution' => $e])->render())
            ->rawColumns(['status_badge', 'actions'])
            ->toJson();
    }

    public function preview(Request $request, string $agent): JsonResponse
    {
        try {
            $this->executor->load($agent);
            $data = $this->executor->preview($request->all());

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function execute(string $agent, Request $request): JsonResponse
    {
        try {
            $this->executor->load($agent);
            $data = $this->executor->execute($request->all());

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Agent execution failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function executionDetail(int $id): JsonResponse
    {
        $execution = AgentExecution::query()->with('executor')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $execution,
        ]);
    }
}
