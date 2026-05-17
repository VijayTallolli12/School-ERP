<?php

namespace App\Models;

use App\Core\Tenant\BelongsToSchool;
use Database\Factories\AcademicYearFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    /** @use HasFactory<AcademicYearFactory> */
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'starts_on',
        'ends_on',
        'is_active',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function terms(): HasMany
    {
        return $this->hasMany(\App\Modules\Academics\Models\AcademicTerm::class);
    }
}
