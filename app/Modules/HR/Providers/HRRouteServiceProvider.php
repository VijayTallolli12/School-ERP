<?php

namespace App\Modules\HR\Providers;

use Illuminate\Support\ServiceProvider;

class HRRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../../routes/modules/hr.php');
    }
}
