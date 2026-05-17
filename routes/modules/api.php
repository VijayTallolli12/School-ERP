<?php

use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\ExamApiController;
use App\Http\Controllers\Api\V1\FeeApiController;
use App\Http\Controllers\Api\V1\NotificationApiController;
use App\Http\Controllers\Api\V1\ParentApiController;
use App\Http\Controllers\Api\V1\StudentApiController;
use App\Http\Controllers\Api\V1\TeacherApiController;
use App\Modules\Auth\Controllers\ApiAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes — School ERP (Sanctum)
|--------------------------------------------------------------------------
|
| Base: /api/v1/*
| Auth: Sanctum token-based (Bearer token)
| School context: X-School-Id header or school_id param
|
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {

    // ===================================================================
    // Authentication (public + throttled)
    // ===================================================================
    Route::post('auth/login', [ApiAuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('auth.login');

    // ===================================================================
    // Authenticated routes (Sanctum + School context)
    // ===================================================================
    Route::middleware(['auth:sanctum', 'school'])->group(function (): void {

        // --- Auth ---
        Route::get('me', [ApiAuthController::class, 'me'])->name('me');
        Route::post('auth/refresh', [ApiAuthController::class, 'refreshToken'])->name('auth.refresh');
        Route::post('auth/logout', [ApiAuthController::class, 'logout'])->name('auth.logout');

        // --- Dashboard ---
        Route::get('dashboard/stats', [DashboardApiController::class, 'stats'])->name('dashboard.stats');
        Route::get('dashboard/activity', [DashboardApiController::class, 'recentActivity'])->name('dashboard.activity');
        Route::get('dashboard/notifications', [DashboardApiController::class, 'notifications'])->name('dashboard.notifications');

        // --- Students ---
        Route::get('students', [StudentApiController::class, 'index'])->name('students.index');
        Route::get('students/{uuid}', [StudentApiController::class, 'show'])->name('students.show');
        Route::get('students/{uuid}/attendance', [StudentApiController::class, 'attendanceSummary'])->name('students.attendance');
        Route::get('students/{uuid}/fees', [StudentApiController::class, 'feesSummary'])->name('students.fees');
        Route::get('students/{uuid}/exams', [StudentApiController::class, 'examSummary'])->name('students.exams');
        Route::get('students/{uuid}/timetable', [StudentApiController::class, 'timetable'])->name('students.timetable');

        // --- Parents ---
        Route::get('parents', [ParentApiController::class, 'index'])->name('parents.index');
        Route::get('parents/{uuid}', [ParentApiController::class, 'show'])->name('parents.show');
        Route::get('parents/{uuid}/dashboard', [ParentApiController::class, 'dashboard'])->name('parents.dashboard');
        Route::get('parents/{uuid}/children', [ParentApiController::class, 'children'])->name('parents.children');
        Route::get('parents/{uuid}/children/{childUuid}/attendance', [ParentApiController::class, 'childAttendance'])->name('parents.child.attendance');
        Route::get('parents/{uuid}/children/{childUuid}/fees', [ParentApiController::class, 'childFees'])->name('parents.child.fees');
        Route::get('parents/{uuid}/children/{childUuid}/exams', [ParentApiController::class, 'childExamResults'])->name('parents.child.exams');
        Route::get('parents/{uuid}/children/{childUuid}/timetable', [ParentApiController::class, 'childTimetable'])->name('parents.child.timetable');

        // --- Teachers ---
        Route::get('teachers', [TeacherApiController::class, 'index'])->name('teachers.index');
        Route::get('teachers/{uuid}', [TeacherApiController::class, 'show'])->name('teachers.show');
        Route::get('teachers/{uuid}/timetable', [TeacherApiController::class, 'timetable'])->name('teachers.timetable');
        Route::get('teachers/{uuid}/attendance', [TeacherApiController::class, 'attendance'])->name('teachers.attendance');
        Route::get('teachers/{uuid}/classes', [TeacherApiController::class, 'assignedClasses'])->name('teachers.classes');
        Route::get('teachers/{uuid}/subjects', [TeacherApiController::class, 'assignedSubjects'])->name('teachers.subjects');

        // --- Attendance ---
        Route::get('attendance', [AttendanceApiController::class, 'index'])->name('attendance.index');
        Route::get('attendance/daily', [AttendanceApiController::class, 'daily'])->name('attendance.daily');
        Route::get('attendance/monthly', [AttendanceApiController::class, 'monthly'])->name('attendance.monthly');
        Route::get('attendance/statistics', [AttendanceApiController::class, 'statistics'])->name('attendance.statistics');

        // --- Fees ---
        Route::get('fees', [FeeApiController::class, 'studentFees'])->name('fees.index');
        Route::get('fees/pending', [FeeApiController::class, 'pendingFees'])->name('fees.pending');
        Route::get('fees/payments', [FeeApiController::class, 'payments'])->name('fees.payments');
        Route::get('fees/payments/{paymentId}/receipt', [FeeApiController::class, 'paymentReceipt'])->name('fees.receipt');
        Route::get('fees/dashboard-stats', [FeeApiController::class, 'dashboardStats'])->name('fees.dashboard');

        // --- Exams ---
        Route::get('exams', [ExamApiController::class, 'index'])->name('exams.index');
        Route::get('exams/{id}', [ExamApiController::class, 'show'])->name('exams.show');
        Route::get('exams/{examId}/results', [ExamApiController::class, 'results'])->name('exams.results');
        Route::get('exams/{examId}/results/{resultId}', [ExamApiController::class, 'resultDetail'])->name('exams.result.detail');
        Route::get('exams/{examId}/report-card', [ExamApiController::class, 'reportCard'])->name('exams.report-card');

        // --- Notifications ---
        Route::get('notifications', [NotificationApiController::class, 'index'])->name('notifications.index');
        Route::get('notifications/unread', [NotificationApiController::class, 'unread'])->name('notifications.unread');
        Route::post('notifications/{id}/read', [NotificationApiController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationApiController::class, 'markAllRead'])->name('notifications.read-all');
        Route::get('notifications/announcements', [NotificationApiController::class, 'announcements'])->name('notifications.announcements');
    });
});
