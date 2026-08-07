<?php

namespace App\Modules\Driver\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverTripActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->hasRole('Driver')
            && $this->user()->can('transport.update');
    }

    public function rules(): array
    {
        return [];
    }
}
