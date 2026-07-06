<?php

namespace App\Modules\AiAssistant\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIResponseFormatter
{
    private const INTENT_TITLES = [
        'student.total' => 'Student Enrollment Summary',
        'student.admitted_this_month' => 'Monthly Admission Report',
        'student.by_class' => 'Class-wise Student Distribution',
        'attendance.absent_today' => 'Daily Attendance Report',
        'attendance.monthly_percentage' => 'Monthly Attendance Analysis',
        'attendance.below_75' => 'Low Attendance Alert',
        'fee.outstanding' => 'Fee Collection Summary',
        'fee.pending_above' => 'Fee Defaulters Report',
        'fee.today_collection' => 'Daily Collection Report',
        'fee.top_defaulters' => 'Top Fee Defaulters',
        'transport.route_occupancy' => 'Transport Utilization Report',
        'transport.students_on_route' => 'Route-wise Student Distribution',
        'transport.vehicle_assignments' => 'Vehicle Assignment Report',
        'library.books_issued' => 'Library Issue Summary',
        'library.overdue_books' => 'Overdue Books Report',
        'library.fine_collection' => 'Library Fine Collection',
        'payroll.latest_run' => 'Latest Payroll Summary',
        'payroll.locked_runs' => 'Payroll Status Report',
        'payroll.highest_salary' => 'Salary Distribution Report',
        'payroll.generated_this_month' => 'Monthly Payroll Status',
    ];

    public function format(string $intent, string $rawAnswer, array $data = []): string
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return $this->formatLocal($intent, $rawAnswer, $data);
        }

        try {
            return $this->callGemini($intent, $rawAnswer, $data);
        } catch (\Throwable $e) {
            Log::warning('AIResponseFormatter failed, using local formatting', [
                'error' => $e->getMessage(),
            ]);
            return $this->formatLocal($intent, $rawAnswer, $data);
        }
    }

    public function formatSchoolSummary(array $summaryData): string
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return $this->buildSchoolSummaryLocal($summaryData);
        }

        try {
            return $this->callGeminiSchoolSummary($summaryData);
        } catch (\Throwable $e) {
            Log::warning('AIResponseFormatter school summary failed, using local', [
                'error' => $e->getMessage(),
            ]);
            return $this->buildSchoolSummaryLocal($summaryData);
        }
    }

    public function formatActionResult(string $intent, array $result, array $params = []): string
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return $this->buildActionResultLocal($intent, $result, $params);
        }

        try {
            return $this->callGeminiActionResult($intent, $result, $params);
        } catch (\Throwable $e) {
            Log::warning('AIResponseFormatter action result failed, using local', [
                'error' => $e->getMessage(),
            ]);
            return $this->buildActionResultLocal($intent, $result, $params);
        }
    }

    public function formatExecutiveReport(array $orchestratorOutput): string
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return $this->buildExecutiveReportLocal($orchestratorOutput);
        }

        try {
            return $this->callGeminiExecutiveReport($orchestratorOutput);
        } catch (\Throwable $e) {
            Log::warning('AIResponseFormatter executive report failed, using local', [
                'error' => $e->getMessage(),
            ]);
            return $this->buildExecutiveReportLocal($orchestratorOutput);
        }
    }

    private function callGemini(string $intent, string $rawAnswer, array $data): string
    {
        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $apiKey = config('services.gemini.api_key');
        $timeout = config('services.gemini.timeout', 30);

        $title = self::INTENT_TITLES[$intent] ?? 'ERP Report';
        $dataJson = !empty($data) ? json_encode($data, JSON_PRETTY_PRINT) : 'none';

        $prompt = <<<PROMPT
You are an executive business report writer for a School ERP system.

Generate a professional executive summary for this report.

REPORT TITLE: {$title}
INTENT: {$intent}

RAW DATA:
{$dataJson}

RAW ANSWER:
{$rawAnswer}

FORMATTING RULES:
- Start with the report title as a heading
- Extract and highlight key metrics with labels
- Use bullet points for multiple data items
- Include a brief business insight (1-2 sentences)
- Include a recommendation (1 sentence)
- NEVER fabricate numbers or data not present in the raw data
- NEVER mention AI, language model, or that this was auto-generated
- Use Indian Rupee symbol (\u{20B9}) for monetary values
- Keep total length under 200 words
- Use professional, concise business language
- Do not use markdown formatting - use plain text with line breaks

Example format:
REPORT TITLE

Metric Name: Value
Metric Name: Value

Business Insight: [observation about the data]
Recommendation: [actionable suggestion]
PROMPT;

        $response = $this->geminiHttp()
            ->timeout($timeout)
            ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'topP' => 0.95,
                    'maxOutputTokens' => 512,
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini API returned status ' . $response->status());
        }

        $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            throw new \RuntimeException('Empty response from Gemini');
        }

        return trim($text);
    }

    private function callGeminiSchoolSummary(array $summaryData): string
    {
        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $apiKey = config('services.gemini.api_key');
        $timeout = config('services.gemini.timeout', 30);

        $dataJson = json_encode($summaryData, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
You are an executive assistant generating a daily school briefing for the principal.

Generate a concise, executive-level school summary.

SCHOOL DATA:
{$dataJson}

FORMATTING RULES:
- Start with "Daily School Briefing" as the title
- Use section headings for each module
- Highlight key metrics with clear labels
- Keep each section to 1-2 lines max
- Include overall school health assessment
- Include priority actions needed
- NEVER fabricate any data
- NEVER mention AI or automation
- Use Indian Rupee symbol (\u{20B9}) for monetary values
- Total length: under 300 words
- Use plain text with line breaks, no markdown

Example sections:
DAILY SCHOOL BRIEFING

Attendance
- Present: X | Absent: X | Rate: X%

Fees
- Pending: \u{20B9}X | Collected Today: \u{20B9}X

[continue for each module]

Overall Status: [Good/Needs Attention/Critical]
Priority Actions: [2-3 items]
PROMPT;

        $response = $this->geminiHttp()
            ->timeout($timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'topP' => 0.95,
                    'maxOutputTokens' => 768,
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini API returned status ' . $response->status());
        }

        $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            throw new \RuntimeException('Empty response from Gemini');
        }

        return trim($text);
    }

    private function callGeminiActionResult(string $intent, array $result, array $params): string
    {
        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $apiKey = config('services.gemini.api_key');
        $timeout = config('services.gemini.timeout', 30);

        $resultJson = json_encode($result, JSON_PRETTY_PRINT);
        $paramsJson = json_encode($params, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
You are an executive report writer for a School ERP system.

Generate a professional summary of an executed action.

INTENT: {$intent}
PARAMETERS: {$paramsJson}
RESULT: {$resultJson}

FORMATTING RULES:
- Start with a clear action summary heading
- Highlight the outcome (success/failure)
- List key metrics from the result
- Include records processed and totals
- NEVER fabricate data
- NEVER mention AI
- Use Indian Rupee symbol (\u{20B9}) for monetary values
- Keep under 150 words
- Use plain text with line breaks

Example format:
ACTION COMPLETE

Status: Success
Records Processed: X
Key Metric: Value
Total: \u{20B9}X
PROMPT;

        $response = $this->geminiHttp()
            ->timeout($timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'topP' => 0.95,
                    'maxOutputTokens' => 384,
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini API returned status ' . $response->status());
        }

        $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            throw new \RuntimeException('Empty response from Gemini');
        }

        return trim($text);
    }

    private function formatLocal(string $intent, string $rawAnswer, array $data): string
    {
        $title = self::INTENT_TITLES[$intent] ?? 'ERP Report';

        $lines = [$title, ''];
        $answerLines = explode("\n", $rawAnswer);

        foreach ($answerLines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        $lines[] = '';
        $lines[] = 'Recommendation: Review the above data and take appropriate action.';

        return implode("\n", $lines);
    }

    private function buildSchoolSummaryLocal(array $data): string
    {
        $lines = ['Daily School Briefing', ''];

        if (!empty($data['attendance'])) {
            $a = $data['attendance'];
            $lines[] = 'Attendance';
            $lines[] = "  Present: {$a['present']} | Absent: {$a['absent']} | Rate: {$a['percentage']}%";
            $lines[] = '';
        }

        if (!empty($data['fees'])) {
            $f = $data['fees'];
            $lines[] = 'Fees';
            $lines[] = "  Pending: \u{20B9}" . number_format($f['total_pending'], 2);
            $lines[] = "  Collected Today: \u{20B9}" . number_format($f['collected_today'], 2);
            $lines[] = "  Collection Rate: {$f['collection_rate']}%";
            $lines[] = '';
        }

        if (!empty($data['transport'])) {
            $t = $data['transport'];
            $lines[] = 'Transport';
            $lines[] = "  Routes: {$t['total_routes']} | Students: {$t['total_students']} | Utilization: {$t['utilization']}%";
            $lines[] = '';
        }

        if (!empty($data['homework'])) {
            $h = $data['homework'];
            $lines[] = 'Homework';
            $lines[] = "  Assigned Today: {$h['assigned_today']} | Due Today: {$h['due_today']} | Overdue: {$h['overdue']}";
            $lines[] = '';
        }

        if (!empty($data['exams'])) {
            $e = $data['exams'];
            $lines[] = 'Exams';
            $lines[] = "  Published: {$e['published']} | Unpublished: {$e['unpublished']}";
            $lines[] = '';
        }

        if (!empty($data['leave'])) {
            $l = $data['leave'];
            $lines[] = 'Leave';
            $lines[] = "  Pending Requests: {$l['pending_requests']} | Approved Today: {$l['approved_today']}";
            $lines[] = '';
        }

        if (!empty($data['notifications'])) {
            $n = $data['notifications'];
            $lines[] = 'Notifications';
            $lines[] = "  Sent Today: {$n['sent_today']} | Drafts: {$n['unsent_drafts']}";
            $lines[] = '';
        }

        if (!empty($data['library'])) {
            $lib = $data['library'];
            $lines[] = 'Library';
            $lines[] = "  Books Issued: {$lib['books_issued']} | Overdue: {$lib['overdue_books']}";
            $lines[] = '';
        }

        $lines[] = 'Recommendation: Review sections requiring attention and take follow-up actions.';

        return implode("\n", $lines);
    }

    private function buildActionResultLocal(string $intent, array $result, array $params): string
    {
        $lines = ['Action Complete', ''];

        $lines[] = 'Status: ' . (($result['success'] ?? false) ? 'Success' : 'Failed');
        $lines[] = '';

        if (isset($result['records_processed'])) {
            $lines[] = "Records Processed: {$result['records_processed']}";
        }

        if (isset($result['total_employees'])) {
            $lines[] = "Total Employees: {$result['total_employees']}";
        }

        if (isset($result['total_gross'])) {
            $lines[] = "Total Gross: \u{20B9}" . number_format($result['total_gross'], 2);
        }

        if (isset($result['total_net'])) {
            $lines[] = "Total Net: \u{20B9}" . number_format($result['total_net'], 2);
        }

        if (isset($result['payslips_generated'])) {
            $lines[] = "Payslips Generated: {$result['payslips_generated']}";
        }

        if (isset($result['notifications_created'])) {
            $lines[] = "Notifications Sent: {$result['notifications_created']}";
        }

        if (isset($result['payroll_run_id'])) {
            $lines[] = "Payroll Run ID: {$result['payroll_run_id']}";
        }

        return implode("\n", $lines);
    }

    private function callGeminiExecutiveReport(array $orchestratorOutput): string
    {
        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $apiKey = config('services.gemini.api_key');
        $timeout = config('services.gemini.timeout', 30);

        $intent = $orchestratorOutput['intent'] ?? 'school.summary';
        $description = $orchestratorOutput['description'] ?? 'Executive summary';
        $sections = $orchestratorOutput['sections'] ?? [];
        $stats = $orchestratorOutput['stats'] ?? [];
        $insights = $orchestratorOutput['insights'] ?? null;

        $sectionsJson = json_encode($sections, JSON_PRETTY_PRINT);
        $statsJson = json_encode($stats, JSON_PRETTY_PRINT);
        $insightsJson = $insights ? json_encode($insights, JSON_PRETTY_PRINT) : 'none';

        $prompt = <<<PROMPT
You are an executive AI assistant generating a school operations briefing.

Generate a concise, actionable executive summary from the following multi-module data.

INTENT: {$intent}
DESCRIPTION: {$description}
MODULE SECTIONS: {$sectionsJson}
STATS: {$statsJson}
INSIGHTS: {$insightsJson}

FORMATTING RULES:
- Start with a greeting and school health overview
- For each module section, show a brief status line with key metric
- If status is 'unavailable', note it briefly
- Include the health score from insights if available
- List top 2-3 alerts or anomalies if present
- List top 2-3 recommendations if present
- NEVER fabricate any data
- NEVER mention AI, language model, or automation
- Use Indian Rupee symbol (\u{20B9}) for monetary values
- Total length: under 400 words
- Use plain text with line breaks, no markdown

Example format:
SCHOOL OPERATIONS BRIEFING

Health Score: 92/100 (Excellent)

MODULES:
Attendance: 432 present (96%)
Fees: \u{20B9}2,45,000 outstanding
Transport: 12 routes active

KEY ALERTS:
- [alert 1]
- [alert 2]

RECOMMENDATIONS:
- [recommendation 1]
- [recommendation 2]
PROMPT;

        $response = $this->geminiHttp()
            ->timeout($timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'topP' => 0.95,
                    'maxOutputTokens' => 768,
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini API returned status ' . $response->status());
        }

        $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            throw new \RuntimeException('Empty response from Gemini');
        }

        return trim($text);
    }

    private function buildExecutiveReportLocal(array $orchestratorOutput): string
    {
        $sections = $orchestratorOutput['sections'] ?? [];
        $stats = $orchestratorOutput['stats'] ?? [];
        $insights = $orchestratorOutput['insights'] ?? null;
        $description = $orchestratorOutput['description'] ?? 'Executive Summary';

        $lines = ['SCHOOL OPERATIONS BRIEFING', ''];

        if ($insights && isset($insights['health_score'])) {
            $hs = $insights['health_score'];
            $lines[] = "Health Score: {$hs['overall']}/100 ({$hs['rating']})";
            $lines[] = '';
        }

        $lines[] = 'MODULES:';
        foreach ($sections as $module => $section) {
            $label = $section['label'] ?? $module;
            $status = $section['status'] ?? 'unknown';
            if ($status === 'ok') {
                $data = $section['data'];
                $dataStr = is_array($data) ? $this->summarizeData($data) : (string) $data;
                $lines[] = "{$label}: {$dataStr}";
            } else {
                $error = $section['error'] ?? 'Unavailable';
                $lines[] = "{$label}: Unavailable ({$error})";
            }
        }

        if ($insights && !empty($insights['alerts'])) {
            $lines[] = '';
            $lines[] = 'KEY ALERTS:';
            foreach (array_slice($insights['alerts'], 0, 3) as $alert) {
                $lines[] = "- {$alert['message']}";
            }
        }

        if ($insights && !empty($insights['recommendations'])) {
            $lines[] = '';
            $lines[] = 'RECOMMENDATIONS:';
            foreach (array_slice($insights['recommendations'], 0, 3) as $rec) {
                $lines[] = "- {$rec['action']}";
            }
        }

        $lines[] = '';
        $lines[] = "Modules: {$stats['successful']}/{$stats['total']} successful";

        return implode("\n", $lines);
    }

    private function summarizeData($data): string
    {
        if (is_array($data)) {
            $parts = [];
            foreach (array_slice($data, 0, 3) as $key => $value) {
                $label = str_replace('_', ' ', ucfirst($key));
                $parts[] = "{$label}: {$value}";
            }
            return implode(' | ', $parts);
        }
        return (string) $data;
    }

    private function geminiHttp()
    {
        return Http::withOptions([
            'verify' => base_path('certificates/cacert.pem'),
        ])
        ->acceptJson();
    }
}
