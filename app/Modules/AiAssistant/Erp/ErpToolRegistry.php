<?php

namespace App\Modules\AiAssistant\Erp;

use App\Modules\AiAssistant\Erp\NaturalLanguageDateParser;

/**
 * Single source of truth for the ERP AI tool catalog.
 *
 * Each tool maps a controlled operation (module.operation) to a handler
 * method plus a strict parameter schema. The AI never calls raw endpoints —
 * it selects an allowed tool and the backend validates + executes it.
 */
class ErpToolRegistry
{
    /**
     * Synonym normalization for ERP domain concepts.
     * Key: canonical value => list of natural-language aliases.
     */
    public const EXAM_TYPE_ALIASES = [
        'mid_term' => ['mid term', 'mid-term', 'midterm', 'mid term exam', 'midterm exam'],
        'half_yearly' => ['half yearly', 'half-yearly', 'half yearly exam', 'half-yearly exam', 'half yearly test'],
        'quarterly' => ['quarterly', 'quarterly exam', 'quarterly test'],
        'annual' => ['annual', 'annual exam', 'final exam', 'final', 'yearly', 'yearly exam', 'term end', 'term-end'],
        'monthly' => ['monthly', 'monthly exam', 'monthly test'],
        'class_test' => ['class test', 'class-test', 'class test exam', 'unit test', 'unit test exam', 'test'],
        'practical' => ['practical', 'practical exam', 'viva', 'lab'],
    ];

    public const STATUS_ALIASES = [
        'scheduled' => ['scheduled', 'upcoming', 'scheduled for', 'to be held', 'planned', 'scheduled exam'],
        'completed' => ['completed', 'conducted', 'done', 'finished', 'over', 'held', 'took place', 'happened'],
        'canceled' => ['canceled', 'cancelled', 'cancelled exam', 'canceled exam'],
    ];

    public const ATTENDANCE_STATUS_ALIASES = [
        'present' => ['present', 'in'],
        'absent' => ['absent', 'away', 'missing', 'not present', 'did not attend'],
        'late' => ['late', 'tardy'],
        'half_day' => ['half day', 'half-day', 'half'],
        'excused' => ['excused', 'exempt', 'permitted absence'],
    ];

    /**
     * The full tool catalog.
     *
     * @return array<string, array>
     */
    public function all(): array
    {
        return [
            // ---------------------------------------------------------- Exams
            'exam.search' => [
                'module' => 'exams',
                'operation' => 'search',
                'description' => 'Search exams by type, date range, class, subject, or status.',
                'result_type' => 'list',
                'handler' => 'ExamQueryHandler',
                'method' => 'search',
                'params' => ['exam_type', 'date_from', 'date_to', 'class_section_id', 'subject_id', 'status', 'limit'],
                'keywords' => ['exam', 'exams', 'examination', 'examinations', 'exam schedule', 'exam dates', 'assessment', 'assessment schedule', 'test schedule'],
            ],
            'exam.count' => [
                'module' => 'exams',
                'operation' => 'count',
                'description' => 'Count exams matching type, date range, class, subject, or status.',
                'result_type' => 'count',
                'handler' => 'ExamQueryHandler',
                'method' => 'count',
                'params' => ['exam_type', 'date_from', 'date_to', 'class_section_id', 'subject_id', 'status'],
                'keywords' => ['how many exams', 'number of exams', 'exam count', 'count exams'],
            ],
            'exam.get' => [
                'module' => 'exams',
                'operation' => 'get',
                'description' => 'Get details of a specific exam by date, subject, class and type.',
                'result_type' => 'single',
                'handler' => 'ExamQueryHandler',
                'method' => 'get',
                'params' => ['exam_type', 'date_from', 'date_to', 'class_section_id', 'subject_id', 'exam_id', 'limit'],
                'keywords' => ['exam details', 'exam held on', 'exam on', 'what exam', 'which exam', 'what subject exam', 'exam details for'],
            ],
            'exam.upcoming' => [
                'module' => 'exams',
                'operation' => 'upcoming',
                'description' => 'Get upcoming (scheduled) exams within an optional date range.',
                'result_type' => 'list',
                'handler' => 'ExamQueryHandler',
                'method' => 'upcoming',
                'params' => ['exam_type', 'date_from', 'date_to', 'class_section_id', 'limit'],
                'keywords' => ['upcoming exams', 'exams coming up', 'next exams', 'upcoming exam', 'future exams', 'exam schedule'],
            ],
            'exam.completed' => [
                'module' => 'exams',
                'operation' => 'completed',
                'description' => 'Get completed exams within an optional date range.',
                'result_type' => 'list',
                'handler' => 'ExamQueryHandler',
                'method' => 'completed',
                'params' => ['exam_type', 'date_from', 'date_to', 'class_section_id', 'limit'],
                'keywords' => ['completed exams', 'completed exam', 'exams done', 'exams conducted', 'finished exams', 'past exams'],
            ],

            // -------------------------------------------------------- Students
            'student.total' => [
                'module' => 'students',
                'operation' => 'total',
                'description' => 'Get total number of active students.',
                'result_type' => 'count',
                'handler' => 'StudentQueryHandler',
                'method' => 'count',
                'params' => ['class_section_id'],
                'keywords' => ['total students', 'how many students', 'number of students', 'all students', 'student count', 'students enrolled'],
            ],
            'student.search' => [
                'module' => 'students',
                'operation' => 'search',
                'description' => 'Search students by class, section, or name.',
                'result_type' => 'list',
                'handler' => 'StudentQueryHandler',
                'method' => 'search',
                'params' => ['class_section_id', 'name', 'limit'],
                'keywords' => ['students in', 'students of', 'show students', 'list students', 'find students', 'student list'],
            ],
            'student.by_class' => [
                'module' => 'students',
                'operation' => 'by_class',
                'description' => 'Get student count grouped by class.',
                'result_type' => 'summary',
                'handler' => 'StudentQueryHandler',
                'method' => 'byClass',
                'params' => [],
                'keywords' => ['students by class', 'class wise students', 'students in each class', 'class wise count', 'per class students', 'students per class'],
            ],
            'student.admitted_this_month' => [
                'module' => 'students',
                'operation' => 'admitted_this_month',
                'description' => 'Get count of students admitted this month.',
                'result_type' => 'count',
                'handler' => 'StudentQueryHandler',
                'method' => 'admittedThisMonthCount',
                'params' => [],
                'keywords' => ['admitted this month', 'joined this month', 'new admissions this month', 'students admitted this month'],
            ],

            // ------------------------------------------------------- Attendance
            'attendance.search' => [
                'module' => 'attendance',
                'operation' => 'search',
                'description' => 'Search attendance records by date range, class, or status.',
                'result_type' => 'list',
                'handler' => 'AttendanceQueryHandler',
                'method' => 'search',
                'params' => ['date_from', 'date_to', 'class_section_id', 'status', 'limit'],
                'keywords' => ['attendance', 'attendance record', 'attendance list', 'show attendance', 'daily attendance', 'attendance status'],
            ],
            'attendance.absent' => [
                'module' => 'attendance',
                'operation' => 'absent',
                'description' => 'Get students absent on a specific date or date range.',
                'result_type' => 'list',
                'handler' => 'AttendanceQueryHandler',
                'method' => 'absent',
                'params' => ['date_from', 'date_to', 'class_section_id', 'limit'],
                'keywords' => ['absent today', 'who is absent', 'absentees', 'absent students', 'missing students', 'not present today', 'absent yesterday', 'who was absent'],
            ],
            'attendance.summary' => [
                'module' => 'attendance',
                'operation' => 'summary',
                'description' => 'Get attendance summary (present/absent/late counts and percentage) for a date range.',
                'result_type' => 'summary',
                'handler' => 'AttendanceQueryHandler',
                'method' => 'summary',
                'params' => ['date_from', 'date_to', 'class_section_id'],
                'keywords' => ['attendance summary', 'attendance percentage', 'attendance report', 'overall attendance', 'attendance rate', 'attendance today', 'today attendance', 'attendance this month'],
            ],
            'attendance.below_75' => [
                'module' => 'attendance',
                'operation' => 'below_75',
                'description' => 'Get students with attendance below 75%.',
                'result_type' => 'list',
                'handler' => 'AttendanceQueryHandler',
                'method' => 'below75',
                'params' => ['class_section_id', 'limit'],
                'keywords' => ['below 75% attendance', 'attendance below 75', 'low attendance', 'attendance below 75 percent', 'students with less than 75 attendance'],
            ],

            // ------------------------------------------------------------- Fees
            'fee.outstanding' => [
                'module' => 'fees',
                'operation' => 'outstanding',
                'description' => 'Get total outstanding / unpaid fee amount.',
                'result_type' => 'summary',
                'handler' => 'FeeQueryHandler',
                'method' => 'outstanding',
                'params' => [],
                'keywords' => ['total outstanding fees', 'outstanding fee', 'pending fees total', 'total pending fees', 'outstanding amount', 'total fee pending', 'fees outstanding', 'unpaid fees total'],
            ],
            'fee.pending' => [
                'module' => 'fees',
                'operation' => 'pending',
                'description' => 'Get students with unpaid / outstanding fees.',
                'result_type' => 'list',
                'handler' => 'FeeQueryHandler',
                'method' => 'pending',
                'params' => ['class_section_id', 'limit'],
                'keywords' => ['students with unpaid fees', 'unpaid fees', 'which students have unpaid fees', 'pending fees', 'fee defaulters', 'outstanding fees list', 'students with pending fees', 'who has not paid fees'],
            ],
            'fee.pending_above' => [
                'module' => 'fees',
                'operation' => 'pending_above',
                'description' => 'Get students with pending fees above a threshold amount.',
                'result_type' => 'list',
                'handler' => 'FeeQueryHandler',
                'method' => 'pendingAbove',
                'params' => ['amount', 'limit'],
                'keywords' => ['pending fees above', 'fees above', 'pending fee more than', 'outstanding above', 'defaulters above'],
            ],
            'fee.today_collection' => [
                'module' => 'fees',
                'operation' => 'today_collection',
                'description' => 'Get today\'s fee collection total.',
                'result_type' => 'summary',
                'handler' => 'FeeQueryHandler',
                'method' => 'todayCollection',
                'params' => [],
                'keywords' => ['today collection', 'today fees collection', 'collected today', 'today fee collection', 'today payment', 'total fee collection this month', 'fee collection this month', 'fee collection'],
            ],
            'fee.top_defaulters' => [
                'module' => 'fees',
                'operation' => 'top_defaulters',
                'description' => 'Get top fee defaulters list.',
                'result_type' => 'list',
                'handler' => 'FeeQueryHandler',
                'method' => 'topDefaulters',
                'params' => ['limit'],
                'keywords' => ['top fee defaulters', 'top defaulters', 'largest defaulters', 'biggest defaulters', 'highest pending fees'],
            ],

            // -------------------------------------------------------- Homework
            'homework.pending' => [
                'module' => 'homework',
                'operation' => 'pending',
                'description' => 'Get pending homework assignments, optionally for a class.',
                'result_type' => 'list',
                'handler' => 'HomeworkQueryHandler',
                'method' => 'pending',
                'params' => ['class_section_id', 'limit'],
                'keywords' => ['pending homework', 'homework pending', 'show pending homework', 'what homework is pending', 'incomplete homework', 'homework not done', 'pending homework for'],
            ],
            'homework.due' => [
                'module' => 'homework',
                'operation' => 'due',
                'description' => 'Get homework due today or on a specific date.',
                'result_type' => 'list',
                'handler' => 'HomeworkQueryHandler',
                'method' => 'due',
                'params' => ['date', 'class_section_id', 'limit'],
                'keywords' => ['homework due today', 'homework due this week', 'due homework', 'what homework is due', 'homework deadline', 'homework due'],
            ],
            'homework.list' => [
                'module' => 'homework',
                'operation' => 'list',
                'description' => 'List all homework assignments.',
                'result_type' => 'list',
                'handler' => 'HomeworkQueryHandler',
                'method' => 'list',
                'params' => ['class_section_id', 'subject_id', 'limit'],
                'keywords' => ['list homework', 'all homework', 'show homework', 'homework assignments', 'homework list', 'show all homework'],
            ],

            // --------------------------------------------------------- Teachers
            'teacher.total' => [
                'module' => 'teachers',
                'operation' => 'total',
                'description' => 'Get total number of teachers.',
                'result_type' => 'count',
                'handler' => 'TeacherQueryHandler',
                'method' => 'count',
                'params' => [],
                'keywords' => ['how many teachers', 'total teachers', 'number of teachers', 'teachers count'],
            ],
            'teacher.search' => [
                'module' => 'teachers',
                'operation' => 'search',
                'description' => 'Search teachers by name or subject.',
                'result_type' => 'list',
                'handler' => 'TeacherQueryHandler',
                'method' => 'search',
                'params' => ['name', 'subject_id', 'limit'],
                'keywords' => ['teachers', 'list teachers', 'show teachers', 'teacher list', 'find teachers'],
            ],
            'teacher.on_leave' => [
                'module' => 'teachers',
                'operation' => 'on_leave',
                'description' => 'Get teachers on leave today or on a specific date.',
                'result_type' => 'list',
                'handler' => 'TeacherQueryHandler',
                'method' => 'onLeave',
                'params' => ['date', 'limit'],
                'keywords' => ['teachers on leave', 'teacher on leave today', 'which teachers are on leave', 'teachers absent today', 'teachers on leave today', 'teacher leave today'],
            ],

            // ------------------------------------------------------------ Leave
            'leave.pending' => [
                'module' => 'leave',
                'operation' => 'pending',
                'description' => 'Get pending leave requests.',
                'result_type' => 'list',
                'handler' => 'LeaveQueryHandler',
                'method' => 'pendingLeave',
                'params' => ['limit'],
                'keywords' => ['pending leave', 'leave pending', 'leave requests', 'show leave requests', 'leave applications', 'pending leave requests'],
            ],

            // --------------------------------------------------------- Transport
            'transport.status' => [
                'module' => 'transport',
                'operation' => 'status',
                'description' => 'Get today\'s transport status: active routes and buses.',
                'result_type' => 'summary',
                'handler' => 'TransportQueryHandler',
                'method' => 'status',
                'params' => ['date'],
                'keywords' => ['transport status', 'today transport status', 'transport today', 'bus status', 'buses active', 'which buses are active', 'active buses', 'buses running today', 'transport running'],
            ],
            'transport.routes' => [
                'module' => 'transport',
                'operation' => 'routes',
                'description' => 'Get list of bus routes.',
                'result_type' => 'list',
                'handler' => 'TransportQueryHandler',
                'method' => 'routes',
                'params' => ['limit'],
                'keywords' => ['bus routes', 'show routes', 'transport routes', 'bus route list', 'route list', 'routes'],
            ],
            'transport.route_occupancy' => [
                'module' => 'transport',
                'operation' => 'route_occupancy',
                'description' => 'Get route occupancy stats.',
                'result_type' => 'summary',
                'handler' => 'TransportQueryHandler',
                'method' => 'routeOccupancyStructured',
                'params' => [],
                'keywords' => ['route occupancy', 'occupancy by route', 'route wise students', 'route capacity', 'route fill'],
            ],
            'transport.students_on_route' => [
                'module' => 'transport',
                'operation' => 'students_on_route',
                'description' => 'Get students per route.',
                'result_type' => 'list',
                'handler' => 'TransportQueryHandler',
                'method' => 'studentsOnRouteStructured',
                'params' => ['limit'],
                'keywords' => ['students on route', 'students per route', 'route students', 'transport students'],
            ],

            // ---------------------------------------------------------- Library
            'library.books_issued' => [
                'module' => 'library',
                'operation' => 'books_issued',
                'description' => 'Get currently issued books count.',
                'result_type' => 'count',
                'handler' => 'LibraryQueryHandler',
                'method' => 'booksIssuedStructured',
                'params' => [],
                'keywords' => ['books issued', 'issued books', 'books currently issued', 'total issued books'],
            ],
            'library.overdue_books' => [
                'module' => 'library',
                'operation' => 'overdue_books',
                'description' => 'Get overdue books count.',
                'result_type' => 'count',
                'handler' => 'LibraryQueryHandler',
                'method' => 'overdueBooksStructured',
                'params' => [],
                'keywords' => ['overdue books', 'overdue', 'books overdue', 'late books', 'overdue returns'],
            ],
            'library.fine_collection' => [
                'module' => 'library',
                'operation' => 'fine_collection',
                'description' => 'Get total fine collected.',
                'result_type' => 'count',
                'handler' => 'LibraryQueryHandler',
                'method' => 'fineCollectionStructured',
                'params' => [],
                'keywords' => ['fine collection', 'total fine', 'fines collected', 'library fine', 'fine amount'],
            ],

            // ---------------------------------------------------------- Payroll
            'payroll.summary' => [
                'module' => 'payroll',
                'operation' => 'summary',
                'description' => 'Get a summary of the latest payroll processing, including employee count and total net payroll.',
                'result_type' => 'summary',
                'handler' => 'PayrollQueryHandler',
                'method' => 'summary',
                'params' => [],
                'keywords' => ['payroll summary', 'payroll report', 'payroll overview', 'payroll status', 'how much payroll', 'payroll details'],
            ],
            'payroll.latest_run' => [
                'module' => 'payroll',
                'operation' => 'latest_run',
                'description' => 'Get latest payroll run details.',
                'result_type' => 'single',
                'handler' => 'PayrollQueryHandler',
                'method' => 'latestRunStructured',
                'params' => [],
                'keywords' => ['latest payroll run', 'last payroll', 'recent payroll run', 'latest payroll'],
            ],
            'payroll.locked_runs' => [
                'module' => 'payroll',
                'operation' => 'locked_runs',
                'description' => 'Get count of locked payroll runs.',
                'result_type' => 'count',
                'handler' => 'PayrollQueryHandler',
                'method' => 'lockedRuns',
                'params' => [],
                'keywords' => ['locked payroll runs', 'locked runs', 'locked payroll', 'payroll locked'],
            ],
            'payroll.highest_salary' => [
                'module' => 'payroll',
                'operation' => 'highest_salary',
                'description' => 'Get highest salary employees.',
                'result_type' => 'list',
                'handler' => 'PayrollQueryHandler',
                'method' => 'highestSalary',
                'params' => ['limit'],
                'keywords' => ['highest salary', 'top salary', 'highest paid', 'maximum salary', 'highest earning'],
            ],
            'payroll.generated_this_month' => [
                'module' => 'payroll',
                'operation' => 'generated_this_month',
                'description' => 'Get payroll runs generated this month.',
                'result_type' => 'count',
                'handler' => 'PayrollQueryHandler',
                'method' => 'generatedThisMonth',
                'params' => [],
                'keywords' => ['payroll generated this month', 'payroll this month', 'monthly payroll', 'payroll runs this month'],
            ],

            // ----------------------------------------------------- Executive
            'school.summary' => [
                'module' => 'reports',
                'operation' => 'school_summary',
                'description' => 'Get executive school summary combining attendance, fees, transport, homework, exams, leave, and notifications.',
                'result_type' => 'summary',
                'handler' => 'SchoolSummaryHandler',
                'method' => 'getExecutiveSummary',
                'params' => [],
                'keywords' => ['school summary', 'executive summary', 'summary of the school', 'today school summary', 'how is my school', 'school health', 'school report', 'give me an executive summary', 'what needs my attention'],
            ],
        ];
    }

    /**
     * Action tools (destructive / side-effect operations) that require confirmation.
     */
    public function actionTools(): array
    {
        return [
            'payroll.generate' => [
                'module' => 'payroll',
                'operation' => 'generate',
                'description' => 'Generate payroll for a specific month/year.',
                'params' => ['month', 'year'],
                'type' => 'agent',
                'agent' => 'payroll',
                'label' => 'Payroll Agent',
            ],
            'attendance.notify' => [
                'module' => 'attendance',
                'operation' => 'notify',
                'description' => 'Send absence notifications to parents.',
                'params' => ['date'],
                'type' => 'agent',
                'agent' => 'attendance',
                'label' => 'Attendance Agent',
            ],
            'fee.send_reminders' => [
                'module' => 'fees',
                'operation' => 'send_reminders',
                'description' => 'Send fee reminders to defaulters.',
                'params' => ['days'],
                'type' => 'agent',
                'agent' => 'fee_collection',
                'label' => 'Fee Collection Agent',
            ],
            'exam.publish' => [
                'module' => 'exams',
                'operation' => 'publish',
                'description' => 'Publish exam results.',
                'params' => ['exam_id'],
                'type' => 'service',
                'service' => 'exam',
                'method' => 'publish',
                'label' => 'Publish Exam Results',
            ],
            'notification.send' => [
                'module' => 'notifications',
                'operation' => 'send',
                'description' => 'Send a notification to users.',
                'params' => ['title', 'message', 'target_type'],
                'type' => 'service',
                'service' => 'notification',
                'method' => 'create',
                'label' => 'Send Notification',
            ],
            'homework.create' => [
                'module' => 'homework',
                'operation' => 'create',
                'description' => 'Create a new homework assignment.',
                'params' => ['class_section_id', 'subject_id', 'title', 'due_date'],
                'type' => 'service',
                'service' => 'homework',
                'method' => 'create',
                'label' => 'Create Homework',
            ],
            'transport.assign' => [
                'module' => 'transport',
                'operation' => 'assign',
                'description' => 'Assign transport to students.',
                'params' => ['route_id', 'student_ids'],
                'type' => 'service',
                'service' => 'transport',
                'method' => 'assignStudents',
                'label' => 'Assign Transport',
            ],
        ];
    }

    public function get(string $tool): ?array
    {
        $tools = $this->all();

        if (isset($tools[$tool])) {
            return $tools[$tool];
        }

        return $this->actionTools()[$tool] ?? null;
    }

    public function has(string $tool): bool
    {
        return $this->get($tool) !== null;
    }

    public function isAction(string $tool): bool
    {
        return isset($this->actionTools()[$tool]);
    }

    /**
     * All query tool names.
     */
    public function toolNames(): array
    {
        return array_keys($this->all());
    }

    /**
     * All action tool names.
     */
    public function actionToolNames(): array
    {
        return array_keys($this->actionTools());
    }

    /**
     * Best-effort keyword match of a free-text question to a tool.
     */
    public function matchByKeywords(string $question): ?string
    {
        $lower = mb_strtolower(trim($question));
        $best = null;
        $bestScore = 0;

        foreach ($this->all() as $tool => $config) {
            $base = 0;
            foreach ($config['keywords'] as $keyword) {
                $base = max($base, $this->scoreKeyword($lower, $keyword));
            }

            // Domain-signal boosts: when the question contains a strong
            // status/entity signal, prefer the tool for that domain. The count
            // boost only applies when the tool's own keywords matched, so a
            // generic "how many" cannot hijack an unrelated module's count tool.
            $score = $base + $this->signalBoost($lower, $tool, $base);

            if (config('app.debug') && app()->runningInConsole()) {
                // Reserved for debugging; no-op in production.
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $tool;
            }
        }

        return $bestScore > 0 ? $best : null;
    }

    private function signalBoost(string $lower, string $tool, int $baseScore): int
    {
        $boost = 0;

        // Count questions ("how many", "number of", "count") -> prefer the
        // matching module's count tool, but only when its keywords matched.
        if (preg_match('/\b(how many|number of|count|total number|count of)\b/', $lower)) {
            $isCountTool = str_ends_with($tool, '.count')
                || $tool === 'student.total'
                || $tool === 'teacher.total';
            if ($isCountTool && $baseScore > 0) {
                $boost += 35;
            }
        }

        // Specific "admitted this month" signal beats the generic student count.
        if (preg_match('/\b(admitted|joined|new admissions)\b/', $lower) && $tool === 'student.admitted_this_month') {
            $boost += 45;
        }

        // "pending fees above <amount>" -> fee.pending_above. Strong signal:
        // an explicit amount threshold is present, so prefer the threshold tool.
        if (preg_match('/\b(?:pending\s+fees|fees?)\s+(?:above|more\s+than|over)\s+\d+|\babove\s+\d+/', $lower) && $tool === 'fee.pending_above') {
            $boost += 55;
        }

        // Exam status signals: completed/upcoming exams -> the specific tool.
        if (preg_match('/\b(completed|done|finished|conducted)\b/', $lower) && $tool === 'exam.completed') {
            $boost += 30;
        }
        if (preg_match('/\b(upcoming|coming up|next exams|future exams)\b/', $lower) && $tool === 'exam.upcoming') {
            $boost += 30;
        }

        // Attendance status signals: present / absent / missing.
        if (preg_match('/\b(absent|present|missing|not present)\b/', $lower)) {
            if ($tool === 'attendance.absent' || $tool === 'attendance.summary' || $tool === 'attendance.search') {
                $boost += 40;
            }
        }

        // "today's attendance" / "attendance today" -> summary (counts, not records).
        if (str_contains($lower, 'attendance') && preg_match('/\b(today|yesterday|this week|this month|monthly)\b/', $lower)) {
            if ($tool === 'attendance.summary') {
                $boost += 30;
            }
        }

        // "which/who students have outstanding fees" -> list of students, not aggregate.
        if (preg_match('/\b(outstanding|unpaid|pending)\b.*\bstudents?\b|\bstudents?\b.*\b(outstanding|unpaid|pending)\b/', $lower)) {
            if ($tool === 'fee.pending') {
                $boost += 40;
            }
        }

        // Fee signals: outstanding / unpaid / dues / pending.
        if (preg_match('/\b(outstanding|unpaid|dues|fee defaulters|not paid|haven.t paid)\b/', $lower)) {
            if ($tool === 'fee.outstanding' || $tool === 'fee.pending' || $tool === 'fee.pending_above' || $tool === 'fee.top_defaulters') {
                $boost += 35;
            }
        }

        // Bus / transport signals.
        if (preg_match('/\b(bus|buses|route)\b/', $lower)) {
            if ($tool === 'transport.status' || $tool === 'transport.routes' || $tool === 'transport.route_occupancy' || $tool === 'transport.students_on_route') {
                $boost += 30;
            }
        }

        // Homework signals.
        if (preg_match('/\bhomework|assignment\b/', $lower)) {
            if ($tool === 'homework.pending' || $tool === 'homework.due' || $tool === 'homework.list') {
                $boost += 25;
            }
        }

        return $boost;
    }

    private const STOP_WORDS = [
        'what', 'which', 'who', 'is', 'are', 'was', 'were', 'did', 'do', 'does',
        'the', 'a', 'an', 'of', 'for', 'to', 'show', 'me', 'how', 'many', 'any',
        'or', 'and', 'in', 'on', 'at', 'have', 'has', 'had', 'been', 'be', 'please',
        'could', 'can', 'would', 'will', 'my', 'our', 'your', 'all', 'list', 'get',
        'find', 'give', 'there', 'today', 'yesterday', 'tomorrow', 'this', 'that',
        'some', 'any', 'with', 'from', 'by', 'about', 'over', 'under', 'into',
    ];

    private function scoreKeyword(string $question, string $keyword): int
    {
        $score = 0;
        $keywordWords = array_diff(explode(' ', $keyword), self::STOP_WORDS);
        $questionWords = explode(' ', $question);

        if (empty($keywordWords)) {
            return 0;
        }

        $meaningful = false;
        foreach ($keywordWords as $kw) {
            if ($kw === '' || in_array($kw, self::STOP_WORDS, true)) {
                continue;
            }
            $meaningful = true;
            if (in_array($kw, $questionWords, true)) {
                $score += 10;
            } elseif (str_contains($question, $kw)) {
                $score += 5;
            }
        }

        if (!$meaningful) {
            return 0;
        }

        $score += count(array_intersect($keywordWords, $questionWords)) * 3;

        if (str_contains($question, $keyword)) {
            $score += 20;
        }

        return $score;
    }

    /**
     * Canonical param schema for a tool (whitelist of accepted params).
     */
    public function paramSchema(string $tool): array
    {
        $config = $this->get($tool);

        return $config['params'] ?? [];
    }

    /**
     * Normalize exam type aliases into canonical values, e.g.
     * "Half Yearly" -> "half_yearly", "Mid Term" -> "mid_term".
     *
     * @return array<int, string> canonical exam type values
     */
    public function normalizeExamTypes(array $values): array
    {
        $result = [];
        foreach ($values as $value) {
            $canonical = $this->normalizeAlias($value, self::EXAM_TYPE_ALIASES);
            if ($canonical && !in_array($canonical, $result, true)) {
                $result[] = $canonical;
            }
        }

        return $result;
    }

    public function normalizeStatus(?string $value): ?string
    {
        return $this->normalizeAlias($value ?? '', self::STATUS_ALIASES);
    }

    public function normalizeAttendanceStatus(?string $value): ?string
    {
        return $this->normalizeAlias($value ?? '', self::ATTENDANCE_STATUS_ALIASES);
    }

    private function normalizeAlias(string $value, array $aliasMap): ?string
    {
        $needle = mb_strtolower(trim($value));

        if ($needle === '') {
            return null;
        }

        foreach ($aliasMap as $canonical => $aliases) {
            if ($needle === $canonical || in_array($needle, $aliases, true)) {
                return $canonical;
            }
            foreach ($aliases as $alias) {
                if (str_contains($needle, $alias)) {
                    return $canonical;
                }
            }
        }

        return null;
    }

    /**
     * Extract canonical exam types from free text, e.g. "mid term or half yearly" -> [mid_term, half_yearly].
     *
     * Longest-alias matching is used so that "half yearly" maps to half_yearly
     * and does not also match annual's generic "yearly" alias.
     *
     * @return array<int, string>
     */
    public function extractExamTypesFromText(string $question): array
    {
        $lower = mb_strtolower($question);
        $matches = [];

        foreach (self::EXAM_TYPE_ALIASES as $canonical => $aliases) {
            $best = null;
            foreach ($aliases as $alias) {
                if (str_contains($lower, $alias)) {
                    if ($best === null || mb_strlen($alias) > mb_strlen($best)) {
                        $best = $alias;
                    }
                }
            }
            if ($best !== null) {
                $matches[] = ['canonical' => $canonical, 'alias' => $best];
            }
        }

        // Drop a canonical whose matched alias is a substring of a longer
        // matched alias belonging to a different canonical ("yearly" inside
        // "half yearly" would otherwise also flag annual).
        $filtered = [];
        foreach ($matches as $match) {
            $isSubstring = false;
            foreach ($matches as $other) {
                if ($other['canonical'] === $match['canonical']) {
                    continue;
                }
                if (mb_strlen($other['alias']) > mb_strlen($match['alias'])
                    && mb_strpos($other['alias'], $match['alias']) !== false) {
                    $isSubstring = true;
                    break;
                }
            }
            if (!$isSubstring) {
                $filtered[] = $match['canonical'];
            }
        }

        return array_values(array_unique($filtered));
    }

    /**
     * Determine the desired result shape from the question (count vs list vs detail).
     */
    public function inferResultType(string $question): string
    {
        $lower = mb_strtolower($question);

        if (preg_match('/\b(how many|number of|count)\b/', $lower)) {
            return 'count';
        }

        if (preg_match('/\b(any|was there|did we|is there|did .* have|was .* scheduled|happened|conducted)\b/', $lower)) {
            return 'list';
        }

        if (preg_match('/\b(which|what|who|list|show|all|details|everything about)\b/', $lower)) {
            return 'list';
        }

        if (preg_match('/\b(summary|overview|status|report)\b/', $lower)) {
            return 'summary';
        }

        return 'list';
    }

    public function dateParser(): NaturalLanguageDateParser
    {
        return new NaturalLanguageDateParser();
    }
}
