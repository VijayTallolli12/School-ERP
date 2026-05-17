<?php

namespace App\Modules\Exams\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Exam extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_section_id',
        'subject_id',
        'exam_name',
        'exam_type',
        'exam_date',
        'maximum_marks',
        'pass_marks',
        'status',
        'is_published',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'maximum_marks' => 'integer',
            'pass_marks' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Exam $exam): void {
            $exam->uuid ??= (string) Str::uuid();
        });
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public static function types(): array
    {
        return ['Monthly', 'Quarterly', 'Half Yearly', 'Annual', 'Class Test', 'Practical'];
    }

    public static function statuses(): array
    {
        return ['scheduled', 'completed', 'canceled'];
    }

    protected static function newFactory(): Factory
    {
        return ExamFactory::new();
    }
}
