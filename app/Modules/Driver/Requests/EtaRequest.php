<?php

namespace App\Modules\Driver\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EtaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->hasRole('Driver')
            && $this->user()->can('transport.view');
    }

    public function rules(): array
    {
        return [
            'current_latitude' => ['required', 'numeric', 'between:-90,90'],
            'current_longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
