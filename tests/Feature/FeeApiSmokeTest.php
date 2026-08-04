<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FeeApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

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

        $guardianUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Guardian',
            'email' => 'guardian@test.com',
            'phone' => '9876543211',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $guardianUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);
        $guardianUser->assignRole('Parent');

        $guardian = Guardian::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $guardianUser->id,
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Guardian',
            'last_name' => 'Test',
            'phone' => '9876543211',
            'email' => 'guardian@test.com',
            'status' => 'active',
        ]);

        $studentUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Student',
            'email' => 'fee.student@test.com',
            'phone' => '9876543212',
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
            'admission_no' => 'FEE-STU-001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'male',
            'status' => 'active',
        ]);

        StudentSession::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $academicYear->id,
            'student_id' => $this->student->id,
            'class_section_id' => $classSection->id,
            'roll_no' => '1',
            'status' => 'active',
        ]);

        $guardian->students()->attach($this->student->id, ['relationship' => 'father', 'is_primary' => true]);
    }

    public function test_guardian_student_fees_works(): void
    {
        $token = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'guardian@test.com',
            'password' => 'password',
        ])->assertOk()->json('data.token');

        $this->withToken($token)
            ->getJson(route('api.v1.fees.index'))
            ->assertOk();

        $this->withToken($token)
            ->getJson(route('api.v1.fees.index') . '?student_id=' . $this->student->id)
            ->assertOk();
    }

    public function test_guardian_blocked_from_other_student_fees(): void
    {
        $otherUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Other Student',
            'email' => 'fee.other@test.com',
            'phone' => '9876543213',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $otherUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);

        $otherStudent = Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $otherUser->id,
            'uuid' => (string) Str::uuid(),
            'admission_no' => 'FEE-STU-002',
            'first_name' => 'Other',
            'last_name' => 'Student',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $token = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'guardian@test.com',
            'password' => 'password',
        ])->assertOk()->json('data.token');

        $this->withToken($token)
            ->getJson(route('api.v1.fees.index') . '?student_id=' . $otherStudent->id)
            ->assertForbidden();
    }

    public function test_guardian_payments_works(): void
    {
        $token = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'guardian@test.com',
            'password' => 'password',
        ])->assertOk()->json('data.token');

        $this->withToken($token)
            ->getJson(route('api.v1.fees.payments'))
            ->assertOk();
    }

    public function test_guardian_blocked_from_pending_fees(): void
    {
        $token = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'guardian@test.com',
            'password' => 'password',
        ])->assertOk()->json('data.token');

        $this->withToken($token)
            ->getJson(route('api.v1.fees.pending'))
            ->assertForbidden();
    }
}
