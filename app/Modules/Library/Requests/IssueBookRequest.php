<?php

namespace App\Modules\Library\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('library.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'book_id' => ['required', 'integer', 'exists:library_books,id'],
            'issueable_type' => ['required', 'string', Rule::in(['student', 'teacher'])],
            'issueable_id' => ['required', 'integer'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $type = $this->input('issueable_type');
            $id = $this->input('issueable_id');

            $modelClass = match ($type) {
                'student' => \App\Modules\Students\Models\Student::class,
                'teacher' => \App\Modules\Teachers\Models\Teacher::class,
                default => null,
            };

            if ($modelClass && !$modelClass::query()->where('id', $id)->exists()) {
                $validator->errors()->add('issueable_id', "The selected {$type} does not exist.");
            }
        });
    }
}
