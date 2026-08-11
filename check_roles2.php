<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check roles
$roles = \Spatie\Permission\Models\Role::all();
echo "All roles:" . PHP_EOL;
foreach ($roles as $role) {
    echo "  - " . $role->name . " (guard: " . $role->guard_name . ", school_id: " . ($role->school_id ?? 'null') . ")" . PHP_EOL;
}

// Check user roles again
$user = \App\Models\User::where('email', 'student@school.com')->first();
echo "User roles (before): " . $user->getRoleNames()->implode(', ') . PHP_EOL;

// Assign Student role with school_id
$schoolId = $user->current_school_id;
app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

if (!$user->hasRole('Student')) {
    $user->assignRole('Student');
    echo "Assigned Student role!" . PHP_EOL;
}

echo "User roles (after): " . $user->getRoleNames()->implode(', ') . PHP_EOL;