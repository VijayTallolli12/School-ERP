<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Events\AttendanceMarked;
use App\Events\ExamPublished;
use App\Events\FeeReminderGenerated;
use App\Events\HomeworkAssigned;
use App\Models\School;
use App\Models\User;
use App\Models\UserDevice;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use App\Modules\Teachers\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RealTimeInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private User $studentUser;
    private Student $student;
    private User $teacherUser;
    private Teacher $teacher;
    private ClassSection $classSection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);

        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

        $class = SchoolClass::query()->create([
            'school_id' => $this->school->id, 'name' => '10', 'code' => '10',
        ]);
        $section = Section::query()->create([
            'school_id' => $this->school->id, 'name' => 'A', 'code' => 'A',
        ]);
        $this->classSection = ClassSection::query()->create([
            'school_id' => $this->school->id, 'class_id' => $class->id, 'section_id' => $section->id,
        ]);

        $this->studentUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'phone' => '9876543210',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $this->studentUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);

        $this->student = Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $this->studentUser->id,
            'uuid' => (string) Str::uuid(),
            'admission_no' => 'STU-TEST-001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $academicYear = \App\Models\AcademicYear::query()->create([
            'school_id' => $this->school->id, 'name' => '2025-26', 'is_active' => true, 'status' => 'active',
            'starts_on' => now()->subMonths(6), 'ends_on' => now()->addMonths(6),
        ]);

        StudentSession::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $academicYear->id,
            'student_id' => $this->student->id,
            'class_section_id' => $this->classSection->id,
            'roll_no' => '1',
            'status' => 'active',
            'joined_on' => now()->subMonths(6),
        ]);

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
    }

    public function test_device_registration(): void
    {
        $token = $this->getStudentToken();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.notifications.devices.register'), [
                'device_type' => 'phone',
                'platform' => 'android',
                'device_token' => 'fcm-token-123',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['device' => ['id', 'device_type', 'platform', 'last_seen_at']]]);

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $this->studentUser->id,
            'device_token' => 'fcm-token-123',
            'platform' => 'android',
        ]);
    }

    public function test_device_registration_updates_existing(): void
    {
        $token = $this->getStudentToken();

        $this->withToken($token)
            ->postJson(route('api.v1.notifications.devices.register'), [
                'device_token' => 'fcm-token-123',
            ])->assertOk();

        $this->withToken($token)
            ->postJson(route('api.v1.notifications.devices.register'), [
                'device_type' => 'tablet',
                'platform' => 'ios',
                'device_token' => 'fcm-token-123',
            ])->assertOk();

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $this->studentUser->id,
            'device_token' => 'fcm-token-123',
            'platform' => 'ios',
        ]);

        $this->assertEquals(1, UserDevice::query()->where('user_id', $this->studentUser->id)->count());
    }

    public function test_device_unregistration(): void
    {
        $token = $this->getStudentToken();

        UserDevice::query()->create([
            'user_id' => $this->studentUser->id,
            'device_token' => 'fcm-token-123',
        ]);

        $response = $this->withToken($token)
            ->postJson(route('api.v1.notifications.devices.unregister'), [
                'device_token' => 'fcm-token-123',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_device_unregistration_unknown_token(): void
    {
        $token = $this->getStudentToken();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.notifications.devices.unregister'), [
                'device_token' => 'nonexistent-token',
            ]);

        $response->assertStatus(404);
    }

    public function test_unauthenticated_device_endpoints_fail(): void
    {
        $response = $this->postJson(route('api.v1.notifications.devices.register'), [
            'device_token' => 'test',
        ]);
        $response->assertStatus(401);

        $response = $this->postJson(route('api.v1.notifications.devices.unregister'), [
            'device_token' => 'test',
        ]);
        $response->assertStatus(401);

        $response = $this->getJson(route('api.v1.notifications.unread-count'));
        $response->assertStatus(401);
    }

    public function test_unread_count_endpoint(): void
    {
        $token = $this->getStudentToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.notifications.unread-count'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['unread_count']])
            ->assertJsonPath('data.unread_count', 0);
    }

    public function test_attendance_marked_event_dispatched(): void
    {
        Event::fake();

        AttendanceMarked::dispatch(
            schoolId: $this->school->id,
            studentId: $this->student->id,
            status: 'present',
            date: now()->toDateString(),
        );

        Event::assertDispatched(AttendanceMarked::class);
    }

    public function test_homework_assigned_event_dispatched(): void
    {
        Event::fake();

        HomeworkAssigned::dispatch(
            homeworkId: 1,
            classSectionId: $this->classSection->id,
            title: 'Math Homework',
            dueDate: now()->addDays(7)->toDateString(),
            studentIds: [$this->student->id],
        );

        Event::assertDispatched(HomeworkAssigned::class);
    }

    public function test_exam_published_event_dispatched(): void
    {
        Event::fake();

        ExamPublished::dispatch(
            examId: 1,
            examName: 'Mid Term',
            classSectionId: $this->classSection->id,
            studentIds: [$this->student->id],
        );

        Event::assertDispatched(ExamPublished::class);
    }

    public function test_fee_reminder_event_dispatched(): void
    {
        Event::fake();

        FeeReminderGenerated::dispatch(
            studentFeeId: 1,
            studentId: $this->student->id,
            parentUserId: $this->studentUser->id,
            amountDue: 5000.00,
            dueDate: now()->addDays(30)->toDateString(),
        );

        Event::assertDispatched(FeeReminderGenerated::class);
    }

    public function test_listener_creates_database_notification(): void
    {
        app(SchoolContext::class)->set($this->school->id);

        Notification::query()->where('school_id', $this->school->id)->delete();

        AttendanceMarked::dispatch(
            schoolId: $this->school->id,
            studentId: $this->student->id,
            status: 'present',
            date: now()->toDateString(),
        );

        $this->assertDatabaseHas('notifications', [
            'school_id' => $this->school->id,
            'type' => 'attendance_alert',
        ]);
    }

    public function test_device_registers_multiple_tokens(): void
    {
        $token = $this->getStudentToken();

        $this->withToken($token)
            ->postJson(route('api.v1.notifications.devices.register'), [
                'device_token' => 'token-a',
            ])->assertOk();

        $this->withToken($token)
            ->postJson(route('api.v1.notifications.devices.register'), [
                'device_token' => 'token-b',
            ])->assertOk();

        $count = UserDevice::query()->where('user_id', $this->studentUser->id)->count();
        $this->assertEquals(2, $count);
    }

    public function test_unauthenticated_access_fails(): void
    {
        $response = $this->postJson(route('api.v1.notifications.devices.register'), [
            'device_token' => 'test',
        ]);
        $response->assertStatus(401);

        $response = $this->postJson(route('api.v1.notifications.devices.unregister'), [
            'device_token' => 'test',
        ]);
        $response->assertStatus(401);

        $response = $this->getJson(route('api.v1.notifications.unread-count'));
        $response->assertStatus(401);
    }

    private function getStudentToken(): string
    {
        $response = $this->postJson(route('api.v1.student.login'), [
            'email' => 'student@test.com',
            'password' => 'password',
        ]);

        return $response->json('data.token');
    }
}
