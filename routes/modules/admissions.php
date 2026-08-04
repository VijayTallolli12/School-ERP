<?php

use App\Modules\Admissions\Controllers\AdmissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('admissions')
    ->name('admissions.')
    ->middleware('permission:admissions.view')
    ->group(function (): void {
        Route::get('/', [AdmissionController::class, 'index'])->name('index');
        Route::get('data', [AdmissionController::class, 'data'])->name('data');
        Route::get('search-students', [AdmissionController::class, 'searchStudents'])->name('search-students');
        Route::get('create', [AdmissionController::class, 'create'])->middleware('permission:admissions.create')->name('create');
        Route::post('/', [AdmissionController::class, 'store'])->middleware('permission:admissions.create')->name('store');
        Route::get('{admission}/print', [AdmissionController::class, 'print'])->name('print');
        Route::get('{admission}', [AdmissionController::class, 'show'])->name('show');
        Route::get('{admission}/edit', [AdmissionController::class, 'edit'])->middleware('permission:admissions.update')->name('edit');
        Route::put('{admission}', [AdmissionController::class, 'update'])->middleware('permission:admissions.update')->name('update');
        Route::post('{admission}/verify', [AdmissionController::class, 'verify'])->middleware('permission:admissions.verify')->name('verify');
        Route::post('{admission}/approve', [AdmissionController::class, 'approve'])->middleware('permission:admissions.approve')->name('approve');
        Route::post('{admission}/reject', [AdmissionController::class, 'reject'])->middleware('permission:admissions.reject')->name('reject');
        Route::post('{admission}/convert', [AdmissionController::class, 'convert'])->middleware('permission:admissions.convert')->name('convert');
        Route::post('{admission}/documents', [AdmissionController::class, 'addDocument'])->middleware('permission:admissions.update')->name('documents.store');
        Route::post('{admission}/documents/{document}/verify', [AdmissionController::class, 'verifyDocument'])->middleware('permission:admissions.verify')->name('documents.verify');
        Route::delete('{admission}/documents/{document}', [AdmissionController::class, 'deleteDocument'])->middleware('permission:admissions.delete')->name('documents.destroy');
        Route::delete('{admission}', [AdmissionController::class, 'destroy'])->middleware('permission:admissions.delete')->name('destroy');
    });
