<?php

use App\Modules\HR\Controllers\EmployeeController;
use App\Modules\HR\Controllers\EmployeeDocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('hr')
    ->name('hr.')
    ->middleware('permission:hr.view')
    ->group(function (): void {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('data', [EmployeeController::class, 'data'])->name('data');
        Route::post('/', [EmployeeController::class, 'store'])->middleware('permission:hr.create')->name('store');

        // Documents
        Route::get('documents', [EmployeeDocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/data', [EmployeeDocumentController::class, 'data'])->name('documents.data');
        Route::post('documents', [EmployeeDocumentController::class, 'store'])->middleware('permission:hr.create')->name('documents.store');
        Route::get('documents/{document}', [EmployeeDocumentController::class, 'show'])->name('documents.show');
        Route::put('documents/{document}', [EmployeeDocumentController::class, 'update'])->middleware('permission:hr.update')->name('documents.update');
        Route::delete('documents/{document}', [EmployeeDocumentController::class, 'destroy'])->middleware('permission:hr.delete')->name('documents.destroy');
        Route::post('documents/{document}/verify', [EmployeeDocumentController::class, 'verify'])->middleware('permission:hr.verify')->name('documents.verify');

        // Wildcard {employee} routes must come after all static sub-routes
        Route::get('{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::put('{employee}', [EmployeeController::class, 'update'])->middleware('permission:hr.update')->name('update');
        Route::delete('{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:hr.delete')->name('destroy');
    });
