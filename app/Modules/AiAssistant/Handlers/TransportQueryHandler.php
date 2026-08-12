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

    public function routeOccupancyStructured(array $parameters): array
    {
        $routes = $this->routesWithAssignments();

        return [
            'count' => count($routes),
            'records' => array_map(fn ($r) => [
                'route_id' => $r['id'],
                'route_name' => $r['route_name'],
                'occupied' => $r['assignments_count'],
                'capacity' => $r['capacity'],
                'utilization' => $r['utilization'],
            ], $routes),
            'summary' => [
                'total_routes' => count($routes),
                'total_students' => array_sum(array_column($routes, 'assignments_count')),
                'total_capacity' => array_sum(array_column($routes, 'capacity')),
            ],
        ];
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

    public function studentsOnRouteStructured(array $parameters): array
    {
        $routes = $this->routesWithAssignments();

        return [
            'count' => count($routes),
            'records' => array_map(fn ($r) => [
                'route_id' => $r['id'],
                'route_name' => $r['route_name'],
                'students' => $r['assignments_count'],
            ], $routes),
            'summary' => [
                'total_students' => array_sum(array_column($routes, 'assignments_count')),
            ],
        ];
    }

    public function status(array $parameters): array
    {
        $date = $parameters['date'] ?? now()->format('Y-m-d');
        $schoolId = $this->schoolContext->id();

        $activeRoutes = Route::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->with('vehicle')
            ->withCount('assignments')
            ->get();

        $records = $activeRoutes->map(fn (Route $r) => [
            'route_id' => $r->id,
            'route_name' => $r->route_name,
            'start' => $r->start_point,
            'end' => $r->end_point,
            'status' => $r->status,
            'vehicle' => $r->vehicle?->vehicle_name,
            'vehicle_number' => $r->vehicle?->vehicle_number,
            'capacity' => $r->vehicle?->capacity ?? 0,
            'assigned_students' => $r->assignments_count,
        ])->all();

        return [
            'count' => $activeRoutes->count(),
            'records' => $records,
            'summary' => [
                'date' => $date,
                'active_routes' => $activeRoutes->count(),
                'active_buses' => $activeRoutes->filter(fn ($r) => $r->vehicle_id !== null)->count(),
                'total_students' => $activeRoutes->sum('assignments_count'),
            ],
        ];
    }

    public function routes(array $parameters): array
    {
        $routes = $this->routesWithAssignments();

        return [
            'count' => count($routes),
            'records' => array_map(fn ($r) => [
                'route_id' => $r['id'],
                'route_name' => $r['route_name'],
                'start' => $r['start_point'],
                'end' => $r['end_point'],
                'status' => $r['status'],
            ], $routes),
            'summary' => null,
        ];
    }

    private function routesWithAssignments(): array
    {
        $routes = Route::query()
            ->where('school_id', $this->schoolContext->id())
            ->with('vehicle')
            ->withCount('assignments')
            ->get();

        return $routes->map(function (Route $route) {
            $capacity = $route->vehicle?->capacity ?? 0;
            $occupied = $route->assignments_count;

            return [
                'id' => $route->id,
                'route_name' => $route->route_name,
                'start_point' => $route->start_point,
                'end_point' => $route->end_point,
                'status' => $route->status,
                'vehicle_id' => $route->vehicle_id,
                'capacity' => $capacity,
                'assignments_count' => $occupied,
                'utilization' => $capacity > 0 ? round(($occupied / $capacity) * 100, 1) : 0,
            ];
        })->all();
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
