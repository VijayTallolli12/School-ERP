<?php

use App\Modules\Leave\Controllers\LeaveRequestController;
use App\Modules\Leave\Controllers\LeaveTypeController;
use Illuminate\Support\Facades\Route;

// Leave Types
Route::prefix('leave-types')
    ->name('leave-types.')
    ->middleware('permission:leave_management.view')
    ->group(function (): void {
        Route::get('/', [LeaveTypeController::class, 'index'])->name('index');
        Route::get('data', [LeaveTypeController::class, 'data'])->name('data');

        Route::post('/', [LeaveTypeController::class, 'store'])->middleware('permission:leave_management.create')->name('store');
        Route::get('{leave_type}', [LeaveTypeController::class, 'show'])->name('show');
        Route::put('{leave_type}', [LeaveTypeController::class, 'update'])->middleware('permission:leave_management.update')->name('update');
        Route::delete('{leave_type}', [LeaveTypeController::class, 'destroy'])->middleware('permission:leave_management.delete')->name('destroy');
    });

// Leave Requests
Route::prefix('leave-requests')
    ->name('leave-requests.')
    ->middleware('permission:leave_management.view')
    ->group(function (): void {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::get('data', [LeaveRequestController::class, 'data'])->name('data');

        Route::post('/', [LeaveRequestController::class, 'store'])->middleware('permission:leave_management.create')->name('store');
        Route::get('{leave_request}', [LeaveRequestController::class, 'show'])->name('show');
        Route::delete('{leave_request}', [LeaveRequestController::class, 'destroy'])->middleware('permission:leave_management.delete')->name('destroy');

        Route::post('{leave_request}/approve', [LeaveRequestController::class, 'approve'])->middleware('permission:leave_management.approve')->name('approve');
        Route::post('{leave_request}/reject', [LeaveRequestController::class, 'reject'])->middleware('permission:leave_management.approve')->name('reject');
    });
