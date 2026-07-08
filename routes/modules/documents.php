<?php

use App\Modules\Documents\Controllers\DocumentController;
use App\Modules\Documents\Controllers\TeacherDocumentController;
use Illuminate\Support\Facades\Route;

// Student Documents (admin/management routes)
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

// Teacher Self-Service Documents (own employment documents)
Route::prefix('teacher-documents')->name('teacher-documents.')->group(function () {
    Route::get('/', [TeacherDocumentController::class, 'index'])->name('index');
    Route::get('/data', [TeacherDocumentController::class, 'data'])->name('data');
    Route::get('/{document}/download', [TeacherDocumentController::class, 'download'])->name('download');
});
