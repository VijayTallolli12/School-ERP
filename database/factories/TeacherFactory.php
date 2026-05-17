<?php

namespace Database\Factories;

use App\Modules\Teachers\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'school_id' => 1,
            'employee_id' => $this->faker->unique()->bothify('T-####'),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional()->firstName(),
            'last_name' => $this->faker->lastName(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'date_of_birth' => $this->faker->date(),
            'qualification' => 'B.Ed',
            'experience_years' => $this->faker->numberBetween(1, 20),
            'joining_date' => $this->faker->date(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'address' => $this->faker->address(),
            'status' => 'active',
        ];
    }
}
