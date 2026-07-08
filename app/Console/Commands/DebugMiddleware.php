<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Http\Kernel;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Exceptions\UnauthorizedException;
use App\Models\User;

class DebugMiddleware extends Command
{
    protected $signature = 'debug:middleware';
    protected $description = 'Debug middleware and permission resolution';

    public function handle(): int
    {
        $this->info('=== MIDDLEWARE AND PERMISSION DEBUG ===');

        // Check global middleware
        $this->warn("\n--- Middleware configuration ---");
        $kernel = app(Kernel::class);
        $middlewareGroups = $kernel->getMiddlewareGroups();
        $this->line("Web group middleware: " . json_encode($middlewareGroups['web'] ?? []));
        $this->line("Route middleware: " . json_encode($kernel->getRouteMiddleware()));

        // Check if school middleware runs before permission in the actual middleware pipeline
        $this->warn("\n--- Simulating the exact middleware pipeline ---");

        $this->warn("\n--- Step-by-step: LoginController redirect ---");
        // Simulating what the LoginController does
        $teacher = User::where('email', 'aisha.khan@example.com')->first();
        $this->line("Teacher current_school_id: " . var_export($teacher->current_school_id, true));

        // Test if the teacher user's school relation is loaded
        $teacher->load('schools');
        $this->line("Teacher school count: " . $teacher->schools->count());
        foreach ($teacher->schools as $s) {
            $this->line("  School ID: {$s->id}, Pivot status: {$s->pivot->status}");
        }

        // Test school resolver chain
        $this->warn("\n--- Testing resolveSchoolId logic ---");
        
        // Step 1: session school_id (null during new login)
        $sessionSchoolId = session('school_id');
        $this->line("session('school_id'): " . var_export($sessionSchoolId, true));
        
        // Step 2: current_school_id
        $this->line("current_school_id: " . var_export($teacher->current_school_id, true));
        
        // Step 3: schools() with active pivot
        $activeSchool = $teacher->schools()->wherePivot('status', 'active')->first();
        $this->line("Active school from pivot: " . ($activeSchool ? $activeSchool->id : 'NONE'));

        // FULL HTTP STACK SIMULATION
        $this->warn("\n--- FULL REQUEST SIMULATION ---");

        // Reset everything
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        
        // Step A: User is authenticated (from session)
        // User model is loaded, no team ID set
        
        // Step B: School middleware runs
        $schoolId = $teacher->current_school_id;
        $this->line("School middleware resolves school ID: " . ($schoolId ?? 'NULL'));
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
        $this->line("After setPermissionsTeamId: " . var_export(app(PermissionRegistrar::class)->getPermissionsTeamId(), true));
        
        // Step C: Permission middleware runs
        $teacher->unsetRelation('roles');
        $teacher->unsetRelation('permissions');
        $result = $teacher->canAny(['dashboard.view']);
        $this->line("canAny(['dashboard.view']) result: " . ($result ? 'YES' : 'NO'));
        
        if (!$result) {
            $this->warn("   => This is where the 403 comes from!");
            $this->line("   Roles: " . $teacher->getRoleNames());
            $this->line("   Permissions: " . $teacher->getAllPermissions()->pluck('name')->implode(', '));
        }

        $this->warn("\n--- Verification ---");
        $this->info("If canAny returned YES above, the permission resolution is correct.");
        $this->info("If canAny returned NO, there's a deeper issue with the Spatie configuration.");

        return Command::SUCCESS;
    }
}
