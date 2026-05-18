<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

require __DIR__.'/modules/auth.php';
require __DIR__.'/modules/reports.php';


Route::get('/debug-upload', function () {
    $files = Storage::disk('public')->files('settings/schools');

    return [
        'disk_root' => storage_path('app/public'),
        'files' => $files,
        'storage_exists' => file_exists(public_path('storage')),
        'storage_link' => is_link(public_path('storage')),
    ];
});
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
        require __DIR__.'/modules/fees.php';
        require __DIR__.'/modules/notifications.php';
        require __DIR__.'/modules/settings.php';
        require __DIR__.'/modules/users.php';
    });
