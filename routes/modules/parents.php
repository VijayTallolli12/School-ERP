<?php

use App\Modules\Parents\Controllers\ParentController;
use Illuminate\Support\Facades\Route;

Route::prefix('parents')
    ->name('parents.')
    ->middleware('permission:parents.view')
    ->group(function (): void {
        Route::get('/', [ParentController::class, 'index'])->name('index');
        Route::get('data', [ParentController::class, 'data'])->name('data');
        Route::post('/', [ParentController::class, 'store'])->middleware('permission:parents.create')->name('store');
        Route::get('{parent}', [ParentController::class, 'show'])->name('show');
        Route::put('{parent}', [ParentController::class, 'update'])->middleware('permission:parents.update')->name('update');
        Route::delete('{parent}', [ParentController::class, 'destroy'])->middleware('permission:parents.delete')->name('destroy');
    });

// Parent Portal Routes (for logged-in parents)
Route::prefix('parent-portal')
    ->name('parent-portal.')
    ->middleware(['auth', 'role:Parent'])
    ->group(function (): void {
        Route::get('/', [ParentController::class, 'dashboard'])->name('dashboard');
        Route::get('attendance', [ParentController::class, 'attendance'])->middleware('permission:attendance.view')->name('attendance');
        Route::get('fees', [ParentController::class, 'fees'])->middleware('permission:fees.view')->name('fees');
        Route::get('exam-results', [ParentController::class, 'examResults'])->middleware('permission:exams.view')->name('exam-results');
        Route::get('timetable', [ParentController::class, 'timetable'])->middleware('permission:timetable.view')->name('timetable');
        Route::get('notifications', [ParentController::class, 'notifications'])->name('notifications');
        Route::get('homework', [ParentController::class, 'homework'])->name('homework');
    });