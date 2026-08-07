<?php

namespace Tests\Unit\Driver;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use App\Models\Trip;
use App\Modules\Driver\Repositories\DriverApiRepository;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverApiRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_driver_by_user_id(): void
    {
        $school = $this->seedSchoolContext();

        $user = \App\Models\User::query()->create([
            'name' => 'Driver User',
            'email' => 'driver.repo@test.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'current_school_id' => $school->id,
        ]);

        $driver = Driver::query()->create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'name' => 'Repo Driver',
            'mobile' => '9999999999',
            'license_number' => 'REPO-DRIVER-001',
            'license_expiry_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $repo = app(DriverApiRepository::class);
        $found = $repo->findDriverByUserId($user->id);

        $this->assertNotNull($found);
        $this->assertSame($driver->id, $found->id);
    }

    public function test_it_returns_trip_scoped_to_driver(): void
    {
        $school = $this->seedSchoolContext();

        $driverA = Driver::query()->create([
            'school_id' => $school->id,
            'name' => 'Driver A',
            'mobile' => '1111111111',
            'license_number' => 'LIC-A',
            'license_expiry_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $driverB = Driver::query()->create([
            'school_id' => $school->id,
            'name' => 'Driver B',
            'mobile' => '2222222222',
            'license_number' => 'LIC-B',
            'license_expiry_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $vehicle = Vehicle::query()->create([
            'school_id' => $school->id,
            'vehicle_number' => 'REPO-BUS-001',
            'vehicle_name' => 'Repo Bus',
            'capacity' => 30,
            'status' => 'active',
        ]);

        $route = Route::query()->create([
            'school_id' => $school->id,
            'route_name' => 'Repo Route',
            'start_point' => 'School',
            'end_point' => 'Town',
            'vehicle_id' => $vehicle->id,
            'status' => 'active',
        ]);

        RouteStop::query()->create([
            'school_id' => $school->id,
            'route_id' => $route->id,
            'stop_name' => 'Stop 1',
            'sequence' => 1,
        ]);

        $trip = Trip::query()->create([
            'school_id' => $school->id,
            'driver_id' => $driverA->id,
            'vehicle_id' => $vehicle->id,
            'route_id' => $route->id,
            'type' => 'both',
            'status' => 'scheduled',
            'trip_date' => now()->startOfDay(),
            'total_students' => 0,
        ]);

        $repo = app(DriverApiRepository::class);

        $this->assertNotNull($repo->findTripForDriver($driverA->id, $trip->id));
        $this->assertNull($repo->findTripForDriver($driverB->id, $trip->id));
    }

    private function seedSchoolContext(): School
    {
        $this->seed(\Database\Seeders\SchoolSeeder::class);

        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        app(SchoolContext::class)->set($school->id);

        return $school;
    }
}
