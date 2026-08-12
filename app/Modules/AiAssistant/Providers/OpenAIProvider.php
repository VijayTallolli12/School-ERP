<?php

namespace App\Modules\AiAssistant\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AiProvider
{
    private const API_URL = 'https://api.openai.com/v1';

    public function providerName(): string
    {
        return 'openai';
    }

    public function modelName(): string
    {
        return (string) config('services.openai.model', 'gpt-4o-mini');
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

        $system = <<<SYSTEM
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
- For exam questions, if a type like "mid term"/"half yearly" appears, add parameters["exam_type"] as canonical values (mid_term / half_yearly).
- For date references (e.g. "Jan 2026", "this month", "today"), compute the concrete dates and add parameters["date_from"] and parameters["date_to"] (YYYY-MM-DD).
- Include class/subject only if mentioned.
- If no tool matches, return {"tool":"unknown","parameters":{},"confidence":0}.
SYSTEM;

        $response = $this->chat($system, $question);

        $parsed = json_decode($response, true);

        if (!is_array($parsed) || !isset($parsed['tool'])) {
            throw new \RuntimeException('OpenAI did not return a valid tool.');
        }

        return [
            'intent' => (string) $parsed['tool'],
            'parameters' => is_array($parsed['parameters'] ?? null) ? $parsed['parameters'] : [],
            'confidence' => (float) ($parsed['confidence'] ?? 0.0),
            'action' => isset($toolCatalog[$parsed['tool']]['result_type']) ? 'query' : 'action',
        ];
    }

    public function generate(string $systemPrompt, string $dataJson): string
    {
        return $this->chat($systemPrompt, $dataJson);
    }

    private function chat(string $system, string $user): string
    {
        $apiKey = (string) config('services.openai.api_key');
        $model = $this->modelName();
        $timeout = (int) config('services.openai.timeout', 30);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout($timeout)
            ->post(self::API_URL . '/chat/completions', [
                'model' => $model,
                'temperature' => 0.1,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

        if ($response->failed()) {
            Log::error('OpenAI API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('OpenAI API returned status ' . $response->status());
        }

        $text = $response->json('choices.0.message.content', null);

        if (!$text) {
            throw new \RuntimeException('Empty response from OpenAI');
        }

        return $text;
    }

    private function json(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]';
    }
}
