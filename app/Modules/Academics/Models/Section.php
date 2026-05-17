<?php

namespace App\Modules\Academics\Models;

use App\Core\Tenant\BelongsToSchool;
use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'capacity',
        'status',
    ];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_section', 'section_id', 'class_id')
            ->withPivot(['id', 'class_teacher_id', 'status'])
            ->withTimestamps();
    }

    protected static function newFactory(): Factory
    {
        return SectionFactory::new();
    }
}
