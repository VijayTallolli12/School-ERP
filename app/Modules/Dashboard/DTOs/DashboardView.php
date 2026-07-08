<?php

namespace App\Modules\Dashboard\DTOs;

use Illuminate\Support\Collection;

class DashboardView
{
    public readonly string $layout;

    public function __construct(
        public readonly string $roleName,
        public readonly string $greeting,
        public readonly array $statCards = [],
        public readonly array $widgets = [],
        public readonly array $quickActions = [],
        public readonly array $insights = [],
        public readonly array $charts = [],
        public readonly array $recentActivity = [],
        public readonly ?array $sidebar = null,
        public readonly ?array $meta = null,
        string $layout = 'admin',
    ) {
        $this->layout = $layout;
    }

    public function statCardsCollection(): Collection
    {
        return collect($this->statCards);
    }

    public function widgetsCollection(): Collection
    {
        return collect($this->widgets);
    }

    public function toArray(): array
    {
        return [
            'roleName' => $this->roleName,
            'greeting' => $this->greeting,
            'statCards' => $this->statCards,
            'widgets' => $this->widgets,
            'quickActions' => $this->quickActions,
            'insights' => $this->insights,
            'charts' => $this->charts,
            'recentActivity' => $this->recentActivity,
            'sidebar' => $this->sidebar,
            'meta' => $this->meta,
            'layout' => $this->layout,
        ];
    }
}
