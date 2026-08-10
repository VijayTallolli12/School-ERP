<?php

namespace Database\Factories;

use App\Models\School;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Student> */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        return [
            'uuid' => (string) Str::uuid(),
            'school_id' => School::factory(),

            'admission_no' => 'ADM'.str_pad((string) $counter, 4, '0', STR_PAD_LEFT),

            'admission_date' => now()->subYears(2)->toDateString(),

            'first_name' => 'Student',
            'middle_name' => null,
            'last_name' => 'Demo '.$counter,

            'date_of_birth' => now()->subYears(10)->toDateString(),

            'gender' => 'male',

            'blood_group' => 'O+',

            'nationality' => 'Indian',

            'status' => 'active',
        ];
    }
}