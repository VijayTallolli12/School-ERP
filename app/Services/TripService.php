<?php

namespace App\Services;

use App\Core\Tenant\SchoolContext;
use App\Events\BusArrived;
use App\Events\BusArriving;
use App\Events\TripCompleted;
use App\Events\TripStarted;
use App\Models\Trip;
use App\Models\TripEvent;
use App\Models\TripStudent;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\RouteStop;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TripService
{
    public function __construct(
        private readonly EtaService $etaService,
    ) {}

    public function createTripsForDate(int $driverId, int $vehicleId, int $routeId, string $date): Collection
    {
        $schoolId = app(SchoolContext::class)->id();

        $activeAssignments = TransportAssignment::query()
            ->where('school_id', $schoolId)
            ->where('vehicle_id', $vehicleId)
            ->where('status', 'active')
            ->with('routeStop')
            ->get();

        $stops = RouteStop::query()
            ->where('route_id', $routeId)
            ->orderBy('sequence')
            ->get();

        $studentsByStop = $activeAssignments->groupBy('route_stop_id');

        $pickupStops = $stops->filter(fn ($s) => $s->pickup_time !== null);
        $dropStops = $stops->filter(fn ($s) => $s->drop_time !== null);

        $trips = collect();

        if ($pickupStops->isNotEmpty()) {
            $pickupStudents = $pickupStops->mapWithKeys(fn ($s) => [
                $s->id => $studentsByStop->get($s->id, collect()),
            ])->flatten(1);

            $trip = DB::transaction(function () use ($schoolId, $driverId, $vehicleId, $routeId, $date, $pickupStudents, $stops) {
                $trip = Trip::query()->create([
                    'school_id' => $schoolId,
                    'driver_id' => $driverId,
                    'vehicle_id' => $vehicleId,
                    'route_id' => $routeId,
                    'type' => 'pickup',
                    'status' => 'scheduled',
                    'trip_date' => $date,
                    'total_students' => $pickupStudents->count(),
                ]);

                foreach ($pickupStudents as $assignment) {
                    TripStudent::query()->create([
                        'school_id' => $schoolId,
                        'trip_id' => $trip->id,
                        'student_id' => $assignment->student_id,
                        'route_stop_id' => $assignment->route_stop_id,
                    ]);
                }

                return $trip;
            });

            $trips->push($trip);
        }

        if ($dropStops->isNotEmpty()) {
            $dropStudents = $dropStops->mapWithKeys(fn ($s) => [
                $s->id => $studentsByStop->get($s->id, collect()),
            ])->flatten(1);

            $trip = DB::transaction(function () use ($schoolId, $driverId, $vehicleId, $routeId, $date, $dropStudents, $stops) {
                $trip = Trip::query()->create([
                    'school_id' => $schoolId,
                    'driver_id' => $driverId,
                    'vehicle_id' => $vehicleId,
                    'route_id' => $routeId,
                    'type' => 'drop',
                    'status' => 'scheduled',
                    'trip_date' => $date,
                    'total_students' => $dropStudents->count(),
                ]);

                foreach ($dropStudents as $assignment) {
                    TripStudent::query()->create([
                        'school_id' => $schoolId,
                        'trip_id' => $trip->id,
                        'student_id' => $assignment->student_id,
                        'route_stop_id' => $assignment->route_stop_id,
                    ]);
                }

                return $trip;
            });

            $trips->push($trip);
        }

        return $trips;
    }

    public function startTrip(Trip $trip): Trip
    {
        $trip->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        TripEvent::query()->create([
            'school_id' => $trip->school_id,
            'trip_id' => $trip->id,
            'event_type' => 'trip_started',
            'metadata' => ['started_at' => now()->toIso8601String()],
        ]);

        TripStarted::dispatch(
            vehicleId: $trip->vehicle_id,
            routeId: $trip->route_id,
            startedAt: now()->toIso8601String(),
        );

        return $trip->fresh(['route', 'vehicle']);
    }

    public function completeTrip(Trip $trip): Trip
    {
        $trip->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        TripEvent::query()->create([
            'school_id' => $trip->school_id,
            'trip_id' => $trip->id,
            'event_type' => 'trip_completed',
            'metadata' => ['completed_at' => now()->toIso8601String()],
        ]);

        TripCompleted::dispatch(
            vehicleId: $trip->vehicle_id,
            routeId: $trip->route_id,
            completedAt: now()->toIso8601String(),
        );

        return $trip->fresh(['route', 'vehicle']);
    }

    public function markPickup(TripStudent $tripStudent, ?float $latitude = null, ?float $longitude = null): TripStudent
    {
        $tripStudent->update([
            'pickup_status' => 'picked_up',
            'picked_up_at' => now(),
            'pickup_latitude' => $latitude,
            'pickup_longitude' => $longitude,
        ]);

        $tripStudent->trip->increment('picked_up_count');

        TripEvent::query()->create([
            'school_id' => $tripStudent->school_id,
            'trip_id' => $tripStudent->trip_id,
            'trip_student_id' => $tripStudent->id,
            'event_type' => 'student_pickup',
            'metadata' => [
                'student_id' => $tripStudent->student_id,
                'picked_up_at' => now()->toIso8601String(),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
        ]);

        return $tripStudent->fresh(['student', 'stop']);
    }

    public function markDrop(TripStudent $tripStudent, ?float $latitude = null, ?float $longitude = null): TripStudent
    {
        $tripStudent->update([
            'drop_status' => 'dropped_off',
            'dropped_off_at' => now(),
            'drop_latitude' => $latitude,
            'drop_longitude' => $longitude,
        ]);

        $tripStudent->trip->increment('dropped_off_count');

        TripEvent::query()->create([
            'school_id' => $tripStudent->school_id,
            'trip_id' => $tripStudent->trip_id,
            'trip_student_id' => $tripStudent->id,
            'event_type' => 'student_drop',
            'metadata' => [
                'student_id' => $tripStudent->student_id,
                'dropped_off_at' => now()->toIso8601String(),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
        ]);

        return $tripStudent->fresh(['student', 'stop']);
    }

    public function notifyBusArriving(int $routeStopId, float $distanceMeters, int $estimatedMinutes): void
    {
        $stop = RouteStop::query()->findOrFail($routeStopId);

        BusArriving::dispatch(
            vehicleId: $stop->route?->vehicle_id ?? 0,
            routeStopId: $routeStopId,
            stopName: $stop->stop_name,
            distanceMeters: $distanceMeters,
            estimatedMinutes: $estimatedMinutes,
        );
    }

    public function notifyBusArrived(int $routeStopId): void
    {
        $stop = RouteStop::query()->findOrFail($routeStopId);

        BusArrived::dispatch(
            vehicleId: $stop->route?->vehicle_id ?? 0,
            routeStopId: $routeStopId,
            stopName: $stop->stop_name,
        );
    }
}
