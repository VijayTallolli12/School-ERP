<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenant\SchoolContext;
use App\Events\LocationUpdated;
use App\Models\TripEvent;
use App\Models\User;
use App\Models\VehicleLocation;
use App\Models\Trip;
use App\Models\TripStudent;
use App\Modules\Transport\Models\Driver;
use App\Notifications\EmergencyAlert;
use App\Services\DriverDashboardService;
use App\Services\EtaService;
use App\Services\TripService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class DriverApiController extends ApiBaseController
{
    public function __construct(
        private readonly DriverDashboardService $dashboardService,
        private readonly TripService $tripService,
        private readonly EtaService $etaService,
    ) {}

    private function resolveDriver(Request $request): Driver
    {
        $user = $request->user();
        $driver = Driver::query()->where('user_id', $user->id)->first();

        if (!$driver) {
            abort(Response::HTTP_FORBIDDEN, 'User is not a registered driver.');
        }

        return $driver;
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->status !== 'active') {
            return $this->error('Account is not active.', Response::HTTP_FORBIDDEN);
        }

        $schoolId = $this->resolveSchoolId($request, $user);
        app(SchoolContext::class)->set($schoolId);
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

        $user->load('roles');
        $abilities = $user->getAllPermissions()->pluck('name')->values()->all();
        $token = $user->createToken(
            $request->input('device_name', 'driver-app'),
            $abilities ?: ['transport.location.update']
        );

        $driver = Driver::query()
            ->where('user_id', $user->id)
            ->with('vehicles', 'routes')
            ->first();

        $response = [
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'school_id' => $schoolId,
            'driver' => $driver ? [
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
                ]),
            ] : null,
        ];

        return $this->success($response, 'Driver logged in successfully.');
    }

    public function dashboard(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        $data = $this->dashboardService->dashboard($driver);

        return $this->success($data, 'Driver dashboard retrieved.');
    }

    public function profile(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);
        $driver->load(['vehicles', 'routes.stops']);

        $vehicle = $driver->vehicles->first();
        $route = $driver->routes->first();

        return $this->success([
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
        ], 'Driver profile retrieved.');
    }

    public function tripsToday(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);

        $trips = Trip::query()
            ->where('driver_id', $driver->id)
            ->where('trip_date', now()->startOfDay())
            ->with(['route', 'vehicle'])
            ->orderBy('created_at')
            ->get();

        return $this->success([
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
        ], 'Today\'s trips retrieved.');
    }

    public function tripShow(Request $request, Trip $trip): JsonResponse
    {
        $driver = $this->resolveDriver($request);

        if ($trip->driver_id !== $driver->id) {
            return $this->error('Unauthorized.', Response::HTTP_FORBIDDEN);
        }

        $trip->load(['route.stops', 'vehicle', 'tripStudents.student', 'tripStudents.stop']);

        $stops = $trip->route?->stops ?? collect();
        $studentsByStop = $trip->tripStudents->groupBy('route_stop_id');

        return $this->success([
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
        ], 'Trip details retrieved.');
    }

    public function tripStart(Request $request, Trip $trip): JsonResponse
    {
        $driver = $this->resolveDriver($request);

        if ($trip->driver_id !== $driver->id) {
            return $this->error('Unauthorized.', Response::HTTP_FORBIDDEN);
        }

        if ($trip->status !== 'scheduled') {
            return $this->error('Trip can only be started from scheduled status.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $trip = $this->tripService->startTrip($trip);

        return $this->success([
            'trip' => [
                'id' => $trip->id,
                'status' => $trip->status,
                'started_at' => $trip->started_at?->toIso8601String(),
            ],
        ], 'Trip started successfully.');
    }

    public function tripComplete(Request $request, Trip $trip): JsonResponse
    {
        $driver = $this->resolveDriver($request);

        if ($trip->driver_id !== $driver->id) {
            return $this->error('Unauthorized.', Response::HTTP_FORBIDDEN);
        }

        if ($trip->status !== 'in_progress') {
            return $this->error('Trip can only be completed from in_progress status.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $trip = $this->tripService->completeTrip($trip);

        return $this->success([
            'trip' => [
                'id' => $trip->id,
                'status' => $trip->status,
                'completed_at' => $trip->completed_at?->toIso8601String(),
            ],
        ], 'Trip completed successfully.');
    }

    public function tripStudents(Request $request, Trip $trip): JsonResponse
    {
        $driver = $this->resolveDriver($request);

        if ($trip->driver_id !== $driver->id) {
            return $this->error('Unauthorized.', Response::HTTP_FORBIDDEN);
        }

        $trip->load(['tripStudents.student', 'tripStudents.stop']);
        $tripStudents = $trip->tripStudents;

        $pickupOrder = $tripStudents->sortBy(fn (TripStudent $ts) => $ts->stop?->sequence);
        $dropOrder = $tripStudents->sortByDesc(fn (TripStudent $ts) => $ts->stop?->sequence);

        return $this->success([
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
        ], 'Trip students retrieved.');
    }

    public function pickup(Request $request, Trip $trip): JsonResponse
    {
        $driver = $this->resolveDriver($request);

        if ($trip->driver_id !== $driver->id) {
            return $this->error('Unauthorized.', Response::HTTP_FORBIDDEN);
        }

        if ($trip->status !== 'in_progress') {
            return $this->error('Trip must be in progress to mark pickup.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'trip_student_id' => ['required', 'integer', 'exists:trip_students,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $tripStudent = TripStudent::query()
            ->where('id', $validated['trip_student_id'])
            ->where('trip_id', $trip->id)
            ->firstOrFail();

        if ($tripStudent->pickup_status === 'picked_up') {
            return $this->error('Student already picked up.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tripStudent = $this->tripService->markPickup(
            $tripStudent,
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        );

        return $this->success([
            'trip_student' => [
                'id' => $tripStudent->id,
                'student_id' => $tripStudent->student_id,
                'student_name' => $tripStudent->student?->full_name,
                'pickup_status' => $tripStudent->pickup_status,
                'picked_up_at' => $tripStudent->picked_up_at?->toIso8601String(),
            ],
        ], 'Student pickup confirmed.');
    }

    public function drop(Request $request, Trip $trip): JsonResponse
    {
        $driver = $this->resolveDriver($request);

        if ($trip->driver_id !== $driver->id) {
            return $this->error('Unauthorized.', Response::HTTP_FORBIDDEN);
        }

        if ($trip->status !== 'in_progress') {
            return $this->error('Trip must be in progress to mark drop.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'trip_student_id' => ['required', 'integer', 'exists:trip_students,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $tripStudent = TripStudent::query()
            ->where('id', $validated['trip_student_id'])
            ->where('trip_id', $trip->id)
            ->firstOrFail();

        if ($tripStudent->drop_status === 'dropped_off') {
            return $this->error('Student already dropped off.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tripStudent = $this->tripService->markDrop(
            $tripStudent,
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        );

        return $this->success([
            'trip_student' => [
                'id' => $tripStudent->id,
                'student_id' => $tripStudent->student_id,
                'student_name' => $tripStudent->student?->full_name,
                'drop_status' => $tripStudent->drop_status,
                'dropped_off_at' => $tripStudent->dropped_off_at?->toIso8601String(),
            ],
        ], 'Student drop confirmed.');
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'captured_at' => ['nullable', 'date'],
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
        ]);

        $vehicle = $driver->vehicles()->where('id', $validated['vehicle_id'])->first();

        if (!$vehicle) {
            return $this->error('Vehicle not assigned to this driver.', Response::HTTP_FORBIDDEN);
        }

        $location = VehicleLocation::query()->create([
            'vehicle_id' => $validated['vehicle_id'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'] ?? null,
            'heading' => $validated['heading'] ?? null,
            'captured_at' => $validated['captured_at'] ?? now(),
            'source' => 'driver_app',
        ]);

        LocationUpdated::dispatch(
            vehicleId: $validated['vehicle_id'],
            latitude: (float) $validated['latitude'],
            longitude: (float) $validated['longitude'],
            speed: isset($validated['speed']) ? (float) $validated['speed'] : null,
            heading: isset($validated['heading']) ? (float) $validated['heading'] : null,
            capturedAt: $location->captured_at->toIso8601String(),
            extra: array_filter(['trip_id' => $validated['trip_id'] ?? null]),
        );

        if ($validated['trip_id'] ?? null) {
            TripEvent::query()->create([
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

        return $this->success([
            'location' => [
                'id' => $location->id,
                'vehicle_id' => $location->vehicle_id,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'speed' => $location->speed,
                'heading' => $location->heading,
                'captured_at' => $location->captured_at->toIso8601String(),
            ],
        ], 'Location updated successfully.');
    }

    public function eta(Request $request, Trip $trip): JsonResponse
    {
        $driver = $this->resolveDriver($request);

        if ($trip->driver_id !== $driver->id) {
            return $this->error('Unauthorized.', Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'current_latitude' => ['required', 'numeric', 'between:-90,90'],
            'current_longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $trip->load('route.stops');

        $stops = $trip->route?->stops ?? collect();

        if ($stops->isEmpty()) {
            return $this->error('No stops found for this route.', Response::HTTP_NOT_FOUND);
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

        return $this->success([
            'trip_id' => $trip->id,
            'current_location' => [
                'latitude' => (float) $validated['current_latitude'],
                'longitude' => (float) $validated['current_longitude'],
            ],
            'eta' => $etaData,
        ], 'ETA retrieved successfully.');
    }

    public function sos(Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($request);

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'message' => ['nullable', 'string', 'max:500'],
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
        ]);

        TripEvent::query()->create([
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

        Log::warning('SOS ALERT from driver', [
            'driver_id' => $driver->id,
            'driver_name' => $driver->name,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'message' => $validated['message'] ?? null,
        ]);

        return $this->success(null, 'SOS alert sent successfully.');
    }

    private function resolveSchoolId(Request $request, User $user): int
    {
        $schoolId = (int) $request->header('X-School-Id', $request->input('school_id', 0));

        if ($schoolId <= 0) {
            $schoolId = $user->current_school_id;
        }

        if ($schoolId <= 0) {
            $primarySchool = $user->schools()?->wherePivot('is_primary', true)->first();
            $schoolId = $primarySchool?->id ?? 0;
        }

        return $schoolId > 0 ? $schoolId : 1;
    }
}
