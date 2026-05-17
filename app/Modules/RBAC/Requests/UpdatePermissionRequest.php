<?php

namespace App\Modules\RBAC\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('permissions.update');
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:125',
                Rule::unique('permissions', 'name')->ignore($this->route('permission')?->id)->where('guard_name', 'web'),
            ],
        ];
    }
}
