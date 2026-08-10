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
                ['Gandhi Nagar', 15.4780, 74.9700],
                ['IT Park', 15.4830, 74.9580],
                ['University Gate', 15.4875, 74.9470],
            ],
            'Route B - East Campus' => [
                ['East Bus Stand', 15.4410, 75.0280],
                ['Indira Nagar', 15.4480, 75.0190],
                ['Rajiv Nagar', 15.4530, 75.0090],
                ['Green Park', 15.4580, 74.9990],
                ['Temple Road', 15.4630, 74.9890],
                ['Ring Road', 15.4680, 74.9790],
            ],
            'Route C - South Campus' => [
                ['South Circle', 15.4350, 75.0110],
                ['MIG Colony', 15.4400, 75.0040],
                ['Vidya Nagar', 15.4440, 74.9970],
                ['Shanti Nagar', 15.4490, 74.9900],
                ['Krishna Nagar', 15.4540, 74.9830],
                ['Water Tank Road', 15.4590, 74.9760],
            ],
            'Route D - Central Area' => [
                ['City Center', 15.4601, 75.0021],
                ['Clock Tower', 15.4620, 75.0080],
                ['Cinema Road', 15.4640, 75.0140],
                ['Church Street', 15.4660, 75.0200],
                ['Gandhi Bazaar', 15.4680, 75.0260],
                ['Bus Depot', 15.4700, 75.0320],
            ],
            'Route E - Old Town' => [
                ['Old Town Depot', 15.4490, 74.9620],
                ['Fort Street', 15.4520, 74.9680],
                ['Bazaar Road', 15.4550, 74.9740],
                ['Nehru Chowk', 15.4580, 74.9800],
                ['Railway Station', 15.4610, 74.9860],
                ['Community Hall', 15.4640, 74.9920],
            ],
            'Route F - Airport Road' => [
                ['Airport Junction', 15.4120, 75.0180],
                ['HAL Colony', 15.4210, 75.0200],
                ['New Airport Road', 15.4300, 75.0220],
                ['Garden Colony', 15.4390, 75.0230],
                ['Lake View East', 15.4480, 75.0240],
                ['Junction Circle', 15.4560, 75.0200],
            ],
        ];

        foreach ($routes as $route) {
            $plan = $stopPlans[$route->route_name] ?? [];

            foreach ($plan as $index => [$stopName, $latitude, $longitude]) {
                RouteStop::query()->firstOrCreate(
                    ['route_id' => $route->id, 'stop_name' => $stopName],
                    [
                        'school_id' => $school->id,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'pickup_time' => sprintf('%02d:%02d', 7 + intdiv($index, 4), ($index % 4) * 15),
                        'drop_time' => sprintf('%02d:%02d', 14 + intdiv($index, 4), ($index % 4) * 15),
                        'sequence' => $index + 1,
                    ]
                );
            }
        }
    }
}