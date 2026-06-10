<?php

use App\Modules\Documents\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('documents')->name('documents.')->middleware('permission:student_documents.view')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    Route::get('/data', [DocumentController::class, 'data'])->name('data');
    Route::post('/', [DocumentController::class, 'store'])->middleware('permission:student_documents.create')->name('store');
    Route::get('/{id}', [DocumentController::class, 'show'])->name('show');
    Route::post('/{id}', [DocumentController::class, 'update'])->middleware('permission:student_documents.update')->name('update');
    Route::delete('/{id}', [DocumentController::class, 'destroy'])->middleware('permission:student_documents.delete')->name('destroy');
    Route::patch('/{id}/toggle-verify', [DocumentController::class, 'toggleVerify'])->middleware('permission:student_documents.verify')->name('toggle-verify');
    Route::get('/{id}/download', [DocumentController::class, 'download'])->name('download');
});
