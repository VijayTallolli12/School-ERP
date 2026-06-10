<?php

namespace App\Modules\Documents\Requests;

use App\Modules\Students\Models\StudentDocument;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('student_documents.create');
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'document_type' => ['required', 'string', 'in:' . implode(',', array_keys(StudentDocument::documentTypes()))],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:' . StudentDocument::maxFileSize(), 'mimes:' . StudentDocument::allowedExtensions()],
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
            'student_id' => 'student',
            'document_type' => 'document type',
            'title' => 'title',
            'file' => 'file',
            'issue_date' => 'issue date',
            'expiry_date' => 'expiry date',
            'remarks' => 'remarks',
        ];
    }
}
