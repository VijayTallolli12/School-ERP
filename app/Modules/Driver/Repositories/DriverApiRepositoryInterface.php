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

interface DriverApiRepositoryInterface
{
    public function findDriverByUserId(int $userId): ?Driver;

    public function findDriverWithRelationsByUserId(int $userId): ?Driver;

    public function getTodayTripsForDriver(int $driverId): Collection;

    public function getTripHistoryForDriver(int $driverId, ?string $from, ?string $to, int $perPage): LengthAwarePaginator;

    public function findCurrentTripForDriver(int $driverId): ?Trip;

    public function getRoutesForDriver(int $driverId): Collection;

    public function findRouteForDriver(int $driverId, int $routeId, bool $withDetails = false): ?Route;

    public function findTripForDriver(int $driverId, int $tripId): ?Trip;

    public function findTripForDriverWithDetails(int $driverId, int $tripId): ?Trip;

    public function findTripStudentInTrip(int $tripId, int $tripStudentId): ?TripStudent;

    public function findDriverVehicle(Driver $driver, int $vehicleId): ?Vehicle;

    public function createVehicleLocation(array $payload): VehicleLocation;

    public function createTripEvent(array $payload): TripEvent;
}
