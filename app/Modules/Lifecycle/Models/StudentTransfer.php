<?php

namespace App\Modules\Lifecycle\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentTransfer extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'transfer_type',
        'from_class_section_id',
        'to_class_section_id',
        'from_academic_year_id',
        'to_academic_year_id',
        'transferred_on',
        'reason',
        'remarks',
        'tc_no',
        'tc_issued_on',
        'conduct',
        'destination_school',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'transferred_on' => 'date',
            'tc_issued_on' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClassSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'from_class_section_id');
    }

    public function toClassSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'to_class_section_id');
    }

    public function fromAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'from_academic_year_id');
    }

    public function toAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'to_academic_year_id');
    }

    public static function types(): array
    {
        return [
            'promotion' => 'Promotion',
            'transfer' => 'Transfer',
            'tc' => 'Transfer Certificate',
            'alumni' => 'Alumni',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::types()[$this->transfer_type] ?? $this->transfer_type;
    }
}
