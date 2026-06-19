<?php

use App\Modules\Library\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

Route::prefix('library')
    ->name('library.')
    ->middleware('permission:library.view')
    ->group(function (): void {
        Route::get('/', [LibraryController::class, 'index'])->name('index');

        // Search (Select2 AJAX)
        Route::get('search/students', [LibraryController::class, 'searchStudents'])->name('search.students');
        Route::get('search/teachers', [LibraryController::class, 'searchTeachers'])->name('search.teachers');

        // Books
        Route::get('books/data', [LibraryController::class, 'booksData'])->name('books.data');
        Route::post('books', [LibraryController::class, 'storeBook'])->middleware('permission:library.create')->name('books.store');
        Route::get('books/{book}', [LibraryController::class, 'showBook'])->name('books.show');
        Route::put('books/{book}', [LibraryController::class, 'updateBook'])->middleware('permission:library.update')->name('books.update');
        Route::delete('books/{book}', [LibraryController::class, 'destroyBook'])->middleware('permission:library.delete')->name('books.destroy');

        // Categories
        Route::get('categories/data', [LibraryController::class, 'categoriesData'])->name('categories.data');
        Route::post('categories', [LibraryController::class, 'storeCategory'])->middleware('permission:library.create')->name('categories.store');
        Route::get('categories/{category}', [LibraryController::class, 'showCategory'])->name('categories.show');
        Route::put('categories/{category}', [LibraryController::class, 'updateCategory'])->middleware('permission:library.update')->name('categories.update');
        Route::delete('categories/{category}', [LibraryController::class, 'destroyCategory'])->middleware('permission:library.delete')->name('categories.destroy');

        // Authors
        Route::get('authors/data', [LibraryController::class, 'authorsData'])->name('authors.data');
        Route::post('authors', [LibraryController::class, 'storeAuthor'])->middleware('permission:library.create')->name('authors.store');
        Route::get('authors/{author}', [LibraryController::class, 'showAuthor'])->name('authors.show');
        Route::put('authors/{author}', [LibraryController::class, 'updateAuthor'])->middleware('permission:library.update')->name('authors.update');
        Route::delete('authors/{author}', [LibraryController::class, 'destroyAuthor'])->middleware('permission:library.delete')->name('authors.destroy');

        // Publishers
        Route::get('publishers/data', [LibraryController::class, 'publishersData'])->name('publishers.data');
        Route::post('publishers', [LibraryController::class, 'storePublisher'])->middleware('permission:library.create')->name('publishers.store');
        Route::get('publishers/{publisher}', [LibraryController::class, 'showPublisher'])->name('publishers.show');
        Route::put('publishers/{publisher}', [LibraryController::class, 'updatePublisher'])->middleware('permission:library.update')->name('publishers.update');
        Route::delete('publishers/{publisher}', [LibraryController::class, 'destroyPublisher'])->middleware('permission:library.delete')->name('publishers.destroy');

        // Book Issues
        Route::get('issues/data', [LibraryController::class, 'issuesData'])->name('issues.data');
        Route::post('issues', [LibraryController::class, 'issueBook'])->middleware('permission:library.create')->name('issues.store');
        Route::get('issues/{issue}', [LibraryController::class, 'showIssue'])->name('issues.show');
        Route::put('issues/{issue}/return', [LibraryController::class, 'returnBook'])->middleware('permission:library.update')->name('issues.return');
        Route::delete('issues/{issue}', [LibraryController::class, 'destroyIssue'])->middleware('permission:library.delete')->name('issues.destroy');

        // Fine Settings
        Route::get('fine-settings/data', [LibraryController::class, 'fineSettingsData'])->name('fine-settings.data');
        Route::post('fine-settings', [LibraryController::class, 'storeFineSetting'])->middleware('permission:library.create')->name('fine-settings.store');
        Route::get('fine-settings/{fineSetting}', [LibraryController::class, 'showFineSetting'])->name('fine-settings.show');
        Route::put('fine-settings/{fineSetting}', [LibraryController::class, 'updateFineSetting'])->middleware('permission:library.update')->name('fine-settings.update');
        Route::delete('fine-settings/{fineSetting}', [LibraryController::class, 'destroyFineSetting'])->middleware('permission:library.delete')->name('fine-settings.destroy');

        // Reports
        Route::get('reports', [LibraryController::class, 'reports'])->name('reports.index');
        Route::get('reports/books-inventory/data', [LibraryController::class, 'booksReportData'])->name('reports.books-inventory.data');
        Route::get('reports/issued-books/data', [LibraryController::class, 'issuedBooksReportData'])->name('reports.issued-books.data');
        Route::get('reports/overdue-books/data', [LibraryController::class, 'overdueBooksReportData'])->name('reports.overdue-books.data');
        Route::get('reports/fine-collection/data', [LibraryController::class, 'fineCollectionReportData'])->name('reports.fine-collection.data');
        Route::get('reports/student-history/data', [LibraryController::class, 'studentHistoryReportData'])->name('reports.student-history.data');
        Route::get('reports/teacher-history/data', [LibraryController::class, 'teacherHistoryReportData'])->name('reports.teacher-history.data');

        Route::get('reports/{report}/export/excel', [LibraryController::class, 'exportExcel'])->middleware('permission:library.view')->name('reports.export.excel');
        Route::get('reports/{report}/export/pdf', [LibraryController::class, 'exportPdf'])->middleware('permission:library.view')->name('reports.export.pdf');
        Route::get('reports/{report}/print', [LibraryController::class, 'printReport'])->middleware('permission:library.view')->name('reports.print');
    });
