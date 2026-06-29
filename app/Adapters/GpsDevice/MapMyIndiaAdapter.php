<?php

namespace App\Adapters\GpsDevice;

use Illuminate\Support\Facades\Log;

class MapMyIndiaAdapter implements GpsDeviceAdapterInterface
{
    public function parsePayload(string $rawPayload): array
    {
        Log::info('MapMyIndia adapter: parsing payload', ['length' => strlen($rawPayload)]);

        return [
            'latitude' => 0.0,
            'longitude' => 0.0,
            'speed' => 0.0,
            'heading' => 0.0,
            'captured_at' => now()->toDateTimeString(),
            'source' => 'gps_device',
            'raw' => $rawPayload,
        ];
    }

    public function formatResponse(bool $success, string $message = ''): string
    {
        return json_encode(['success' => $success, 'message' => $message]);
    }

    public function supports(string $manufacturer): bool
    {
        return in_array(strtolower($manufacturer), ['mapmyindia', 'map_my_india']);
    }
}
