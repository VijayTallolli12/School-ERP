<?php

namespace Database\Factories;

use App\Modules\Teachers\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        return [
            'school_id' => 1,
            'employee_id' => 'T-'.str_pad((string) $counter, 4, '0', STR_PAD_LEFT),
            'first_name' => 'Teacher',
            'middle_name' => null,
            'last_name' => 'Demo '.$counter,
            'gender' => 'male',
            'date_of_birth' => '1980-01-01',
            'qualification' => 'B.Ed',
            'experience_years' => 5,
            'joining_date' => now()->subYears(5)->toDateString(),
            'phone' => '+91 90000 '.str_pad((string) $counter, 5, '0', STR_PAD_LEFT),
            'email' => 'teacher.demo.'.$counter.'@example.com',
            'address' => 'Demo Address '.$counter,
            'status' => 'active',
        ];
    }
}
