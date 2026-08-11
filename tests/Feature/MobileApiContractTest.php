<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use App\Modules\Teachers\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Mobile API contract tests.
 *
 * Pins the canonical JSON shapes consumed by the mobile apps
 * (see mobile/src/api/types.ts):
 *   POST /api/v1/auth/login  → { token, token_type, user, school_id, ... }
 *   GET  /api/v1/me          → { user, roles, permissions, ... }
 *   GET  /api/v1/parents/{uuid}/dashboard → { students, attendance_summary,
 *                                             fees_summary, exam_results_summary }
 */
class MobileApiContractTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private User $studentUser;
    private Student $student;
    private User $guardianUser;
    private Guardian $guardian;
    private User $teacherUser;

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
            'school_id' => $this->school->id,
            'name' => '2025-26',
            'is_active' => true,
            'status' => 'active',
            'starts_on' => now()->subMonths(6),
            'ends_on' => now()->addMonths(6),
        ]);

        $class = SchoolClass::query()->create([
            'school_id' => $this->school->id,
            'name' => '10',
            'code' => '10',
        ]);
        $section = Section::query()->create([
            'school_id' => $this->school->id,
            'name' => 'A',
            'code' => 'A',
        ]);
        $classSection = ClassSection::query()->create([
            'school_id' => $this->school->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);

        // ── Student user + record + active session ──────────────────────
        $this->studentUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Contract Student',
            'email' => 'contract.student@test.com',
            'phone' => '9876500001',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $this->studentUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);
        $this->studentUser->assignRole('Student');

        $this->student = Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $this->studentUser->id,
            'uuid' => (string) Str::uuid(),
            'admission_no' => 'CTR-STU-001',
            'first_name' => 'Contract',
            'last_name' => 'Student',
            'gender' => 'male',
            'status' => 'active',
        ]);

        StudentSession::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $academicYear->id,
            'student_id' => $this->student->id,
            'class_section_id' => $classSection->id,
            'roll_no' => '7',
            'status' => 'active',
        ]);

        // ── Parent (guardian) user + guardian + linked child ─────────────
        $this->guardianUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Contract Parent',
            'email' => 'contract.parent@test.com',
            'phone' => '9876500002',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $this->guardianUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);
        $this->guardianUser->assignRole('Parent');

        $this->guardian = Guardian::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $this->guardianUser->id,
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Contract',
            'last_name' => 'Parent',
            'phone' => '9876500002',
            'email' => 'contract.parent@test.com',
            'status' => 'active',
        ]);
        $this->guardian->students()->attach($this->student->id, ['relationship' => 'father', 'is_primary' => true]);

        // ── Teacher user + record ────────────────────────────────────────
        $this->teacherUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Contract Teacher',
            'email' => 'contract.teacher@test.com',
            'phone' => '9876500003',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $this->teacherUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);
        $this->teacherUser->assignRole('Teacher');

        Teacher::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $this->teacherUser->id,
            'uuid' => (string) Str::uuid(),
            'employee_id' => 'CTR-TCH-001',
            'first_name' => 'Contract',
            'last_name' => 'Teacher',
            'gender' => 'female',
            'phone' => '9876500003',
            'email' => 'contract.teacher@test.com',
            'status' => 'active',
        ]);
    }

    public function test_generic_login_returns_student_context_for_student_user(): void
    {
        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'contract.student@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email'],
                    'school_id',
                    'student' => ['uuid', 'name', 'class', 'section', 'roll_number'],
                ],
            ])
            ->assertJsonPath('data.user.email', 'contract.student@test.com')
            ->assertJsonPath('data.school_id', $this->school->id);
    }

    public function test_generic_login_returns_parent_context_for_parent_user(): void
    {
        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'contract.parent@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email'],
                    'school_id',
                    'students',
                    'parent_uuid',
                ],
            ])
            ->assertJsonPath('data.parent_uuid', $this->guardian->uuid)
            ->assertJsonCount(1, 'data.students')
            ->assertJsonPath('data.students.0.uuid', $this->student->uuid);
    }

    public function test_me_returns_roles_and_permissions(): void
    {
        $token = $this->loginToken('contract.teacher@test.com');

        $response = $this->withToken($token)
            ->getJson(route('api.v1.me'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'roles',
                    'permissions',
                ],
            ])
            ->assertJsonPath('data.user.email', 'contract.teacher@test.com')
            ->assertJsonPath('data.roles.0', 'Teacher');
    }

    public function test_me_returns_parent_children(): void
    {
        $token = $this->loginToken('contract.parent@test.com');

        $response = $this->withToken($token)
            ->getJson(route('api.v1.me'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'roles',
                    'permissions',
                    'students',
                    'parent_uuid',
                ],
            ])
            ->assertJsonPath('data.parent_uuid', $this->guardian->uuid)
            ->assertJsonCount(1, 'data.students');
    }

    public function test_parent_dashboard_contract(): void
    {
        $token = $this->loginToken('contract.parent@test.com');

        $response = $this->withToken($token)
            ->getJson(route('api.v1.parents.dashboard', $this->guardian->uuid));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'students',
                    'attendance_summary' => ['present', 'absent', 'total', 'percentage'],
                    'fees_summary' => ['total', 'paid', 'pending'],
                    'exam_results_summary' => ['average', 'subjects', 'total_marks', 'obtained_marks'],
                    'upcoming_exams',
                ],
            ])
            ->assertJsonPath('data.students.0.uuid', $this->student->uuid);
    }

    public function test_student_dashboard_contract(): void
    {
        $token = $this->loginToken('contract.student@test.com');

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.dashboard'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student',
                    'current_session' => ['class', 'section', 'roll_no', 'academic_year'],
                    'attendance' => ['total_days', 'present_days', 'percentage'],
                    'fees_summary' => ['total', 'paid', 'pending'],
                    'pending_homework_count',
                    'notifications' => ['unread_count'],
                ],
            ])
            ->assertJsonPath('data.current_session.roll_no', '7');
    }

    public function test_teacher_dashboard_contract(): void
    {
        $token = $this->loginToken('contract.teacher@test.com');

        $response = $this->withToken($token)
            ->getJson(route('api.v1.teacher.dashboard'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'teacher',
                    'today_classes',
                    'pending_homework_count',
                    'upcoming_exams',
                    'notifications' => ['unread_count'],
                ],
            ])
            ->assertJsonPath('data.teacher.full_name', 'Contract Teacher');
    }

    private function loginToken(string $email): string
    {
        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => $email,
            'password' => 'password',
        ]);

        $response->assertOk();

        return $response->json('data.token');
    }
}
