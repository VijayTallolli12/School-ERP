<?php

namespace Database\Factories;

use App\Models\School;
use App\Modules\Academics\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Section> */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        $letter = fake()->unique()->randomElement(['A', 'B', 'C', 'D']);

        return [
            'school_id' => School::factory(),
            'name' => 'Section '.$letter,
            'code' => $letter,
            'capacity' => 40,
            'status' => 'active',
        ];
    }
}
