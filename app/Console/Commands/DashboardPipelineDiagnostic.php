<?php

namespace App\Console\Commands;

use App\Core\Tenant\SchoolContext;
use App\Http\Middleware\SetSchoolContext;
use App\Models\User;
use App\Modules\Dashboard\Services\DashboardFactory;
use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class DashboardPipelineDiagnostic extends Command
{
    protected $signature = 'debug:dashboard-pipeline {email? : User email to diagnose}';

    protected $description = 'Run full dashboard pipeline diagnostic for a Teacher login';

    private array $log = [];

    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask('Enter teacher email', 'aisha.khan@example.com');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User not found: {$email}");
            return Command::FAILURE;
        }

        $this->info("=== DASHBOARD PIPELINE DIAGNOSTIC ===");
        $this->line("Diagnosing user: {$user->name} ({$user->email}) ID: {$user->id}");
        $this->line("");

        // ================================================================
        // STAGE 1: AFTER LOGIN (simulating LoginController)
        // ================================================================
        $this->stage1($user);

        // ================================================================
        // STAGE 2: BEFORE DashboardFactory::make()
        // ================================================================
        $this->stage2($user);

        // ================================================================
        // STAGE 3: INSIDE DashboardFactory::make()
        // ================================================================
        $this->stage3($user);

        // ================================================================
        // STAGE 4: IF NO BUILDER (handled inside stage3)
        // ================================================================

        // ================================================================
        // STAGE 5: VERIFY DATABASE
        // ================================================================
        $this->stage5($user);

        // ================================================================
        // STAGE 6: VERIFY MIDDLEWARE
        // ================================================================
        $this->stage6();

        // ================================================================
        // STAGE 7: VERIFY CACHING
        // ================================================================
        $this->stage7($user);

        // ================================================================
        // Write Report
        // ================================================================
        $this->writeReport($user);

        return Command::SUCCESS;
    }

    private function log(string $section, string $key, mixed $value): void
    {
        $this->log[] = [
            'section' => $section,
            'key' => $key,
            'value' => $this->formatValue($value),
        ];
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if ($value === true) {
            return 'true';
        }
        if ($value === false) {
            return 'false';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        if (is_object($value) && method_exists($value, 'toArray')) {
            return json_encode($value->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        if (is_object($value)) {
            return (string) $value;
        }
        return (string) $value;
    }

    // ====================================================================
    // STAGE 1
    // ====================================================================
    private function stage1(User $user): void
    {
        $this->warn("\n====================================================");
        $this->warn("STAGE 1: AFTER LOGIN (LoginController)");
        $this->warn("====================================================\n");

        $this->line("User ID: {$user->id}");
        $this->line("Email: {$user->email}");
        $this->line("Current School ID: " . var_export($user->current_school_id, true));

        $sessionSchoolId = session('school_id');
        $this->line("Session school_id: " . var_export($sessionSchoolId, true));

        // Reload SchoolContext to match what's in the app
        $currentSchoolId = app(SchoolContext::class)->id();
        $this->line("current_school_id (SchoolContext): " . var_export($currentSchoolId, true));

        $teamId = app(PermissionRegistrar::class)->getPermissionsTeamId();
        $this->line("getPermissionsTeamId(): " . var_export($teamId, true));

        // Roles - BEFORE setting team ID (simulating what happens in LoginController without context)
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');
        $this->line("Roles (before context): " . $user->roles->pluck('name')->implode(', '));
        $this->line("Role Names (before context): " . $user->getRoleNames()->implode(', '));
        $this->line("Permissions (before context): " . $user->getAllPermissions()->pluck('name')->implode(', '));
        $this->line("hasRole('Teacher') (before context): " . ($user->hasRole('Teacher') ? 'true' : 'false'));

        // Now SIMULATE what LoginController does:
        $this->line("");
        $this->line("--- After SetSchoolContext::applySchoolContext() ---");
        $appliedId = SetSchoolContext::applySchoolContext($user, request());
        $this->line("applySchoolContext returned: " . var_export($appliedId, true));

        $teamIdAfter = app(PermissionRegistrar::class)->getPermissionsTeamId();
        $this->line("getPermissionsTeamId() after apply: " . var_export($teamIdAfter, true));

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');
        $this->line("Roles (after context): " . $user->roles->pluck('name')->implode(', '));
        $this->line("Role Names (after context): " . $user->getRoleNames()->implode(', '));
        $this->line("Permissions (after context): " . $user->getAllPermissions()->pluck('name')->implode(', '));
        $this->line("hasRole('Teacher') (after context): " . ($user->hasRole('Teacher') ? 'true' : 'false'));

        $this->log('1. AFTER LOGIN', 'User ID', $user->id);
        $this->log('1. AFTER LOGIN', 'Email', $user->email);
        $this->log('1. AFTER LOGIN', 'Current School ID (DB column)', $user->current_school_id);
        $this->log('1. AFTER LOGIN', 'Session school_id', $sessionSchoolId);
        $this->log('1. AFTER LOGIN', 'SchoolContext::id()', $currentSchoolId);
        $this->log('1. AFTER LOGIN', 'getPermissionsTeamId() before apply', $teamId);
        $this->log('1. AFTER LOGIN', 'Roles (before context)', $user->roles->pluck('name')->implode(', '));
        $this->log('1. AFTER LOGIN', 'Role Names (before context)', $user->getRoleNames()->implode(', '));
        $this->log('1. AFTER LOGIN', 'Permissions (before context)', $user->getAllPermissions()->pluck('name')->implode(', '));
        $this->log('1. AFTER LOGIN', 'hasRole(Teacher) before context', $user->hasRole('Teacher') ? 'true' : 'false');
        $this->log('1. AFTER LOGIN', 'applySchoolContext returned', $appliedId);
        $this->log('1. AFTER LOGIN', 'getPermissionsTeamId() after apply', $teamIdAfter);
        $this->log('1. AFTER LOGIN', 'Roles (after context)', $user->roles->pluck('name')->implode(', '));
        $this->log('1. AFTER LOGIN', 'Role Names (after context)', $user->getRoleNames()->implode(', '));
        $this->log('1. AFTER LOGIN', 'Permissions (after context)', $user->getAllPermissions()->pluck('name')->implode(', '));
        $this->log('1. AFTER LOGIN', 'hasRole(Teacher) after context', $user->hasRole('Teacher') ? 'true' : 'false');
    }

    // ====================================================================
    // STAGE 2
    // ====================================================================
    private function stage2(User $user): void
    {
        $this->warn("\n====================================================");
        $this->warn("STAGE 2: BEFORE DashboardFactory::make()");
        $this->warn("====================================================\n");

        $contextId = app(SchoolContext::class)->id();
        $teamId = app(PermissionRegistrar::class)->getPermissionsTeamId();

        $this->line("SchoolContext ID: " . var_export($contextId, true));
        $this->line("getPermissionsTeamId(): " . var_export($teamId, true));

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');
        $this->line("Roles: " . $user->roles->pluck('name')->implode(', '));
        $this->line("Role Names: " . $user->getRoleNames()->implode(', '));
        $this->line("Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', '));

        // Dump ROLE_PRIORITY via reflection
        $ref = new \ReflectionClass(DashboardFactory::class);
        $rolePriority = $ref->getReflectionConstant('ROLE_PRIORITY')?->getValue();
        $this->line("ROLE_PRIORITY: " . json_encode($rolePriority, JSON_PRETTY_PRINT));

        $this->log('2. BEFORE DashboardFactory::make()', 'SchoolContext ID', $contextId);
        $this->log('2. BEFORE DashboardFactory::make()', 'getPermissionsTeamId()', $teamId);
        $this->log('2. BEFORE DashboardFactory::make()', 'Roles', $user->roles->pluck('name')->implode(', '));
        $this->log('2. BEFORE DashboardFactory::make()', 'Role Names', $user->getRoleNames()->implode(', '));
        $this->log('2. BEFORE DashboardFactory::make()', 'Permissions', $user->getAllPermissions()->pluck('name')->implode(', '));
        $this->log('2. BEFORE DashboardFactory::make()', 'ROLE_PRIORITY', json_encode($rolePriority));
    }

    // ====================================================================
    // STAGE 3
    // ====================================================================
    private function stage3(User $user): void
    {
        $this->warn("\n====================================================");
        $this->warn("STAGE 3: INSIDE DashboardFactory::make()");
        $this->warn("====================================================\n");

        $ref = new \ReflectionClass(DashboardFactory::class);
        $rolePriority = $ref->getReflectionConstant('ROLE_PRIORITY')?->getValue();

        $builderSelected = null;
        $reasons = [];

        foreach ($rolePriority as $role => $builderClass) {
            $hasRole = $user->hasRole($role);

            $this->line("Checking role: {$role} -> hasRole(): " . ($hasRole ? 'true' : 'false'));

            if ($hasRole) {
                $this->line("  => SELECTED builder: {$builderClass}");
                $builderSelected = $role;
            } else {
                // Determine WHY it returned false
                $reason = $this->diagnoseWhyHasRoleFalse($user, $role);
                $reasons[$role] = $reason;
                $this->line("  => Reason: {$reason}");
            }
        }

        $this->log('3. INSIDE DashboardFactory::make()', 'ROLE_PRIORITY iteration', 'see above');
        foreach ($rolePriority as $role => $builderClass) {
            $this->log('3. INSIDE DashboardFactory::make()', "hasRole({$role})", $user->hasRole($role) ? 'true' : 'false');
            if (isset($reasons[$role])) {
                $this->log('3. INSIDE DashboardFactory::make()', "Reason {$role} failed", $reasons[$role]);
            }
        }
        $this->log('3. INSIDE DashboardFactory::make()', 'Builder selected', $builderSelected ?? 'NONE');

        if ($builderSelected === null) {
            $this->stage4($user, $reasons);
        }
    }

    private function diagnoseWhyHasRoleFalse(User $user, string $roleName): string
    {
        // Check 1: Does the role exist at all?
        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            return "Role '{$roleName}' does not exist in the roles table.";
        }

        // Check 2: Is the team ID set?
        $teamId = app(PermissionRegistrar::class)->getPermissionsTeamId();
        if (! $teamId) {
            return "Permissions team ID is NULL - Spatie cannot scope roles without a team ID.";
        }

        // Check 3: Is the role assigned to this user in model_has_roles?
        $assignment = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->where('model_id', $user->getKey())
            ->where('model_type', get_class($user))
            ->first();

        if (! $assignment) {
            return "User is NOT assigned role '{$roleName}' in model_has_roles table.";
        }

        // Check 4: Does the assignment match the current team ID?
        if ($assignment->school_id != $teamId) {
            return "Role '{$roleName}' is assigned to school_id={$assignment->school_id} but current team_id={$teamId}. Team ID mismatch!";
        }

        // Check 5: Cached roles might be stale
        return "Role '{$roleName}' exists, is assigned, team ID matches ({$teamId}), but hasRole() still returned false. Possibly stale permission cache. Try running: php artisan cache:forget spatie.permission.cache";
    }

    // ====================================================================
    // STAGE 4
    // ====================================================================
    private function stage4(User $user, array $reasons): void
    {
        $this->warn("\n====================================================");
        $this->warn("STAGE 4: NO BUILDER FOUND — DETAILED DIAGNOSIS");
        $this->warn("====================================================\n");

        $teamId = app(PermissionRegistrar::class)->getPermissionsTeamId();
        $contextId = app(SchoolContext::class)->id();

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        $this->line("User ID: {$user->id}");
        $this->line("Email: {$user->email}");
        $this->line("Current Team ID (getPermissionsTeamId): " . var_export($teamId, true));
        $this->line("Current School (SchoolContext): " . var_export($contextId, true));
        $this->line("Current School (DB column): " . var_export($user->current_school_id, true));
        $this->line("Roles: " . $user->roles->pluck('name')->implode(', '));
        $this->line("Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', '));
        $this->line("Role Names: " . $user->getRoleNames()->implode(', '));

        $ref = new \ReflectionClass(DashboardFactory::class);
        $rolePriority = $ref->getReflectionConstant('ROLE_PRIORITY')?->getValue();
        $this->line("ROLE_PRIORITY: " . json_encode($rolePriority, JSON_PRETTY_PRINT));

        $this->line("");
        $this->line("--- Reason each hasRole() returned false ---");
        foreach ($reasons as $role => $reason) {
            $this->line("  {$role}: {$reason}");
        }

        $this->log('4. NO BUILDER FOUND', 'User ID', $user->id);
        $this->log('4. NO BUILDER FOUND', 'Email', $user->email);
        $this->log('4. NO BUILDER FOUND', 'Current Team ID', $teamId);
        $this->log('4. NO BUILDER FOUND', 'Current School (SchoolContext)', $contextId);
        $this->log('4. NO BUILDER FOUND', 'Current School (DB column)', $user->current_school_id);
        $this->log('4. NO BUILDER FOUND', 'Roles', $user->roles->pluck('name')->implode(', '));
        $this->log('4. NO BUILDER FOUND', 'Permissions', $user->getAllPermissions()->pluck('name')->implode(', '));
        $this->log('4. NO BUILDER FOUND', 'Role Names', $user->getRoleNames()->implode(', '));
        $this->log('4. NO BUILDER FOUND', 'ROLE_PRIORITY', json_encode($rolePriority));
        foreach ($reasons as $role => $reason) {
            $this->log('4. NO BUILDER FOUND', "Reason {$role} failed", $reason);
        }
    }

    // ====================================================================
    // STAGE 5
    // ====================================================================
    private function stage5(User $user): void
    {
        $this->warn("\n====================================================");
        $this->warn("STAGE 5: VERIFY DATABASE");
        $this->warn("====================================================\n");

        // 5a: model_has_roles
        $this->line("--- model_has_roles rows for user {$user->id} ---");
        $mhrRows = DB::table('model_has_roles')
            ->where('model_id', $user->getKey())
            ->where('model_type', get_class($user))
            ->get();
        if ($mhrRows->isEmpty()) {
            $this->line("  NO ROWS FOUND — user has NO role assignments in model_has_roles!");
        } else {
            foreach ($mhrRows as $row) {
                $roleName = DB::table('roles')->where('id', $row->role_id)->value('name') ?? 'UNKNOWN';
                $this->line("  role_id={$row->role_id} ({$roleName}), model_id={$row->model_id}, school_id={$row->school_id}");
            }
        }

        // 5b: roles
        $this->line("");
        $this->line("--- roles table ---");
        $allRoles = DB::table('roles')->get();
        foreach ($allRoles as $r) {
            $this->line("  id={$r->id}, name={$r->name}, guard_name={$r->guard_name}, school_id={$r->school_id}");
        }

        // 5c: school_user pivot
        $this->line("");
        $this->line("--- school_user for user {$user->id} ---");
        $suRows = DB::table('school_user')
            ->where('user_id', $user->getKey())
            ->get();
        if ($suRows->isEmpty()) {
            $this->line("  NO ROWS FOUND — user is not linked to any school in school_user!");
        } else {
            foreach ($suRows as $row) {
                $this->line("  school_id={$row->school_id}, user_id={$row->user_id}, status={$row->status}, is_primary={$row->is_primary}");
            }
        }

        // 5d: users table
        $this->line("");
        $this->line("--- users row for user {$user->id} ---");
        $userRow = DB::table('users')->where('id', $user->getKey())->first(['id', 'name', 'email', 'current_school_id', 'is_super_admin']);
        if ($userRow) {
            $this->line("  id={$userRow->id}, name={$userRow->name}, email={$userRow->email}, current_school_id={$userRow->current_school_id}, is_super_admin={$userRow->is_super_admin}");
        }

        $this->log('5. VERIFY DATABASE', 'model_has_roles rows', $mhrRows->toArray());
        $this->log('5. VERIFY DATABASE', 'roles table', $allRoles->toArray());
        $this->log('5. VERIFY DATABASE', 'school_user rows', $suRows->toArray());
        $this->log('5. VERIFY DATABASE', 'user row', $userRow ? (array) $userRow : 'NOT FOUND');
    }

    // ====================================================================
    // STAGE 6
    // ====================================================================
    private function stage6(): void
    {
        $this->warn("\n====================================================");
        $this->warn("STAGE 6: VERIFY MIDDLEWARE");
        $this->warn("====================================================\n");

        $this->line("Middleware registration (from bootstrap/app.php):");
        $this->line("  'school' => SetSchoolContext::class");
        $this->line("");

        $this->line("Route definition (from routes/web.php):");
        $this->line("  Route::middleware(['auth', 'school'])");
        $this->line("    ->prefix('admin')->as('admin.')");
        $this->line("    ->group(function () { require 'dashboard.php'; });");
        $this->line("");

        $this->line("Dashboard route (from routes/modules/dashboard.php):");
        $this->line("  Route::get('dashboard', DashboardController::class)->name('dashboard');");
        $this->line("");

        $this->line("Middleware execution order for GET /admin/dashboard:");
        $this->line("  1. auth      (Laravel auth middleware)");
        $this->line("  2. school    (SetSchoolContext - sets PermissionRegistrar team ID)");
        $this->line("  3. DashboardController::__invoke");
        $this->line("");

        $this->line("SetSchoolContext::handle() calls:");
        $this->line("  - app(SchoolContext::class)->set(schoolId)");
        $this->line("  - app(PermissionRegistrar::class)->setPermissionsTeamId(schoolId)");
        $this->line("  - session(['school_id' => schoolId])");
        $this->line("");

        $this->line("DashboardService::build() calls:");
        $this->line("  - schoolId = SchoolContext::id()");
        $this->line("  - app(PermissionRegistrar::class)->setPermissionsTeamId(schoolId)  [belt-and-suspenders]");
        $this->line("  - DashboardFactory::make(user)  [iterates ROLE_PRIORITY, checks hasRole()]");
        $this->line("  - builder->build(user, schoolId)");
        $this->line("");

        // Simulate the exact middleware flow
        $this->line("--- Current runtime state (simulating middleware execution) ---");
        $this->line("SchoolContext::id(): " . var_export(app(SchoolContext::class)->id(), true));
        $this->line("getPermissionsTeamId(): " . var_export(app(PermissionRegistrar::class)->getPermissionsTeamId(), true));

        $this->log('6. VERIFY MIDDLEWARE', 'Middleware execution order', 'auth -> school -> DashboardController');
        $this->log('6. VERIFY MIDDLEWARE', 'SetSchoolContext.handle operations', 'SchoolContext::set() + PermissionRegistrar::setPermissionsTeamId() + session()');
        $this->log('6. VERIFY MIDDLEWARE', 'DashboardService.build operations', 'SchoolContext::id() -> setPermissionsTeamId() -> DashboardFactory::make() -> builder->build()');
    }

    // ====================================================================
    // STAGE 7
    // ====================================================================
    private function stage7(User $user): void
    {
        $this->warn("\n====================================================");
        $this->warn("STAGE 7: VERIFY CACHING");
        $this->warn("====================================================\n");

        $this->line("Clearing all relevant caches...");
        $this->line("");

        // Clear permission cache
        $this->line("1. Clearing permission cache...");
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Cache::forget('spatie.permission.cache');
        $this->line("   Done.");

        // Clear config cache
        $this->line("2. Clearing config cache...");
        $this->call('config:clear');
        $this->line("   Done.");

        // Clear application cache
        $this->line("3. Clearing application cache...");
        $this->call('cache:clear');
        $this->line("   Done.");

        // Clear route cache
        $this->line("4. Clearing route cache...");
        $this->call('route:clear');
        $this->line("   Done.");

        $this->line("");

        // Now re-run stages 1-3 after cache clear
        $this->warn("--- Re-running diagnostics after cache clear ---");
        $this->line("");

        // Re-apply context
        SetSchoolContext::applySchoolContext($user, request());

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        $teamId = app(PermissionRegistrar::class)->getPermissionsTeamId();
        $this->line("getPermissionsTeamId() after cache clear: " . var_export($teamId, true));
        $this->line("Roles after cache clear: " . $user->roles->pluck('name')->implode(', '));
        $this->line("Role Names after cache clear: " . $user->getRoleNames()->implode(', '));
        $this->line("Permissions after cache clear: " . $user->getAllPermissions()->pluck('name')->implode(', '));
        $this->line("hasRole('Teacher') after cache clear: " . ($user->hasRole('Teacher') ? 'true' : 'false'));
        $this->line("hasRole('Super Admin') after cache clear: " . ($user->hasRole('Super Admin') ? 'true' : 'false'));
        $this->line("hasRole('School Admin') after cache clear: " . ($user->hasRole('School Admin') ? 'true' : 'false'));
        $this->line("hasRole('Principal') after cache clear: " . ($user->hasRole('Principal') ? 'true' : 'false'));
        $this->line("hasRole('Staff') after cache clear: " . ($user->hasRole('Staff') ? 'true' : 'false'));
        $this->line("hasRole('Parent') after cache clear: " . ($user->hasRole('Parent') ? 'true' : 'false'));

        // Re-test DashboardFactory
        $this->line("");
        $this->line("--- Re-testing DashboardFactory::make() after cache clear ---");
        $ref = new \ReflectionClass(DashboardFactory::class);
        $rolePriority = $ref->getReflectionConstant('ROLE_PRIORITY')?->getValue();

        foreach ($rolePriority as $role => $builderClass) {
            $hasRole = $user->hasRole($role);
            $this->line("hasRole('{$role}'): " . ($hasRole ? 'true' : 'false'));
            if ($hasRole) {
                $this->line("  => Would select: {$builderClass}");
            }
        }

        $this->log('7. VERIFY CACHING', 'Cache cleared', 'permission + config + application + route');
        $this->log('7. VERIFY CACHING', 'getPermissionsTeamId() after cache clear', $teamId);
        $this->log('7. VERIFY CACHING', 'Roles after cache clear', $user->roles->pluck('name')->implode(', '));
        $this->log('7. VERIFY CACHING', 'Role Names after cache clear', $user->getRoleNames()->implode(', '));
        $this->log('7. VERIFY CACHING', 'hasRole(Teacher) after cache clear', $user->hasRole('Teacher') ? 'true' : 'false');
    }

    // ====================================================================
    // WRITE REPORT
    // ====================================================================
    private function writeReport(User $user): void
    {
        $reportPath = getcwd() . '/DASHBOARD_PIPELINE_DIAGNOSTIC.md';
        $lines = [];

        $lines[] = '# Dashboard Pipeline Diagnostic';
        $lines[] = '';
        $lines[] = "**User:** {$user->name} ({$user->email})";
        $lines[] = "**User ID:** {$user->id}";
        $lines[] = "**Date:** " . now()->toDateTimeString();
        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';

        foreach ($this->log as $entry) {
            $lines[] = "## {$entry['section']}";
        $lines[] = '';
            $lines[] = "- **{$entry['key']}:** {$entry['value']}";

            // Check for root cause indicators
            $value = $entry['value'];
            if (str_contains($entry['key'], 'Reason') && str_contains($value, 'Team ID mismatch')) {
                $lines[] = "  ⚠️ **ROOT CAUSE CANDIDATE**";
            }
            if ($entry['key'] === 'hasRole(Teacher) after context' && $value === 'false') {
                $lines[] = "  ❌ **FAILURE: Teacher role not resolved even after school context applied**";
            }
            if ($entry['key'] === 'Builder selected' && $value === 'NONE') {
                $lines[] = "  ❌ **FAILURE: No dashboard builder selected — user will see 403**";
            }
            if (str_contains($entry['key'], 'model_has_roles') && str_contains($value, 'NO ROWS')) {
                $lines[] = "  ❌ **ROOT CAUSE: User has no role assignments in model_has_roles**";
            }
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## Root Cause Analysis';
        $lines[] = '';

        // Analyze the log for root causes
        $rootCauses = [];
        foreach ($this->log as $entry) {
            if (str_contains($entry['value'] ?? '', 'Team ID mismatch')) {
                $rootCauses[] = $entry['value'];
            }
            if (str_contains($entry['value'] ?? '', 'NO ROWS FOUND')) {
                $rootCauses[] = $entry['value'];
            }
            if (str_contains($entry['key'], 'hasRole(Teacher) after context') && $entry['value'] === 'false') {
                $rootCauses[] = 'hasRole("Teacher") returns false even after SetSchoolContext::applySchoolContext() sets the team ID.';
            }
            if ($entry['key'] === 'Builder selected' && $entry['value'] === 'NONE') {
                $rootCauses[] = 'DashboardFactory::make() did not find any matching role. All hasRole() checks returned false, leading to 403 abort.';
            }
        }

        if (empty($rootCauses)) {
            $lines[] = 'No definitive root cause identified from the diagnostic data. Review the full output above.';
        } else {
            $lines[] = '### Identified Issues';
            $lines[] = '';
            foreach ($rootCauses as $i => $cause) {
                $lines[] = "{$i}. {$cause}";
            }
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## Evidence';
        $lines[] = '';
        $lines[] = 'See above sections for complete runtime state at each pipeline stage.';

        $content = implode("\n", $lines);
        file_put_contents($reportPath, $content);
        $this->info("\nReport written to: {$reportPath}");
    }
}
