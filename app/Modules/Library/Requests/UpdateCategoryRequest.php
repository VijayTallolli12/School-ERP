<?php

namespace App\Modules\Library\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('library.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('library_categories')->where('school_id', $schoolId)->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
