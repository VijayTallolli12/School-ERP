<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check parent user
$parentUser = \App\Models\User::where('email', 'parent@school.com')->first();
echo "Parent User: " . $parentUser->name . " (ID: " . $parentUser->id . ")" . PHP_EOL;

// Set school context
app(\App\Core\Tenant\SchoolContext::class)->set($parentUser->current_school_id);
app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($parentUser->current_school_id);

$parentUser->unsetRelation('roles');
echo "Parent roles: " . $parentUser->getRoleNames()->implode(', ') . PHP_EOL;

// Assign Parent role
if (!$parentUser->hasRole('Parent')) {
    $parentUser->assignRole('Parent');
    echo "Assigned Parent role!" . PHP_EOL;
}

$parentUser->unsetRelation('roles');
echo "Parent roles (after): " . $parentUser->getRoleNames()->implode(', ') . PHP_EOL;

// Check guardian
$guardian = $parentUser->guardian;
if ($guardian) {
    echo "Guardian: " . $guardian->full_name . " (ID: " . $guardian->id . ")" . PHP_EOL;
    echo "Guardian user_id: " . $guardian->user_id . PHP_EOL;
    echo "Guardian students: " . $guardian->students->count() . PHP_EOL;
} else {
    echo "No guardian linked" . PHP_EOL;
}

// Check notification for parents
$notification = \App\Modules\Notifications\Models\Notification::find(2);
if ($notification) {
    // Attach parent user
    $notification->users()->syncWithoutDetaching([
        $parentUser->id => ['delivery_status' => 'delivered', 'created_at' => now(), 'updated_at' => now()],
    ]);
    echo "Attached parent to notification" . PHP_EOL;
}

// Check bellData for parent
$service = app(\App\Modules\Notifications\Services\NotificationService::class);
$bellData = $service->bellData($parentUser->id);
echo "Parent Bell data unread count: " . $bellData['unread_count'] . PHP_EOL;
echo "Parent Bell data notifications: " . count($bellData['notifications']) . PHP_EOL;
foreach ($bellData['notifications'] as $n) {
    echo "  - " . $n['title'] . " (read: " . ($n['is_read'] ? 'yes' : 'no') . ")" . PHP_EOL;
}

// Check ParentApiController childCirculars method
$parentApiController = app(\App\Http\Controllers\Api\V1\ParentApiController::class);
// Just check the query it uses
$circulars = \App\Modules\Notifications\Models\Notification::query()
    ->where('target_type', 'parents')
    ->where('type', 'announcement')
    ->where('status', 'sent')
    ->with('creator:id,name')
    ->orderByDesc('id')
    ->get();
echo "Circulars for parents: " . $circulars->count() . PHP_EOL;