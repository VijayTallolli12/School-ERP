<?php

namespace App\Modules\Admissions\Requests;

use App\Modules\Admissions\Models\AdmissionDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdmissionDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('admissions.update');
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', Rule::in(array_keys(AdmissionDocument::documentTypes()))],
            'document_name' => ['nullable', 'string', 'max:150'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }
}
