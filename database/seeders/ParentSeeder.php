<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        School::query()->each(function (School $school) {

            $parents = [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+1234567890',
                    'occupation' => 'Engineer',
                    'address' => '123 Main St, City, State',
                    'status' => 'active',
                ],
                [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane.smith@example.com',
                    'phone' => '+1234567891',
                    'occupation' => 'Teacher',
                    'address' => '456 Oak Ave, City, State',
                    'status' => 'active',
                ],
            ];

            foreach ($parents as $parentData) {

                if (Guardian::where('email', $parentData['email'])->exists()) {
                    continue;
                }

                $parentData['school_id'] = $school->id;

                // FIX: Generate UUID
                $parentData['uuid'] = (string) Str::uuid();

                // Create user if not exists
                $user = User::where('email', $parentData['email'])->first();

                if (!$user) {
                    $user = User::factory()->create([
                        'name' => $parentData['first_name'].' '.$parentData['last_name'],
                        'email' => $parentData['email'],
                        'current_school_id' => $school->id,
                    ]);
                }

                // Assign Parent role with team context
                app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
                if (!$user->hasRole('Parent')) {
                    $user->assignRole('Parent');
                }

                $parentData['user_id'] = $user->id;

                $parent = Guardian::create($parentData);

                // Attach students safely
                $students = Student::query()
                    ->where('school_id', $school->id)
                    ->inRandomOrder()
                    ->take(rand(1,2))
                    ->get();

                foreach ($students as $index => $student) {

                    $parent->students()->attach(
                        $student->id,
                        [
                            'relationship' => $index === 0 ? 'father' : 'mother',
                            'is_primary' => $index === 0,
                        ]
                    );
                }
            }
        });
    }
}