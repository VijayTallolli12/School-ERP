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
        return [
            'uuid' => (string) Str::uuid(),
            'school_id' => School::factory(),
            'admission_no' => strtoupper(fake()->unique()->bothify('ADM####')),
            'admission_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'first_name' => fake()->firstName(),
            'middle_name' => null,
            'last_name' => fake()->lastName(),
            'date_of_birth' => fake()->dateTimeBetween('-17 years', '-4 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female']),
            'blood_group' => fake()->optional()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'O+', 'O-']),
            'nationality' => 'Indian',
            'status' => 'active',
        ];
    }
}
