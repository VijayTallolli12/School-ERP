<?php

namespace App\Modules\HR\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('hr.create');
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],
            'document_type' => ['required', Rule::in(['id_proof', 'qualification', 'appointment_letter', 'experience_certificate', 'other'])],
            'document_name' => ['required', 'string', 'max:200'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:5120'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
