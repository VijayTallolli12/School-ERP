<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FixSuperAdmin extends Command
{
    protected $signature = 'fix:super-admin
                            {--email=superadmin@example.com : Email for the super admin}
                            {--password=password : Password for the super admin}';

    protected $description = 'Create or repair the super admin user with full permissions. Safe to run multiple times.';

    public function handle(): int
    {
        $email = $this->option('email');
        $password = $this->option('password');

        $school = School::query()->first();

        if (! $school) {
            $this->error('No school found. Run SchoolSeeder first (php artisan db:seed --class=SchoolSeeder).');

            return self::FAILURE;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

        $role = Role::query()->where('name', 'Super Admin')->first();

        if (! $role) {
            $this->error('Super Admin role does not exist. Run PermissionSeeder first (php artisan db:seed --class=PermissionSeeder).');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $user->update([
                'password' => Hash::make($password),
                'status' => 'active',
                'is_super_admin' => true,
                'current_school_id' => $school->id,
            ]);
            $this->line("Updated existing user #{$user->id} ({$email}).");
        } else {
            $user = User::query()->create([
                'uuid' => (string) Str::uuid(),
                'name' => 'Super Admin',
                'email' => $email,
                'phone' => '+91 90000 00001',
                'password' => Hash::make($password),
                'status' => 'active',
                'is_super_admin' => true,
                'current_school_id' => $school->id,
                'email_verified_at' => now(),
            ]);
            $user->schools()->syncWithoutDetaching([
                $school->id => [
                    'designation' => 'Platform Administrator',
                    'joined_at' => now()->toDateString(),
                    'status' => 'active',
                    'is_primary' => true,
                ],
            ]);
            $this->line("Created new user #{$user->id} ({$email}).");
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

        if (! $user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
            $this->line("Assigned 'Super Admin' role.");
        } else {
            $this->line("Already has 'Super Admin' role.");
        }

        $this->info("Super admin ({$email}) is ready. You can now log in.");

        return self::SUCCESS;
    }
}
