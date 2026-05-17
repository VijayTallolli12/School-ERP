<?php

namespace App\Modules\Timetable\Models;

use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Teachers\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimetableSlot extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teacher_timetable_slots';

    protected $fillable = [
        'teacher_id',
        'class_section_id',
        'subject_id',
        'academic_year_id',
        'day_of_week',
        'period_number',
        'period_label',
        'start_time',
        'end_time',
        'room',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'period_number' => 'integer',
    ];

    public static function days(): array
    {
        return [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }

    public static function statuses(): array
    {
        return ['active', 'inactive'];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getDayNameAttribute(): string
    {
        return self::days()[$this->day_of_week] ?? '-';
    }

    public function getTimeRangeAttribute(): string
    {
        if (! $this->start_time || ! $this->end_time) {
            return '-';
        }

        return sprintf('%s - %s', date('H:i', strtotime($this->start_time)), date('H:i', strtotime($this->end_time)));
    }
}
