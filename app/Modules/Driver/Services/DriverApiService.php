<?php

namespace App\Modules\Driver\Services;

use App\Core\Tenant\SchoolContext;
use App\Events\LocationUpdated;
use App\Http\Resources\Api\V1\DriverAuthResource;
use App\Models\Trip;
use App\Models\TripStudent;
use App\Models\User;
use App\Modules\Driver\Repositories\DriverApiRepositoryInterface;
use App\Modules\Transport\Models\Driver;
use App\Services\DriverDashboardService;
use App\Services\EtaService;
use App\Services\TripService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class DriverApiService
{
    public function __construct(
        private readonly DriverApiRepositoryInterface $drivers,
        private readonly DriverDashboardService $dashboardService,
        private readonly TripService $tripService,
        private readonly EtaService $etaService,
    ) {}

    public function login(array $validated, ?int $schoolIdFromHeader = null): array
    {
        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->status !== 'active') {
            throw new AuthorizationException('Account is not active.', Response::HTTP_FORBIDDEN);
        }

        $schoolId = $this->resolveSchoolId($user, $schoolIdFromHeader, $validated['school_id'] ?? null);

        if (! $schoolId) {
            throw new AuthorizationException('Could not resolve your school. Contact the administrator.', Response::HTTP_FORBIDDEN);
        }

        app(SchoolContext::class)->set($schoolId);
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

        $user->load('roles');

        if (! $user->hasRole('Driver')) {
            throw new AuthorizationException('Only Driver role users can access this endpoint.', Response::HTTP_FORBIDDEN);
        }

        $driver = $this->drivers->findDriverWithRelationsByUserId($user->id);

        if (! $driver || $driver->school_id !== $schoolId) {
            throw new AuthorizationException('User is not a registered driver for the selected school.', Response::HTTP_FORBIDDEN);
        }

        $abilities = $user->getAllPermissions()->pluck('name')->values()->all();
        $token = $user->createToken(
            $validated['device_name'] ?? 'driver-app',
            $abilities ?: ['transport.update', 'transport.view']
        );

        activity()
            ->causedBy($user)
            ->performedOn($driver)
            ->event('driver_login')
            ->withProperties(['school_id' => $schoolId])
            ->log('Driver logged in via mobile app');

        return DriverAuthResource::make([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'school_id' => $schoolId,
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'mobile' => $driver->mobile,
                'vehicle' => $driver->vehicles->first() ? [
                    'id' => $driver->vehicles->first()->id,
                    'vehicle_number' => $driver->vehicles->first()->vehicle_number,
                    'vehicle_name' => $driver->vehicles->first()->vehicle_name,
                ] : null,
                'routes' => $driver->routes->map(fn ($r) => [
                    'id' => $r->id,
                    'route_name' => $r->route_name,
                ])->values()->all(),
            ],
        ])->resolve();
    }

    public function dashboard(User $user): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');

        return $this->dashboardService->dashboard($driver);
    }

    public function profile(User $user): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');
        $driver->load(['vehicles', 'routes.stops']);

        $vehicle = $driver->vehicles->first();
        $route = $driver->routes->first();

        return [
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'mobile' => $driver->mobile,
                'license_number' => $driver->license_number,
                'license_expiry_date' => $driver->license_expiry_date?->format('Y-m-d'),
                'address' => $driver->address,
                'status' => $driver->status,
            ],
            'vehicle' => $vehicle ? [
                'id' => $vehicle->id,
                'vehicle_number' => $vehicle->vehicle_number,
                'vehicle_name' => $vehicle->vehicle_name,
                'vehicle_type' => $vehicle->vehicle_type,
                'capacity' => $vehicle->capacity,
            ] : null,
            'route' => $route ? [
                'id' => $route->id,
                'route_name' => $route->route_name,
                'start_point' => $route->start_point,
                'end_point' => $route->end_point,
                'distance' => $route->distance,
                'stops' => $route->stops->map(fn ($s) => [
                    'id' => $s->id,
                    'stop_name' => $s->stop_name,
                    'pickup_time' => $s->pickup_time?->format('H:i'),
                    'drop_time' => $s->drop_time?->format('H:i'),
                    'sequence' => $s->sequence,
                ])->values()->all(),
            ] : null,
        ];
    }

    public function tripsToday(User $user): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');
        $trips = $this->drivers->getTodayTripsForDriver($driver->id);

        return [
            'trips' => $trips->map(fn (Trip $t) => [
                'id' => $t->id,
                'type' => $t->type,
                'status' => $t->status,
                'route_name' => $t->route?->route_name,
                'route_id' => $t->route_id,
                'vehicle_number' => $t->vehicle?->vehicle_number,
                'vehicle_id' => $t->vehicle_id,
                'total_students' => $t->total_students,
                'picked_up_count' => $t->picked_up_count,
                'dropped_off_count' => $t->dropped_off_count,
                'started_at' => $t->started_at?->toIso8601String(),
                'completed_at' => $t->completed_at?->toIso8601String(),
                'created_at' => $t->created_at->toIso8601String(),
            ])->values()->all(),
        ];
    }

    public function tripShow(User $user, Trip $trip): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, true);

        $stops = $trip->route?->stops ?? collect();
        $studentsByStop = $trip->tripStudents->groupBy('route_stop_id');

        return [
            'trip' => [
                'id' => $trip->id,
                'type' => $trip->type,
                'status' => $trip->status,
                'trip_date' => $trip->trip_date->format('Y-m-d'),
                'started_at' => $trip->started_at?->toIso8601String(),
                'completed_at' => $trip->completed_at?->toIso8601String(),
                'total_students' => $trip->total_students,
                'picked_up_count' => $trip->picked_up_count,
                'dropped_off_count' => $trip->dropped_off_count,
                'notes' => $trip->notes,
            ],
            'route' => [
                'id' => $trip->route?->id,
                'route_name' => $trip->route?->route_name,
                'start_point' => $trip->route?->start_point,
                'end_point' => $trip->route?->end_point,
            ],
            'vehicle' => $trip->vehicle ? [
                'id' => $trip->vehicle->id,
                'vehicle_number' => $trip->vehicle->vehicle_number,
                'vehicle_name' => $trip->vehicle->vehicle_name,
            ] : null,
            'stops' => $stops->map(fn ($stop) => [
                'id' => $stop->id,
                'stop_name' => $stop->stop_name,
                'pickup_time' => $stop->pickup_time?->format('H:i'),
                'drop_time' => $stop->drop_time?->format('H:i'),
                'sequence' => $stop->sequence,
                'students' => ($studentsByStop->get($stop->id) ?? collect())->map(fn (TripStudent $ts) => [
                    'id' => $ts->id,
                    'student_id' => $ts->student_id,
                    'student_name' => $ts->student?->full_name,
                    'pickup_status' => $ts->pickup_status,
                    'drop_status' => $ts->drop_status,
                    'picked_up_at' => $ts->picked_up_at?->toIso8601String(),
                    'dropped_off_at' => $ts->dropped_off_at?->toIso8601String(),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    public function tripStart(User $user, Trip $trip): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, false);

        if ($trip->status !== 'scheduled') {
            throw ValidationException::withMessages([
                'trip' => ['Trip can only be started from scheduled status.'],
            ]);
        }

        $trip = $this->tripService->startTrip($trip);

        activity()->causedBy($user)->performedOn($trip)->event('started')->log('Driver started trip');

        return [
            'trip' => [
                'id' => $trip->id,
                'status' => $trip->status,
                'started_at' => $trip->started_at?->toIso8601String(),
            ],
        ];
    }

    public function tripComplete(User $user, Trip $trip): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, false);

        if ($trip->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'trip' => ['Trip can only be completed from in_progress status.'],
            ]);
        }

        $trip = $this->tripService->completeTrip($trip);

        activity()->causedBy($user)->performedOn($trip)->event('completed')->log('Driver completed trip');

        return [
            'trip' => [
                'id' => $trip->id,
                'status' => $trip->status,
                'completed_at' => $trip->completed_at?->toIso8601String(),
            ],
        ];
    }

    public function tripStudents(User $user, Trip $trip): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, true);

        $tripStudents = $trip->tripStudents;
        $pickupOrder = $tripStudents->sortBy(fn (TripStudent $ts) => $ts->stop?->sequence);
        $dropOrder = $tripStudents->sortByDesc(fn (TripStudent $ts) => $ts->stop?->sequence);

        return [
            'pickup_order' => $pickupOrder->values()->map(fn (TripStudent $ts) => [
                'id' => $ts->id,
                'student_id' => $ts->student_id,
                'student_name' => $ts->student?->full_name,
                'stop_name' => $ts->stop?->stop_name,
                'stop_sequence' => $ts->stop?->sequence,
                'pickup_status' => $ts->pickup_status,
                'picked_up_at' => $ts->picked_up_at?->toIso8601String(),
            ])->all(),
            'drop_order' => $dropOrder->values()->map(fn (TripStudent $ts) => [
                'id' => $ts->id,
                'student_id' => $ts->student_id,
                'student_name' => $ts->student?->full_name,
                'stop_name' => $ts->stop?->stop_name,
                'stop_sequence' => $ts->stop?->sequence,
                'drop_status' => $ts->drop_status,
                'dropped_off_at' => $ts->dropped_off_at?->toIso8601String(),
            ])->all(),
        ];
    }

    public function pickup(User $user, Trip $trip, array $validated): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, false);

        if ($trip->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'trip' => ['Trip must be in progress to mark pickup.'],
            ]);
        }

        $tripStudent = $this->drivers->findTripStudentInTrip($trip->id, (int) $validated['trip_student_id']);

        if (! $tripStudent) {
            throw ValidationException::withMessages([
                'trip_student_id' => ['Trip student does not belong to this trip.'],
            ]);
        }

        if ($tripStudent->pickup_status === 'picked_up') {
            throw ValidationException::withMessages([
                'trip_student_id' => ['Student already picked up.'],
            ]);
        }

        $tripStudent = $this->tripService->markPickup(
            $tripStudent,
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        );

        activity()->causedBy($user)->performedOn($tripStudent)->event('pickup')->log('Driver marked student pickup');

        return [
            'trip_student' => [
                'id' => $tripStudent->id,
                'student_id' => $tripStudent->student_id,
                'student_name' => $tripStudent->student?->full_name,
                'pickup_status' => $tripStudent->pickup_status,
                'picked_up_at' => $tripStudent->picked_up_at?->toIso8601String(),
            ],
        ];
    }

    public function drop(User $user, Trip $trip, array $validated): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, false);

        if ($trip->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'trip' => ['Trip must be in progress to mark drop.'],
            ]);
        }

        $tripStudent = $this->drivers->findTripStudentInTrip($trip->id, (int) $validated['trip_student_id']);

        if (! $tripStudent) {
            throw ValidationException::withMessages([
                'trip_student_id' => ['Trip student does not belong to this trip.'],
            ]);
        }

        if ($tripStudent->drop_status === 'dropped_off') {
            throw ValidationException::withMessages([
                'trip_student_id' => ['Student already dropped off.'],
            ]);
        }

        $tripStudent = $this->tripService->markDrop(
            $tripStudent,
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        );

        activity()->causedBy($user)->performedOn($tripStudent)->event('drop')->log('Driver marked student drop');

        return [
            'trip_student' => [
                'id' => $tripStudent->id,
                'student_id' => $tripStudent->student_id,
                'student_name' => $tripStudent->student?->full_name,
                'drop_status' => $tripStudent->drop_status,
                'dropped_off_at' => $tripStudent->dropped_off_at?->toIso8601String(),
            ],
        ];
    }

    public function updateLocation(User $user, array $validated): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');

        $vehicle = $this->drivers->findDriverVehicle($driver, (int) $validated['vehicle_id']);

        if (! $vehicle) {
            throw new AuthorizationException('Vehicle not assigned to this driver.', Response::HTTP_FORBIDDEN);
        }

        if (! empty($validated['trip_id'])) {
            $trip = $this->drivers->findTripForDriver($driver->id, (int) $validated['trip_id']);

            if (! $trip) {
                throw new AuthorizationException('Trip not assigned to this driver.', Response::HTTP_FORBIDDEN);
            }
        }

        $location = $this->drivers->createVehicleLocation([
            'vehicle_id' => $validated['vehicle_id'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'] ?? null,
            'heading' => $validated['heading'] ?? null,
            'captured_at' => $validated['captured_at'] ?? now(),
            'source' => 'driver_app',
        ]);

        LocationUpdated::dispatch(
            vehicleId: (int) $validated['vehicle_id'],
            latitude: (float) $validated['latitude'],
            longitude: (float) $validated['longitude'],
            speed: isset($validated['speed']) ? (float) $validated['speed'] : null,
            heading: isset($validated['heading']) ? (float) $validated['heading'] : null,
            capturedAt: $location->captured_at->toIso8601String(),
            extra: array_filter(['trip_id' => $validated['trip_id'] ?? null]),
        );

        if (! empty($validated['trip_id'])) {
            $this->drivers->createTripEvent([
                'school_id' => $driver->school_id,
                'trip_id' => $validated['trip_id'],
                'event_type' => 'location_update',
                'metadata' => [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'speed' => $validated['speed'] ?? null,
                    'heading' => $validated['heading'] ?? null,
                    'captured_at' => $location->captured_at->toIso8601String(),
                ],
            ]);
        }

        activity()
            ->causedBy($user)
            ->performedOn($vehicle)
            ->event('location_update')
            ->withProperties(['trip_id' => $validated['trip_id'] ?? null])
            ->log('Driver location update submitted');

        return [
            'location' => [
                'id' => $location->id,
                'vehicle_id' => $location->vehicle_id,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'speed' => $location->speed,
                'heading' => $location->heading,
                'captured_at' => $location->captured_at->toIso8601String(),
            ],
        ];
    }

    public function eta(User $user, Trip $trip, array $validated): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, true);

        $stops = $trip->route?->stops ?? collect();

        if ($stops->isEmpty()) {
            throw ValidationException::withMessages([
                'trip' => ['No stops found for this route.'],
            ]);
        }

        $etaData = $stops->map(function ($stop) use ($validated) {
            $stopLat = $stop->latitude ?? $validated['current_latitude'];
            $stopLng = $stop->longitude ?? $validated['current_longitude'];

            $distanceKm = $this->etaService->distanceBetween(
                (float) $validated['current_latitude'],
                (float) $validated['current_longitude'],
                (float) $stopLat,
                (float) $stopLng,
            );

            $distanceMeters = $distanceKm * 1000;
            $estimatedMinutes = $this->etaService->estimatedMinutes($distanceKm);
            $isNearby = $this->etaService->isWithinThreshold($distanceKm);

            return [
                'stop_id' => $stop->id,
                'stop_name' => $stop->stop_name,
                'sequence' => $stop->sequence,
                'distance_meters' => round($distanceMeters, 1),
                'distance_km' => round($distanceKm, 2),
                'estimated_minutes' => $estimatedMinutes,
                'is_nearby' => $isNearby,
            ];
        })->values()->all();

        return [
            'trip_id' => $trip->id,
            'current_location' => [
                'latitude' => (float) $validated['current_latitude'],
                'longitude' => (float) $validated['current_longitude'],
            ],
            'eta' => $etaData,
        ];
    }

    public function sos(User $user, array $validated): void
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');

        if (! empty($validated['trip_id'])) {
            $trip = $this->drivers->findTripForDriver($driver->id, (int) $validated['trip_id']);

            if (! $trip) {
                throw new AuthorizationException('Trip not assigned to this driver.', Response::HTTP_FORBIDDEN);
            }
        }

        $this->drivers->createTripEvent([
            'school_id' => $driver->school_id,
            'trip_id' => $validated['trip_id'] ?? null,
            'event_type' => 'sos_alert',
            'metadata' => [
                'driver_id' => $driver->id,
                'driver_name' => $driver->name,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'message' => $validated['message'] ?? null,
                'triggered_at' => now()->toIso8601String(),
            ],
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($driver)
            ->event('sos_alert')
            ->withProperties([
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'trip_id' => $validated['trip_id'] ?? null,
            ])
            ->log('Driver SOS alert sent');

        Log::warning('SOS ALERT from driver', [
            'driver_id' => $driver->id,
            'driver_name' => $driver->name,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'message' => $validated['message'] ?? null,
        ]);
    }

    private function resolveDriverForUser(User $user, string $permission): Driver
    {
        if (! $user->hasRole('Driver')) {
            throw new AuthorizationException('Only Driver role users can access this endpoint.', Response::HTTP_FORBIDDEN);
        }

        if (! $user->can($permission)) {
            throw new AuthorizationException('You do not have permission to access this endpoint.', Response::HTTP_FORBIDDEN);
        }

        $driver = $this->drivers->findDriverByUserId($user->id);

        if (! $driver) {
            throw new AuthorizationException('User is not a registered driver.', Response::HTTP_FORBIDDEN);
        }

        return $driver;
    }

    private function ensureDriverOwnsTrip(Driver $driver, Trip $trip, bool $withDetails): Trip
    {
        $ownedTrip = $withDetails
            ? $this->drivers->findTripForDriverWithDetails($driver->id, $trip->id)
            : $this->drivers->findTripForDriver($driver->id, $trip->id);

        if (! $ownedTrip) {
            throw new AuthorizationException('Unauthorized.', Response::HTTP_FORBIDDEN);
        }

        return $ownedTrip;
    }

    private function resolveSchoolId(User $user, ?int $headerSchoolId, ?int $inputSchoolId): ?int
    {
        $schoolId = (int) ($headerSchoolId ?? $inputSchoolId ?? 0);

        if ($schoolId <= 0) {
            $schoolId = $user->current_school_id;
        }

        if ($schoolId <= 0) {
            $primarySchool = $user->schools()?->wherePivot('is_primary', true)->first();
            $schoolId = $primarySchool?->id ?? 0;
        }

        return $schoolId > 0 ? $schoolId : null;
    }
}
