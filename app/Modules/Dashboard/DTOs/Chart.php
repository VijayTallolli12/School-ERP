<?php

namespace App\Modules\Dashboard\DTOs;

class Chart
{
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $type,
        public readonly array $labels,
        public readonly array $datasets,
        public readonly ?int $height = 300,
        public readonly ?array $options = null,
    ) {}
}
