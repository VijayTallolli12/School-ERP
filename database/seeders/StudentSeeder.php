<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        $academicYear = AcademicYear::query()->where('school_id', $school->id)->where('is_active', true)->firstOrFail();
        $classSections = ClassSection::query()->where('school_id', $school->id)->get();

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

        $students = [
            [
                'admission_no' => 'ADM0001',
                'first_name' => 'Arjun',
                'last_name' => 'Verma',
                'email' => 'student@school.com',
            ],
            [
                'admission_no' => 'ADM0002',
                'first_name' => 'Priya',
                'last_name' => 'Patel',
                'email' => 'priya.patel@example.com',
            ],
            [
                'admission_no' => 'ADM0003',
                'first_name' => 'Rohit',
                'last_name' => 'Sharma',
                'email' => 'rohit.sharma@example.com',
            ],
        ];

        foreach ($students as $index => $data) {
            $student = Student::query()->updateOrCreate(
                ['school_id' => $school->id, 'admission_no' => $data['admission_no']],
                [
                    'uuid' => (string) Str::uuid(),
                    'school_id' => $school->id,
                    'admission_date' => now()->subYears(2)->toDateString(),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'date_of_birth' => now()->subYears(10 + $index)->toDateString(),
                    'gender' => $index % 2 === 0 ? 'male' : 'female',
                    'blood_group' => 'O+',
                    'nationality' => 'Indian',
                    'status' => 'active',
                ]
            );

            StudentSession::query()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'class_section_id' => $classSections[$index % $classSections->count()]->id,
                    'roll_no' => (string) ($index + 1),
                    'joined_on' => $student->admission_date,
                    'status' => 'active',
                ]
            );

            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $data['first_name'].' '.$data['last_name'],
                    'password' => Hash::make('password'),
                    'current_school_id' => $school->id,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
            $user->assignRole('Student');
            $user->schools()->syncWithoutDetaching([$school->id => ['status' => 'active', 'is_primary' => true]]);

            $student->update(['user_id' => $user->id]);
        }
    }
}
