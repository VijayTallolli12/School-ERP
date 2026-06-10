<?php

namespace App\Modules\Leave\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use App\Modules\Leave\Models\LeaveType;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class LeaveRequest extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $table = 'leave_requests';

    protected $fillable = [
        'school_id',
        'user_id',
        'student_id',
        'leave_type_id',
        'from_date',
        'to_date',
        'days',
        'reason',
        'attachment',
        'status',
        'approved_by',
        'approved_at',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'approved_at' => 'datetime',
            'days' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment);
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'cancelled' => 'bg-secondary',
            default => 'bg-warning',
        };
    }

    public static function statuses(): array
    {
        return ['pending', 'approved', 'rejected', 'cancelled'];
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
