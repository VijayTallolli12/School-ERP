<?php

namespace App\Modules\Transport\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\User;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;
use App\Modules\Transport\Repositories\TransportRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class TransportService
{
    public function __construct(private readonly TransportRepositoryInterface $transport) {}

    public function createVehicle(array $data): Vehicle
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $vehicle = $this->transport->createVehicle($data);
        activity()->causedBy(auth()->user())->performedOn($vehicle)->event('created')->log('Vehicle created');

        return $vehicle;
    }

    public function updateVehicle(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle = $this->transport->updateVehicle($vehicle, $data);
        activity()->causedBy(auth()->user())->performedOn($vehicle)->event('updated')->log('Vehicle updated');

        return $vehicle;
    }

    public function createDriver(array $data): Driver
    {
        return DB::transaction(function () use ($data): Driver {
            $schoolId = app(SchoolContext::class)->id();
            $data['school_id'] = $schoolId;
            $user = $this->createOrUpdateLoginUser(null, $data, $schoolId);
            $data['user_id'] = $user?->id;

            $driver = $this->transport->createDriver($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($driver)
                ->event('created')
                ->withProperties(['login_enabled' => $user !== null])
                ->log('Driver created');

            return $driver;
        });
    }

    public function updateDriver(Driver $driver, array $data): Driver
    {
        return DB::transaction(function () use ($driver, $data): Driver {
            $schoolId = app(SchoolContext::class)->id();
            $user = $this->createOrUpdateLoginUser($driver, $data, $schoolId);

            $data['school_id'] = $schoolId;
            $data['user_id'] = $user?->id ?? $driver->user_id;

            $driver = $this->transport->updateDriver($driver, $data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($driver)
                ->event('updated')
                ->withProperties(['login_enabled' => $data['user_id'] !== null])
                ->log('Driver updated');

            return $driver;
        });
    }

    public function resetDriverPassword(Driver $driver, array $data): void
    {
        $user = $driver->user;

        if (! $user) {
            throw ValidationException::withMessages([
                'driver' => ['This driver does not have a login account yet. Enable Driver Login to create one.'],
            ]);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($driver)
            ->event('password_reset')
            ->log('Driver password reset');
    }

    public function createRoute(array $data): Route
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $route = $this->transport->createRoute($data);
        activity()->causedBy(auth()->user())->performedOn($route)->event('created')->log('Route created');

        return $route;
    }

    public function updateRoute(Route $route, array $data): Route
    {
        $route = $this->transport->updateRoute($route, $data);
        activity()->causedBy(auth()->user())->performedOn($route)->event('updated')->log('Route updated');

        return $route;
    }

    public function createRouteStop(array $data): RouteStop
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $stop = $this->transport->createRouteStop($data);
        activity()->causedBy(auth()->user())->performedOn($stop)->event('created')->log('Route stop created');

        return $stop;
    }

    public function updateRouteStop(RouteStop $routeStop, array $data): RouteStop
    {
        $stop = $this->transport->updateRouteStop($routeStop, $data);
        activity()->causedBy(auth()->user())->performedOn($stop)->event('updated')->log('Route stop updated');

        return $stop;
    }

    public function createAssignment(array $data): TransportAssignment
    {
        return DB::transaction(function () use ($data): TransportAssignment {
            $data['school_id'] = app(SchoolContext::class)->id();
            $assignment = $this->transport->createAssignment($data);
            activity()->causedBy(auth()->user())->performedOn($assignment)->event('created')->log('Transport assignment created');

            return $assignment;
        });
    }

    public function updateAssignment(TransportAssignment $assignment, array $data): TransportAssignment
    {
        return DB::transaction(function () use ($assignment, $data): TransportAssignment {
            $assignment = $this->transport->updateAssignment($assignment, $data);
            activity()->causedBy(auth()->user())->performedOn($assignment)->event('updated')->log('Transport assignment updated');

            return $assignment;
        });
    }

    /**
     * Create or update the User account behind a driver when login is enabled.
     * When login is disabled the existing account (if any) is left untouched.
     */
    private function createOrUpdateLoginUser(?Driver $driver, array $data, ?int $schoolId): ?User
    {
        if (! filter_var($data['enable_login'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => 'active',
            'current_school_id' => $schoolId,
            'email_verified_at' => now(),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user = $driver?->user;

        if ($user) {
            $user->update(array_filter($payload));
        } else {
            if (! isset($payload['password'])) {
                throw ValidationException::withMessages([
                    'password' => ['Password is required.'],
                ]);
            }

            $user = User::query()->create($payload);
        }

        if ($schoolId) {
            $user->schools()->syncWithoutDetaching([
                $schoolId => [
                    'designation' => 'Driver',
                    'joined_at' => now()->toDateString(),
                    'status' => 'active',
                    'is_primary' => true,
                ],
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

            if (! $user->hasRole('Driver')) {
                $user->assignRole('Driver');
            }
        }

        return $user;
    }
}
