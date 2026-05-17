<?php

namespace App\Modules\Teachers\Requests;

use App\Core\Tenant\SchoolContext;
use App\Modules\Teachers\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('teachers.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $teacher = $this->route('teacher');

        return [
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('teachers')->ignore($teacher?->id)->where('school_id', $schoolId)],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'qualification' => ['nullable', 'string', 'max:150'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'joining_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher?->user?->id)],
            'address' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive', 'probation', 'retired'])],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'class_section_ids' => ['nullable', 'array'],
            'class_section_ids.*' => ['integer', Rule::exists('class_section', 'id')->where('school_id', $schoolId)],
            'class_teacher_section_ids' => ['nullable', 'array'],
            'class_teacher_section_ids.*' => ['integer', Rule::exists('class_section', 'id')->where('school_id', $schoolId)],
            'certificates.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
            'id_proofs.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
            'create_user' => ['nullable', 'boolean'],
            'password' => ['nullable', 'required_if:create_user,1', 'confirmed', Password::defaults()],
        ];
    }
}
