<?php

namespace App\Modules\Library\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('library.update');
    }

    public function rules(): array
    {
        return [
            'return_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
