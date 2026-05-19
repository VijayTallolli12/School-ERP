<?php

use App\Modules\Academics\Controllers\AcademicController;
use Illuminate\Support\Facades\Route;

Route::prefix('academics')
    ->name('academics.')
    ->middleware('permission:academics.view')
    ->group(function (): void {
        Route::get('/', [AcademicController::class, 'index'])->name('index');

        Route::get('academic-years/data', [AcademicController::class, 'academicYearsData'])->name('academic-years.data');
        Route::post('academic-years', [AcademicController::class, 'storeAcademicYear'])->middleware('permission:academics.create')->name('academic-years.store');
        Route::get('academic-years/{academicYear}', [AcademicController::class, 'showAcademicYear'])->name('academic-years.show');
        Route::put('academic-years/{academicYear}', [AcademicController::class, 'updateAcademicYear'])->middleware('permission:academics.update')->name('academic-years.update');
        Route::delete('academic-years/{academicYear}', [AcademicController::class, 'destroyAcademicYear'])->middleware('permission:academics.delete')->name('academic-years.destroy');

        Route::get('classes/data', [AcademicController::class, 'classesData'])->name('classes.data');
        Route::post('classes', [AcademicController::class, 'storeClass'])->middleware('permission:academics.create')->name('classes.store');
        Route::get('classes/{class}', [AcademicController::class, 'showClass'])->name('classes.show');
        Route::put('classes/{class}', [AcademicController::class, 'updateClass'])->middleware('permission:academics.update')->name('classes.update');
        Route::delete('classes/{class}', [AcademicController::class, 'destroyClass'])->middleware('permission:academics.delete')->name('classes.destroy');

        Route::get('sections/data', [AcademicController::class, 'sectionsData'])->name('sections.data');
        Route::post('sections', [AcademicController::class, 'storeSection'])->middleware('permission:academics.create')->name('sections.store');
        Route::get('sections/{section}', [AcademicController::class, 'showSection'])->name('sections.show');
        Route::put('sections/{section}', [AcademicController::class, 'updateSection'])->middleware('permission:academics.update')->name('sections.update');
        Route::delete('sections/{section}', [AcademicController::class, 'destroySection'])->middleware('permission:academics.delete')->name('sections.destroy');

        Route::get('subjects/data', [AcademicController::class, 'subjectsData'])->name('subjects.data');
        Route::post('subjects', [AcademicController::class, 'storeSubject'])->middleware('permission:academics.create')->name('subjects.store');
        Route::get('subjects/{subject}', [AcademicController::class, 'showSubject'])->name('subjects.show');
        Route::put('subjects/{subject}', [AcademicController::class, 'updateSubject'])->middleware('permission:academics.update')->name('subjects.update');
        Route::delete('subjects/{subject}', [AcademicController::class, 'destroySubject'])->middleware('permission:academics.delete')->name('subjects.destroy');

        Route::get('class-sections/data', [AcademicController::class, 'classSectionsData'])->name('class-sections.data');
        Route::post('class-sections', [AcademicController::class, 'storeClassSection'])->middleware('permission:academics.create')->name('class-sections.store');
        Route::get('class-sections/{classSection}', [AcademicController::class, 'showClassSection'])->name('class-sections.show');
        Route::put('class-sections/{classSection}', [AcademicController::class, 'updateClassSection'])->middleware('permission:academics.update')->name('class-sections.update');
        Route::delete('class-sections/{classSection}', [AcademicController::class, 'destroyClassSection'])->middleware('permission:academics.delete')->name('class-sections.destroy');

        Route::get('class-subjects/data', [AcademicController::class, 'classSubjectsData'])->name('class-subjects.data');
        Route::post('class-subjects', [AcademicController::class, 'assignSubject'])->middleware('permission:academics.create')->name('class-subjects.store');
        Route::get('class-subjects/{classSubject}', [AcademicController::class, 'showClassSubject'])->name('class-subjects.show');
        Route::put('class-subjects/{classSubject}', [AcademicController::class, 'updateClassSubject'])->middleware('permission:academics.update')->name('class-subjects.update');
        Route::delete('class-subjects/{classSubject}', [AcademicController::class, 'destroyClassSubject'])->middleware('permission:academics.delete')->name('class-subjects.destroy');
    });
