<?php

namespace Database\Factories;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Students\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $schoolId = app(SchoolContext::class)?->id() ?? 1;

        return [
            'school_id' => $schoolId,
            'student_id' => Student::factory(),
            'class_section_id' => ClassSection::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'attendance_date' => Carbon::now()->subDays(rand(0, 30)),
            'status' => $this->faker->randomElement(['present', 'absent', 'late', 'half_day', 'excused']),
            'marked_by' => User::first()?->id ?? User::factory(),
            'remarks' => $this->faker->optional()->sentence(),
        ];
    }

    public function present(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'present',
        ]);
    }

    public function absent(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'absent',
        ]);
    }

    public function late(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
        ]);
    }

    public function halfDay(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'half_day',
        ]);
    }
}
