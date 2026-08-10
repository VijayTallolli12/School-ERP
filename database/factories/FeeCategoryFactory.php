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
        static $counter = 0;
        $counter++;

        return [
            'school_id' => School::factory(),
            'code' => 'cat_'.str_pad((string) $counter, 4, '0', STR_PAD_LEFT),
            'name' => 'Fee Category '.$counter,
            'description' => null,
            'sort_order' => $counter,
        ];
    }
}
