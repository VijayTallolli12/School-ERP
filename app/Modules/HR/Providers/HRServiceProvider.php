<?php

namespace App\Modules\HR\Providers;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\EmployeeDocument;
use App\Modules\HR\Policies\EmployeeDocumentPolicy;
use App\Modules\HR\Policies\EmployeePolicy;
use App\Modules\HR\Repositories\EmployeeRepository;
use App\Modules\HR\Repositories\EmployeeRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class HRServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(EmployeeDocument::class, EmployeeDocumentPolicy::class);

        $this->loadRoutesFrom(__DIR__.'/../../../routes/modules/hr.php');
        $this->loadViewsFrom(__DIR__.'/../../../resources/views/modules/hr', 'hr');
    }
}
