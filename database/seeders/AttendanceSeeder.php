<?php

namespace Database\Seeders;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active students with sessions
        $students = Student::query()
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->with('sessions')
            ->limit(50)
            ->get();

        $statuses = ['present', 'absent', 'late', 'half_day', 'excused'];
        $createdCount = 0;

        // Generate attendance for last 30 days
        for ($day = 30; $day >= 1; $day--) {
            $date = now()->subDays($day)->toDateString();

            foreach ($students as $student) {
                $session = $student->sessions->firstWhere('status', 'active');
                if (!$session) continue;

                // Skip weekends (Saturday=6, Sunday=0)
                $dayOfWeek = now()->subDays($day)->dayOfWeek;
                if (in_array($dayOfWeek, [0, 6])) continue;

                // Randomly mark attendance (80% present, rest absent/late)
                if (rand(1, 100) > 20) {
                    $status = $statuses[rand(0, 2)]; // present, absent, late
                } else {
                    $status = $statuses[rand(3, 4)]; // half_day, excused
                }

                try {
                    Attendance::query()->firstOrCreate(
                        [
                            'school_id' => $student->school_id,
                            'student_id' => $student->id,
                            'attendance_date' => $date,
                        ],
                        [
                            'school_id' => $student->school_id,
                            'class_section_id' => $session->class_section_id,
                            'academic_year_id' => $session->academic_year_id,
                            'status' => $status,
                            'marked_by' => 1, // Default admin user
                            'remarks' => rand(1, 100) > 90 ? 'Medical leave' : null,
                        ]
                    );
                    $createdCount++;
                } catch (\Exception $e) {
                    // Skip duplicate entries
                    continue;
                }
            }
        }

        $this->command->info("Attendance seeding completed. Created {$createdCount} attendance records.");
    }
}
