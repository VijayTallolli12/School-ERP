<?php

namespace App\Modules\Dashboard\DTOs;

class Widget
{
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $type,
        public readonly mixed $data,
        public readonly ?string $icon = null,
        public readonly ?string $color = null,
        public readonly ?int $cols = null,
        public readonly ?int $rows = null,
        public readonly ?string $route = null,
        public readonly ?string $emptyMessage = null,
    ) {}
}
