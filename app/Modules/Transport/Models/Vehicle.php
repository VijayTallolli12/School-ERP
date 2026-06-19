<?php

namespace App\Modules\Transport\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\TransportAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'vehicle_number',
        'vehicle_name',
        'vehicle_type',
        'capacity',
        'driver_id',
        'attendant',
        'status',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TransportAssignment::class);
    }
}
