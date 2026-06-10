<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check academic_years
echo "=== academic_years ===\n";
$cols = DB::select('SHOW COLUMNS FROM academic_years');
foreach ($cols as $col) {
    echo "  {$col->Field} ({$col->Type})";
    if ($col->Key === 'PRI') echo ' PK';
    echo "\n";
}
$years = DB::table('academic_years')->get();
foreach ($years as $y) {
    echo "  ID {$y->id}: ";
    foreach ($y as $k => $v) {
        echo "{$k}=" . (is_null($v) ? 'NULL' : $v) . " ";
    }
    echo "\n";
}

echo "\n=== Parents ===\n";
$parents = DB::table('parents')->get();
foreach ($parents as $p) {
    echo "  ID {$p->id}: {$p->first_name} {$p->last_name} ({$p->email})\n";
    $kids = DB::table('parent_student')->where('parent_id', $p->id)->get();
    foreach ($kids as $k) {
        $s = DB::table('students')->where('id', $k->student_id)->first();
        echo "    -> Student: {$s->first_name} {$s->last_name} (as {$k->relationship}, primary={$k->is_primary})\n";
    }
}

echo "\n=== Student Fees ===\n";
$sf = DB::table('student_fees')->get();
foreach ($sf as $f) {
    echo "  ID {$f->id}: student_id={$f->student_id}, fee_structure_id={$f->fee_structure_id}, status={$f->status}\n";
    $items = DB::table('student_fee_items')->where('student_fee_id', $f->id)->get();
    foreach ($items as $it) {
        $cat = DB::table('fee_categories')->where('id', $it->fee_category_id)->first();
        echo "    - Item ID {$it->id}: {$cat->name} = \${$it->amount}, due: {$it->due_date}\n";
    }
}

echo "\n=== Existing Exams ===\n";
$exams = DB::table('exams')->get();
foreach ($exams as $e) {
    $sub = DB::table('subjects')->where('id', $e->subject_id)->first();
    echo "  ID {$e->id}: {$e->exam_name} ({$e->exam_type}), subject={$sub->name}, date={$e->exam_date}, max={$e->maximum_marks}, pass={$e->pass_marks}, published={$e->is_published}\n";
    $results = DB::table('exam_results')->where('exam_id', $e->id)->get();
    foreach ($results as $r) {
        $s = DB::table('students')->where('id', $r->student_id)->first();
        echo "    -> {$s->first_name}: {$r->marks_obtained}/{$e->maximum_marks}, grade={$r->grade}, status={$r->status}\n";
    }
}

echo "\n=== Existing Attendance ===\n";
$atts = DB::table('attendances')->get();
foreach ($atts as $a) {
    $s = DB::table('students')->where('id', $a->student_id)->first();
    echo "  ID {$a->id}: {$s->first_name} {$s->last_name} on {$a->attendance_date} = {$a->status}\n";
}

echo "\n=== Existing Homework ===\n";
$hw = DB::table('homework')->get();
foreach ($hw as $h) {
    $sub = DB::table('subjects')->where('id', $h->subject_id)->first();
    $cs = DB::table('class_section')->where('id', $h->class_section_id)->first();
    $cls = DB::table('classes')->where('id', $cs->class_id)->first();
    $sec = DB::table('sections')->where('id', $cs->section_id)->first();
    echo "  ID {$h->id}: {$h->title} - {$cls->name} {$sec->name}, {$sub->name}, assigned: {$h->assigned_date}, due: {$h->due_date}\n";
}

echo "\n=== Existing Calendar Events ===\n";
$cal = DB::table('academic_calendars')->get();
foreach ($cal as $c) {
    echo "  ID {$c->id}: {$c->title} ({$c->event_type}), {$c->start_date} to {$c->end_date}, audience={$c->audience}, published={$c->is_published}\n";
}

echo "\n=== Student Documents ===\n";
$docs = DB::table('student_documents')->get();
echo "  Count: " . count($docs) . "\n";
foreach ($docs as $d) {
    echo "  ID {$d->id}: {$d->title} ({$d->document_type}), student_id={$d->student_id}\n";
}

echo "\n=== DONE ===\n";
