<?php

// ────────────────────────────────────────────────────────────────────────────
// Attendance — api.v1.attendance.*
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\AttendanceApiController;
use Illuminate\Support\Facades\Route;

Route::get('attendance', [AttendanceApiController::class, 'index'])
    ->middleware('permission:attendance.view')->name('attendance.index');
Route::get('attendance/daily', [AttendanceApiController::class, 'daily'])
    ->middleware('permission:attendance.view')->name('attendance.daily');
Route::get('attendance/monthly', [AttendanceApiController::class, 'monthly'])
    ->middleware('permission:attendance.view')->name('attendance.monthly');
Route::get('attendance/statistics', [AttendanceApiController::class, 'statistics'])
    ->middleware('permission:attendance.view')->name('attendance.statistics');

// Phase 5.4B — Live Attendance real-time status
Route::get('attendance/realtime-status', [\App\Http\Controllers\Api\V1\AttendanceRealtimeController::class, 'status'])
    ->name('attendance.realtime-status');
