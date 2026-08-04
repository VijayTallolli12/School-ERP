<?php

namespace App\Modules\Fees\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidFeePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fees.update');
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }
}
