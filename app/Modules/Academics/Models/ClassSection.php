<?php

namespace App\Modules\Academics\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use App\Modules\Students\Models\StudentSession;
use Database\Factories\ClassSectionFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSection extends Model
{
    use BelongsToSchool, HasFactory;

    protected $table = 'class_section';

    protected $fillable = [
        'school_id',
        'class_id',
        'section_id',
        'class_teacher_id',
        'status',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    public function studentSessions(): HasMany
    {
        return $this->hasMany(StudentSession::class);
    }

    protected static function newFactory(): Factory
    {
        return ClassSectionFactory::new();
    }
}
