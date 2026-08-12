<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Modules\AiAssistant\Erp\AiResponseGenerator;
use App\Modules\AiAssistant\Erp\ErpQueryExecutor;
use App\Modules\AiAssistant\Erp\QueryPlanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Major ERP domain acceptance tests against real seeded data.
 */
class AiDomainAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $this->seed(\Database\Seeders\AcademicStructureSeeder::class);
        $this->seed(\Database\Seeders\FeeCategorySeeder::class);
        $this->seed(\Database\Seeders\TeacherSeeder::class);
        $this->seed(\Database\Seeders\StudentSeeder::class);
        $this->seed(\Database\Seeders\ParentSeeder::class);
        $this->seed(\Database\Seeders\NotificationSeeder::class);
        $this->seed(\Database\Seeders\Transport\DriverSeeder::class);
        $this->seed(\Database\Seeders\Transport\VehicleSeeder::class);
        $this->seed(\Database\Seeders\Transport\TransportRouteSeeder::class);
        $this->seed(\Database\Seeders\Transport\RouteStopSeeder::class);
        $this->seed(\Database\Seeders\Transport\StudentTransportSeeder::class);
        $this->seed(\Database\Seeders\Transport\TripSeeder::class);
        $this->seed(\Database\Seeders\TimetableSeeder::class);
        $this->seed(\Database\Seeders\AttendanceSeeder::class);
        $this->seed(\Database\Seeders\Golden\GoldenSchoolSeeder::class);
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

    private function ask(string $q): array
    {
        $plan = $this->plan($q);
        if ($plan['intent'] === 'unknown') {
            return ['success' => false, 'answer' => 'unknown'];
        }
        $result = $this->execute($plan['intent'], $plan['parameters']);
        $answer = app(AiResponseGenerator::class)->generate($result, $q);

        return [
            'success' => $result['success'],
            'answer' => $answer,
            'intent' => $plan['intent'],
            'count' => $result['count'] ?? null,
        ];
    }

    // ────────────────────────────────────────────────────────────────────
    // STUDENTS
    // ────────────────────────────────────────────────────────────────────

    public function test_how_many_students_are_there(): void
    {
        $res = $this->ask('How many students are there?');
        $this->assertTrue($res['success']);
        $this->assertSame('student.total', $res['intent']);
        $this->assertGreaterThanOrEqual(1, $res['count']);
    }

    public function test_how_many_students_in_class_1(): void
    {
        $res = $this->ask('How many students are in Class 1?');
        $this->assertTrue($res['success']);
        $this->assertIsInt($res['count']);
    }

    public function test_show_students_in_class_1(): void
    {
        $res = $this->ask('Show students in Class 1.');
        $this->assertTrue($res['success']);
        $this->assertSame('student.search', $res['intent']);
        $this->assertStringContainsString('matching record', $res['answer']);
    }

    // ────────────────────────────────────────────────────────────────────
    // ATTENDANCE
    // ────────────────────────────────────────────────────────────────────

    public function test_who_is_absent_today(): void
    {
        $res = $this->ask('Who is absent today?');
        $this->assertTrue($res['success']);
        $this->assertSame('attendance.absent', $res['intent']);
    }

    public function test_todays_attendance_summary(): void
    {
        $res = $this->ask('Give me today\'s attendance summary.');
        $this->assertTrue($res['success']);
        $this->assertSame('attendance.summary', $res['intent']);
        $this->assertStringContainsString('Attendance', $res['answer']);
    }

    // ────────────────────────────────────────────────────────────────────
    // FEES
    // ────────────────────────────────────────────────────────────────────

    public function test_how_much_fees_are_pending(): void
    {
        $res = $this->ask('How much fees are pending?');
        $this->assertTrue($res['success']);
        $this->assertSame('fee.outstanding', $res['intent']);
        $this->assertStringContainsString('₹', $res['answer']);
    }

    public function test_which_students_have_outstanding_fees(): void
    {
        $res = $this->ask('Which students have outstanding fees?');
        $this->assertTrue($res['success']);
        $this->assertSame('fee.pending', $res['intent']);
    }

    public function test_todays_fee_collection(): void
    {
        $res = $this->ask('What is today\'s fee collection?');
        $this->assertTrue($res['success']);
        $this->assertSame('fee.today_collection', $res['intent']);
        $this->assertStringContainsString('₹', $res['answer']);
    }

    // ────────────────────────────────────────────────────────────────────
    // TEACHERS
    // ────────────────────────────────────────────────────────────────────

    public function test_how_many_teachers(): void
    {
        $res = $this->ask('How many teachers are there?');
        $this->assertTrue($res['success']);
        $this->assertSame('teacher.total', $res['intent']);
        $this->assertGreaterThanOrEqual(1, $res['count']);
    }

    public function test_teachers_on_leave_today(): void
    {
        $res = $this->ask('Which teachers are on leave today?');
        $this->assertTrue($res['success']);
        $this->assertSame('teacher.on_leave', $res['intent']);
    }

    // ────────────────────────────────────────────────────────────────────
    // HOMEWORK
    // ────────────────────────────────────────────────────────────────────

    public function test_what_homework_is_pending(): void
    {
        $res = $this->ask('What homework is pending?');
        $this->assertTrue($res['success']);
        $this->assertSame('homework.pending', $res['intent']);
    }

    public function test_pending_homework_for_class_5(): void
    {
        $res = $this->ask('Show pending homework for Class 5.');
        $this->assertTrue($res['success']);
        $this->assertSame('homework.pending', $res['intent']);
        $this->assertStringContainsString('Class 5', $res['answer']);
    }

    // ────────────────────────────────────────────────────────────────────
    // TRANSPORT
    // ────────────────────────────────────────────────────────────────────

    public function test_todays_transport_status(): void
    {
        $res = $this->ask('What is today\'s transport status?');
        $this->assertTrue($res['success']);
        $this->assertSame('transport.status', $res['intent']);
    }

    public function test_which_routes_are_active(): void
    {
        $res = $this->ask('Which routes are active?');
        $this->assertTrue($res['success']);
        $this->assertContains($res['intent'], ['transport.routes', 'transport.status']);
    }

    // ────────────────────────────────────────────────────────────────────
    // LIBRARY
    // ────────────────────────────────────────────────────────────────────

    public function test_library_books_issued(): void
    {
        $res = $this->ask('How many books are issued?');
        $this->assertTrue($res['success']);
        $this->assertSame('library.books_issued', $res['intent']);
    }

    public function test_library_overdue_books(): void
    {
        $res = $this->ask('How many books are overdue?');
        $this->assertTrue($res['success']);
        $this->assertSame('library.overdue_books', $res['intent']);
    }

    // ────────────────────────────────────────────────────────────────────
    // PAYROLL
    // ────────────────────────────────────────────────────────────────────

    public function test_payroll_latest_run(): void
    {
        $res = $this->ask('Show the latest payroll run.');
        $this->assertTrue($res['success']);
        $this->assertSame('payroll.latest_run', $res['intent']);
    }

    public function test_payroll_summary(): void
    {
        $res = $this->ask('Payroll summary');
        $this->assertTrue($res['success']);
        $this->assertSame('payroll.summary', $res['intent']);
        $this->assertStringContainsString('Payroll', $res['answer']);
        $this->assertStringNotContainsString('Attendance Summary', $res['answer']);
        $this->assertStringNotContainsString('Present', $res['answer']);
    }

    // ────────────────────────────────────────────────────────────────────
    // EXECUTIVE (Phase 19)
    // ────────────────────────────────────────────────────────────────────

    public function test_executive_school_summary_uses_real_data(): void
    {
        $res = $this->ask('Give me today\'s school summary.');
        $this->assertTrue($res['success']);
        $this->assertSame('school.summary', $res['intent']);

        // The answer must reference real computed metrics, not boilerplate.
        $this->assertStringContainsString('Attendance', $res['answer']);
        $this->assertStringContainsString('Fees', $res['answer']);
        $this->assertStringNotContainsString('confidence', strtolower($res['answer']));
        $this->assertStringNotContainsString('QueryPlanner', $res['answer']);
    }

    public function test_executive_what_needs_my_attention(): void
    {
        $res = $this->ask('What needs my attention today?');
        $this->assertTrue($res['success']);
        $this->assertSame('school.summary', $res['intent']);
    }

    public function test_executive_school_performance_summary(): void
    {
        $res = $this->ask('Give me a summary of school performance.');
        $this->assertTrue($res['success']);
        $this->assertSame('school.summary', $res['intent']);
    }
}
