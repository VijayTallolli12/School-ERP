<?php

namespace App\Modules\Payroll\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePayslip extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'employee_payslips';

    protected $fillable = [
        'school_id',
        'payroll_run_id',
        'payroll_item_id',
        'payslip_number',
        'employee_type',
        'employee_id',
        'employee_name',
        'department_name',
        'designation_name',
        'earnings_json',
        'deductions_json',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'generated_by',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'earnings_json' => 'array',
            'deductions_json' => 'array',
            'gross_salary' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'generated_at' => 'datetime',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class, 'payroll_item_id');
    }

    public function employee(): MorphTo
    {
        return $this->morphTo('employee', 'employee_type', 'employee_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
