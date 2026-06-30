<?php

namespace App\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Modules\Students\Models\Student;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'driver_id',
        'vehicle_id',
        'route_id',
        'type',
        'status',
        'trip_date',
        'started_at',
        'completed_at',
        'total_students',
        'picked_up_count',
        'dropped_off_count',
        'total_distance',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'trip_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_students' => 'integer',
            'picked_up_count' => 'integer',
            'dropped_off_count' => 'integer',
            'total_distance' => 'decimal:2',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function tripStudents(): HasMany
    {
        return $this->hasMany(TripStudent::class);
    }

    public function tripEvents(): HasMany
    {
        return $this->hasMany(TripEvent::class);
    }
}
