<?php

namespace App\Console\Commands;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use App\Models\Trip;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;
use App\Services\TripService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateDailyTrips extends Command
{
    protected $signature = 'transport:generate-trips
        {--date= : Trip date (Y-m-d). Defaults to today.}
        {--school= : Restrict generation to a single school id.}';

    protected $description = 'Generate scheduled pickup/drop trips for active transport assignments';

    public function handle(TripService $tripService, SchoolContext $context): int
    {
        $requestedDate = $this->option('date');

        $schools = School::query()
            ->where('status', 'active')
            ->when($this->option('school'), fn ($query, $id) => $query->where('id', $id))
            ->get();

        if ($schools->isEmpty()) {
            $this->error('No active schools found.');

            return self::FAILURE;
        }

        $totalTrips = 0;

        $vehiclesWithActiveAssignments = TransportAssignment::query()
            ->where('status', 'active')
            ->whereNotNull('vehicle_id')
            ->whereNotNull('route_id')
            ->whereNotNull('route_stop_id')
            ->distinct()
            ->pluck('vehicle_id');

        foreach ($schools as $school) {
            $context->set($school->id);

            $date = $requestedDate
                ? Carbon::parse($requestedDate, $context->timezone())->toDateString()
                : $context->startOfToday()->toDateString();

            $this->line("Processing school [{$school->name}] for {$date}.");

            $vehicles = Vehicle::query()
                ->where('school_id', $school->id)
                ->where('status', 'active')
                ->whereIn('id', $vehiclesWithActiveAssignments)
                ->with('driver', 'routes')
                ->get();

            $generated = 0;
            $skipped = 0;

            foreach ($vehicles as $vehicle) {
                $driver = $vehicle->driver;

                if (! $driver) {
                    $this->warn("Vehicle [{$vehicle->vehicle_number}] has no assigned driver; skipping.");

                    continue;
                }

                foreach ($vehicle->routes as $route) {
                    $alreadyExists = Trip::query()
                        ->where('school_id', $school->id)
                        ->where('vehicle_id', $vehicle->id)
                        ->where('route_id', $route->id)
                        ->where('trip_date', $date)
                        ->exists();

                    if ($alreadyExists) {
                        $skipped++;

                        continue;
                    }

                    $trips = $tripService->createTripsForDate($driver->id, $vehicle->id, $route->id, $date);
                    $generated += $trips->count();
                    $totalTrips += $trips->count();

                    $this->line(
                        "  Vehicle [{$vehicle->vehicle_number}] route [{$route->route_name}]: generated {$trips->count()} trips."
                    );
                }
            }

            $this->info("School [{$school->name}]: generated {$generated} trips ({$skipped} already present).");
        }

        $context->set(null);

        $this->info("Done. Generated {$totalTrips} trips for {$date}.");

        return self::SUCCESS;
    }
}