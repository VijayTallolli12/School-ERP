<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Modules\Lifecycle\Models\StudentTransfer;
use App\Modules\Students\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentLifecycleTransferTest extends TestCase
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

    public function test_school_admin_can_view_lifecycle_index(): void
    {
        [$school] = $this->actingAdmin();

        $this->get(route('admin.lifecycle.index'))
            ->assertOk()
            ->assertSee('Student Lifecycle');
    }

    public function test_school_admin_can_transfer_a_student(): void
    {
        [$school] = $this->actingAdmin();

        $student = Student::query()
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->firstOrFail();

        $response = $this->postJson(route('admin.lifecycle.transfer'), [
            'student_id' => $student->id,
            'transferred_on' => now()->toDateString(),
            'reason' => 'Family relocation',
            'destination_school' => 'City Public School',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('student_transfers', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'transfer_type' => 'transfer',
            'status' => 'issued',
        ]);

        $student->refresh();
        $this->assertEquals('transferred', $student->status);
        $this->assertFalse($student->sessions()->where('status', 'active')->exists());
    }

    public function test_transfer_closes_active_session_and_records_left_on(): void
    {
        [$school] = $this->actingAdmin();

        $student = Student::query()
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->with('sessions')
            ->firstOrFail();

        $activeSession = $student->sessions->firstWhere('status', 'active');

        $this->postJson(route('admin.lifecycle.transfer'), [
            'student_id' => $student->id,
            'transferred_on' => now()->toDateString(),
        ])->assertOk();

        $activeSession->refresh();

        $this->assertEquals('transferred', $activeSession->status);
        $this->assertEquals(now()->toDateString(), $activeSession->left_on?->toDateString());
    }

    public function test_already_transferred_student_cannot_be_transferred_again(): void
    {
        [$school] = $this->actingAdmin();

        $student = Student::query()
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->firstOrFail();

        $this->postJson(route('admin.lifecycle.transfer'), [
            'student_id' => $student->id,
        ])->assertOk();

        $this->postJson(route('admin.lifecycle.transfer'), [
            'student_id' => $student->id,
        ])->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertEquals(1, StudentTransfer::query()->where('student_id', $student->id)->count());
    }

    public function test_transfer_requires_valid_student(): void
    {
        $this->actingAdmin();

        $this->postJson(route('admin.lifecycle.transfer'), [
            'student_id' => 999999,
        ])->assertStatus(422);
    }
}
