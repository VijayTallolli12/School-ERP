<?php

namespace App\Modules\Transport\Repositories;

use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;

interface TransportRepositoryInterface
{
    public function vehicles(): Builder;

    public function drivers(): Builder;

    public function routes(): Builder;

    public function routeStops(): Builder;

    public function assignments(): Builder;

    public function createVehicle(array $data): Vehicle;

    public function updateVehicle(Vehicle $vehicle, array $data): Vehicle;

    public function createDriver(array $data): Driver;

    public function updateDriver(Driver $driver, array $data): Driver;

    public function createRoute(array $data): Route;

    public function updateRoute(Route $route, array $data): Route;

    public function createRouteStop(array $data): RouteStop;

    public function updateRouteStop(RouteStop $routeStop, array $data): RouteStop;

    public function createAssignment(array $data): TransportAssignment;

    public function updateAssignment(TransportAssignment $assignment, array $data): TransportAssignment;
}
