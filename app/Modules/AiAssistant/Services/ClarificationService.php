<?php

namespace App\Modules\AiAssistant\Services;

class ClarificationService
{
    private const CONFIDENCE_THRESHOLD = 0.85;

    private const REQUIRED_PARAMS = [
        'fee.pending_above' => ['amount'],
        'payroll.generate' => ['month', 'year'],
        'attendance.notify' => ['date'],
        'fee.send_reminders' => ['days'],
        'exam.publish' => ['exam_id'],
        'notification.send' => ['title', 'message', 'target_type'],
        'homework.create' => ['class_section_id', 'subject_id', 'title', 'due_date'],
        'transport.assign' => ['route_id', 'student_ids'],
    ];

    private array $modules;

    public function __construct()
    {
        $this->modules = config('ai.modules', []);
    }

    public function needsClarification(array $intentResult): ?array
    {
        $confidence = $intentResult['confidence'] ?? 0.0;
        $intent = $intentResult['intent'] ?? 'unknown';
        $parameters = $intentResult['parameters'] ?? [];

        if ($confidence < self::CONFIDENCE_THRESHOLD) {
            return $this->buildModuleClarification($intentResult);
        }

        if ($intent !== 'unknown' && $this->hasMissingRequiredParams($intent, $parameters)) {
            return $this->buildParamClarification($intent, $parameters);
        }

        return null;
    }

    private function hasMissingRequiredParams(string $intent, array $parameters): bool
    {
        $required = self::REQUIRED_PARAMS[$intent] ?? [];

        foreach ($required as $param) {
            if (empty($parameters[$param])) {
                return true;
            }
        }

        return false;
    }

    private function buildModuleClarification(array $intentResult): ?array
    {
        $module = $intentResult['module'] ?? null;

        if (!$module) {
            $module = $this->guessModule($intentResult);
        }

        if (!$module || !isset($this->modules[$module]['clarification'])) {
            return $this->buildGenericClarification();
        }

        $clarification = $this->modules[$module]['clarification'];
        $options = $clarification['options'] ?? [];
        $prompt = $clarification['prompt'] ?? 'Which option would you like?';

        if (empty($options)) {
            return $this->buildGenericClarification();
        }

        return [
            'type' => 'clarification',
            'question' => $prompt,
            'options' => array_keys($options),
            'intent_map' => $options,
        ];
    }

    private function guessModule(array $intentResult): ?string
    {
        $intent = $intentResult['intent'] ?? '';

        if ($intent === 'unknown') {
            return null;
        }

        $parts = explode('.', $intent);
        $module = $parts[0] ?? '';

        $moduleMap = [
            'student' => 'students',
            'attendance' => 'attendance',
            'fee' => 'fees',
            'transport' => 'transport',
            'library' => 'library',
            'payroll' => 'payroll',
            'exam' => 'exams',
            'homework' => 'homework',
            'notification' => 'notifications',
            'school' => 'reports',
        ];

        return $moduleMap[$module] ?? null;
    }

    private function buildParamClarification(string $intent, array $parameters): ?array
    {
        $required = self::REQUIRED_PARAMS[$intent] ?? [];

        foreach ($required as $param) {
            if (empty($parameters[$param])) {
                return $this->buildParamQuestion($param);
            }
        }

        return null;
    }

    private function buildParamQuestion(string $param): ?array
    {
        $questions = [
            'amount' => [
                'question' => 'What is the minimum fee amount?',
                'options' => ['₹1,000', '₹5,000', '₹10,000', '₹25,000'],
                'param_name' => 'amount',
            ],
            'month' => [
                'question' => 'Which month?',
                'options' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                'param_name' => 'month',
            ],
            'year' => [
                'question' => 'Which year?',
                'options' => [date('Y'), date('Y', strtotime('-1 year')), date('Y', strtotime('+1 year'))],
                'param_name' => 'year',
            ],
            'date' => [
                'question' => 'For which date?',
                'options' => ['Today', 'Yesterday'],
                'param_name' => 'date',
            ],
            'days' => [
                'question' => 'How many days overdue?',
                'options' => ['30 days', '60 days', '90 days'],
                'param_name' => 'days',
            ],
            'exam_id' => [
                'question' => 'Which exam?',
                'options' => [],
                'param_name' => 'exam_id',
            ],
            'title' => [
                'question' => 'What is the notification title?',
                'options' => [],
                'param_name' => 'title',
            ],
            'message' => [
                'question' => 'What is the notification message?',
                'options' => [],
                'param_name' => 'message',
            ],
            'target_type' => [
                'question' => 'Who should receive this?',
                'options' => ['Students', 'Teachers', 'Parents', 'All'],
                'param_name' => 'target_type',
            ],
            'class_section_id' => [
                'question' => 'Which class?',
                'options' => [],
                'param_name' => 'class_section_id',
            ],
            'subject_id' => [
                'question' => 'Which subject?',
                'options' => [],
                'param_name' => 'subject_id',
            ],
            'due_date' => [
                'question' => 'When is it due?',
                'options' => ['Today', 'Tomorrow', 'This Week', 'Next Week'],
                'param_name' => 'due_date',
            ],
            'route_id' => [
                'question' => 'Which route?',
                'options' => [],
                'param_name' => 'route_id',
            ],
            'student_ids' => [
                'question' => 'Which students?',
                'options' => [],
                'param_name' => 'student_ids',
            ],
        ];

        $questionData = $questions[$param] ?? null;

        if (!$questionData) {
            return null;
        }

        return [
            'type' => 'clarification',
            'question' => $questionData['question'],
            'options' => $questionData['options'],
            'param_name' => $questionData['param_name'],
        ];
    }

    private function buildGenericClarification(): array
    {
        $allOptions = [];

        foreach ($this->modules as $moduleName => $moduleConfig) {
            $clarification = $moduleConfig['clarification'] ?? [];
            $options = $clarification['options'] ?? [];

            foreach ($options as $label => $intent) {
                $allOptions[$label] = $intent;
            }
        }

        return [
            'type' => 'clarification',
            'question' => 'What would you like to do?',
            'options' => array_slice(array_keys($allOptions), 0, 10),
            'intent_map' => array_slice($allOptions, 0, 10, true),
        ];
    }

    public function resolveClarification(string $selectedOption, array $clarificationContext): ?array
    {
        $intentMap = $clarificationContext['intent_map'] ?? [];
        $paramName = $clarificationContext['param_name'] ?? null;

        if ($paramName) {
            return [
                'type' => 'param',
                'param_name' => $paramName,
                'value' => $this->normalizeParamValue($paramName, $selectedOption),
            ];
        }

        if (isset($intentMap[$selectedOption])) {
            return [
                'type' => 'intent',
                'intent' => $intentMap[$selectedOption],
            ];
        }

        return null;
    }

    private function normalizeParamValue(string $param, string $value): mixed
    {
        $normalized = strtolower(trim($value));

        if ($param === 'amount') {
            $cleaned = preg_replace('/[^0-9.]/', '', $normalized);
            return (float) $cleaned;
        }

        if ($param === 'month') {
            $monthMap = [
                'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
                'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
                'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            ];
            return $monthMap[$normalized] ?? (int) $value;
        }

        if ($param === 'year') {
            return (int) $value;
        }

        if ($param === 'date') {
            if ($normalized === 'today') {
                return now()->format('Y-m-d');
            }
            if ($normalized === 'yesterday') {
                return now()->subDay()->format('Y-m-d');
            }
            return $value;
        }

        if ($param === 'days') {
            $cleaned = preg_replace('/[^0-9]/', '', $normalized);
            return (int) $cleaned;
        }

        if ($param === 'target_type') {
            $targetMap = [
                'students' => 'students',
                'teachers' => 'teachers',
                'parents' => 'parents',
                'all' => 'all',
            ];
            return $targetMap[$normalized] ?? $normalized;
        }

        if ($param === 'due_date') {
            if ($normalized === 'today') {
                return now()->format('Y-m-d');
            }
            if ($normalized === 'tomorrow') {
                return now()->addDay()->format('Y-m-d');
            }
            if ($normalized === 'this week') {
                return now()->endOfWeek()->format('Y-m-d');
            }
            if ($normalized === 'next week') {
                return now()->addWeek()->format('Y-m-d');
            }
            return $value;
        }

        return $value;
    }

    public function getConfidenceThreshold(): float
    {
        return self::CONFIDENCE_THRESHOLD;
    }
}
