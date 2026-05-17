<?php

namespace App\Modules\Students\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use App\Modules\Fees\Models\StudentFee;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'school_id',
        'user_id',
        'admission_no',
        'admission_date',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'blood_group',
        'religion',
        'category',
        'caste',
        'nationality',
        'mother_tongue',
        'aadhar_no',
        'photo_path',
        'current_address',
        'permanent_address',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'admission_date' => 'date',
            'date_of_birth' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Student $student): void {
            $student->uuid ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(StudentGuardian::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(StudentSession::class);
    }

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    public function currentSession(): HasMany
    {
        return $this->sessions()->where('status', 'active')->latest();
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->implode(' '));
    }

    protected static function newFactory(): Factory
    {
        return StudentFactory::new();
    }
}
