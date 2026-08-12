<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use App\Models\User;
use App\Modules\AiAssistant\Erp\QueryPlanner;
use App\Modules\AiAssistant\Services\AIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * CRITICAL FIX: Natural-language ACTION detection for Ask ERP.
 *
 * Real-world action requests ("Send a notification to all students...")
 * were previously misclassified as READ/QUERY requests (returning "Found 3
 * matching records") because:
 *   1. Action keyword matching required exact contiguous phrases
 *      ("send notification" vs "send a notification").
 *   2. The fallback/provider path only ever considered query tools.
 *
 * These tests pin the correct behavior: action requests must be classified
 * as actions, must go through confirmation, and must never auto-execute.
 */
class AiActionDetectionTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);

        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
        app(SchoolContext::class)->set($this->school->id);

        auth()->login(User::query()->where('email', 'superadmin@example.com')->firstOrFail());
    }

    private function plan(string $question): array
    {
        return app(QueryPlanner::class)->plan($question);
    }

    private function ask(string $question, bool $confirmed = false): array
    {
        return app(AIService::class)->ask($question, $confirmed);
    }

    private function makeUser(string $email, string $role): User
    {
        $user = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $role,
            'email' => $email,
            'phone' => '9876500123',
            'password' => bcrypt('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
            'email_verified_at' => now(),
        ]);
        $user->schools()->syncWithoutDetaching([$this->school->id => ['status' => 'active', 'is_primary' => true]]);
        $user->assignRole($role);

        return $user;
    }

    // ─── The exact real-world failing case ──────────────────────────────

    public function test_send_notification_to_all_students_is_detected_as_action(): void
    {
        $plan = $this->plan(
            'Send a notification to all students that they should come tomorrow in colour dress because tomorrow is our school Sports Day.'
        );

        $this->assertSame('notification.send', $plan['intent']);
        $this->assertSame('action', $plan['action']);
        $this->assertSame('students', $plan['parameters']['target_type'] ?? null);
        $this->assertStringContainsStringIgnoringCase('colour dress', $plan['parameters']['message'] ?? '');
    }

    public function test_send_notification_requires_confirmation_not_records(): void
    {
        $result = $this->ask(
            'Send a notification to all students that they should come tomorrow in colour dress because tomorrow is our school Sports Day.',
            false
        );

        $this->assertTrue($result['confirmation_required'] ?? false);
        $this->assertArrayNotHasKey('execution', $result);
        $this->assertArrayNotHasKey('result', $result);
        $this->assertStringNotContainsString('matching records', $result['answer'] ?? '');
    }

    public function test_send_notification_executes_only_after_confirmation(): void
    {
        $result = $this->ask(
            'Send a notification to all students about the school Sports Day.',
            true
        );

        // Confirmation given => not bounced back to confirmation; the action
        // proceeds through NotificationService.
        $this->assertNotTrue($result['confirmation_required'] ?? true);
    }

    // ─── Other action phrasings (natural language tolerance) ────────────

    public function test_notify_all_parents_is_an_action(): void
    {
        $plan = $this->plan("Notify all parents about tomorrow's holiday.");

        $this->assertSame('notification.send', $plan['intent']);
        $this->assertSame('action', $plan['action']);
        $this->assertSame('parents', $plan['parameters']['target_type'] ?? null);
    }

    public function test_create_homework_for_class_5_is_an_action(): void
    {
        $plan = $this->plan('Create homework for Class 5.');

        $this->assertSame('homework.create', $plan['intent']);
        $this->assertSame('action', $plan['action']);
    }

    public function test_publish_the_mathematics_exam_is_an_action(): void
    {
        $plan = $this->plan('Publish the Mathematics exam.');

        $this->assertSame('exam.publish', $plan['intent']);
        $this->assertSame('action', $plan['action']);
    }

    public function test_assign_this_student_to_route_3_is_an_action(): void
    {
        $plan = $this->plan('Assign this student to Route 3.');

        $this->assertSame('transport.assign', $plan['intent']);
        $this->assertSame('action', $plan['action']);
    }

    public function test_generate_the_payroll_is_an_action(): void
    {
        $plan = $this->plan('Generate the payroll.');

        $this->assertSame('payroll.generate', $plan['intent']);
        $this->assertSame('action', $plan['action']);
    }

    public function test_send_fee_reminders_to_defaulters_is_an_action(): void
    {
        $plan = $this->plan('Send fee reminders to defaulters.');

        $this->assertSame('fee.send_reminders', $plan['intent']);
        $this->assertSame('action', $plan['action']);
    }

    public function test_send_absence_notifications_to_parents_is_an_action(): void
    {
        $plan = $this->plan('Send absence notifications to parents of absent students today.');

        $this->assertSame('attendance.notify', $plan['intent']);
        $this->assertSame('action', $plan['action']);
    }

    // ─── READ requests must remain queries ──────────────────────────────

    public function test_show_all_exams_in_january_remains_a_query(): void
    {
        $plan = $this->plan('Show all exams in January.');

        $this->assertSame('exam.search', $plan['intent']);
        $this->assertSame('query', $plan['action']);
    }

    public function test_how_many_students_in_class_1_remains_a_query(): void
    {
        $plan = $this->plan('How many students are in Class 1?');

        $this->assertSame('query', $plan['action']);
        $this->assertSame('student.total', $plan['intent']);
    }

    public function test_who_is_absent_today_remains_a_query(): void
    {
        $plan = $this->plan('Who is absent today?');

        $this->assertSame('query', $plan['action']);
    }

    public function test_show_todays_transport_status_remains_a_query(): void
    {
        $plan = $this->plan('Show today\'s transport status.');

        $this->assertSame('query', $plan['action']);
        $this->assertSame('transport.status', $plan['intent']);
    }

    public function test_pending_fee_amount_remains_a_query(): void
    {
        $plan = $this->plan('How much fees are pending?');

        $this->assertSame('query', $plan['action']);
    }

    // ─── Personal requests are not broadcasts ───────────────────────────

    public function test_send_me_the_list_is_not_a_broadcast(): void
    {
        $plan = $this->plan('Send me the list of absent students.');

        $this->assertSame('query', $plan['action']);
        $this->assertNotSame('notification.send', $plan['intent']);
    }

    // ─── Authorization still gates the action ───────────────────────────

    public function test_teacher_cannot_send_notifications(): void
    {
        $teacher = $this->makeUser('teacher.action@test.com', 'Teacher');
        auth()->login($teacher);

        $result = $this->ask('Send a notification to all students about Sports Day.', false);

        $this->assertFalse($result['success'] ?? true);
    }
}
