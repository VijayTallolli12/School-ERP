<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Academics\Models\Subject;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Homework\Models\Homework;
use App\Modules\Library\Models\Book;
use App\Modules\Library\Models\BookIssue;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentAppApiTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private User $studentUser;
    private Student $student;
    private ClassSection $classSection;
    private Subject $subject;
    private StudentSession $session;
    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);

        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

        // Create class, section, class_section
        $class = SchoolClass::query()->create([
            'school_id' => $this->school->id, 'name' => '10', 'code' => '10',
        ]);
        $section = Section::query()->create([
            'school_id' => $this->school->id, 'name' => 'A', 'code' => 'A',
        ]);
        $this->classSection = ClassSection::query()->create([
            'school_id' => $this->school->id, 'class_id' => $class->id, 'section_id' => $section->id,
        ]);

        // Create subject
        $this->subject = Subject::query()->create([
            'school_id' => $this->school->id, 'name' => 'Mathematics', 'code' => 'MATH101',
        ]);

        // Create academic year
        $this->academicYear = AcademicYear::query()->create([
            'school_id' => $this->school->id, 'name' => '2025-26', 'is_active' => true, 'status' => 'active',
            'starts_on' => now()->subMonths(6), 'ends_on' => now()->addMonths(6),
        ]);

        // Create student user
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

        // Create student profile
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

        // Create active student session
        $this->session = StudentSession::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'class_section_id' => $this->classSection->id,
            'roll_no' => '1',
            'status' => 'active',
            'joined_on' => now()->subMonths(6),
        ]);
    }

    public function test_student_login_success(): void
    {
        $response = $this->postJson(route('api.v1.student.login'), [
            'email' => 'student@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success', 'message', 'data' => ['token', 'token_type', 'user', 'student', 'school_id'],
            ]);
    }

    public function test_student_login_fails_with_wrong_credentials(): void
    {
        $response = $this->postJson(route('api.v1.student.login'), [
            'email' => 'student@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_student_login_fails_for_non_student(): void
    {
        $response = $this->postJson(route('api.v1.student.login'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(404);
    }

    public function test_student_profile(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.profile'));

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'student@test.com')
            ->assertJsonStructure(['data' => ['user', 'student']]);
    }

    public function test_student_dashboard(): void
    {
        // Seed some data
        Attendance::query()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'class_section_id' => $this->classSection->id,
            'academic_year_id' => $this->academicYear->id,
            'attendance_date' => now()->format('Y-m-d'),
            'status' => 'present',
            'marked_by' => $this->studentUser->id,
        ]);

        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.dashboard'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['student', 'current_session', 'attendance', 'pending_homework_count',
                           'upcoming_exams', 'issued_books_count', 'notifications'],
            ]);
    }

    public function test_student_attendance(): void
    {
        // Create attendance records
        Attendance::query()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'class_section_id' => $this->classSection->id,
            'academic_year_id' => $this->academicYear->id,
            'attendance_date' => now()->format('Y-m-d'),
            'status' => 'present',
            'marked_by' => $this->studentUser->id,
        ]);

        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.attendance'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['month', 'year', 'total_records', 'records']]);
    }

    public function test_student_attendance_monthly(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.attendance.monthly'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['month', 'year', 'total_days', 'present_days', 'percentage', 'breakdown']]);
    }

    public function test_student_attendance_summary(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.attendance.summary'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['total_days', 'present_days', 'percentage', 'breakdown']]);
    }

    public function test_student_homework_index(): void
    {
        Homework::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'class_section_id' => $this->classSection->id,
            'subject_id' => $this->subject->id,
            'title' => 'Test Homework',
            'assigned_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'active',
            'created_by' => $this->studentUser->id,
            'updated_by' => $this->studentUser->id,
        ]);

        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.homework.index'));

        $response->assertOk()
            ->assertJsonStructure(['data', 'message']);
    }

    public function test_student_homework_show(): void
    {
        $homework = Homework::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'class_section_id' => $this->classSection->id,
            'subject_id' => $this->subject->id,
            'title' => 'Test Homework',
            'assigned_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'active',
            'created_by' => $this->studentUser->id,
            'updated_by' => $this->studentUser->id,
        ]);

        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.homework.show', $homework->id));

        $response->assertOk()
            ->assertJsonPath('data.title', 'Test Homework');
    }

    public function test_student_timetable(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.timetable'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['timetable']]);
    }

    public function test_student_exams_index(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.exams.index'));

        $response->assertOk()
            ->assertJsonStructure(['data', 'message']);
    }

    public function test_student_results(): void
    {
        $exam = Exam::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'class_section_id' => $this->classSection->id,
            'subject_id' => $this->subject->id,
            'exam_name' => 'Mid Term',
            'exam_type' => 'Half Yearly',
            'exam_date' => now()->format('Y-m-d'),
            'maximum_marks' => 100,
            'pass_marks' => 35,
            'status' => 'completed',
            'is_published' => true,
            'created_by' => $this->studentUser->id,
            'updated_by' => $this->studentUser->id,
        ]);

        ExamResult::query()->create([
            'school_id' => $this->school->id,
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'marks_obtained' => 85,
            'grade' => 'A',
            'status' => 'pass',
            'created_by' => $this->studentUser->id,
            'updated_by' => $this->studentUser->id,
        ]);

        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.results'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['student', 'results_by_academic_year']]);
    }

    public function test_student_report_card(): void
    {
        $exam = Exam::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'class_section_id' => $this->classSection->id,
            'subject_id' => $this->subject->id,
            'exam_name' => 'Mid Term',
            'exam_type' => 'Half Yearly',
            'exam_date' => now()->format('Y-m-d'),
            'maximum_marks' => 100,
            'pass_marks' => 35,
            'status' => 'completed',
            'is_published' => true,
            'created_by' => $this->studentUser->id,
            'updated_by' => $this->studentUser->id,
        ]);

        ExamResult::query()->create([
            'school_id' => $this->school->id,
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'marks_obtained' => 85,
            'grade' => 'A',
            'status' => 'pass',
            'created_by' => $this->studentUser->id,
            'updated_by' => $this->studentUser->id,
        ]);

        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.report-card'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['student', 'class_section', 'results_by_type']]);
    }

    public function test_student_library_books(): void
    {
        $book = Book::query()->create([
            'school_id' => $this->school->id,
            'title' => 'Test Book',
            'isbn' => '1234567890',
            'quantity' => 5,
            'available_copies' => 4,
            'status' => 'active',
        ]);

        BookIssue::query()->create([
            'school_id' => $this->school->id,
            'book_id' => $book->id,
            'issueable_type' => Student::class,
            'issueable_id' => $this->student->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'status' => 'issued',
        ]);

        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.library.books'));

        $response->assertOk()
            ->assertJsonPath('data.total_issued', 1)
            ->assertJsonStructure(['data' => ['total_issued', 'books']]);
    }

    public function test_student_library_history(): void
    {
        $book = Book::query()->create([
            'school_id' => $this->school->id,
            'title' => 'Test Book',
            'isbn' => '1234567890',
            'quantity' => 5,
            'available_copies' => 5,
            'status' => 'active',
        ]);

        BookIssue::query()->create([
            'school_id' => $this->school->id,
            'book_id' => $book->id,
            'issueable_type' => Student::class,
            'issueable_id' => $this->student->id,
            'issue_date' => now()->subDays(30)->format('Y-m-d'),
            'due_date' => now()->subDays(16)->format('Y-m-d'),
            'return_date' => now()->subDays(14)->format('Y-m-d'),
            'fine_amount' => 10.00,
            'fine_paid' => true,
            'status' => 'returned',
        ]);

        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.library.history'));

        $response->assertOk()
            ->assertJsonStructure(['data', 'message']);
    }

    public function test_student_library_fines(): void
    {
        $book = Book::query()->create([
            'school_id' => $this->school->id,
            'title' => 'Test Book',
            'isbn' => '1234567890',
            'quantity' => 5,
            'available_copies' => 5,
            'status' => 'active',
        ]);

        BookIssue::query()->create([
            'school_id' => $this->school->id,
            'book_id' => $book->id,
            'issueable_type' => Student::class,
            'issueable_id' => $this->student->id,
            'issue_date' => now()->subDays(30)->format('Y-m-d'),
            'due_date' => now()->subDays(16)->format('Y-m-d'),
            'return_date' => now()->subDays(14)->format('Y-m-d'),
            'fine_amount' => 10.00,
            'fine_paid' => false,
            'status' => 'returned',
        ]);

        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.library.fines'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['total_outstanding_fine', 'total_items', 'fines']]);
    }

    public function test_student_notifications(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.student.notifications.index'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['unread_count', 'notifications']]);
    }

    public function test_student_notifications_read(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.student.notifications.read'));

        $response->assertOk();
    }

    public function test_student_logout(): void
    {
        $token = $this->getToken();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.student.logout'));

        $response->assertOk();
    }

    public function test_unauthenticated_access_fails(): void
    {
        $response = $this->getJson(route('api.v1.student.dashboard'));
        $response->assertStatus(401);

        $response = $this->getJson(route('api.v1.student.profile'));
        $response->assertStatus(401);

        $response = $this->getJson(route('api.v1.student.timetable'));
        $response->assertStatus(401);
    }

    private function getToken(): string
    {
        $response = $this->postJson(route('api.v1.student.login'), [
            'email' => 'student@test.com',
            'password' => 'password',
        ]);

        return $response->json('data.token');
    }
}
