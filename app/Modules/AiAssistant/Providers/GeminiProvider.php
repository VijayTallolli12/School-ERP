<?php

namespace App\Modules\AiAssistant\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AiProvider
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function providerName(): string
    {
        return 'gemini';
    }

    public function modelName(): string
    {
        return (string) config('services.gemini.model', 'gemini-2.5-flash');
    }

    public function understand(string $question, array $toolCatalog, array $context = []): array
    {
        $toolList = array_map(
            fn (string $name, array $cfg) => [
                'tool' => $name,
                'module' => $cfg['module'] ?? '',
                'description' => $cfg['description'] ?? '',
                'params' => $cfg['params'] ?? [],
            ],
            array_keys($toolCatalog),
            array_values($toolCatalog)
        );

        $prompt = <<<PROMPT
You are the query planner for a School ERP assistant.

The user asks a question in natural language. Choose ONE tool from the catalog below and
extract the relevant parameters.

TOOLS:
{$this->json($toolList)}

TODAY:
{$context['today']}

RULES:
- Respond with JSON only: {"tool":"tool_name","parameters":{...},"confidence":0.9}
- Use the exact tool name from the catalog.
- For exam questions, if a type like "mid term"/"half yearly" appears, add parameters["exam_type"] as the canonical values (mid_term / half_yearly).
- For date references (e.g. "Jan 2026", "this month", "today"), compute the concrete dates and add parameters["date_from"] and parameters["date_to"] (YYYY-MM-DD).
- Include class/subject only if mentioned.
- If no tool matches, return {"tool":"unknown","parameters":{},"confidence":0}.
PROMPT;

        $response = $this->callJson($prompt);

        if (!isset($response['tool'])) {
            throw new \RuntimeException('Gemini did not return a tool.');
        }

        return [
            'intent' => (string) $response['tool'],
            'parameters' => is_array($response['parameters'] ?? null) ? $response['parameters'] : [],
            'confidence' => (float) ($response['confidence'] ?? 0.0),
            'action' => isset($toolCatalog[$response['tool']]['result_type']) ? 'query' : 'action',
        ];
    }

    public function generate(string $systemPrompt, string $dataJson): string
    {
        $prompt = $systemPrompt . "\n\nACTUAL DATA (from the ERP database):\n" . $dataJson;

        return $this->callText($prompt);
    }

    private function callJson(string $prompt): array
    {
        $text = $this->callText($prompt, 'application/json');
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        $parsed = json_decode($text, true);

        if (!is_array($parsed)) {
            throw new \RuntimeException('Gemini returned invalid JSON.');
        }

        return $parsed;
    }

    private function callText(string $prompt, string $mimeType = 'text/plain'): string
    {
        $model = $this->modelName();
        $apiKey = (string) config('services.gemini.api_key');
        $timeout = (int) config('services.gemini.timeout', 30);

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $prompt]],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topP' => 0.95,
                'maxOutputTokens' => 768,
                'responseMimeType' => $mimeType,
            ],
        ];

        $response = Http::withOptions(['verify' => base_path('certificates/cacert.pem')])
            ->acceptJson()
            ->timeout($timeout)
            ->post("{$this->apiUrl()}/{$model}:generateContent?key={$apiKey}", $payload);

        if ($response->failed()) {
            Log::error('Gemini API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Gemini API returned status ' . $response->status());
        }

        $text = $response->json('candidates.0.content.parts.0.text', null);

        if (!$text) {
            throw new \RuntimeException('Empty response from Gemini');
        }

        return $text;
    }

    private function apiUrl(): string
    {
        return self::API_URL;
    }

    private function json(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]';
    }
}
