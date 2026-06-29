<?php

namespace App\Listeners;

use App\Events\BusArrived;
use App\Events\BusArriving;
use App\Events\LocationUpdated;
use App\Events\TripCompleted;
use App\Events\TripStarted;
use Illuminate\Support\Facades\Log;

class LogTransportActivity
{
    public function handle(LocationUpdated|BusArriving|BusArrived|TripStarted|TripCompleted $event): void
    {
        $context = match ($event::class) {
            LocationUpdated::class => [
                'type' => 'location_updated',
                'vehicle_id' => $event->vehicleId,
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
                'speed' => $event->speed,
                'captured_at' => $event->capturedAt,
            ],
            BusArriving::class => [
                'type' => 'bus_arriving',
                'vehicle_id' => $event->vehicleId,
                'route_stop_id' => $event->routeStopId,
                'stop_name' => $event->stopName,
                'distance_meters' => $event->distanceMeters,
                'estimated_minutes' => $event->estimatedMinutes,
            ],
            BusArrived::class => [
                'type' => 'bus_arrived',
                'vehicle_id' => $event->vehicleId,
                'route_stop_id' => $event->routeStopId,
                'stop_name' => $event->stopName,
            ],
            TripStarted::class => [
                'type' => 'trip_started',
                'vehicle_id' => $event->vehicleId,
                'route_id' => $event->routeId,
                'started_at' => $event->startedAt,
            ],
            TripCompleted::class => [
                'type' => 'trip_completed',
                'vehicle_id' => $event->vehicleId,
                'route_id' => $event->routeId,
                'completed_at' => $event->completedAt,
            ],
        };

        Log::info('Transport activity', $context);
    }
}
