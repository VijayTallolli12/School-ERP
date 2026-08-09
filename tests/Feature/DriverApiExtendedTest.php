<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Trip;
use App\Models\TripStudent;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DriverApiExtendedTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private Driver $driver;
    private Vehicle $vehicle;
    private Route $route;
    private RouteStop $stop1;
    private RouteStop $stop2;
    private Student $student1;
    private Student $student2;
    private User $driverUser;
    private AcademicYear $academicYear;

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
            'school_id' => $this->school->id, 'name' => '9', 'code' => '9',
        ]);
        $section = Section::query()->create([
            'school_id' => $this->school->id, 'name' => 'A', 'code' => 'A',
        ]);
        $classSection = ClassSection::query()->create([
            'school_id' => $this->school->id, 'class_id' => $class->id, 'section_id' => $section->id,
        ]);

        $this->driverUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Extended Driver',
            'email' => 'driver.ext@test.com',
            'phone' => '9876543201',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $this->driverUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);
        $this->driverUser->assignRole('Driver');

        $this->driver = Driver::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $this->driverUser->id,
            'name' => 'Extended Driver',
            'mobile' => '9876543201',
            'license_number' => 'LIC-EXT-001',
            'license_expiry_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $this->vehicle = Vehicle::query()->create([
            'school_id' => $this->school->id,
            'vehicle_number' => 'BUS-EXT-001',
            'vehicle_name' => 'Extended Bus',
            'vehicle_type' => 'bus',
            'capacity' => 40,
            'driver_id' => $this->driver->id,
            'status' => 'active',
        ]);

        $this->route = Route::query()->create([
            'school_id' => $this->school->id,
            'route_name' => 'Extended Route',
            'start_point' => 'School',
            'end_point' => 'City',
            'distance' => 12.5,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'status' => 'active',
        ]);

        $this->stop1 = RouteStop::query()->create([
            'school_id' => $this->school->id,
            'route_id' => $this->route->id,
            'stop_name' => 'Ext Stop 1',
            'latitude' => 28.6128,
            'longitude' => 77.2295,
            'pickup_time' => '07:30',
            'drop_time' => '15:00',
            'sequence' => 1,
        ]);

        $this->stop2 = RouteStop::query()->create([
            'school_id' => $this->school->id,
            'route_id' => $this->route->id,
            'stop_name' => 'Ext Stop 2',
            'latitude' => 28.6200,
            'longitude' => 77.2400,
            'pickup_time' => '07:45',
            'drop_time' => '14:45',
            'sequence' => 2,
        ]);

        $this->student1 = $this->makeStudent($classSection, 'Ext Student', 'One', 'STU-EXT-001', 'student.ext1@test.com', '1');
        $this->student2 = $this->makeStudent($classSection, 'Ext Student', 'Two', 'STU-EXT-002', 'student.ext2@test.com', '2');

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
    }

    private function makeStudent(ClassSection $classSection, string $first, string $last, string $admission, string $email, string $rollNo): Student
    {
        $user = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $first.' '.$last,
            'email' => $email,
            'phone' => '9876543202',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $user->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);

        $student = Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $user->id,
            'uuid' => (string) Str::uuid(),
            'admission_no' => $admission,
            'first_name' => $first,
            'last_name' => $last,
            'gender' => 'male',
            'status' => 'active',
        ]);

        StudentSession::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $student->id,
            'class_section_id' => $classSection->id,
            'roll_no' => $rollNo,
            'status' => 'active',
        ]);

        return $student;
    }

    public function test_driver_me(): void
    {
        $response = $this->withToken($this->getDriverToken())
            ->getJson(route('api.v1.driver.me'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['driver', 'vehicle', 'route']]);
        $response->assertJsonPath('data.driver.name', 'Extended Driver');
        $response->assertJsonPath('data.vehicle.vehicle_number', 'BUS-EXT-001');
    }

    public function test_driver_logout_revokes_token(): void
    {
        $token = $this->getDriverToken();

        $this->withToken($token)->postJson(route('api.v1.driver.logout'))->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_routes_today(): void
    {
        $trip = $this->createTrip();

        $response = $this->withToken($this->getDriverToken())
            ->getJson(route('api.v1.driver.routes.today'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['routes' => [['route_id', 'route_name', 'today_trips']]]]);
        $response->assertJsonPath('data.routes.0.route_id', $this->route->id);
        $response->assertJsonCount(1, 'data.routes.0.today_trips');
    }

    public function test_route_stops_include_lat_lng(): void
    {
        $response = $this->withToken($this->getDriverToken())
            ->getJson(route('api.v1.driver.routes.stops', $this->route->id));

        $response->assertOk()->assertJsonPath('data.route_name', 'Extended Route');
        $response->assertJsonCount(2, 'data.stops');
        $response->assertJsonPath('data.stops.0.stop_name', 'Ext Stop 1');
        $response->assertJsonPath('data.stops.0.latitude', '28.6128000');
        $response->assertJsonPath('data.stops.0.students_count', 1);
    }

    public function test_route_students(): void
    {
        $response = $this->withToken($this->getDriverToken())
            ->getJson(route('api.v1.driver.routes.students', $this->route->id));

        $response->assertOk()->assertJsonCount(2, 'data.students');
        $response->assertJsonPath('data.students.0.name', 'Ext Student One');
        $response->assertJsonPath('data.students.0.stop_id', $this->stop1->id);
    }

    public function test_trip_current_empty_returns_no_trip(): void
    {
        $response = $this->withToken($this->getDriverToken())
            ->getJson(route('api.v1.driver.trips.current'));

        $response->assertOk();
        $response->assertJsonPath('data.has_current_trip', false);
        $response->assertJsonPath('data.trip', null);
    }

    public function test_trip_current_returns_active_trip(): void
    {
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $response = $this->withToken($this->getDriverToken())
            ->getJson(route('api.v1.driver.trips.current'));

        $response->assertOk();
        $response->assertJsonPath('data.has_current_trip', true);
        $response->assertJsonPath('data.trip.id', $trip->id);
        $response->assertJsonPath('data.trip.status', 'in_progress');
    }

    public function test_trip_start_by_id(): void
    {
        $trip = $this->createTrip();

        $response = $this->withToken($this->getDriverToken())
            ->postJson(route('api.v1.driver.trips.start-by-id'), ['trip_id' => $trip->id]);

        $response->assertOk();
        $response->assertJsonPath('data.trip.status', 'in_progress');
        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'status' => 'in_progress']);
    }

    public function test_trip_end_requires_in_progress(): void
    {
        $trip = $this->createTrip();

        $response = $this->withToken($this->getDriverToken())
            ->postJson(route('api.v1.driver.trips.end', $trip->id));

        $response->assertStatus(422);
    }

    public function test_trip_end_completes_trip(): void
    {
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $response = $this->withToken($this->getDriverToken())
            ->postJson(route('api.v1.driver.trips.end', $trip->id));

        $response->assertOk();
        $response->assertJsonPath('data.trip.status', 'completed');
    }

    public function test_mark_attendance_pickup(): void
    {
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $response = $this->withToken($this->getDriverToken())
            ->postJson(route('api.v1.driver.attendance.store', $trip->id), [
                'student_id' => $this->student1->id,
                'action' => 'pickup',
                'latitude' => 28.6128,
                'longitude' => 77.2295,
                'request_id' => 'req-1',
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.trip_student.pickup_status', 'picked_up');
        $this->assertDatabaseHas('trip_students', [
            'student_id' => $this->student1->id,
            'pickup_status' => 'picked_up',
        ]);
    }

    public function test_mark_attendance_is_duplicate_safe(): void
    {
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $uri = route('api.v1.driver.attendance.store', $trip->id);
        $payload = ['student_id' => $this->student1->id, 'action' => 'pickup', 'request_id' => 'req-replay'];

        $this->withToken($this->getDriverToken())->postJson($uri, $payload)->assertOk();
        $replay = $this->withToken($this->getDriverToken())->postJson($uri, $payload);

        $replay->assertOk();
        $replay->assertJsonPath('data.trip_student.pickup_status', 'picked_up');
        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'picked_up_count' => 1]);
    }

    public function test_attendance_rejected_before_trip_start(): void
    {
        $trip = $this->createTrip();

        $response = $this->withToken($this->getDriverToken())
            ->postJson(route('api.v1.driver.attendance.store', $trip->id), [
                'student_id' => $this->student1->id,
                'action' => 'pickup',
            ]);

        $response->assertStatus(422);
    }

    public function test_update_attendance_put_drop(): void
    {
        $trip = $this->createTrip();
        $this->startTrip($trip);
        $tripStudent = $trip->tripStudents()->where('student_id', $this->student1->id)->first();

        $response = $this->withToken($this->getDriverToken())
            ->putJson(route('api.v1.driver.attendance.update', [$trip->id, $tripStudent->id]), [
                'action' => 'drop',
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.trip_student.drop_status', 'dropped_off');
    }

    public function test_arrive_stop_records_event(): void
    {
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $response = $this->withToken($this->getDriverToken())
            ->postJson(route('api.v1.driver.trips.arrive-stop', $trip->id), [
                'route_stop_id' => $this->stop1->id,
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.event', 'stop_arrived');
        $this->assertDatabaseHas('trip_events', [
            'trip_id' => $trip->id,
            'event_type' => 'stop_arrived',
        ]);
    }

    public function test_leave_stop_records_event(): void
    {
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $this->withToken($this->getDriverToken())
            ->postJson(route('api.v1.driver.trips.leave-stop', $trip->id), [
                'route_stop_id' => $this->stop2->id,
            ])->assertOk();

        $this->assertDatabaseHas('trip_events', [
            'trip_id' => $trip->id,
            'event_type' => 'stop_left',
        ]);
    }

    public function test_stop_flow_rejected_before_trip_start(): void
    {
        $trip = $this->createTrip();

        $response = $this->withToken($this->getDriverToken())
            ->postJson(route('api.v1.driver.trips.arrive-stop', $trip->id), [
                'route_stop_id' => $this->stop1->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_stop_not_in_route_rejected(): void
    {
        $trip = $this->createTrip();
        $this->startTrip($trip);

        $other = Vehicle::query()->create([
            'school_id' => $this->school->id,
            'vehicle_number' => 'BUS-STOP-EXT',
            'vehicle_name' => 'Other Route Bus',
            'capacity' => 10,
            'status' => 'active',
        ]);
        $foreignRoute = Route::query()->create([
            'school_id' => $this->school->id,
            'route_name' => 'Foreign Route',
            'start_point' => 'C',
            'end_point' => 'D',
            'vehicle_id' => $other->id,
            'status' => 'active',
        ]);
        $foreignStop = RouteStop::query()->create([
            'school_id' => $this->school->id,
            'route_id' => $foreignRoute->id,
            'stop_name' => 'Foreign Stop',
            'sequence' => 1,
        ]);

        $response = $this->withToken($this->getDriverToken())
            ->postJson(route('api.v1.driver.trips.arrive-stop', $trip->id), [
                'route_stop_id' => $foreignStop->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_notifications_endpoint(): void
    {
        $response = $this->withToken($this->getDriverToken())
            ->getJson(route('api.v1.driver.notifications'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['unread_count', 'notifications']]);
    }

    public function test_mark_notifications_read(): void
    {
        $response = $this->withToken($this->getDriverToken())
            ->postJson(route('api.v1.driver.notifications.read'), ['read_all' => true]);

        $response->assertOk();
        $response->assertJsonPath('data.unread_count', 0);
    }

    public function test_trip_history(): void
    {
        $trip = $this->createTrip();

        $response = $this->withToken($this->getDriverToken())
            ->getJson(route('api.v1.driver.trips.history'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['trips', 'meta']]);
        $response->assertJsonCount(1, 'data.trips');
        $response->assertJsonPath('data.trips.0.id', $trip->id);
    }

    public function test_route_ownership_enforced(): void
    {
        $other = Vehicle::query()->create([
            'school_id' => $this->school->id,
            'vehicle_number' => 'BUS-OTHER-EXT',
            'vehicle_name' => 'Other',
            'capacity' => 10,
            'status' => 'active',
        ]);
        $foreignRoute = Route::query()->create([
            'school_id' => $this->school->id,
            'route_name' => 'Not Mine',
            'start_point' => 'A',
            'end_point' => 'B',
            'vehicle_id' => $other->id,
            'status' => 'active',
        ]);

        $response = $this->withToken($this->getDriverToken())
            ->getJson(route('api.v1.driver.routes.show', $foreignRoute->id));

        $response->assertStatus(403);
    }

    public function test_trip_ownership_enforced_on_attendance(): void
    {
        $otherDriver = Driver::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Other Driver',
            'mobile' => '9876543209',
            'license_number' => 'LIC-OTHER-001',
            'license_expiry_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $otherVehicle = Vehicle::query()->create([
            'school_id' => $this->school->id,
            'vehicle_number' => 'BUS-OWN-EXT',
            'vehicle_name' => 'Ownership Bus',
            'capacity' => 30,
            'driver_id' => $otherDriver->id,
            'status' => 'active',
        ]);
        $otherRoute = Route::query()->create([
            'school_id' => $this->school->id,
            'route_name' => 'Ownership Route',
            'start_point' => 'X',
            'end_point' => 'Y',
            'vehicle_id' => $otherVehicle->id,
            'driver_id' => $otherDriver->id,
            'status' => 'active',
        ]);

        $foreignTrip = Trip::query()->create([
            'school_id' => $this->school->id,
            'driver_id' => $otherDriver->id,
            'vehicle_id' => $otherVehicle->id,
            'route_id' => $otherRoute->id,
            'type' => 'both',
            'status' => 'scheduled',
            'trip_date' => now()->startOfDay(),
            'total_students' => 1,
        ]);

        $response = $this->withToken($this->getDriverToken())
            ->postJson(route('api.v1.driver.attendance.store', $foreignTrip->id), [
                'student_id' => $this->student2->id,
                'action' => 'pickup',
            ]);

        $response->assertStatus(403);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function getDriverToken(): string
    {
        $response = $this->postJson(route('api.v1.driver.login'), [
            'email' => 'driver.ext@test.com',
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