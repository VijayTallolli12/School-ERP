<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    $registry = $app->make('App\Modules\AiAgents\Registry\AgentRegistry');
    $defs = $registry->definitions();
    echo "Registry OK. Agents: " . count($defs) . PHP_EOL;
    foreach ($defs as $name => $def) {
        echo "  - {$name}: {$def['description']}" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
