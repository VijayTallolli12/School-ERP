<?php

return [
    'modules' => [
        'attendance', 'student', 'fee', 'exam', 'homework', 'transport', 'library', 'payroll',
    ],
    'role_permissions' => [
        'Super Admin' => ['*'],
        'School Admin' => ['*'],
        'Principal' => ['*'],
        'Accountant' => ['fee.*', 'student.*', 'attendance.*', 'school.*'],
        'Teacher' => [
            'attendance.absent_today', 'attendance.monthly_percentage', 'attendance.below_75',
            'student.total', 'student.by_class',
            'homework.create',
            'exam.publish',
            'notification.send',
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
