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
        static $counter = 0;
        $counter++;

        $names = ['English', 'Mathematics', 'Science', 'Social Studies', 'Computer Science', 'Hindi'];
        $name = $names[($counter - 1) % count($names)];

        return [
            'school_id' => School::factory(),
            'name' => $name,
            'code' => 'SUB'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT),
            'type' => 'core',
            'credit_hours' => 4,
            'status' => 'active',
        ];
    }
}
