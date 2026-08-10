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
        static $counter = 0;
        $counter++;

        return [
            'school_id' => School::factory(),
            'name' => 'Class '.$counter,
            'code' => 'CLS'.$counter,
            'sort_order' => $counter,
            'status' => 'active',
        ];
    }
}
