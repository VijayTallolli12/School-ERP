<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->firstOrCreate(
            ['code' => 'DEMO'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Demo Public School',
                'slug' => 'demo-public-school',
                'email' => 'school@example.com',
                'phone' => '+91 98765 43210',
                'address' => 'Main Campus Road',
                'city' => 'Dharwad',
                'state' => 'Karnataka',
                'country' => 'India',
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
                'status' => 'active',
            ],
        );

        $year = now()->month >= 4 ? now()->year : now()->year - 1;

        AcademicYear::query()->firstOrCreate(
            ['school_id' => $school->id, 'name' => $year.'-'.($year + 1)],
            [
                'starts_on' => now()->setDate($year, 4, 1)->toDateString(),
                'ends_on' => now()->setDate($year + 1, 3, 31)->toDateString(),
                'is_active' => true,
                'status' => 'active',
            ],
        );
    }
}
