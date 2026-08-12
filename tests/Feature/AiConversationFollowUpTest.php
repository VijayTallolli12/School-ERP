<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Modules\AiAssistant\Erp\QueryPlanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Documents conversational follow-up behavior.
 *
 * The current Ask ERP / Executive Gemini pipeline is STATELESS: each
 * /admin/ai/ask request carries only the question (the frontend keeps a
 * display-only history). There is no server-side conversation memory, so a
 * bare follow-up like "What about February?" cannot inherit context from the
 * previous question. These tests pin that documented limitation — they do not
 * pretend follow-ups work.
 */
class AiConversationFollowUpTest extends TestCase
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

    public function test_standalone_question_is_fully_resolvable(): void
    {
        // A complete question carries its own context and resolves normally.
        $plan = app(QueryPlanner::class)->plan('Show Half Yearly exams in January 2026.');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);
    }

    public function test_bare_follow_up_without_context_is_ambiguous(): void
    {
        // Without server-side conversation memory, a bare follow-up has no
        // inherited context. The planner must not hallucinate an intent.
        $plan = app(QueryPlanner::class)->plan('What about February?');

        // It resolves to something (or unknown) but must NOT claim to be an
        // exam query for a guessed context it was never given.
        $this->assertArrayHasKey('intent', $plan);
        $this->assertNotContains($plan['intent'], ['exam.search', 'exam.completed', 'exam.upcoming']);
    }

    public function test_follow_up_with_full_self_contained_context_works(): void
    {
        // A follow-up that repeats enough context behaves like a fresh query.
        $plan = app(QueryPlanner::class)->plan('Show Half Yearly exams in February 2026.');
        $this->assertSame('exam.search', $plan['intent']);
        $this->assertContains('half_yearly', $plan['parameters']['exam_type'] ?? []);
        $this->assertSame('2026-02-01', $plan['parameters']['date_from'] ?? null);
        $this->assertSame('2026-02-28', $plan['parameters']['date_to'] ?? null);
    }
}
