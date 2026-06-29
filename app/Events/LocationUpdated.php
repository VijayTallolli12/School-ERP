<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $vehicleId,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?float $speed,
        public readonly ?float $heading,
        public readonly string $capturedAt,
        public readonly array $extra = [],
    ) {}
}
