<?php

namespace App\Modules\Students\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('students.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $student = $this->route('student');

        $rules = [
            'admission_no' => ['required', 'string', 'max:50', Rule::unique('students')->ignore($student?->id)->where('school_id', $schoolId)],
            'admission_date' => ['nullable', 'date'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'religion' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'max:80'],
            'caste' => ['nullable', 'string', 'max:80'],
            'nationality' => ['nullable', 'string', 'max:80'],
            'mother_tongue' => ['nullable', 'string', 'max:80'],
            'aadhar_no' => ['nullable', 'string', 'max:20'],
            'current_address' => ['nullable', 'string', 'max:2000'],
            'permanent_address' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive', 'alumni', 'transferred'])],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'class_section_id' => ['required', Rule::exists('class_section', 'id')->where('school_id', $schoolId)],
            'roll_no' => ['nullable', 'string', 'max:30'],

            // Option A: Link to an existing parent (no guardian data needed)
            'parent_id' => ['nullable', 'integer', Rule::exists('parents', 'id')->where('school_id', $schoolId)],
        ];

        // If no existing parent selected, require guardian information
        if (!$this->filled('parent_id')) {
            $rules = array_merge($rules, [
                'guardians' => ['nullable', 'array', 'min:1'],
                'guardians.*.id' => ['nullable', 'integer', Rule::exists('student_guardians', 'id')->where('student_id', $student?->id)],
                'guardians.*.name' => ['required_with:guardians', 'string', 'max:150'],
                'guardians.*.relation' => ['required_with:guardians', 'string', 'max:50'],
                'guardians.*.phone' => ['required_with:guardians', 'string', 'max:30'],
                'guardians.*.email' => ['nullable', 'email', 'max:255'],
                'guardians.*.occupation' => ['nullable', 'string', 'max:120'],
                'guardians.*.is_primary' => ['nullable', 'boolean'],
                'guardians.*.can_pickup' => ['nullable', 'boolean'],

                'guardian_name' => ['required_without:guardians', 'string', 'max:150'],
                'guardian_relation' => ['required_without:guardians', 'string', 'max:50'],
                'guardian_phone' => ['required_without:guardians', 'string', 'max:30'],
                'guardian_email' => ['nullable', 'email', 'max:255'],
                'guardian_occupation' => ['nullable', 'string', 'max:120'],
            ]);
        }

        return $rules;
    }
}
