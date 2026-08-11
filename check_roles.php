<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check notification_user pivot
$notification = \App\Modules\Notifications\Models\Notification::where('title', 'Welcome to Demo Public School')->first();
if ($notification) {
    echo "Notification ID: " . $notification->id . PHP_EOL;
    echo "Target type: " . $notification->target_type . PHP_EOL;
    echo "Users attached: " . $notification->users->count() . PHP_EOL;
    foreach ($notification->users as $user) {
        echo "  - User: " . $user->name . " (" . $user->email . "), Roles: " . $user->getRoleNames()->implode(', ') . ", Pivot: is_read=" . ($user->pivot->is_read ?? 'n/a') . ", delivery=" . ($user->pivot->delivery_status ?? 'n/a') . PHP_EOL;
    }
}

// Check all users with Student role
$studentUsers = \App\Models\User::role('Student')->get();
echo "Users with Student role: " . $studentUsers->count() . PHP_EOL;
foreach ($studentUsers as $u) {
    echo "  - " . $u->name . " (" . $u->email . ")" . PHP_EOL;
}

// Check all users with Parent role
$parentUsers = \App\Models\User::role('Parent')->get();
echo "Users with Parent role: " . $parentUsers->count() . PHP_EOL;
foreach ($parentUsers as $u) {
    echo "  - " . $u->name . " (" . $u->email . ")" . PHP_EOL;
}

// Check the specific user
$user = \App\Models\User::where('email', 'student@school.com')->first();
echo "User roles (direct): " . $user->getRoleNames()->implode(', ') . PHP_EOL;
echo "User has Student role: " . ($user->hasRole('Student') ? 'YES' : 'NO') . PHP_EOL;

// Assign Student role
if (!$user->hasRole('Student')) {
    $user->assignRole('Student');
    echo "Assigned Student role!" . PHP_EOL;
}

// Check notification dispatch logic
$service = app(\App\Modules\Notifications\Services\NotificationService::class);
$schoolId = app(\App\Core\Tenant\SchoolContext::class)->id();
echo "School Context ID: " . $schoolId . PHP_EOL;

// Test resolveTargetUserIds
$targetTypes = ['all', 'students', 'parents', 'teachers'];
foreach ($targetTypes as $type) {
    $ids = $service->resolveTargetUserIds($type);
    echo "Target type '$type': " . count($ids) . " users" . PHP_EOL;
}