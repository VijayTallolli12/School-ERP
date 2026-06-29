<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SchoolSeeder::class,
            PermissionSeeder::class,
            AdminUserSeeder::class,
        ]);

        if (env('DEMO_DATASET', false)) {
            $this->call(\Database\Seeders\Golden\GoldenSchoolSeeder::class);
        }
    }
}
