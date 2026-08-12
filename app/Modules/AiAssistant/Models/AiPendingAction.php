<?php

namespace App\Modules\AiAssistant\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A server-side, tenant-scoped record of an AI action that is waiting for the
 * user's explicit confirmation.
 *
 * The pending action is created server-side when an action intent is detected
 * and confirmation is requested. It is bound to the authenticated user AND
 * school, carries the exact approved parameters, and expires after a short TTL.
 * The client only ever communicates the user's confirmation/cancellation; the
 * backend resolves the pending action from this trusted state.
 */
class AiPendingAction extends Model
{
    use BelongsToSchool, HasFactory;

    protected $table = 'ai_pending_actions';

    protected $fillable = [
        'school_id',
        'user_id',
        'tool',
        'parameters',
        'question',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_confirmation';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
