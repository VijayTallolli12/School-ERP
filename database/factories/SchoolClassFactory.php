<?php

namespace Database\Factories;

use App\Models\School;
use App\Modules\Academics\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SchoolClass> */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 12);

        return [
            'school_id' => School::factory(),
            'name' => 'Class '.$number,
            'code' => 'CLS'.$number,
            'sort_order' => $number,
            'status' => 'active',
        ];
    }
}
