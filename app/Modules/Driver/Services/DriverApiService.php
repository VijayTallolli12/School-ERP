<?php

namespace App\Modules\Driver\Services;

use App\Core\Tenant\SchoolContext;
use App\Events\LocationUpdated;
use App\Http\Resources\Api\V1\DriverAuthResource;
use App\Models\Trip;
use App\Models\TripEvent;
use App\Models\TripStudent;
use App\Models\User;
use App\Modules\Driver\Repositories\DriverApiRepositoryInterface;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\SosAlert;
use App\Services\DriverDashboardService;
use App\Services\EtaService;
use App\Services\TripService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
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
        private readonly NotificationService $notificationService,
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
        $stopProgress = $this->deriveTripProgress($trip);

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
                'current_stop_id' => $stopProgress['current_stop_id'],
                'next_stop_id' => $stopProgress['next_stop_id'],
                'completed_stops' => $stopProgress['completed_stops'],
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
                'arrived_at' => $stopProgress['progress'][$stop->id]['arrived_at'] ?? null,
                'left_at' => $stopProgress['progress'][$stop->id]['left_at'] ?? null,
                'students' => ($studentsByStop->get($stop->id) ?? collect())->map(fn (TripStudent $ts) => $this->tripStudentPayload($ts))->values()->all(),
            ])->values()->all(),
            'students' => $trip->tripStudents->values()->map(fn (TripStudent $ts) => $this->tripStudentPayload($ts))->all(),
        ];
    }

    public function tripStart(User $user, Trip $trip): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, false);

        return $this->performTripStart($user, $driver, $trip);
    }

    public function tripStartById(User $user, array $validated): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');
        $trip = $this->drivers->findTripForDriver($driver->id, (int) $validated['trip_id']);

        if (! $trip) {
            throw new AuthorizationException('Trip not assigned to this driver.', Response::HTTP_FORBIDDEN);
        }

        return $this->performTripStart($user, $driver, $trip);
    }

    private function performTripStart(User $user, Driver $driver, Trip $trip): array
    {
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

    public function tripEnd(User $user, Trip $trip): array
    {
        return $this->tripComplete($user, $trip);
    }

    public function tripCurrent(User $user): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');
        $trip = $this->drivers->findCurrentTripForDriver($driver->id);

        if (! $trip) {
            return [
                'has_current_trip' => false,
                'trip' => null,
            ];
        }

        return $this->buildTripDetails($trip) + ['has_current_trip' => true];
    }

    public function routesToday(User $user): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');
        $trips = $this->drivers->getTodayTripsForDriver($driver->id);
        $tripByRoute = $trips->groupBy('route_id');
        $routes = $this->drivers->getRoutesForDriver($driver->id);

        return [
            'routes' => $routes->map(fn (Route $r) => [
                'route_id' => $r->id,
                'route_name' => $r->route_name,
                'start_point' => $r->start_point,
                'end_point' => $r->end_point,
                'distance' => $r->distance,
                'stops_count' => $r->stops->count(),
                'today_trips' => ($tripByRoute->get($r->id) ?? collect())->values()->map(fn (Trip $t) => [
                    'trip_id' => $t->id,
                    'type' => $t->type,
                    'status' => $t->status,
                    'total_students' => $t->total_students,
                    'started_at' => $t->started_at?->toIso8601String(),
                    'completed_at' => $t->completed_at?->toIso8601String(),
                ])->all(),
            ])->values()->all(),
        ];
    }

    public function routeShow(User $user, Route $route): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');
        $route = $this->ensureDriverOwnsRoute($driver, $route);

        return $this->buildRoutePayload($route);
    }

    public function routeStops(User $user, Route $route): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');
        $route = $this->ensureDriverOwnsRoute($driver, $route);

        $stops = $route->stops()->with('assignments.student')->get();

        return [
            'route_id' => $route->id,
            'route_name' => $route->route_name,
            'stops' => $stops->map(fn (RouteStop $s) => [
                'stop_id' => $s->id,
                'stop_name' => $s->stop_name,
                'latitude' => $s->latitude,
                'longitude' => $s->longitude,
                'sequence' => $s->sequence,
                'pickup_time' => $s->pickup_time?->format('H:i'),
                'drop_time' => $s->drop_time?->format('H:i'),
                'students_count' => $s->assignments->where('status', 'active')->count(),
            ])->values()->all(),
        ];
    }

    public function routeStudents(User $user, Route $route): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');
        $route = $this->ensureDriverOwnsRoute($driver, $route);

        $students = collect();
        foreach ($route->stops()->with('assignments.student')->get() as $stop) {
            foreach ($stop->assignments->where('status', 'active') as $assignment) {
                $student = $assignment->student;
                if (! $student) {
                    continue;
                }
                $students->push([
                    'student_id' => $assignment->student_id,
                    'name' => $student->full_name,
                    'class' => $this->studentClassName($student),
                    'stop_id' => $stop->id,
                    'stop_name' => $stop->stop_name,
                    'stop_sequence' => $stop->sequence,
                ]);
            }
        }

        return [
            'route_id' => $route->id,
            'route_name' => $route->route_name,
            'students' => $students->values()->all(),
        ];
    }

    public function tripHistory(User $user, ?string $from, ?string $to, int $perPage): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.view');
        $paginator = $this->drivers->getTripHistoryForDriver($driver->id, $from, $to, $perPage);

        return [
            'trips' => collect($paginator->items())->map(fn (Trip $t) => [
                'id' => $t->id,
                'trip_date' => $t->trip_date->format('Y-m-d'),
                'type' => $t->type,
                'status' => $t->status,
                'route_id' => $t->route_id,
                'route_name' => $t->route?->route_name,
                'vehicle_number' => $t->vehicle?->vehicle_number,
                'total_students' => $t->total_students,
                'picked_up_count' => $t->picked_up_count,
                'dropped_off_count' => $t->dropped_off_count,
                'started_at' => $t->started_at?->toIso8601String(),
                'completed_at' => $t->completed_at?->toIso8601String(),
            ])->values()->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function markAttendance(User $user, Trip $trip, array $validated): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, false);

        if ($trip->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'trip' => ['Trip must be in progress to mark attendance.'],
            ]);
        }

        $tripStudent = TripStudent::query()
            ->where('trip_id', $trip->id)
            ->where('student_id', (int) $validated['student_id'])
            ->first();

        if (! $tripStudent) {
            throw ValidationException::withMessages([
                'student_id' => ['Student is not part of this trip.'],
            ]);
        }

        return $this->markAction($user, $trip, $tripStudent, $validated['action'], $validated['latitude'] ?? null, $validated['longitude'] ?? null, $validated['request_id'] ?? null);
    }

    public function updateAttendance(User $user, Trip $trip, TripStudent $tripStudent, array $validated): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, false);

        $owned = $this->drivers->findTripStudentInTrip($trip->id, $tripStudent->id);
        if (! $owned) {
            throw new AuthorizationException('Student is not part of this trip.', Response::HTTP_FORBIDDEN);
        }

        if ($trip->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'trip' => ['Trip must be in progress to mark attendance.'],
            ]);
        }

        return $this->markAction($user, $trip, $owned, $validated['action'], $validated['latitude'] ?? null, $validated['longitude'] ?? null, $validated['request_id'] ?? null);
    }

    private function markAction(User $user, Trip $trip, TripStudent $tripStudent, string $action, ?float $latitude, ?float $longitude, ?string $requestId): array
    {
        $alreadyDone = $action === 'pickup'
            ? $tripStudent->pickup_status === 'picked_up'
            : $tripStudent->drop_status === 'dropped_off';

        // Idempotent: duplicate-safe attendance marking (offline retry safe) -> return current state.
        if ($alreadyDone) {
            return $this->tripStudentStatus($tripStudent);
        }

        $tripStudent = $action === 'pickup'
            ? $this->tripService->markPickup($tripStudent, $latitude, $longitude)
            : $this->tripService->markDrop($tripStudent, $latitude, $longitude);

        activity()
            ->causedBy($user)
            ->performedOn($tripStudent)
            ->withProperties(['request_id' => $requestId])
            ->event($action)
            ->log('Driver marked student '.$action);

        return $this->tripStudentStatus($tripStudent);
    }

    public function arriveStop(User $user, Trip $trip, array $validated): array
    {
        return $this->recordStop($user, $trip, $validated, 'stop_arrived');
    }

    public function leaveStop(User $user, Trip $trip, array $validated): array
    {
        return $this->recordStop($user, $trip, $validated, 'stop_left');
    }

    public function logout(User $user, array $validated): void
    {
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        } else {
            $user->tokens()->delete();
        }

        activity()->causedBy($user)->event('driver_logout')->log('Driver logged out');
    }

    public function me(User $user): array
    {
        return $this->profile($user);
    }

    public function notifications(User $user): array
    {
        $notifications = $user->appNotifications()
            ->with('creator:id,name')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $unreadCount = $user->appNotifications()
            ->wherePivot('is_read', false)
            ->count();

        return [
            'unread_count' => $unreadCount,
            'notifications' => $notifications->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'type' => $n->type,
                'priority' => $n->priority,
                'is_read' => (bool) ($n->pivot?->is_read ?? false),
                'sent_at' => $n->sent_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    public function markNotificationsRead(User $user, array $validated): array
    {
        $ids = $validated['ids'] ?? [];
        $readAll = (bool) ($validated['read_all'] ?? count($ids) === 0);

        if ($readAll) {
            $this->notificationService->markAllRead($user->id);
            $unreadCount = 0;
        } else {
            foreach ($ids as $id) {
                $notification = $user->appNotifications()->where('notifications.id', $id)->first();
                if ($notification) {
                    $this->notificationService->markRead($notification, $user->id);
                }
            }
            $unreadCount = $user->appNotifications()->wherePivot('is_read', false)->count();
        }

        return [
            'unread_count' => $unreadCount,
        ];
    }

    private function recordStop(User $user, Trip $trip, array $validated, string $eventType): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, false);

        if ($trip->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'trip' => ['Trip must be in progress to record stop events.'],
            ]);
        }

        $stop = RouteStop::query()->find((int) $validated['route_stop_id']);

        if (! $stop || $stop->route_id !== $trip->route_id) {
            throw ValidationException::withMessages([
                'route_stop_id' => ['Stop does not belong to this trip route.'],
            ]);
        }

        $this->drivers->createTripEvent([
            'school_id' => $driver->school_id,
            'trip_id' => $trip->id,
            'event_type' => $eventType,
            'metadata' => [
                'route_stop_id' => $stop->id,
                'stop_name' => $stop->stop_name,
                'sequence' => $stop->sequence,
                'latitude' => $validated['latitude'] ?? $stop->latitude,
                'longitude' => $validated['longitude'] ?? $stop->longitude,
                'request_id' => $validated['request_id'] ?? null,
                'at' => now()->toIso8601String(),
            ],
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($trip)
            ->event($eventType)
            ->withProperties(['route_stop_id' => $stop->id])
            ->log('Driver '.str_replace('_', ' ', $eventType));

        $students = $trip->tripStudents->filter(fn ($ts) => $ts->route_stop_id === $stop->id)->values();

        return [
            'stop' => [
                'stop_id' => $stop->id,
                'stop_name' => $stop->stop_name,
                'sequence' => $stop->sequence,
                'latitude' => isset($validated['latitude']) ? (float) $validated['latitude'] : (float) $stop->latitude,
                'longitude' => isset($validated['longitude']) ? (float) $validated['longitude'] : (float) $stop->longitude,
            ],
            'event' => $eventType,
            'recorded_at' => now()->toIso8601String(),
            'students' => $students->map(fn (TripStudent $ts) => $this->compactStudent($ts))->all(),
        ];
    }

    private function tripStudentStatus(TripStudent $tripStudent): array
    {
        return [
            'trip_student' => [
                'id' => $tripStudent->id,
                'student_id' => $tripStudent->student_id,
                'student_name' => $tripStudent->student?->full_name,
                'pickup_status' => $tripStudent->pickup_status,
                'drop_status' => $tripStudent->drop_status,
                'picked_up_at' => $tripStudent->picked_up_at?->toIso8601String(),
                'dropped_off_at' => $tripStudent->dropped_off_at?->toIso8601String(),
            ],
        ];
    }

    private function compactStudent(TripStudent $ts): array
    {
        return [
            'id' => $ts->id,
            'student_id' => $ts->student_id,
            'name' => $ts->student?->full_name,
            'pickup_status' => $ts->pickup_status,
            'drop_status' => $ts->drop_status,
        ];
    }

    private function buildRoutePayload(Route $route): array
    {
        $stops = $route->stops()->with('assignments.student')->get();

        return [
            'route_id' => $route->id,
            'route_name' => $route->route_name,
            'start_point' => $route->start_point,
            'end_point' => $route->end_point,
            'distance' => $route->distance,
            'vehicle' => $route->vehicle ? [
                'id' => $route->vehicle->id,
                'vehicle_number' => $route->vehicle->vehicle_number,
                'vehicle_name' => $route->vehicle->vehicle_name,
            ] : null,
            'driver' => $route->driver ? [
                'id' => $route->driver->id,
                'name' => $route->driver->name,
                'mobile' => $route->driver->mobile,
            ] : null,
            'stops' => $stops->map(fn (RouteStop $s) => [
                'stop_id' => $s->id,
                'stop_name' => $s->stop_name,
                'latitude' => $s->latitude,
                'longitude' => $s->longitude,
                'sequence' => $s->sequence,
                'pickup_time' => $s->pickup_time?->format('H:i'),
                'drop_time' => $s->drop_time?->format('H:i'),
                'students_count' => $s->assignments->where('status', 'active')->count(),
            ])->values()->all(),
        ];
    }

    private function tripStudentPayload(TripStudent $ts): array
    {
        return [
            'id' => $ts->id,
            'trip_student_id' => $ts->id,
            'student_id' => $ts->student_id,
            'name' => $ts->student?->full_name,
            'class' => $this->studentClassName($ts->student),
            'route_stop_id' => $ts->route_stop_id,
            'stop_name' => $ts->stop?->stop_name,
            'stop_sequence' => $ts->stop?->sequence,
            'pickup_status' => $ts->pickup_status,
            'drop_status' => $ts->drop_status,
            'picked_up_at' => $ts->picked_up_at?->toIso8601String(),
            'dropped_off_at' => $ts->dropped_off_at?->toIso8601String(),
        ];
    }

    /**
     * Derive per-stop progress for a trip from recorded stop_arrived /
     * stop_left events. This is the authoritative source of truth for where
     * the driver currently is, and survives app reloads / device switches.
     */
    private function deriveTripProgress(Trip $trip): array
    {
        $stops = $trip->route?->stops ?? collect();
        $events = TripEvent::query()
            ->where('trip_id', $trip->id)
            ->whereIn('event_type', ['stop_arrived', 'stop_left'])
            ->orderBy('id')
            ->get();

        $progress = [];
        foreach ($events as $event) {
            $stopId = (int) ($event->metadata['route_stop_id'] ?? 0);
            if ($stopId <= 0) {
                continue;
            }
            $stamp = $event->created_at?->toIso8601String();
            if ($event->event_type === 'stop_left') {
                $progress[$stopId]['left_at'] = $stamp;
                $progress[$stopId]['arrived_at'] ??= $stamp;
            } else {
                $progress[$stopId]['arrived_at'] ??= $stamp;
            }
        }

        $ordered = $stops->sortBy('sequence')->values();
        $current = $ordered->first(fn ($stop) => isset($progress[$stop->id]['arrived_at']) && ! isset($progress[$stop->id]['left_at']));
        $currentId = $current?->id;
        $nextId = null;
        if ($currentId !== null) {
            $idx = $ordered->search(fn ($stop) => $stop->id === $currentId);
            $nextId = $idx !== false && isset($ordered[$idx + 1]) ? $ordered[$idx + 1]->id : null;
        }

        return [
            'progress' => $progress,
            'current_stop_id' => $currentId,
            'next_stop_id' => $nextId,
            'completed_stops' => $stops->filter(fn ($stop) => isset($progress[$stop->id]['left_at']))->count(),
        ];
    }

    private function buildTripDetails(Trip $trip): array
    {
        $stopProgress = $this->deriveTripProgress($trip);

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
                'current_stop_id' => $stopProgress['current_stop_id'],
                'next_stop_id' => $stopProgress['next_stop_id'],
                'completed_stops' => $stopProgress['completed_stops'],
            ],
            'route' => [
                'route_id' => $trip->route?->id,
                'route_name' => $trip->route?->route_name,
                'start_point' => $trip->route?->start_point,
                'end_point' => $trip->route?->end_point,
                'total_stops' => $trip->route?->stops?->count() ?? 0,
            ],
            'vehicle' => $trip->vehicle ? [
                'id' => $trip->vehicle->id,
                'vehicle_number' => $trip->vehicle->vehicle_number,
                'vehicle_name' => $trip->vehicle->vehicle_name,
            ] : null,
            'students' => $trip->tripStudents->values()->map(fn (TripStudent $ts) => $this->tripStudentPayload($ts))->all(),
            'stops' => ($trip->route?->stops ?? collect())->map(fn (RouteStop $s) => [
                'stop_id' => $s->id,
                'stop_name' => $s->stop_name,
                'sequence' => $s->sequence,
                'arrived_at' => $stopProgress['progress'][$s->id]['arrived_at'] ?? null,
                'left_at' => $stopProgress['progress'][$s->id]['left_at'] ?? null,
                'students' => $trip->tripStudents->where('route_stop_id', $s->id)->values()->map(fn (TripStudent $ts) => $this->tripStudentPayload($ts))->all(),
            ])->values()->all(),
        ];
    }

    private function ensureDriverOwnsRoute(Driver $driver, Route $route): Route
    {
        $owned = $this->drivers->findRouteForDriver($driver->id, $route->id);

        if (! $owned) {
            throw new AuthorizationException('Unauthorized.', Response::HTTP_FORBIDDEN);
        }

        return $owned;
    }

    private function studentClassName($student): ?string
    {
        try {
            $session = $student->sessions()->where('status', 'active')->latest()->first();

            return $session?->classSection?->schoolClass?->name
                ?? $session?->class_section?->schoolClass?->name;
        } catch (\Throwable) {
            return null;
        }
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

        // Idempotent (offline-retry safe): an already-picked-up student returns
        // the current state instead of erroring or writing a duplicate event.
        if ($tripStudent->pickup_status === 'picked_up') {
            return $this->tripStudentStatus($tripStudent);
        }

        $tripStudent = $this->tripService->markPickup(
            $tripStudent,
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        );

        activity()->causedBy($user)->performedOn($tripStudent)->event('pickup')->log('Driver marked student pickup');

        return $this->tripStudentStatus($tripStudent);
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

        // Idempotent (offline-retry safe): an already-dropped-off student returns
        // the current state instead of erroring or writing a duplicate event.
        if ($tripStudent->drop_status === 'dropped_off') {
            return $this->tripStudentStatus($tripStudent);
        }

        $tripStudent = $this->tripService->markDrop(
            $tripStudent,
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        );

        activity()->causedBy($user)->performedOn($tripStudent)->event('drop')->log('Driver marked student drop');

        return $this->tripStudentStatus($tripStudent);
    }

    public function markMissed(User $user, Trip $trip, array $validated): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, false);

        if ($trip->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'trip' => ['Trip must be in progress to mark a student as missed.'],
            ]);
        }

        $tripStudent = $this->drivers->findTripStudentInTrip($trip->id, (int) $validated['trip_student_id']);

        if (! $tripStudent) {
            throw ValidationException::withMessages([
                'trip_student_id' => ['Trip student does not belong to this trip.'],
            ]);
        }

        $status = $trip->type === 'drop' ? $tripStudent->drop_status : $tripStudent->pickup_status;

        // Idempotent (offline-retry safe): already missed returns the current state.
        if ($status === 'missed') {
            return $this->tripStudentStatus($tripStudent);
        }

        if ($status === 'picked_up' || $status === 'dropped_off') {
            throw ValidationException::withMessages([
                'trip_student_id' => [ucfirst($trip->type === 'drop' ? 'drop already recorded' : 'pickup already recorded').'.'],
            ]);
        }

        $tripStudent = $this->tripService->markMissed(
            $tripStudent,
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        );

        activity()->causedBy($user)->performedOn($tripStudent)->event('missed')->log('Driver marked student missed');

        return $this->tripStudentStatus($tripStudent);
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

    /**
     * Batch live-location recording scoped to a trip. Accepts the driver app's
     * format: { locations: [{ lat, lng, speed, heading, accuracy, timestamp }] }
     * or a single flat point. vehicle/trip are derived from the route binding.
     */
    public function updateTripLocation(User $user, Trip $trip, array $validated): array
    {
        $driver = $this->resolveDriverForUser($user, 'transport.update');
        $trip = $this->ensureDriverOwnsTrip($driver, $trip, false);

        $vehicleId = $trip->vehicle_id;

        if ($vehicleId === null) {
            throw new AuthorizationException('Trip has no assigned vehicle.', Response::HTTP_FORBIDDEN);
        }

        $vehicle = $this->drivers->findDriverVehicle($driver, (int) $vehicleId);

        if (! $vehicle) {
            throw new AuthorizationException('Vehicle not assigned to this driver.', Response::HTTP_FORBIDDEN);
        }

        $points = $validated['locations']
            ?? [array_intersect_key($validated, array_flip(['lat', 'lng', 'speed', 'heading', 'accuracy', 'timestamp']))];

        $received = count($points);
        $latest = null;

        foreach ($points as $point) {
            $latest = $this->drivers->createVehicleLocation([
                'vehicle_id' => $vehicleId,
                'latitude' => (float) $point['lat'],
                'longitude' => (float) $point['lng'],
                'speed' => isset($point['speed']) ? (float) $point['speed'] : null,
                'heading' => isset($point['heading']) ? (float) $point['heading'] : null,
                'captured_at' => ! empty($point['timestamp']) ? $point['timestamp'] : now(),
                'source' => 'driver_app',
            ]);

            $this->drivers->createTripEvent([
                'school_id' => $driver->school_id,
                'trip_id' => $trip->id,
                'event_type' => 'location_update',
                'metadata' => [
                    'latitude' => (float) $point['lat'],
                    'longitude' => (float) $point['lng'],
                    'speed' => isset($point['speed']) ? (float) $point['speed'] : null,
                    'heading' => isset($point['heading']) ? (float) $point['heading'] : null,
                    'accuracy' => isset($point['accuracy']) ? (float) $point['accuracy'] : null,
                    'captured_at' => $latest->captured_at->toIso8601String(),
                ],
            ]);
        }

        if ($latest) {
            LocationUpdated::dispatch(
                vehicleId: (int) $vehicleId,
                latitude: (float) $latest->latitude,
                longitude: (float) $latest->longitude,
                speed: $latest->speed ? (float) $latest->speed : null,
                heading: $latest->heading ? (float) $latest->heading : null,
                capturedAt: $latest->captured_at->toIso8601String(),
                extra: array_filter(['trip_id' => $trip->id]),
            );
        }

        return [
            'received' => $received,
            'trip_id' => $trip->id,
            'latest' => $latest ? [
                'latitude' => (float) $latest->latitude,
                'longitude' => (float) $latest->longitude,
                'captured_at' => $latest->captured_at->toIso8601String(),
            ] : null,
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

        SosAlert::query()->create([
            'driver_id' => $driver->id,
            'trip_id' => $validated['trip_id'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => 'new',
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
