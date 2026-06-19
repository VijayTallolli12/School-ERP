<?php

namespace App\Modules\Transport\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transport.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'max:20'],
            'license_number' => ['required', 'string', 'max:60', Rule::unique('drivers')->where('school_id', $schoolId)],
            'license_expiry_date' => ['required', 'date'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
