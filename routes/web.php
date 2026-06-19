<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

require __DIR__.'/modules/auth.php';
require __DIR__.'/modules/reports.php';

Route::middleware(['auth', 'school'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function (): void {
        require __DIR__.'/modules/dashboard.php';
        require __DIR__.'/modules/rbac.php';
        require __DIR__.'/modules/academics.php';
        require __DIR__.'/modules/students.php';
        require __DIR__.'/modules/parents.php';
        require __DIR__.'/modules/timetable.php';
        require __DIR__.'/modules/attendance.php';
        require __DIR__.'/modules/teachers.php';
        require __DIR__.'/modules/exams.php';
        require __DIR__.'/modules/homework.php';
        require __DIR__.'/modules/leave.php';
        require __DIR__.'/modules/fees.php';
        require __DIR__.'/modules/notifications.php';
        require __DIR__.'/modules/settings.php';
        require __DIR__.'/modules/users.php';
        require __DIR__.'/modules/calendar.php';
        require __DIR__.'/modules/documents.php';
        require __DIR__.'/modules/transport.php';
        require __DIR__.'/modules/library.php';
    });
