<?php

use App\Modules\Fees\Controllers\FeesController;
use Illuminate\Support\Facades\Route;

Route::prefix('fees')
    ->as('fees.')
    ->middleware('permission:fees.view')
    ->group(function (): void {
        Route::get('/', [FeesController::class, 'index'])->name('index');

        Route::get('categories/data', [FeesController::class, 'categoriesData'])->name('categories.data');
        Route::post('categories', [FeesController::class, 'storeCategory'])->middleware('permission:fees.create')->name('categories.store');
        Route::get('categories/{fee_category}', [FeesController::class, 'showCategory'])->name('categories.show');
        Route::put('categories/{fee_category}', [FeesController::class, 'updateCategory'])->middleware('permission:fees.update')->name('categories.update');
        Route::delete('categories/{fee_category}', [FeesController::class, 'destroyCategory'])->middleware('permission:fees.delete')->name('categories.destroy');

        Route::get('structures/data', [FeesController::class, 'structuresData'])->name('structures.data');
        Route::post('structures', [FeesController::class, 'storeStructure'])->middleware('permission:fees.create')->name('structures.store');
        Route::get('structures/{fee_structure}', [FeesController::class, 'showStructure'])->name('structures.show');
        Route::put('structures/{fee_structure}', [FeesController::class, 'updateStructure'])->middleware('permission:fees.update')->name('structures.update');
        Route::delete('structures/{fee_structure}', [FeesController::class, 'destroyStructure'])->middleware('permission:fees.delete')->name('structures.destroy');

        Route::get('assignments/data', [FeesController::class, 'assignmentsData'])->name('assignments.data');
        Route::post('assignments', [FeesController::class, 'storeAssignment'])->middleware('permission:fees.create')->name('assignments.store');
        Route::post('assignments/bulk', [FeesController::class, 'bulkAssignments'])->middleware('permission:fees.create')->name('assignments.bulk');
        Route::get('assignments/{student_fee}', [FeesController::class, 'showAssignment'])->name('assignments.show');
        Route::put('assignments/{student_fee}', [FeesController::class, 'updateAssignment'])->middleware('permission:fees.update')->name('assignments.update');
        Route::delete('assignments/{student_fee}', [FeesController::class, 'destroyAssignment'])->middleware('permission:fees.delete')->name('assignments.destroy');

        Route::get('collections/data', [FeesController::class, 'collectionsData'])->name('collections.data');
        Route::post('collections', [FeesController::class, 'storeCollection'])->middleware('permission:fees.collect')->name('collections.store');
        Route::delete('collections/{fee_payment}', [FeesController::class, 'destroyCollection'])->middleware('permission:fees.delete')->name('collections.destroy');

        Route::get('api/student-fee-items', [FeesController::class, 'studentFeeItems'])->middleware('permission:fees.collect')->name('api.student-fee-items');

        Route::get('dues/data', [FeesController::class, 'duesData'])->name('dues.data');

        Route::get('collections/{fee_payment}/receipt', [FeesController::class, 'receiptPrint'])->name('collections.receipt.print');
        Route::get('collections/{fee_payment}/receipt/pdf', [FeesController::class, 'receiptPdf'])->name('collections.receipt.pdf');

        Route::middleware('permission:fees.reports')->group(function (): void {
            Route::get('reports/collection', [FeesController::class, 'reportCollection'])->name('reports.collection');
            Route::get('reports/collection/pdf', [FeesController::class, 'reportCollectionPdf'])->name('reports.collection.pdf');
            Route::get('reports/due', [FeesController::class, 'reportDue'])->name('reports.due');
            Route::get('reports/due/pdf', [FeesController::class, 'reportDuePdf'])->name('reports.due.pdf');
            Route::get('reports/class-wise', [FeesController::class, 'reportClassWise'])->name('reports.class-wise');
            Route::get('reports/class-wise/pdf', [FeesController::class, 'reportClassWisePdf'])->name('reports.class-wise.pdf');
            Route::get('reports/daily', [FeesController::class, 'reportDaily'])->name('reports.daily');
            Route::get('reports/daily/pdf', [FeesController::class, 'reportDailyPdf'])->name('reports.daily.pdf');
        });
    });
