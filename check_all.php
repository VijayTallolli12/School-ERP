<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check notification_user pivot now
$notification = \App\Modules\Notifications\Models\Notification::where('title', 'Welcome to Demo Public School')->first();
if ($notification) {
    echo "Notification ID: " . $notification->id . PHP_EOL;
    echo "Target type: " . $notification->target_type . PHP_EOL;
    echo "Users attached: " . $notification->users->count() . PHP_EOL;
    foreach ($notification->users as $user) {
        echo "  - User: " . $user->name . " (" . $user->email . "), Roles: " . $user->getRoleNames()->implode(', ') . ", Pivot: is_read=" . ($user->pivot->is_read ?? 'n/a') . ", delivery=" . ($user->pivot->delivery_status ?? 'n/a') . PHP_EOL;
    }
}

// Check student user now
$user = \App\Models\User::where('email', 'student@school.com')->first();
echo "User roles: " . $user->getRoleNames()->implode(', ') . PHP_EOL;

// Test notification service bellData
$service = app(\App\Modules\Notifications\Services\NotificationService::class);
$bellData = $service->bellData($user->id);
echo "Bell data unread count: " . $bellData['unread_count'] . PHP_EOL;
echo "Bell data notifications: " . count($bellData['notifications']) . PHP_EOL;
foreach ($bellData['notifications'] as $n) {
    echo "  - " . $n['title'] . " (read: " . ($n['is_read'] ? 'yes' : 'no') . ")" . PHP_EOL;
}

// Test timetable
$student = $user->student;
if ($student) {
    echo "Student: " . $student->full_name . PHP_EOL;
    $session = $student->sessions()->where('status', 'active')->first();
    if ($session) {
        echo "Active Session: Class Section ID: " . $session->class_section_id . ", Academic Year ID: " . $session->academic_year_id . PHP_EOL;
        
        $slots = \App\Modules\Timetable\Models\TimetableSlot::query()
            ->where('class_section_id', $session->class_section_id)
            ->where('academic_year_id', $session->academic_year_id)
            ->with(['subject:id,name,code', 'teacher.user:id,name'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        echo "Timetable slots: " . $slots->count() . PHP_EOL;
        foreach ($slots as $slot) {
            echo "  Day " . $slot->day_of_week . ": " . ($slot->subject?->name ?? 'none') . " - " . ($slot->teacher?->user?->name ?? 'none') . " - Room: " . ($slot->room ?? 'none') . PHP_EOL;
        }
    } else {
        echo "No active session" . PHP_EOL;
    }
}

// Test dispatch logic
$targetTypes = ['all', 'students', 'parents', 'teachers'];
foreach ($targetTypes as $type) {
    $ids = $service->resolveTargetUserIds($type);
    echo "Target type '$type': " . count($ids) . " users" . PHP_EOL;
}