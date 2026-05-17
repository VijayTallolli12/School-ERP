<?php

namespace App\Modules\Notifications\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', Rule::in(array_keys(\App\Modules\Notifications\Models\Notification::types()))],
            'priority' => ['required', 'string', Rule::in(\App\Modules\Notifications\Models\Notification::priorities())],
            'target_type' => ['required', 'string', Rule::in(array_keys(\App\Modules\Notifications\Models\Notification::targetTypes()))],
            'channel' => ['required', 'string', Rule::in(array_keys(\App\Modules\Notifications\Models\Notification::channels()))],
            'status' => ['required', 'string', Rule::in(\App\Modules\Notifications\Models\Notification::statuses())],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }
}