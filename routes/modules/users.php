<?php

use App\Modules\Users\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')
    ->name('users.')
    ->middleware('permission:users.view')
    ->group(function (): void {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('data', [UserManagementController::class, 'data'])->name('data');
        Route::post('/', [UserManagementController::class, 'store'])->middleware('permission:users.create')->name('store');
        Route::get('{user}', [UserManagementController::class, 'show'])->name('show');
        Route::put('{user}', [UserManagementController::class, 'update'])->middleware('permission:users.update')->name('update');
        Route::delete('{user}', [UserManagementController::class, 'destroy'])->middleware('permission:users.delete')->name('destroy');

        Route::put('{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->middleware('permission:users.update')->name('toggle-status');
        Route::put('{user}/reset-password', [UserManagementController::class, 'resetPassword'])->middleware('permission:users.update')->name('reset-password');
        Route::put('{user}/assign-role', [UserManagementController::class, 'assignRole'])->middleware('permission:users.update')->name('assign-role');
    });