<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    $registry = $app->make('App\Modules\AiAgents\Registry\AgentRegistry');
    $executions = $app->make('App\Modules\AiAgents\Models\AgentExecution');
    
    $agents = $registry->definitions();
    $stats = $executions::query()
        ->selectRaw('agent_name, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as success_count, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failure_count, MAX(completed_at) as last_run, SUM(records_processed) as total_records', ['completed', 'failed'])
        ->groupBy('agent_name')
        ->get()
        ->keyBy('agent_name');
    
    echo "View data prepared OK" . PHP_EOL;
    echo "Agents: " . count($agents) . PHP_EOL;
    echo "Stats: " . count($stats) . PHP_EOL;
    foreach ($agents as $name => $agent) {
        echo "  Agent: {$name}" . PHP_EOL;
        echo "  Config: " . json_encode($agent['config']) . PHP_EOL;
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
