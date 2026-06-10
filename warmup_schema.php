<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'students', 'parents', 'parent_student',
    'student_sessions', 'classes', 'sections', 'subjects',
    'attendances', 'exams', 'exam_results',
    'student_fee_items', 'student_fees', 'fee_payments', 'fee_payment_items',
    'fee_structure_items', 'fee_structures', 'fee_categories',
    'homework', 'student_documents',
    'academic_calendars', 'users', 'schools'
];

foreach ($tables as $table) {
    try {
        echo "\n=== {$table} ===\n";
        $cols = DB::select("SHOW COLUMNS FROM {$table}");
        foreach ($cols as $col) {
            echo "  {$col->Field} ({$col->Type})";
            if ($col->Key === 'PRI') echo ' PK';
            if ($col->Key === 'MUL') echo ' FK';
            echo "\n";
        }
        // Show one row
        $row = DB::table($table)->first();
        if ($row) {
            echo "  --- Sample row ---\n";
            foreach ($row as $k => $v) {
                echo "    {$k}: " . (is_null($v) ? 'NULL' : (strlen($v) > 80 ? substr($v, 0, 80) . '...' : $v)) . "\n";
            }
        } else {
            echo "  (empty)\n";
        }
    } catch (\Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}
