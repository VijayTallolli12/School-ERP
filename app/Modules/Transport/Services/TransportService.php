<?php

namespace App\Modules\Transport\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;
use App\Modules\Transport\Repositories\TransportRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransportService
{
    public function __construct(private readonly TransportRepositoryInterface $transport)
    {
    }

    public function createVehicle(array $data): Vehicle
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $vehicle = $this->transport->createVehicle($data);
        activity()->causedBy(auth()->user())->performedOn($vehicle)->event('created')->log('Vehicle created');

        return $vehicle;
    }

    public function updateVehicle(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle = $this->transport->updateVehicle($vehicle, $data);
        activity()->causedBy(auth()->user())->performedOn($vehicle)->event('updated')->log('Vehicle updated');

        return $vehicle;
    }

    public function createDriver(array $data): Driver
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $driver = $this->transport->createDriver($data);
        activity()->causedBy(auth()->user())->performedOn($driver)->event('created')->log('Driver created');

        return $driver;
    }

    public function updateDriver(Driver $driver, array $data): Driver
    {
        $driver = $this->transport->updateDriver($driver, $data);
        activity()->causedBy(auth()->user())->performedOn($driver)->event('updated')->log('Driver updated');

        return $driver;
    }

    public function createRoute(array $data): Route
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $route = $this->transport->createRoute($data);
        activity()->causedBy(auth()->user())->performedOn($route)->event('created')->log('Route created');

        return $route;
    }

    public function updateRoute(Route $route, array $data): Route
    {
        $route = $this->transport->updateRoute($route, $data);
        activity()->causedBy(auth()->user())->performedOn($route)->event('updated')->log('Route updated');

        return $route;
    }

    public function createRouteStop(array $data): RouteStop
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $stop = $this->transport->createRouteStop($data);
        activity()->causedBy(auth()->user())->performedOn($stop)->event('created')->log('Route stop created');

        return $stop;
    }

    public function updateRouteStop(RouteStop $routeStop, array $data): RouteStop
    {
        $stop = $this->transport->updateRouteStop($routeStop, $data);
        activity()->causedBy(auth()->user())->performedOn($stop)->event('updated')->log('Route stop updated');

        return $stop;
    }

    public function createAssignment(array $data): TransportAssignment
    {
        return DB::transaction(function () use ($data): TransportAssignment {
            $data['school_id'] = app(SchoolContext::class)->id();
            $assignment = $this->transport->createAssignment($data);
            activity()->causedBy(auth()->user())->performedOn($assignment)->event('created')->log('Transport assignment created');

            return $assignment;
        });
    }

    public function updateAssignment(TransportAssignment $assignment, array $data): TransportAssignment
    {
        return DB::transaction(function () use ($assignment, $data): TransportAssignment {
            $assignment = $this->transport->updateAssignment($assignment, $data);
            activity()->causedBy(auth()->user())->performedOn($assignment)->event('updated')->log('Transport assignment updated');

            return $assignment;
        });
    }
}
