<?php

namespace App\Modules\Exams\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamMark extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $table = 'exam_marks';

    protected $fillable = [
        'exam_schedule_id',
        'student_id',
        'marks_obtained',
        'grade',
        'grade_point',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'marks_obtained' => 'decimal:2',
            'grade_point' => 'decimal:2',
        ];
    }

    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
