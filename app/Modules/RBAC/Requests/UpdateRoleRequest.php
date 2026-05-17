<?php

namespace App\Modules\RBAC\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.update');
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:125',
                Rule::unique('roles', 'name')
                    ->ignore($role?->id)
                    ->where('guard_name', 'web')
                    ->where('school_id', app(SchoolContext::class)->id()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
