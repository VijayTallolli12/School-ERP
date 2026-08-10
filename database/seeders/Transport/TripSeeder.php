<?php

namespace Database\Seeders\Transport;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use App\Models\Trip;
use App\Models\TripStudent;
use App\Modules\Transport\Models\Route;
use App\Services\TripService;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        $context = app(SchoolContext::class);

        $today = $context->startOfToday()->toDateString();
        $yesterday = $context->now()->subDay()->toDateString();

        /** @var TripService $tripService */
        $tripService = app(TripService::class);

        $routes = Route::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->whereNotNull('driver_id')
            ->orderBy('id')
            ->get();

        $generated = 0;

        foreach ($routes as $route) {
            if ($this->tripsExist($school->id, $route, $today)) {
                continue;
            }

            $trips = $tripService->createTripsForDate($route->driver_id, $route->vehicle_id, $route->id, $today);
            $generated += $trips->count();
        }

        $todayPickupTrip = Trip::query()
            ->where('school_id', $school->id)
            ->where('trip_date', $today)
            ->where('type', 'pickup')
            ->latest('id')
            ->first();

        // Demo "live" trip: put the most recent morning pickup in progress so
        // the driver app has an active trip to show on the home screen.
        if ($todayPickupTrip) {
            $todayPickupTrip->update([
                'status' => 'in_progress',
                'started_at' => $context->now()->subMinutes(20),
            ]);
        }

        // Completed history for the first route so trip history isn't empty.
        $firstRoute = $routes->first();
        if ($firstRoute && ! $this->tripsExist($school->id, $firstRoute, $yesterday)) {
            $this->createCompletedHistory($tripService, $firstRoute, $yesterday, $context);
        }

        $this->command->info("Trip seeding completed. Generated {$generated} trips for today ({$today}).");
    }

    private function tripsExist(int $schoolId, Route $route, string $date): bool
    {
        return Trip::query()
            ->where('school_id', $schoolId)
            ->where('vehicle_id', $route->vehicle_id)
            ->where('route_id', $route->id)
            ->whereDate('trip_date', $date)
            ->exists();
    }

    private function createCompletedHistory(TripService $tripService, Route $route, string $date, SchoolContext $context): void
    {
        $trips = $tripService->createTripsForDate($route->driver_id, $route->vehicle_id, $route->id, $date);

        $pickupStart = $context->now()->subDay()->setTime(7, 0);
        $pickupEnd = $context->now()->subDay()->setTime(8, 45);
        $dropStart = $context->now()->subDay()->setTime(14, 30);
        $dropEnd = $context->now()->subDay()->setTime(16, 0);

        foreach ($trips as $trip) {
            $isPickup = $trip->type === 'pickup';

            $trip->update([
                'status' => 'completed',
                'started_at' => $isPickup ? $pickupStart : $dropStart,
                'completed_at' => $isPickup ? $pickupEnd : $dropEnd,
                'picked_up_count' => $isPickup ? $trip->total_students : 0,
                'dropped_off_count' => $isPickup ? 0 : $trip->total_students,
            ]);

            $trip->tripStudents->each(function (TripStudent $tripStudent) use ($isPickup, $pickupStart, $dropStart): void {
                if ($isPickup) {
                    $tripStudent->update([
                        'pickup_status' => 'picked_up',
                        'picked_up_at' => $pickupStart->copy()->addMinutes(rand(5, 30)),
                    ]);
                } else {
                    $tripStudent->update([
                        'drop_status' => 'dropped_off',
                        'dropped_off_at' => $dropStart->copy()->addMinutes(rand(5, 30)),
                    ]);
                }
            });
        }
    }
}