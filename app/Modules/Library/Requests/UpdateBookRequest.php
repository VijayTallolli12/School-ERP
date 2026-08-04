<?php

namespace App\Modules\Library\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('library.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $book = $this->route('book');
        $issuedCount = $book?->issues()->where('status', 'issued')->count() ?? 0;

        return [
            'isbn' => ['nullable', 'string', 'max:40', Rule::unique('library_books')->where('school_id', $schoolId)->ignore($book?->id)],
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::exists('library_categories')->where('school_id', $schoolId)],
            'author_id' => ['nullable', 'integer', Rule::exists('library_authors')->where('school_id', $schoolId)],
            'publisher_id' => ['nullable', 'integer', Rule::exists('library_publishers')->where('school_id', $schoolId)],
            'edition' => ['nullable', 'string', 'max:60'],
            'language' => ['nullable', 'string', 'max:60'],
            'rack_number' => ['nullable', 'string', 'max:60'],
            'quantity' => ['required', 'integer', 'min:'.max(1, $issuedCount), 'max:9999'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
