<?php

namespace App\Modules\Library\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublisherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('library.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('library_publishers')->where('school_id', $schoolId)],
            'address' => ['nullable', 'string', 'max:2000'],
            'contact' => ['nullable', 'string', 'max:60'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
