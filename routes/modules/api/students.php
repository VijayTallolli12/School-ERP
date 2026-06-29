<?php

// ────────────────────────────────────────────────────────────────────────────
// Students — api.v1.students.*
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\StudentApiController;
use Illuminate\Support\Facades\Route;

Route::get('students', [StudentApiController::class, 'index'])
    ->middleware('permission:students.view')->name('students.index');
Route::get('students/{uuid}', [StudentApiController::class, 'show'])
    ->middleware('permission:students.view')->name('students.show');
Route::get('students/{uuid}/attendance', [StudentApiController::class, 'attendanceSummary'])
    ->middleware('permission:attendance.view')->name('students.attendance');
Route::get('students/{uuid}/fees', [StudentApiController::class, 'feesSummary'])
    ->middleware('permission:fees.view')->name('students.fees');
Route::get('students/{uuid}/exams', [StudentApiController::class, 'examSummary'])
    ->middleware('permission:exams.view')->name('students.exams');
Route::get('students/{uuid}/timetable', [StudentApiController::class, 'timetable'])
    ->middleware('permission:timetable.view')->name('students.timetable');
