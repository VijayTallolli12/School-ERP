<?php

namespace App\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripEvent extends Model
{
    public const UPDATED_AT = null;

    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'trip_id',
        'trip_student_id',
        'event_type',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function tripStudent(): BelongsTo
    {
        return $this->belongsTo(TripStudent::class);
    }
}
