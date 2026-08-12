<?php

use App\Modules\Parents\Controllers\ParentController;
use Illuminate\Support\Facades\Route;

Route::prefix('parents')
    ->name('parents.')
    ->middleware('permission:parents.view')
    ->group(function (): void {
        Route::get('/', [ParentController::class, 'index'])->name('index');
        Route::get('data', [ParentController::class, 'data'])->name('data');
        Route::post('/', [ParentController::class, 'store'])->middleware('permission:parents.create')->name('store');
        Route::get('{parent}', [ParentController::class, 'show'])->name('show');
        Route::put('{parent}', [ParentController::class, 'update'])->middleware('permission:parents.update')->name('update');
        Route::delete('{parent}', [ParentController::class, 'destroy'])->middleware('permission:parents.delete')->name('destroy');
    });