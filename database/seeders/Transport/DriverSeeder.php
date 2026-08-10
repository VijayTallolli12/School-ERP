<?php

namespace Database\Seeders\Transport;

use App\Models\School;
use App\Models\User;
use App\Modules\Transport\Models\Driver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();

        $drivers = [
            [
                'name' => 'Rajesh Kumar',
                'mobile' => '9876500001',
                'email' => 'rajesh.kumar@example.com',
                'license_number' => 'DL-2024-IND-001',
                'address' => '45 Station Road, Old Town, Demo City',
            ],
            [
                'name' => 'Suresh Patil',
                'mobile' => '9876500002',
                'email' => 'suresh.patil@example.com',
                'license_number' => 'DL-2024-IND-002',
                'address' => '102 Lake View Colony, Demo City',
            ],
            [
                'name' => 'Mahesh Gowda',
                'mobile' => '9876500003',
                'email' => 'mahesh.gowda@example.com',
                'license_number' => 'DL-2024-IND-003',
                'address' => '7 Market Square Road, Demo City',
            ],
            [
                'name' => 'Venkatesh Iyer',
                'mobile' => '9876500004',
                'email' => 'venkatesh.iyer@example.com',
                'license_number' => 'DL-2024-IND-004',
                'address' => '220 Airport Road, Demo City',
            ],
        ];

        foreach ($drivers as $data) {
            $user = User::factory()->create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['mobile'],
                'password' => Hash::make('password'),
                'current_school_id' => $school->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $user->assignRole('Driver');
            $user->schools()->syncWithoutDetaching([$school->id => ['status' => 'active', 'is_primary' => true]]);

            Driver::query()->create([
                'school_id' => $school->id,
                'user_id' => $user->id,
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'license_number' => $data['license_number'],
                'license_expiry_date' => now()->addYears(5)->toDateString(),
                'address' => $data['address'],
                'status' => 'active',
            ]);
        }
    }
}