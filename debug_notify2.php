<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'student@school.com')->first();

// Set school context
app(\App\Core\Tenant\SchoolContext::class)->set($user->current_school_id);
app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->current_school_id);

$user->unsetRelation('roles');
echo "User roles: " . $user->getRoleNames()->implode(', ') . PHP_EOL;

// Check the hasRole method
echo "Has Student role: " . ($user->hasRole('Student') ? 'YES' : 'NO') . PHP_EOL;

// Check the query that fails
try {
    $query = \App\Models\User::query()->whereHas('schools', fn ($q) => $q->whereKey(1));
    $studentQuery = (clone $query)->whereHas('roles', fn ($q) => $q->where('name', 'Student'));
    echo "Students count: " . $studentQuery->count() . PHP_EOL;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

// Check model_has_roles table
$roles = \Spatie\Permission\Models\Role::where('name', 'Student')->where('school_id', 1)->get();
echo "Student roles: " . $roles->count() . PHP_EOL;
foreach ($roles as $role) {
    echo "  Role: " . $role->name . ", school_id: " . $role->school_id . ", guard: " . $role->guard_name . PHP_EOL;
    $users = $role->users;
    echo "  Users: " . $users->count() . PHP_EOL;
    foreach ($users as $u) {
        echo "    - " . $u->name . " (" . $u->email . ")" . PHP_EOL;
    }
}

// Check notification dispatch
$notification = \App\Modules\Notifications\Models\Notification::find(2);
if ($notification) {
    echo "Notification 2: " . $notification->title . ", target_type: " . $notification->target_type . ", status: " . $notification->status . PHP_EOL;
    
    // Manually dispatch
    $service = app(\App\Modules\Notifications\Services\NotificationService::class);
    
    // Check if already dispatched
    $pivotExists = $notification->users()->where('user_id', $user->id)->exists();
    echo "Pivot exists for user: " . ($pivotExists ? 'YES' : 'NO') . PHP_EOL;
    
    // Try to manually attach
    $notification->users()->syncWithoutDetaching([
        $user->id => ['delivery_status' => 'delivered', 'created_at' => now(), 'updated_at' => now()],
    ]);
    echo "Manually attached user to notification" . PHP_EOL;
}

// Check bellData
$service = app(\App\Modules\Notifications\Services\NotificationService::class);
$bellData = $service->bellData($user->id);
echo "Bell data unread count: " . $bellData['unread_count'] . PHP_EOL;
echo "Bell data notifications: " . count($bellData['notifications']) . PHP_EOL;
foreach ($bellData['notifications'] as $n) {
    echo "  - " . $n['title'] . " (read: " . ($n['is_read'] ? 'yes' : 'no') . ")" . PHP_EOL;
}