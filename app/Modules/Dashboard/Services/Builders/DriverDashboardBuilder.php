<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Models\Trip;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\SosAlert;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Support\Facades\Cache;

class DriverDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'Driver';
    }

    public function getLayout(): string
    {
        return 'admin';
    }

    protected function buildStatCards(): array
    {
        $vehicles = Cache::remember("dashboard.driver.vehicles.{$this->schoolId}", 300, fn () => Vehicle::query()->count());
        $routes = Cache::remember("dashboard.driver.routes.{$this->schoolId}", 300, fn () => Route::query()->count());
        $tripsToday = Cache::remember("dashboard.driver.trips_today.{$this->schoolId}", 300, fn () => Trip::query()->whereDate('trip_date', today())->count());
        $openSos = Cache::remember("dashboard.driver.sos.{$this->schoolId}", 60, fn () => SosAlert::query()->where('status', 'open')->count());

        return [
            $this->statCard('Vehicles', $vehicles, 'truck', 'primary', null, null, route('admin.transport.index')),
            $this->statCard('Routes', $routes, 'map', 'info', null, null, route('admin.transport.index')),
            $this->statCard("Today's Trips", $tripsToday, 'calendar-event', 'success'),
            $this->statCard('Open SOS Alerts', $openSos, 'alert-triangle', 'danger', null, null, route('admin.transport.sos.index')),
        ];
    }

    protected function buildWidgets(): array
    {
        return [];
    }

    protected function buildQuickActions(): array
    {
        return [
            $this->quickAction('Transport', route('admin.transport.index'), 'bus', 'primary', 'transport.view'),
            $this->quickAction('SOS Alerts', route('admin.transport.sos.index'), 'alert-triangle', 'danger', 'transport.view'),
        ];
    }

    protected function buildCharts(): array
    {
        return [];
    }
}
