<?php

namespace App\Modules\RBAC\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('permissions.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:125', Rule::unique('permissions', 'name')->where('guard_name', 'web')],
        ];
    }
}
