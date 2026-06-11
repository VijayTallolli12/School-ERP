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
    // Authenticated routes (Sanctum + School context + throttle)
    // ===================================================================
    Route::middleware(['auth:sanctum', 'school', 'throttle:60,1'])->group(function (): void {

        // --- Auth ---
        Route::get('me', [ApiAuthController::class, 'me'])->name('me');
        Route::post('auth/refresh', [ApiAuthController::class, 'refreshToken'])->name('auth.refresh');
        Route::post('auth/logout', [ApiAuthController::class, 'logout'])->name('auth.logout');

        // --- Dashboard (permission-gated) ---
        Route::get('dashboard/stats', [DashboardApiController::class, 'stats'])
            ->middleware('permission:dashboard.view')->name('dashboard.stats');
        Route::get('dashboard/activity', [DashboardApiController::class, 'recentActivity'])
            ->middleware('permission:dashboard.view')->name('dashboard.activity');
        Route::get('dashboard/notifications', [DashboardApiController::class, 'notifications'])
            ->middleware('permission:dashboard.view')->name('dashboard.notifications');

        // --- Students (permission-gated) ---
        Route::get('students', [StudentApiController::class, 'index'])
            ->middleware('permission:students.view')->name('students.index');
        Route::get('students/{uuid}', [StudentApiController::class, 'show'])
            ->middleware('permission:students.view')->name('students.show');
        Route::get('students/{uuid}/attendance', [StudentApiController::class, 'attendanceSummary'])
            ->middleware('permission:attendance.view')->name('students.attendance');
        Route::get('students/{uuid}/fees', [StudentApiController::class, 'feesSummary'])
            ->middleware('permission:fees.view')->name('students.fees');
        Route::get('students/{uuid}/exams', [StudentApiController::class, 'examSummary'])
            ->middleware('permission:exams.view')->name('students.exams');
        Route::get('students/{uuid}/timetable', [StudentApiController::class, 'timetable'])
            ->middleware('permission:timetable.view')->name('students.timetable');

        // --- Parents (permission-gated) ---
        Route::get('parents', [ParentApiController::class, 'index'])
            ->middleware('permission:parents.view')->name('parents.index');
        Route::get('parents/{uuid}', [ParentApiController::class, 'show'])
            ->middleware('permission:parents.view')->name('parents.show');
        Route::get('parents/{uuid}/dashboard', [ParentApiController::class, 'dashboard'])
            ->middleware('permission:dashboard.view')->name('parents.dashboard');
        Route::get('parents/{uuid}/children', [ParentApiController::class, 'children'])
            ->middleware('permission:parents.view')->name('parents.children');
        Route::get('parents/{uuid}/children/{childUuid}/attendance', [ParentApiController::class, 'childAttendance'])
            ->middleware('permission:attendance.view')->name('parents.child.attendance');
        Route::get('parents/{uuid}/children/{childUuid}/fees', [ParentApiController::class, 'childFees'])
            ->middleware('permission:fees.view')->name('parents.child.fees');
        Route::get('parents/{uuid}/children/{childUuid}/exams', [ParentApiController::class, 'childExamResults'])
            ->middleware('permission:exams.view')->name('parents.child.exams');
        Route::get('parents/{uuid}/children/{childUuid}/timetable', [ParentApiController::class, 'childTimetable'])
            ->middleware('permission:timetable.view')->name('parents.child.timetable');
        Route::get('parents/{uuid}/children/{childUuid}/homework', [ParentApiController::class, 'childHomework'])
            ->middleware('permission:homework.view')->name('parents.child.homework');
        Route::get('parents/{uuid}/children/{childUuid}/calendar', [ParentApiController::class, 'childCalendar'])
            ->middleware('permission:academic_calendar.view')->name('parents.child.calendar');
        Route::get('parents/{uuid}/children/{childUuid}/documents', [ParentApiController::class, 'childDocuments'])
            ->middleware('permission:student_documents.view')->name('parents.child.documents');
        Route::get('parents/{uuid}/children/{childUuid}/leave-requests', [ParentApiController::class, 'childLeaveRequests'])
            ->middleware('permission:leave_management.view')->name('parents.child.leave-requests');
        Route::post('parents/{uuid}/children/{childUuid}/leave-requests', [ParentApiController::class, 'storeLeaveRequest'])
            ->middleware('permission:leave_management.create')->name('parents.child.leave-requests.store');
        Route::get('parents/{uuid}/children/{childUuid}/leave-requests/{id}', [ParentApiController::class, 'showLeaveRequest'])
            ->middleware('permission:leave_management.view')->name('parents.child.leave-requests.show');
        Route::put('parents/{uuid}/children/{childUuid}/leave-requests/{id}', [ParentApiController::class, 'updateLeaveRequest'])
            ->middleware('permission:leave_management.create')->name('parents.child.leave-requests.update');

        Route::get('parents/{uuid}/circulars', [ParentApiController::class, 'childCirculars'])
            ->middleware('permission:notifications.view')->name('parents.circulars');
        Route::get('parents/{uuid}/circulars/{id}', [ParentApiController::class, 'childCircularDetail'])
            ->middleware('permission:notifications.view')->name('parents.circulars.show');
        Route::post('parents/{uuid}/circulars/{id}/read', [ParentApiController::class, 'markCircularRead'])
            ->middleware('permission:notifications.view')->name('parents.circulars.read');

        Route::put('parents/{uuid}', [ParentApiController::class, 'updateParentProfile'])
            ->middleware('permission:parents.view')->name('parents.update');
        Route::put('parents/{uuid}/change-password', [ParentApiController::class, 'changeParentPassword'])
            ->middleware('permission:parents.view')->name('parents.change-password');

        // --- Teachers (permission-gated) ---
        Route::get('teachers', [TeacherApiController::class, 'index'])
            ->middleware('permission:teachers.view')->name('teachers.index');
        Route::get('teachers/{uuid}', [TeacherApiController::class, 'show'])
            ->middleware('permission:teachers.view')->name('teachers.show');
        Route::get('teachers/{uuid}/timetable', [TeacherApiController::class, 'timetable'])
            ->middleware('permission:timetable.view')->name('teachers.timetable');
        Route::get('teachers/{uuid}/attendance', [TeacherApiController::class, 'attendance'])
            ->middleware('permission:attendance.view')->name('teachers.attendance');
        Route::get('teachers/{uuid}/classes', [TeacherApiController::class, 'assignedClasses'])
            ->middleware('permission:teachers.view')->name('teachers.classes');
        Route::get('teachers/{uuid}/subjects', [TeacherApiController::class, 'assignedSubjects'])
            ->middleware('permission:teachers.view')->name('teachers.subjects');

        // --- Attendance (permission-gated) ---
        Route::get('attendance', [AttendanceApiController::class, 'index'])
            ->middleware('permission:attendance.view')->name('attendance.index');
        Route::get('attendance/daily', [AttendanceApiController::class, 'daily'])
            ->middleware('permission:attendance.view')->name('attendance.daily');
        Route::get('attendance/monthly', [AttendanceApiController::class, 'monthly'])
            ->middleware('permission:attendance.view')->name('attendance.monthly');
        Route::get('attendance/statistics', [AttendanceApiController::class, 'statistics'])
            ->middleware('permission:attendance.view')->name('attendance.statistics');

        // --- Fees (permission-gated) ---
        Route::get('fees', [FeeApiController::class, 'studentFees'])
            ->middleware('permission:fees.view')->name('fees.index');
        Route::get('fees/pending', [FeeApiController::class, 'pendingFees'])
            ->middleware('permission:fees.view')->name('fees.pending');
        Route::get('fees/payments', [FeeApiController::class, 'payments'])
            ->middleware('permission:fees.view')->name('fees.payments');
        Route::get('fees/payments/{paymentId}/receipt', [FeeApiController::class, 'paymentReceipt'])
            ->middleware('permission:fees.view')->name('fees.receipt');
        Route::get('fees/dashboard-stats', [FeeApiController::class, 'dashboardStats'])
            ->middleware('permission:fees.view')->name('fees.dashboard');

        // --- Exams (permission-gated) ---
        Route::get('exams', [ExamApiController::class, 'index'])
            ->middleware('permission:exams.view')->name('exams.index');
        Route::get('exams/{id}', [ExamApiController::class, 'show'])
            ->middleware('permission:exams.view')->name('exams.show');
        Route::get('exams/{examId}/results', [ExamApiController::class, 'results'])
            ->middleware('permission:exams.view')->name('exams.results');
        Route::get('exams/{examId}/results/{resultId}', [ExamApiController::class, 'resultDetail'])
            ->middleware('permission:exams.view')->name('exams.result.detail');
        Route::get('exams/{examId}/report-card', [ExamApiController::class, 'reportCard'])
            ->middleware('permission:exams.view')->name('exams.report-card');

        // --- Notifications (permission-gated) ---
        Route::get('notifications', [NotificationApiController::class, 'index'])
            ->middleware('permission:notifications.view')->name('notifications.index');
        Route::get('notifications/unread', [NotificationApiController::class, 'unread'])
            ->middleware('permission:notifications.view')->name('notifications.unread');
        Route::post('notifications/{id}/read', [NotificationApiController::class, 'markRead'])
            ->middleware('permission:notifications.view')->name('notifications.read');
        Route::post('notifications/read-all', [NotificationApiController::class, 'markAllRead'])
            ->middleware('permission:notifications.view')->name('notifications.read-all');
        Route::get('notifications/announcements', [NotificationApiController::class, 'announcements'])
            ->middleware('permission:notifications.view')->name('notifications.announcements');
    });
});
