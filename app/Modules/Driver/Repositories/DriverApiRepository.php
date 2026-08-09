<?php

namespace App\Modules\Driver\Repositories;

use App\Models\Trip;
use App\Models\TripEvent;
use App\Models\TripStudent;
use App\Models\VehicleLocation;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function getTripHistoryForDriver(int $driverId, ?string $from, ?string $to, int $perPage): LengthAwarePaginator
    {
        return Trip::query()
            ->where('driver_id', $driverId)
            ->when($from, fn ($q) => $q->whereDate('trip_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('trip_date', '<=', $to))
            ->with(['route', 'vehicle'])
            ->orderByDesc('trip_date')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findCurrentTripForDriver(int $driverId): ?Trip
    {
        return Trip::query()
            ->where('driver_id', $driverId)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderByDesc('created_at')
            ->with(['route.stops', 'vehicle', 'tripStudents.student', 'tripStudents.stop'])
            ->first();
    }

    public function getRoutesForDriver(int $driverId): Collection
    {
        return Route::query()
            ->where('driver_id', $driverId)
            ->with(['vehicle', 'stops', 'assignments.student'])
            ->get();
    }

    public function findRouteForDriver(int $driverId, int $routeId, bool $withDetails = false): ?Route
    {
        $query = Route::query()
            ->where('id', $routeId)
            ->where('driver_id', $driverId);

        if ($withDetails) {
            $query->with(['vehicle', 'driver', 'stops.assignments.student']);
        }

        return $query->first();
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
