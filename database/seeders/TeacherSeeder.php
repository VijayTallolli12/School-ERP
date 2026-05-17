<?php

namespace Database\Seeders;

use App\Models\School;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Teachers\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();

        $subjects = Subject::query()->where('school_id', $school->id)->get();
        $classSections = ClassSection::query()->where('school_id', $school->id)->get();

        $teachers = [
            [
                'employee_id' => 'T-1001',
                'first_name' => 'Aisha',
                'last_name' => 'Khan',
                'gender' => 'female',
                'qualification' => 'M.Sc. Mathematics',
                'experience_years' => 8,
                'joining_date' => now()->subYears(5)->toDateString(),
                'phone' => '9876543210',
                'email' => 'aisha.khan@example.com',
                'address' => '12 Rose Lane, Demo City',
                'status' => 'active',
            ],
            [
                'employee_id' => 'T-1002',
                'first_name' => 'Rahul',
                'last_name' => 'Mehta',
                'gender' => 'male',
                'qualification' => 'M.A. English',
                'experience_years' => 6,
                'joining_date' => now()->subYears(4)->toDateString(),
                'phone' => '9123456780',
                'email' => 'rahul.mehta@example.com',
                'address' => '8 Garden Street, Demo City',
                'status' => 'active',
            ],
            [
                'employee_id' => 'T-1003',
                'first_name' => 'Priya',
                'last_name' => 'Sharma',
                'gender' => 'female',
                'qualification' => 'M.Sc. Physics',
                'experience_years' => 10,
                'joining_date' => now()->subYears(7)->toDateString(),
                'phone' => '9988776655',
                'email' => 'priya.sharma@example.com',
                'address' => '221 Baker Street, Demo City',
                'status' => 'active',
            ],
        ];

        foreach ($teachers as $data) {
            $teacher = Teacher::query()->firstOrCreate([
                'school_id' => $school->id,
                'employee_id' => $data['employee_id'],
            ], array_merge($data, ['school_id' => $school->id]));

            $teacher->subjects()->sync($subjects->take(2)->pluck('id')->all());
            $teacher->classSections()->sync([
                $classSections->first()->id => [
                    'is_class_teacher' => true
                ]
            ]);
        }
    }
}
