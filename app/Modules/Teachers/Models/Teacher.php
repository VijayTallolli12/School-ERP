<?php

namespace App\Modules\Teachers\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Teacher extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'school_id',
        'user_id',
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'qualification',
        'experience_years',
        'joining_date',
        'phone',
        'email',
        'address',
        'photo_path',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'experience_years' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Teacher $teacher): void {
            $teacher->uuid ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id')
            ->using(TeacherSubjectPivot::class);
    }

    public function classSections(): BelongsToMany
    {
        return $this->belongsToMany(ClassSection::class, 'teacher_class_section', 'teacher_id', 'class_section_id')
            ->using(TeacherClassSectionPivot::class)
            ->withPivot('is_class_teacher');
    }

    public function classTeacherSections(): BelongsToMany
    {
        return $this->classSections()->wherePivot('is_class_teacher', true);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TeacherDocument::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(TeacherLeave::class);
    }

    public function timetableSlots(): HasMany
    {
        return $this->hasMany(TeacherTimetableSlot::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name])));
    }

    public static function statuses(): array
    {
        return ['active', 'inactive', 'probation', 'retired'];
    }

    public static function defaultStatus(): string
    {
        return 'active';
    }

    protected static function newFactory(): Factory
    {
        return TeacherFactory::new();
    }
}
