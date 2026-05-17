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
        $name = fake()->company().' School';

        return [
            'uuid' => (string) Str::uuid(),
            'code' => strtoupper(fake()->unique()->bothify('SCH###')),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => 'India',
            'postal_code' => fake()->postcode(),
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'date_format' => 'd-m-Y',
            'status' => 'active',
        ];
    }
}
