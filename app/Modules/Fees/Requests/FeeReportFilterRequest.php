<?php

namespace App\Modules\Fees\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeeReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fees.reports');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'class_section_id' => ['nullable', Rule::exists('class_section', 'id')->where('school_id', $schoolId)],
            'payment_mode' => ['nullable', Rule::in(array_keys(\App\Modules\Fees\Models\FeePayment::paymentModes()))],
            'overdue_only' => ['nullable', 'boolean'],
            'report_date' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterPayload(): array
    {
        return $this->validated();
    }
}
