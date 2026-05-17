<?php

namespace App\Modules\Academics\Models;

use App\Core\Tenant\BelongsToSchool;
use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'type',
        'credit_hours',
        'description',
        'status',
    ];

    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }

    protected static function newFactory(): Factory
    {
        return SubjectFactory::new();
    }
}
