<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Parents\Models\Guardian;
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

class ParentWorkflowApiTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private User $guardianUser;
    private Guardian $guardian;
    private Student $student;
    private Guardian $otherGuardian;
    private Student $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);

        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
        app(SchoolContext::class)->set($this->school->id);

        $academicYear = AcademicYear::query()->create([
            'school_id' => $this->school->id, 'name' => '2025-26', 'is_active' => true, 'status' => 'active',
            'starts_on' => now()->subMonths(6), 'ends_on' => now()->addMonths(6),
        ]);

        $class = SchoolClass::query()->create([
            'school_id' => $this->school->id, 'name' => '10', 'code' => '10',
        ]);
        $section = Section::query()->create([
            'school_id' => $this->school->id, 'name' => 'A', 'code' => 'A',
        ]);
        $classSection = ClassSection::query()->create([
            'school_id' => $this->school->id, 'class_id' => $class->id, 'section_id' => $section->id,
        ]);

        [$this->guardianUser, $this->guardian] = $this->makeGuardian('guardian@test.com');

        $this->student = $this->makeStudent('PARENT-STU-001', 'first.student@test.com', 'Test', 'Student', 'male');
        StudentSession::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $academicYear->id,
            'student_id' => $this->student->id,
            'class_section_id' => $classSection->id,
            'roll_no' => '1',
            'status' => 'active',
        ]);
        $this->guardian->students()->attach($this->student->id, ['relationship' => 'father', 'is_primary' => true]);

        // Second guardian used to prove cross-tenant / cross-parent isolation.
        [, $this->otherGuardian] = $this->makeGuardian('other.guardian@test.com');
        $this->otherStudent = $this->makeStudent('PARENT-STU-002', 'second.student@test.com', 'Other', 'Student', 'female');
        $this->otherGuardian->students()->attach($this->otherStudent->id, ['relationship' => 'father', 'is_primary' => true]);
    }

    private function makeGuardian(string $email): array
    {
        $user = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Guardian',
            'email' => $email,
            'phone' => '9876543211',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $user->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);
        $user->assignRole('Parent');

        $guardian = Guardian::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $user->id,
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Guardian',
            'last_name' => 'Test',
            'phone' => '9876543211',
            'email' => $email,
            'status' => 'active',
        ]);

        return [$user, $guardian];
    }

    private function makeStudent(string $admissionNo, string $email, string $first, string $last, string $gender): Student
    {
        $user = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $first.' '.$last,
            'email' => $email,
            'phone' => '9876543212',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $user->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);

        return Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $user->id,
            'uuid' => (string) Str::uuid(),
            'admission_no' => $admissionNo,
            'first_name' => $first,
            'last_name' => $last,
            'gender' => $gender,
            'status' => 'active',
        ]);
    }

    private function parentToken(): string
    {
        return $this->postJson(route('api.v1.auth.login'), [
            'email' => 'guardian@test.com',
            'password' => 'password',
        ])->assertOk()->json('data.token');
    }

    public function test_guardian_can_view_own_profile(): void
    {
        $token = $this->parentToken();

        $this->withToken($token)
            ->getJson(route('api.v1.parents.show', $this->guardian->uuid))
            ->assertOk()
            ->assertJsonPath('data.email', 'guardian@test.com');
    }

    public function test_guardian_blocked_from_other_parent_profile(): void
    {
        $token = $this->parentToken();

        $this->withToken($token)
            ->getJson(route('api.v1.parents.show', $this->otherGuardian->uuid))
            ->assertNotFound();
    }

    public function test_guardian_blocked_from_other_parent_children(): void
    {
        $token = $this->parentToken();

        $this->withToken($token)
            ->getJson(route('api.v1.parents.children', $this->otherGuardian->uuid))
            ->assertNotFound();
    }

    public function test_guardian_can_access_own_child_attendance(): void
    {
        $token = $this->parentToken();

        $this->withToken($token)
            ->getJson(route('api.v1.parents.child.attendance', [
                'uuid' => $this->guardian->uuid,
                'childUuid' => $this->student->uuid,
            ]))
            ->assertOk();
    }

    public function test_guardian_blocked_from_other_parent_child_attendance(): void
    {
        $token = $this->parentToken();

        $this->withToken($token)
            ->getJson(route('api.v1.parents.child.attendance', [
                'uuid' => $this->otherGuardian->uuid,
                'childUuid' => $this->otherStudent->uuid,
            ]))
            ->assertNotFound();
    }

    public function test_admin_can_view_any_parent_profile(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->getJson(route('api.v1.parents.show', $this->otherGuardian->uuid))
            ->assertOk();
    }

    public function test_guardian_cannot_enumerate_parents(): void
    {
        $token = $this->parentToken();

        $this->withToken($token)
            ->getJson(route('api.v1.parents.index'))
            ->assertForbidden();
    }

    public function test_admin_can_enumerate_parents(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $token = $admin->createToken('admin-test')->plainTextToken;

        $this->withToken($token)
            ->getJson(route('api.v1.parents.index'))
            ->assertOk();
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $token = $this->parentToken();

        $this->withToken($token)
            ->putJson(route('api.v1.parents.change-password', $this->guardian->uuid), [
                'current_password' => 'wrong-password',
                'new_password' => 'newpass123',
                'confirm_password' => 'newpass123',
            ])
            ->assertStatus(422);
    }

    public function test_route_requires_token(): void
    {
        $this->getJson(route('api.v1.parents.show', $this->guardian->uuid))
            ->assertStatus(401);

        $this->withToken('garbage-token')
            ->getJson(route('api.v1.parents.show', $this->guardian->uuid))
            ->assertStatus(401);
    }

    public function test_deleted_token_is_rejected(): void
    {
        $token = $this->parentToken();

        $this->guardianUser->tokens()->delete();

        $this->withToken($token)
            ->getJson(route('api.v1.parents.show', $this->guardian->uuid))
            ->assertStatus(401);
    }

    public function test_change_password_revokes_other_tokens_and_clears_force_flag(): void
    {
        $this->guardianUser->force_password_change = true;
        $this->guardianUser->save();

        $token1 = $this->parentToken();
        $token2 = $this->guardianUser->createToken('other-session')->plainTextToken;

        $this->withToken($token1)
            ->putJson(route('api.v1.parents.change-password', $this->guardian->uuid), [
                'current_password' => 'password',
                'new_password' => 'newpass123',
                'confirm_password' => 'newpass123',
            ])
            ->assertOk();

        $this->assertFalse($this->guardianUser->fresh()->force_password_change);
        $this->assertTrue(Hash::check('newpass123', $this->guardianUser->fresh()->password));

        // The other session's token must be revoked.
        $this->assertNull(\Laravel\Sanctum\PersonalAccessToken::findToken($token2));

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();
        $this->app->forgetInstance('session');

        $this->withToken($token2)
            ->getJson(route('api.v1.parents.show', $this->guardian->uuid))
            ->assertStatus(401);

        $this->flushHeaders();

        $this->withToken($token1)
            ->getJson(route('api.v1.parents.show', $this->guardian->uuid))
            ->assertOk();
    }

    public function test_parent_role_blocked_from_admin_parents_data(): void
    {
        $this->actingAs($this->guardianUser)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.parents.data'))
            ->assertForbidden();
    }

    public function test_web_portal_notifications_read_from_generic_notifications_table(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $notification = Notification::query()->create([
            'school_id' => $this->school->id,
            'title' => 'Parent Announcement',
            'message' => 'Dear parents, annual day is next Friday.',
            'type' => 'announcement',
            'status' => 'sent',
            'target_type' => 'parents',
            'channel' => 'in_app',
            'created_by' => $admin->id,
        ]);
        $notification->users()->attach($this->guardianUser->id, [
            'is_read' => false,
            'delivery_status' => 'delivered',
        ]);

        $this->actingAs($this->guardianUser)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.parent-portal.notifications'))
            ->assertOk()
            ->assertSee('Parent Announcement');
    }

    // ─── Transport ───────────────────────────────────────────────────────

    public function test_guardian_can_view_child_transport_with_assignment(): void
    {
        $token = $this->parentToken();

        $route = Route::query()->create([
            'school_id' => $this->school->id,
            'route_name' => 'North Zone Route',
            'start_point' => 'Sector 15',
            'end_point' => 'School Main Gate',
            'status' => 'active',
        ]);

        $stop1 = RouteStop::query()->create([
            'school_id' => $this->school->id,
            'route_id' => $route->id,
            'stop_name' => 'Sector 15 Park',
            'pickup_time' => '07:30',
            'drop_time' => null,
            'sequence' => 1,
        ]);
        $stop2 = RouteStop::query()->create([
            'school_id' => $this->school->id,
            'route_id' => $route->id,
            'stop_name' => 'School Main Gate',
            'pickup_time' => '07:50',
            'drop_time' => '13:30',
            'sequence' => 5,
        ]);

        $vehicle = Vehicle::query()->create([
            'school_id' => $this->school->id,
            'vehicle_number' => 'MH-12-AB-1234',
            'vehicle_name' => 'School Bus 1',
            'vehicle_type' => 'school_bus',
            'capacity' => 40,
            'status' => 'active',
        ]);

        $driver = Driver::query()->create([
            'school_id' => $this->school->id,
            'user_id' => null,
            'name' => 'Rajesh Kumar',
            'mobile' => '+91-9876543210',
            'license_number' => 'DL-0420190012345',
            'license_expiry_date' => '2028-12-31',
            'status' => 'active',
        ]);

        $vehicle->driver()->associate($driver);
        $vehicle->save();

        TransportAssignment::query()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'route_id' => $route->id,
            'route_stop_id' => $stop1->id,
            'vehicle_id' => $vehicle->id,
            'pickup_point' => 'Sector 15 Park',
            'monthly_fee' => 2500.00,
            'status' => 'active',
        ]);

        $this->withToken($token)
            ->getJson(route('api.v1.parents.child.transport', [
                'uuid' => $this->guardian->uuid,
                'childUuid' => $this->student->uuid,
            ]))
            ->assertOk()
            ->assertJsonPath('data.assigned', true)
            ->assertJsonPath('data.transport.vehicle_number', 'MH-12-AB-1234')
            ->assertJsonPath('data.transport.driver_name', 'Rajesh Kumar')
            ->assertJsonPath('data.transport.route_name', 'North Zone Route')
            ->assertJsonPath('data.transport.pickup_stop', 'Sector 15 Park')
            ->assertJsonPath('data.transport.drop_stop', 'School Main Gate')
            ->assertJsonPath('data.transport.pickup_time', '07:30')
            ->assertJsonPath('data.transport.drop_time', '13:30')
            ->assertJsonCount(2, 'data.stops');
    }

    public function test_guardian_gets_not_assigned_when_no_transport(): void
    {
        $token = $this->parentToken();

        $this->withToken($token)
            ->getJson(route('api.v1.parents.child.transport', [
                'uuid' => $this->guardian->uuid,
                'childUuid' => $this->student->uuid,
            ]))
            ->assertOk()
            ->assertJsonPath('data.assigned', false)
            ->assertJsonPath('data.message', 'No transport assigned.');
    }

    public function test_guardian_blocked_from_other_parent_child_transport(): void
    {
        $token = $this->parentToken();

        $this->withToken($token)
            ->getJson(route('api.v1.parents.child.transport', [
                'uuid' => $this->otherGuardian->uuid,
                'childUuid' => $this->otherStudent->uuid,
            ]))
            ->assertNotFound();
    }

    public function test_guardian_blocked_from_other_parent_child_transport_wrong_child(): void
    {
        $token = $this->parentToken();

        $this->withToken($token)
            ->getJson(route('api.v1.parents.child.transport', [
                'uuid' => $this->guardian->uuid,
                'childUuid' => $this->otherStudent->uuid,
            ]))
            ->assertNotFound();
    }

    public function test_transport_endpoint_requires_token(): void
    {
        $this->getJson(route('api.v1.parents.child.transport', [
            'uuid' => $this->guardian->uuid,
            'childUuid' => $this->student->uuid,
        ]))
            ->assertStatus(401);
    }
}
