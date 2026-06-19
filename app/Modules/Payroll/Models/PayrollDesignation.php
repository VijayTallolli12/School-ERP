<?php

namespace App\Modules\Payroll\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollDesignation extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'payroll_designations';

    protected $fillable = [
        'school_id',
        'department_id',
        'name',
        'description',
        'status',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(PayrollDepartment::class, 'department_id');
    }
}
