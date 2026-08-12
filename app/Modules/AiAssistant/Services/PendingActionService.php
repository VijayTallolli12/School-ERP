<?php

namespace App\Modules\AiAssistant\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\User;
use App\Modules\AiAssistant\Models\AiPendingAction;
use Illuminate\Support\Facades\DB;

/**
 * Server-side pending-action store for the Ask ERP confirmation workflow.
 *
 * When an action intent is detected, a pending action is persisted here (bound
 * to the authenticated user + school) and a confirmation is requested. A later
 * message is checked against this store FIRST — if the user confirms, the
 * trusted stored parameters are executed; if they cancel, the pending action
 * is discarded. The normal query planner only runs when no pending action
 * requires confirmation.
 */
class PendingActionService
{
    public function __construct(
        private readonly SchoolContext $schoolContext,
    ) {}

    public function ttlMinutes(): int
    {
        return (int) config('ai.pending_action_ttl_minutes', 10);
    }

    /**
     * Create a pending action for the authenticated user + school. Any other
     * pending action for the same user/school is superseded (only one action
     * can await confirmation at a time).
     *
     * @return AiPendingAction
     */
    public function create(string $tool, array $parameters, string $question, ?User $user = null, ?int $schoolId = null): AiPendingAction
    {
        $user ??= auth()->user();
        $schoolId ??= $this->schoolContext->id();

        if (! $user || ! $schoolId) {
            throw new \RuntimeException('Cannot create a pending action without an authenticated user and school.');
        }

        return DB::transaction(function () use ($tool, $parameters, $question, $user, $schoolId): AiPendingAction {
            // Supersede any earlier pending action for this user + school so
            // stale confirmations can never fire on an outdated action.
            AiPendingAction::query()
                ->where('user_id', $user->getKey())
                ->where('school_id', $schoolId)
                ->where('status', 'pending_confirmation')
                ->update(['status' => 'superseded']);

            return AiPendingAction::query()->create([
                'school_id' => $schoolId,
                'user_id' => $user->getKey(),
                'tool' => $tool,
                'parameters' => $parameters,
                'question' => $question,
                'status' => 'pending_confirmation',
                'expires_at' => now()->addMinutes($this->ttlMinutes()),
            ]);
        });
    }

    /**
     * The currently pending action for the given user + school, or null.
     * Expired pending actions are lazily marked expired and ignored.
     */
    public function getPending(?User $user = null, ?int $schoolId = null): ?AiPendingAction
    {
        $user ??= auth()->user();
        $schoolId ??= $this->schoolContext->id();

        if (! $user || ! $schoolId) {
            return null;
        }

        $pending = AiPendingAction::query()
            ->where('user_id', $user->getKey())
            ->where('school_id', $schoolId)
            ->whereIn('status', ['pending_confirmation', 'executing'])
            ->orderByDesc('id')
            ->first();

        if (! $pending) {
            return null;
        }

        if ($pending->isPending() && $pending->isExpired()) {
            $pending->update(['status' => 'expired']);

            return null;
        }

        return $pending;
    }

    /**
     * Atomically claim a pending action for execution. Returns true exactly
     * once; a concurrent or duplicate confirm cannot claim it a second time,
     * which prevents double submission.
     */
    public function claimForExecution(AiPendingAction $pending, ?User $user = null, ?int $schoolId = null): bool
    {
        $user ??= auth()->user();
        $schoolId ??= $this->schoolContext->id();

        $affected = AiPendingAction::query()
            ->where('id', $pending->getKey())
            ->where('user_id', $user->getKey())
            ->where('school_id', $schoolId)
            ->where('status', 'pending_confirmation')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->update(['status' => 'executing']);

        return $affected === 1;
    }

    public function markCompleted(AiPendingAction $pending): void
    {
        $pending->update(['status' => 'completed']);
    }

    public function markCancelled(AiPendingAction $pending): void
    {
        $pending->update(['status' => 'cancelled']);
    }

    public function markExpired(AiPendingAction $pending): void
    {
        $pending->update(['status' => 'expired']);
    }
}
