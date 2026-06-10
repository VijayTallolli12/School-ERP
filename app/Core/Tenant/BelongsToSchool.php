<?php

namespace App\Core\Tenant;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::creating(function ($model): void {
            if (! $model->school_id && app(SchoolContext::class)->id()) {
                $model->school_id = app(SchoolContext::class)->id();
            }
        });

        static::addGlobalScope('school', function (Builder $builder): void {
            $schoolId = app(SchoolContext::class)->id();

            if ($schoolId && ! self::isSuperAdminCached()) {
                $builder->where($builder->getModel()->getTable().'.school_id', $schoolId);
            }
        });
    }

    private static function isSuperAdminCached(): bool
    {
        static $superAdmin = null;

        if ($superAdmin === null) {
            $user = Auth::user();
            $superAdmin = $user && $user->isSuperAdmin();
        }

        return $superAdmin;
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
