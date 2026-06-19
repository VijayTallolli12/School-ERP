<?php

namespace App\Modules\Transport\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Modules\Students\Models\Student;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportAssignment extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'transport_assignments';

    protected $fillable = [
        'school_id',
        'student_id',
        'route_id',
        'route_stop_id',
        'vehicle_id',
        'pickup_point',
        'monthly_fee',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'monthly_fee' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function stop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'route_stop_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
