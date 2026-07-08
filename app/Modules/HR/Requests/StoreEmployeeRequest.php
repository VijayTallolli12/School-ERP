<?php

namespace App\Modules\HR\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('hr.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'employee_code' => ['required', 'string', 'max:50', Rule::unique('employees')->where('school_id', $schoolId)],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'religion' => ['nullable', 'string', 'max:50'],
            'address_line1' => ['nullable', 'string', 'max:500'],
            'address_line2' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'bank_ifsc_code' => ['nullable', 'string', 'max:20'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'uan_number' => ['nullable', 'string', 'max:20'],
            'pf_number' => ['nullable', 'string', 'max:20'],
            'esi_number' => ['nullable', 'string', 'max:20'],
            'date_of_joining' => ['nullable', 'date'],
            'date_of_leaving' => ['nullable', 'date', 'after:date_of_joining'],
            'employment_type' => ['required', Rule::in(['permanent', 'contract', 'probationary', 'temporary'])],
            'employment_status' => ['required', Rule::in(['active', 'inactive', 'terminated', 'resigned', 'retired'])],
            'department_id' => ['nullable', 'integer', Rule::exists('payroll_departments', 'id')],
            'designation_id' => ['nullable', 'integer', Rule::exists('payroll_designations', 'id')],
            'reporting_to_id' => ['nullable', 'integer', Rule::exists('employees', 'id')],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
