<?php

namespace App\Modules\MobileApps\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MobileAppsController extends Controller
{
    public function index(): View
    {
        return view('modules.mobile-apps.index', [
            'apps' => config('mobile_apps.apps', []),
        ]);
    }
}
