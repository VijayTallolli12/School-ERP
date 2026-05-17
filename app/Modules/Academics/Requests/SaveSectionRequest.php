<?php

namespace App\Modules\Academics\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('section') ? 'academics.update' : 'academics.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'name' => ['required', 'string', 'max:80'],
            'code' => ['required', 'string', 'max:30', Rule::unique('sections')->ignore($this->route('section')?->id)->where('school_id', $schoolId)],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
