<?php

namespace Database\Factories;

use App\Models\School;
use App\Modules\Academics\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subject> */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['English', 'Mathematics', 'Science', 'Social Studies', 'Computer Science', 'Hindi']);

        return [
            'school_id' => School::factory(),
            'name' => $name,
            'code' => strtoupper(fake()->unique()->bothify('SUB###')),
            'type' => 'core',
            'credit_hours' => fake()->numberBetween(3, 6),
            'status' => 'active',
        ];
    }
}
