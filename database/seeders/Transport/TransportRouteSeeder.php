<?php

namespace Database\Seeders\Transport;

use App\Models\School;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Database\Seeder;

class TransportRouteSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();

        $routes = [
            [
                'route_name' => 'Route A - North Campus',
                'start_point' => 'North Gate',
                'end_point' => 'School Main',
                'distance' => 12.50,
                'vehicle_number' => 'KA-01-AB-1234',
            ],
            [
                'route_name' => 'Route B - East Campus',
                'start_point' => 'East Bus Stand',
                'end_point' => 'School Main',
                'distance' => 15.25,
                'vehicle_number' => 'KA-01-CD-5678',
            ],
            [
                'route_name' => 'Route C - South Campus',
                'start_point' => 'South Circle',
                'end_point' => 'School Main',
                'distance' => 10.75,
                'vehicle_number' => 'KA-01-AB-1234',
            ],
        ];

        foreach ($routes as $data) {
            $vehicle = Vehicle::query()
                ->where('school_id', $school->id)
                ->where('vehicle_number', $data['vehicle_number'])
                ->first();

            Route::query()->updateOrCreate(
                ['school_id' => $school->id, 'route_name' => $data['route_name']],
                [
                    'school_id' => $school->id,
                    'start_point' => $data['start_point'],
                    'end_point' => $data['end_point'],
                    'distance' => $data['distance'],
                    'driver_id' => $vehicle?->driver_id,
                    'vehicle_id' => $vehicle?->id,
                    'status' => 'active',
                ]
            );
        }
    }
}
