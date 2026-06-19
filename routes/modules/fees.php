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

        // Legacy fee report routes — permanently redirect to Reports module
        Route::permanentRedirect('reports/collection', '/reports/fees/paid')->name('reports.collection');
        Route::permanentRedirect('reports/collection/pdf', '/reports/fees/paid')->name('reports.collection.pdf');
        Route::permanentRedirect('reports/due', '/reports/fees/pending')->name('reports.due');
        Route::permanentRedirect('reports/due/pdf', '/reports/fees/pending')->name('reports.due.pdf');
        Route::permanentRedirect('reports/class-wise', '/reports/fees/collection-summary')->name('reports.class-wise');
        Route::permanentRedirect('reports/class-wise/pdf', '/reports/fees/collection-summary')->name('reports.class-wise.pdf');
        Route::permanentRedirect('reports/daily', '/reports/fees/paid')->name('reports.daily');
        Route::permanentRedirect('reports/daily/pdf', '/reports/fees/paid')->name('reports.daily.pdf');
    });
