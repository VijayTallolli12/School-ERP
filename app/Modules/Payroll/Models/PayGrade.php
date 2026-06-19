<?php

namespace App\Modules\Payroll\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayGrade extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'pay_grades';

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'min_salary',
        'max_salary',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'min_salary' => 'decimal:2',
            'max_salary' => 'decimal:2',
        ];
    }
}
