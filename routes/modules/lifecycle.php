<?php

use App\Modules\Lifecycle\Controllers\StudentLifecycleController;
use Illuminate\Support\Facades\Route;

Route::prefix('lifecycle')
    ->name('lifecycle.')
    ->middleware('permission:student_lifecycle.view')
    ->group(function (): void {
        Route::get('/', [StudentLifecycleController::class, 'index'])->name('index');
        Route::get('data', [StudentLifecycleController::class, 'data'])->name('data');
        Route::get('promotions', [StudentLifecycleController::class, 'promoteIndex'])->middleware('permission:student_lifecycle.promote')->name('promotions');
        Route::post('promotions', [StudentLifecycleController::class, 'promote'])->middleware('permission:student_lifecycle.promote')->name('promotions.store');
        Route::post('transfer', [StudentLifecycleController::class, 'transfer'])->middleware('permission:student_lifecycle.transfer')->name('transfer');
        Route::post('tc', [StudentLifecycleController::class, 'issueTc'])->middleware('permission:student_lifecycle.tc')->name('tc');
        Route::get('tc/{transfer}/print', [StudentLifecycleController::class, 'printTc'])->middleware('permission:student_lifecycle.tc')->name('tc.print');
        Route::get('search-students', [StudentLifecycleController::class, 'searchStudents'])->name('search-students');
    });

Route::prefix('students')
    ->name('students.')
    ->group(function (): void {
        Route::post('{student}/alumni', [StudentLifecycleController::class, 'markAlumni'])
            ->middleware('permission:student_lifecycle.alumni')
            ->name('alumni');
    });
