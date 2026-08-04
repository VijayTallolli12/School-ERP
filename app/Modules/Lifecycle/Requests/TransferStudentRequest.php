<?php

namespace App\Modules\Lifecycle\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('student_lifecycle.transfer');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'transferred_on' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'destination_school' => ['nullable', 'string', 'max:255'],
        ];
    }
}
