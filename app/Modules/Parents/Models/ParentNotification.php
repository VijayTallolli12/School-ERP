<?php

namespace App\Modules\Parents\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(Parent::class, 'parent_notification_parent')
            ->withTimestamps();
    }

    public function isSent(): bool
    {
        return !is_null($this->sent_at);
    }
}