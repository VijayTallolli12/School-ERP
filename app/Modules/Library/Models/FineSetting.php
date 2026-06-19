<?php

namespace App\Modules\Library\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FineSetting extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'library_fine_settings';

    protected $fillable = [
        'school_id',
        'fine_per_day',
        'max_fine',
        'grace_period_days',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'fine_per_day' => 'decimal:2',
            'max_fine' => 'decimal:2',
            'grace_period_days' => 'integer',
        ];
    }
}
