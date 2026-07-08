<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Core\Tenant\SchoolContext;
use App\Models\User;
use App\Modules\Dashboard\Contracts\RoleDashboardBuilderInterface;
use App\Modules\Dashboard\DTOs\Chart;
use App\Modules\Dashboard\DTOs\DashboardView;
use App\Modules\Dashboard\DTOs\QuickAction;
use App\Modules\Dashboard\DTOs\StatCard;
use App\Modules\Dashboard\DTOs\Widget;
use Illuminate\Support\Facades\Cache;

abstract class BaseDashboardBuilder implements RoleDashboardBuilderInterface
{
    protected User $user;
    protected int $schoolId;

    abstract public function getRoleName(): string;

    abstract public function getLayout(): string;

    abstract protected function buildStatCards(): array;

    abstract protected function buildWidgets(): array;

    abstract protected function buildQuickActions(): array;

    protected function buildInsights(): array
    {
        return [];
    }

    protected function buildCharts(): array
    {
        return [];
    }

    protected function buildRecentActivity(): array
    {
        return [];
    }

    protected function buildSidebar(): ?array
    {
        return null;
    }

    protected function buildMeta(): array
    {
        return [];
    }

    protected function greeting(): string
    {
        $hour = (int) now()->format('G');

        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        return $greeting.', '.($this->user->name ?? 'User');
    }

    protected function statCard(string $label, string|int|float $value, ?string $icon = null, ?string $color = null, ?string $trend = null, ?float $trendValue = null, ?string $route = null): StatCard
    {
        return new StatCard($label, $value, $icon, $color, $trend, $trendValue, $route);
    }

    protected function widget(string $key, string $title, string $type, mixed $data, ?string $icon = null, ?string $color = null, ?int $cols = null, ?int $rows = null, ?string $route = null, ?string $emptyMessage = null): Widget
    {
        return new Widget($key, $title, $type, $data, $icon, $color, $cols, $rows, $route, $emptyMessage);
    }

    protected function quickAction(string $label, string $route, string $icon, ?string $color = 'primary', ?string $permission = null): QuickAction
    {
        return new QuickAction($label, $route, $icon, $color, $permission);
    }

    protected function chart(string $key, string $title, string $type, array $labels, array $datasets, ?int $height = 300, ?array $options = null): Chart
    {
        return new Chart($key, $title, $type, $labels, $datasets, $height, $options);
    }

    protected function can(string $permission): bool
    {
        return $this->user->can($permission);
    }

    protected function hasRole(string $role): bool
    {
        return $this->user->hasRole($role);
    }

    protected function cacheKey(string $key): string
    {
        return "dashboard.{$this->getRoleName()}.{$this->user->getKey()}.{$this->schoolId}.{$key}";
    }

    protected function cached(string $key, \Closure $callback, int $ttl = 300): mixed
    {
        return Cache::remember($this->cacheKey($key), $ttl, $callback);
    }

    public function initialize(User $user, int $schoolId): void
    {
        $this->user = $user;
        $this->schoolId = $schoolId;
    }

    public function build(User $user, int $schoolId): DashboardView
    {
        $this->initialize($user, $schoolId);

        return new DashboardView(
            roleName: $this->getRoleName(),
            greeting: $this->greeting(),
            statCards: $this->buildStatCards(),
            widgets: $this->buildWidgets(),
            quickActions: $this->buildQuickActions(),
            insights: $this->buildInsights(),
            charts: $this->buildCharts(),
            recentActivity: $this->buildRecentActivity(),
            sidebar: $this->buildSidebar(),
            meta: $this->buildMeta(),
            layout: $this->getLayout(),
        );
    }
}
