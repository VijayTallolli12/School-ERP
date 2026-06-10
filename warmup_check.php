<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DATABASE TABLES ===\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    echo '  ' . reset($t) . "\n";
}

echo "\n=== KEY COUNTS ===\n";
$checks = [
    'schools', 'users', 'roles', 'permissions',
    'students', 'parents', 'parent_student',
    'student_sessions', 'attendances', 'fee_types',
    'student_fee_items', 'fee_paid_transactions',
    'exams', 'exam_marks', 'exam_results',
    'homework', 'homework_submissions',
    'calendar_events', 'student_documents',
    'notifications',
];
// Try class table variations
$classTables = ['school_classes', 'classes', 'class_sections', 'sections', 'subjects'];
foreach ($classTables as $ct) {
    try {
        $cnt = DB::table($ct)->count();
        echo "  {$ct}: {$cnt}\n";
    } catch (\Exception $e) {
        echo "  {$ct}: NOT FOUND\n";
    }
}

foreach ($checks as $table) {
    try {
        $cnt = DB::table($table)->count();
        echo "  {$table}: {$cnt}\n";
    } catch (\Exception $e) {
        echo "  {$table}: NOT FOUND\n";
    }
}
