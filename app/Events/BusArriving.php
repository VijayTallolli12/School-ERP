<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BusArriving
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $vehicleId,
        public readonly int $routeStopId,
        public readonly string $stopName,
        public readonly float $distanceMeters,
        public readonly int $estimatedMinutes,
        public readonly array $extra = [],
    ) {}
}
