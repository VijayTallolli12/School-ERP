<?php

use App\Modules\Dashboard\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', DashboardController::class)
    ->middleware('permission:dashboard.view')
    ->name('dashboard');
