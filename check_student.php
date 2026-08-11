<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'student@school.com')->first();
if ($user) {
    echo 'User: ' . $user->name . ' (ID: ' . $user->id . ')' . PHP_EOL;
    echo 'Email: ' . $user->email . PHP_EOL;
    echo 'Roles: ' . $user->getRoleNames()->implode(', ') . PHP_EOL;
    echo 'Schools: ' . $user->schools->pluck('name')->implode(', ') . PHP_EOL;
    echo 'Current School ID: ' . $user->current_school_id . PHP_EOL;
    $student = $user->student;
    if ($student) {
        echo 'Student: ' . $student->full_name . ' (ID: ' . $student->id . ')' . PHP_EOL;
        echo 'Student School ID: ' . $student->school_id . PHP_EOL;
        echo 'Student Status: ' . $student->status . PHP_EOL;
        $session = $student->sessions()->where('status', 'active')->first();
        if ($session) {
            echo 'Active Session: Class Section ID: ' . $session->class_section_id . ', Academic Year ID: ' . $session->academic_year_id . PHP_EOL;
        } else {
            echo 'No active session' . PHP_EOL;
        }
    } else {
        echo 'No student linked' . PHP_EOL;
    }
} else {
    echo 'User not found' . PHP_EOL;
}