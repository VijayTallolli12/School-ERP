<?php

namespace Database\Factories;

use App\Models\School;
use App\Modules\Fees\Models\FeeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FeeCategory> */
class FeeCategoryFactory extends Factory
{
    protected $model = FeeCategory::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'code' => fake()->unique()->bothify('cat_????'),
            'name' => fake()->words(2, true),
            'description' => null,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
