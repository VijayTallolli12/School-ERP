<?php

namespace App\Listeners;

use App\Events\BusArrived;
use App\Events\BusArriving;
use App\Events\LocationUpdated;
use App\Events\TripCompleted;
use App\Events\TripStarted;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class SendTransportPushNotification
{
    public function __construct(
        private readonly PushNotificationService $pushService,
    ) {}

    public function handle(LocationUpdated|BusArriving|BusArrived|TripStarted|TripCompleted $event): void
    {
        try {
            [$title, $body, $userIds] = match ($event::class) {
                BusArriving::class => [
                    'Bus Approaching',
                    "Bus approaching {$event->stopName} (≈{$event->estimatedMinutes} min away).",
                    $this->resolveStopParentUserIds($event->routeStopId),
                ],
                BusArrived::class => [
                    'Bus Arrived',
                    "Bus has arrived at {$event->stopName}.",
                    $this->resolveStopParentUserIds($event->routeStopId),
                ],
                TripStarted::class => [
                    'Trip Started',
                    $this->getVehicleName($event->vehicleId) . " trip has started.",
                    $this->resolveVehicleParentUserIds($event->vehicleId),
                ],
                TripCompleted::class => [
                    'Trip Completed',
                    $this->getVehicleName($event->vehicleId) . " trip completed.",
                    $this->resolveVehicleParentUserIds($event->vehicleId),
                ],
                LocationUpdated::class => [null, null, []],
            };

            if ($title === null || empty($userIds)) {
                return;
            }

            $this->pushService->sendToUsers($userIds, $title, $body);
        } catch (\Throwable $e) {
            Log::error('Failed to send transport push notification', [
                'event' => $event::class,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveStopParentUserIds(int $routeStopId): array
    {
        return TransportAssignment::query()
            ->where('route_stop_id', $routeStopId)
            ->where('status', 'active')
            ->with('student.parents.user')
            ->get()
            ->flatMap(fn ($a) => $a->student?->parents->pluck('user_id') ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function resolveVehicleParentUserIds(int $vehicleId): array
    {
        return TransportAssignment::query()
            ->where('vehicle_id', $vehicleId)
            ->where('status', 'active')
            ->with('student.parents.user')
            ->get()
            ->flatMap(fn ($a) => $a->student?->parents->pluck('user_id') ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function getVehicleName(int $vehicleId): string
    {
        return Vehicle::query()->find($vehicleId)?->vehicle_name ?? "Vehicle #{$vehicleId}";
    }
}
