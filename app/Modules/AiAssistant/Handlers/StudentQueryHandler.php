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

    public function count(array $parameters): array
    {
        $query = Student::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('status', 'active');

        $this->applyScopeFilters($query, $parameters);

        return [
            'count' => $query->count(),
            'records' => [],
            'summary' => null,
        ];
    }

    public function search(array $parameters): array
    {
        $query = Student::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('status', 'active');

        $this->applyScopeFilters($query, $parameters);

        if (!empty($parameters['name'])) {
            $name = (string) $parameters['name'];
            $query->where(function ($q) use ($name) {
                $q->where('full_name', 'like', "%{$name}%")
                    ->orWhere('admission_no', 'like', "%{$name}%");
            });
        }

        $limit = max(1, min((int) ($parameters['limit'] ?? 25), 50));

        $students = $query
            ->orderBy('full_name')
            ->limit($limit)
            ->get();

        return [
            'count' => $students->count(),
            'records' => $students->map(fn (Student $s) => [
                'id' => $s->id,
                'name' => $s->full_name,
                'admission_no' => $s->admission_no,
                'status' => $s->status,
            ])->all(),
            'summary' => null,
        ];
    }

    public function byClass(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();

        $rows = StudentSession::query()
            ->selectRaw('class_section_id, COUNT(*) as total')
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', 'active')
            ->when(!empty($parameters['class_section_id']), fn ($q) => $q->where('class_section_id', $parameters['class_section_id']))
            ->when(!empty($parameters['class_section_ids']), fn ($q) => $q->whereIn('class_section_id', (array) $parameters['class_section_ids']))
            ->when(!empty($parameters['student_ids']), fn ($q) => $q->whereIn('student_id', (array) $parameters['student_ids']))
            ->groupBy('class_section_id')
            ->get();

        $classIds = $rows->pluck('class_section_id');
        $classes = ClassSection::query()
            ->whereIn('id', $classIds)
            ->with(['schoolClass', 'section'])
            ->get()
            ->keyBy('id');

        $records = $rows->map(function ($row) use ($classes) {
            $cs = $classes->get($row->class_section_id);
            $label = $cs ? "{$cs->schoolClass->name} - {$cs->section->name}" : "Class #{$row->class_section_id}";

            return [
                'class_section_id' => $row->class_section_id,
                'class' => $label,
                'count' => (int) $row->total,
            ];
        })->all();

        return [
            'count' => count($records),
            'records' => $records,
            'summary' => ['total_students' => $rows->sum('total')],
        ];
    }

    public function admittedThisMonthCount(array $parameters): array
    {
        $now = Carbon::now();
        $query = Student::query()
            ->where('school_id', $this->schoolContext->id())
            ->whereYear('admission_date', $now->year)
            ->whereMonth('admission_date', $now->month);

        $this->applyScopeFilters($query, $parameters);

        return [
            'count' => $query->count(),
            'records' => [],
            'summary' => null,
        ];
    }

    private function applyScopeFilters($query, array $parameters): void
    {
        if (!empty($parameters['class_section_id'])) {
            $query->whereHas('sessions', fn ($q) => $q->where('class_section_id', $parameters['class_section_id']));
        } elseif (!empty($parameters['class_section_ids'])) {
            $query->whereHas('sessions', fn ($q) => $q->whereIn('class_section_id', (array) $parameters['class_section_ids']));
        }

        if (!empty($parameters['student_ids'])) {
            $query->whereIn('id', (array) $parameters['student_ids']);
        }

        if (!empty($parameters['student_id'])) {
            $query->where('id', $parameters['student_id']);
        }
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
