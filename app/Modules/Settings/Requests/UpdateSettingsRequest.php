<?php

namespace App\Modules\Settings\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'school.name' => ['required', 'string', 'max:255'],
            'school.logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'school.favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp', 'max:1024'],
            'school.address' => ['nullable', 'string', 'max:1000'],
            'school.phone' => ['nullable', 'string', 'max:30'],
            'school.email' => ['nullable', 'email', 'max:255'],
            'school.website' => ['nullable', 'url', 'max:255'],
            'school.principal_name' => ['nullable', 'string', 'max:150'],

            'academic.current_academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'academic.grading_system' => ['required', 'string', Rule::in(['percentage', 'grade_points', 'letter_grades'])],
            'academic.attendance.default_status' => ['required', 'string', Rule::in(['present', 'absent'])],
            'academic.attendance.minimum_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'academic.attendance.allow_late_marking' => ['nullable', 'boolean'],

            'system.timezone' => ['required', 'timezone'],
            'system.currency' => ['required', 'string', 'max:10'],
            'system.date_format' => ['required', 'string', Rule::in(['d-m-Y', 'd/m/Y', 'm/d/Y', 'Y-m-d', 'd M Y'])],

            'email.smtp_host' => ['nullable', 'string', 'max:255'],
            'email.smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'email.smtp_username' => ['nullable', 'string', 'max:255'],
            'email.smtp_password' => ['nullable', 'string', 'max:255'],
            'email.smtp_encryption' => ['nullable', Rule::in(['tls', 'ssl'])],

            'payment.razorpay.enabled' => ['nullable', 'boolean'],
            'payment.razorpay.key' => ['nullable', 'string', 'max:255'],
            'payment.razorpay.secret' => ['nullable', 'string', 'max:255'],
            'payment.stripe.enabled' => ['nullable', 'boolean'],
            'payment.stripe.key' => ['nullable', 'string', 'max:255'],
            'payment.stripe.secret' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function validatedPayload(): array
    {
        $validated = array_replace_recursive([
            'school' => [],
            'academic' => ['attendance' => []],
            'system' => [],
            'email' => [],
            'payment' => ['razorpay' => [], 'stripe' => []],
        ], $this->validated());

        $validated['academic']['attendance']['allow_late_marking'] = $this->boolean('academic.attendance.allow_late_marking');
        $validated['payment']['razorpay']['enabled'] = $this->boolean('payment.razorpay.enabled');
        $validated['payment']['stripe']['enabled'] = $this->boolean('payment.stripe.enabled');

        foreach ([
            'academic.current_academic_year_id',
            'school.address',
            'school.phone',
            'school.email',
            'school.website',
            'school.principal_name',
            'email.smtp_host',
            'email.smtp_port',
            'email.smtp_username',
            'email.smtp_password',
            'email.smtp_encryption',
            'payment.razorpay.key',
            'payment.razorpay.secret',
            'payment.stripe.key',
            'payment.stripe.secret',
        ] as $key) {
            if (data_get($validated, $key) === '') {
                data_set($validated, $key, null);
            }
        }

        return $validated;
    }
}
