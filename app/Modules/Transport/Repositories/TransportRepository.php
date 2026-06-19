<?php

namespace App\Modules\Transport\Repositories;

use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;

class TransportRepository implements TransportRepositoryInterface
{
    public function vehicles(): Builder
    {
        return Vehicle::query()->with('driver')->orderBy('vehicle_name');
    }

    public function drivers(): Builder
    {
        return Driver::query()->withCount('vehicles')->orderBy('name');
    }

    public function routes(): Builder
    {
        return Route::query()->with(['vehicle', 'driver'])->withCount('stops')->orderBy('route_name');
    }

    public function routeStops(): Builder
    {
        return RouteStop::query()->with('route')->orderBy('route_id')->orderBy('sequence');
    }

    public function assignments(): Builder
    {
        return TransportAssignment::query()
            ->with(['student', 'route', 'stop', 'vehicle'])
            ->latest();
    }

    public function createVehicle(array $data): Vehicle
    {
        return Vehicle::query()->create($data);
    }

    public function updateVehicle(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->fill($data)->save();

        return $vehicle->refresh();
    }

    public function createDriver(array $data): Driver
    {
        return Driver::query()->create($data);
    }

    public function updateDriver(Driver $driver, array $data): Driver
    {
        $driver->fill($data)->save();

        return $driver->refresh();
    }

    public function createRoute(array $data): Route
    {
        return Route::query()->create($data);
    }

    public function updateRoute(Route $route, array $data): Route
    {
        $route->fill($data)->save();

        return $route->refresh();
    }

    public function createRouteStop(array $data): RouteStop
    {
        return RouteStop::query()->create($data);
    }

    public function updateRouteStop(RouteStop $routeStop, array $data): RouteStop
    {
        $routeStop->fill($data)->save();

        return $routeStop->refresh();
    }

    public function createAssignment(array $data): TransportAssignment
    {
        return TransportAssignment::query()->create($data);
    }

    public function updateAssignment(TransportAssignment $assignment, array $data): TransportAssignment
    {
        $assignment->fill($data)->save();

        return $assignment->refresh();
    }
}
