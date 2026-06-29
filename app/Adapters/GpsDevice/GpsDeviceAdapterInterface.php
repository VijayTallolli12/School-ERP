<?php

namespace App\Adapters\GpsDevice;

interface GpsDeviceAdapterInterface
{
    public function parsePayload(string $rawPayload): array;
    public function formatResponse(bool $success, string $message = ''): string;
    public function supports(string $manufacturer): bool;
}
