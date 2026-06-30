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
use App\Models\Trip;
use App\Models\TripEvent;
use App\Models\TripStudent;
use App\Models\User;
use App\Models\VehicleLocation;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;
use App\Services\TripService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DriverApiTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private AcademicYear $academicYear;
    private ClassSection $classSection;
    private Vehicle $vehicle;
    private Driver $driver;
    private Route $route;
    private RouteStop $stop1;
    private RouteStop $stop2;
    private Student $student1;
    private Student $student2;
    private User $driverUser;
    private User $parentUser;

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

        $this->driverUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Driver',
            'email' => 'driver@test.com',
            'phone' => '9876543211',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $this->driverUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);

        $this->driver = Driver::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $this->driverUser->id,
            'name' => 'Test Driver',
            'mobile' => '9876543211',
            'license_number' => 'LIC-DRIVER-001',
            'license_expiry_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $this->vehicle = Vehicle::query()->create([
            'school_id' => $this->school->id,
            'vehicle_number' => 'BUS-DRIVER-001',
            'vehicle_name' => 'Driver Bus 1',
            'vehicle_type' => 'bus',
            'capacity' => 40,
            'driver_id' => $this->driver->id,
            'status' => 'active',
        ]);

        $this->route = Route::query()->create([
            'school_id' => $this->school->id,
            'route_name' => 'Driver Route A',
            'start_point' => 'School',
            'end_point' => 'City Center',
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'status' => 'active',
        ]);

        $this->stop1 = RouteStop::query()->create([
            'school_id' => $this->school->id,
            'route_id' => $this->route->id,
            'stop_name' => 'Stop 1 - Main',
            'pickup_time' => '07:30',
            'drop_time' => '15:00',
            'sequence' => 1,
        ]);

        $this->stop2 = RouteStop::query()->create([
            'school_id' => $this->school->id,
            'route_id' => $this->route->id,
            'stop_name' => 'Stop 2 - North',
            'pickup_time' => '07:45',
            'drop_time' => '14:45',
            'sequence' => 2,
        ]);

        $studentUser1 = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Student One',
            'email' => 'student1@test.com',
            'phone' => '9876543212',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $studentUser1->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);

        $this->student1 = Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $studentUser1->id,
            'uuid' => (string) Str::uuid(),
            'admission_no' => 'STU-DRIVER-001',
            'first_name' => 'Student',
            'last_name' => 'One',
            'gender' => 'male',
            'status' => 'active',
        ]);

        StudentSession::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student1->id,
            'class_section_id' => $this->classSection->id,
            'roll_no' => '1',
            'status' => 'active',
        ]);

        $studentUser2 = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Student Two',
            'email' => 'student2@test.com',
            'phone' => '9876543213',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $studentUser2->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);

        $this->student2 = Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $studentUser2->id,
            'uuid' => (string) Str::uuid(),
            'admission_no' => 'STU-DRIVER-002',
            'first_name' => 'Student',
            'last_name' => 'Two',
            'gender' => 'female',
            'status' => 'active',
        ]);

        StudentSession::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student2->id,
            'class_section_id' => $this->classSection->id,
            'roll_no' => '2',
            'status' => 'active',
        ]);

        TransportAssignment::query()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student1->id,
            'route_id' => $this->route->id,
            'route_stop_id' => $this->stop1->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'active',
        ]);

        TransportAssignment::query()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student2->id,
            'route_id' => $this->route->id,
            'route_stop_id' => $this->stop2->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'active',
        ]);

        $this->parentUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Parent',
            'email' => 'parent@test.com',
            'phone' => '9876543210',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $this->parentUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);
        $this->parentUser->assignRole('Parent');

        $guardian = Guardian::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $this->parentUser->id,
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Test',
            'last_name' => 'Parent',
            'email' => 'parent@test.com',
            'phone' => '9876543210',
            'status' => 'active',
        ]);

        $guardian->students()->syncWithoutDetaching([
            $this->student1->id => ['relationship' => 'father', 'is_primary' => true],
            $this->student2->id => ['relationship' => 'father', 'is_primary' => true],
        ]);
    }

    // ─── Authentication ───────────────────────────────────────────────

    public function test_driver_login_success(): void
    {
        $response = $this->postJson(route('api.v1.driver.login'), [
            'email' => 'driver@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'token', 'token_type', 'user', 'school_id', 'driver',
                ],
            ]);

        $response->assertJsonPath('data.driver.name', 'Test Driver');
        $response->assertJsonPath('data.driver.vehicle.vehicle_number', 'BUS-DRIVER-001');
    }

    public function test_driver_login_fails_with_wrong_credentials(): void
    {
        $response = $this->postJson(route('api.v1.driver.login'), [
            'email' => 'driver@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    // ─── Profile ──────────────────────────────────────────────────────

    public function test_driver_profile(): void
    {
        $token = $this->getDriverToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.driver.profile'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['driver', 'vehicle', 'route'],
            ]);

        $response->assertJsonPath('data.driver.name', 'Test Driver');
        $response->assertJsonPath('data.vehicle.vehicle_number', 'BUS-DRIVER-001');
        $response->assertJsonPath('data.route.route_name', 'Driver Route A');
    }

    // ─── Dashboard ────────────────────────────────────────────────────

    public function test_driver_dashboard_empty(): void
    {
        $token = $this->getDriverToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.driver.dashboard'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['summary', 'vehicle', 'routes', 'route_stops_count', 'today_trips'],
            ]);

        $response->assertJsonPath('data.summary.total_trips_today', 0);
    }

    public function test_driver_dashboard_with_trips(): void
    {
        $token = $this->getDriverToken();
        $trip = $this->createTrip();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.driver.dashboard'));

        $response->assertOk();
        $response->assertJsonPath('data.summary.total_trips_today', 1);
        $response->assertJsonPath('data.summary.total_students_today', 2);
    }

    // ─── Trips Today ──────────────────────────────────────────────────

    public function test_trips_today_empty(): void
    {
        $token = $this->getDriverToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.driver.trips.today'));

        $response->assertOk();
        $response->assertJsonCount(0, 'data.trips');
    }

    public function test_trips_today_with_data(): void
    {
        $token = $this->getDriverToken();
        $trip = $this->createTrip();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.driver.trips.today'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data.trips');
        $response->assertJsonPath('data.trips.0.id', $trip->id);
        $response->assertJsonPath('data.trips.0.status', 'scheduled');
    }

    // ─── Trip Lifecycle ───────────────────────────────────────────────

    public function test_trip_start(): void
    {
        Event::fake();
        $token = $this->getDriverToken();
        $trip = $this->createTrip();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.driver.trips.start', $trip->id));

        $response->assertOk();
        $response->assertJsonPath('data.trip.status', 'in_progress');

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('trip_events', [
            'trip_id' => $trip->id,
            'event_type' => 'trip_started',
        ]);

        Event::assertDispatched(TripStarted::class, function ($event) use ($trip) {
            return $event->vehicleId === $trip->vehicle_id
                && $event->routeId === $trip->route_id;
        });
    }

    public function test_trip_complete(): void
    {
        Event::fake();
        $token = $this->getDriverToken();
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $response = $this->withToken($token)
            ->postJson(route('api.v1.driver.trips.complete', $trip->id));

        $response->assertOk();
        $response->assertJsonPath('data.trip.status', 'completed');

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('trip_events', [
            'trip_id' => $trip->id,
            'event_type' => 'trip_completed',
        ]);

        Event::assertDispatched(TripCompleted::class, function ($event) use ($trip) {
            return $event->vehicleId === $trip->vehicle_id
                && $event->routeId === $trip->route_id;
        });
    }

    public function test_trip_cannot_start_from_in_progress(): void
    {
        $token = $this->getDriverToken();
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $response = $this->withToken($token)
            ->postJson(route('api.v1.driver.trips.start', $trip->id));

        $response->assertStatus(422);
    }

    public function test_trip_cannot_complete_from_scheduled(): void
    {
        $token = $this->getDriverToken();
        $trip = $this->createTrip();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.driver.trips.complete', $trip->id));

        $response->assertStatus(422);
    }

    // ─── Student Pickup / Drop ────────────────────────────────────────

    public function test_student_pickup(): void
    {
        $token = $this->getDriverToken();
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $tripStudent = $trip->tripStudents()->first();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.driver.trips.pickup', $trip->id), [
                'trip_student_id' => $tripStudent->id,
                'latitude' => 28.6128,
                'longitude' => 77.2295,
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.trip_student.pickup_status', 'picked_up');

        $this->assertDatabaseHas('trip_students', [
            'id' => $tripStudent->id,
            'pickup_status' => 'picked_up',
        ]);

        $this->assertDatabaseHas('trip_events', [
            'trip_id' => $trip->id,
            'trip_student_id' => $tripStudent->id,
            'event_type' => 'student_pickup',
        ]);
    }

    public function test_student_drop(): void
    {
        $token = $this->getDriverToken();
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $tripStudent = $trip->tripStudents()->first();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.driver.trips.drop', $trip->id), [
                'trip_student_id' => $tripStudent->id,
                'latitude' => 28.6128,
                'longitude' => 77.2295,
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.trip_student.drop_status', 'dropped_off');

        $this->assertDatabaseHas('trip_students', [
            'id' => $tripStudent->id,
            'drop_status' => 'dropped_off',
        ]);

        $this->assertDatabaseHas('trip_events', [
            'trip_id' => $trip->id,
            'trip_student_id' => $tripStudent->id,
            'event_type' => 'student_drop',
        ]);
    }

    public function test_cannot_pickup_twice(): void
    {
        $token = $this->getDriverToken();
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $tripStudent = $trip->tripStudents()->first();

        $this->withToken($token)
            ->postJson(route('api.v1.driver.trips.pickup', $trip->id), [
                'trip_student_id' => $tripStudent->id,
            ]);

        $response = $this->withToken($token)
            ->postJson(route('api.v1.driver.trips.pickup', $trip->id), [
                'trip_student_id' => $tripStudent->id,
            ]);

        $response->assertStatus(422);
    }

    // ─── Trip Students ────────────────────────────────────────────────

    public function test_trip_students_list(): void
    {
        $token = $this->getDriverToken();
        $trip = $this->createTrip();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.driver.trips.students', $trip->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['pickup_order', 'drop_order'],
            ]);

        $this->assertCount(2, $response->json('data.pickup_order'));
        $this->assertCount(2, $response->json('data.drop_order'));
    }

    // ─── Trip Show ────────────────────────────────────────────────────

    public function test_trip_show(): void
    {
        $token = $this->getDriverToken();
        $trip = $this->createTrip();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.driver.trips.show', $trip->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['trip', 'route', 'vehicle', 'stops'],
            ]);

        $response->assertJsonPath('data.trip.total_students', 2);
    }

    // ─── Location Update ──────────────────────────────────────────────

    public function test_driver_location_update(): void
    {
        $token = $this->getDriverToken();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.driver.location.update'), [
                'vehicle_id' => $this->vehicle->id,
                'latitude' => 28.6128,
                'longitude' => 77.2295,
                'speed' => 35.5,
                'heading' => 180.0,
            ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['location' => ['id', 'vehicle_id', 'latitude', 'longitude']]]);

        $this->assertDatabaseHas('vehicle_locations', [
            'vehicle_id' => $this->vehicle->id,
            'source' => 'driver_app',
        ]);
    }

    public function test_driver_location_update_with_trip(): void
    {
        $token = $this->getDriverToken();
        $trip = $this->createTrip();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.driver.location.update'), [
                'vehicle_id' => $this->vehicle->id,
                'latitude' => 28.6128,
                'longitude' => 77.2295,
                'trip_id' => $trip->id,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('trip_events', [
            'trip_id' => $trip->id,
            'event_type' => 'location_update',
        ]);
    }

    public function test_driver_location_update_wrong_vehicle(): void
    {
        $token = $this->getDriverToken();
        $otherVehicle = Vehicle::query()->create([
            'school_id' => $this->school->id,
            'vehicle_number' => 'BUS-OTHER-001',
            'vehicle_name' => 'Other Bus',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $response = $this->withToken($token)
            ->postJson(route('api.v1.driver.location.update'), [
                'vehicle_id' => $otherVehicle->id,
                'latitude' => 28.6128,
                'longitude' => 77.2295,
            ]);

        $response->assertStatus(403);
    }

    // ─── SOS ──────────────────────────────────────────────────────────

    public function test_sos_alert(): void
    {
        $token = $this->getDriverToken();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.driver.sos'), [
                'latitude' => 28.6128,
                'longitude' => 77.2295,
                'message' => 'Emergency! Need assistance.',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('trip_events', [
            'event_type' => 'sos_alert',
        ]);
    }

    // ─── ETA ──────────────────────────────────────────────────────────

    public function test_trip_eta(): void
    {
        $token = $this->getDriverToken();
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $uri = route('api.v1.driver.trips.eta', $trip->id)
            . '?current_latitude=28.6128&current_longitude=77.2295';

        $response = $this->withToken($token)
            ->getJson($uri);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['trip_id', 'current_location', 'eta'],
            ]);

        $this->assertCount(2, $response->json('data.eta'));
    }

    // ─── Unauthorized Access ─────────────────────────────────────────

    public function test_unauthenticated_access_fails(): void
    {
        $response = $this->getJson(route('api.v1.driver.profile'));
        $response->assertStatus(401);

        $response = $this->getJson(route('api.v1.driver.dashboard'));
        $response->assertStatus(401);

        $response = $this->getJson(route('api.v1.driver.trips.today'));
        $response->assertStatus(401);

        $response = $this->postJson(route('api.v1.driver.location.update'), [
            'vehicle_id' => 1, 'latitude' => 0, 'longitude' => 0,
        ]);
        $response->assertStatus(401);
    }

    // ─── Event Dispatch ───────────────────────────────────────────────

    public function test_location_updated_event_dispatched(): void
    {
        Event::fake();
        $token = $this->getDriverToken();

        $this->withToken($token)
            ->postJson(route('api.v1.driver.location.update'), [
                'vehicle_id' => $this->vehicle->id,
                'latitude' => 28.6128,
                'longitude' => 77.2295,
            ]);

        Event::assertDispatched(LocationUpdated::class, function ($event) {
            return $event->vehicleId === $this->vehicle->id;
        });
    }

    public function test_trip_started_event_dispatched(): void
    {
        Event::fake();
        $token = $this->getDriverToken();
        $trip = $this->createTrip();

        $this->withToken($token)
            ->postJson(route('api.v1.driver.trips.start', $trip->id));

        Event::assertDispatched(TripStarted::class);
    }

    public function test_trip_completed_event_dispatched(): void
    {
        Event::fake();
        $token = $this->getDriverToken();
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $this->withToken($token)
            ->postJson(route('api.v1.driver.trips.complete', $trip->id));

        Event::assertDispatched(TripCompleted::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function getDriverToken(): string
    {
        $response = $this->postJson(route('api.v1.driver.login'), [
            'email' => 'driver@test.com',
            'password' => 'password',
        ]);

        return $response->json('data.token');
    }

    private function createTrip(): Trip
    {
        $trip = Trip::query()->create([
            'school_id' => $this->school->id,
            'driver_id' => $this->driver->id,
            'vehicle_id' => $this->vehicle->id,
            'route_id' => $this->route->id,
            'type' => 'both',
            'status' => 'scheduled',
            'trip_date' => now()->startOfDay(),
            'total_students' => 2,
        ]);

        TripStudent::query()->create([
            'school_id' => $this->school->id,
            'trip_id' => $trip->id,
            'student_id' => $this->student1->id,
            'route_stop_id' => $this->stop1->id,
        ]);

        TripStudent::query()->create([
            'school_id' => $this->school->id,
            'trip_id' => $trip->id,
            'student_id' => $this->student2->id,
            'route_stop_id' => $this->stop2->id,
        ]);

        return $trip->fresh();
    }

    private function startTrip(Trip $trip): void
    {
        $trip->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }
}
