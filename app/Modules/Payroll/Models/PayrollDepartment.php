<?php

namespace App\Modules\Payroll\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollDepartment extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'payroll_departments';

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function designations(): HasMany
    {
        return $this->hasMany(PayrollDesignation::class, 'department_id');
    }
}
