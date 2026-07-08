<?php

echo "=== FULL PERMISSION RESOLUTION DEBUG ===\n\n";

$teacher = App\Models\User::where('email', 'aisha.khan@example.com')->first();
$schoolId = $teacher->current_school_id;

echo "Teacher: {$teacher->name} (ID: {$teacher->id})\n";
echo "current_school_id: " . var_export($schoolId, true) . "\n";

// Simulate what happens during the web request
echo "\n--- STEP 1: Before SetSchoolContext middleware ---\n";
$teamId = app(Spatie\Permission\PermissionRegistrar::class)->getPermissionsTeamId();
echo "getPermissionsTeamId(): " . var_export($teamId, true) . "\n";

// Force reload roles
$teacher->unsetRelation('roles');
$teacher->unsetRelation('permissions');
echo "Roles found (before): " . $teacher->getRoleNames() . "\n";

echo "\n--- STEP 2: After SetSchoolContext middleware (setPermissionsTeamId) ---\n";
app(Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
$teamId = app(Spatie\Permission\PermissionRegistrar::class)->getPermissionsTeamId();
echo "getPermissionsTeamId(): " . var_export($teamId, true) . "\n";

$teacher->unsetRelation('roles');
$teacher->unsetRelation('permissions');
echo "Roles found (after): " . $teacher->getRoleNames() . "\n";

echo "\n--- STEP 3: Permission check ---\n";
echo "can('dashboard.view'): " . ($teacher->can('dashboard.view') ? 'YES' : 'NO') . "\n";

// Verify the Gate before callback
echo "\n--- STEP 4: Gate before callback check ---\n";
$gate = app(\Illuminate\Contracts\Auth\Access\Gate::class);
echo "Gate check result for dashboard.view: " . var_export($gate->forUser($teacher)->check('dashboard.view'), true) . "\n";

echo "\n--- STEP 5: Check canAny (same as middleware) ---\n";
echo "canAny(['dashboard.view']): " . ($teacher->canAny(['dashboard.view']) ? 'YES' : 'NO') . "\n";

echo "\n--- STEP 6: Testing all user types with team ID set ---\n";
$users = [
    ['name' => 'Super Admin', 'email' => 'superadmin@example.com'],
    ['name' => 'School Admin', 'email' => 'admin@example.com'],
    ['name' => 'Teacher 1', 'email' => 'aisha.khan@example.com'],
    ['name' => 'Parent 1', 'email' => 'john.doe@example.com'],
];

foreach ($users as $u) {
    $user = App\Models\User::where('email', $u['email'])->first();
    app(Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->current_school_id);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');
    echo "{$u['name']}: roles=" . $user->getRoleNames() . " can(dashboard.view)=" . ($user->can('dashboard.view') ? 'YES' : 'NO') . " isSuperAdmin=" . ($user->isSuperAdmin() ? 'YES' : 'NO') . "\n";
}

echo "\n--- STEP 7: Check if Principal and Staff roles exist and have dashboard.view ---\n";
$principalRole = Spatie\Permission\Models\Role::where('name', 'Principal')->first();
$staffRole = Spatie\Permission\Models\Role::where('name', 'Staff')->first();
if ($principalRole) {
    echo "Principal role ID: {$principalRole->id}, school_id: " . ($principalRole->school_id ?? 'NULL') . "\n";
    echo "Principal has dashboard.view: " . ($principalRole->hasPermissionTo('dashboard.view') ? 'YES' : 'NO') . "\n";
}
if ($staffRole) {
    echo "Staff role ID: {$staffRole->id}, school_id: " . ($staffRole->school_id ?? 'NULL') . "\n";
    echo "Staff has dashboard.view: " . ($staffRole->hasPermissionTo('dashboard.view') ? 'YES' : 'NO') . "\n";
}

echo "\n--- STEP 8: Test $_SESSION['school_id'] persistence ---\n";
session(['school_id' => 1]);
echo "Session school_id after setting: " . var_export(session('school_id'), true) . "\n";

echo "\n--- STEP 9: Simulate the LoginController redirect check ---\n";
$parent = App\Models\User::where('email', 'john.doe@example.com')->first();
echo "Parent hasRole('Parent') with team=null: " . ($parent->hasRole('Parent') ? 'YES' : 'NO') . "\n";
echo "(This explains why LoginController never redirects to parent-portal correctly)\n";
