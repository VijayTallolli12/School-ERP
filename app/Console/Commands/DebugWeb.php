<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Http\Kernel;

class DebugWeb extends Command
{
    protected $signature = 'debug:web';
    protected $description = 'Debug web middleware config';

    public function handle(): int
    {
        $kernel = app(Kernel::class);
        $this->info('Web group middleware:');
        $this->line(json_encode($kernel->getMiddlewareGroups()['web'] ?? [], JSON_PRETTY_PRINT));
        
        $this->info("\nRoute middleware aliases:");
        $routeMiddleware = $kernel->getRouteMiddleware();
        foreach ($routeMiddleware as $name => $class) {
            $this->line("  $name => $class");
        }

        $this->info("\nGlobal middleware:");
        $this->line(json_encode($kernel->getGlobalMiddleware(), JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }
}
