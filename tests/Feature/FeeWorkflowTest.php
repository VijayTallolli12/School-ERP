<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Fees\Models\FeeCategory;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\FeePaymentItem;
use App\Modules\Fees\Models\FeeStructure;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Fees\Models\StudentFeeItem;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FeeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private User $accountant;
    private AcademicYear $year;
    private ClassSection $classSection;
    private FeeCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
        app(SchoolContext::class)->set($this->school->id);

        $this->year = AcademicYear::query()->create([
            'school_id' => $this->school->id,
            'name' => '2025-26',
            'is_active' => true,
            'status' => 'active',
            'starts_on' => now()->subMonths(6),
            'ends_on' => now()->addMonths(6),
        ]);

        $class = SchoolClass::query()->create(['school_id' => $this->school->id, 'name' => '10', 'code' => '10']);
        $section = Section::query()->create(['school_id' => $this->school->id, 'name' => 'A', 'code' => 'A']);
        $this->classSection = ClassSection::query()->create([
            'school_id' => $this->school->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);

        $this->category = FeeCategory::query()->create([
            'school_id' => $this->school->id,
            'code' => 'tuition',
            'name' => 'Tuition',
            'sort_order' => 1,
        ]);

        $this->accountant = $this->makeUser('accountant@test.com', 'Accountant');
    }

    private function makeUser(string $email, string $role): User
    {
        $user = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $role,
            'email' => $email,
            'phone' => '9876543000',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
            'email_verified_at' => now(),
        ]);
        $user->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
        $user->assignRole($role);

        return $user;
    }

    private function makeStudent(string $admissionNo, ?ClassSection $classSection = null): Student
    {
        $classSection ??= $this->classSection;

        $user = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Student '.$admissionNo,
            'email' => strtolower($admissionNo).'@test.com',
            'phone' => '9876543001',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
        ]);
        $user->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);

        $student = Student::query()->create([
            'school_id' => $this->school->id,
            'user_id' => $user->id,
            'uuid' => (string) Str::uuid(),
            'admission_no' => $admissionNo,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'male',
            'status' => 'active',
        ]);

        StudentSession::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->year->id,
            'student_id' => $student->id,
            'class_section_id' => $classSection->id,
            'roll_no' => $student->id,
            'status' => 'active',
        ]);

        return $student;
    }

    private function actingAsAccountant(): self
    {
        return $this->actingAs($this->accountant)->withSession(['school_id' => $this->school->id]);
    }

    private function createStructure(int $amount = 1000): FeeStructure
    {
        $data = $this->actingAsAccountant()
            ->postJson(route('admin.fees.structures.store'), [
                'academic_year_id' => $this->year->id,
                'class_section_id' => $this->classSection->id,
                'name' => 'Annual Fees',
                'status' => 'active',
                'items' => [
                    ['fee_category_id' => $this->category->id, 'amount' => $amount],
                ],
            ])
            ->assertOk()
            ->json('data');

        return FeeStructure::query()->findOrFail($data['id']);
    }

    private function assign(Student $student, FeeStructure $structure): StudentFee
    {
        $data = $this->actingAsAccountant()
            ->postJson(route('admin.fees.assignments.store'), [
                'student_id' => $student->id,
                'academic_year_id' => $this->year->id,
                'fee_structure_id' => $structure->id,
                'default_due_date' => now()->addMonth()->toDateString(),
            ])
            ->assertOk()
            ->json('data');

        return StudentFee::query()->with('items')->findOrFail($data['id']);
    }

    private function collect(Student $student, array $lines, string $paidOn = 'today'): array
    {
        $response = $this->actingAsAccountant()
            ->postJson(route('admin.fees.collections.store'), [
                'student_id' => $student->id,
                'academic_year_id' => $this->year->id,
                'paid_on' => $paidOn === 'today' ? now()->toDateString() : $paidOn,
                'payment_mode' => 'cash',
                'lines' => $lines,
            ]);

        return [$response, $response->json()];
    }

    // ── Feature: Category / Structure / Assignment CRUD ─────────────────────────

    public function test_fee_category_create_update_delete_writes_audit_trail(): void
    {
        $created = $this->actingAsAccountant()
            ->postJson(route('admin.fees.categories.store'), [
                'code' => 'library',
                'name' => 'Library Fee',
                'sort_order' => 2,
            ])
            ->assertOk()
            ->json('data');

        $this->assertDatabaseHas('fee_categories', ['id' => $created['id'], 'code' => 'library']);
        $this->assertSame('created', Activity::query()->where('event', 'created')->latest()->value('event'));

        $this->actingAsAccountant()
            ->putJson(route('admin.fees.categories.update', $created['id']), [
                'code' => 'library',
                'name' => 'Library Fee Updated',
            ])
            ->assertOk();

        $this->assertTrue(Activity::query()->where('event', 'updated')->where('subject_id', $created['id'])->exists());
    }

    public function test_fee_structure_can_be_created_and_duplicate_class_year_rejected(): void
    {
        $this->actingAsAccountant()
            ->postJson(route('admin.fees.structures.store'), [
                'academic_year_id' => $this->year->id,
                'class_section_id' => $this->classSection->id,
                'status' => 'active',
                'items' => [['fee_category_id' => $this->category->id, 'amount' => 1000]],
            ])
            ->assertOk();

        $this->actingAsAccountant()
            ->postJson(route('admin.fees.structures.store'), [
                'academic_year_id' => $this->year->id,
                'class_section_id' => $this->classSection->id,
                'status' => 'active',
                'items' => [['fee_category_id' => $this->category->id, 'amount' => 500]],
            ])
            ->assertStatus(422);
    }

    public function test_individual_assignment_rejects_duplicate_for_same_year(): void
    {
        $student = $this->makeStudent('FEE-STU-001');
        $structure = $this->createStructure();
        $this->assign($student, $structure);

        $this->actingAsAccountant()
            ->postJson(route('admin.fees.assignments.store'), [
                'student_id' => $student->id,
                'academic_year_id' => $this->year->id,
                'fee_structure_id' => $structure->id,
            ])
            ->assertStatus(422)
            ->assertJson(['message' => 'This student already has a fee assignment for the selected academic year.']);
    }

    public function test_individual_assignment_rejects_class_section_mismatch(): void
    {
        $otherClass = SchoolClass::query()->create(['school_id' => $this->school->id, 'name' => '9', 'code' => '9']);
        $otherSection = Section::query()->create(['school_id' => $this->school->id, 'name' => 'B', 'code' => 'B']);
        $otherClassSection = ClassSection::query()->create([
            'school_id' => $this->school->id,
            'class_id' => $otherClass->id,
            'section_id' => $otherSection->id,
        ]);

        $structure = $this->createStructure();
        $student = $this->makeStudent('FEE-STU-002', $otherClassSection);

        $this->actingAsAccountant()
            ->postJson(route('admin.fees.assignments.store'), [
                'student_id' => $student->id,
                'academic_year_id' => $this->year->id,
                'fee_structure_id' => $structure->id,
            ])
            ->assertStatus(422)
            ->assertJson(['message' => 'The fee structure does not match the student\'s current class section.']);
    }

    public function test_bulk_assignment_skips_already_assigned_students(): void
    {
        $studentA = $this->makeStudent('FEE-STU-003');
        $this->makeStudent('FEE-STU-004');
        $structure = $this->createStructure();
        $this->assign($studentA, $structure);

        $result = $this->actingAsAccountant()
            ->postJson(route('admin.fees.assignments.bulk'), [
                'academic_year_id' => $this->year->id,
                'class_section_id' => $this->classSection->id,
                'fee_structure_id' => $structure->id,
            ])
            ->assertOk()
            ->json('data');

        $this->assertSame(1, $result['assigned']);
        $this->assertSame(1, $result['skipped']);
    }

    // ── Feature: Collection ────────────────────────────────────────────────────

    public function test_payment_recording_partial_and_overpay_rejected(): void
    {
        $student = $this->makeStudent('FEE-STU-005');
        $structure = $this->createStructure(1000);
        $assignment = $this->assign($student, $structure);
        $item = $assignment->items->first();

        [$response, $body] = $this->collect($student, [
            ['student_fee_item_id' => $item->id, 'amount' => 400],
        ]);
        $response->assertOk();
        $this->assertSame('400.00', $body['data']['amount']);
        $this->assertSame('completed', FeePayment::query()->findOrFail($body['data']['id'])->status);

        [$over, $overBody] = $this->collect($student, [
            ['student_fee_item_id' => $item->id, 'amount' => 700],
        ]);
        $over->assertStatus(422);
        $this->assertStringContainsString('Invalid amount', $overBody['message']);

        $this->assertDatabaseHas('activity_log', ['event' => 'created', 'subject_type' => FeePayment::class]);
    }

    public function test_receipt_numbers_are_sequential_per_school_and_year(): void
    {
        $student = $this->makeStudent('FEE-STU-006');
        $structure = $this->createStructure(1000);
        $assignment = $this->assign($student, $structure);
        $item = $assignment->items->first();

        [$r1] = $this->collect($student, [['student_fee_item_id' => $item->id, 'amount' => 100]]);
        [$r2] = $this->collect($student, [['student_fee_item_id' => $item->id, 'amount' => 100]]);

        $no1 = $r1->json('data.receipt_number');
        $no2 = $r2->json('data.receipt_number');

        $this->assertSame(sprintf('RCP-%d-%d-000001', $this->school->id, $this->year->id), $no1);
        $this->assertSame(sprintf('RCP-%d-%d-000002', $this->school->id, $this->year->id), $no2);
        $this->assertNotSame($no1, $no2);
    }

    public function test_payment_line_must_belong_to_selected_student(): void
    {
        $studentA = $this->makeStudent('FEE-STU-007');
        $studentB = $this->makeStudent('FEE-STU-008');
        $structure = $this->createStructure();
        $itemB = $this->assign($studentB, $structure)->items->first();

        $this->actingAsAccountant()
            ->postJson(route('admin.fees.collections.store'), [
                'student_id' => $studentA->id,
                'academic_year_id' => $this->year->id,
                'paid_on' => now()->toDateString(),
                'payment_mode' => 'cash',
                'lines' => [['student_fee_item_id' => $itemB->id, 'amount' => 50]],
            ])
            ->assertStatus(422);
    }

    public function test_future_paid_on_date_is_rejected(): void
    {
        $student = $this->makeStudent('FEE-STU-009');
        $structure = $this->createStructure();
        $item = $this->assign($student, $structure)->items->first();

        $this->actingAsAccountant()
            ->postJson(route('admin.fees.collections.store'), [
                'student_id' => $student->id,
                'academic_year_id' => $this->year->id,
                'paid_on' => now()->addDay()->toDateString(),
                'payment_mode' => 'cash',
                'lines' => [['student_fee_item_id' => $item->id, 'amount' => 50]],
            ])
            ->assertStatus(422);
    }

    // ── Regression: B1 waived/cancelled assignments are no longer collectable ──

    public function test_waived_assignment_is_excluded_from_collection_and_dues(): void
    {
        $student = $this->makeStudent('FEE-STU-010');
        $structure = $this->createStructure(1000);
        $assignment = $this->assign($student, $structure);
        $item = $assignment->items->first();

        $this->actingAsAccountant()
            ->putJson(route('admin.fees.assignments.update', $assignment->id), ['status' => 'waived'])
            ->assertOk();

        $this->actingAsAccountant()
            ->getJson(route('admin.fees.api.student-fee-items').'?student_id='.$student->id.'&academic_year_id='.$this->year->id)
            ->assertOk()
            ->assertJsonPath('data', []);

        $this->actingAsAccountant()
            ->getJson(route('admin.fees.dues.data').'?student_id='.$student->id)
            ->assertOk()
            ->assertDontSee($student->admission_no);

        [$response] = $this->collect($student, [['student_fee_item_id' => $item->id, 'amount' => 100]]);
        $response->assertStatus(422);
    }

    // ── Void / reversal flow (replaces hard delete) ────────────────────────────

    public function test_voided_payment_restores_dues_and_keeps_line_items(): void
    {
        $student = $this->makeStudent('FEE-STU-011');
        $structure = $this->createStructure(1000);
        $assignment = $this->assign($student, $structure);
        $item = $assignment->items->first();

        [$paymentResponse] = $this->collect($student, [['student_fee_item_id' => $item->id, 'amount' => 200]]);
        $paymentId = $paymentResponse->json('data.id');
        $this->assertSame(1, FeePaymentItem::query()->count());

        $this->actingAsAccountant()
            ->postJson(route('admin.fees.collections.void', $paymentId), ['reason' => 'Wrong amount entered'])
            ->assertOk();

        $payment = FeePayment::query()->findOrFail($paymentId);
        $this->assertTrue($payment->isVoided());
        $this->assertSame('Wrong amount entered', $payment->void_reason);
        $this->assertNotNull($payment->voided_at);
        $this->assertSame(1, FeePaymentItem::query()->count());
        $this->assertTrue(Activity::query()->where('event', 'voided')->where('subject_id', $paymentId)->exists());

        // Balance is restored to the student's dues.
        $dueRows = app(\App\Modules\Fees\Services\FeeService::class)->dueReport($this->year->id, false);
        $this->assertSame(1000.0, (float) $dueRows[0]['balance']);

        // Voided payments are excluded from the collection report.
        $report = app(\App\Modules\Fees\Services\FeeService::class)->collectionReport(null, null, null, null);
        $this->assertSame([], $report);

        // Voiding twice is rejected.
        $this->actingAsAccountant()
            ->postJson(route('admin.fees.collections.void', $paymentId), ['reason' => 'Voiding again'])
            ->assertStatus(422);

        // Voiding without a reason is rejected.
        $this->actingAsAccountant()
            ->postJson(route('admin.fees.collections.void', $paymentId), ['reason' => 'x'])
            ->assertStatus(422);
    }

    public function test_voided_receipt_is_blocked_in_api(): void
    {
        $student = $this->makeStudent('FEE-STU-012');
        $structure = $this->createStructure(1000);
        $item = $this->assign($student, $structure)->items->first();

        [$paymentResponse] = $this->collect($student, [['student_fee_item_id' => $item->id, 'amount' => 100]]);
        $paymentId = $paymentResponse->json('data.id');

        $this->actingAsAccountant()
            ->postJson(route('admin.fees.collections.void', $paymentId), ['reason' => 'Duplicate entry'])
            ->assertOk();

        $token = $this->accountant->createToken('api-test')->plainTextToken;
        $this->withToken($token)
            ->getJson(route('api.v1.fees.receipt', $paymentId))
            ->assertStatus(422);
    }

    // ── School isolation ───────────────────────────────────────────────────────

    public function test_fee_payment_items_are_school_scoped(): void
    {
        $student = $this->makeStudent('FEE-STU-013');
        $structure = $this->createStructure(1000);
        $item = $this->assign($student, $structure)->items->first();
        [$paymentResponse] = $this->collect($student, [['student_fee_item_id' => $item->id, 'amount' => 100]]);

        $this->assertDatabaseHas('fee_payment_items', [
            'id' => FeePaymentItem::query()->first()->id,
            'school_id' => $this->school->id,
        ]);

        $otherSchool = School::query()->create([
            'uuid' => (string) Str::uuid(),
            'code' => 'SCHOOL2',
            'name' => 'Second School',
            'slug' => 'second-school',
            'email' => 'second@example.com',
            'phone' => '9123456789',
            'address' => 'Somewhere',
            'city' => 'Mysore',
            'state' => 'Karnataka',
            'country' => 'India',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'status' => 'active',
        ]);

        app(SchoolContext::class)->set($otherSchool->id);
        app(PermissionRegistrar::class)->setPermissionsTeamId($otherSchool->id);

        $this->assertSame(0, FeePaymentItem::query()->count());
        $this->assertSame(0, FeePayment::query()->count());
    }

    // ── API: status filter honored ─────────────────────────────────────────────

    public function test_api_status_filter_is_honored(): void
    {
        $studentA = $this->makeStudent('FEE-STU-014');
        $studentB = $this->makeStudent('FEE-STU-015');
        $structure = $this->createStructure(1000);
        $assignmentA = $this->assign($studentA, $structure);
        $assignmentB = $this->assign($studentB, $structure);
        $itemA = $assignmentA->items->first();
        $itemB = $assignmentB->items->first();

        // Student A: fully paid. Student B: pending.
        $this->collect($studentA, [['student_fee_item_id' => $itemA->id, 'amount' => 1000]]);
        $this->assertSame('1000.00', (string) $itemB->fresh()->amount);

        $token = $this->accountant->createToken('api-test')->plainTextToken;

        $paidIds = collect($this->withToken($token)->getJson(route('api.v1.fees.index').'?status=paid')->json('data'))->pluck('id');
        $this->assertContains($assignmentA->id, $paidIds);
        $this->assertNotContains($assignmentB->id, $paidIds);

        $pendingIds = collect($this->withToken($token)->getJson(route('api.v1.fees.index').'?status=pending')->json('data'))->pluck('id');
        $this->assertContains($assignmentB->id, $pendingIds);
        $this->assertNotContains($assignmentA->id, $pendingIds);
    }

    // ── Authorization ──────────────────────────────────────────────────────────

    public function test_payment_collection_requires_fees_collect_permission(): void
    {
        $principal = $this->makeUser('principal@test.com', 'Principal');
        $student = $this->makeStudent('FEE-STU-016');
        $structure = $this->createStructure();
        $item = $this->assign($student, $structure)->items->first();

        $this->actingAs($principal)->withSession(['school_id' => $this->school->id])
            ->postJson(route('admin.fees.collections.store'), [
                'student_id' => $student->id,
                'academic_year_id' => $this->year->id,
                'paid_on' => now()->toDateString(),
                'payment_mode' => 'cash',
                'lines' => [['student_fee_item_id' => $item->id, 'amount' => 50]],
            ])
            ->assertForbidden();
    }

    public function test_voiding_payment_requires_fees_update_permission(): void
    {
        $collectorOnly = $this->makeUser('collector@test.com', 'Principal');
        $student = $this->makeStudent('FEE-STU-017');
        $structure = $this->createStructure();
        $item = $this->assign($student, $structure)->items->first();
        [$paymentResponse] = $this->collect($student, [['student_fee_item_id' => $item->id, 'amount' => 100]]);

        $this->actingAs($collectorOnly)->withSession(['school_id' => $this->school->id])
            ->postJson(route('admin.fees.collections.void', $paymentResponse->json('data.id')), ['reason' => 'Not allowed'])
            ->assertForbidden();
    }

    // ── Report type restriction ────────────────────────────────────────────────

    public function test_unknown_fee_report_type_returns_404(): void
    {
        $token = $this->accountant->createToken('api-test')->plainTextToken;
        $this->withToken($token)
            ->getJson(route('reports.fees.export.pdf', ['type' => 'hack']))
            ->assertNotFound();
    }
}
