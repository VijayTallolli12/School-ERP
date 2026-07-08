<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Services\DashboardService;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboardService)
    {
        $dashboardView = $dashboardService->build(auth()->user());

        return view('modules.dashboard.index', [
            'dashboard' => $dashboardView,
        ]);
    }
}
