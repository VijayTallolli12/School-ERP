<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentLinkageApiTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private AcademicYear $academicYear;

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

        $this->academicYear = AcademicYear::query()
            ->where('school_id', $this->school->id)
            ->where('is_active', true)
            ->firstOrFail();
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function makeStudentUser(string $name = 'Test Student', ?int $schoolId = null): User
    {
        $user = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $name,
            'email' => 'linkage.' . Str::lower(Str::random(8)) . '@test.com',
            'phone' => '9876543210',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $schoolId ?? $this->school->id,
        ]);

        return $user;
    }

    private function makeStudent(User $user, array $overrides = []): Student
    {
        return Student::query()->create(array_merge([
            'school_id' => $overrides['school_id'] ?? $this->school->id,
            'user_id' => $user->id,
            'uuid' => (string) Str::uuid(),
            'admission_no' => 'LNK-' . Str::upper(Str::random(5)),
            'first_name' => $user->name,
            'last_name' => '',
            'gender' => 'male',
            'status' => 'active',
        ], $overrides));
    }

    private function makeSession(Student $student, ?int $academicYearId = null): StudentSession
    {
        return StudentSession::query()->create([
            'school_id' => $student->school_id,
            'academic_year_id' => $academicYearId ?? $this->academicYear->id,
            'student_id' => $student->id,
            'class_section_id' => $this->classSection->id,
            'roll_no' => '1',
            'status' => 'active',
            'joined_on' => now()->subMonths(3),
        ]);
    }

    private function login(array $body): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/v1/student/login', $body);
    }

    // ── Tests ─────────────────────────────────────────────────────────

    public function test_login_returns_complete_identity_payload(): void
    {
        $user = $this->makeStudentUser('Arjun Verma');
        $user->assignRole('Student');
        $student = $this->makeStudent($user, ['first_name' => 'Arjun', 'last_name' => 'Verma']);
        $this->makeSession($student);

        $response = $this->login(['email' => $user->email, 'password' => 'password'])
            ->assertOk();

        $data = $response->json('data');

        $this->assertSame('Student', $data['role']);
        $this->assertSame($student->id, $data['student_id']);
        $this->assertSame($student->uuid, $data['student_uuid']);
        $this->assertSame($this->school->id, $data['school_id']);
        $this->assertSame($this->academicYear->id, $data['academic_year']['id']);
        $this->assertSame($this->academicYear->name, $data['academic_year']['name']);
        $this->assertSame('10', $data['class']);
        $this->assertSame('A', $data['section']);
        $this->assertContains('dashboard.view', $data['permissions']);
        $this->assertContains('attendance.view', $data['permissions']);
        $this->assertSame($this->school->name, $data['branding']['school_name']);
        $this->assertSame($user->email, $data['user']['email']);
        $this->assertSame($student->uuid, $data['student']['uuid']);
    }

    public function test_login_with_no_student_returns_404_with_meaningful_message(): void
    {
        $user = $this->makeStudentUser('Orphan Student');
        $user->assignRole('Student');

        $this->login(['email' => $user->email, 'password' => 'password'])
            ->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'No student is linked to this account.');
    }

    public function test_authenticated_endpoint_returns_404_when_no_student_linked(): void
    {
        $user = $this->makeStudentUser('Orphan Student');
        $user->assignRole('Student');

        $token = $user->createToken('linkage-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/student/dashboard')
            ->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'No student is linked to this account.');
    }

    public function test_broken_user_id_linkage_is_self_healed_on_login(): void
    {
        $user = $this->makeStudentUser('Arjun Verma');
        $user->assignRole('Student');

        $student = Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => null,
            'uuid' => (string) Str::uuid(),
            'admission_no' => 'LNK-BROKEN',
            'first_name' => 'Arjun',
            'last_name' => 'Verma',
            'gender' => 'male',
            'status' => 'active',
        ]);
        $this->makeSession($student);

        $response = $this->login(['email' => $user->email, 'password' => 'password'])
            ->assertOk();

        $this->assertSame($student->id, $response->json('data.student_id'));
        $this->assertSame($user->id, $student->fresh()->user_id);
    }

    public function test_broken_linkage_is_self_healed_by_middleware(): void
    {
        $user = $this->makeStudentUser('Arjun Verma');
        $user->assignRole('Student');

        $student = Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => null,
            'uuid' => (string) Str::uuid(),
            'admission_no' => 'LNK-BROKEN',
            'first_name' => 'Arjun',
            'last_name' => 'Verma',
            'gender' => 'male',
            'status' => 'active',
        ]);
        $this->makeSession($student);

        $token = $user->createToken('linkage-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/student/dashboard')
            ->assertOk();

        $this->assertSame($user->id, $student->fresh()->user_id);
    }

    public function test_ambiguous_linkage_returns_404(): void
    {
        $user = $this->makeStudentUser('Arjun Verma');
        $user->assignRole('Student');

        $this->makeStudent($user, ['first_name' => 'Arjun', 'last_name' => 'Verma', 'admission_no' => 'LNK-A']);
        $this->makeStudent($user, ['first_name' => 'Arjun', 'last_name' => 'Verma', 'admission_no' => 'LNK-B']);

        $this->login(['email' => $user->email, 'password' => 'password'])
            ->assertStatus(404)
            ->assertJsonPath('message', 'The student record for this account is ambiguous. Please contact the administrator.');
    }

    public function test_inactive_student_returns_403(): void
    {
        $user = $this->makeStudentUser('Arjun Verma');
        $user->assignRole('Student');
        $student = $this->makeStudent($user, ['first_name' => 'Arjun', 'last_name' => 'Verma', 'status' => 'inactive']);
        $this->makeSession($student);

        $this->login(['email' => $user->email, 'password' => 'password'])
            ->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'This student account is not active (Arjun Verma).');
    }

    public function test_soft_deleted_student_returns_404(): void
    {
        $user = $this->makeStudentUser('Arjun Verma');
        $user->assignRole('Student');
        $student = $this->makeStudent($user, ['first_name' => 'Arjun', 'last_name' => 'Verma']);
        $student->delete();

        $this->login(['email' => $user->email, 'password' => 'password'])
            ->assertStatus(404)
            ->assertJsonPath('message', 'The student record linked to this account has been archived.');
    }

    public function test_login_without_active_session_returns_null_class_and_section(): void
    {
        $user = $this->makeStudentUser('Arjun Verma');
        $user->assignRole('Student');
        $this->makeStudent($user, ['first_name' => 'Arjun', 'last_name' => 'Verma']);

        $response = $this->login(['email' => $user->email, 'password' => 'password'])
            ->assertOk();

        $data = $response->json('data');

        $this->assertNull($data['class']);
        $this->assertNull($data['section']);
        $this->assertSame($this->academicYear->id, $data['academic_year']['id']);
    }

    public function test_students_school_is_authoritative_for_school_context(): void
    {
        $otherSchool = School::query()->create([
            'uuid' => (string) Str::uuid(),
            'code' => 'OTHER',
            'name' => 'Other School',
            'slug' => 'other-school',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'status' => 'active',
        ]);

        $user = $this->makeStudentUser('Arjun Verma', $this->school->id);
        $user->assignRole('Student');
        $student = $this->makeStudent($user, [
            'school_id' => $otherSchool->id,
            'first_name' => 'Arjun',
            'last_name' => 'Verma',
        ]);

        $response = $this->login(['email' => $user->email, 'password' => 'password'])
            ->assertOk();

        $this->assertSame($otherSchool->id, $response->json('data.school_id'));
        $this->assertSame($student->id, $response->json('data.student_id'));
    }

    public function test_missing_school_pivot_is_self_healed_on_login(): void
    {
        $user = $this->makeStudentUser('Arjun Verma');
        $user->assignRole('Student');
        $student = $this->makeStudent($user, ['first_name' => 'Arjun', 'last_name' => 'Verma']);
        $this->makeSession($student);

        $this->assertFalse($user->schools()->whereKey($this->school->id)->exists());

        $this->login(['email' => $user->email, 'password' => 'password'])
            ->assertOk();

        $this->assertTrue($user->schools()->whereKey($this->school->id)->exists());
    }

    public function test_login_branding_urls_never_contain_localhost(): void
    {
        $user = $this->makeStudentUser('Arjun Verma');
        $user->assignRole('Student');
        $student = $this->makeStudent($user, ['first_name' => 'Arjun', 'last_name' => 'Verma']);
        $this->makeSession($student);

        $response = $this->login(['email' => $user->email, 'password' => 'password'])
            ->assertOk();

        $logo = $response->json('data.branding.school_logo');
        $favicon = $response->json('data.branding.favicon');

        $this->assertNotNull($logo);
        $this->assertNotNull($favicon);
        $this->assertNotContains(parse_url($logo, PHP_URL_HOST), ['localhost', '127.0.0.1']);
        $this->assertNotContains(parse_url($favicon, PHP_URL_HOST), ['localhost', '127.0.0.1']);
    }

    public function test_orphan_student_record_without_user_is_never_exposed(): void
    {
        $orphan = Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => null,
            'uuid' => (string) Str::uuid(),
            'admission_no' => 'ORPHAN-1',
            'first_name' => 'Orphan',
            'last_name' => 'Student',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $user = $this->makeStudentUser('Someone Else');
        $user->assignRole('Student');
        $this->makeStudent($user, ['first_name' => 'Someone', 'last_name' => 'Else']);

        $response = $this->login(['email' => $user->email, 'password' => 'password'])
            ->assertOk();

        $this->assertNotSame($orphan->id, $response->json('data.student_id'));
        $this->assertSame($this->school->id, $response->json('data.school_id'));
    }
}
