<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Fees\Models\FeeCategory;
use App\Modules\Fees\Models\FeeStructure;
use App\Modules\Fees\Models\FeeStructureItem;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Lifecycle\Models\StudentTransfer;
use App\Modules\Students\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentLifecyclePromotionTest extends TestCase
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

    private function activeYear(School $school): AcademicYear
    {
        return AcademicYear::query()
            ->where('school_id', $school->id)
            ->where('is_active', true)
            ->firstOrFail();
    }

    private function createNextYear(School $school, AcademicYear $activeYear): AcademicYear
    {
        return AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => $activeYear->name.' (Next)',
            'starts_on' => $activeYear->ends_on->copy()->addDay()->toDateString(),
            'ends_on' => $activeYear->ends_on->copy()->addYear()->toDateString(),
            'is_active' => false,
            'status' => 'active',
        ]);
    }

    private function eligibleStudent(School $school): Student
    {
        return Student::query()
            ->where('school_id', $school->id)
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->firstOrFail();
    }

    private function targetClassSection(School $school): ClassSection
    {
        return ClassSection::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->firstOrFail();
    }

    public function test_school_admin_can_view_promotions_page(): void
    {
        [$school] = $this->actingAdmin();

        $this->get(route('admin.lifecycle.promotions'))
            ->assertOk()
            ->assertSee('Bulk Student Promotion');
    }

    public function test_promotions_page_only_lists_active_students(): void
    {
        [$school] = $this->actingAdmin();

        $student = $this->eligibleStudent($school);
        $student->update(['status' => 'inactive']);

        $this->get(route('admin.lifecycle.promotions'))
            ->assertOk()
            ->assertDontSee(e($student->full_name));
    }

    public function test_school_admin_can_promote_a_student(): void
    {
        [$school] = $this->actingAdmin();

        $activeYear = $this->activeYear($school);
        $newYear = $this->createNextYear($school, $activeYear);
        $student = $this->eligibleStudent($school);
        $targetClass = $this->targetClassSection($school);

        $response = $this->postJson(route('admin.lifecycle.promotions.store'), [
            'student_ids' => [$student->id],
            'to_academic_year_id' => $newYear->id,
            'to_class_section_id' => $targetClass->id,
            'roll_numbers' => [$student->id => '21'],
        ]);

        $response->assertOk()
            ->assertJson(['success' => true, 'data' => ['promoted' => 1]]);

        $this->assertDatabaseHas('student_sessions', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'academic_year_id' => $activeYear->id,
            'status' => 'promoted',
        ]);

        $this->assertDatabaseHas('student_sessions', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'academic_year_id' => $newYear->id,
            'class_section_id' => $targetClass->id,
            'roll_no' => '21',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('student_transfers', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'transfer_type' => 'promotion',
            'from_academic_year_id' => $activeYear->id,
            'to_academic_year_id' => $newYear->id,
            'status' => 'issued',
        ]);

        $student->refresh();
        $this->assertEquals('active', $student->status);
        $this->assertEquals(1, $student->sessions()->where('status', 'active')->count());
    }

    public function test_promotion_closes_old_session_and_records_left_on(): void
    {
        [$school] = $this->actingAdmin();

        $newYear = $this->createNextYear($school, $this->activeYear($school));
        $student = $this->eligibleStudent($school)->load('sessions');
        $activeSession = $student->sessions->firstWhere('status', 'active');
        $targetClass = $this->targetClassSection($school);

        $this->postJson(route('admin.lifecycle.promotions.store'), [
            'student_ids' => [$student->id],
            'to_academic_year_id' => $newYear->id,
            'to_class_section_id' => $targetClass->id,
        ])->assertOk();

        $activeSession->refresh();

        $this->assertEquals('promoted', $activeSession->status);
        $this->assertEquals(now()->toDateString(), $activeSession->left_on?->toDateString());
    }

    public function test_cannot_promote_into_archived_academic_year(): void
    {
        [$school] = $this->actingAdmin();

        $archivedYear = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => 'Archived Year '.Str::random(4),
            'starts_on' => now()->subYears(2)->toDateString(),
            'ends_on' => now()->subYear()->toDateString(),
            'is_active' => false,
            'status' => 'archived',
        ]);

        $student = $this->eligibleStudent($school);

        $response = $this->postJson(route('admin.lifecycle.promotions.store'), [
            'student_ids' => [$student->id],
            'to_academic_year_id' => $archivedYear->id,
            'to_class_section_id' => $this->targetClassSection($school)->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to_academic_year_id']);

        $this->assertEquals(0, StudentTransfer::query()->where('transfer_type', 'promotion')->count());
    }

    public function test_cannot_promote_into_past_academic_year(): void
    {
        [$school] = $this->actingAdmin();

        $pastYear = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => 'Past Year '.Str::random(4),
            'starts_on' => now()->subYears(3)->toDateString(),
            'ends_on' => now()->subYears(2)->toDateString(),
            'is_active' => false,
            'status' => 'active',
        ]);

        $student = $this->eligibleStudent($school);

        $response = $this->postJson(route('admin.lifecycle.promotions.store'), [
            'student_ids' => [$student->id],
            'to_academic_year_id' => $pastYear->id,
            'to_class_section_id' => $this->targetClassSection($school)->id,
        ]);

        $response->assertOk()
            ->assertJson(['success' => false, 'data' => ['promoted' => 0]])
            ->assertJsonPath('data.skipped.0', $student->full_name);

        $this->assertDatabaseMissing('student_sessions', [
            'student_id' => $student->id,
            'academic_year_id' => $pastYear->id,
        ]);
    }

    public function test_duplicate_roll_number_in_target_class_is_skipped(): void
    {
        [$school] = $this->actingAdmin();

        $newYear = $this->createNextYear($school, $this->activeYear($school));
        $students = Student::query()
            ->where('school_id', $school->id)
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->limit(2)
            ->get();
        $targetClass = $this->targetClassSection($school);

        $response = $this->postJson(route('admin.lifecycle.promotions.store'), [
            'student_ids' => $students->pluck('id')->all(),
            'to_academic_year_id' => $newYear->id,
            'to_class_section_id' => $targetClass->id,
            'roll_numbers' => [
                $students[0]->id => '5',
                $students[1]->id => '5',
            ],
        ]);

        $response->assertOk()
            ->assertJson(['success' => true, 'data' => ['promoted' => 1]])
            ->assertJsonPath('data.skipped.0', $students[1]->full_name);

        $this->assertEquals(1, StudentTransfer::query()->where('transfer_type', 'promotion')->count());
    }

    public function test_already_promoted_student_in_target_year_is_skipped(): void
    {
        [$school] = $this->actingAdmin();

        $newYear = $this->createNextYear($school, $this->activeYear($school));
        $student = $this->eligibleStudent($school);
        $targetClass = $this->targetClassSection($school);

        $this->postJson(route('admin.lifecycle.promotions.store'), [
            'student_ids' => [$student->id],
            'to_academic_year_id' => $newYear->id,
            'to_class_section_id' => $targetClass->id,
        ])->assertOk();

        $response = $this->postJson(route('admin.lifecycle.promotions.store'), [
            'student_ids' => [$student->id],
            'to_academic_year_id' => $newYear->id,
            'to_class_section_id' => $targetClass->id,
        ]);

        $response->assertOk()
            ->assertJson(['success' => false, 'data' => ['promoted' => 0]]);
    }

    public function test_transferred_student_is_not_eligible_for_promotion(): void
    {
        [$school] = $this->actingAdmin();

        $newYear = $this->createNextYear($school, $this->activeYear($school));
        $student = $this->eligibleStudent($school);
        $student->update(['status' => 'transferred']);

        $response = $this->postJson(route('admin.lifecycle.promotions.store'), [
            'student_ids' => [$student->id],
            'to_academic_year_id' => $newYear->id,
            'to_class_section_id' => $this->targetClassSection($school)->id,
        ]);

        $response->assertOk()
            ->assertJson(['success' => false, 'data' => ['promoted' => 0]])
            ->assertJsonPath('data.skipped.0', $student->full_name);

        $this->assertDatabaseMissing('student_sessions', [
            'student_id' => $student->id,
            'academic_year_id' => $newYear->id,
        ]);
    }

    public function test_promotion_assigns_fee_structure_for_new_year_when_available(): void
    {
        [$school] = $this->actingAdmin();

        $activeYear = $this->activeYear($school);
        $newYear = $this->createNextYear($school, $activeYear);
        $student = $this->eligibleStudent($school);
        $targetClass = $this->targetClassSection($school);

        $category = FeeCategory::query()->where('school_id', $school->id)->firstOrFail();

        $structure = FeeStructure::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $newYear->id,
            'class_section_id' => $targetClass->id,
            'name' => 'Promotion Fee Structure',
            'status' => 'active',
        ]);

        FeeStructureItem::query()->create([
            'fee_structure_id' => $structure->id,
            'fee_category_id' => $category->id,
            'amount' => 2500,
            'sort_order' => 1,
        ]);

        $this->postJson(route('admin.lifecycle.promotions.store'), [
            'student_ids' => [$student->id],
            'to_academic_year_id' => $newYear->id,
            'to_class_section_id' => $targetClass->id,
        ])->assertOk();

        $studentFee = StudentFee::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $newYear->id)
            ->firstOrFail();

        $this->assertEquals($structure->id, $studentFee->fee_structure_id);
        $this->assertEquals(1, $studentFee->items()->count());
    }

    public function test_promotion_without_fee_structure_leaves_fee_assignment_untouched(): void
    {
        [$school] = $this->actingAdmin();

        $activeYear = $this->activeYear($school);
        $newYear = $this->createNextYear($school, $activeYear);
        $student = $this->eligibleStudent($school);

        $this->postJson(route('admin.lifecycle.promotions.store'), [
            'student_ids' => [$student->id],
            'to_academic_year_id' => $newYear->id,
            'to_class_section_id' => $this->targetClassSection($school)->id,
        ])->assertOk();

        $this->assertDatabaseMissing('student_fees', [
            'student_id' => $student->id,
            'academic_year_id' => $newYear->id,
        ]);

        $this->assertDatabaseHas('student_fees', [
            'student_id' => $student->id,
            'academic_year_id' => $activeYear->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_promotions_page(): void
    {
        $this->get(route('admin.lifecycle.promotions'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_promote(): void
    {
        [$school] = $this->actingAdmin();

        // parent@school.com is created by ParentSeeder and has no promotion permissions.
        $parent = User::query()->where('email', 'parent@school.com')->firstOrFail();

        $this->actingAs($parent)->withSession(['school_id' => $school->id]);

        $this->get(route('admin.lifecycle.promotions'))
            ->assertForbidden();
    }
}
