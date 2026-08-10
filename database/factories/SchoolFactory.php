<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<School> */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        $name = 'Demo School '.$counter;

        return [
            'uuid' => (string) Str::uuid(),
            'code' => 'SCH'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT),
            'name' => $name,
            'slug' => Str::slug($name),
            'email' => 'school.'.$counter.'@example.com',
            'phone' => '+91 90000 '.str_pad((string) $counter, 5, '0', STR_PAD_LEFT),
            'address' => 'Main Campus Road, Demo City',
            'city' => 'Demo City',
            'state' => 'Karnataka',
            'country' => 'India',
            'postal_code' => '580001',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'date_format' => 'd-m-Y',
            'status' => 'active',
        ];
    }
}
