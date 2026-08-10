<?php

namespace Database\Seeders\Transport;

use App\Models\School;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use Illuminate\Database\Seeder;

class RouteStopSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();

        $routes = Route::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        $stopPlans = [
            'Route A - North Campus' => [
                ['Station Road', 15.4520, 75.0120],
                ['City Center', 15.4601, 75.0021],
                ['Market Square', 15.4655, 74.9930],
                ['Lake View', 15.4720, 74.9810],
            ],
            'Route B - East Campus' => [
                ['East Bus Stand', 15.4410, 75.0280],
                ['Indira Nagar', 15.4480, 75.0190],
                ['Rajiv Nagar', 15.4530, 75.0090],
                ['Green Park', 15.4580, 74.9990],
                ['Temple Road', 15.4630, 74.9890],
            ],
            'Route C - South Campus' => [
                ['South Circle', 15.4350, 75.0110],
                ['MIG Colony', 15.4400, 75.0040],
                ['Vidya Nagar', 15.4440, 74.9970],
                ['Shanti Nagar', 15.4490, 74.9900],
            ],
        ];

        foreach ($routes as $route) {
            $plan = $stopPlans[$route->route_name] ?? [];

            foreach ($plan as $index => [$stopName, $latitude, $longitude]) {
                RouteStop::query()->updateOrCreate(
                    ['route_id' => $route->id, 'stop_name' => $stopName],
                    [
                        'school_id' => $school->id,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'pickup_time' => sprintf('%02d:%02d', 7, ($index % 4) * 15),
                        'drop_time' => sprintf('%02d:%02d', 14, ($index % 4) * 15),
                        'sequence' => $index + 1,
                    ]
                );
            }
        }
    }
}
