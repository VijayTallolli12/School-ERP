<?php

use App\Modules\Teachers\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

// Teacher self-service routes (no `teachers.view` required — teachers may manage their own)
Route::prefix('teachers')
    ->name('teachers.')
    ->middleware('role_or_permission:Teacher|teachers.view')
    ->group(function (): void {
        Route::get('my-leaves', [TeacherController::class, 'myLeaveIndex'])->name('my-leaves.index');
        Route::get('my-leaves/data', [TeacherController::class, 'myLeaveData'])->name('my-leaves.data');
        Route::post('my-leaves', [TeacherController::class, 'myLeaveStore'])->name('my-leaves.store');
        Route::get('my-attendance', [TeacherController::class, 'myAttendanceIndex'])->name('my-attendance.index');
        Route::get('my-attendance/data', [TeacherController::class, 'myAttendanceData'])->name('my-attendance.data');
        Route::get('my-profile', [TeacherController::class, 'myProfile'])->name('my-profile');
        Route::put('my-profile', [TeacherController::class, 'myProfileUpdate'])->name('my-profile.update');
    });

Route::prefix('teachers')
    ->name('teachers.')
    ->middleware('permission:teachers.view')
    ->group(function (): void {
        Route::get('/', [TeacherController::class, 'index'])->name('index');
        Route::get('data', [TeacherController::class, 'data'])->name('data');
        Route::get('search', [TeacherController::class, 'search'])->name('search');
        Route::post('/', [TeacherController::class, 'store'])->middleware('permission:teachers.create')->name('store');

        // Static sub-routes must be defined BEFORE wildcard {teacher} routes
        Route::get('attendance', [TeacherController::class, 'attendanceIndex'])->name('attendance.index');
        Route::get('attendance/data', [TeacherController::class, 'attendanceData'])->name('attendance.data');
        Route::post('attendance', [TeacherController::class, 'attendanceStore'])->middleware('permission:teachers.create')->name('attendance.store');
        Route::get('attendance/{attendance}', [TeacherController::class, 'attendanceShow'])->name('attendance.show');
        Route::put('attendance/{attendance}', [TeacherController::class, 'attendanceUpdate'])->middleware('permission:teachers.update')->name('attendance.update');
        Route::delete('attendance/{attendance}', [TeacherController::class, 'attendanceDestroy'])->middleware('permission:teachers.delete')->name('attendance.destroy');

        Route::get('leaves', [TeacherController::class, 'leaveIndex'])->name('leaves.index');
        Route::get('leaves/data', [TeacherController::class, 'leaveData'])->name('leaves.data');
        Route::post('leaves', [TeacherController::class, 'leaveStore'])->middleware('permission:teachers.create')->name('leaves.store');
        Route::get('leaves/{leave}', [TeacherController::class, 'leaveShow'])->name('leaves.show');
        Route::put('leaves/{leave}', [TeacherController::class, 'leaveUpdate'])->middleware('permission:teachers.update')->name('leaves.update');
        Route::delete('leaves/{leave}', [TeacherController::class, 'leaveDestroy'])->middleware('permission:teachers.delete')->name('leaves.destroy');

        Route::get('reports/subjects', [TeacherController::class, 'subjectAllocationReport'])->middleware('permission:teachers.reports')->name('reports.subjects');
        Route::get('reports/attendance', [TeacherController::class, 'attendanceReport'])->middleware('permission:teachers.reports')->name('reports.attendance');

        // Wildcard {teacher} routes MUST come AFTER all static sub-routes
        Route::get('{teacher}', [TeacherController::class, 'show'])->name('show');
        Route::put('{teacher}', [TeacherController::class, 'update'])->middleware('permission:teachers.update')->name('update');
        Route::delete('{teacher}', [TeacherController::class, 'destroy'])->middleware('permission:teachers.delete')->name('destroy');
    });
