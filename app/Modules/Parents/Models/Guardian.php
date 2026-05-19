<?php

namespace App\Modules\Parents\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use App\Modules\Students\Models\Student;
use Database\Factories\GuardianFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Guardian extends Model
{
    /** @use HasFactory<GuardianFactory> */
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $table = 'parents';

    protected $fillable = [
        'uuid',
        'school_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'occupation',
        'address',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public static function statuses(): array
    {
        return ['active', 'inactive'];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
            ->withPivot('relationship', 'is_primary')
            ->withTimestamps();
    }

    public function primaryStudents(): BelongsToMany
    {
        return $this->students()->wherePivot('is_primary', true);
    }

    /**
     * Get notifications targeting this guardian.
     *
     * Returns notifications where target_parents is null (all guardians)
     * OR where target_parents JSON array contains this guardian's ID.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(ParentNotification::class, 'school_id', 'school_id')
            ->where(function ($query) {
                $query->whereNull('target_parents')
                    ->orWhereJsonContains('target_parents', $this->id);
            });
    }

    protected static function newFactory(): Factory
    {
        return GuardianFactory::make();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Guardian $parent): void {
            $parent->uuid = (string) Str::uuid();
        });
    }
}