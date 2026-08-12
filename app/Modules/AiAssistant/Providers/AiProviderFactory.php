<?php

namespace App\Modules\AiAssistant\Providers;

use RuntimeException;

class AiProviderFactory
{
    public function make(): AiProvider
    {
        $provider = mb_strtolower((string) config('ai.provider', 'gemini'));

        return match ($provider) {
            'gemini' => new GeminiProvider(),
            'openai' => new OpenAIProvider(),
            default => throw new RuntimeException("Unsupported AI provider '{$provider}'."),
        };
    }

    public function isConfigured(): bool
    {
        $provider = mb_strtolower((string) config('ai.provider', 'gemini'));

        return match ($provider) {
            'openai' => !empty(config('services.openai.api_key')),
            default => !empty(config('services.gemini.api_key')),
        };
    }
}
