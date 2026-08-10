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

        $vehicles = [
            [
                'vehicle_number' => 'KA-01-AB-1234',
                'vehicle_name' => 'School Bus 1',
                'vehicle_type' => 'bus',
                'capacity' => 40,
                'attendant' => 'Lakshmi Bai',
                'driver_license' => 'DL-2024-IND-001',
            ],
            [
                'vehicle_number' => 'KA-01-CD-5678',
                'vehicle_name' => 'School Bus 2',
                'vehicle_type' => 'bus',
                'capacity' => 45,
                'attendant' => 'Sunita Devi',
                'driver_license' => 'DL-2024-IND-002',
            ],
        ];

        foreach ($vehicles as $data) {
            $driver = Driver::query()
                ->where('school_id', $school->id)
                ->where('license_number', $data['driver_license'])
                ->first();

            unset($data['driver_license']);

            Vehicle::query()->updateOrCreate(
                ['school_id' => $school->id, 'vehicle_number' => $data['vehicle_number']],
                array_merge($data, [
                    'school_id' => $school->id,
                    'driver_id' => $driver?->id,
                    'status' => 'active',
                ])
            );
        }
    }
}
