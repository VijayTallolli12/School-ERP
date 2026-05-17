<?php

use App\Modules\Students\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('students')
    ->name('students.')
    ->middleware('permission:students.view')
    ->group(function (): void {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('data', [StudentController::class, 'data'])->name('data');
        Route::post('/', [StudentController::class, 'store'])->middleware('permission:students.create')->name('store');
        Route::get('{student}', [StudentController::class, 'show'])->name('show');
        Route::put('{student}', [StudentController::class, 'update'])->middleware('permission:students.update')->name('update');
        Route::delete('{student}', [StudentController::class, 'destroy'])->middleware('permission:students.delete')->name('destroy');
    });
