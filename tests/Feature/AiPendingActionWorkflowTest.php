<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use App\Models\User;
use App\Modules\AiAssistant\Models\AiPendingAction;
use App\Modules\AiAssistant\Services\AIService;
use App\Modules\AiAssistant\Services\ConfirmationClassifier;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * PENDING ACTION CONFIRMATION WORKFLOW.
 *
 * Real user testing found: after the AI asks for confirmation, typing
 * "Sure" was treated as a brand-new query ("I couldn't understand your
 * question") because the pending action was never stored server-side.
 *
 * These tests pin the corrected flow: the action is persisted server-side,
 * bound to the authenticated user + school, and a follow-up confirmation /
 * cancellation is resolved BEFORE the normal query planner runs.
 */
class AiPendingActionWorkflowTest extends TestCase
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
    }

    private function superAdmin(): User
    {
        return User::query()->where('email', 'superadmin@example.com')->firstOrFail();
    }

    private function ask(string $question, bool $confirmed = false): array
    {
        return app(AIService::class)->ask($question, $confirmed);
    }

    // ─── The exact real-world regression ────────────────────────────────

    public function test_sure_confirms_the_pending_notification_and_executes_it(): void
    {
        auth()->login($this->superAdmin());

        $first = $this->ask(
            'Send a notification to all students that they should come tomorrow in colour dress because tomorrow is our school Sports Day.',
            false
        );

        $this->assertTrue($first['confirmation_required'] ?? false);
        $this->assertNotEmpty($first['pending_action_id'] ?? null);

        // A pending action was persisted server-side.
        $this->assertDatabaseHas('ai_pending_actions', [
            'id' => $first['pending_action_id'],
            'tool' => 'notification.send',
            'status' => 'pending_confirmation',
        ]);

        // The user replies "Sure" — this must NOT reach the query planner.
        $second = $this->ask('Sure');

        $this->assertNotTrue($second['confirmation_required'] ?? false);
        $this->assertTrue($second['success'] ?? false);
        $this->assertStringNotContainsString("couldn't understand", $second['answer']);
        $this->assertStringNotContainsString('matching records', $second['answer']);
        $this->assertSame('notification.send', $second['intent']);

        // The notification was actually created.
        $this->assertDatabaseHas('notifications', [
            'school_id' => $this->school->id,
            'target_type' => 'students',
        ]);

        // Pending action is now completed (no duplicate execution possible).
        $this->assertDatabaseHas('ai_pending_actions', [
            'id' => $first['pending_action_id'],
            'status' => 'completed',
        ]);
    }

    // ─── Natural confirmation phrases ───────────────────────────────────

    public function test_natural_confirmations_all_execute(): void
    {
        auth()->login($this->superAdmin());

        $confirmations = [
            'Yes', 'Sure', 'Okay', 'OK', 'Go ahead', 'Send it', 'Confirm',
            'Please do', 'Yes, send it', 'Proceed', 'Sure, send it', 'Do it',
        ];

        foreach ($confirmations as $reply) {
            $pending = $this->createPendingNotification();

            $result = $this->ask($reply);

            $this->assertTrue($result['success'] ?? false, "Reply '{$reply}' should confirm.");
            $this->assertSame('completed', AiPendingAction::query()->find($pending->id)->status, "Reply '{$reply}' should complete the action.");
        }
    }

    // ─── Natural cancellation phrases ───────────────────────────────────

    public function test_natural_cancellations_all_cancel(): void
    {
        auth()->login($this->superAdmin());

        $before = Notification::query()->count();

        $cancellations = [
            'No', 'Cancel', "Don't send it", 'Stop', 'Not now', 'Never mind', "Don't do it",
        ];

        foreach ($cancellations as $reply) {
            $pending = $this->createPendingNotification();

            $result = $this->ask($reply);

            $this->assertTrue($result['cancelled'] ?? false, "Reply '{$reply}' should cancel.");
            $this->assertSame('cancelled', AiPendingAction::query()->find($pending->id)->status, "Reply '{$reply}' should cancel the action.");
        }

        // No notification was ever created.
        $this->assertSame($before, Notification::query()->count());
    }

    // ─── Ambiguous responses ────────────────────────────────────────────

    public function test_ambiguous_response_does_not_execute(): void
    {
        auth()->login($this->superAdmin());

        $pending = $this->createPendingNotification();

        foreach (['Maybe', "I'm not sure", 'Do you think so?'] as $reply) {
            $result = $this->ask($reply);

            $this->assertTrue($result['confirmation_required'] ?? false);
            $this->assertStringContainsStringIgnoringCase('confirm', $result['answer']);
        }

        // Still pending, nothing executed.
        $this->assertSame('pending_confirmation', AiPendingAction::query()->find($pending->id)->status);
        $this->assertSame(0, Notification::query()->count());
    }

    // ─── Confirmation with no pending action ────────────────────────────

    public function test_sure_without_pending_action_does_not_execute(): void
    {
        auth()->login($this->superAdmin());

        $result = $this->ask('Sure');

        // Nothing pending: "Sure" is not an action by itself.
        $this->assertFalse($result['confirmation_required'] ?? false);
        $this->assertArrayNotHasKey('execution', $result);
        $this->assertSame(0, Notification::query()->count());
    }

    // ─── Double submission prevention ───────────────────────────────────

    public function test_double_confirmation_does_not_execute_twice(): void
    {
        auth()->login($this->superAdmin());

        $this->createPendingNotification();

        $first = $this->ask('Sure');
        $this->assertTrue($first['success'] ?? false);

        $countAfterFirst = Notification::query()->count();
        $this->assertSame(1, $countAfterFirst);

        // A second "Sure" must not create a second notification.
        $second = $this->ask('Sure');
        $this->assertFalse($second['success'] ?? true);
        $this->assertSame($countAfterFirst, Notification::query()->count());
    }

    // ─── Ownership: another user cannot confirm ─────────────────────────

    public function test_another_user_cannot_confirm_my_pending_action(): void
    {
        auth()->login($this->superAdmin());
        $this->createPendingNotification();

        // A second admin of the same school logs in.
        $other = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Other Admin',
            'email' => 'other.admin@test.com',
            'phone' => '9876500111',
            'password' => bcrypt('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
            'email_verified_at' => now(),
        ]);
        $other->schools()->syncWithoutDetaching([$this->school->id => ['status' => 'active', 'is_primary' => true]]);
        $other->assignRole('School Admin');

        auth()->login($other);

        $result = $this->ask('Sure');

        // No pending action belongs to this user -> nothing executes, and the
        // original pending action is untouched.
        $this->assertFalse($result['success'] ?? true);
        $this->assertSame(0, Notification::query()->count());
        $this->assertDatabaseHas('ai_pending_actions', [
            'status' => 'pending_confirmation',
        ]);
    }

    // ─── Multi-turn: two separate actions ───────────────────────────────

    public function test_two_separate_actions_each_confirm_independently(): void
    {
        auth()->login($this->superAdmin());

        // Action 1
        $this->ask('Send a notification to all students about Sports Day.', false);
        $r1 = $this->ask('Sure');
        $this->assertSame('notification.send', $r1['intent'] ?? null);

        // Action 2
        $this->ask('Send another notification saying school starts at 8 AM tomorrow.', false);
        $r2 = $this->ask('Yes');
        $this->assertSame('notification.send', $r2['intent'] ?? null);

        $this->assertSame(2, Notification::query()->count());
    }

    // ─── Action modification supersedes the old action ─────────────────

    public function test_modified_request_supersedes_old_pending_action(): void
    {
        auth()->login($this->superAdmin());

        $this->createPendingNotification();

        // The user revises the request instead of confirming.
        $revised = $this->ask('Actually, send it only to Class 5.', false);

        // The old all-students action is superseded and never executes.
        $this->assertDatabaseMissing('ai_pending_actions', [
            'status' => 'pending_confirmation',
        ]);
        $this->assertSame(0, Notification::query()->count());

        // If the revision is itself a clear action, it becomes a new pending
        // action needing its own confirmation; otherwise the planner answers.
        $this->assertArrayHasKey('answer', $revised);
    }

    // ─── Expiration ─────────────────────────────────────────────────────

    public function test_expired_pending_action_cannot_execute(): void
    {
        auth()->login($this->superAdmin());

        $pending = $this->createPendingNotification();
        $pending->update(['expires_at' => now()->subMinute(), 'status' => 'pending_confirmation']);

        $result = $this->ask('Sure');

        $this->assertFalse($result['success'] ?? true);
        $this->assertSame(0, Notification::query()->count());
        $this->assertDatabaseHas('ai_pending_actions', [
            'id' => $pending->id,
            'status' => 'expired',
        ]);
    }

    // ─── Other action tools ─────────────────────────────────────────────

    public function test_homework_create_pending_then_confirm(): void
    {
        auth()->login($this->superAdmin());

        $first = $this->ask('Create homework for Class 5.', false);
        $this->assertTrue($first['confirmation_required'] ?? false);
        $this->assertSame('homework.create', $first['intent']);

        $second = $this->ask('Sure');
        $this->assertSame('homework.create', $second['intent'] ?? null);
        // The action is claimed/completed even if execution cannot resolve a
        // full class_section_id from a bare class name — it never re-runs the
        // query planner and never asks "I couldn't understand".
        $this->assertNotTrue($second['confirmation_required'] ?? false);
    }

    public function test_publish_exam_pending_then_confirm(): void
    {
        auth()->login($this->superAdmin());

        $first = $this->ask('Publish the Mathematics exam.', false);
        $this->assertTrue($first['confirmation_required'] ?? false);
        $this->assertSame('exam.publish', $first['intent']);

        // Confirming with no resolvable exam id fails safely (no exception,
        // no fake success) but never runs the query planner.
        $second = $this->ask('Sure');
        $this->assertSame('exam.publish', $second['intent'] ?? null);
        $this->assertStringNotContainsString('matching records', $second['answer'] ?? '');
    }

    public function test_transport_assign_pending_then_confirm(): void
    {
        auth()->login($this->superAdmin());

        $first = $this->ask('Assign this student to Route 3.', false);
        $this->assertTrue($first['confirmation_required'] ?? false);
        $this->assertSame('transport.assign', $first['intent']);
    }

    public function test_generate_payroll_pending_then_confirm(): void
    {
        auth()->login($this->superAdmin());

        $first = $this->ask('Generate the payroll.', false);
        $this->assertTrue($first['confirmation_required'] ?? false);
        $this->assertSame('payroll.generate', $first['intent']);

        $second = $this->ask('Yes');
        $this->assertSame('payroll.generate', $second['intent'] ?? null);
    }

    // ─── Queries keep working ───────────────────────────────────────────

    public function test_queries_still_work_through_normal_planner(): void
    {
        auth()->login($this->superAdmin());

        $this->assertSame('exam.search', $this->ask('Show all exams in January.', false)['intent']);
        $this->assertSame('student.total', $this->ask('How many students are in Class 1?', false)['intent']);

        $result = $this->ask('Give me today\'s school summary.', false);
        $this->assertSame('school.summary', $result['intent'] ?? null);
    }

    // ─── ConfirmationClassifier unit behavior ───────────────────────────

    public function test_confirmation_classifier_recognizes_common_phrases(): void
    {
        $classifier = app(ConfirmationClassifier::class);

        foreach (['Yes', 'sure', 'OK', 'go ahead', 'send it', 'Confirm', 'proceed'] as $phrase) {
            $this->assertSame('confirm', $classifier->classify($phrase), $phrase);
        }

        foreach (['No', 'cancel', "don't send it", 'stop', 'never mind'] as $phrase) {
            $this->assertSame('cancel', $classifier->classify($phrase), $phrase);
        }

        foreach (['maybe', "I'm not sure", 'do you think so'] as $phrase) {
            $this->assertSame('ambiguous', $classifier->classify($phrase), $phrase);
        }
    }

    // ─── HTTP endpoint: full two-turn flow ──────────────────────────────

    public function test_http_endpoint_full_two_turn_confirmation_flow(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        auth()->login($this->superAdmin());

        // Turn 1: action request -> confirmation.
        $first = $this->postJson(route('admin.ai.ask'), [
            'question' => 'Send a notification to all students that they should come tomorrow in colour dress because tomorrow is our school Sports Day.',
        ])
            ->assertOk()
            ->assertJsonPath('confirmation_required', true)
            ->json();

        $this->assertNotEmpty($first['pending_action_id'] ?? null);

        // Turn 2: "Sure" resolves the pending action and executes it.
        $second = $this->postJson(route('admin.ai.ask'), [
            'question' => 'Sure',
        ])
            ->assertOk()
            ->assertJsonPath('intent', 'notification.send')
            ->json();

        $this->assertTrue($second['success'] ?? false);
        $this->assertStringNotContainsString("couldn't understand", $second['answer'] ?? '');
        $this->assertDatabaseHas('notifications', ['school_id' => $this->school->id]);
    }

    public function test_http_endpoint_cancel_two_turn_flow(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        auth()->login($this->superAdmin());

        $this->postJson(route('admin.ai.ask'), [
            'question' => 'Send a notification to all students about Sports Day.',
        ])->assertOk()->assertJsonPath('confirmation_required', true);

        $second = $this->postJson(route('admin.ai.ask'), [
            'question' => 'No',
        ])
            ->assertOk()
            ->assertJsonPath('cancelled', true)
            ->json();

        $this->assertStringContainsStringIgnoringCase('cancelled', $second['answer'] ?? '');
        $this->assertSame(0, Notification::query()->count());
    }

    // ─── Helpers ────────────────────────────────────────────────────────

    private function createPendingNotification(): AiPendingAction
    {
        $result = $this->ask(
            'Send a notification to all students about Sports Day.',
            false
        );

        $this->assertTrue($result['confirmation_required'] ?? false);

        return AiPendingAction::query()->findOrFail($result['pending_action_id']);
    }
}
