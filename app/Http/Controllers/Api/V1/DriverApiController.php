<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Trip;
use App\Http\Resources\Api\V1\DriverDashboardResource;
use App\Http\Resources\Api\V1\DriverEtaResource;
use App\Http\Resources\Api\V1\DriverLocationResource;
use App\Http\Resources\Api\V1\DriverProfileResource;
use App\Http\Resources\Api\V1\DriverTripDetailsResource;
use App\Http\Resources\Api\V1\DriverTripsResource;
use App\Http\Resources\Api\V1\DriverTripStatusResource;
use App\Http\Resources\Api\V1\DriverTripStudentStatusResource;
use App\Http\Resources\Api\V1\DriverTripStudentsResource;
use App\Modules\Driver\Requests\DriverLogoutRequest;
use App\Modules\Driver\Requests\DriverReadAccessRequest;
use App\Modules\Driver\Requests\DriverLoginRequest;
use App\Modules\Driver\Requests\DriverTripActionRequest;
use App\Modules\Driver\Requests\EtaRequest;
use App\Modules\Driver\Requests\MarkAttendanceRequest;
use App\Modules\Driver\Requests\MarkNotificationsReadRequest;
use App\Modules\Driver\Requests\MarkTripStudentRequest;
use App\Modules\Driver\Requests\SosAlertRequest;
use App\Modules\Driver\Requests\StopArrivalRequest;
use App\Modules\Driver\Requests\TripStartRequest;
use App\Modules\Driver\Requests\UpdateAttendanceRequest;
use App\Modules\Driver\Requests\UpdateDriverLocationRequest;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Driver\Services\DriverApiService;
use App\Models\TripStudent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverApiController extends ApiBaseController
{
    public function __construct(
        private readonly DriverApiService $service,
    ) {}

    public function login(DriverLoginRequest $request): JsonResponse
    {
        $schoolHeader = (int) $request->header('X-School-Id', 0);
        $payload = $this->service->login($request->validated(), $schoolHeader > 0 ? $schoolHeader : null);

        return $this->success($payload, 'Driver logged in successfully.');
    }

    public function dashboard(DriverReadAccessRequest $request): JsonResponse
    {
        $data = $this->service->dashboard($request->user());

        return $this->success(DriverDashboardResource::make($data)->resolve(), 'Driver dashboard retrieved.');
    }

    public function profile(DriverReadAccessRequest $request): JsonResponse
    {
        $data = $this->service->profile($request->user());

        return $this->success(DriverProfileResource::make($data)->resolve(), 'Driver profile retrieved.');
    }

    public function tripsToday(DriverReadAccessRequest $request): JsonResponse
    {
        $data = $this->service->tripsToday($request->user());

        return $this->success(DriverTripsResource::make($data)->resolve(), 'Today\'s trips retrieved.');
    }

    public function tripShow(DriverReadAccessRequest $request, Trip $trip): JsonResponse
    {
        $data = $this->service->tripShow($request->user(), $trip);

        return $this->success(DriverTripDetailsResource::make($data)->resolve(), 'Trip details retrieved.');
    }

    public function tripStart(DriverTripActionRequest $request, Trip $trip): JsonResponse
    {
        $data = $this->service->tripStart($request->user(), $trip);

        return $this->success(DriverTripStatusResource::make($data)->resolve(), 'Trip started successfully.');
    }

    public function tripComplete(DriverTripActionRequest $request, Trip $trip): JsonResponse
    {
        $data = $this->service->tripComplete($request->user(), $trip);

        return $this->success(DriverTripStatusResource::make($data)->resolve(), 'Trip completed successfully.');
    }

    public function tripStudents(DriverReadAccessRequest $request, Trip $trip): JsonResponse
    {
        $data = $this->service->tripStudents($request->user(), $trip);

        return $this->success(DriverTripStudentsResource::make($data)->resolve(), 'Trip students retrieved.');
    }

    public function pickup(MarkTripStudentRequest $request, Trip $trip): JsonResponse
    {
        $data = $this->service->pickup($request->user(), $trip, $request->validated());

        return $this->success(DriverTripStudentStatusResource::make($data)->resolve(), 'Student pickup confirmed.');
    }

    public function drop(MarkTripStudentRequest $request, Trip $trip): JsonResponse
    {
        $data = $this->service->drop($request->user(), $trip, $request->validated());

        return $this->success(DriverTripStudentStatusResource::make($data)->resolve(), 'Student drop confirmed.');
    }

    public function updateLocation(UpdateDriverLocationRequest $request): JsonResponse
    {
        $data = $this->service->updateLocation($request->user(), $request->validated());

        return $this->success(DriverLocationResource::make($data)->resolve(), 'Location updated successfully.');
    }

    public function eta(EtaRequest $request, Trip $trip): JsonResponse
    {
        $data = $this->service->eta($request->user(), $trip, $request->validated());

        return $this->success(DriverEtaResource::make($data)->resolve(), 'ETA retrieved successfully.');
    }

    public function sos(SosAlertRequest $request): JsonResponse
    {
        $this->service->sos($request->user(), $request->validated());

        return $this->success(null, 'SOS alert sent successfully.');
    }

    // ─── Auth ─────────────────────────────────────────────────────────

    public function logout(DriverLogoutRequest $request): JsonResponse
    {
        $this->service->logout($request->user(), $request->validated());

        return $this->success(null, 'Driver logged out successfully.');
    }

    public function me(DriverReadAccessRequest $request): JsonResponse
    {
        $data = $this->service->me($request->user());

        return $this->success(DriverProfileResource::make($data)->resolve(), 'Driver profile retrieved.');
    }

    // ─── Routes ───────────────────────────────────────────────────────

    public function routesToday(DriverReadAccessRequest $request): JsonResponse
    {
        $data = $this->service->routesToday($request->user());

        return $this->success($data, 'Today\'s routes retrieved.');
    }

    public function routeShow(DriverReadAccessRequest $request, Route $route): JsonResponse
    {
        $data = $this->service->routeShow($request->user(), $route);

        return $this->success($data, 'Route details retrieved.');
    }

    public function routeStops(DriverReadAccessRequest $request, Route $route): JsonResponse
    {
        $data = $this->service->routeStops($request->user(), $route);

        return $this->success($data, 'Route stops retrieved.');
    }

    public function routeStudents(DriverReadAccessRequest $request, Route $route): JsonResponse
    {
        $data = $this->service->routeStudents($request->user(), $route);

        return $this->success($data, 'Route students retrieved.');
    }

    // ─── Trip Control ─────────────────────────────────────────────────

    public function tripStartById(TripStartRequest $request): JsonResponse
    {
        $data = $this->service->tripStartById($request->user(), $request->validated());

        return $this->success(DriverTripStatusResource::make($data)->resolve(), 'Trip started successfully.');
    }

    public function tripEnd(DriverTripActionRequest $request, Trip $trip): JsonResponse
    {
        $data = $this->service->tripEnd($request->user(), $trip);

        return $this->success(DriverTripStatusResource::make($data)->resolve(), 'Trip completed successfully.');
    }

    public function tripCurrent(DriverReadAccessRequest $request): JsonResponse
    {
        $data = $this->service->tripCurrent($request->user());

        return $this->success($data, 'Current trip retrieved.');
    }

    // ─── Attendance ───────────────────────────────────────────────────

    public function markAttendance(MarkAttendanceRequest $request, Trip $trip): JsonResponse
    {
        $data = $this->service->markAttendance($request->user(), $trip, $request->validated());

        return $this->success($data, 'Student attendance marked.');
    }

    public function updateAttendance(UpdateAttendanceRequest $request, Trip $trip, TripStudent $tripStudent): JsonResponse
    {
        $data = $this->service->updateAttendance($request->user(), $trip, $tripStudent, $request->validated());

        return $this->success($data, 'Student attendance updated.');
    }

    // ─── Stop Flow ────────────────────────────────────────────────────

    public function arriveStop(StopArrivalRequest $request, Trip $trip): JsonResponse
    {
        $data = $this->service->arriveStop($request->user(), $trip, $request->validated());

        return $this->success($data, 'Stop arrival recorded.');
    }

    public function leaveStop(StopArrivalRequest $request, Trip $trip): JsonResponse
    {
        $data = $this->service->leaveStop($request->user(), $trip, $request->validated());

        return $this->success($data, 'Stop departure recorded.');
    }

    // ─── Notifications ────────────────────────────────────────────────

    public function notifications(DriverReadAccessRequest $request): JsonResponse
    {
        $data = $this->service->notifications($request->user());

        return $this->success($data, 'Driver notifications retrieved.');
    }

    public function markNotificationsRead(MarkNotificationsReadRequest $request): JsonResponse
    {
        $data = $this->service->markNotificationsRead($request->user(), $request->validated());

        return $this->success($data, 'Notifications marked as read.');
    }

    // ─── History ──────────────────────────────────────────────────────

    public function tripsHistory(DriverReadAccessRequest $request): JsonResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $data = $this->service->tripHistory(
            $request->user(),
            $request->input('from'),
            $request->input('to'),
            $request->integer('per_page', 15)
        );

        return $this->success($data, 'Trip history retrieved.');
    }
}
