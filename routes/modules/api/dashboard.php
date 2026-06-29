<?php

// ────────────────────────────────────────────────────────────────────────────
// Dashboard — api.v1.dashboard.*
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\DashboardApiController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard/stats', [DashboardApiController::class, 'stats'])
    ->middleware('permission:dashboard.view')->name('dashboard.stats');
Route::get('dashboard/activity', [DashboardApiController::class, 'recentActivity'])
    ->middleware('permission:dashboard.view')->name('dashboard.activity');
Route::get('dashboard/notifications', [DashboardApiController::class, 'notifications'])
    ->middleware('permission:dashboard.view')->name('dashboard.notifications');
