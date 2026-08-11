<?php

namespace Database\Seeders;

use Database\Seeders\Golden\GoldenSchoolSeeder;
use Database\Seeders\Transport\DriverSeeder;
use Database\Seeders\Transport\RouteStopSeeder;
use Database\Seeders\Transport\StudentTransportSeeder;
use Database\Seeders\Transport\TransportRouteSeeder;
use Database\Seeders\Transport\TripSeeder;
use Database\Seeders\Transport\VehicleSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Ordering respects dependencies:
     * School -> Permissions -> Admin -> Academic structure -> Teachers -> Students
     * -> Parents -> Fees -> Transport (Drivers -> Vehicles -> Routes -> Stops
     * -> Student assignments -> Trips) -> Timetable -> Attendance -> Enrichment.
     *
     * Every seeder is idempotent (updateOrCreate / firstOrCreate on stable keys),
     * so this can be re-run safely without duplicate or unique-key errors.
     */
    public function run(): void
    {
        // Demo-only safety: disable FK checks while (re)seeding the key tables
        // so stale rows can be cleared without constraint errors.
        $resetDemoData = (bool) env('SEED_RESET_DEMO_DATA', app()->environment('local'));

        if ($resetDemoData) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $tables = DB::select('SHOW TABLES');
            $dbName = DB::connection()->getDatabaseName();

            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];

                if (strtolower($tableName) === 'migrations') {
                    continue;
                }

                DB::table($tableName)->truncate();
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->call([
            SchoolSeeder::class,
            PermissionSeeder::class,
            AdminUserSeeder::class,
            AcademicStructureSeeder::class,
            FeeCategorySeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
            NotificationSeeder::class,
            ParentSeeder::class,
            DriverSeeder::class,
            VehicleSeeder::class,
            TransportRouteSeeder::class,
            RouteStopSeeder::class,
            StudentTransportSeeder::class,
            TripSeeder::class,
            TimetableSeeder::class,
            AttendanceSeeder::class,
            GoldenSchoolSeeder::class,
        ]);
    }
}
