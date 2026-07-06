<?php

return [

    'students' => [
        'description' => 'Student admissions, records, sessions, documents',
        'intents' => [
            'student.total' => [
                'description' => 'Get total number of active students.',
                'action' => 'query',
            ],
            'student.admitted_this_month' => [
                'description' => 'Get count of students admitted this month.',
                'action' => 'query',
            ],
            'student.by_class' => [
                'description' => 'Get student count grouped by class.',
                'action' => 'query',
                'param_fields' => ['group_by'],
            ],
        ],
        'clarification' => [
            'prompt' => 'Which student report would you like?',
            'options' => [
                'Total Students' => 'student.total',
                'Admitted This Month' => 'student.admitted_this_month',
                'Class-wise Count' => 'student.by_class',
            ],
        ],
    ],

    'attendance' => [
        'description' => 'Student and teacher daily tracking, attendance reports',
        'intents' => [
            'attendance.absent_today' => [
                'description' => 'Get count of students absent today.',
                'action' => 'query',
            ],
            'attendance.monthly_percentage' => [
                'description' => 'Get overall attendance percentage for current month.',
                'action' => 'query',
            ],
            'attendance.below_75' => [
                'description' => 'Get students with attendance below 75%.',
                'action' => 'query',
            ],
            'attendance.notify' => [
                'description' => 'Send absence notifications to parents.',
                'action' => 'action',
                'param_fields' => ['date'],
                'destructive' => true,
            ],
        ],
        'clarification' => [
            'prompt' => 'Which attendance report would you like?',
            'options' => [
                "Today's Absences" => 'attendance.absent_today',
                'Monthly Percentage' => 'attendance.monthly_percentage',
                'Below 75%' => 'attendance.below_75',
                'Send Notifications' => 'attendance.notify',
            ],
        ],
    ],

    'fees' => [
        'description' => 'Fee categories, structures, payments, receipts, collection reports, defaulters',
        'intents' => [
            'fee.outstanding' => [
                'description' => 'Get total outstanding fee amount.',
                'action' => 'query',
            ],
            'fee.pending_above' => [
                'description' => 'Get students with pending fees above a threshold.',
                'action' => 'query',
                'param_fields' => ['amount'],
            ],
            'fee.today_collection' => [
                'description' => "Get today's fee collection total.",
                'action' => 'query',
            ],
            'fee.top_defaulters' => [
                'description' => 'Get top fee defaulters list.',
                'action' => 'query',
            ],
            'fee.send_reminders' => [
                'description' => 'Send fee reminders to defaulters.',
                'action' => 'action',
                'param_fields' => ['days'],
                'destructive' => true,
            ],
        ],
        'clarification' => [
            'prompt' => 'Which fee report would you like?',
            'options' => [
                'Outstanding Fees' => 'fee.outstanding',
                'Today\'s Collection' => 'fee.today_collection',
                'Top Defaulters' => 'fee.top_defaulters',
                'Send Reminders' => 'fee.send_reminders',
            ],
        ],
    ],

    'transport' => [
        'description' => 'Vehicles, drivers, routes, stops, transport assignments',
        'intents' => [
            'transport.route_occupancy' => [
                'description' => 'Get route occupancy stats.',
                'action' => 'query',
            ],
            'transport.students_on_route' => [
                'description' => 'Get students per route.',
                'action' => 'query',
            ],
            'transport.vehicle_assignments' => [
                'description' => 'Get vehicle assignment details.',
                'action' => 'query',
            ],
            'transport.assign' => [
                'description' => 'Assign transport to students.',
                'action' => 'action',
                'param_fields' => ['route_id', 'student_ids'],
            ],
        ],
        'clarification' => [
            'prompt' => 'Which transport information would you like?',
            'options' => [
                'Route Occupancy' => 'transport.route_occupancy',
                'Students by Route' => 'transport.students_on_route',
                'Vehicle Assignments' => 'transport.vehicle_assignments',
            ],
        ],
    ],

    'library' => [
        'description' => 'Books, authors, publishers, book issues, fines',
        'intents' => [
            'library.books_issued' => [
                'description' => 'Get currently issued books count.',
                'action' => 'query',
            ],
            'library.overdue_books' => [
                'description' => 'Get overdue books count.',
                'action' => 'query',
            ],
            'library.fine_collection' => [
                'description' => 'Get total fine collected.',
                'action' => 'query',
            ],
        ],
        'clarification' => [
            'prompt' => 'Which library report would you like?',
            'options' => [
                'Books Issued' => 'library.books_issued',
                'Overdue Books' => 'library.overdue_books',
                'Fine Collection' => 'library.fine_collection',
            ],
        ],
    ],

    'payroll' => [
        'description' => 'Departments, designations, salary components, pay grades, payroll runs, payslips',
        'intents' => [
            'payroll.latest_run' => [
                'description' => 'Get latest payroll run details.',
                'action' => 'query',
            ],
            'payroll.locked_runs' => [
                'description' => 'Get count of locked payroll runs.',
                'action' => 'query',
            ],
            'payroll.highest_salary' => [
                'description' => 'Get highest salary employees.',
                'action' => 'query',
                'param_fields' => ['limit'],
            ],
            'payroll.generated_this_month' => [
                'description' => 'Get payroll runs generated this month.',
                'action' => 'query',
            ],
            'payroll.generate' => [
                'description' => 'Generate payroll for a specific month/year.',
                'action' => 'action',
                'param_fields' => ['month', 'year'],
                'destructive' => true,
            ],
        ],
        'clarification' => [
            'prompt' => 'Which payroll information would you like?',
            'options' => [
                'Latest Run' => 'payroll.latest_run',
                'Locked Runs' => 'payroll.locked_runs',
                'Highest Salaries' => 'payroll.highest_salary',
                'This Month\'s Payroll' => 'payroll.generated_this_month',
                'Generate Payroll' => 'payroll.generate',
            ],
        ],
    ],

    'exams' => [
        'description' => 'Exam creation, results, grading, publish/unpublish',
        'intents' => [
            'exam.publish' => [
                'description' => 'Publish exam results.',
                'action' => 'action',
                'param_fields' => ['exam_id'],
                'destructive' => true,
            ],
        ],
        'clarification' => [
            'prompt' => 'Which exam action would you like?',
            'options' => [
                'Publish Results' => 'exam.publish',
            ],
        ],
    ],

    'homework' => [
        'description' => 'Homework assignments, attachments, due dates',
        'intents' => [
            'homework.create' => [
                'description' => 'Create a new homework assignment.',
                'action' => 'action',
                'param_fields' => ['class_section_id', 'subject_id', 'title', 'due_date'],
            ],
        ],
        'clarification' => [
            'prompt' => 'What would you like to do with homework?',
            'options' => [
                'Create Assignment' => 'homework.create',
            ],
        ],
    ],

    'notifications' => [
        'description' => 'In-app notifications, push notifications, announcements',
        'intents' => [
            'notification.send' => [
                'description' => 'Send a notification to users.',
                'action' => 'action',
                'param_fields' => ['title', 'message', 'target_type'],
                'destructive' => true,
            ],
        ],
        'clarification' => [
            'prompt' => 'What would you like to do with notifications?',
            'options' => [
                'Send Notification' => 'notification.send',
            ],
        ],
    ],

    'reports' => [
        'description' => 'Attendance, exam, fee, parent, student, teacher reports, school summary',
        'intents' => [
            'school.summary' => [
                'description' => 'Get executive school summary combining attendance, fees, transport, homework, exams, leave, and notifications.',
                'action' => 'query',
            ],
        ],
        'clarification' => [
            'prompt' => 'Which report would you like?',
            'options' => [
                'School Summary' => 'school.summary',
            ],
        ],
    ],

    'parents' => [
        'description' => 'Parent portal, guardian notifications',
        'intents' => [],
        'clarification' => [
            'prompt' => 'What would you like to know about parents?',
            'options' => [],
        ],
    ],

    'teachers' => [
        'description' => 'Teacher records, attendance, timetable, subjects',
        'intents' => [],
        'clarification' => [
            'prompt' => 'What would you like to know about teachers?',
            'options' => [],
        ],
    ],

    'leave' => [
        'description' => 'Leave types, leave requests',
        'intents' => [],
        'clarification' => [
            'prompt' => 'What would you like to do with leave?',
            'options' => [],
        ],
    ],

    'dashboard' => [
        'description' => 'School dashboard overview',
        'intents' => [],
        'clarification' => [
            'prompt' => 'What would you like to see on the dashboard?',
            'options' => [],
        ],
    ],

    'settings' => [
        'description' => 'System settings and configuration',
        'intents' => [],
        'clarification' => [
            'prompt' => 'What setting would you like to change?',
            'options' => [],
        ],
    ],

    'general' => [
        'description' => 'General queries, help, about the system',
        'intents' => [],
        'clarification' => [
            'prompt' => 'How can I help you?',
            'options' => [],
        ],
    ],

];
