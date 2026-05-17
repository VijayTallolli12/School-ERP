<?php

namespace App\Modules\Fees\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\AcademicYear;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentFee extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'academic_year_id',
        'fee_structure_id',
        'status',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StudentFeeItem::class);
    }
}
