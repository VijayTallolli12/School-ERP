<?php

namespace App\Modules\Fees\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveFeeStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->isMethod('post')
            ? $this->user()->can('fees.create')
            : $this->user()->can('fees.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'class_section_id' => ['required', Rule::exists('class_section', 'id')->where('school_id', $schoolId)],
            'name' => ['nullable', 'string', 'max:150'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.fee_category_id' => ['required', Rule::exists('fee_categories', 'id')->where('school_id', $schoolId)],
            'items.*.amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $ids = collect($this->input('items', []))->pluck('fee_category_id')->filter();
            if ($ids->count() !== $ids->unique()->count()) {
                $validator->errors()->add('items', 'Duplicate fee categories are not allowed in one structure.');
            }

            $feeStructure = $this->route('fee_structure');
            $q = \App\Modules\Fees\Models\FeeStructure::query()
                ->where('academic_year_id', $this->input('academic_year_id'))
                ->where('class_section_id', $this->input('class_section_id'));

            if ($feeStructure) {
                $q->whereKeyNot($feeStructure->id);
            }

            if ($q->exists()) {
                $validator->errors()->add('class_section_id', 'A fee structure already exists for this class and academic year.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function structurePayload(): array
    {
        $data = $this->validated();
        $items = [];
        foreach ($data['items'] as $row) {
            $items[] = [
                'fee_category_id' => (int) $row['fee_category_id'],
                'amount' => $row['amount'],
            ];
        }
        unset($data['items']);
        $data['items'] = $items;

        return $data;
    }
}
