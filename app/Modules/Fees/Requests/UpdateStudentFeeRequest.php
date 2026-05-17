<?php

namespace App\Modules\Fees\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fees.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $studentFee = $this->route('student_fee');

        return [
            'status' => ['nullable', Rule::in(['active', 'waived', 'cancelled'])],
            'items' => ['nullable', 'array'],
            'items.*.id' => [
                'required_with:items',
                Rule::exists('student_fee_items', 'id')->where('student_fee_id', $studentFee->id),
            ],
            'items.*.amount' => ['required_with:items', 'numeric', 'min:0', 'max:99999999.99'],
            'items.*.due_date' => ['nullable', 'date'],
        ];
    }
}
