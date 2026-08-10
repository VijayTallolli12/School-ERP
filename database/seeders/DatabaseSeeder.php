<?php

namespace Database\Seeders;

use Database\Seeders\Transport\TransportSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Ordering respects dependencies:
     * School -> Permissions -> Admin -> Academic structure -> Teachers -> Students
     * -> Parents -> Timetable -> Attendance -> Fees -> Transport.
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
            TransportSeeder::class,
        ]);

        // Enrichment seeders (exams, homework, library, payroll, leave,
        // calendar, notifications, fee structures). Safe to re-run; they rely
        // on the base data created above.
        $this->call([
            \Database\Seeders\Golden\GoldenSchoolSeeder::class,
        ]);
    }
}