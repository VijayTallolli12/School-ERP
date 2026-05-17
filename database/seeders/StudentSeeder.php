<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        $academicYear = AcademicYear::query()->where('school_id', $school->id)->where('is_active', true)->firstOrFail();
        $classSections = ClassSection::query()->where('school_id', $school->id)->limit(5)->get();

        foreach (range(1, 12) as $index) {
            $student = Student::factory()->create([
                'school_id' => $school->id,
                'admission_no' => 'ADM'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
            ]);

            $student->sessions()->create([
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'class_section_id' => $classSections->random()->id,
                'roll_no' => (string) $index,
                'joined_on' => $student->admission_date,
                'status' => 'active',
            ]);

            $student->guardians()->create([
                'school_id' => $school->id,
                'relation' => fake()->randomElement(['Father', 'Mother', 'Guardian']),
                'name' => fake()->name(),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->safeEmail(),
                'occupation' => fake()->jobTitle(),
                'is_primary' => true,
                'can_pickup' => true,
            ]);
        }
    }
}
