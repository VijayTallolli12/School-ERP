<?php

namespace App\Models;

use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'logo_path',
        'timezone',
        'currency',
        'date_format',
        'status',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (School $school): void {
            $school->uuid ??= (string) Str::uuid();
            $school->slug ??= Str::slug($school->name);
        });
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['designation', 'employee_code', 'joined_at', 'status', 'is_primary'])
            ->withTimestamps();
    }
}
