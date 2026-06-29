<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Events\AttendanceMarked;
use App\Events\TeacherAttendanceMarked;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LiveAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private User $studentUser;
    private Student $student;
    private User $parentUser;
    private Guardian $guardian;
    private User $teacherUser;
    private Teacher $teacher;
    private ClassSection $classSection;
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

        $class = SchoolClass::query()->create([
            'school_id' => $this->school->id, 'name' => '10', 'code' => '10',
        ]);
        $section = Section::query()->create([
            'school_id' => $this->school->id, 'name' => 'A', 'code' => 'A',
        ]);
        $this->classSection = ClassSection::query()->create([
            'school_id' => $this->school->id, 'class_id' => $class->id, 'section_id' => $section->id,
        ]);

        $this->academicYear = AcademicYear::query()->create([
            'school_id' => $this->school->id, 'name' => '2025-26', 'is_active' => true, 'status' => 'active',
            'starts_on' => now()->subMonths(6), 'ends_on' => now()->addMonths(6),
        ]);

        // Student user
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
            'first_name' => 'Ananya',
            'last_name' => 'Sharma',
            'gender' => 'female',
            'status' => 'active',
        ]);

        StudentSession::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'class_section_id' => $this->classSection->id,
            'roll_no' => '1',
            'status' => 'active',
            'joined_on' => now()->subMonths(6),
        ]);

        // Parent user
        $this->parentUser = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Parent User',
            'email' => 'parent@test.com',
            'phone' => '9876543211',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $this->parentUser->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);

        $this->guardian = Guardian::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $this->parentUser->id,
            'first_name' => 'Parent',
            'last_name' => 'User',
            'email' => 'parent@test.com',
            'phone' => '9876543211',
            'status' => 'active',
        ]);
        $this->guardian->students()->sync([
            $this->student->id => ['relationship' => 'mother', 'is_primary' => true],
        ]);

        // Teacher user
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

    // ──────────────────────────────────────────────────────────────────────────
    // Event Dispatch Tests
    // ──────────────────────────────────────────────────────────────────────────

    public function test_attendance_marked_event_dispatched_with_student_name(): void
    {
        Event::fake();

        AttendanceMarked::dispatch(
            schoolId: $this->school->id,
            studentId: $this->student->id,
            status: 'present',
            date: now()->toDateString(),
            studentName: 'Ananya Sharma',
            markedAt: '09:02 AM',
        );

        Event::assertDispatched(AttendanceMarked::class, function ($event) {
            return $event->studentName === 'Ananya Sharma'
                && $event->status === 'present'
                && $event->markedAt === '09:02 AM';
        });
    }

    public function test_teacher_attendance_marked_event_dispatched(): void
    {
        Event::fake();

        TeacherAttendanceMarked::dispatch(
            schoolId: $this->school->id,
            teacherId: $this->teacher->id,
            status: 'present',
            date: now()->toDateString(),
            teacherName: 'Test Teacher',
            markedAt: '08:30 AM',
        );

        Event::assertDispatched(TeacherAttendanceMarked::class, function ($event) {
            return $event->teacherName === 'Test Teacher'
                && $event->status === 'present';
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Listener Creates Database Notification
    // ──────────────────────────────────────────────────────────────────────────

    public function test_attendance_event_creates_notification_for_student_and_parent(): void
    {
        AttendanceMarked::dispatch(
            schoolId: $this->school->id,
            studentId: $this->student->id,
            status: 'present',
            date: now()->toDateString(),
            studentName: 'Ananya Sharma',
            markedAt: '09:02 AM',
        );

        $this->assertDatabaseHas('notifications', [
            'school_id' => $this->school->id,
            'type' => 'attendance_alert',
        ]);

        $notification = \App\Modules\Notifications\Models\Notification::query()
            ->where('school_id', $this->school->id)
            ->where('type', 'attendance_alert')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('Ananya Sharma', $notification->message);
        $this->assertStringContainsString('PRESENT', $notification->message);
        $this->assertStringContainsString('09:02 AM', $notification->message);

        // Both student and parent should be attached
        $attachedUserIds = $notification->users->pluck('id')->all();
        $this->assertContains($this->studentUser->id, $attachedUserIds);
        $this->assertContains($this->parentUser->id, $attachedUserIds);
    }

    public function test_teacher_attendance_event_creates_notification_for_teacher(): void
    {
        TeacherAttendanceMarked::dispatch(
            schoolId: $this->school->id,
            teacherId: $this->teacher->id,
            status: 'present',
            date: now()->toDateString(),
            teacherName: 'Test Teacher',
            markedAt: '08:30 AM',
        );

        $this->assertDatabaseHas('notifications', [
            'school_id' => $this->school->id,
            'type' => 'attendance_alert',
        ]);

        $notification = \App\Modules\Notifications\Models\Notification::query()
            ->where('school_id', $this->school->id)
            ->where('type', 'attendance_alert')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('Test Teacher', $notification->message);
        $this->assertStringContainsString('PRESENT', $notification->message);

        $attachedUserIds = $notification->users->pluck('id')->all();
        $this->assertContains($this->teacherUser->id, $attachedUserIds);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // API Endpoint — Teacher marks attendance → event dispatched
    // ──────────────────────────────────────────────────────────────────────────

    public function test_teacher_mark_attendance_dispatches_event(): void
    {
        Event::fake();

        $this->teacher->classSections()->sync([
            $this->classSection->id => ['is_class_teacher' => true, 'school_id' => $this->school->id],
        ]);

        $token = $this->getTeacherToken();

        $response = $this->withToken($token)
            ->postJson(route('api.v1.teacher.attendance.mark'), [
                'class_section_id' => $this->classSection->id,
                'attendance_date' => now()->toDateString(),
                'students' => [
                    ['student_id' => $this->student->id, 'status' => 'present'],
                ],
            ]);

        $response->assertOk();

        Event::assertDispatched(AttendanceMarked::class, function ($event) {
            return $event->studentId === $this->student->id
                && $event->status === 'present';
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Realtime Status API
    // ──────────────────────────────────────────────────────────────────────────

    public function test_realtime_status_endpoint(): void
    {
        // Seed attendance records
        Attendance::query()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'class_section_id' => $this->classSection->id,
            'academic_year_id' => $this->academicYear->id,
            'attendance_date' => now()->toDateString(),
            'status' => 'present',
            'marked_by' => $this->teacherUser->id,
        ]);

        TeacherAttendance::query()->create([
            'teacher_id' => $this->teacher->id,
            'attendance_date' => now()->toDateString(),
            'status' => 'present',
            'marked_by' => $this->teacherUser->id,
        ]);

        $token = $this->getTeacherToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.attendance.realtime-status'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'date',
                    'student_attendance' => ['total', 'summary' => ['present', 'absent', 'late', 'half_day', 'excused']],
                    'teacher_attendance' => ['total', 'summary' => ['present', 'absent', 'late', 'half_day', 'excused']],
                    'recent_activity',
                ],
            ]);

        $response->assertJsonPath('data.student_attendance.total', 1);
        $response->assertJsonPath('data.student_attendance.summary.present', 1);
        $response->assertJsonPath('data.teacher_attendance.total', 1);
        $response->assertJsonPath('data.teacher_attendance.summary.present', 1);
    }

    public function test_realtime_status_empty_day(): void
    {
        $token = $this->getTeacherToken();

        $response = $this->withToken($token)
            ->getJson(route('api.v1.attendance.realtime-status'));

        $response->assertOk()
            ->assertJsonPath('data.student_attendance.total', 0)
            ->assertJsonPath('data.teacher_attendance.total', 0)
            ->assertJsonPath('data.recent_activity', []);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Notification message format
    // ──────────────────────────────────────────────────────────────────────────

    public function test_attendance_notification_message_format(): void
    {
        AttendanceMarked::dispatch(
            schoolId: $this->school->id,
            studentId: $this->student->id,
            status: 'late',
            date: now()->toDateString(),
            studentName: 'Ananya Sharma',
            markedAt: '09:02 AM',
        );

        $notification = \App\Modules\Notifications\Models\Notification::query()
            ->where('school_id', $this->school->id)
            ->where('type', 'attendance_alert')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals('Ananya Sharma marked LATE at 09:02 AM.', $notification->message);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Unauthenticated access
    // ──────────────────────────────────────────────────────────────────────────

    public function test_realtime_status_unauthenticated(): void
    {
        $response = $this->getJson(route('api.v1.attendance.realtime-status'));
        $response->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function getTeacherToken(): string
    {
        $response = $this->postJson(route('api.v1.teacher.login'), [
            'email' => 'teacher@test.com',
            'password' => 'password',
        ]);

        return $response->json('data.token');
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
