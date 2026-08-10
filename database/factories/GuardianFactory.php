<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\User;
use App\Modules\Parents\Models\Guardian;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Guardian>
 */
class GuardianFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        return [
            'school_id' => School::factory(),
            'user_id' => User::factory(),
            'first_name' => 'Parent',
            'last_name' => 'Demo '.$counter,
            'email' => 'parent.demo.'.$counter.'@example.com',
            'phone' => '+91 90000 '.str_pad((string) $counter, 5, '0', STR_PAD_LEFT),
            'occupation' => 'Engineer',
            'address' => 'Demo Address '.$counter,
            'status' => 'active',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }
}