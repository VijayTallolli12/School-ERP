<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    | Supported: gemini | openai
    | API credentials live in the .env file (GEMINI_API_KEY / OPENAI_API_KEY).
    */
    'provider' => env('AI_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | AI Endpoint Rate Limit
    |--------------------------------------------------------------------------
    | Max AI questions per minute per authenticated user (falls back to IP).
    */
    'rate_limit_per_minute' => (int) env('AI_RATE_LIMIT_PER_MINUTE', 20),

    /*
    |--------------------------------------------------------------------------
    | Pending Action Confirmation TTL
    |--------------------------------------------------------------------------
    | How many minutes a pending action may wait for the user's confirmation
    | before it expires. Expired actions cannot execute.
    */
    'pending_action_ttl_minutes' => (int) env('AI_PENDING_ACTION_TTL_MINUTES', 10),

    'role_permissions' => [
        'Super Admin' => ['*'],
        'School Admin' => ['*'],
        'Principal' => ['*'],
        'HR' => [
            'teacher.*', 'leave.*', 'attendance.*', 'student.*',
            'school.summary',
        ],
        'Payroll Manager' => [
            'payroll.*', 'teacher.*', 'school.summary',
        ],
        'Accountant' => ['fee.*', 'student.*', 'attendance.*', 'school.*', 'exam.*', 'homework.*', 'teacher.*', 'transport.*'],
        'Teacher' => [
            'attendance.*', 'student.*', 'exam.*', 'homework.*',
            'school.summary',
        ],
        'Parent' => [
            'attendance.*', 'student.*', 'fee.*', 'exam.*', 'homework.*',
            'school.summary',
        ],
        'Student' => [
            'attendance.*', 'exam.*', 'homework.*',
            'school.summary',
        ],
        'Librarian' => ['library.*', 'school.summary'],
        'Staff' => ['attendance.*', 'school.summary'],
        'Receptionist' => ['student.*'],
    ],
    'data_scoping' => [
        'Teacher' => ['class_section_ids', 'teacher_id'],
        'Parent' => ['student_ids'],
        'Student' => ['student_id'],
    ],
];
