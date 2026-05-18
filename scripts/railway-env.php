<?php

function railway_env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);

    if ($value !== false) {
        return $value;
    }

    static $dotenv = null;

    if ($dotenv === null) {
        $dotenv = [];
        $path = dirname(__DIR__).'/.env';

        if (is_file($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                    continue;
                }

                [$name, $rawValue] = explode('=', $line, 2);
                $dotenv[trim($name)] = trim($rawValue, " \t\n\r\0\x0B\"'");
            }
        }
    }

    return $dotenv[$key] ?? $default;
}
