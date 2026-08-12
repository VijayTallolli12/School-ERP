<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ERP Web Application Access
    |--------------------------------------------------------------------------
    | The School ERP web application is an internal staff / administration
    | application. Parents and Students use the separate mobile app / portal
    | experiences and MUST NOT access the administrative ERP web application.
    |
    | `external_roles` are blocked from every /admin/* web route and from the
    | web login flow. Any role NOT listed here is treated as an internal staff
    | role and is allowed to log into the ERP web application.
    */
    'external_roles' => [
        'Parent',
        'Student',
    ],

    /*
    |--------------------------------------------------------------------------
    | Approved Internal Staff Roles
    |--------------------------------------------------------------------------
    | Documented for reference / tooling. These roles may log into the ERP web
    | application; each only sees the modules its Spatie permissions allow.
    */
    'staff_roles' => [
        'Super Admin',
        'School Admin',
        'Principal',
        'Teacher',
        'Accountant',
        'Librarian',
        'Payroll Manager',
        'Receptionist',
        'HR',
        'Staff',
        'Driver',
    ],
];
