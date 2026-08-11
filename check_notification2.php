<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'student@school.com')->first();

// Refresh roles
$user->unsetRelation('roles');
echo "User roles (refreshed): " . $user->getRoleNames()->implode(', ') . PHP_EOL;

// Check notification_user pivot for this user
$notification = \App\Modules\Notifications\Models\Notification::where('title', 'Welcome to Demo Public School')->first();
if ($notification) {
    $pivot = $notification->users()->where('user_id', $user->id)->first();
    echo "Pivot record exists: " . ($pivot ? 'YES' : 'NO') . PHP_EOL;
    if ($pivot) {
        echo "Pivot: is_read=" . $pivot->is_read . ", delivery=" . $pivot->delivery_status . PHP_EOL;
    }
}

// Check bellQuery directly
$repo = app(\App\Modules\Notifications\Repositories\NotificationRepository::class);
$query = $repo->bellQuery($user->id);
echo "Bell query count: " . $query->count() . PHP_EOL;

// Check what the query looks like
$query2 = \App\Modules\Notifications\Models\Notification::query()
    ->where('status', 'sent')
    ->whereHas('users', fn ($q) => $q->where('notification_user.user_id', $user->id))
    ->with(['users' => fn ($q) => $q->where('notification_user.user_id', $user->id)])
    ->latest()
    ->limit(10);
echo "Direct query count: " . $query2->count() . PHP_EOL;

// Test creating a new notification for students target type
$service = app(\App\Modules\Notifications\Services\NotificationService::class);

// Create test notification targeting students
$notification = \App\Modules\Notifications\Models\Notification::create([
    'school_id' => 1,
    'title' => 'Test Student Notification',
    'message' => 'This is a test for students',
    'type' => 'announcement',
    'priority' => 'medium',
    'status' => 'sent',
    'target_type' => 'students',
    'channel' => 'in_app',
    'created_by' => 1,
    'updated_by' => 1,
    'sent_at' => now(),
]);

echo "Created test notification: " . $notification->id . PHP_EOL;

// Now check bellData
$bellData = $service->bellData($user->id);
echo "Bell data unread count: " . $bellData['unread_count'] . PHP_EOL;
echo "Bell data notifications: " . count($bellData['notifications']) . PHP_EOL;
foreach ($bellData['notifications'] as $n) {
    echo "  - " . $n['title'] . " (read: " . ($n['is_read'] ? 'yes' : 'no') . ")" . PHP_EOL;
}