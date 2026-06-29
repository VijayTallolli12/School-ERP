<?php

namespace App\Adapters\GpsDevice;

use Illuminate\Support\Facades\Log;

class GpsDeviceManager
{
    private array $adapters = [];

    public function registerAdapter(GpsDeviceAdapterInterface $adapter): void
    {
        $this->adapters[] = $adapter;
    }

    public function getAdapter(string $manufacturer): ?GpsDeviceAdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($manufacturer)) {
                return $adapter;
            }
        }

        Log::warning('No GPS adapter found for manufacturer', ['manufacturer' => $manufacturer]);

        return null;
    }

    public function parseFromDevice(string $manufacturer, string $rawPayload): array
    {
        $adapter = $this->getAdapter($manufacturer);

        if (! $adapter) {
            return [
                'latitude' => 0.0,
                'longitude' => 0.0,
                'speed' => 0.0,
                'heading' => 0.0,
                'captured_at' => now()->toDateTimeString(),
                'source' => 'gps_device',
                'raw' => $rawPayload,
                'error' => "No adapter for manufacturer: {$manufacturer}",
            ];
        }

        return $adapter->parsePayload($rawPayload);
    }
}
