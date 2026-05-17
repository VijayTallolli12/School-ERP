<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Timetable\Models\TimetableSlot;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::query()->where('status', 'active')->first();
        $classSection = ClassSection::query()->where('status', 'active')->first();
        $subject = Subject::query()->where('status', 'active')->first();
        $teacher = Teacher::query()->where('status', 'active')->first();

        if (! $academicYear || ! $classSection || ! $subject || ! $teacher) {
            return;
        }

        $slots = [
            [
                'academic_year_id' => $academicYear->id,
                'class_section_id' => $classSection->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'day_of_week' => 1,
                'period_number' => 1,
                'period_label' => 'Period 1',
                'start_time' => '08:30',
                'end_time' => '09:15',
                'room' => 'A1',
                'status' => 'active',
            ],
            [
                'academic_year_id' => $academicYear->id,
                'class_section_id' => $classSection->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'day_of_week' => 1,
                'period_number' => 2,
                'period_label' => 'Period 2',
                'start_time' => '09:20',
                'end_time' => '10:05',
                'room' => 'A1',
                'status' => 'active',
            ],
            [
                'academic_year_id' => $academicYear->id,
                'class_section_id' => $classSection->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'day_of_week' => 2,
                'period_number' => 1,
                'period_label' => 'Period 1',
                'start_time' => '08:30',
                'end_time' => '09:15',
                'room' => 'A1',
                'status' => 'active',
            ],
        ];

        foreach ($slots as $slot) {
            TimetableSlot::query()->updateOrCreate([
                'academic_year_id' => $slot['academic_year_id'],
                'class_section_id' => $slot['class_section_id'],
                'teacher_id' => $slot['teacher_id'],
                'day_of_week' => $slot['day_of_week'],
                'period_number' => $slot['period_number'],
            ], $slot);
        }
    }
}
