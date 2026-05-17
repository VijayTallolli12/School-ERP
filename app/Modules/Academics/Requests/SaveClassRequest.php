<?php

namespace App\Modules\Academics\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('class') ? 'academics.update' : 'academics.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'name' => ['required', 'string', 'max:80'],
            'code' => ['required', 'string', 'max:30', Rule::unique('classes')->ignore($this->route('class')?->id)->where('school_id', $schoolId)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
