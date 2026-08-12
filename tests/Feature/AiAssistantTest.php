<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Modules\AiAssistant\Erp\AiResponseGenerator;
use App\Modules\AiAssistant\Erp\ErpQueryExecutor;
use App\Modules\AiAssistant\Erp\QueryPlanner;
use App\Modules\AiAssistant\Erp\ErpToolRegistry;
use App\Modules\AiAssistant\Erp\NaturalLanguageDateParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AI intelligence layer regression suite.
 *
 * Runs against seeded real ERP data (Demo Public School + AiExamDataSeeder)
 * and pins the structured-query contract for the previously failing query:
 *
 *   "Any Mid Term or Half Yearly exam was scheduled on Jan 2026"
 *     => tool exam.search, date_from=2026-01-01, date_to=2026-01-31,
 *        exam_type contains mid_term + half_yearly
 *     => finds the 2026-01-31 Half Yearly / Computer Science / Class 1 - A
 *        exam (Max Marks 100, Completed).
 */
class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $this->seed(\Database\Seeders\AcademicStructureSeeder::class);
        $this->seed(\Database\Seeders\AiExamDataSeeder::class);

        $school = \App\Models\School::query()->where('code', 'DEMO')->firstOrFail();
        app(SchoolContext::class)->set($school->id);

        $admin = \App\Models\User::query()->where('email', 'superadmin@example.com')->firstOrFail();
        auth()->login($admin);
    }

    private function plan(string $question): array
    {
        return app(QueryPlanner::class)->plan($question);
    }

    private function execute(string $tool, array $params): array
    {
        return app(ErpQueryExecutor::class)->execute($tool, $params);
    }

    private function answer(array $result, string $question): string
    {
        return app(AiResponseGenerator::class)->generate($result, $question);
    }

    // ────────────────────────────────────────────────────────────────────
    // MANDATORY FAILING QUERY + VARIATIONS
    // ────────────────────────────────────────────────────────────────────

    public function test_mandatory_query_jan_2026_half_yearly_exam(): void
    {
        $plan = $this->plan('Any Mid Term or Half Yearly exam was scheduled on Jan 2026');

        $this->assertSame('exam.search', $plan['intent']);
        $this->assertSame('2026-01-01', $plan['parameters']['date_from'] ?? null);
        $this->assertSame('2026-01-31', $plan['parameters']['date_to'] ?? null);
        $this->assertContains('mid_term', $plan['parameters']['exam_type'] ?? []);
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);

        $result = $this->execute($plan['intent'], $plan['parameters']);

        $this->assertTrue($result['success']);
        $this->assertGreaterThanOrEqual(1, $result['count']);

        $jan31 = collect($result['records'])->firstWhere('exam_date', '2026-01-31');
        $this->assertNotNull($jan31, 'Expected the 2026-01-31 Half Yearly exam to be found.');
        $this->assertSame('half_yearly', $jan31['exam_type']);
        $this->assertSame('Computer Science', $jan31['subject']);
        $this->assertSame(100, $jan31['maximum_marks']);
        $this->assertSame('completed', $jan31['status']);

        $answer = $this->answer($result, 'Any Mid Term or Half Yearly exam was scheduled on Jan 2026');
        $this->assertStringContainsString('Half Yearly', $answer);
        $this->assertStringContainsString('2026-01-31', $answer);
    }

    public function test_variation_did_we_schedule_half_yearly_in_jan_2026(): void
    {
        $plan = $this->plan('Did we schedule a Half Yearly examination in Jan 2026?');

        $this->assertSame('exam.search', $plan['intent']);
        $this->assertSame('2026-01-01', $plan['parameters']['date_from'] ?? null);
        $this->assertSame('2026-01-31', $plan['parameters']['date_to'] ?? null);
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertContains('half_yearly', collect($result['records'])->pluck('exam_type')->all());
    }

    public function test_variation_any_half_yearly_exam_happened_in_jan(): void
    {
        $plan = $this->plan('Any half yearly exam happened in Jan?');

        $this->assertSame('exam.search', $plan['intent']);
        $this->assertSame(now()->year, (int) substr($plan['parameters']['date_from'] ?? '', 0, 4));
        $this->assertSame('01', substr($plan['parameters']['date_from'] ?? '', 5, 2));
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);
    }

    public function test_variation_midterm_or_half_yearly_tests_during_january_2026(): void
    {
        $plan = $this->plan('Were any midterm or half yearly tests scheduled during January 2026?');

        $this->assertSame('exam.search', $plan['intent']);
        $this->assertSame('2026-01-01', $plan['parameters']['date_from'] ?? null);
        $this->assertSame('2026-01-31', $plan['parameters']['date_to'] ?? null);
        $this->assertContains('mid_term', $plan['parameters']['exam_type'] ?? []);
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);
    }

    public function test_variation_did_class_1_have_any_half_yearly_exam_in_january(): void
    {
        $plan = $this->plan('Did Class 1 have any half yearly exam in January?');

        $this->assertSame('exam.search', $plan['intent']);
        $this->assertArrayHasKey('class', $plan['parameters']);
        $this->assertStringContainsString('Class 1', $plan['parameters']['class'] ?? '');
        $this->assertSame('01', substr($plan['parameters']['date_from'] ?? '', 5, 2));
    }

    // ────────────────────────────────────────────────────────────────────
    // DATE PARSER
    // ────────────────────────────────────────────────────────────────────

    public function test_date_parser_jan_2026(): void
    {
        $parser = new NaturalLanguageDateParser();
        $range = $parser->parse('Jan 2026');
        $this->assertSame('2026-01-01', $range['date_from']);
        $this->assertSame('2026-01-31', $range['date_to']);
    }

    public function test_date_parser_full_date(): void
    {
        $parser = new NaturalLanguageDateParser();
        $range = $parser->parse('15 January 2026');
        $this->assertSame('2026-01-15', $range['date_from']);
        $this->assertSame('2026-01-15', $range['date_to']);
    }

    public function test_date_parser_between_range(): void
    {
        $parser = new NaturalLanguageDateParser();
        $range = $parser->parse('between January 2026 and March 2026');
        $this->assertSame('2026-01-01', $range['date_from']);
        $this->assertSame('2026-03-31', $range['date_to']);
    }

    public function test_date_parser_this_month(): void
    {
        $parser = new NaturalLanguageDateParser();
        $range = $parser->parse('this month');
        $this->assertSame(now()->startOfMonth()->format('Y-m-d'), $range['date_from']);
        $this->assertSame(now()->endOfMonth()->format('Y-m-d'), $range['date_to']);
    }

    // ────────────────────────────────────────────────────────────────────
    // OTHER DOMAINS
    // ────────────────────────────────────────────────────────────────────

    public function test_student_total(): void
    {
        $plan = $this->plan('How many students are there?');
        $this->assertSame('student.total', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertSame('count', $result['result_type']);
    }

    public function test_fee_pending(): void
    {
        $plan = $this->plan('Which students have unpaid fees?');
        $this->assertSame('fee.pending', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertSame('list', $result['result_type']);
    }

    public function test_attendance_absent_today(): void
    {
        $plan = $this->plan('How many students are absent today?');
        $this->assertSame('attendance.absent', $plan['intent']);
        $this->assertSame(now()->format('Y-m-d'), $plan['parameters']['date_from'] ?? null);
    }

    public function test_homework_pending_for_class_3(): void
    {
        $plan = $this->plan('Show pending homework for Class 3.');
        $this->assertSame('homework.pending', $plan['intent']);
        $this->assertStringContainsString('Class 3', $plan['parameters']['class'] ?? '');

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
    }

    public function test_transport_status(): void
    {
        $plan = $this->plan('Which buses are currently active?');
        $this->assertSame('transport.status', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertSame('summary', $result['result_type']);
    }

    public function test_school_summary(): void
    {
        $plan = $this->plan('Give me an executive summary of the school.');
        $this->assertSame('school.summary', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertSame('summary', $result['result_type']);
        $this->assertArrayHasKey('attendance', $result['summary']);
        $this->assertArrayHasKey('fees', $result['summary']);
    }

    public function test_teacher_on_leave(): void
    {
        $plan = $this->plan('Which teachers are on leave today?');
        $this->assertSame('teacher.on_leave', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
    }

    // ────────────────────────────────────────────────────────────────────
    // TOOL REGISTRY SYNONYMS
    // ────────────────────────────────────────────────────────────────────

    public function test_exam_type_synonyms_do_not_false_match(): void
    {
        $registry = app(ErpToolRegistry::class);
        $types = $registry->extractExamTypesFromText('Any Mid Term or Half Yearly exam was scheduled on Jan 2026');

        $this->assertContains('mid_term', $types);
        $this->assertContains('half_yearly', $types);
        $this->assertNotContains('annual', $types);
    }

    public function test_result_type_inference(): void
    {
        $registry = app(ErpToolRegistry::class);
        $this->assertSame('count', $registry->inferResultType('How many exams were conducted?'));
        $this->assertSame('list', $registry->inferResultType('Show all exams in January.'));
    }

    // ────────────────────────────────────────────────────────────────────
    // EXTENDED DOMAIN COVERAGE
    // ────────────────────────────────────────────────────────────────────

    public function test_show_exams_for_class_1(): void
    {
        $plan = $this->plan('Show exams for Class 1.');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertStringContainsString('Class 1', $plan['parameters']['class'] ?? '');

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertGreaterThanOrEqual(1, $result['count']);
        foreach ($result['records'] as $record) {
            $this->assertStringContainsString('Class 1', $record['class'] ?? '');
        }
    }

    public function test_how_many_exams_were_conducted(): void
    {
        $plan = $this->plan('How many exams were conducted?');
        $this->assertSame('exam.count', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertSame('count', $result['result_type']);
        $this->assertGreaterThanOrEqual(1, $result['count']);
    }

    public function test_what_exam_was_held_on_january_31(): void
    {
        $plan = $this->plan('What exam was held on January 31?');

        $this->assertContains($plan['intent'], ['exam.search', 'exam.get']);
        $this->assertSame('2026-01-31', $plan['parameters']['date_from'] ?? null);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertGreaterThanOrEqual(1, $result['count']);
        $this->assertContains('half_yearly', collect($result['records'])->pluck('exam_type')->all());
    }

    public function test_show_completed_exams(): void
    {
        $plan = $this->plan('Show completed exams.');
        $this->assertSame('exam.completed', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertGreaterThanOrEqual(1, $result['count']);
        foreach ($result['records'] as $record) {
            $this->assertSame('completed', $record['status']);
        }
    }

    public function test_upcoming_exams(): void
    {
        $plan = $this->plan('Show me upcoming exams.');
        $this->assertSame('exam.upcoming', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        foreach ($result['records'] as $record) {
            $this->assertSame('scheduled', $record['status']);
        }
    }

    public function test_total_fee_collection_this_month(): void
    {
        $plan = $this->plan('What is the total fee collection this month?');
        $this->assertSame('fee.today_collection', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertSame('summary', $result['result_type']);
    }

    public function test_outstanding_fees_amount(): void
    {
        $plan = $this->plan('How much fees are outstanding?');
        $this->assertSame('fee.outstanding', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('total_outstanding', $result['summary']);
    }

    public function test_show_students_in_class_1(): void
    {
        $plan = $this->plan('Show students in Class 1.');
        $this->assertSame('student.search', $plan['intent']);
        $this->assertStringContainsString('Class 1', $plan['parameters']['class'] ?? '');
    }

    public function test_show_todays_attendance(): void
    {
        $plan = $this->plan("Show today's attendance.");
        $this->assertSame('attendance.summary', $plan['intent']);
        $this->assertSame(now()->format('Y-m-d'), $plan['parameters']['date_from'] ?? null);
    }

    public function test_pending_homework_no_class(): void
    {
        $plan = $this->plan('Which homework is pending?');
        $this->assertSame('homework.pending', $plan['intent']);
    }

    public function test_how_many_teachers(): void
    {
        $plan = $this->plan('How many teachers are there?');
        $this->assertSame('teacher.total', $plan['intent']);
    }

    public function test_pending_leave_requests(): void
    {
        $plan = $this->plan('Show pending leave requests.');
        $this->assertSame('leave.pending', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
    }

    public function test_compound_exam_class_date(): void
    {
        $plan = $this->plan('Show Half Yearly exams for Class 1 in January 2026.');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);
        $this->assertSame('2026-01-01', $plan['parameters']['date_from'] ?? null);
        $this->assertStringContainsString('Class 1', $plan['parameters']['class'] ?? '');
    }

    public function test_unknown_intent_is_friendly(): void
    {
        $plan = $this->plan('What is the meaning of life?');
        $this->assertSame('unknown', $plan['intent']);
    }

    // ────────────────────────────────────────────────────────────────────
    // HTTP ENDPOINT (AIController /admin/ai/ask)
    // ────────────────────────────────────────────────────────────────────

    public function test_http_endpoint_returns_exam_answer(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->actingAs(\App\Models\User::query()->where('email', 'superadmin@example.com')->firstOrFail())
            ->postJson(route('admin.ai.ask'), [
                'question' => 'Any Mid Term or Half Yearly exam was scheduled on Jan 2026',
                'confirmed' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('intent', 'exam.search')
            ->assertJsonStructure(['answer', 'result' => ['count', 'records']]);
    }

    public function test_http_endpoint_never_leaks_route_errors(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->actingAs(\App\Models\User::query()->where('email', 'superadmin@example.com')->firstOrFail())
            ->postJson(route('admin.ai.ask'), [
                'question' => 'Any Mid Term or Half Yearly exam was scheduled on Jan 2026',
            ]);

        $this->assertStringNotContainsString('route not found', $response->json('answer') ?? '');
        $this->assertStringNotContainsString('Internal error', $response->json('answer') ?? '');
    }
}
