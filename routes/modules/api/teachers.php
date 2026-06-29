<?php

// ────────────────────────────────────────────────────────────────────────────
// Teachers — api.v1.teachers.*
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\TeacherApiController;
use Illuminate\Support\Facades\Route;

Route::get('teachers', [TeacherApiController::class, 'index'])
    ->middleware('permission:teachers.view')->name('teachers.index');
Route::get('teachers/{uuid}', [TeacherApiController::class, 'show'])
    ->middleware('permission:teachers.view')->name('teachers.show');
Route::get('teachers/{uuid}/timetable', [TeacherApiController::class, 'timetable'])
    ->middleware('permission:timetable.view')->name('teachers.timetable');
Route::get('teachers/{uuid}/attendance', [TeacherApiController::class, 'attendance'])
    ->middleware('permission:attendance.view')->name('teachers.attendance');
Route::get('teachers/{uuid}/classes', [TeacherApiController::class, 'assignedClasses'])
    ->middleware('permission:teachers.view')->name('teachers.classes');
Route::get('teachers/{uuid}/subjects', [TeacherApiController::class, 'assignedSubjects'])
    ->middleware('permission:teachers.view')->name('teachers.subjects');
