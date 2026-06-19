<?php

namespace App\Modules\Payroll\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryComponent extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'salary_components';

    protected $fillable = [
        'school_id',
        'name',
        'name_display',
        'component_type',
        'calculation_type',
        'value',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }
}
