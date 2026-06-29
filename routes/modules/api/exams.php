<?php

// ────────────────────────────────────────────────────────────────────────────
// Exams — api.v1.exams.*
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\ExamApiController;
use Illuminate\Support\Facades\Route;

Route::get('exams', [ExamApiController::class, 'index'])
    ->middleware('permission:exams.view')->name('exams.index');
Route::get('exams/{id}', [ExamApiController::class, 'show'])
    ->middleware('permission:exams.view')->name('exams.show');
Route::get('exams/{examId}/results', [ExamApiController::class, 'results'])
    ->middleware('permission:exams.view')->name('exams.results');
Route::get('exams/{examId}/results/{resultId}', [ExamApiController::class, 'resultDetail'])
    ->middleware('permission:exams.view')->name('exams.result.detail');
Route::get('exams/{examId}/report-card', [ExamApiController::class, 'reportCard'])
    ->middleware('permission:exams.view')->name('exams.report-card');
