<?php

namespace App\Modules\Dashboard\DTOs;

class StatCard
{
    public readonly string $formattedValue;

    public function __construct(
        public readonly string $label,
        public readonly string|int|float $value,
        public readonly ?string $icon = null,
        public readonly ?string $color = null,
        public readonly ?string $trend = null,
        public readonly ?float $trendValue = null,
        public readonly ?string $route = null,
    ) {
        $this->formattedValue = is_numeric($value) && !str_contains((string) $value, '%')
            ? number_format((float) $value)
            : (string) $value;
    }
}
