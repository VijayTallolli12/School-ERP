<?php

namespace App\Modules\Library\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('library.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $author = $this->route('author');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('library_authors')->where('school_id', $schoolId)->ignore($author?->id)],
            'biography' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
