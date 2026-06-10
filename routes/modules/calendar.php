<?php

use App\Modules\Calendar\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

Route::prefix('calendar')->name('calendar.')->middleware('permission:academic_calendar.view')->group(function () {
    Route::get('/', [CalendarController::class, 'index'])->name('index');
    Route::get('/data', [CalendarController::class, 'data'])->name('data');
    Route::get('/events', [CalendarController::class, 'calendarEvents'])->name('events');
    Route::post('/', [CalendarController::class, 'store'])->middleware('permission:academic_calendar.create')->name('store');
    Route::get('/{id}', [CalendarController::class, 'show'])->name('show');
    Route::put('/{id}', [CalendarController::class, 'update'])->middleware('permission:academic_calendar.update')->name('update');
    Route::delete('/{id}', [CalendarController::class, 'destroy'])->middleware('permission:academic_calendar.delete')->name('destroy');
    Route::patch('/{id}/toggle-publish', [CalendarController::class, 'togglePublish'])->middleware('permission:academic_calendar.publish')->name('toggle-publish');
});
