<?php

use App\Modules\Homework\Controllers\HomeworkController;
use Illuminate\Support\Facades\Route;

Route::prefix('homework')
    ->name('homework.')
    ->middleware('permission:homework.view')
    ->group(function (): void {
        Route::get('/', [HomeworkController::class, 'index'])->name('index');
        Route::get('data', [HomeworkController::class, 'data'])->name('data');

        Route::post('/', [HomeworkController::class, 'store'])->middleware('permission:homework.create')->name('store');

        // Static sub-routes MUST be defined BEFORE wildcard {homework} routes
        Route::get('subjects/by-class', [HomeworkController::class, 'getSubjectsByClass'])->name('subjects.by-class');

        // Wildcard {homework} routes MUST come AFTER all static sub-routes
        Route::get('{homework}', [HomeworkController::class, 'show'])->name('show');
        Route::post('{homework}', [HomeworkController::class, 'update'])->middleware('permission:homework.update')->name('update');
        Route::delete('{homework}', [HomeworkController::class, 'destroy'])->middleware('permission:homework.delete')->name('destroy');
    });
