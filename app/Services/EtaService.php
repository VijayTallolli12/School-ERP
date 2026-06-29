<?php

namespace App\Services;

class EtaService
{
    private const EARTH_RADIUS_KM = 6371;

    public function distanceBetween(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    public function distanceToStop(float $vehicleLat, float $vehicleLng, float $stopLat, float $stopLng): float
    {
        return $this->distanceBetween($vehicleLat, $vehicleLng, $stopLat, $stopLng);
    }

    public function estimatedMinutes(float $distanceKm, ?float $avgSpeedKmph = null): int
    {
        $speed = $avgSpeedKmph ?? 30;

        if ($speed <= 0) {
            $speed = 30;
        }

        $hours = $distanceKm / $speed;

        return max(1, (int) ceil($hours * 60));
    }

    public function isWithinThreshold(float $distanceKm, float $thresholdKm = 0.5): bool
    {
        return $distanceKm <= $thresholdKm;
    }
}
