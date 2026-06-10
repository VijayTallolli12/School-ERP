<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar_path',
        'status',
        'current_school_id',
        'last_login_at',
        'last_login_ip',
        'force_password_change',
        'password',
    ];

    protected $guarded = [
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_super_admin' => 'boolean',
            'force_password_change' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->uuid ??= (string) Str::uuid();
        });
    }

    public function currentSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'current_school_id');
    }

    public function getSchoolIdAttribute(): ?int
    {
        return $this->current_school_id;
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class)
            ->withPivot(['designation', 'employee_code', 'joined_at', 'status', 'is_primary'])
            ->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin || $this->hasRole('Super Admin');
    }

    public function activeSchool(): ?School
    {
        return $this->currentSchool ?: $this->schools()->wherePivot('status', 'active')->first();
    }

    public function guardian(): HasOne
    {
        return $this->hasOne(\App\Modules\Parents\Models\Guardian::class);
    }

    /**
     * Custom notification relationship via notification_user pivot.
     * Named differently to avoid conflicting with Notifiable trait's notifications() MorphMany return type.
     */
    public function appNotifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
            ->withPivot(['is_read', 'read_at', 'delivery_status'])
            ->withTimestamps()
            ->latest('notifications.id');
    }
}
