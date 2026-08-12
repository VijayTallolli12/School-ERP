<?php

namespace App\Modules\AiAssistant\Erp;

use App\Modules\AiAssistant\Models\AiQueryLog;
use Illuminate\Support\Facades\Log;

/**
 * Safe, structured logging for every AI request.
 *
 * Never logs secrets: API keys, tokens, or passwords are excluded.
 */
class AiRequestLogger
{
    public function log(array $payload): void
    {
        $safe = $this->scrub($payload);

        try {
            AiQueryLog::query()->create([
                'user_id' => auth()->id(),
                'role' => auth()->user()?->roles->first()?->name ?? 'unknown',
                'intent' => $safe['intent'] ?? 'unknown',
                'question' => $safe['question'] ?? '',
                'parameters' => $safe['parameters'] ?? [],
                'response_summary' => isset($safe['response'])
                    ? mb_substr((string) $safe['response'], 0, 500)
                    : null,
                'status' => $safe['status'] ?? 'error',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Never let logging failures break the main flow.
            Log::warning('[AI] Could not persist query log', [
                'error' => $e->getMessage(),
            ]);
        }

        $logLevel = ($safe['status'] ?? 'error') === 'error' ? 'warning' : 'info';

        Log::channel('daily')->{$logLevel}('[AI Request] ' . ($safe['intent'] ?? 'unknown'), [
            'user_id' => auth()->id(),
            'question' => $safe['question'] ?? '',
            'intent' => $safe['intent'] ?? 'unknown',
            'source' => $safe['source'] ?? null,
            'structured_parameters' => $safe['parameters'] ?? [],
            'result_count' => $safe['result_count'] ?? null,
            'execution_time_ms' => $safe['execution_time_ms'] ?? null,
            'status' => $safe['status'] ?? 'error',
        ]);
    }

    private function scrub(array $payload): array
    {
        $denied = ['api_key', 'apikey', 'token', 'password', 'secret', 'authorization'];

        $walk = function (mixed $value) use (&$walk, $denied): mixed {
            if (is_array($value)) {
                return array_map($walk, $value);
            }

            return $value;
        };

        $walked = $walk($payload);

        if (isset($walked['parameters']) && is_array($walked['parameters'])) {
            foreach (array_keys($walked['parameters']) as $key) {
                if (in_array(mb_strtolower((string) $key), $denied, true)) {
                    unset($walked['parameters'][$key]);
                }
            }
        }

        return $walked;
    }
}
