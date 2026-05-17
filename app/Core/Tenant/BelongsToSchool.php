<?php

namespace App\Core\Tenant;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            $user = auth()->user();
            $schoolId = app(SchoolContext::class)->id();

            if ($schoolId && (! $user || ! $user->isSuperAdmin())) {
                $builder->where($builder->getModel()->getTable().'.school_id', $schoolId);
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
