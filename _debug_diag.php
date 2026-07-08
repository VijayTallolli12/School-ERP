<?php

echo "=== TEST 1: LoginController hasRole(Parent) check ===\n";
$user = App\Models\User::where('email', 'john.doe@example.com')->first();
echo 'Parent user ID: ' . $user->id . "\n";
echo 'Team ID (before set): ' . app(Spatie\Permission\PermissionRegistrar::class)->getPermissionsTeamId() . "\n";
echo 'hasRole(Parent): ' . ($user->hasRole('Parent') ? 'YES' : 'NO') . "\n";

echo "\n=== TEST 2: After setting team ID ===\n";
$schoolId = $user->current_school_id;
echo 'current_school_id: ' . $schoolId . "\n";
app(Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
echo 'Team ID (after set): ' . app(Spatie\Permission\PermissionRegistrar::class)->getPermissionsTeamId() . "\n";
$user->unsetRelation('roles');
echo 'hasRole(Parent): ' . ($user->hasRole('Parent') ? 'YES' : 'NO') . "\n";

echo "\n=== TEST 3: Teacher permission check ===\n";
$teacher = App\Models\User::where('email', 'aisha.khan@example.com')->first();
echo 'Teacher ID: ' . $teacher->id . "\n";
echo 'current_school_id: ' . $teacher->current_school_id . "\n";
app(Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
echo 'Team ID: ' . app(Spatie\Permission\PermissionRegistrar::class)->getPermissionsTeamId() . "\n";
echo 'Roles: ' . $teacher->getRoleNames() . "\n";
echo 'can(dashboard.view): ' . ($teacher->can('dashboard.view') ? 'YES' : 'NO') . "\n";
echo 'All permissions: ' . $teacher->getAllPermissions()->pluck('name')->implode(', ') . "\n";

echo "\n=== DATABASE STATE ===\n";
echo 'User count: ' . App\Models\User::count() . "\n";
echo 'School count: ' . App\Models\School::count() . "\n";

echo "\n=== model_has_roles ===\n";
$roles = DB::table('model_has_roles')->get();
foreach ($roles as $r) {
    echo '  user_id=' . $r->model_id . ' role_id=' . $r->role_id . ' school_id=' . ($r->school_id ?? 'NULL') . "\n";
}

echo "\n=== role_has_permissions (all) ===\n";
$roleHasPerms = DB::table('role_has_permissions')->get();
foreach ($roleHasPerms as $rp) {
    echo '  role_id=' . $rp->role_id . ' permission_id=' . $rp->permission_id . ' school_id=' . ($rp->school_id ?? 'NULL') . "\n";
}

echo "\n=== User-school relationships ===\n";
$pivot = DB::table('school_user')->get();
foreach ($pivot as $p) {
    echo '  user_id=' . $p->user_id . ' school_id=' . $p->school_id . ' status=' . $p->status . "\n";
}

echo "\n=== Roles table ===\n";
$allRoles = DB::table('roles')->get();
foreach ($allRoles as $role) {
    echo '  id=' . $role->id . ' name=' . $role->name . ' school_id=' . ($role->school_id ?? 'NULL') . "\n";
}

echo "\n=== Permissions for dashboard.view ===\n";
$perm = DB::table('permissions')->where('name', 'dashboard.view')->first();
echo 'dashboard.view id=' . $perm->id . "\n";

echo "\n=== Checking teacher user role IDs ===\n";
$teacher->load('roles');
foreach ($teacher->roles as $role) {
    echo 'Role: ' . $role->name . ' (id=' . $role->id . ', school_id=' . $role->pivot->school_id . ")\n";
    $hasPerm = DB::table('role_has_permissions')
        ->where('role_id', $role->id)
        ->where('permission_id', $perm->id)
        ->first();
    echo '  Has dashboard.view: ' . ($hasPerm ? 'YES (school_id=' . ($hasPerm->school_id ?? 'NULL') . ')' : 'NO') . "\n";
}
