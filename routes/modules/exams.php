<?php

use App\Modules\Exams\Controllers\ExamController;
use Illuminate\Support\Facades\Route;

Route::prefix('exams')
    ->name('exams.')
    ->middleware('permission:exams.view')
    ->group(function (): void {
        Route::get('/', [ExamController::class, 'index'])->name('index');
        Route::get('data', [ExamController::class, 'data'])->name('data');

        Route::get('results/data', [ExamController::class, 'resultsData'])->name('results.data');
        Route::post('results', [ExamController::class, 'storeResult'])->middleware('permission:exams.update')->name('results.store');
        Route::get('results/{result}', [ExamController::class, 'showResult'])->name('results.show');
        Route::put('results/{result}', [ExamController::class, 'updateResult'])->middleware('permission:exams.update')->name('results.update');
        Route::delete('results/{result}', [ExamController::class, 'destroyResult'])->middleware('permission:exams.delete')->name('results.destroy');

        Route::get('class-sections/{classSection}/students', [ExamController::class, 'getStudentsByClassSection'])->name('students');

        Route::post('/', [ExamController::class, 'store'])->middleware('permission:exams.create')->name('store');
        Route::post('{exam}/publish', [ExamController::class, 'publish'])->middleware('permission:exams.publish')->name('publish');
        Route::get('{exam}', [ExamController::class, 'show'])->name('show');
        Route::put('{exam}', [ExamController::class, 'update'])->middleware('permission:exams.update')->name('update');
        Route::delete('{exam}', [ExamController::class, 'destroy'])->middleware('permission:exams.delete')->name('destroy');
    });
