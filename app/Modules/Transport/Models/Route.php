<?php

namespace App\Modules\Transport\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Vehicle;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\TransportAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'routes';

    protected $fillable = [
        'school_id',
        'route_name',
        'start_point',
        'end_point',
        'distance',
        'vehicle_id',
        'driver_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'distance' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(RouteStop::class, 'route_id')->orderBy('sequence');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TransportAssignment::class);
    }
}
