<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check timetable slots for class_section_id=1, academic_year_id=1
$slots = \App\Modules\Timetable\Models\TimetableSlot::query()
    ->where('class_section_id', 1)
    ->where('academic_year_id', 1)
    ->with(['subject:id,name,code', 'teacher.user:id,name'])
    ->orderBy('day_of_week')
    ->orderBy('start_time')
    ->get();

echo "Timetable slots count: " . $slots->count() . PHP_EOL;
foreach ($slots as $slot) {
    echo "Slot: Day=" . $slot->day_of_week . ", Period=" . $slot->period_label . ", Subject=" . ($slot->subject?->name ?? 'none') . ", Teacher=" . ($slot->teacher?->user?->name ?? 'none') . ", Room=" . ($slot->room ?? 'none') . PHP_EOL;
}

// Check academic year
$ay = \App\Models\AcademicYear::find(1);
echo "Academic Year: " . ($ay?->name ?? 'not found') . ", Active: " . ($ay?->is_active ?? 'n/a') . PHP_EOL;

// Check class section
$cs = \App\Modules\Academics\Models\ClassSection::find(1);
echo "Class Section: " . ($cs?->schoolClass?->name ?? 'none') . " - " . ($cs?->section?->name ?? 'none') . PHP_EOL;

// Check notifications
$notifications = \App\Modules\Notifications\Models\Notification::query()
    ->where('school_id', 1)
    ->where('status', 'sent')
    ->get();
echo "Sent notifications count: " . $notifications->count() . PHP_EOL;
foreach ($notifications as $n) {
    echo "Notification: " . $n->title . " (target: " . $n->target_type . ", users: " . $n->users->count() . ")" . PHP_EOL;
}

// Check user roles
$user = \App\Models\User::where('email', 'student@school.com')->first();
echo "User roles: " . $user->getRoleNames()->implode(', ') . PHP_EOL;
echo "User schools pivot: " . PHP_EOL;
foreach ($user->schools as $school) {
    echo "  - School: " . $school->name . ", Pivot: status=" . $school->pivot->status . ", is_primary=" . $school->pivot->is_primary . PHP_EOL;
}