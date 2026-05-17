<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AcademicYear> */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $startYear = (int) now()->format('Y');

        return [
            'school_id' => School::factory(),
            'name' => $startYear.'-'.($startYear + 1),
            'starts_on' => now()->startOfYear()->addMonths(3)->toDateString(),
            'ends_on' => now()->startOfYear()->addYear()->addMonths(2)->endOfMonth()->toDateString(),
            'is_active' => true,
            'status' => 'active',
        ];
    }
}
