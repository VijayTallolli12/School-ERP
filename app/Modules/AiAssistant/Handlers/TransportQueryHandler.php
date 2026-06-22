<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;

class TransportQueryHandler
{
    public function __construct(
        private readonly SchoolContext $schoolContext
    ) {}

    public function routeOccupancy(): string
    {
        $schoolId = $this->schoolContext->id();

        $routes = Route::query()
            ->where('school_id', $schoolId)
            ->withCount('assignments')
            ->with('vehicle')
            ->get();

        if ($routes->isEmpty()) {
            return 'No routes found.';
        }

        $lines = [];
        foreach ($routes as $route) {
            $capacity = $route->vehicle?->capacity ?? 0;
            $occupied = $route->assignments_count;
            $percent = $capacity > 0 ? round(($occupied / $capacity) * 100, 1) : 0;
            $lines[] = "{$route->route_name}: {$occupied} students / {$capacity} capacity ({$percent}%)";
        }

        return "Route occupancy:\n" . implode("\n", $lines);
    }

    public function studentsOnRoute(): string
    {
        $schoolId = $this->schoolContext->id();

        $routes = Route::query()
            ->where('school_id', $schoolId)
            ->withCount('assignments')
            ->get();

        if ($routes->isEmpty()) {
            return 'No routes found.';
        }

        $total = $routes->sum('assignments_count');
        $lines = [];
        foreach ($routes as $route) {
            $lines[] = "{$route->route_name}: {$route->assignments_count} students";
        }

        return "Students on routes (total: {$total}):\n" . implode("\n", $lines);
    }

    public function vehicleAssignments(): string
    {
        $schoolId = $this->schoolContext->id();

        $vehicles = Vehicle::query()
            ->where('school_id', $schoolId)
            ->withCount('assignments')
            ->with('driver')
            ->get();

        if ($vehicles->isEmpty()) {
            return 'No vehicles found.';
        }

        $lines = [];
        foreach ($vehicles as $vehicle) {
            $driverName = $vehicle->driver?->name ?? 'No driver';
            $lines[] = "{$vehicle->vehicle_name} ({$vehicle->vehicle_number}): {$vehicle->assignments_count} students assigned, Driver: {$driverName}";
        }

        return "Vehicle assignments:\n" . implode("\n", $lines);
    }
}
