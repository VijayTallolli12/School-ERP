<?php

// ────────────────────────────────────────────────────────────────────────────
// Teacher App API — Authenticated routes (api.v1.teacher.*)
// Login is defined in routes/modules/api.php (public)
// Self-scoped to authenticated teacher (no UUID in URL)
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\TeacherAppController;
use Illuminate\Support\Facades\Route;

Route::prefix('teacher')->name('teacher.')->middleware('role:Teacher')->group(function (): void {

    // Auth
    Route::post('logout', [TeacherAppController::class, 'logout'])->name('logout');

    // Profile
    Route::get('profile', [TeacherAppController::class, 'profile'])->name('profile');
    Route::put('profile', [TeacherAppController::class, 'updateProfile'])->name('profile.update');
    Route::match(['put', 'post'], 'change-password', [TeacherAppController::class, 'changePassword'])->name('change-password');

    // Dashboard
    Route::get('dashboard', [TeacherAppController::class, 'dashboard'])->name('dashboard');

    // Classes
    Route::get('classes', [TeacherAppController::class, 'classes'])->name('classes');

    // Students directory
    Route::get('students', [TeacherAppController::class, 'students'])->name('students');
    Route::get('students/{id}', [TeacherAppController::class, 'studentShow'])->name('students.show');
    Route::get('students/{id}/attendance', [TeacherAppController::class, 'studentAttendance'])->name('students.attendance');

    // Timetable
    Route::get('timetable', [TeacherAppController::class, 'timetable'])->name('timetable');

    // Attendance
    Route::get('attendance/classes', [TeacherAppController::class, 'attendanceClasses'])->name('attendance.classes');
    Route::get('attendance/students/{classSectionId}', [TeacherAppController::class, 'attendanceStudents'])->name('attendance.students');
    Route::post('attendance/mark', [TeacherAppController::class, 'markAttendance'])->name('attendance.mark');

    // Homework
    Route::get('homework', [TeacherAppController::class, 'homeworkIndex'])->name('homework.index');
    Route::post('homework', [TeacherAppController::class, 'homeworkStore'])->name('homework.store');
    Route::get('homework/{id}', [TeacherAppController::class, 'homeworkShow'])->name('homework.show');
    Route::put('homework/{id}', [TeacherAppController::class, 'homeworkUpdate'])->name('homework.update');

    // Exams
    Route::get('exams', [TeacherAppController::class, 'examsIndex'])->name('exams.index');
    Route::get('exams/{id}', [TeacherAppController::class, 'examsShow'])->name('exams.show');
    Route::get('exams/{id}/schedule', [TeacherAppController::class, 'examsSchedule'])->name('exams.schedule');
    Route::get('exams/{id}/marks', [TeacherAppController::class, 'examsMarks'])->name('exams.marks');
    Route::post('exams/{id}/marks', [TeacherAppController::class, 'examsStoreMarks'])->name('exams.marks.store');
    Route::post('exams/{id}/publish', [TeacherAppController::class, 'examsPublish'])->name('exams.publish');

    // Leave (teacher's own leave via teacher_leaves)
    Route::get('leaves', [TeacherAppController::class, 'leavesIndex'])->name('leaves.index');
    Route::post('leaves', [TeacherAppController::class, 'leavesStore'])->name('leaves.store');
    Route::get('leaves/balance', [TeacherAppController::class, 'leavesBalance'])->name('leaves.balance');
    Route::get('leaves/types', [TeacherAppController::class, 'leavesTypes'])->name('leaves.types');
    Route::get('leaves/{leaveId}', [TeacherAppController::class, 'leavesShow'])->name('leaves.show');
    Route::post('leaves/{leaveId}/cancel', [TeacherAppController::class, 'leavesCancel'])->name('leaves.cancel');

    // Leave (legacy singular routes, kept for backward compatibility)
    Route::get('leave', [TeacherAppController::class, 'leaveIndex'])->name('leave.index');
    Route::post('leave', [TeacherAppController::class, 'leaveStore'])->name('leave.store');
    Route::get('leave-types', [TeacherAppController::class, 'leaveTypes'])->name('leave-types');

    // Transport
    Route::get('transport/routes', [TeacherAppController::class, 'transportRoutes'])->name('transport.routes');
    Route::get('transport/routes/{routeId}', [TeacherAppController::class, 'transportRouteShow'])->name('transport.routes.show');
    Route::get('transport/vehicles', [TeacherAppController::class, 'transportVehicles'])->name('transport.vehicles');
    Route::get('transport/vehicles/{vehicleId}/location', [TeacherAppController::class, 'transportVehicleLocation'])->name('transport.vehicles.location');
    Route::get('transport/live-status', [TeacherAppController::class, 'transportLiveStatus'])->name('transport.live-status');

    // Documents
    Route::get('documents', [TeacherAppController::class, 'documents'])->name('documents');

    // Circulars
    Route::get('circulars', [TeacherAppController::class, 'circulars'])->name('circulars');

    // Notifications
    Route::get('notifications', [TeacherAppController::class, 'notificationsIndex'])->name('notifications.index');
    Route::post('notifications', [TeacherAppController::class, 'notificationsStore'])->name('notifications.store');
    Route::post('notifications/{id}/read', [TeacherAppController::class, 'notificationsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [TeacherAppController::class, 'notificationsReadAll'])->name('notifications.read-all');
});
