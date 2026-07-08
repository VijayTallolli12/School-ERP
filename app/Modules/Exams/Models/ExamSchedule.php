<?php

namespace App\Modules\Exams\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Modules\Academics\Models\Subject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamSchedule extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $table = 'exam_schedules';

    protected $fillable = [
        'exam_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'room',
        'maximum_marks',
        'pass_marks',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'maximum_marks' => 'integer',
            'pass_marks' => 'integer',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(ExamMark::class, 'exam_schedule_id');
    }
}
