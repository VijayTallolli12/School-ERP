<?php

namespace App\Modules\Documents\Requests;

use App\Modules\Students\Models\StudentDocument;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('student_documents.update');
    }

    public function rules(): array
    {
        return [
            'document_type' => ['sometimes', 'required', 'string', 'in:' . implode(',', array_keys(StudentDocument::documentTypes()))],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'max:' . StudentDocument::maxFileSize(), 'mimes:' . StudentDocument::allowedExtensions()],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'The file size must not exceed 10 MB.',
            'file.mimes' => 'Allowed file types: PDF, JPG, PNG, DOC, DOCX.',
        ];
    }

    public function attributes(): array
    {
        return [
            'document_type' => 'document type',
            'title' => 'title',
            'file' => 'file',
            'issue_date' => 'issue date',
            'expiry_date' => 'expiry date',
            'remarks' => 'remarks',
        ];
    }
}
