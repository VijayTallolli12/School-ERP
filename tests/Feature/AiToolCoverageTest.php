<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Modules\AiAssistant\Erp\AiResponseGenerator;
use App\Modules\AiAssistant\Erp\ErpQueryExecutor;
use App\Modules\AiAssistant\Erp\ErpToolRegistry;
use App\Modules\AiAssistant\Erp\QueryPlanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Every registered query tool must be selectable, executable, produce a valid
 * result envelope, and answer gracefully — against real seeded ERP data.
 */
class AiToolCoverageTest extends TestCase
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
        $this->seed(\Database\Seeders\TeacherSeeder::class);
        $this->seed(\Database\Seeders\StudentSeeder::class);
        $this->seed(\Database\Seeders\FeeCategorySeeder::class);

        $school = \App\Models\School::query()->where('code', 'DEMO')->firstOrFail();
        app(SchoolContext::class)->set($school->id);

        auth()->login(\App\Models\User::query()->where('email', 'superadmin@example.com')->firstOrFail());
    }

    private function registry(): ErpToolRegistry
    {
        return app(ErpToolRegistry::class);
    }

    /**
     * A representative natural-language question for each tool (used to verify
     * the planner can select it) plus a params provider for execution.
     *
     * @return array<string, array{question: string, params: array}>
     */
    private function toolQuestions(): array
    {
        return [
            'exam.search' => ['question' => 'Show all exams in January 2026.', 'params' => ['date_from' => '2026-01-01', 'date_to' => '2026-01-31']],
            'exam.count' => ['question' => 'How many exams were conducted?', 'params' => []],
            'exam.get' => ['question' => 'Show exam details for the exam on 2026-01-31.', 'params' => ['date_from' => '2026-01-31', 'date_to' => '2026-01-31']],
            'exam.upcoming' => ['question' => 'Show me upcoming exams.', 'params' => []],
            'exam.completed' => ['question' => 'Show completed exams.', 'params' => []],
            'student.total' => ['question' => 'How many students are there?', 'params' => []],
            'student.search' => ['question' => 'Show students in Class 1.', 'params' => ['class' => 'Class 1']],
            'student.by_class' => ['question' => 'Show students by class.', 'params' => []],
            'student.admitted_this_month' => ['question' => 'How many students were admitted this month?', 'params' => []],
            'attendance.search' => ['question' => 'Show attendance records.', 'params' => []],
            'attendance.absent' => ['question' => 'Who is absent today?', 'params' => []],
            'attendance.summary' => ['question' => "Show today's attendance.", 'params' => []],
            'attendance.below_75' => ['question' => 'Which students have attendance below 75%?', 'params' => []],
            'fee.outstanding' => ['question' => 'How much fees are outstanding?', 'params' => []],
            'fee.pending' => ['question' => 'Which students have unpaid fees?', 'params' => []],
            'fee.pending_above' => ['question' => 'Show students with pending fees above 1000.', 'params' => ['amount' => 1000]],
            'fee.today_collection' => ['question' => 'What is today\'s fee collection?', 'params' => []],
            'fee.top_defaulters' => ['question' => 'Who are the top fee defaulters?', 'params' => []],
            'homework.pending' => ['question' => 'Show pending homework.', 'params' => []],
            'homework.due' => ['question' => 'What homework is due today?', 'params' => []],
            'homework.list' => ['question' => 'Show all homework.', 'params' => []],
            'teacher.total' => ['question' => 'How many teachers are there?', 'params' => []],
            'teacher.search' => ['question' => 'Show teachers.', 'params' => []],
            'teacher.on_leave' => ['question' => 'Which teachers are on leave today?', 'params' => []],
            'leave.pending' => ['question' => 'Show pending leave requests.', 'params' => []],
            'transport.status' => ['question' => 'What is today\'s transport status?', 'params' => []],
            'transport.routes' => ['question' => 'Show bus routes.', 'params' => []],
            'transport.route_occupancy' => ['question' => 'Show route occupancy.', 'params' => []],
            'transport.students_on_route' => ['question' => 'Show students per route.', 'params' => []],
            'library.books_issued' => ['question' => 'How many books are issued?', 'params' => []],
            'library.overdue_books' => ['question' => 'How many books are overdue?', 'params' => []],
            'library.fine_collection' => ['question' => 'What is the library fine collection?', 'params' => []],
            'payroll.latest_run' => ['question' => 'Show the latest payroll run.', 'params' => []],
            'payroll.locked_runs' => ['question' => 'How many payroll runs are locked?', 'params' => []],
            'payroll.highest_salary' => ['question' => 'Who has the highest salary?', 'params' => []],
            'payroll.generated_this_month' => ['question' => 'How many payroll runs were generated this month?', 'params' => []],
            'payroll.summary' => ['question' => 'What is the payroll summary?', 'params' => []],
            'school.summary' => ['question' => 'Give me an executive summary of the school.', 'params' => []],
        ];
    }

    public function test_every_registered_tool_executes_safely(): void
    {
        $executor = app(ErpQueryExecutor::class);
        $questions = $this->toolQuestions();
        $registryTools = $this->registry()->toolNames();

        $untested = array_diff($registryTools, array_keys($questions));
        $this->assertSame([], $untested, 'Every registered tool must have a coverage entry.');

        foreach ($questions as $tool => $spec) {
            $this->assertContains($tool, $registryTools, "Coverage references unknown tool '{$tool}'.");

            $result = $executor->execute($tool, $spec['params']);

            $this->assertTrue($result['success'], "Tool '{$tool}' execution failed: " . ($result['error'] ?? ''));
            $this->assertSame($tool, $result['tool']);
            $this->assertArrayHasKey('result_type', $result);
            $this->assertArrayHasKey('count', $result);
            $this->assertArrayHasKey('records', $result);
            $this->assertArrayHasKey('summary', $result);
            $this->assertArrayHasKey('filters', $result);
        }
    }

    public function test_planner_selects_tool_for_representative_question(): void
    {
        $planner = app(QueryPlanner::class);

        foreach ($this->toolQuestions() as $tool => $spec) {
            $plan = $planner->plan($spec['question']);
            $this->assertSame($tool, $plan['intent'], "Question '{$spec['question']}' should select '{$tool}', got '{$plan['intent']}'.");
        }
    }

    public function test_empty_results_are_handled_gracefully(): void
    {
        $executor = app(ErpQueryExecutor::class);
        $generator = app(AiResponseGenerator::class);

        // Date range with no exams in the seeded data set.
        $result = $executor->execute('exam.search', ['date_from' => '1999-01-01', 'date_to' => '1999-01-31']);
        $this->assertTrue($result['success']);
        $this->assertSame(0, $result['count']);
        $this->assertSame([], $result['records']);

        $answer = $generator->generate($result, 'Any exams in 1999?');
        $this->assertStringContainsString('No matching records', $answer);
    }

    public function test_invalid_tool_is_rejected_safely(): void
    {
        $executor = app(ErpQueryExecutor::class);
        $result = $executor->execute('not.a_real_tool', []);
        $this->assertFalse($result['success']);
        $this->assertSame('tool_not_found', $result['error_code']);
    }

    public function test_action_tool_rejected_by_query_executor(): void
    {
        $executor = app(ErpQueryExecutor::class);
        $result = $executor->execute('payroll.generate', ['month' => 1, 'year' => 2026]);
        $this->assertFalse($result['success']);
        $this->assertSame('action_not_supported', $result['error_code']);
    }

    public function test_every_count_tool_uses_an_aggregate(): void
    {
        // Count tools must return a scalar count without pulling full record lists.
        $executor = app(ErpQueryExecutor::class);

        foreach (['student.total', 'exam.count', 'teacher.total', 'library.books_issued'] as $tool) {
            DB::enableQueryLog();
            $result = $executor->execute($tool, []);
            $queries = DB::getQueryLog();
            DB::disableQueryLog();

            $this->assertTrue($result['success'], "Tool '{$tool}' failed.");
            $this->assertSame('count', $result['result_type']);
            $this->assertIsInt($result['count']);
            $this->assertGreaterThanOrEqual(0, $result['count']);
            $this->assertNotEmpty($queries, "Tool '{$tool}' ran no queries.");
        }
    }

    public function test_lists_are_capped_at_reasonable_limit(): void
    {
        $executor = app(ErpQueryExecutor::class);

        $result = $executor->execute('exam.search', ['limit' => 100000]);
        $this->assertTrue($result['success']);
        $this->assertLessThanOrEqual(50, count($result['records']));
    }
}
