<?php

use App\Core\Tenant\SchoolContext;
use App\Models\School;

if (! function_exists('setting')) {
    /**
     * Retrieve a school-scoped setting value with fallback.
     *
     * Supported built-in keys:
     *   - school_name    : School::$name → config('app.name', 'School ERP')
     *   - school_logo    : School::$logo_path → storage URL
     *   - favicon        : settings.school.favicon_path → storage URL
     *   - footer_text    : School::$name (alias for school_name)
     *   - principal_name : settings.school.principal_name
     *   - website        : settings.school.website
     *   - address        : School::$address
     *   - phone          : School::$phone
     *   - email          : School::$email
     *   - any other key  : looked up in School::$settings JSON via data_get()
     *
     * @param  string  $key      Dot-notation key (e.g. 'school_name', 'academic.grading_system')
     * @param  mixed   $default  Fallback value when the school or key is unavailable
     * @return mixed
     */
    function setting(string $key, mixed $default = null): mixed
    {
        static $resolved = false;
        static $school = null;

        if (! $resolved) {
            try {
                if (app()->bound(SchoolContext::class)) {
                    $context = app(SchoolContext::class);
                    $school = $context->school();
                }

                if (! $school && auth()->check()) {
                    $school = auth()->user()->currentSchool;
                }
            } catch (Throwable) {
                // Application not fully booted (CLI, queue, early bootstrap, etc.)
            }
            $resolved = true;
        }

        $value = null;

        if ($school instanceof School) {
            $value = match ($key) {
                'school_name'  => $school->name,
                'footer_text'  => $school->name,
                'school_logo'  => $school->logo_path ? asset('storage/' . $school->logo_path) : null,
                'favicon'      => ($fav = data_get($school->settings, 'school.favicon_path'))
                                    ? asset('storage/' . $fav)
                                    : null,
                'principal_name' => data_get($school->settings, 'school.principal_name'),
                'website'      => data_get($school->settings, 'school.website'),
                'address'      => $school->address,
                'phone'        => $school->phone,
                'email'        => $school->email,
                default        => data_get($school->settings, $key),
            };
        }

        if ($value !== null && $value !== '') {
            return $value;
        }

        if ($default !== null) {
            return $default;
        }

        // Safe default for the most common branding key
        if ($key === 'school_name' || $key === 'footer_text') {
            return config('app.name', 'School ERP');
        }

        return $default;
    }
}