<?php

namespace App\Modules\Library\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('library.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'isbn' => ['nullable', 'string', 'max:40', Rule::unique('library_books')->where('school_id', $schoolId)],
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:library_categories,id'],
            'author_id' => ['nullable', 'integer', 'exists:library_authors,id'],
            'publisher_id' => ['nullable', 'integer', 'exists:library_publishers,id'],
            'edition' => ['nullable', 'string', 'max:60'],
            'language' => ['nullable', 'string', 'max:60'],
            'rack_number' => ['nullable', 'string', 'max:60'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
