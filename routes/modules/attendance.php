<?php

use App\Modules\Attendance\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('attendance')
    ->as('attendance.')
    ->group(function () {
        Route::middleware(['permission:attendance.reports'])->group(function () {
            Route::get('reports/export-excel', [AttendanceController::class, 'exportExcel'])->name('export.excel');
            Route::get('reports/export-pdf', [AttendanceController::class, 'exportPdf'])->name('export.pdf');
            Route::get('reports/print', [AttendanceController::class, 'printReport'])->name('print');
        });

        Route::middleware(['permission:attendance.view'])->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('data', [AttendanceController::class, 'data'])->name('data');
            Route::get('statistics', [AttendanceController::class, 'statistics'])->name('statistics');
            Route::get('class-sections/{classSection}/students', [AttendanceController::class, 'getStudentsByClassSection'])->name('students');
            Route::get('class-sections/{classSection}/monthly-report', [AttendanceController::class, 'monthlyReport'])->name('monthly-report');
            Route::get('{attendance}', [AttendanceController::class, 'show'])->name('show');
        });

        Route::middleware(['permission:attendance.create'])->group(function () {
            Route::post('/', [AttendanceController::class, 'store'])->name('store');
            Route::post('bulk-mark', [AttendanceController::class, 'bulkMark'])->name('bulk-mark');
        });

        Route::middleware(['permission:attendance.update'])->group(function () {
            Route::put('{attendance}', [AttendanceController::class, 'update'])->name('update');
        });

        Route::middleware(['permission:attendance.delete'])->group(function () {
            Route::delete('{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
        });
    });
