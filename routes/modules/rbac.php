<?php

use App\Modules\RBAC\Controllers\PermissionController;
use App\Modules\RBAC\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('roles')
    ->name('roles.')
    ->middleware('permission:roles.view')
    ->group(function (): void {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('data', [RoleController::class, 'data'])->name('data');
        Route::post('/', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('store');
        Route::get('{role}', [RoleController::class, 'show'])->name('show');
        Route::put('{role}', [RoleController::class, 'update'])->middleware('permission:roles.update')->name('update');
        Route::delete('{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete')->name('destroy');
    });

Route::prefix('permissions')
    ->name('permissions.')
    ->middleware('permission:permissions.view')
    ->group(function (): void {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('data', [PermissionController::class, 'data'])->name('data');
        Route::post('/', [PermissionController::class, 'store'])->middleware('permission:permissions.create')->name('store');
        Route::get('{permission}', [PermissionController::class, 'show'])->name('show');
        Route::put('{permission}', [PermissionController::class, 'update'])->middleware('permission:permissions.update')->name('update');
        Route::delete('{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permissions.delete')->name('destroy');
    });
