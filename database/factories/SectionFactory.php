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
        static $counter = 0;
        $counter++;

        $letter = ['A', 'B', 'C', 'D'][($counter - 1) % 4];

        return [
            'school_id' => School::factory(),
            'name' => 'Section '.$letter,
            'code' => $letter,
            'capacity' => 40,
            'status' => 'active',
        ];
    }
}
