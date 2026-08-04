<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Modules\Lifecycle\Models\StudentTransfer;
use App\Modules\Students\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentLifecycleTcTest extends TestCase
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

    public function test_school_admin_can_issue_tc_for_student(): void
    {
        [$school] = $this->actingAdmin();

        $student = Student::query()
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->firstOrFail();

        $response = $this->postJson(route('admin.lifecycle.tc'), [
            'student_id' => $student->id,
            'transferred_on' => now()->toDateString(),
            'tc_issued_on' => now()->toDateString(),
            'reason' => 'Family relocation',
            'conduct' => 'Excellent',
            'destination_school' => 'City Public School',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('student_transfers', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'transfer_type' => 'tc',
            'status' => 'issued',
            'conduct' => 'Excellent',
            'destination_school' => 'City Public School',
        ]);

        $this->assertNotNull(StudentTransfer::query()->where('student_id', $student->id)->value('tc_no'));

        $student->refresh();
        $this->assertEquals('transferred', $student->status);
        $this->assertFalse($student->sessions()->where('status', 'active')->exists());
    }

    public function test_tc_auto_generates_unique_number_when_not_provided(): void
    {
        $this->actingAdmin();

        $student = Student::query()
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->firstOrFail();

        $this->postJson(route('admin.lifecycle.tc'), [
            'student_id' => $student->id,
        ])->assertOk();

        $tcNo = StudentTransfer::query()
            ->where('student_id', $student->id)
            ->where('transfer_type', 'tc')
            ->value('tc_no');

        $this->assertNotNull($tcNo);
        $this->assertMatchesRegularExpression('/^TC-'.now()->year.'-\d{4}$/', (string) $tcNo);
    }

    public function test_tc_closes_active_session_and_records_left_on(): void
    {
        $this->actingAdmin();

        $student = Student::query()
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->with('sessions')
            ->firstOrFail();

        $activeSession = $student->sessions->firstWhere('status', 'active');

        $this->postJson(route('admin.lifecycle.tc'), [
            'student_id' => $student->id,
            'transferred_on' => now()->toDateString(),
        ])->assertOk();

        $activeSession->refresh();

        $this->assertEquals('transferred', $activeSession->status);
        $this->assertEquals(now()->toDateString(), $activeSession->left_on?->toDateString());
    }

    public function test_tc_requires_valid_student(): void
    {
        $this->actingAdmin();

        $this->postJson(route('admin.lifecycle.tc'), [
            'student_id' => 999999,
        ])->assertStatus(422);
    }

    public function test_tc_print_page_renders(): void
    {
        [$school] = $this->actingAdmin();

        $student = Student::query()
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->firstOrFail();

        $this->postJson(route('admin.lifecycle.tc'), [
            'student_id' => $student->id,
            'conduct' => 'Good',
        ])->assertOk();

        $transfer = StudentTransfer::query()
            ->where('student_id', $student->id)
            ->where('transfer_type', 'tc')
            ->firstOrFail();

        $this->get(route('admin.lifecycle.tc.print', $transfer))
            ->assertOk()
            ->assertSee('Transfer Certificate')
            ->assertSee((string) $transfer->tc_no)
            ->assertSee('Good');
    }
}
