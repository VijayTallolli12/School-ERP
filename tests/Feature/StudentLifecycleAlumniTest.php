<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Modules\Lifecycle\Models\StudentTransfer;
use App\Modules\Students\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentLifecycleAlumniTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): array
    {
        $this->seed();

        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

        $this->actingAs($user)->withSession(['school_id' => $school->id]);

        return [$school, $user];
    }

    public function test_school_admin_can_mark_student_as_alumni(): void
    {
        [$school] = $this->actingAdmin();

        $student = Student::query()
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->firstOrFail();

        $response = $this->postJson(route('admin.students.alumni', $student));

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('student_transfers', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'transfer_type' => 'alumni',
            'status' => 'issued',
        ]);

        $student->refresh();
        $this->assertEquals('alumni', $student->status);
        $this->assertFalse($student->sessions()->where('status', 'active')->exists());
    }

    public function test_mark_alumni_closes_active_session_and_records_left_on(): void
    {
        $this->actingAdmin();

        $student = Student::query()
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->with('sessions')
            ->firstOrFail();

        $activeSession = $student->sessions->firstWhere('status', 'active');

        $this->postJson(route('admin.students.alumni', $student))->assertOk();

        $activeSession->refresh();

        $this->assertEquals('alumni', $activeSession->status);
        $this->assertEquals(now()->toDateString(), $activeSession->left_on?->toDateString());
    }

    public function test_already_alumni_student_cannot_be_marked_again(): void
    {
        $this->actingAdmin();

        $student = Student::query()
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->firstOrFail();

        $this->postJson(route('admin.students.alumni', $student))->assertOk();

        $this->postJson(route('admin.students.alumni', $student))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertEquals(1, StudentTransfer::query()->where('student_id', $student->id)->count());
    }

    public function test_mark_alumni_requires_valid_student(): void
    {
        $this->actingAdmin();

        $this->postJson(route('admin.students.alumni', 999999))->assertStatus(404);
    }
}
