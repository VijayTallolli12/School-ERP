<?php

use App\Modules\Exams\Controllers\ExamController;
use App\Modules\Exams\Controllers\ExamMarkController;
use App\Modules\Exams\Controllers\ExamScheduleController;
use App\Modules\Exams\Controllers\GradeScaleController;
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

        Route::get('{exam}/results/bulk', [ExamController::class, 'bulkEntry'])->name('results.bulk');
        Route::post('{exam}/results/bulk-save', [ExamController::class, 'bulkSave'])->middleware('permission:exams.update')->name('results.bulk-save');

        Route::get('class-sections/{classSection}/students', [ExamController::class, 'getStudentsByClassSection'])->name('students');

        Route::post('/', [ExamController::class, 'store'])->middleware('permission:exams.create')->name('store');
        Route::post('{exam}/publish', [ExamController::class, 'publish'])->middleware('permission:exams.publish')->name('publish');
        Route::get('{exam}', [ExamController::class, 'show'])->name('show');
        Route::put('{exam}', [ExamController::class, 'update'])->middleware('permission:exams.update')->name('update');
        Route::delete('{exam}', [ExamController::class, 'destroy'])->middleware('permission:exams.delete')->name('destroy');
    });

Route::prefix('grade-scales')
    ->name('grade-scales.')
    ->middleware('permission:exams.view')
    ->group(function (): void {
        Route::get('/', [GradeScaleController::class, 'index'])->name('index');
        Route::get('data', [GradeScaleController::class, 'data'])->name('data');
        Route::post('/', [GradeScaleController::class, 'store'])->middleware('permission:exams.create')->name('store');
        Route::get('{gradeScale}', [GradeScaleController::class, 'show'])->name('show');
        Route::put('{gradeScale}', [GradeScaleController::class, 'update'])->middleware('permission:exams.update')->name('update');
        Route::delete('{gradeScale}', [GradeScaleController::class, 'destroy'])->middleware('permission:exams.delete')->name('destroy');
    });

Route::prefix('exams/{exam}/schedules')
    ->name('exams.schedules.')
    ->middleware('permission:exams.view')
    ->group(function (): void {
        Route::get('/', [ExamScheduleController::class, 'index'])->name('index');
        Route::get('data', [ExamScheduleController::class, 'data'])->name('data');
        Route::post('/', [ExamScheduleController::class, 'store'])->middleware('permission:exams.update')->name('store');
        Route::get('{schedule}', [ExamScheduleController::class, 'show'])->name('show');
        Route::put('{schedule}', [ExamScheduleController::class, 'update'])->middleware('permission:exams.update')->name('update');
        Route::delete('{schedule}', [ExamScheduleController::class, 'destroy'])->middleware('permission:exams.delete')->name('destroy');
    });

Route::prefix('exam-schedules/{schedule}/marks')
    ->name('exams.schedules.marks.')
    ->middleware('permission:exams.view')
    ->group(function (): void {
        Route::get('/', [ExamMarkController::class, 'index'])->name('index');
        Route::get('data', [ExamMarkController::class, 'data'])->name('data');
        Route::post('bulk-save', [ExamMarkController::class, 'bulkSave'])->middleware('permission:exams.update')->name('bulk-save');
        Route::get('{mark}', [ExamMarkController::class, 'show'])->name('show');
        Route::put('{mark}', [ExamMarkController::class, 'update'])->middleware('permission:exams.update')->name('update');
    });
