<?php

namespace App\Modules\Teachers\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherAttendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teacher_attendances';

    protected $fillable = [
        'teacher_id',
        'attendance_date',
        'status',
        'remarks',
        'marked_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public static function statuses(): array
    {
        return ['present', 'absent', 'late', 'half_day', 'excused'];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'half_day' => 'Half Day',
            'excused' => 'Excused',
            default => 'Unknown',
        };
    }
}
