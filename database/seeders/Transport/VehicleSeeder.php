<?php

namespace Database\Seeders\Transport;

use App\Models\School;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
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

        $vehicles = [
            ['vehicle_number' => 'KA-01-AB-1234', 'vehicle_name' => 'School Bus 1', 'vehicle_type' => 'bus', 'capacity' => 40, 'attendant' => 'Lakshmi Bai', 'driver_index' => 0],
            ['vehicle_number' => 'KA-01-CD-5678', 'vehicle_name' => 'School Bus 2', 'vehicle_type' => 'bus', 'capacity' => 45, 'attendant' => 'Sunita Devi', 'driver_index' => 1],
            ['vehicle_number' => 'KA-01-EF-9012', 'vehicle_name' => 'School Van 1', 'vehicle_type' => 'van', 'capacity' => 12, 'attendant' => null, 'driver_index' => 2],
            ['vehicle_number' => 'KA-01-GH-3456', 'vehicle_name' => 'School Van 2', 'vehicle_type' => 'van', 'capacity' => 15, 'attendant' => null, 'driver_index' => 3],
        ];

        foreach ($vehicles as $data) {
            $driverIndex = $data['driver_index'];
            unset($data['driver_index']);

            Vehicle::query()->firstOrCreate(
                ['school_id' => $school->id, 'vehicle_number' => $data['vehicle_number']],
                array_merge($data, [
                    'school_id' => $school->id,
                    'driver_id' => $drivers[$driverIndex]?->id,
                    'status' => 'active',
                ])
            );
        }
    }
}