<?php

namespace App\Modules\Fees\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fees.collect');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'paid_on' => ['required', 'date'],
            'payment_mode' => ['required', Rule::in(array_keys(\App\Modules\Fees\Models\FeePayment::paymentModes()))],
            'remarks' => ['nullable', 'string', 'max:500'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.student_fee_item_id' => ['required', 'integer'],
            'lines.*.amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
        ];
    }
}
