<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();

        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Super Admin',
                'phone' => '+91 90000 00001',
                'password' => Hash::make('password'),
                'status' => 'active',
                'is_super_admin' => true,
                'current_school_id' => $school->id,
                'email_verified_at' => now(),
            ],
        );

        $superAdmin->schools()->syncWithoutDetaching([
            $school->id => [
                'designation' => 'Platform Administrator',
                'joined_at' => now()->toDateString(),
                'status' => 'active',
                'is_primary' => true,
            ],
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
        $superAdmin->assignRole('Super Admin');

        $schoolAdmin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'School Admin',
                'phone' => '+91 90000 00002',
                'password' => Hash::make('password'),
                'status' => 'active',
                'current_school_id' => $school->id,
                'email_verified_at' => now(),
            ],
        );

        $schoolAdmin->schools()->syncWithoutDetaching([
            $school->id => [
                'designation' => 'Administrator',
                'joined_at' => now()->toDateString(),
                'status' => 'active',
                'is_primary' => true,
            ],
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
        $schoolAdmin->assignRole('School Admin');
    }
}
