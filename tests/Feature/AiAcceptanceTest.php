<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Modules\AiAssistant\Erp\AiResponseGenerator;
use App\Modules\AiAssistant\Erp\ErpQueryExecutor;
use App\Modules\AiAssistant\Erp\QueryPlanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Natural-language acceptance: date understanding, AND/OR filters, result
 * types, and response quality — against real seeded ERP data.
 */
class AiAcceptanceTest extends TestCase
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

        auth()->login(\App\Models\User::query()->where('email', 'superadmin@example.com')->firstOrFail());
    }

    private function plan(string $q): array
    {
        return app(QueryPlanner::class)->plan($q);
    }

    private function execute(string $tool, array $params): array
    {
        return app(ErpQueryExecutor::class)->execute($tool, $params);
    }

    private function answer(array $result, string $q): string
    {
        return app(AiResponseGenerator::class)->generate($result, $q);
    }

    // ────────────────────────────────────────────────────────────────────
    // EXAM NATURAL-LANGUAGE VARIATIONS (Phase 13)
    // ────────────────────────────────────────────────────────────────────

    public function test_exam_variation_show_all_scheduled_in_january(): void
    {
        $plan = $this->plan('Show all exams scheduled in January 2026.');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertSame('2026-01-01', $plan['parameters']['date_from'] ?? null);
        $this->assertSame('2026-01-31', $plan['parameters']['date_to'] ?? null);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertGreaterThanOrEqual(1, $result['count']);
    }

    public function test_exam_variation_any_half_yearly_in_january(): void
    {
        $plan = $this->plan('Were there any Half Yearly exams in January?');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertSame('01', substr($plan['parameters']['date_from'] ?? '', 5, 2));
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertGreaterThanOrEqual(1, $result['count']);
    }

    public function test_exam_variation_did_we_have_half_yearly_in_jan_2026(): void
    {
        $plan = $this->plan('Did we have a Half Yearly examination in Jan 2026?');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertSame('2026-01-01', $plan['parameters']['date_from'] ?? null);
        $this->assertSame('2026-01-31', $plan['parameters']['date_to'] ?? null);
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);
    }

    public function test_exam_variation_show_me_exams_for_class_1(): void
    {
        $plan = $this->plan('Show me exams for Class 1.');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertStringContainsString('Class 1', $plan['parameters']['class'] ?? '');
    }

    public function test_exam_variation_which_subject_had_half_yearly(): void
    {
        $plan = $this->plan('Which subject had the Half Yearly exam?');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertGreaterThanOrEqual(1, $result['count']);
    }

    public function test_exam_variation_how_many_scheduled_in_january(): void
    {
        $plan = $this->plan('How many exams were scheduled in January 2026?');
        $this->assertSame('exam.count', $plan['intent']);
        $this->assertSame('2026-01-01', $plan['parameters']['date_from'] ?? null);
        $this->assertSame('2026-01-31', $plan['parameters']['date_to'] ?? null);
    }

    public function test_exam_variation_which_exams_were_completed(): void
    {
        $plan = $this->plan('Which exams were completed?');
        $this->assertSame('exam.completed', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertGreaterThanOrEqual(1, $result['count']);
    }

    // ────────────────────────────────────────────────────────────────────
    // DATE UNDERSTANDING (Phase 14)
    // ────────────────────────────────────────────────────────────────────

    public function test_date_understanding_today(): void
    {
        $plan = $this->plan('Show exams today.');
        $this->assertSame(now()->format('Y-m-d'), $plan['parameters']['date_from'] ?? null);
        $this->assertSame(now()->format('Y-m-d'), $plan['parameters']['date_to'] ?? null);
    }

    public function test_date_understanding_yesterday(): void
    {
        $plan = $this->plan('Show exams yesterday.');
        $this->assertSame(now()->subDay()->format('Y-m-d'), $plan['parameters']['date_from'] ?? null);
        $this->assertSame(now()->subDay()->format('Y-m-d'), $plan['parameters']['date_to'] ?? null);
    }

    public function test_date_understanding_this_week(): void
    {
        $plan = $this->plan('Show exams this week.');
        $this->assertSame(now()->startOfWeek()->format('Y-m-d'), $plan['parameters']['date_from'] ?? null);
        $this->assertSame(now()->endOfWeek()->format('Y-m-d'), $plan['parameters']['date_to'] ?? null);
    }

    public function test_date_understanding_last_week(): void
    {
        $plan = $this->plan('Show exams last week.');
        $start = now()->startOfWeek()->subWeek();
        $this->assertSame($start->format('Y-m-d'), $plan['parameters']['date_from'] ?? null);
        $this->assertSame($start->copy()->endOfWeek()->format('Y-m-d'), $plan['parameters']['date_to'] ?? null);
    }

    public function test_date_understanding_this_month(): void
    {
        $plan = $this->plan('Show exams this month.');
        $this->assertSame(now()->startOfMonth()->format('Y-m-d'), $plan['parameters']['date_from'] ?? null);
        $this->assertSame(now()->endOfMonth()->format('Y-m-d'), $plan['parameters']['date_to'] ?? null);
    }

    public function test_date_understanding_last_month(): void
    {
        $plan = $this->plan('Show exams last month.');
        $start = now()->startOfMonth()->subMonth();
        $this->assertSame($start->format('Y-m-d'), $plan['parameters']['date_from'] ?? null);
        $this->assertSame($start->copy()->endOfMonth()->format('Y-m-d'), $plan['parameters']['date_to'] ?? null);
    }

    public function test_date_understanding_january_2026(): void
    {
        $plan = $this->plan('Show exams in January 2026.');
        $this->assertSame('2026-01-01', $plan['parameters']['date_from'] ?? null);
        $this->assertSame('2026-01-31', $plan['parameters']['date_to'] ?? null);
    }

    public function test_date_understanding_full_date(): void
    {
        $plan = $this->plan('Show exams on 15 January 2026.');
        $this->assertSame('2026-01-15', $plan['parameters']['date_from'] ?? null);
        $this->assertSame('2026-01-15', $plan['parameters']['date_to'] ?? null);
    }

    public function test_date_understanding_between_range(): void
    {
        $plan = $this->plan('Show exams between January and March 2026.');
        $this->assertSame('2026-01-01', $plan['parameters']['date_from'] ?? null);
        $this->assertSame('2026-03-31', $plan['parameters']['date_to'] ?? null);
    }

    public function test_date_understanding_first_week_of_january(): void
    {
        $plan = $this->plan('Show exams in the first week of January 2026.');
        $this->assertSame('2026-01-01', $plan['parameters']['date_from'] ?? null);
        $this->assertLessThanOrEqual('2026-01-11', $plan['parameters']['date_to'] ?? '');
    }

    public function test_date_understanding_last_week_of_january(): void
    {
        $plan = $this->plan('Show exams in the last week of January 2026.');
        $this->assertGreaterThanOrEqual('2026-01-25', $plan['parameters']['date_from'] ?? '');
        $this->assertSame('2026-01-31', $plan['parameters']['date_to'] ?? null);
    }

    // ────────────────────────────────────────────────────────────────────
    // AND / OR FILTERS (Phase 15)
    // ────────────────────────────────────────────────────────────────────

    public function test_or_exam_types_in_january(): void
    {
        $plan = $this->plan('Show Mid Term or Half Yearly exams in January 2026.');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertContains('mid_term', $plan['parameters']['exam_type'] ?? []);
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);
        $this->assertSame('2026-01-01', $plan['parameters']['date_from'] ?? null);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertGreaterThanOrEqual(1, $result['count']);
        foreach ($result['records'] as $record) {
            $this->assertContains($record['exam_type'], ['mid_term', 'half_yearly']);
        }
    }

    public function test_compound_half_yearly_class_1_january(): void
    {
        $plan = $this->plan('Show Half Yearly exams for Class 1 in January.');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);
        $this->assertStringContainsString('Class 1', $plan['parameters']['class'] ?? '');
        $this->assertSame('01', substr($plan['parameters']['date_from'] ?? '', 5, 2));
    }

    public function test_completed_half_yearly_exams_in_january(): void
    {
        $plan = $this->plan('Show completed Half Yearly exams in January.');
        $this->assertContains($plan['intent'], ['exam.search', 'exam.completed']);
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        foreach ($result['records'] as $record) {
            $this->assertSame('completed', $record['status']);
            $this->assertSame('half_yearly', $record['exam_type']);
        }
    }

    public function test_class_1_exams_in_january(): void
    {
        $plan = $this->plan('Show Class 1 exams in January.');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertStringContainsString('Class 1', $plan['parameters']['class'] ?? '');
        $this->assertSame('01', substr($plan['parameters']['date_from'] ?? '', 5, 2));

        $result = $this->execute($plan['intent'], $plan['parameters']);
        foreach ($result['records'] as $record) {
            $this->assertStringContainsString('Class 1', $record['class'] ?? '');
        }
    }

    // ────────────────────────────────────────────────────────────────────
    // RESULT TYPES (Phase 16)
    // ────────────────────────────────────────────────────────────────────

    public function test_result_type_list(): void
    {
        $plan = $this->plan('Show all exams in January.');
        $this->assertSame('exam.search', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertSame('list', $result['result_type']);
        $this->assertIsArray($result['records']);
    }

    public function test_result_type_count(): void
    {
        $plan = $this->plan('How many exams were held in January?');
        $this->assertSame('exam.count', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertSame('count', $result['result_type']);
        $this->assertIsInt($result['count']);
    }

    public function test_result_type_single(): void
    {
        $plan = $this->plan('Was there a Half Yearly exam on 2026-01-31?');
        $this->assertSame('exam.search', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertGreaterThanOrEqual(1, $result['count']);

        $answer = $this->answer($result, 'Was there a Half Yearly exam on 2026-01-31?');
        $this->assertStringContainsString('2026-01-31', $answer);
    }

    public function test_result_type_summary(): void
    {
        $plan = $this->plan('Give me an exam summary for January.');
        $this->assertSame('exam.search', $plan['intent']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
    }

    public function test_result_type_detail(): void
    {
        $plan = $this->plan('Tell me everything about the January 31 Computer Science exam.');
        $this->assertContains($plan['intent'], ['exam.get', 'exam.search']);

        $result = $this->execute($plan['intent'], $plan['parameters']);
        $this->assertTrue($result['success']);
        $this->assertGreaterThanOrEqual(1, $result['count']);
    }

    // ────────────────────────────────────────────────────────────────────
    // RESPONSE QUALITY (Phase 21)
    // ────────────────────────────────────────────────────────────────────

    public function test_answer_never_exposes_internal_metadata(): void
    {
        $plan = $this->plan('Show exams in January 2026.');
        $result = $this->execute($plan['intent'], $plan['parameters']);
        $answer = $this->answer($result, 'Show exams in January 2026.');

        $forbidden = ['exam.search', 'exam.count', 'QueryPlanner', 'ErpQueryExecutor', 'confidence', 'tool_name', 'route not found'];
        foreach ($forbidden as $token) {
            $this->assertStringNotContainsString($token, $answer);
        }
    }

    public function test_answer_uses_real_data_only(): void
    {
        // No exams in 1999 -> the answer must honestly say none, not invent one.
        $plan = $this->plan('Show exams in 1999.');
        $result = $this->execute($plan['intent'] === 'unknown' ? 'exam.search' : $plan['intent'], $plan['parameters'] ?? []);
        $this->assertTrue($result['success']);
        $this->assertSame(0, $result['count']);

        $answer = $this->answer($result, 'Show exams in 1999.');
        $this->assertStringContainsString('No matching records', $answer);
    }
}
