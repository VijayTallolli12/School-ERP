<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Leave\Models\LeaveType;
use App\Modules\Teachers\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TeacherAppApiTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private User $teacherUser;
    private Teacher $teacher;
    private ClassSection $classSection;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);

        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

        // Create class section
        $class = \App\Modules\Academics\Models\SchoolClass::query()->create([
            'school_id' => $this->school->id,
            'name' => '10',
            'code' => '10',
        ]);
        $section = \App\Modules\Academics\Models\Section::query()->create([
            'school_id' => $this->school->id,
            'name' => 'A',
            'code' => 'A',
        ]);
        $this->classSection = ClassSection::query()->create([
            'school_id' => $this->school->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);

        // Create subject
        $this->subject = Subject::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Mathematics',
            'code' => 'MATH101',
        ]);

        // Create teacher user with Teacher role
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

        // Create teacher profile
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

        // Assign teacher to class section and subject
        $this->teacher->classSections()->sync([
            $this->classSection->id => ['is_class_teacher' => true, 'school_id' => $this->school->id],
        ]);
        $this->teacher->subjects()->sync([
            $this->subject->id => ['school_id' => $this->school->id],
        ]);

        // Create academic year
        AcademicYear::query()->create([
            'school_id' => $this->school->id,
            'name' => '2025-26',
            'is_active' => true,
            'status' => 'active',
            'starts_on' => now()->subMonths(6),
            'ends_on' => now()->addMonths(6),
        ]);

        // Create leave type
        LeaveType::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Sick Leave',
            'is_active' => true,
        ]);
    }

    public function test_teacher_login_success(): void
    {
        $response = $this->postJson(route('api.v1.teacher.login'), [
            'email' => 'teacher@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success', 'message', 'data' => ['token', 'token_type', 'user', 'teacher', 'school_id'],
            ]);
    }

    public function test_teacher_login_fails_with_wrong_credentials(): void
    {
        $response = $this->postJson(route('api.v1.teacher.login'), [
            'email' => 'teacher@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_teacher_login_fails_for_non_teacher(): void
    {
        $response = $this->postJson(route('api.v1.teacher.login'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
    }

    public function test_teacher_profile(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.teacher.profile'));

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'teacher@test.com');
    }

    public function test_teacher_dashboard(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.teacher.dashboard'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['teacher', 'today_classes', 'pending_homework_count', 'upcoming_exams', 'notifications'],
            ]);
    }

    public function test_teacher_timetable(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.teacher.timetable'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['timetable', 'classes']]);
    }

    public function test_teacher_classes(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.teacher.classes'));

        $response->assertOk()
            ->assertJsonPath('data.classes.0.class', '10')
            ->assertJsonPath('data.classes.0.section', 'A');
    }

    public function test_teacher_attendance_classes(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.teacher.attendance.classes'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['classes']]);
    }

    public function test_teacher_leave_types(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.teacher.leave-types'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['leave_types']]);
    }

    public function test_teacher_logout(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.teacher.logout'));

        $response->assertOk();
    }

    public function test_unauthenticated_access_fails(): void
    {
        $response = $this->getJson(route('api.v1.teacher.dashboard'));
        $response->assertStatus(401);

        $response = $this->getJson(route('api.v1.teacher.profile'));
        $response->assertStatus(401);

        $response = $this->getJson(route('api.v1.teacher.timetable'));
        $response->assertStatus(401);
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
