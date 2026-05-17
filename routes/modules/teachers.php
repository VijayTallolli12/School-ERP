<?php

use App\Modules\Teachers\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::prefix('teachers')
    ->name('teachers.')
    ->middleware('permission:teachers.view')
    ->group(function (): void {
        Route::get('/', [TeacherController::class, 'index'])->name('index');
        Route::get('data', [TeacherController::class, 'data'])->name('data');
        Route::post('/', [TeacherController::class, 'store'])->middleware('permission:teachers.create')->name('store');
        Route::get('{teacher}', [TeacherController::class, 'show'])->name('show');
        Route::put('{teacher}', [TeacherController::class, 'update'])->middleware('permission:teachers.update')->name('update');
        Route::delete('{teacher}', [TeacherController::class, 'destroy'])->middleware('permission:teachers.delete')->name('destroy');

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
    });
