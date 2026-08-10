<?php

namespace Database\Seeders\Transport;

use App\Models\School;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();

        $drivers = Driver::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('id')
            ->get()
            ->values()
            ->all();

        $vehicles = Vehicle::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('id')
            ->get()
            ->values()
            ->all();

        $routes = [
            ['route_name' => 'Route A - North Campus', 'start_point' => 'North Gate', 'end_point' => 'School Main', 'distance' => 12.50, 'driver' => 0, 'vehicle' => 0],
            ['route_name' => 'Route B - East Campus', 'start_point' => 'East Bus Stand', 'end_point' => 'School Main', 'distance' => 15.25, 'driver' => 0, 'vehicle' => 0],
            ['route_name' => 'Route C - South Campus', 'start_point' => 'South Circle', 'end_point' => 'School Main', 'distance' => 10.75, 'driver' => 1, 'vehicle' => 1],
            ['route_name' => 'Route D - Central Area', 'start_point' => 'City Center', 'end_point' => 'School Main', 'distance' => 8.40, 'driver' => 1, 'vehicle' => 1],
            ['route_name' => 'Route E - Old Town', 'start_point' => 'Old Town Depot', 'end_point' => 'School Main', 'distance' => 6.80, 'driver' => 2, 'vehicle' => 2],
            ['route_name' => 'Route F - Airport Road', 'start_point' => 'Airport Junction', 'end_point' => 'School Main', 'distance' => 18.90, 'driver' => 3, 'vehicle' => 3],
        ];

        foreach ($routes as $data) {
            $driver = $drivers[$data['driver']] ?? $drivers[0] ?? null;
            $vehicle = $vehicles[$data['vehicle']] ?? $vehicles[0] ?? null;

            Route::query()->firstOrCreate(
                ['school_id' => $school->id, 'route_name' => $data['route_name']],
                [
                    'school_id' => $school->id,
                    'start_point' => $data['start_point'],
                    'end_point' => $data['end_point'],
                    'distance' => $data['distance'],
                    'driver_id' => $driver?->id,
                    'vehicle_id' => $vehicle?->id,
                    'status' => 'active',
                ]
            );
        }
    }
}