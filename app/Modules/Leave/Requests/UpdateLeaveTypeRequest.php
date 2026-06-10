<?php

namespace App\Modules\Leave\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('leave_management.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $leaveTypeId = $this->route('leave_type')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('leave_types')->where('school_id', $schoolId)->ignore($leaveTypeId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
