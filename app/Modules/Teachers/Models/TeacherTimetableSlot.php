<?php

namespace App\Modules\Teachers\Models;

use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherTimetableSlot extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teacher_timetable_slots';

    protected $fillable = [
        'teacher_id',
        'class_section_id',
        'subject_id',
        'day_of_week',
        'period_label',
        'room',
        'status',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
