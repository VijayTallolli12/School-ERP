<?php

namespace App\Modules\Fees\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveFeeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->isMethod('post')
            ? $this->user()->can('fees.create')
            : $this->user()->can('fees.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $category = $this->route('fee_category');

        return [
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('fee_categories', 'code')
                    ->where('school_id', $schoolId)
                    ->ignore($category?->id),
            ],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
