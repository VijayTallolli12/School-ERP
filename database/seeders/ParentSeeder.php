<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

        $parents = [
            [
                'first_name' => 'Rajesh',
                'last_name' => 'Verma',
                'email' => 'parent@school.com',
                'phone' => '+91 98765 43210',
                'occupation' => 'Engineer',
                'address' => '123 Main St, Demo City',
                'status' => 'active',
                'student_admission_no' => 'ADM0001',
            ],
            [
                'first_name' => 'Nilesh',
                'last_name' => 'Patel',
                'email' => 'nilesh.patel@example.com',
                'phone' => '+91 98765 43211',
                'occupation' => 'Doctor',
                'address' => '456 Oak Ave, Demo City',
                'status' => 'active',
                'student_admission_no' => 'ADM0002',
            ],
        ];

        foreach ($parents as $parentData) {
            $guardianEmail = $parentData['email'];

            $guardian = Guardian::query()->updateOrCreate(
                ['school_id' => $school->id, 'email' => $guardianEmail],
                [
                    'uuid' => (string) Str::uuid(),
                    'school_id' => $school->id,
                    'first_name' => $parentData['first_name'],
                    'last_name' => $parentData['last_name'],
                    'email' => $guardianEmail,
                    'phone' => $parentData['phone'],
                    'occupation' => $parentData['occupation'],
                    'address' => $parentData['address'],
                    'status' => $parentData['status'],
                ]
            );

            $user = User::query()->updateOrCreate(
                ['email' => $guardianEmail],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $parentData['first_name'].' '.$parentData['last_name'],
                    'phone' => $parentData['phone'],
                    'password' => Hash::make('password'),
                    'current_school_id' => $school->id,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
            $user->assignRole('Parent');
            $user->schools()->syncWithoutDetaching([$school->id => ['status' => 'active', 'is_primary' => true]]);

            if (!$guardian->user_id) {
                $guardian->update(['user_id' => $user->id]);
            }

            $student = Student::query()
                ->where('school_id', $school->id)
                ->where('admission_no', $parentData['student_admission_no'])
                ->first();

            if ($student) {
                $guardian->students()->syncWithoutDetaching([
                    $student->id => [
                        'relationship' => 'father',
                        'is_primary' => true,
                    ],
                ]);
            }
        }
    }
}
