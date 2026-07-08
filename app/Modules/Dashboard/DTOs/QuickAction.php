<?php

namespace App\Modules\Dashboard\DTOs;

class QuickAction
{
    public function __construct(
        public readonly string $label,
        public readonly string $route,
        public readonly string $icon,
        public readonly ?string $color = 'primary',
        public readonly ?string $permission = null,
    ) {}
}
