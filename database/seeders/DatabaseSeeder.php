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
            AcademicStructureSeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
            ParentSeeder::class,
            TimetableSeeder::class,
            AttendanceSeeder::class,
            FeeCategorySeeder::class,
        ]);
    }
}
