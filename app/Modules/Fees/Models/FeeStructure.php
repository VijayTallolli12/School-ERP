<?php

namespace App\Modules\Fees\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeStructure extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_section_id',
        'name',
        'status',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeeStructureItem::class)->orderBy('sort_order');
    }

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }
}
