<?php

namespace App\Modules\Driver\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverReadAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->hasRole('Driver')
            && $this->user()->can('transport.view');
    }

    public function rules(): array
    {
        return [];
    }
}
