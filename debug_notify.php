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
echo "User current_school_id: " . $user->current_school_id . PHP_EOL;
echo "User schools: " . $user->schools->pluck('id')->implode(', ') . PHP_EOL;

// Check the resolveTargetUserIds logic manually
$schoolId = 1;
$query = \App\Models\User::query()->whereHas('schools', fn ($q) => $q->whereKey($schoolId));
echo "Users in school: " . $query->count() . PHP_EOL;

$studentQuery = (clone $query)->whereHas('roles', fn ($q) => $q->where('name', 'Student')->where('school_id', $schoolId));
echo "Users with Student role in school: " . $studentQuery->count() . PHP_EOL;
foreach ($studentQuery->get() as $u) {
    echo "  - " . $u->name . " (" . $u->email . ")" . PHP_EOL;
}

// Check notification 2
$notification = \App\Modules\Notifications\Models\Notification::find(2);
if ($notification) {
    echo "Notification 2: " . $notification->title . ", target_type: " . $notification->target_type . ", status: " . $notification->status . PHP_EOL;
    echo "Users attached: " . $notification->users->count() . PHP_EOL;
    foreach ($notification->users as $u) {
        echo "  - " . $u->name . " (" . $u->email . ")" . PHP_EOL;
    }
}

// Check bellQuery
$repo = app(\App\Modules\Notifications\Repositories\NotificationRepository::class);
$bellQuery = $repo->bellQuery($user->id);
echo "Bell query SQL: " . $bellQuery->toSql() . PHP_EOL;
echo "Bell query bindings: " . json_encode($bellQuery->getBindings()) . PHP_EOL;
echo "Bell query count: " . $bellQuery->count() . PHP_EOL;