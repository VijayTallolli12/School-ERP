<?php

namespace Database\Seeders\Transport;

use App\Models\School;
use App\Modules\Students\Models\Student;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\TransportAssignment;
use Illuminate\Database\Seeder;

class StudentTransportSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        $academicYear = $school->academicYears()->where('is_active', true)->first();

        $routes = Route::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('id')
            ->with('stops')
            ->get();

        if ($routes->isEmpty()) {
            return;
        }

        $students = Student::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('id')
            ->take(36)
            ->get();

        foreach ($students as $index => $student) {
            $route = $routes[$index % $routes->count()];
            $stops = $route->stops->sortBy('sequence')->values();

            if ($stops->isEmpty()) {
                continue;
            }

            $stop = $stops[min($index % $stops->count(), $stops->count() - 1)];

            TransportAssignment::query()->updateOrCreate(
                ['school_id' => $school->id, 'student_id' => $student->id],
                [
                    'route_id' => $route->id,
                    'route_stop_id' => $stop->id,
                    'vehicle_id' => $route->vehicle_id,
                    'pickup_point' => $stop->stop_name,
                    'monthly_fee' => $route->vehicle?->vehicle_type === 'van' ? 1800 : 1500,
                    'status' => 'active',
                ]
            );
        }

        $assigned = TransportAssignment::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->count();

        $yearLabel = $academicYear?->name ?? 'current';

        $this->command->info("Student-route mapping completed. {$assigned} students assigned for {$yearLabel} academic year.");
    }
}
