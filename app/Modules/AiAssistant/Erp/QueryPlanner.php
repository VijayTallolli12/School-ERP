<?php

namespace App\Modules\AiAssistant\Erp;

use App\Modules\AiAssistant\Providers\AiProvider;
use App\Modules\AiAssistant\Providers\AiProviderFactory;
use Illuminate\Support\Facades\Log;

/**
 * Converts a natural-language question into a structured ERP query:
 *
 *   {
 *     "tool": "exam.search",
 *     "parameters": { "exam_type": [...], "date_from": "...", "date_to": "..." },
 *     "confidence": 0.9,
 *     "action": "query"
 *   }
 *
 * Uses the configured LLM provider when available; otherwise falls back to a
 * deterministic keyword + date + synonym planner so the feature never breaks
 * when the provider is unavailable.
 */
class QueryPlanner
{
    /**
     * Ordered action detection patterns. Most specific actions are checked
     * FIRST so that e.g. "send absence notification to parents" matches
     * attendance.notify, not the generic notification.send.
     *
     * Patterns tolerate articles ("send a notification"), word order and
     * intervening words. First-person recipients ("send ME the list") are
     * guarded out in detectAction() so they remain read/query requests.
     */
    private const ACTION_PATTERNS = [
        'payroll.generate' => '/\b(generate|run|process|create)\b.{0,40}\b(monthly\s+)?(payroll|salary)\b/i',
        'attendance.notify' => '/\b(send|notify|inform)\b.{0,40}\b(absent|absence|attendance)\b.{0,40}\b(parents?|guardians?)\b/i',
        'fee.send_reminders' => '/\b(send|remind)\b.{0,40}\b(reminders?|defaulters?)\b/i',
        'exam.publish' => '/\b(publish|release)\b.{0,40}\b(exam|result|results|marks|report\s*card)\b/i',
        'homework.create' => '/\b(create|add|assign|give|set)\b.{0,40}\b(homework|assignment|task)\b/i',
        'transport.assign' => '/\b(assign|allocate|add|put)\b.{0,40}\b(route|bus|transport|bus\s+route)\b/i',
        'notification.send' => '/\b(send|notify|inform|announce|broadcast|push)\b.{0,40}\b(notification|announcement|message|notice|circular|students?|parents?|teachers?|staff)\b/i',
    ];

    /**
     * Exact-phrase action keywords kept for backward compatibility with the
     * original matching (checked after the regex patterns).
     */
    private const ACTION_KEYWORDS = [
        'payroll.generate' => ['generate payroll', 'run payroll', 'process payroll'],
        'attendance.notify' => ['send absence notification', 'notify parents of absent', 'send attendance notification', 'inform parents about absent'],
        'fee.send_reminders' => ['send fee reminder', 'send reminders to defaulters', 'remind defaulters'],
        'exam.publish' => ['publish exam result', 'publish results'],
        'notification.send' => ['send notification', 'send announcement', 'broadcast'],
        'homework.create' => ['create homework', 'add homework', 'assign homework'],
        'transport.assign' => ['assign transport', 'assign bus', 'assign route'],
    ];

    /**
     * First-person recipient markers indicate a personal/display request
     * ("send me the list", "notify me when...") — a query, not a broadcast.
     */
    private const PERSONAL_REQUEST_PATTERN = '/\b(send|notify|inform|show|tell|give|get|display|list|fetch)\s+(me|us)\b/i';

    public function __construct(
        private readonly ErpToolRegistry $registry,
        private readonly AiProviderFactory $providerFactory,
    ) {}

    /**
     * @return array{intent: string, parameters: array, confidence: float, action: string, source: string}
     */
    public function plan(string $question): array
    {
        $trimmed = trim($question);

        if ($trimmed === '') {
            return [
                'intent' => 'unknown',
                'parameters' => [],
                'confidence' => 0.0,
                'action' => 'unknown',
                'source' => 'empty',
            ];
        }

        // 1. Deterministic action detection first (these must never be run via LLM freely).
        if ($action = $this->detectAction($trimmed)) {
            return $action;
        }

        // 2. Provider-based planning.
        if ($this->providerFactory->isConfigured()) {
            try {
                $provider = $this->providerFactory->make();
                $result = $provider->understand(
                    $trimmed,
                    $this->combinedCatalog(),
                    ['today' => now()->format('Y-m-d')]
                );

                if ($this->isUsable($result)) {
                    $result['source'] = $provider->providerName();
                    return $this->normalize($result, $trimmed);
                }
            } catch (\Throwable $e) {
                Log::warning('AI provider planning failed, using fallback planner', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 3. Deterministic fallback planner.
        return $this->planByRules($trimmed);
    }

    /**
     * The full tool catalog the LLM may choose from: query tools AND action
     * tools. Action tools carry no result_type, so `normalize()` marks them
     * as actions (confirmation required). Without this, the provider can
     * never select an action tool and every action request degrades into a
     * query.
     */
    private function combinedCatalog(): array
    {
        return array_merge($this->registry->all(), $this->registry->actionTools());
    }

    private function detectAction(string $question): ?array
    {
        $lower = mb_strtolower($question);

        // "send me / show me / notify me" are personal requests for data, not
        // broadcasts. Also skip clear question markers so reads stay queries.
        if (preg_match(self::PERSONAL_REQUEST_PATTERN, $lower)) {
            return null;
        }

        // Explicit read/query markers that should never be treated as actions
        // ("how many", "show the list", "which students", "?").
        if (preg_match('/\b(how many|how much|which|list of|show me the list|show the list|tell me about)\b/', $lower) || str_ends_with($lower, '?')) {
            return null;
        }

        // 1. Regex patterns (most specific first).
        foreach (self::ACTION_PATTERNS as $intent => $pattern) {
            if (preg_match($pattern, $lower)) {
                return [
                    'intent' => $intent,
                    'parameters' => $this->extractActionParams($intent, $question),
                    'confidence' => 0.9,
                    'action' => 'action',
                    'source' => 'rules',
                ];
            }
        }

        // 2. Backward-compatible exact-phrase keywords.
        foreach (self::ACTION_KEYWORDS as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return [
                        'intent' => $intent,
                        'parameters' => $this->extractActionParams($intent, $question),
                        'confidence' => 0.9,
                        'action' => 'action',
                        'source' => 'rules',
                    ];
                }
            }
        }

        return null;
    }

    private function extractActionParams(string $intent, string $question): array
    {
        $lower = mb_strtolower($question);
        $params = [];

        if ($intent === 'payroll.generate') {
            $month = null;
            foreach (self::MONTHS as $name => $num) {
                if (str_contains($lower, $name)) {
                    $month = $num;
                    break;
                }
            }
            $params['month'] = $month ?? (int) now()->format('n');
            $params['year'] = (int) now()->format('Y');
        }

        if ($intent === 'attendance.notify') {
            $params['date'] = str_contains($lower, 'yesterday')
                ? now()->subDay()->format('Y-m-d')
                : now()->format('Y-m-d');
        }

        if ($intent === 'fee.send_reminders') {
            $params['days'] = 30;
            if (preg_match('/(\d+)\s*days?/', $lower, $m)) {
                $params['days'] = (int) $m[1];
            }
        }

        if ($intent === 'notification.send') {
            $params = $this->extractNotificationParams($question);
        }

        if ($intent === 'homework.create') {
            $params = $this->extractHomeworkParams($question);
        }

        if ($intent === 'transport.assign') {
            $params = $this->extractTransportAssignParams($question);
        }

        if ($intent === 'exam.publish') {
            $params = $this->extractExamPublishParams($question);
        }

        return $params;
    }

    /**
     * Extract notification fields from a natural-language "send a
     * notification..." request. target_type is normalized to one of the ERP
     * Notification target types; title/message are derived from the request.
     */
    private function extractNotificationParams(string $question): array
    {
        $lower = mb_strtolower($question);
        $params = [];

        // Target audience.
        $target = 'all';
        if (preg_match('/\bstudents?\b/', $lower)) {
            $target = 'students';
        } elseif (preg_match('/\bparents?\b/', $lower)) {
            $target = 'parents';
        } elseif (preg_match('/\bteachers?\b/', $lower)) {
            $target = 'teachers';
        } elseif (preg_match('/\bstaff\b/', $lower)) {
            $target = 'staff';
        }
        $params['target_type'] = $target;

        // Message content: everything after the notification verb phrase,
        // e.g. "they should come tomorrow in colour dress because...".
        $message = $question;
        if (preg_match('/\b(notification|announcement|notice|message|circular)\b[^.!?]*?\b(that|to|about|regarding)\b\s*(.+)$/i', $question, $m)) {
            $message = trim($m[3]);
        }
        if ($message === '') {
            $message = $question;
        }
        // Drop a leading "all students / the students / students" target prefix
        // so the message reads as the announcement content.
        $message = preg_replace('/^(?:all\s+)?(?:the\s+)?(students?|parents?|teachers?|staff)\s+(?:that|who|should|to)\s+/i', '', $message) ?? $message;
        $params['message'] = $message;

        // Title: a short subject. Prefer an explicit "about X" clause, else a
        // compact first part of the message.
        $title = null;
        if (preg_match('/\babout\s+(?:the\s+|our\s+)?([A-Z][A-Za-z\s]{2,30})/i', $question, $m)) {
            $title = trim($m[1]);
        }
        if (! $title && preg_match('/\b(?:titled|subject:|heading)\s+["\']?([^"\']{3,60})/i', $question, $m)) {
            $title = trim($m[1]);
        }
        if (! $title) {
            $title = mb_substr($message, 0, 40);
        }
        $params['title'] = $title ?: 'School Announcement';

        return $params;
    }

    /**
     * Extract homework creation fields: class/section target + title.
     */
    private function extractHomeworkParams(string $question): array
    {
        $params = [];

        if (preg_match('/\b(class|grade)\s*(\d{1,2})\b/i', $question, $m)) {
            $params['class'] = 'Class '.$m[2];
        }

        if (preg_match('/class\s+(\d{1,2})\s*[-\s]\s*(section\s*)?([a-z])\b/i', $question, $m)) {
            $params['class'] = 'Class '.$m[1];
            $params['section'] = 'Section '.mb_strtoupper($m[3]);
        }

        if (preg_match('/\b(subject)\s*[:]?\s*([A-Za-z][A-Za-z\s]{2,30})/i', $question, $m)) {
            $params['subject'] = trim($m[2]);
        }

        return $params;
    }

    /**
     * Extract transport assignment fields: route + student identifiers.
     */
    private function extractTransportAssignParams(string $question): array
    {
        $params = [];

        if (preg_match('/\b(?:route|bus)\s*(?:no\.?|number)?\s*[:#]?\s*(\d{1,3})\b/i', $question, $m)) {
            $params['route'] = $m[1];
        }

        if (preg_match('/\b(?:student|child)\b[^,;.!?]{0,40}?\b(?:id|no\.?|number)?\s*[:#]?\s*([A-Za-z0-9-]{2,20})\b/i', $question, $m)) {
            $params['student'] = $m[1];
        }

        return $params;
    }

    /**
     * Extract the exam being published (subject / type / date).
     */
    private function extractExamPublishParams(string $question): array
    {
        $params = [];

        if (preg_match('/\b(subject)\s*[:]?\s*([A-Za-z][A-Za-z\s]{2,30})/i', $question, $m)) {
            $params['subject'] = trim($m[2]);
        }

        $types = $this->registry->extractExamTypesFromText($question);
        if (! empty($types)) {
            $params['exam_type'] = $types;
        }

        return $params;
    }

    private function isUsable(array $result): bool
    {
        if (!isset($result['intent'], $result['parameters'], $result['confidence'])) {
            return false;
        }

        $intent = (string) $result['intent'];

        if ($intent === 'unknown') {
            return false;
        }

        return $this->registry->has($intent);
    }

    private function normalize(array $result, string $question): array
    {
        $intent = (string) $result['intent'];
        $parameters = is_array($result['parameters'] ?? null) ? $result['parameters'] : [];
        $confidence = (float) ($result['confidence'] ?? 0.0);

        if (!$this->registry->has($intent)) {
            return $this->planByRules($question);
        }

        $config = $this->registry->get($intent);

        // Whitelist parameters to the tool's schema.
        $allowed = $config['params'] ?? [];
        $parameters = array_intersect_key($parameters, array_flip($allowed));

        // Normalize exam types from free-text if the provider did not.
        if (in_array('exam_type', $allowed, true) && isset($parameters['exam_type'])) {
            $raw = is_array($parameters['exam_type']) ? $parameters['exam_type'] : [$parameters['exam_type']];
            $normalized = $this->registry->normalizeExamTypes($raw);
            if (empty($normalized)) {
                $normalized = $this->registry->extractExamTypesFromText($question);
            }
            if (!empty($normalized)) {
                $parameters['exam_type'] = $normalized;
            } else {
                unset($parameters['exam_type']);
            }
        }

        return [
            'intent' => $intent,
            'parameters' => $parameters,
            'confidence' => max(0.0, min(1.0, $confidence)),
            'action' => isset($config['result_type']) ? 'query' : 'action',
            'source' => $result['source'] ?? 'provider',
        ];
    }

    private function planByRules(string $question): array
    {
        $tool = $this->registry->matchByKeywords($question);

        if (!$tool) {
            return [
                'intent' => 'unknown',
                'parameters' => [],
                'confidence' => 0.0,
                'action' => 'unknown',
                'source' => 'rules',
            ];
        }

        $config = $this->registry->get($tool);
        $parameters = $this->extractRuleParams($tool, $question);
        $parameters = $this->applyDateFilters($tool, $question, $parameters);
        $parameters = $this->applyExamTypeFilters($tool, $question, $parameters);
        $parameters = $this->applyClassFilter($tool, $question, $parameters);
        $parameters = $this->applyStatusFilter($tool, $question, $parameters);

        return [
            'intent' => $tool,
            'parameters' => $parameters,
            'confidence' => 0.7,
            'action' => isset($config['result_type']) ? 'query' : 'action',
            'source' => 'rules',
        ];
    }

    private function extractRuleParams(string $tool, string $question): array
    {
        $lower = mb_strtolower($question);
        $params = [];

        if ($tool === 'fee.pending_above' && preg_match('/above\s+(\d+)/i', $lower, $m)) {
            $params['amount'] = (float) $m[1];
        }

        if ($tool === 'payroll.highest_salary' || $tool === 'fee.top_defaulters') {
            if (preg_match('/(\d+)\s*(employees|people|top|students|defaulters|students)/i', $lower, $m)) {
                $params['limit'] = (int) $m[1];
            }
        }

        return $params;
    }

    private function applyDateFilters(string $tool, string $question, array $params): array
    {
        $config = $this->registry->get($tool);
        $allowed = $config['params'] ?? [];

        $hasDateCapability = array_intersect($allowed, ['date_from', 'date_to', 'date']);
        if (empty($hasDateCapability)) {
            return $params;
        }

        // Full date range extraction from the question.
        $dateParser = $this->registry->dateParser();
        $range = $dateParser->parse($question);

        if ($range) {
            $params['date_from'] = $range['date_from'];
            $params['date_to'] = $range['date_to'];
        } elseif (in_array('date', $allowed, true) && preg_match('/\b(today|yesterday|tomorrow)\b/i', $question)) {
            $params['date'] = now()->format('Y-m-d');
        }

        return $params;
    }

    private function applyExamTypeFilters(string $tool, string $question, array $params): array
    {
        $config = $this->registry->get($tool);

        if (!in_array('exam_type', $config['params'] ?? [], true)) {
            return $params;
        }

        $types = $this->registry->extractExamTypesFromText($question);

        if (!empty($types)) {
            $params['exam_type'] = $types;
        }

        return $params;
    }

    private function applyClassFilter(string $tool, string $question, array $params): array
    {
        $config = $this->registry->get($tool);

        if (!in_array('class_section_id', $config['params'] ?? [], true)) {
            return $params;
        }

        if (preg_match('/\b(class|grade)\s*(\d{1,2})\b/i', $question, $m)) {
            $params['class'] = 'Class ' . $m[2];
        }

        if (preg_match('/class\s+(\d{1,2})\s*[-\s]\s*(section\s*)?([a-z])\b/i', $question, $m)) {
            $params['class'] = 'Class ' . $m[1];
            $params['section'] = 'Section ' . mb_strtoupper($m[3]);
        }

        return $params;
    }

    private function applyStatusFilter(string $tool, string $question, array $params): array
    {
        $config = $this->registry->get($tool);

        if (!in_array('status', $config['params'] ?? [], true)) {
            return $params;
        }

        $lower = mb_strtolower($question);

        // "was scheduled on" / "scheduled for" / "scheduled on" / "scheduled in <month>"
        // / "scheduled to be held" are about WHEN something happens, not the
        // current exam status.
        if (preg_match('/\bwas\s+scheduled\b|\bscheduled\s+for\b|\bscheduled\s+on\b|\bscheduled\s+in\b|\bscheduled\s+to\b|\bto\s+be\s+held\b/', $lower)) {
            return $params;
        }

        foreach (ErpToolRegistry::STATUS_ALIASES as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($lower, $alias)) {
                    $params['status'] = $canonical;
                    break 2;
                }
            }
        }

        return $params;
    }

    private const MONTHS = [
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
}
