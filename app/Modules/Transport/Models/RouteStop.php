<?php

namespace App\Modules\Transport\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\TransportAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteStop extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'route_stops';

    protected $fillable = [
        'school_id',
        'route_id',
        'stop_name',
        'pickup_time',
        'drop_time',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'pickup_time' => 'datetime:H:i',
            'drop_time' => 'datetime:H:i',
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TransportAssignment::class, 'route_stop_id');
    }
}
