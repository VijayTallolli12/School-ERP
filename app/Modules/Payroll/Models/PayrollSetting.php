<?php

namespace App\Modules\Payroll\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'payroll_currency',
        'salary_day',
        'enable_professional_tax',
        'enable_provident_fund',
        'enable_esi',
        'pf_employee_share',
        'pf_employer_share',
        'esi_employee_share',
        'esi_employer_share',
        'professional_tax_monthly',
        'overtime_rate_multiplier',
        'pay_period',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'salary_day' => 'integer',
            'enable_professional_tax' => 'boolean',
            'enable_provident_fund' => 'boolean',
            'enable_esi' => 'boolean',
            'pf_employee_share' => 'float',
            'pf_employer_share' => 'float',
            'esi_employee_share' => 'float',
            'esi_employer_share' => 'float',
            'professional_tax_monthly' => 'float',
            'overtime_rate_multiplier' => 'float',
        ];
    }
}
