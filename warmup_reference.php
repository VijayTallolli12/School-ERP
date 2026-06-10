<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== EXISTING REFERENCE DATA ===\n";

// Classes
echo "\n--- Classes ---\n";
$classes = DB::table('classes')->get();
foreach ($classes as $c) {
    echo "  ID {$c->id}: {$c->name} ({$c->code})\n";
}

// Sections
echo "\n--- Sections ---\n";
$sections = DB::table('sections')->get();
foreach ($sections as $s) {
    echo "  ID {$s->id}: {$s->name} ({$s->code})\n";
}

// Class Sections
echo "\n--- Class Sections ---\n";
$cs = DB::table('class_section')->get();
foreach ($cs as $c) {
    echo "  ID {$c->id}: class_id={$c->class_id}, section_id={$c->section_id}, class_teacher_id={$c->class_teacher_id}, status={$c->status}\n";
}

// Subjects
echo "\n--- Subjects ---\n";
$subs = DB::table('subjects')->get();
foreach ($subs as $s) {
    echo "  ID {$s->id}: {$s->name} ({$s->code}) - {$s->type}\n";
}

// Students with sessions
echo "\n--- Students ---\n";
$students = DB::table('students')->get();
foreach ($students as $s) {
    $session = DB::table('student_sessions')->where('student_id', $s->id)->first();
    $csData = $session ? DB::table('class_section')->where('id', $session->class_section_id)->first() : null;
    $className = $csData ? DB::table('classes')->where('id', $csData->class_id)->value('name') : 'N/A';
    $secName = $csData ? DB::table('sections')->where('id', $csData->section_id)->value('name') : 'N/A';
    echo "  ID {$s->id}: {$s->first_name} {$s->last_name} ({$s->admission_no}) - Class: {$className} {$secName}, Roll: {$session->roll_no}\n";
}

// Fee structures
echo "\n--- Fee Structures ---\n";
$fs = DB::table('fee_structures')->get();
foreach ($fs as $f) {
    echo "  ID {$f->id}: {$f->name} (class_section_id={$f->class_section_id}, status={$f->status})\n";
    $items = DB::table('fee_structure_items')->where('fee_structure_id', $f->id)->get();
    foreach ($items as $it) {
        $cat = DB::table('fee_categories')->where('id', $it->fee_category_id)->first();
        echo "    - {$cat->name}: \${$it->amount}\n";
    }
}

// Fee categories
echo "\n--- Fee Categories ---\n";
$cats = DB::table('fee_categories')->get();
foreach ($cats as $c) {
    echo "  ID {$c->id}: {$c->name} ({$c->code})\n";
}

// Academic years
echo "\n--- Academic Years ---\n";
$years = DB::table('academic_years')->get();
foreach ($years as $y) {
    echo "  ID {$y->id}: {$y->name} ({$y->start_date} to {$y->end_date})\n";
}

// Users with roles
echo "\n--- Users ---\n";
$users = DB::table('users')->get();
foreach ($users as $u) {
    $roles = DB::table('model_has_roles')->where('model_id', $u->id)->join('roles', 'model_has_roles.role_id', '=', 'roles.id')->pluck('roles.name')->implode(', ');
    echo "  ID {$u->id}: {$u->name} ({$u->email}) - Roles: {$roles}\n";
}

echo "\n=== DONE ===\n";
