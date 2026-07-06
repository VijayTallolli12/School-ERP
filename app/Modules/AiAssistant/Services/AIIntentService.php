<?php

namespace App\Modules\AiAssistant\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIIntentService
{
    private const SUPPORTED_INTENTS = [
        'student.total' => [
            'description' => 'Get total number of active students.',
            'action' => 'query',
            'category' => 'students',
        ],
        'student.admitted_this_month' => [
            'description' => 'Get count of students admitted this month.',
            'action' => 'query',
            'category' => 'students',
        ],
        'student.by_class' => [
            'description' => 'Get student count grouped by class.',
            'action' => 'query',
            'category' => 'students',
            'group_by_options' => ['class', 'section'],
        ],
        'attendance.absent_today' => [
            'description' => 'Get count of students absent today.',
            'action' => 'query',
            'category' => 'attendance',
        ],
        'attendance.monthly_percentage' => [
            'description' => 'Get overall attendance percentage for current month.',
            'action' => 'query',
            'category' => 'attendance',
        ],
        'attendance.below_75' => [
            'description' => 'Get students with attendance below 75%.',
            'action' => 'query',
            'category' => 'attendance',
        ],
        'fee.outstanding' => [
            'description' => 'Get total outstanding fee amount.',
            'action' => 'query',
            'category' => 'fees',
        ],
        'fee.pending_above' => [
            'description' => 'Get students with pending fees above a threshold.',
            'action' => 'query',
            'category' => 'fees',
            'param_fields' => ['amount'],
        ],
        'fee.today_collection' => [
            'description' => "Get today's fee collection total.",
            'action' => 'query',
            'category' => 'fees',
        ],
        'fee.top_defaulters' => [
            'description' => 'Get top fee defaulters list.',
            'action' => 'query',
            'category' => 'fees',
        ],
        'transport.route_occupancy' => [
            'description' => 'Get route occupancy stats.',
            'action' => 'query',
            'category' => 'transport',
        ],
        'transport.students_on_route' => [
            'description' => 'Get students per route.',
            'action' => 'query',
            'category' => 'transport',
        ],
        'transport.vehicle_assignments' => [
            'description' => 'Get vehicle assignment details.',
            'action' => 'query',
            'category' => 'transport',
        ],
        'library.books_issued' => [
            'description' => 'Get currently issued books count.',
            'action' => 'query',
            'category' => 'library',
        ],
        'library.overdue_books' => [
            'description' => 'Get overdue books count.',
            'action' => 'query',
            'category' => 'library',
        ],
        'library.fine_collection' => [
            'description' => 'Get total fine collected.',
            'action' => 'query',
            'category' => 'library',
        ],
        'payroll.latest_run' => [
            'description' => 'Get latest payroll run details.',
            'action' => 'query',
            'category' => 'payroll',
        ],
        'payroll.locked_runs' => [
            'description' => 'Get count of locked payroll runs.',
            'action' => 'query',
            'category' => 'payroll',
        ],
        'payroll.highest_salary' => [
            'description' => 'Get highest salary employees.',
            'action' => 'query',
            'category' => 'payroll',
            'param_fields' => ['limit'],
        ],
        'payroll.generated_this_month' => [
            'description' => 'Get payroll runs generated this month.',
            'action' => 'query',
            'category' => 'payroll',
        ],
        'payroll.generate' => [
            'description' => 'Generate payroll for a specific month/year.',
            'action' => 'action',
            'category' => 'payroll',
            'param_fields' => ['month', 'year'],
            'destructive' => true,
        ],
        'attendance.notify' => [
            'description' => 'Send absence notifications to parents.',
            'action' => 'action',
            'category' => 'attendance',
            'param_fields' => ['date'],
            'destructive' => true,
        ],
        'fee.send_reminders' => [
            'description' => 'Send fee reminders to defaulters.',
            'action' => 'action',
            'category' => 'fees',
            'param_fields' => ['days'],
            'destructive' => true,
        ],
        'exam.publish' => [
            'description' => 'Publish exam results.',
            'action' => 'action',
            'category' => 'exams',
            'param_fields' => ['exam_id'],
            'destructive' => true,
        ],
        'notification.send' => [
            'description' => 'Send a notification to users.',
            'action' => 'action',
            'category' => 'notifications',
            'param_fields' => ['title', 'message', 'target_type'],
            'destructive' => true,
        ],
        'homework.create' => [
            'description' => 'Create a new homework assignment.',
            'action' => 'action',
            'category' => 'homework',
            'param_fields' => ['class_section_id', 'subject_id', 'title', 'due_date'],
        ],
        'transport.assign' => [
            'description' => 'Assign transport to students.',
            'action' => 'action',
            'category' => 'transport',
            'param_fields' => ['route_id', 'student_ids'],
        ],
        'school.summary' => [
            'description' => 'Get executive school summary combining attendance, fees, transport, homework, exams, leave, and notifications.',
            'action' => 'query',
            'category' => 'reports',
        ],
    ];

    private const SYNONYM_MAP = [
        'today' => 'today',
        'current day' => 'today',
        'this day' => 'today',
        'yesterday' => 'yesterday',
        'tomorrow' => 'tomorrow',
        'this week' => 'this_week',
        'current week' => 'this_week',
        'last week' => 'last_week',
        'previous week' => 'last_week',
        'this month' => 'current_month',
        'monthly' => 'current_month',
        'current month' => 'current_month',
        'last month' => 'last_month',
        'previous month' => 'last_month',
        'this year' => 'current_year',
        'current year' => 'current_year',
        'class wise' => 'group_by_class',
        'class-wise' => 'group_by_class',
        'by class' => 'group_by_class',
        'section wise' => 'group_by_section',
        'section-wise' => 'group_by_section',
        'by section' => 'group_by_section',
        'teacher wise' => 'group_by_teacher',
        'by teacher' => 'group_by_teacher',
        'route wise' => 'group_by_route',
        'by route' => 'group_by_route',
        'department wise' => 'group_by_department',
        'by department' => 'group_by_department',
        'pending fee' => 'fee_pending_report',
        'fee due' => 'fee_pending_report',
        'outstanding fee' => 'fee_pending_report',
        'fee balance' => 'fee_pending_report',
        'fee pending' => 'fee_pending_report',
        'salary' => 'payroll',
        'employee salary' => 'payroll',
        'salaries' => 'payroll',
        'bus' => 'transport',
        'school bus' => 'transport',
        'van' => 'transport',
        'highest' => 'sort_highest',
        'lowest' => 'sort_lowest',
        'top' => 'sort_top',
        'bottom' => 'sort_bottom',
        'best' => 'sort_top',
        'worst' => 'sort_bottom',
        'present' => 'status_present',
        'absent' => 'status_absent',
        'late' => 'status_late',
        'completed' => 'status_completed',
        'running' => 'status_running',
        'scheduled' => 'status_scheduled',
        'paid' => 'status_paid',
    ];

    private const MONTH_MAP = [
        'january' => 1, 'jan' => 1,
        'february' => 2, 'feb' => 2,
        'march' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4,
        'may' => 5,
        'june' => 6, 'jun' => 6,
        'july' => 7, 'jul' => 7,
        'august' => 8, 'aug' => 8,
        'september' => 9, 'sep' => 9,
        'october' => 10, 'oct' => 10,
        'november' => 11, 'nov' => 11,
        'december' => 12, 'dec' => 12,
    ];

    public function __construct(
        private readonly IntentResolver $resolver,
        private readonly PromptBuilder $promptBuilder,
    ) {}

    public function resolve(string $question): array
    {
        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            return $this->fallback($question);
        }

        try {
            $result = $this->callGeminiHierarchical($question);
            if ($this->isValidResponse($result)) {
                $result['source'] = 'gemini';
                return $result;
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini intent resolution failed, falling back to keyword parser', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this->fallback($question);
    }

    private function callGeminiHierarchical(string $question): array
    {
        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $apiKey = config('services.gemini.api_key');
        $timeout = config('services.gemini.timeout', 30);

        $now = now();
        $dateContext = "\n\nCurrent date: {$now->format('Y-m-d')} ({$now->format('l')}). Current month: {$now->format('F')}. Current year: {$now->format('Y')}.";

        $modulePrompt = $this->promptBuilder->buildModulePrompt();
        $modulePromptTokens = $this->promptBuilder->estimateTokens($modulePrompt);

        $this->logDebug('Module prompt built', [
            'tokens_estimated' => $modulePromptTokens,
        ]);

        $module = $this->callGeminiApi(
            $model, $apiKey, $timeout,
            $modulePrompt . $dateContext . "\n\nClassify this query: " . $question
        );

        $moduleName = $module['module'] ?? 'unknown';
        $moduleConfidence = $module['confidence'] ?? 0.0;

        $this->logDebug('Module selected', [
            'module' => $moduleName,
            'confidence' => $moduleConfidence,
        ]);

        if ($moduleName === 'unknown') {
            return [
                'intent' => 'unknown',
                'parameters' => [],
                'confidence' => 0.0,
                'action' => 'unknown',
            ];
        }

        $intentPrompt = $this->promptBuilder->buildIntentPrompt($moduleName);
        $intentPromptTokens = $this->promptBuilder->estimateTokens($intentPrompt);

        $this->logDebug('Intent prompt built', [
            'module' => $moduleName,
            'tokens_estimated' => $intentPromptTokens,
        ]);

        $intentResult = $this->callGeminiApi(
            $model, $apiKey, $timeout,
            $intentPrompt . $dateContext . "\n\nClassify this query: " . $question
        );

        $intent = $intentResult['intent'] ?? 'unknown';
        $parameters = $intentResult['parameters'] ?? [];
        $action = $intentResult['action'] ?? ($intent !== 'unknown' ? 'query' : 'unknown');
        $confidence = (float) ($intentResult['confidence'] ?? 0.0);

        if ($intent !== 'unknown' && isset(self::SUPPORTED_INTENTS[$intent])) {
            $action = self::SUPPORTED_INTENTS[$intent]['action'];
        }

        $parameters = $this->normalizeParameters($parameters, $intent);

        $this->logDebug('Intent selected', [
            'module' => $moduleName,
            'intent' => $intent,
            'parameters' => $parameters,
            'confidence' => $confidence,
            'action' => $action,
            'total_tokens_estimated' => $modulePromptTokens + $intentPromptTokens,
        ]);

        return [
            'intent' => $intent,
            'parameters' => $parameters,
            'confidence' => $confidence,
            'action' => $action,
        ];
    }

    private function callGeminiApi(string $model, string $apiKey, int $timeout, string $prompt): array
    {
        $response = $this->geminiHttp()
            ->timeout($timeout)
            ->withHeader('Content-Type', 'application/json')
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => $prompt,
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'topP' => 0.95,
                    'maxOutputTokens' => 512,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            Log::error('Gemini API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Gemini API returned status ' . $response->status());
        }

        $data = $response->json();

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            throw new \RuntimeException('Empty response from Gemini');
        }

        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        $parsed = json_decode($text, true);

        if (!is_array($parsed)) {
            throw new \RuntimeException('Failed to parse Gemini response as JSON');
        }

        return $parsed;
    }

    private function normalizeParameters(array $parameters, string $intent): array
    {
        $normalized = $parameters;

        if (!isset($normalized['month']) && !empty($normalized['month_name'])) {
            $monthName = mb_strtolower(trim($normalized['month_name']));
            if (isset(self::MONTH_MAP[$monthName])) {
                $normalized['month'] = self::MONTH_MAP[$monthName];
            }
            unset($normalized['month_name']);
        }

        if (!isset($normalized['year'])) {
            $normalized['year'] = (int) now()->format('Y');
        }

        if (!empty($normalized['limit']) && is_numeric($normalized['limit'])) {
            $normalized['limit'] = (int) $normalized['limit'];
        } elseif (isset(self::SUPPORTED_INTENTS[$intent]['param_fields'])
            && in_array('limit', self::SUPPORTED_INTENTS[$intent]['param_fields'] ?? [], true)
            && !isset($normalized['limit'])
        ) {
            $normalized['limit'] = 10;
        }

        if (!empty($normalized['amount']) && is_numeric($normalized['amount'])) {
            $normalized['amount'] = (float) $normalized['amount'];
        }

        if (!empty($normalized['day_count']) && is_numeric($normalized['day_count'])) {
            $normalized['days'] = (int) $normalized['day_count'];
            unset($normalized['day_count']);
        }

        if (!empty($normalized['student_ids']) && !is_array($normalized['student_ids'])) {
            $normalized['student_ids'] = array_map('intval', array_filter(explode(',', (string) $normalized['student_ids'])));
        }

        return $normalized;
    }

    private function fallback(string $question): array
    {
        $intentKey = $this->resolver->resolveKey($question);
        $intent = $intentKey ? $this->resolver->resolve($question) : null;

        if (!$intent) {
            return [
                'intent' => 'unknown',
                'parameters' => [],
                'confidence' => 0.0,
                'source' => 'fallback',
                'action' => 'unknown',
            ];
        }

        $action = 'query';
        if (isset(self::SUPPORTED_INTENTS[$intentKey])) {
            $action = self::SUPPORTED_INTENTS[$intentKey]['action'];
        }

        return [
            'intent' => $intentKey,
            'parameters' => $this->extractFallbackParams($question, $intentKey),
            'confidence' => 0.6,
            'source' => 'fallback',
            'action' => $action,
        ];
    }

    private function extractFallbackParams(string $question, string $intentKey): array
    {
        $lower = mb_strtolower(trim($question));
        $params = [];

        if ($intentKey === 'fee.pending_above') {
            if (preg_match('/above\s+(\d+)/i', $lower, $m)) {
                $params['amount'] = (int) $m[1];
            }
        }

        if ($intentKey === 'payroll.highest_salary') {
            if (preg_match('/(\d+)\s*(employees|people|top)/i', $lower, $m)) {
                $params['limit'] = (int) $m[1];
            } else {
                $params['limit'] = 10;
            }
        }

        if ($intentKey === 'payroll.generate') {
            $params['month'] = (int) now()->format('n');
            $params['year'] = (int) now()->format('Y');
            foreach (self::MONTH_MAP as $name => $num) {
                if (str_contains($lower, $name)) {
                    $params['month'] = $num;
                    break;
                }
            }
        }

        if ($intentKey === 'fee.send_reminders') {
            if (preg_match('/(\d+)\s*days?/i', $lower, $m)) {
                $params['days'] = (int) $m[1];
            } else {
                $params['days'] = 30;
            }
        }

        if ($intentKey === 'attendance.notify') {
            if (preg_match('/yesterday/i', $lower)) {
                $params['date'] = now()->subDay()->format('Y-m-d');
            } else {
                $params['date'] = now()->format('Y-m-d');
            }
        }

        if ($intentKey === 'student.by_class') {
            if (preg_match('/class\s*wise|by\s*class/i', $lower)) {
                $params['group_by'] = 'class';
            }
            if (preg_match('/section\s*wise|by\s*section/i', $lower)) {
                $params['group_by'] = 'section';
            }
        }

        if ($intentKey === 'transport.route_occupancy' || $intentKey === 'transport.students_on_route') {
            if (preg_match('/route\s*wise|by\s*route/i', $lower)) {
                $params['group_by'] = 'route';
            }
        }

        return $params;
    }

    private function isValidResponse(array $result): bool
    {
        if (!isset($result['intent'], $result['parameters'], $result['confidence'])) {
            return false;
        }

        if ($result['intent'] === 'unknown') {
            return true;
        }

        if (!array_key_exists($result['intent'], self::SUPPORTED_INTENTS)) {
            return false;
        }

        return $result['confidence'] >= 0.0 && $result['confidence'] <= 1.0;
    }

    public static function getSupportedIntents(): array
    {
        return self::SUPPORTED_INTENTS;
    }

    public static function getSynonymMap(): array
    {
        return self::SYNONYM_MAP;
    }

    private function geminiHttp()
    {
        return Http::withOptions([
            'verify' => base_path('certificates/cacert.pem'),
        ])
        ->acceptJson();
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (!app()->environment('local', 'development')) {
            return;
        }

        Log::channel('daily')->debug("[AI Intent] {$message}", $context);
    }
}
