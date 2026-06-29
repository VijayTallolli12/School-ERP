<?php

// ────────────────────────────────────────────────────────────────────────────
// Student App API — Authenticated routes (api.v1.student.*)
// Login is defined in routes/modules/api.php (public)
// All endpoints self-scoped to the authenticated student (no UUID required)
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\StudentAppController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')->name('student.')->group(function (): void {

    // Auth
    Route::post('logout', [StudentAppController::class, 'logout'])->name('logout');

    // Profile
    Route::get('profile', [StudentAppController::class, 'profile'])->name('profile');
    Route::put('profile', [StudentAppController::class, 'updateProfile'])->name('profile.update');
    Route::put('change-password', [StudentAppController::class, 'changePassword'])->name('change-password');

    // Dashboard
    Route::get('dashboard', [StudentAppController::class, 'dashboard'])->name('dashboard');

    // Attendance
    Route::get('attendance', [StudentAppController::class, 'attendance'])->name('attendance');
    Route::get('attendance/monthly', [StudentAppController::class, 'attendanceMonthly'])->name('attendance.monthly');
    Route::get('attendance/summary', [StudentAppController::class, 'attendanceSummary'])->name('attendance.summary');

    // Homework
    Route::get('homework', [StudentAppController::class, 'homeworkIndex'])->name('homework.index');
    Route::get('homework/{id}', [StudentAppController::class, 'homeworkShow'])->name('homework.show');

    // Timetable
    Route::get('timetable', [StudentAppController::class, 'timetable'])->name('timetable');

    // Exams
    Route::get('exams', [StudentAppController::class, 'examsIndex'])->name('exams.index');
    Route::get('results', [StudentAppController::class, 'results'])->name('results');
    Route::get('report-card', [StudentAppController::class, 'reportCard'])->name('report-card');

    // Library
    Route::get('library/books', [StudentAppController::class, 'libraryBooks'])->name('library.books');
    Route::get('library/history', [StudentAppController::class, 'libraryHistory'])->name('library.history');
    Route::get('library/fines', [StudentAppController::class, 'libraryFines'])->name('library.fines');

    // Notifications
    Route::get('notifications', [StudentAppController::class, 'notificationsIndex'])->name('notifications.index');
    Route::post('notifications/read', [StudentAppController::class, 'notificationsRead'])->name('notifications.read');
});
