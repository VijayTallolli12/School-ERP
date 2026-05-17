<?php

use Illuminate\Support\Facades\Route;

// Reports Module Routes
Route::middleware(['auth', 'verified', 'permission:reports.view'])->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        // Student Reports
        Route::get('students', [\App\Modules\Reports\Controllers\StudentReportController::class, 'index'])->name('students.index');
        Route::get('students/list', [\App\Modules\Reports\Controllers\StudentReportController::class, 'list'])->name('students.list');
        Route::get('students/admission', [\App\Modules\Reports\Controllers\StudentReportController::class, 'admission'])->name('students.admission');
        Route::get('students/class-wise', [\App\Modules\Reports\Controllers\StudentReportController::class, 'classWise'])->name('students.class_wise');
        Route::get('students/list/export/{type}', [\App\Modules\Reports\Controllers\StudentReportController::class, 'exportList'])->name('students.list.export');
        Route::get('students/admission/export/{type}', [\App\Modules\Reports\Controllers\StudentReportController::class, 'exportAdmission'])->name('students.admission.export');
        Route::get('students/class-wise/export/{type}', [\App\Modules\Reports\Controllers\StudentReportController::class, 'exportClassWise'])->name('students.class_wise.export');

        // Attendance Reports
        Route::get('attendance', [\App\Modules\Reports\Controllers\AttendanceReportController::class, 'index'])->name('attendance.index');
        Route::get('attendance/daily', [\App\Modules\Reports\Controllers\AttendanceReportController::class, 'daily'])->name('attendance.daily');
        Route::get('attendance/daily/list', [\App\Modules\Reports\Controllers\AttendanceReportController::class, 'dailyList'])->name('attendance.daily_list');
        Route::get('attendance/monthly', [\App\Modules\Reports\Controllers\AttendanceReportController::class, 'monthly'])->name('attendance.monthly');
        Route::get('attendance/class-wise', [\App\Modules\Reports\Controllers\AttendanceReportController::class, 'classWise'])->name('attendance.class_wise');

        // Fee Reports
        Route::get('fees', [\App\Modules\Reports\Controllers\FeeReportController::class, 'index'])->name('fees.index');
        Route::get('fees/collection-summary', [\App\Modules\Reports\Controllers\FeeReportController::class, 'collectionSummary'])->name('fees.collection_summary');
        Route::get('fees/paid', [\App\Modules\Reports\Controllers\FeeReportController::class, 'paid'])->name('fees.paid');
        Route::get('fees/pending', [\App\Modules\Reports\Controllers\FeeReportController::class, 'pending'])->name('fees.pending');
        Route::get('fees/overdue', [\App\Modules\Reports\Controllers\FeeReportController::class, 'overdue'])->name('fees.overdue');
        Route::get('fees/{type}/export/pdf', [\App\Modules\Reports\Controllers\FeeReportController::class, 'exportPdf'])->name('fees.export.pdf');
        Route::get('fees/{type}/export/excel', [\App\Modules\Reports\Controllers\FeeReportController::class, 'exportExcel'])->name('fees.export.excel');
        Route::get('fees/{type}/print', [\App\Modules\Reports\Controllers\FeeReportController::class, 'printReport'])->name('fees.print');

        // Exam Reports
        Route::get('exams', [\App\Modules\Reports\Controllers\ExamReportController::class, 'index'])->name('exams.index');
        Route::get('exams/results', [\App\Modules\Reports\Controllers\ExamReportController::class, 'results'])->name('exams.results');
        Route::get('exams/class-performance', [\App\Modules\Reports\Controllers\ExamReportController::class, 'classPerformance'])->name('exams.class_performance');
        Route::get('exams/subject-performance', [\App\Modules\Reports\Controllers\ExamReportController::class, 'subjectPerformance'])->name('exams.subject_performance');
        Route::get('exams/student-summary', [\App\Modules\Reports\Controllers\ExamReportController::class, 'studentSummary'])->name('exams.student_summary');
        Route::get('exams/{type}/export/pdf', [\App\Modules\Reports\Controllers\ExamReportController::class, 'exportPdf'])->name('exams.export.pdf');
        Route::get('exams/{type}/export/excel', [\App\Modules\Reports\Controllers\ExamReportController::class, 'exportExcel'])->name('exams.export.excel');
        Route::get('exams/{type}/print', [\App\Modules\Reports\Controllers\ExamReportController::class, 'printReport'])->name('exams.print');

        // Teacher Reports
        Route::get('teachers', [\App\Modules\Reports\Controllers\TeacherReportController::class, 'index'])->name('teachers.index');
        Route::get('teachers/list', [\App\Modules\Reports\Controllers\TeacherReportController::class, 'list'])->name('teachers.list');
        Route::get('teachers/attendance', [\App\Modules\Reports\Controllers\TeacherReportController::class, 'attendance'])->name('teachers.attendance');
        Route::get('teachers/subject-allocation', [\App\Modules\Reports\Controllers\TeacherReportController::class, 'subjectAllocation'])->name('teachers.subject_allocation');
        Route::get('teachers/class-teacher-mapping', [\App\Modules\Reports\Controllers\TeacherReportController::class, 'classTeacherMapping'])->name('teachers.class_teacher_mapping');
        Route::get('teachers/{type}/export/pdf', [\App\Modules\Reports\Controllers\TeacherReportController::class, 'exportPdf'])->name('teachers.export.pdf');
        Route::get('teachers/{type}/export/excel', [\App\Modules\Reports\Controllers\TeacherReportController::class, 'exportExcel'])->name('teachers.export.excel');
        Route::get('teachers/{type}/print', [\App\Modules\Reports\Controllers\TeacherReportController::class, 'printReport'])->name('teachers.print');

        // Parent Reports
        Route::get('parents', [\App\Modules\Reports\Controllers\ParentReportController::class, 'index'])->name('parents.index');
        Route::get('parents/list', [\App\Modules\Reports\Controllers\ParentReportController::class, 'list'])->name('parents.list');
        Route::get('parents/mapping', [\App\Modules\Reports\Controllers\ParentReportController::class, 'mapping'])->name('parents.mapping');
        Route::get('parents/activity-summary', [\App\Modules\Reports\Controllers\ParentReportController::class, 'activitySummary'])->name('parents.activity_summary');
        Route::get('parents/{type}/export/pdf', [\App\Modules\Reports\Controllers\ParentReportController::class, 'exportPdf'])->name('parents.export.pdf');
        Route::get('parents/{type}/export/excel', [\App\Modules\Reports\Controllers\ParentReportController::class, 'exportExcel'])->name('parents.export.excel');
        Route::get('parents/{type}/print', [\App\Modules\Reports\Controllers\ParentReportController::class, 'printReport'])->name('parents.print');
    });
});