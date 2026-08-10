<?php

namespace Database\Seeders\Transport;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
class TransportSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();

        app(SchoolContext::class)->set($school->id);
        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

        $this->call([
            DriverSeeder::class,
            VehicleSeeder::class,
            RouteSeeder::class,
            RouteStopSeeder::class,
            StudentRouteSeeder::class,
            TripSeeder::class,
        ]);
    }
}