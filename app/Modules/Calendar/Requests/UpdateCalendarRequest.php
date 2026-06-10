<?php

namespace App\Modules\Calendar\Requests;

use App\Modules\Calendar\Models\AcademicCalendar;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('academic_calendar.update');
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => 'sometimes|required|exists:academic_years,id',
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'event_type' => ['sometimes', 'required', 'string', 'in:' . implode(',', AcademicCalendar::eventTypes())],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'audience' => ['sometimes', 'required', 'string', 'in:' . implode(',', AcademicCalendar::audiences())],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
            return [
                'academic_year_id' => 'academic year',
                'event_type' => 'event type',
                'start_date' => 'start date',
                'end_date' => 'end date',
                'audience' => 'audience',
                'location' => 'location',
                'description' => 'description',
                'title' => 'title',
            ];
    }
}
