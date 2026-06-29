<?php

namespace App\Providers;

use App\Modules\AiAgents\Agents\AttendanceAgent;
use App\Modules\AiAgents\Agents\FeeCollectionAgent;
use App\Modules\AiAgents\Agents\LibraryAgent;
use App\Modules\AiAgents\Agents\PayrollAgent;
use App\Modules\AiAgents\Registry\AgentRegistry;
use Illuminate\Support\ServiceProvider;

class AiAgentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AgentRegistry::class, function () {
            return new AgentRegistry();
        });
    }

    public function boot(): void
    {
        $registry = $this->app->make(AgentRegistry::class);

        $registry->register(
            $this->app->make(FeeCollectionAgent::class)
        );

        $registry->register(
            $this->app->make(AttendanceAgent::class)
        );

        $registry->register(
            $this->app->make(LibraryAgent::class)
        );

        $registry->register(
            $this->app->make(PayrollAgent::class)
        );
    }
}
