<?php

namespace App\Modules\Parents\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentNotification extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'title',
        'message',
        'type',
        'target_parents',
        'sent_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_parents' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public static function types(): array
    {
        return ['announcement', 'attendance_alert', 'fee_reminder', 'exam_result', 'general'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the guardians targeted by this notification.
     *
     * If target_parents is null, the notification targets all guardians in the school.
     * Otherwise, returns the specific guardians whose IDs are stored in the JSON column.
     */
    public function parents()
    {
        $query = Guardian::where('school_id', $this->school_id);

        if (empty($this->target_parents)) {
            // Null/empty target_parents means all guardians in the school.
            return $query;
        }

        return $query->whereIn('id', $this->target_parents);
    }

    public function isSent(): bool
    {
        return !is_null($this->sent_at);
    }
}