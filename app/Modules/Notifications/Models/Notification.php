<?php

namespace App\Modules\Notifications\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'title',
        'message',
        'type',
        'priority',
        'status',
        'target_type',
        'channel',
        'scheduled_at',
        'sent_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_user')
            ->withPivot(['is_read', 'read_at', 'delivery_status'])
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // -- scopes --

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeTarget($query, string $targetType)
    {
        return $query->where('target_type', $targetType);
    }

    // -- static helpers --

    public static function types(): array
    {
        return [
            'attendance_alert' => 'Attendance Alert',
            'fee_reminder' => 'Fee Reminder',
            'exam_result_alert' => 'Exam Result Alert',
            'announcement' => 'Announcement',
            'timetable_update' => 'Timetable Update',
            'calendar_event' => 'Calendar Event',
        ];
    }

    public static function priorities(): array
    {
        return ['low', 'medium', 'high', 'urgent'];
    }

    public static function statuses(): array
    {
        return ['draft', 'sent', 'failed'];
    }

    public static function targetTypes(): array
    {
        return [
            'all' => 'All Users',
            'students' => 'Students',
            'parents' => 'Parents',
            'teachers' => 'Teachers',
            'staff' => 'Staff',
            'admins' => 'Admins',
        ];
    }

    public static function channels(): array
    {
        return [
            'in_app' => 'In-App',
            'email' => 'Email',
            'sms' => 'SMS',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::types()[$this->type] ?? $this->type;
    }

    public function getTargetLabelAttribute(): string
    {
        return self::targetTypes()[$this->target_type] ?? $this->target_type;
    }

    public function getChannelLabelAttribute(): string
    {
        return self::channels()[$this->channel] ?? $this->channel;
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => '<span class="badge bg-danger">Urgent</span>',
            'high' => '<span class="badge bg-warning text-dark">High</span>',
            'low' => '<span class="badge bg-secondary">Low</span>',
            default => '<span class="badge bg-info">Medium</span>',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'sent' => '<span class="badge bg-success">Sent</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
            default => '<span class="badge bg-warning text-dark">Draft</span>',
        };
    }

    public function getUnreadCountAttribute(): int
    {
        return $this->users()->where('notification_user.is_read', false)->count();
    }

    public function getDeliveredCountAttribute(): int
    {
        return $this->users()->where('notification_user.delivery_status', 'delivered')->count();
    }

    public function getFailedCountAttribute(): int
    {
        return $this->users()->where('notification_user.delivery_status', 'failed')->count();
    }
}