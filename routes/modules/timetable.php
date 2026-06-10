<?php

use App\Modules\Timetable\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

Route::prefix('timetable')
    ->name('timetable.')
    ->middleware('permission:timetable.view')
    ->group(function (): void {
        Route::get('/', [TimetableController::class, 'index'])->name('index');
        Route::get('data', [TimetableController::class, 'data'])->name('data');
        Route::get('class-schedule', [TimetableController::class, 'classSchedule'])->name('class-schedule');
        Route::get('teacher-schedule', [TimetableController::class, 'teacherSchedule'])->name('teacher-schedule');
        Route::get('class-schedule/print', [TimetableController::class, 'printClassSchedule'])->name('print.class');
        Route::get('teacher-schedule/print', [TimetableController::class, 'printTeacherSchedule'])->name('print.teacher');

        Route::post('duplicate-day', [TimetableController::class, 'duplicateDay'])->middleware('permission:timetable.create')->name('duplicate-day');
        Route::post('copy-class', [TimetableController::class, 'copyClass'])->middleware('permission:timetable.create')->name('copy-class');

        Route::post('/', [TimetableController::class, 'store'])->middleware('permission:timetable.create')->name('store');
        Route::get('{timetableSlot}', [TimetableController::class, 'show'])->name('show');
        Route::put('{timetableSlot}', [TimetableController::class, 'update'])->middleware('permission:timetable.update')->name('update');
        Route::delete('{timetableSlot}', [TimetableController::class, 'destroy'])->middleware('permission:timetable.delete')->name('destroy');
    });
