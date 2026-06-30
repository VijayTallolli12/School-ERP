<?php

namespace App\Services;

use App\Models\Trip;
use App\Modules\Transport\Models\Driver;
use Illuminate\Support\Collection;

class DriverDashboardService
{
    public function dashboard(Driver $driver): array
    {
        $today = now()->startOfDay();

        $todayTrips = Trip::query()
            ->where('driver_id', $driver->id)
            ->where('trip_date', $today)
            ->with('route', 'vehicle')
            ->orderBy('created_at')
            ->get();

        $activeTrip = $todayTrips->firstWhere('status', 'in_progress');

        $vehicle = $driver->vehicles()->first();
        $routes = $driver->routes()->with('stops')->get();

        $routeStopsCount = $routes->sum(fn ($r) => $r->stops->count());

        return [
            'summary' => [
                'total_trips_today' => $todayTrips->count(),
                'completed_trips' => $todayTrips->where('status', 'completed')->count(),
                'active_trip' => $activeTrip?->id,
                'total_students_today' => $todayTrips->sum('total_students'),
                'total_picked_up' => $todayTrips->sum('picked_up_count'),
                'total_dropped_off' => $todayTrips->sum('dropped_off_count'),
            ],
            'vehicle' => $vehicle ? [
                'id' => $vehicle->id,
                'vehicle_number' => $vehicle->vehicle_number,
                'vehicle_name' => $vehicle->vehicle_name,
                'vehicle_type' => $vehicle->vehicle_type,
                'capacity' => $vehicle->capacity,
            ] : null,
            'routes' => $routes->map(fn ($r) => [
                'id' => $r->id,
                'route_name' => $r->route_name,
                'start_point' => $r->start_point,
                'end_point' => $r->end_point,
                'distance' => $r->distance,
                'stops_count' => $r->stops->count(),
            ])->values()->all(),
            'route_stops_count' => $routeStopsCount,
            'today_trips' => $todayTrips->map(fn (Trip $t) => [
                'id' => $t->id,
                'type' => $t->type,
                'status' => $t->status,
                'route_name' => $t->route?->route_name,
                'vehicle_number' => $t->vehicle?->vehicle_number,
                'total_students' => $t->total_students,
                'picked_up' => $t->picked_up_count,
                'dropped_off' => $t->dropped_off_count,
                'started_at' => $t->started_at?->toIso8601String(),
                'completed_at' => $t->completed_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
