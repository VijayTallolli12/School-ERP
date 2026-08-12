<?php

namespace App\Modules\AiAssistant\Providers;

/**
 * Contract for the LLM provider used by the AI intelligence layer.
 *
 * Implementations must:
 *  - never expose credentials to the frontend
 *  - never be called directly from the browser
 */
interface AiProvider
{
    /**
     * Ask the provider to translate a natural-language question into a
     * structured tool request.
     *
     * @param  array<int, array<string, mixed>>  $toolCatalog
     * @return array{intent: string, parameters: array<string, mixed>, confidence: float, action: string}
     */
    public function understand(string $question, array $toolCatalog, array $context = []): array;

    /**
     * Ask the provider to generate a natural-language response from actual
     * validated ERP result data.
     */
    public function generate(string $systemPrompt, string $dataJson): string;

    public function providerName(): string;

    public function modelName(): string;
}
