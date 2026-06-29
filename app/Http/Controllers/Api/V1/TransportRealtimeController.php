<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenant\SchoolContext;
use App\Events\LocationUpdated;
use App\Models\VehicleLocation;
use App\Modules\Transport\Models\Vehicle;
use App\Services\EtaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportRealtimeController extends ApiBaseController
{
    public function __construct(
        private readonly EtaService $etaService,
    ) {}

    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'captured_at' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:30'],
        ]);

        $location = VehicleLocation::query()->create([
            'vehicle_id' => $validated['vehicle_id'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'] ?? null,
            'heading' => $validated['heading'] ?? null,
            'captured_at' => $validated['captured_at'] ?? now(),
            'source' => $validated['source'] ?? 'driver_app',
        ]);

        LocationUpdated::dispatch(
            vehicleId: $validated['vehicle_id'],
            latitude: (float) $validated['latitude'],
            longitude: (float) $validated['longitude'],
            speed: isset($validated['speed']) ? (float) $validated['speed'] : null,
            heading: isset($validated['heading']) ? (float) $validated['heading'] : null,
            capturedAt: $location->captured_at->toIso8601String(),
        );

        return $this->success([
            'location' => [
                'id' => $location->id,
                'vehicle_id' => $location->vehicle_id,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'speed' => $location->speed,
                'heading' => $location->heading,
                'captured_at' => $location->captured_at->toIso8601String(),
            ],
        ], 'Location updated successfully.');
    }

    public function liveStatus(Request $request): JsonResponse
    {
        $schoolId = app(SchoolContext::class)->id();

        $vehicles = Vehicle::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->with('driver')
            ->get();

        $activeVehicles = [];
        $inactiveVehicles = [];

        foreach ($vehicles as $vehicle) {
            $latestLocation = VehicleLocation::query()
                ->where('vehicle_id', $vehicle->id)
                ->latest('captured_at')
                ->first();

            $isActive = $latestLocation && $latestLocation->captured_at->diffInMinutes(now()) <= 15;

            $entry = [
                'id' => $vehicle->id,
                'vehicle_number' => $vehicle->vehicle_number,
                'vehicle_name' => $vehicle->vehicle_name,
                'vehicle_type' => $vehicle->vehicle_type,
                'capacity' => $vehicle->capacity,
                'driver_name' => $vehicle->driver?->name,
                'current_location' => $latestLocation ? [
                    'latitude' => (float) $latestLocation->latitude,
                    'longitude' => (float) $latestLocation->longitude,
                    'speed' => $latestLocation->speed,
                    'heading' => $latestLocation->heading,
                    'captured_at' => $latestLocation->captured_at->toIso8601String(),
                    'last_seen_minutes_ago' => $latestLocation->captured_at->diffInMinutes(now()),
                ] : null,
            ];

            if ($isActive) {
                $activeVehicles[] = $entry;
            } else {
                $inactiveVehicles[] = $entry;
            }
        }

        $tripsRunning = VehicleLocation::query()
            ->whereIn('vehicle_id', $vehicles->pluck('id'))
            ->where('captured_at', '>=', now()->subMinutes(15))
            ->distinct('vehicle_id')
            ->count('vehicle_id');

        return $this->success([
            'summary' => [
                'total_vehicles' => $vehicles->count(),
                'active_vehicles' => count($activeVehicles),
                'inactive_vehicles' => count($inactiveVehicles),
                'trips_running' => $tripsRunning,
            ],
            'active_vehicles' => $activeVehicles,
            'inactive_vehicles' => $inactiveVehicles,
        ], 'Live transport status retrieved.');
    }

    public function vehicleLocation(int $id, Request $request): JsonResponse
    {
        $vehicle = Vehicle::query()->findOrFail($id);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = VehicleLocation::query()
            ->where('vehicle_id', $vehicle->id);

        if ($validated['from'] ?? null) {
            $query->where('captured_at', '>=', $validated['from']);
        }

        if ($validated['to'] ?? null) {
            $query->where('captured_at', '<=', $validated['to']);
        }

        $locations = $query->orderByDesc('captured_at')
            ->limit($validated['limit'] ?? 50)
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'latitude' => (float) $l->latitude,
                'longitude' => (float) $l->longitude,
                'speed' => $l->speed,
                'heading' => $l->heading,
                'captured_at' => $l->captured_at->toIso8601String(),
                'source' => $l->source,
            ]);

        $latest = $locations->first();

        return $this->success([
            'vehicle' => [
                'id' => $vehicle->id,
                'vehicle_number' => $vehicle->vehicle_number,
                'vehicle_name' => $vehicle->vehicle_name,
            ],
            'current_location' => $latest ? [
                'latitude' => $latest['latitude'],
                'longitude' => $latest['longitude'],
                'speed' => $latest['speed'],
                'heading' => $latest['heading'],
                'captured_at' => $latest['captured_at'],
            ] : null,
            'location_history' => $locations,
        ], 'Vehicle location retrieved.');
    }

}
