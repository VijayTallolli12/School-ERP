<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Events\BusArrived;
use App\Events\BusArriving;
use App\Events\LocationUpdated;
use App\Events\TripCompleted;
use App\Events\TripStarted;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Models\VehicleLocation;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Academics\Models\Subject;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;
use App\Services\EtaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LiveTransportTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private AcademicYear $academicYear;
    private ClassSection $classSection;
    private Vehicle $vehicle;
    private Driver $driver;
    private Route $route;
    private RouteStop $stop;
    private Student $student;
    private User $teacherUser;
    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);

        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
        app(SchoolContext::class)->set($this->school->id);

        $this->academicYear = AcademicYear::query()->create([
            'school_id' => $this->school->id, 'name' => '2025-26', 'is_active' => true, 'status' => 'active',
            'starts_on' => now()->subMonths(6), 'ends_on' => now()->addMonths(6),
        ]);

        $class = SchoolClass::query()->create([
            'school_id' => $this->school->id, 'name' => '10', 'code' => '10',
        ]);
        $section = Section::query()->create([
            'school_id' => $this->school->id, 'name' => 'A', 'code' => 'A',
        ]);
        $this->classSection = ClassSection::query()->create([
            'school_id' => $this->school->id, 'class_id' => $class->id, 'section_id' => $section->id,
        ]);

        // Create teacher user for authenticated requests
        $this->teacherUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Teacher',
            'email' => 'teacher@test.com',
            'phone' => '9876543210',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $this->teacherUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);
        $this->teacherUser->assignRole('Teacher');

        $this->teacher = Teacher::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $this->teacherUser->id,
            'uuid' => (string) Str::uuid(),
            'employee_id' => 'T-TEST-001',
            'first_name' => 'Test',
            'last_name' => 'Teacher',
            'gender' => 'male',
            'phone' => '9876543210',
            'email' => 'teacher@test.com',
            'status' => 'active',
        ]);

        $this->driver = Driver::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Test Driver',
            'mobile' => '9876543210',
            'license_number' => 'LIC-001',
            'license_expiry_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $this->vehicle = Vehicle::query()->create([
            'school_id' => $this->school->id,
            'vehicle_number' => 'BUS-001',
            'vehicle_name' => 'School Bus 1',
            'vehicle_type' => 'bus',
            'capacity' => 40,
            'driver_id' => $this->driver->id,
            'status' => 'active',
        ]);

        $this->route = Route::query()->create([
            'school_id' => $this->school->id,
            'route_name' => 'Route A',
            'start_point' => 'School',
            'end_point' => 'City Center',
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'status' => 'active',
        ]);

        $this->stop = RouteStop::query()->create([
            'school_id' => $this->school->id,
            'route_id' => $this->route->id,
            'stop_name' => 'Main Stop',
            'pickup_time' => '08:00',
            'drop_time' => '15:00',
            'sequence' => 1,
        ]);

        $studentUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'phone' => '9876543210',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $studentUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);

        $this->student = Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $studentUser->id,
            'uuid' => (string) Str::uuid(),
            'admission_no' => 'STU-001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'male',
            'status' => 'active',
        ]);

        StudentSession::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'class_section_id' => $this->classSection->id,
            'roll_no' => '1',
            'status' => 'active',
        ]);

        TransportAssignment::query()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'route_id' => $this->route->id,
            'route_stop_id' => $this->stop->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'active',
        ]);
    }

    public function test_update_location(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.transport.location.update'), [
                'vehicle_id' => $this->vehicle->id,
                'latitude' => 28.6128,
                'longitude' => 77.2295,
                'speed' => 35.5,
                'heading' => 180.0,
            ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['location' => ['id', 'vehicle_id', 'latitude', 'longitude', 'speed', 'heading', 'captured_at']]]);

        $this->assertStringContainsString('28.6128', (string) $response->json('data.location.latitude'));
        $this->assertStringContainsString('77.2295', (string) $response->json('data.location.longitude'));

        $this->assertDatabaseHas('vehicle_locations', [
            'vehicle_id' => $this->vehicle->id,
            'latitude' => 28.6128,
            'longitude' => 77.2295,
        ]);
    }

    public function test_update_location_dispatches_event(): void
    {
        Event::fake();

        $token = $this->getToken();

        $this->withToken($token)
            ->postJson(route('api.v1.transport.location.update'), [
                'vehicle_id' => $this->vehicle->id,
                'latitude' => 28.6128,
                'longitude' => 77.2295,
            ]);

        Event::assertDispatched(LocationUpdated::class, function ($event) {
            return $event->vehicleId === $this->vehicle->id
                && $event->latitude === 28.6128;
        });
    }

    public function test_live_status_endpoint(): void
    {
        VehicleLocation::query()->create([
            'vehicle_id' => $this->vehicle->id,
            'latitude' => 28.6128,
            'longitude' => 77.2295,
            'speed' => 35.0,
            'captured_at' => now(),
        ]);

        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.transport.live'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary' => ['total_vehicles', 'active_vehicles', 'inactive_vehicles', 'trips_running'],
                    'active_vehicles',
                    'inactive_vehicles',
                ],
            ]);

        $response->assertJsonPath('data.summary.total_vehicles', 1);
        $response->assertJsonPath('data.summary.active_vehicles', 1);
    }

    public function test_vehicle_location_endpoint(): void
    {
        VehicleLocation::query()->create([
            'vehicle_id' => $this->vehicle->id,
            'latitude' => 28.6128,
            'longitude' => 77.2295,
            'speed' => 35.0,
            'captured_at' => now()->subMinutes(5),
        ]);

        VehicleLocation::query()->create([
            'vehicle_id' => $this->vehicle->id,
            'latitude' => 28.6130,
            'longitude' => 77.2300,
            'speed' => 40.0,
            'captured_at' => now(),
        ]);

        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.transport.vehicle.location', $this->vehicle->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'vehicle' => ['id', 'vehicle_number', 'vehicle_name'],
                    'current_location',
                    'location_history',
                ],
            ]);

        $this->assertCount(2, $response->json('data.location_history'));
    }

    public function test_vehicle_location_not_found(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.transport.vehicle.location', 999));

        $response->assertStatus(404);
    }

    public function test_unauthenticated_endpoints_fail(): void
    {
        $response = $this->postJson(route('api.v1.transport.location.update'), [
            'vehicle_id' => 1, 'latitude' => 0, 'longitude' => 0,
        ]);
        $response->assertStatus(401);

        $response = $this->getJson(route('api.v1.transport.live'));
        $response->assertStatus(401);

        $response = $this->getJson(route('api.v1.transport.vehicle.location', 1));
        $response->assertStatus(401);
    }

    public function test_location_updated_event_dispatched(): void
    {
        Event::fake();

        LocationUpdated::dispatch(
            vehicleId: $this->vehicle->id,
            latitude: 28.6128,
            longitude: 77.2295,
            speed: 35.0,
            heading: 180.0,
            capturedAt: now()->toIso8601String(),
        );

        Event::assertDispatched(LocationUpdated::class);
    }

    public function test_bus_arriving_event(): void
    {
        Event::fake();

        BusArriving::dispatch(
            vehicleId: $this->vehicle->id,
            routeStopId: $this->stop->id,
            stopName: $this->stop->stop_name,
            distanceMeters: 300,
            estimatedMinutes: 2,
        );

        Event::assertDispatched(BusArriving::class, function ($event) {
            return $event->stopName === 'Main Stop' && $event->estimatedMinutes === 2;
        });
    }

    public function test_bus_arrived_event(): void
    {
        Event::fake();

        BusArrived::dispatch(
            vehicleId: $this->vehicle->id,
            routeStopId: $this->stop->id,
            stopName: $this->stop->stop_name,
        );

        Event::assertDispatched(BusArrived::class);
    }

    public function test_trip_started_event(): void
    {
        Event::fake();

        TripStarted::dispatch(
            vehicleId: $this->vehicle->id,
            routeId: $this->route->id,
            startedAt: now()->toIso8601String(),
        );

        Event::assertDispatched(TripStarted::class);
    }

    public function test_trip_completed_event(): void
    {
        Event::fake();

        TripCompleted::dispatch(
            vehicleId: $this->vehicle->id,
            routeId: $this->route->id,
            completedAt: now()->toIso8601String(),
        );

        Event::assertDispatched(TripCompleted::class);
    }

    public function test_eta_service_distance(): void
    {
        $eta = app(EtaService::class);

        $distance = $eta->distanceBetween(28.6128, 77.2295, 28.7041, 77.1025);

        $this->assertGreaterThan(10, $distance);
        $this->assertLessThan(20, $distance);
    }

    public function test_eta_service_estimated_minutes(): void
    {
        $eta = app(EtaService::class);

        $minutes = $eta->estimatedMinutes(15, 30);

        $this->assertEquals(30, $minutes);
    }

    public function test_eta_service_is_within_threshold(): void
    {
        $eta = app(EtaService::class);

        $this->assertTrue($eta->isWithinThreshold(0.3, 0.5));
        $this->assertFalse($eta->isWithinThreshold(1.0, 0.5));
    }

    public function test_live_status_empty(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.transport.live'));

        $response->assertOk();
        $response->assertJsonPath('data.summary.total_vehicles', 1);
        $response->assertJsonPath('data.summary.active_vehicles', 0);
    }

    public function test_update_location_minimal_fields(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.transport.location.update'), [
                'vehicle_id' => $this->vehicle->id,
                'latitude' => 28.6128,
                'longitude' => 77.2295,
            ]);

        $response->assertOk();
    }

    public function test_update_location_validation_fails(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.transport.location.update'), [
                'vehicle_id' => 999,
                'latitude' => 100.0,
                'longitude' => 200.0,
            ]);

        $response->assertStatus(422);
    }

    private function getToken(): string
    {
        $response = $this->postJson(route('api.v1.teacher.login'), [
            'email' => 'teacher@test.com',
            'password' => 'password',
        ]);

        return $response->json('data.token');
    }
}
