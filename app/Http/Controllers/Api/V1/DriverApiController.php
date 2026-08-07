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
use App\Modules\Driver\Requests\DriverReadAccessRequest;
use App\Modules\Driver\Requests\DriverLoginRequest;
use App\Modules\Driver\Requests\DriverTripActionRequest;
use App\Modules\Driver\Requests\EtaRequest;
use App\Modules\Driver\Requests\MarkTripStudentRequest;
use App\Modules\Driver\Requests\SosAlertRequest;
use App\Modules\Driver\Requests\UpdateDriverLocationRequest;
use App\Modules\Driver\Services\DriverApiService;
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
}
