<?php

namespace App\Modules\AiAssistant\Services;

class IntentResolver
{
    private const INTENTS = [
        'student.total' => [
            'keywords' => ['total students', 'all students', 'student count', 'how many students', 'number of students'],
            'handler' => 'StudentQueryHandler',
            'method' => 'totalStudents',
        ],
        'student.admitted_this_month' => [
            'keywords' => ['admitted this month', 'joined this month', 'new admissions this month', 'students admitted this month'],
            'handler' => 'StudentQueryHandler',
            'method' => 'admittedThisMonth',
        ],
        'student.by_class' => [
            'keywords' => ['students by class', 'class wise students', 'students in each class', 'class wise count', 'per class students'],
            'handler' => 'StudentQueryHandler',
            'method' => 'studentsByClass',
        ],
        'attendance.absent_today' => [
            'keywords' => ['absent today', 'today absent', 'students absent today', 'who is absent today', 'absentees today'],
            'handler' => 'AttendanceQueryHandler',
            'method' => 'absentToday',
        ],
        'attendance.monthly_percentage' => [
            'keywords' => ['monthly attendance', 'attendance percentage', 'attendance rate', 'monthly attendance percent'],
            'handler' => 'AttendanceQueryHandler',
            'method' => 'monthlyPercentage',
        ],
        'attendance.below_75' => [
            'keywords' => ['below 75% attendance', 'attendance below 75', 'low attendance', 'attendance below 75 percent', 'students with less than 75 attendance'],
            'handler' => 'AttendanceQueryHandler',
            'method' => 'studentsBelow75',
        ],
        'fee.outstanding' => [
            'keywords' => ['total outstanding fees', 'outstanding fee', 'pending fees total', 'total pending fees', 'outstanding amount'],
            'handler' => 'FeeQueryHandler',
            'method' => 'totalOutstanding',
        ],
        'fee.pending_above' => [
            'keywords' => ['pending fees above', 'fees above', 'pending fee more than', 'outstanding above', 'defaulters above'],
            'handler' => 'FeeQueryHandler',
            'method' => 'studentsWithPendingAbove',
        ],
        'fee.today_collection' => [
            'keywords' => ['today collection', 'today fees collection', 'collected today', 'today fee collection', 'today payment'],
            'handler' => 'FeeQueryHandler',
            'method' => 'todayCollection',
        ],
        'fee.top_defaulters' => [
            'keywords' => ['top fee defaulters', 'top defaulters', 'largest defaulters', 'biggest defaulters', 'highest pending fees'],
            'handler' => 'FeeQueryHandler',
            'method' => 'topDefaulters',
        ],
        'transport.route_occupancy' => [
            'keywords' => ['route occupancy', 'occupancy by route', 'route wise students', 'route capacity', 'route fill'],
            'handler' => 'TransportQueryHandler',
            'method' => 'routeOccupancy',
        ],
        'transport.students_on_route' => [
            'keywords' => ['students on route', 'students per route', 'route students', 'transport students'],
            'handler' => 'TransportQueryHandler',
            'method' => 'studentsOnRoute',
        ],
        'transport.vehicle_assignments' => [
            'keywords' => ['vehicle assignments', 'assigned vehicles', 'vehicle allocation', 'vehicles assigned'],
            'handler' => 'TransportQueryHandler',
            'method' => 'vehicleAssignments',
        ],
        'library.books_issued' => [
            'keywords' => ['books issued', 'issued books', 'books currently issued', 'total issued books'],
            'handler' => 'LibraryQueryHandler',
            'method' => 'booksIssued',
        ],
        'library.overdue_books' => [
            'keywords' => ['overdue books', 'overdue', 'books overdue', 'late books', 'overdue returns'],
            'handler' => 'LibraryQueryHandler',
            'method' => 'overdueBooks',
        ],
        'library.fine_collection' => [
            'keywords' => ['fine collection', 'total fine', 'fines collected', 'library fine', 'fine amount'],
            'handler' => 'LibraryQueryHandler',
            'method' => 'fineCollection',
        ],
        'payroll.latest_run' => [
            'keywords' => ['latest payroll run', 'last payroll', 'recent payroll run', 'latest payroll'],
            'handler' => 'PayrollQueryHandler',
            'method' => 'latestRun',
        ],
        'payroll.locked_runs' => [
            'keywords' => ['locked payroll runs', 'locked runs', 'locked payroll', 'payroll locked'],
            'handler' => 'PayrollQueryHandler',
            'method' => 'lockedRuns',
        ],
        'payroll.highest_salary' => [
            'keywords' => ['highest salary', 'top salary', 'highest paid', 'maximum salary', 'highest earning'],
            'handler' => 'PayrollQueryHandler',
            'method' => 'highestSalaryEmployees',
        ],
        'payroll.generated_this_month' => [
            'keywords' => ['payroll generated this month', 'payroll this month', 'monthly payroll', 'payroll runs this month'],
            'handler' => 'PayrollQueryHandler',
            'method' => 'generatedThisMonth',
        ],
    ];

    public function resolve(string $question): ?array
    {
        $lower = mb_strtolower(trim($question));

        $bestMatch = null;
        $bestScore = 0;

        foreach (self::INTENTS as $intent => $config) {
            foreach ($config['keywords'] as $keyword) {
                $score = $this->matchScore($lower, $keyword);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $config;
                }
            }
        }

        return $bestMatch;
    }

    private function matchScore(string $question, string $keyword): int
    {
        $keywordLen = strlen($keyword);
        $questionLen = strlen($question);

        if ($questionLen === 0) {
            return 0;
        }

        $exactMatches = 0;
        $keywordWords = explode(' ', $keyword);
        $questionWords = explode(' ', $question);

        $score = 0;
        foreach ($keywordWords as $kw) {
            if (in_array($kw, $questionWords, true)) {
                $score += 10;
            } elseif (str_contains($question, $kw)) {
                $score += 5;
            }
        }

        $wordOverlap = count(array_intersect($keywordWords, $questionWords));
        $score += $wordOverlap * 3;

        if (str_contains($question, $keyword)) {
            $score += 20;
        }

        return $score;
    }

    public static function getIntentsForTesting(): array
    {
        return self::INTENTS;
    }

    public function getSupportedQuestions(): array
    {
        $groups = [];
        foreach (self::INTENTS as $intent => $config) {
            $category = explode('.', $intent)[0];
            $groups[$category][] = $config['keywords'][0];
        }
        return $groups;
    }
}
