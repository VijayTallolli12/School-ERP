<?php

namespace App\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Modules\Students\Models\Student;
use App\Modules\Transport\Models\RouteStop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripStudent extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'trip_id',
        'student_id',
        'route_stop_id',
        'pickup_status',
        'drop_status',
        'picked_up_at',
        'dropped_off_at',
        'pickup_latitude',
        'pickup_longitude',
        'drop_latitude',
        'drop_longitude',
    ];

    protected function casts(): array
    {
        return [
            'pickup_status' => 'string',
            'drop_status' => 'string',
            'picked_up_at' => 'datetime',
            'dropped_off_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function stop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'route_stop_id');
    }
}
