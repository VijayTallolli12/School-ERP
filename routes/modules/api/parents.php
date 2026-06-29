<?php

// ────────────────────────────────────────────────────────────────────────────
// Parents — api.v1.parents.*
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\ParentApiController;
use Illuminate\Support\Facades\Route;

Route::get('parents', [ParentApiController::class, 'index'])
    ->middleware('permission:parents.view')->name('parents.index');
Route::get('parents/{uuid}', [ParentApiController::class, 'show'])
    ->middleware('permission:parents.view')->name('parents.show');
Route::get('parents/{uuid}/dashboard', [ParentApiController::class, 'dashboard'])
    ->middleware('permission:dashboard.view')->name('parents.dashboard');
Route::get('parents/{uuid}/children', [ParentApiController::class, 'children'])
    ->middleware('permission:parents.view')->name('parents.children');

// Children sub-resources
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

// Leave requests
Route::get('parents/{uuid}/children/{childUuid}/leave-requests', [ParentApiController::class, 'childLeaveRequests'])
    ->middleware('permission:leave_management.view')->name('parents.child.leave-requests');
Route::post('parents/{uuid}/children/{childUuid}/leave-requests', [ParentApiController::class, 'storeLeaveRequest'])
    ->middleware('permission:leave_management.create')->name('parents.child.leave-requests.store');
Route::get('parents/{uuid}/children/{childUuid}/leave-requests/{id}', [ParentApiController::class, 'showLeaveRequest'])
    ->middleware('permission:leave_management.view')->name('parents.child.leave-requests.show');
Route::put('parents/{uuid}/children/{childUuid}/leave-requests/{id}', [ParentApiController::class, 'updateLeaveRequest'])
    ->middleware('permission:leave_management.create')->name('parents.child.leave-requests.update');

// Circulars / Announcements
Route::get('parents/{uuid}/circulars', [ParentApiController::class, 'childCirculars'])
    ->middleware('permission:notifications.view')->name('parents.circulars');
Route::get('parents/{uuid}/circulars/{id}', [ParentApiController::class, 'childCircularDetail'])
    ->middleware('permission:notifications.view')->name('parents.circulars.show');
Route::post('parents/{uuid}/circulars/{id}/read', [ParentApiController::class, 'markCircularRead'])
    ->middleware('permission:notifications.view')->name('parents.circulars.read');

// Profile
Route::put('parents/{uuid}', [ParentApiController::class, 'updateParentProfile'])
    ->middleware('permission:parents.view')->name('parents.update');
Route::put('parents/{uuid}/change-password', [ParentApiController::class, 'changeParentPassword'])
    ->middleware('permission:parents.view')->name('parents.change-password');
