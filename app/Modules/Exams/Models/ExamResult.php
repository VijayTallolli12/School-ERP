<?php

namespace App\Modules\Exams\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamResult extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'exam_id',
        'student_id',
        'marks_obtained',
        'grade',
        'remarks',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'marks_obtained' => 'integer',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status ?? 'pending');
    }
}
