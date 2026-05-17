<?php

namespace App\Modules\Academics\Models;

use App\Core\Tenant\BelongsToSchool;
use Database\Factories\SchoolClassFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    /** @use HasFactory<SchoolClassFactory> */
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'sort_order',
        'status',
    ];

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'class_section', 'class_id', 'section_id')
            ->withPivot(['id', 'class_teacher_id', 'status'])
            ->withTimestamps();
    }

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class, 'class_id');
    }

    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'class_id');
    }

    protected static function newFactory(): Factory
    {
        return SchoolClassFactory::new();
    }
}
