<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ClassSection> */
class ClassSectionFactory extends Factory
{
    protected $model = ClassSection::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'class_id' => SchoolClass::factory(),
            'section_id' => Section::factory(),
            'class_teacher_id' => null,
            'status' => 'active',
        ];
    }
}
