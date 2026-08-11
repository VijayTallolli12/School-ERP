<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Academics\Models\Subject;
use App\Modules\Exams\Models\Exam;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * ERP Exam Schedule → Student App + Parent App sync.
 *
 * Pins the real-data contract: an exam scheduled in the ERP
 * (status = scheduled, exam_date in the future, linked to the student's
 * active class section) MUST appear in:
 *   GET /api/v1/student/dashboard         → data.upcoming_exams
 *   GET /api/v1/parents/{uuid}/dashboard  → data.upcoming_exams
 *
 * Also guards: exams from other class sections / other schools / other
 * statuses are NOT leaked to the mobile dashboards.
 */
class ExamScheduleSyncTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private AcademicYear $academicYear;
    private ClassSection $classSection;
    private ClassSection $otherClassSection;
    private Subject $subject;
    private User $studentUser;
    private Student $student;
    private User $guardianUser;
    private Guardian $guardian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);

        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
        app(SchoolContext::class)->set($this->school->id);

        // The SchoolSeeder already creates the active academic year for the
        // DEMO school (e.g. 2026-2027). Reuse it to avoid unique conflicts.
        $this->academicYear = AcademicYear::query()
            ->where('school_id', $this->school->id)
            ->where('is_active', true)
            ->firstOrFail();

        $class = SchoolClass::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Class 1',
            'code' => '1',
        ]);
        $section = Section::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Section A',
            'code' => 'A',
        ]);
        $this->classSection = ClassSection::query()->create([
            'school_id' => $this->school->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);

        // A second class section in the same school (for isolation tests)
        $otherClass = SchoolClass::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Class 2',
            'code' => '2',
        ]);
        $otherSection = Section::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Section B',
            'code' => 'B',
        ]);
        $this->otherClassSection = ClassSection::query()->create([
            'school_id' => $this->school->id,
            'class_id' => $otherClass->id,
            'section_id' => $otherSection->id,
        ]);

        $this->subject = Subject::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Computer Science',
            'code' => 'CS',
        ]);

        // ── Student user + record + active session in Class 1 - A ───────
        $this->studentUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Exam Student',
            'email' => 'exam.student@test.com',
            'phone' => '9876500101',
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
            'admission_no' => 'EXM-STU-001',
            'first_name' => 'Exam',
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

        // ── Parent (guardian) linked to the student ──────────────────────
        $this->guardianUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Exam Parent',
            'email' => 'exam.parent@test.com',
            'phone' => '9876500102',
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
            'first_name' => 'Exam',
            'last_name' => 'Parent',
            'phone' => '9876500102',
            'email' => 'exam.parent@test.com',
            'status' => 'active',
        ]);
        $this->guardian->students()->attach($this->student->id, ['relationship' => 'father', 'is_primary' => true]);
    }

    private function makeExam(string $name = 'Test Exam', array $overrides = []): Exam
    {
        return Exam::query()->create(array_merge([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'class_section_id' => $this->classSection->id,
            'subject_id' => $this->subject->id,
            'exam_name' => $name,
            'exam_type' => 'Class Test',
            'exam_date' => now()->addDays(3)->toDateString(),
            'maximum_marks' => 20,
            'pass_marks' => 8,
            'status' => 'scheduled',
            'is_published' => true,
        ], $overrides));
    }

    private function loginToken(string $email): string
    {
        return $this->postJson(route('api.v1.auth.login'), [
            'email' => $email,
            'password' => 'password',
        ])->assertOk()->json('data.token');
    }

    /**
     * Authenticated GET that mirrors a real device request.
     *
     * PHPUnit reuses the app container across requests within one test, so
     * Laravel's RequestGuard caches the previous request's user. In production
     * every HTTP request is a fresh lifecycle — forgetGuards() restores that
     * per-request behavior so each token resolves to its own user.
     */
    private function authedGet(string $token, string $uri): \Illuminate\Testing\TestResponse
    {
        app('auth')->forgetGuards();

        return $this->withToken($token)->getJson($uri);
    }

    public function test_student_dashboard_returns_scheduled_exam(): void
    {
        $exam = $this->makeExam('Test Exam');

        $response = $this->authedGet($this->loginToken('exam.student@test.com'), route('api.v1.student.dashboard'));

        $response->assertOk()
            ->assertJsonPath('data.upcoming_exams.0.id', $exam->id)
            ->assertJsonPath('data.upcoming_exams.0.exam_name', 'Test Exam')
            ->assertJsonPath('data.upcoming_exams.0.exam_type', 'Class Test')
            ->assertJsonPath('data.upcoming_exams.0.exam_date', now()->addDays(3)->toDateString())
            ->assertJsonPath('data.upcoming_exams.0.subject', 'Computer Science');
    }

    public function test_parent_dashboard_returns_scheduled_exam_for_child(): void
    {
        $exam = $this->makeExam('Test Exam');

        $response = $this->authedGet($this->loginToken('exam.parent@test.com'), route('api.v1.parents.dashboard', $this->guardian->uuid));

        $response->assertOk()
            ->assertJsonPath('data.upcoming_exams.0.id', $exam->id)
            ->assertJsonPath('data.upcoming_exams.0.exam_name', 'Test Exam')
            ->assertJsonPath('data.upcoming_exams.0.exam_type', 'Class Test')
            ->assertJsonPath('data.upcoming_exams.0.exam_date', now()->addDays(3)->toDateString())
            ->assertJsonPath('data.upcoming_exams.0.subject', 'Computer Science')
            ->assertJsonPath('data.upcoming_exams.0.class_section', 'Class 1 Section A');
    }

    public function test_exam_from_other_class_section_is_not_returned(): void
    {
        $this->makeExam('Other Class Exam', [
            'class_section_id' => $this->otherClassSection->id,
        ]);

        $studentResponse = $this->authedGet($this->loginToken('exam.student@test.com'), route('api.v1.student.dashboard'));

        $studentResponse->assertOk()
            ->assertJsonCount(0, 'data.upcoming_exams');

        $parentResponse = $this->authedGet($this->loginToken('exam.parent@test.com'), route('api.v1.parents.dashboard', $this->guardian->uuid));

        $parentResponse->assertOk()
            ->assertJsonCount(0, 'data.upcoming_exams');
    }

    public function test_completed_exam_is_not_returned(): void
    {
        $this->makeExam('Past Exam', [
            'exam_date' => now()->subDays(3)->toDateString(),
            'status' => 'completed',
        ]);

        $studentResponse = $this->authedGet($this->loginToken('exam.student@test.com'), route('api.v1.student.dashboard'));

        $studentResponse->assertOk()
            ->assertJsonCount(0, 'data.upcoming_exams');

        $parentResponse = $this->authedGet($this->loginToken('exam.parent@test.com'), route('api.v1.parents.dashboard', $this->guardian->uuid));

        $parentResponse->assertOk()
            ->assertJsonCount(0, 'data.upcoming_exams');
    }

    public function test_exam_from_other_school_is_not_returned(): void
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

        $otherYear = AcademicYear::query()->create([
            'school_id' => $otherSchool->id,
            'name' => '2026-2027',
            'is_active' => true,
            'status' => 'active',
            'starts_on' => now()->subMonths(6),
            'ends_on' => now()->addMonths(6),
        ]);
        $otherClass = SchoolClass::query()->create([
            'school_id' => $otherSchool->id,
            'name' => 'Class 1',
            'code' => '1',
        ]);
        $otherSection = Section::query()->create([
            'school_id' => $otherSchool->id,
            'name' => 'Section A',
            'code' => 'A',
        ]);
        $otherClassSection = ClassSection::query()->create([
            'school_id' => $otherSchool->id,
            'class_id' => $otherClass->id,
            'section_id' => $otherSection->id,
        ]);
        $otherSubject = Subject::query()->create([
            'school_id' => $otherSchool->id,
            'name' => 'Science',
            'code' => 'SC',
        ]);

        Exam::query()->create([
            'school_id' => $otherSchool->id,
            'academic_year_id' => $otherYear->id,
            'class_section_id' => $otherClassSection->id,
            'subject_id' => $otherSubject->id,
            'exam_name' => 'Other School Exam',
            'exam_type' => 'Class Test',
            'exam_date' => now()->addDays(3)->toDateString(),
            'maximum_marks' => 20,
            'pass_marks' => 8,
            'status' => 'scheduled',
            'is_published' => true,
        ]);

        $studentResponse = $this->authedGet($this->loginToken('exam.student@test.com'), route('api.v1.student.dashboard'));

        $studentResponse->assertOk()
            ->assertJsonCount(0, 'data.upcoming_exams');

        $parentResponse = $this->authedGet($this->loginToken('exam.parent@test.com'), route('api.v1.parents.dashboard', $this->guardian->uuid));

        $parentResponse->assertOk()
            ->assertJsonCount(0, 'data.upcoming_exams');
    }
}
