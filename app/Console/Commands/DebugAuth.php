<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class DebugAuth extends Command
{
    protected $signature = 'debug:auth';
    protected $description = 'Debug authentication and permission resolution';

    public function handle(): int
    {
        $this->info('=== FULL PERMISSION RESOLUTION DEBUG ===');

        $teacher = User::where('email', 'aisha.khan@example.com')->first();
        $schoolId = $teacher?->current_school_id;

        $this->line("Teacher: {$teacher->name} (ID: {$teacher->id})");
        $this->line("current_school_id: " . var_export($schoolId, true));

        $this->warn("\n--- STEP 1: Before SetSchoolContext middleware ---");
        $teamId = app(PermissionRegistrar::class)->getPermissionsTeamId();
        $this->line("getPermissionsTeamId(): " . var_export($teamId, true));

        $teacher->unsetRelation('roles');
        $teacher->unsetRelation('permissions');
        $this->line("Roles found (before): " . $teacher->getRoleNames());

        $this->warn("\n--- STEP 2: After SetSchoolContext middleware (setPermissionsTeamId) ---");
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
        $teamId = app(PermissionRegistrar::class)->getPermissionsTeamId();
        $this->line("getPermissionsTeamId(): " . var_export($teamId, true));

        $teacher->unsetRelation('roles');
        $teacher->unsetRelation('permissions');
        $this->line("Roles found (after): " . $teacher->getRoleNames());

        $this->warn("\n--- STEP 3: Permission check ---");
        $this->line("can('dashboard.view'): " . ($teacher->can('dashboard.view') ? 'YES' : 'NO'));

        $this->warn("\n--- STEP 4: Gate check ---");
        $gate = app(\Illuminate\Contracts\Auth\Access\Gate::class);
        $this->line("Gate check for dashboard.view: " . var_export($gate->forUser($teacher)->check('dashboard.view'), true));

        $this->warn("\n--- STEP 5: canAny check (same as middleware) ---");
        $this->line("canAny(['dashboard.view']): " . ($teacher->canAny(['dashboard.view']) ? 'YES' : 'NO'));

        $this->warn("\n--- STEP 6: All user types (with team ID set) ---");
        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@example.com'],
            ['name' => 'School Admin', 'email' => 'admin@example.com'],
            ['name' => 'Teacher 1', 'email' => 'aisha.khan@example.com'],
            ['name' => 'Parent 1', 'email' => 'john.doe@example.com'],
        ];
        foreach ($users as $u) {
            $user = User::where('email', $u['email'])->first();
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->current_school_id);
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
            $this->line("{$u['name']}: roles={$user->getRoleNames()} can(dashboard.view)=" . ($user->can('dashboard.view') ? 'YES' : 'NO') . " isSuperAdmin=" . ($user->isSuperAdmin() ? 'YES' : 'NO'));
        }

        $this->warn("\n--- STEP 7: Principal and Staff roles ---");
        $principalRole = Role::where('name', 'Principal')->first();
        $staffRole = Role::where('name', 'Staff')->first();
        if ($principalRole) {
            $this->line("Principal role ID: {$principalRole->id}, school_id: " . ($principalRole->school_id ?? 'NULL'));
            $this->line("Principal has dashboard.view: " . ($principalRole->hasPermissionTo('dashboard.view') ? 'YES' : 'NO'));
        }
        if ($staffRole) {
            $this->line("Staff role ID: {$staffRole->id}, school_id: " . ($staffRole->school_id ?? 'NULL'));
            $this->line("Staff has dashboard.view: " . ($staffRole->hasPermissionTo('dashboard.view') ? 'YES' : 'NO'));
        }

        $this->warn("\n--- STEP 8: Session school_id persistence ---");
        session(['school_id' => 1]);
        $this->line("Session school_id: " . var_export(session('school_id'), true));

        $this->warn("\n--- STEP 9: LoginController redirect check ---");
        $parent = User::where('email', 'john.doe@example.com')->first();
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        $parent->unsetRelation('roles');
        $this->line("Parent hasRole('Parent') with team=null: " . ($parent->hasRole('Parent') ? 'YES' : 'NO'));
        $this->line("(This shows why LoginController never finds Parent role correctly)");

        return Command::SUCCESS;
    }
}
