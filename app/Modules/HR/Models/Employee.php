<?php

namespace App\Modules\HR\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use App\Modules\Payroll\Models\PayrollDepartment;
use App\Modules\Payroll\Models\PayrollDesignation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'employee_code',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'date_of_birth',
        'gender',
        'marital_status',
        'blood_group',
        'nationality',
        'religion',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'zip_code',
        'country',
        'bank_name',
        'bank_account_no',
        'bank_ifsc_code',
        'pan_number',
        'uan_number',
        'pf_number',
        'esi_number',
        'date_of_joining',
        'date_of_leaving',
        'employment_type',
        'employment_status',
        'department_id',
        'designation_id',
        'reporting_to_id',
        'profile_image',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_of_joining' => 'date',
            'date_of_leaving' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(PayrollDepartment::class, 'department_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(PayrollDesignation::class, 'designation_id');
    }

    public function reportingTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reporting_to_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name])));
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->profile_image
            ? Storage::url($this->profile_image)
            : null;
    }
}
