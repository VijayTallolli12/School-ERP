<?php

namespace App\Modules\Payroll\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollItem extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'payroll_items';

    protected $fillable = [
        'school_id',
        'payroll_run_id',
        'employee_type',
        'employee_id',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gross_salary' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function employee()
    {
        return $this->morphTo('employee', 'employee_type', 'employee_id');
    }
}
