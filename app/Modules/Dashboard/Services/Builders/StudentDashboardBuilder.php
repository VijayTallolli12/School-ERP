<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Exams\Models\Exam;
use App\Modules\Homework\Models\Homework;
use App\Modules\Students\Models\Student;

class StudentDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'Student';
    }

    public function getLayout(): string
    {
        return 'admin';
    }

    protected function buildStatCards(): array
    {
        $student = Student::query()->where('user_id', $this->user->getKey())->first();

        if (!$student) {
            return [];
        }

        $totalDays = Attendance::query()->where('student_id', $student->id)->count();
        $presentDays = Attendance::query()->where('student_id', $student->id)->whereIn('status', ['present', 'late', 'half_day'])->count();
        $attendancePct = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;

        $homeworkCount = Homework::query()
            ->whereHas('classSection', fn ($q) => $q->whereIn('id', $student->sessions()->where('status', 'active')->pluck('class_section_id')))
            ->count();

        $upcomingExams = Exam::query()
            ->whereIn('class_section_id', $student->sessions()->where('status', 'active')->pluck('class_section_id'))
            ->where('exam_date', '>=', now())
            ->count();

        return [
            $this->statCard('Attendance', $attendancePct.'%', 'calendar-check', 'info'),
            $this->statCard('Homework', $homeworkCount, 'books', 'primary'),
            $this->statCard('Upcoming Exams', $upcomingExams, 'chart-arrows-vertical', 'warning'),
            $this->statCard('Active Sessions', $student->sessions()->where('status', 'active')->count(), 'school', 'success'),
        ];
    }

    protected function buildWidgets(): array
    {
        return [];
    }

    protected function buildQuickActions(): array
    {
        return [];
    }

    protected function buildCharts(): array
    {
        return [];
    }
}