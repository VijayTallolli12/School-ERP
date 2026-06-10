<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Parents\Models\Guardian;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FixParentRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:parent-roles
                            {--school-id= : Limit to a specific school ID}
                            {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign the Parent role to existing parent users who are missing it (with team context).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $schoolId = $this->option('school-id');
        $dryRun = (bool) $this->option('dry-run');

        // Find all users linked to guardian records that may be missing the Parent role
        $guardianQuery = Guardian::query()
            ->whereNotNull('user_id')
            ->with('user');

        if ($schoolId) {
            $guardianQuery->where('school_id', $schoolId);
        }

        $guardians = $guardianQuery->get();

        if ($guardians->isEmpty()) {
            $this->info('No parent records with user accounts found.');

            return self::SUCCESS;
        }

        $fixed = 0;
        $alreadyOk = 0;
        $skipped = 0;

        /** @var Guardian $guardian */
        foreach ($guardians as $guardian) {
            $user = $guardian->user;

            if (! $user) {
                $this->warn("Guardian #{$guardian->id} has user_id={$guardian->user_id} but user not found. Skipping.");
                $skipped++;
                continue;
            }

            $schoolId = $guardian->school_id;

            // Check if Parent role exists for this school
            $parentRole = Role::query()
                ->where('name', 'Parent')
                ->where('school_id', $schoolId)
                ->first();

            if (! $parentRole) {
                $this->warn("School #{$schoolId}: No 'Parent' role exists. Run PermissionSeeder first. Skipping user #{$user->id}.");
                $skipped++;
                continue;
            }

            // Set team context before checking/assigning role
            app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

            if ($user->hasRole('Parent')) {
                $alreadyOk++;
                continue;
            }

            if ($dryRun) {
                $this->line("[DRY RUN] Would assign 'Parent' role to user #{$user->id} ({$user->email}) for school #{$schoolId}");
            } else {
                $user->assignRole('Parent');
                $this->line("Assigned 'Parent' role to user #{$user->id} ({$user->email}) for school #{$schoolId}");
            }

            $fixed++;
        }

        $action = $dryRun ? 'would be fixed' : 'fixed';
        $this->info("Done: {$fixed} {$action}, {$alreadyOk} already OK, {$skipped} skipped.");

        return self::SUCCESS;
    }
}