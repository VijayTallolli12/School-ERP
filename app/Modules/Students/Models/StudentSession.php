<?php

namespace App\Modules\Students\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentSession extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'student_id',
        'class_section_id',
        'roll_no',
        'joined_on',
        'left_on',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'joined_on' => 'date',
            'left_on' => 'date',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }
}
