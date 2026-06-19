<?php

namespace App\Modules\Transport\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Modules\Transport\Models\Vehicle;
use App\Modules\Transport\Models\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'mobile',
        'license_number',
        'license_expiry_date',
        'address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry_date' => 'date',
        ];
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }
}
