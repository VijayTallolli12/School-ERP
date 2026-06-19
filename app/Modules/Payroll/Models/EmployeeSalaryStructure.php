<?php

namespace App\Modules\Payroll\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSalaryStructure extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'employee_salary_structures';

    protected $fillable = [
        'school_id',
        'employee_id',
        'employee_type',
        'pay_grade_id',
        'effective_from',
        'effective_to',
        'total_ctc',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_ctc' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function payGrade(): BelongsTo
    {
        return $this->belongsTo(PayGrade::class, 'pay_grade_id');
    }

    public function employee(): MorphTo
    {
        return $this->morphTo('employee', 'employee_type', 'employee_id');
    }
}
