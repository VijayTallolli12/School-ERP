<?php

namespace App\Modules\Driver\Repositories;

use App\Models\Trip;
use App\Models\TripEvent;
use App\Models\TripStudent;
use App\Models\VehicleLocation;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Support\Collection;

class DriverApiRepository implements DriverApiRepositoryInterface
{
    public function findDriverByUserId(int $userId): ?Driver
    {
        return Driver::query()->where('user_id', $userId)->first();
    }

    public function findDriverWithRelationsByUserId(int $userId): ?Driver
    {
        return Driver::query()
            ->where('user_id', $userId)
            ->with(['vehicles', 'routes.stops'])
            ->first();
    }

    public function getTodayTripsForDriver(int $driverId): Collection
    {
        return Trip::query()
            ->where('driver_id', $driverId)
            ->where('trip_date', now()->startOfDay())
            ->with(['route', 'vehicle'])
            ->orderBy('created_at')
            ->get();
    }

    public function findTripForDriver(int $driverId, int $tripId): ?Trip
    {
        return Trip::query()
            ->where('id', $tripId)
            ->where('driver_id', $driverId)
            ->first();
    }

    public function findTripForDriverWithDetails(int $driverId, int $tripId): ?Trip
    {
        return Trip::query()
            ->where('id', $tripId)
            ->where('driver_id', $driverId)
            ->with(['route.stops', 'vehicle', 'tripStudents.student', 'tripStudents.stop'])
            ->first();
    }

    public function findTripStudentInTrip(int $tripId, int $tripStudentId): ?TripStudent
    {
        return TripStudent::query()
            ->where('id', $tripStudentId)
            ->where('trip_id', $tripId)
            ->first();
    }

    public function findDriverVehicle(Driver $driver, int $vehicleId): ?Vehicle
    {
        return $driver->vehicles()->where('id', $vehicleId)->first();
    }

    public function createVehicleLocation(array $payload): VehicleLocation
    {
        return VehicleLocation::query()->create($payload);
    }

    public function createTripEvent(array $payload): TripEvent
    {
        return TripEvent::query()->create($payload);
    }
}
