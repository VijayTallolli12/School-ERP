<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use Illuminate\Support\Carbon;

class StudentQueryHandler
{
    public function __construct(
        private readonly SchoolContext $schoolContext
    ) {}

    public function totalStudents(): string
    {
        $count = Student::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('status', 'active')
            ->count();

        return "Total active students: {$count}";
    }

    public function admittedThisMonth(): string
    {
        $now = Carbon::now();
        $count = Student::query()
            ->where('school_id', $this->schoolContext->id())
            ->whereYear('admission_date', $now->year)
            ->whereMonth('admission_date', $now->month)
            ->count();

        return "Students admitted this month ({$now->format('F Y')}): {$count}";
    }

    public function studentsByClass(): string
    {
        $schoolId = $this->schoolContext->id();

        $rows = StudentSession::query()
            ->selectRaw('class_section_id, COUNT(*) as total')
            ->whereHas('student', fn($q) => $q->where('school_id', $schoolId))
            ->where('status', 'active')
            ->groupBy('class_section_id')
            ->get();

        if ($rows->isEmpty()) {
            return 'No students found.';
        }

        $classIds = $rows->pluck('class_section_id');
        $classes = ClassSection::query()
            ->whereIn('id', $classIds)
            ->with(['schoolClass', 'section'])
            ->get()
            ->keyBy('id');

        $lines = [];
        foreach ($rows as $row) {
            $cs = $classes->get($row->class_section_id);
            $label = $cs ? "{$cs->schoolClass->name} - {$cs->section->name}" : "Class #{$row->class_section_id}";
            $lines[] = "{$label}: {$row->total} students";
        }

        return "Students by class:\n" . implode("\n", $lines);
    }
}
