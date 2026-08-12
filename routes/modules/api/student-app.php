<?php

// ────────────────────────────────────────────────────────────────────────────
// Student App API — Authenticated routes (api.v1.student.*)
// Login is defined in routes/modules/api.php (public)
// All endpoints self-scoped to the authenticated student (no UUID required)
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\StudentAppController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')->middleware('student.linked')->name('student.')->group(function (): void {

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

    // Fees
    Route::get('fees', [StudentAppController::class, 'fees'])->name('fees');

    // Homework
    Route::get('homework', [StudentAppController::class, 'homeworkIndex'])->name('homework.index');
    Route::get('homework/{id}', [StudentAppController::class, 'homeworkShow'])->name('homework.show');

    // Assignments
    Route::get('assignments', [StudentAppController::class, 'assignments'])->name('assignments');

    // Timetable
    Route::get('timetable', [StudentAppController::class, 'timetable'])->name('timetable');

    // Exams
    Route::get('exams', [StudentAppController::class, 'examsIndex'])->name('exams.index');
    Route::get('exam-schedule', [StudentAppController::class, 'examSchedule'])->name('exam-schedule');
    Route::get('results', [StudentAppController::class, 'results'])->name('results');
    Route::get('report-card', [StudentAppController::class, 'reportCard'])->name('report-card');

    // Event Calendar
    Route::get('calendar', [StudentAppController::class, 'calendar'])->name('calendar');

    // Documents
    Route::get('documents', [StudentAppController::class, 'documents'])->name('documents');

    // Transport
    Route::get('transport', [StudentAppController::class, 'transport'])->name('transport');

    // Leave requests
    Route::get('leave-requests', [StudentAppController::class, 'leaveRequests'])->name('leave-requests');
    Route::post('leave-requests', [StudentAppController::class, 'storeLeaveRequest'])->name('leave-requests.store');
    Route::get('leave-requests/{id}', [StudentAppController::class, 'showLeaveRequest'])->name('leave-requests.show');
    Route::put('leave-requests/{id}', [StudentAppController::class, 'updateLeaveRequest'])->name('leave-requests.update');

    // Library
    Route::get('library/books', [StudentAppController::class, 'libraryBooks'])->name('library.books');
    Route::get('library/history', [StudentAppController::class, 'libraryHistory'])->name('library.history');
    Route::get('library/fines', [StudentAppController::class, 'libraryFines'])->name('library.fines');

    // Notifications
    Route::get('notifications', [StudentAppController::class, 'notificationsIndex'])->name('notifications.index');
    Route::get('notifications/unread', [StudentAppController::class, 'notificationsUnread'])->name('notifications.unread');
    Route::get('notifications/{id}', [StudentAppController::class, 'notificationShow'])->name('notifications.show');
    Route::post('notifications/{id}/read', [StudentAppController::class, 'notificationRead'])->name('notifications.read');
    Route::post('notifications/read-all', [StudentAppController::class, 'notificationsReadAll'])->name('notifications.read-all');

    // Circulars / Announcements
    Route::get('circulars', [StudentAppController::class, 'circulars'])->name('circulars');
    Route::get('circulars/{id}', [StudentAppController::class, 'showCircular'])->name('circulars.show');
    Route::post('circulars/{id}/read', [StudentAppController::class, 'markCircularRead'])->name('circulars.read');
});
