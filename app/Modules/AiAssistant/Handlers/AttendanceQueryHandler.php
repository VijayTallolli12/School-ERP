<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceQueryHandler
{
    public function __construct(
        private readonly SchoolContext $schoolContext
    ) {}

    public function absentToday(): string
    {
        $today = Carbon::today()->toDateString();
        $count = Attendance::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('attendance_date', $today)
            ->where('status', 'absent')
            ->count();

        $totalMarked = Attendance::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('attendance_date', $today)
            ->count();

        return "Students absent today ({$today}): {$count}" . ($totalMarked > 0 ? " (out of {$totalMarked} marked)" : '');
    }

    public function monthlyPercentage(): string
    {
        $now = Carbon::now();
        $schoolId = $this->schoolContext->id();

        $totals = Attendance::query()
            ->where('school_id', $schoolId)
            ->whereYear('attendance_date', $now->year)
            ->whereMonth('attendance_date', $now->month)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_day_count,
                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_count
            ")
            ->first();

        $total = (int) ($totals->total ?? 0);
        $presentLike = (int) ($totals->present_count ?? 0)
            + (int) ($totals->late_count ?? 0)
            + (int) ($totals->half_day_count ?? 0)
            + (int) ($totals->excused_count ?? 0);

        $percentage = $total > 0 ? round(($presentLike / $total) * 100, 1) : 0;

        return "Monthly attendance percentage ({$now->format('F Y')}): {$percentage}% ({$presentLike} present-like out of {$total} total records)";
    }

    public function studentsBelow75(): string
    {
        $schoolId = $this->schoolContext->id();
        $academicYearId = $this->getActiveAcademicYearId();

        $students = Student::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->get();

        $belowThreshold = [];

        foreach ($students as $student) {
            $totalMarked = Attendance::query()
                ->where('school_id', $schoolId)
                ->where('student_id', $student->id)
                ->whereYear('attendance_date', now()->year)
                ->count();

            if ($totalMarked === 0) {
                continue;
            }

            $presentCount = Attendance::query()
                ->where('school_id', $schoolId)
                ->where('student_id', $student->id)
                ->whereIn('status', ['present', 'late', 'half_day', 'excused'])
                ->whereYear('attendance_date', now()->year)
                ->count();

            $percentage = round(($presentCount / $totalMarked) * 100, 1);

            if ($percentage < 75) {
                $belowThreshold[] = "{$student->full_name} (Admission No: {$student->admission_no}) - {$percentage}%";
            }
        }

        if (empty($belowThreshold)) {
            return 'No students with attendance below 75%.';
        }

        $count = count($belowThreshold);
        return "Students below 75% attendance ({$count}):\n" . implode("\n", array_slice($belowThreshold, 0, 50));
    }

    public function search(array $parameters): array
    {
        $query = Attendance::query()
            ->where('school_id', $this->schoolContext->id())
            ->with(['student', 'classSection.schoolClass', 'classSection.section'])
            ->orderByDesc('attendance_date');

        $this->applyDateFilters($query, $parameters);

        $this->applyScopeFilters($query, $parameters);

        if (!empty($parameters['status'])) {
            $statuses = is_array($parameters['status']) ? $parameters['status'] : [$parameters['status']];
            $query->whereIn('status', $statuses);
        }

        $limit = max(1, min((int) ($parameters['limit'] ?? 50), 50));

        $records = $query->limit($limit)->get();

        return [
            'count' => $records->count(),
            'records' => $records->map(fn (Attendance $a) => [
                'id' => $a->id,
                'date' => $a->attendance_date?->toDateString(),
                'student' => $a->student?->full_name,
                'admission_no' => $a->student?->admission_no,
                'class' => $a->classSection
                    ? trim(($a->classSection->schoolClass->name ?? '') . ' - ' . ($a->classSection->section->name ?? ''))
                    : null,
                'status' => $a->status,
            ])->all(),
            'summary' => null,
        ];
    }

    public function absent(array $parameters): array
    {
        $query = Attendance::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('status', 'absent')
            ->with(['student', 'classSection.schoolClass', 'classSection.section'])
            ->orderByDesc('attendance_date');

        $this->applyDateFilters($query, $parameters);

        $this->applyScopeFilters($query, $parameters);

        $limit = max(1, min((int) ($parameters['limit'] ?? 50), 50));

        $records = $query->limit($limit)->get();

        return [
            'count' => $records->count(),
            'records' => $records->map(fn (Attendance $a) => [
                'id' => $a->id,
                'date' => $a->attendance_date?->toDateString(),
                'student' => $a->student?->full_name,
                'admission_no' => $a->student?->admission_no,
                'class' => $a->classSection
                    ? trim(($a->classSection->schoolClass->name ?? '') . ' - ' . ($a->classSection->section->name ?? ''))
                    : null,
                'status' => $a->status,
            ])->all(),
            'summary' => null,
        ];
    }

    public function summary(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();

        $query = Attendance::query()->where('school_id', $schoolId);

        $this->applyDateFilters($query, $parameters);

        $this->applyScopeFilters($query, $parameters);

        $totals = (clone $query)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_day_count,
                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_count
            ")
            ->first();

        $total = (int) ($totals->total ?? 0);
        $present = (int) ($totals->present_count ?? 0);
        $absent = (int) ($totals->absent_count ?? 0);
        $late = (int) ($totals->late_count ?? 0);
        $presentLike = $present + $late
            + (int) ($totals->half_day_count ?? 0)
            + (int) ($totals->excused_count ?? 0);
        $percentage = $total > 0 ? round(($presentLike / $total) * 100, 1) : 0;

        return [
            'count' => $total,
            'records' => [],
            'summary' => [
                'total_marked' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'percentage' => $percentage,
            ],
        ];
    }

    public function below75(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();

        $students = Student::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->when(!empty($parameters['class_section_id']), fn ($q) => $q->whereHas('sessions', fn ($q2) => $q2->where('class_section_id', $parameters['class_section_id'])))
            ->when(!empty($parameters['class_section_ids']), fn ($q) => $q->whereHas('sessions', fn ($q2) => $q2->whereIn('class_section_id', (array) $parameters['class_section_ids'])))
            ->when(!empty($parameters['student_ids']), fn ($q) => $q->whereIn('id', (array) $parameters['student_ids']))
            ->when(!empty($parameters['student_id']), fn ($q) => $q->where('id', $parameters['student_id']))
            ->limit(500)
            ->get();

        $below = [];

        // Aggregate all students' attendance in one pass (no N+1 per student).
        $aggregate = Attendance::query()
            ->where('school_id', $schoolId)
            ->whereYear('attendance_date', now()->year)
            ->selectRaw("
                student_id,
                COUNT(*) as total_marked,
                SUM(CASE WHEN status IN ('present','late','half_day','excused') THEN 1 ELSE 0 END) as present_like
            ")
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        foreach ($students as $student) {
            $row = $aggregate->get($student->id);

            if (!$row || (int) $row->total_marked === 0) {
                continue;
            }

            $percentage = round(((int) $row->present_like / (int) $row->total_marked) * 100, 1);

            if ($percentage < 75) {
                $below[] = [
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'admission_no' => $student->admission_no,
                    'percentage' => $percentage,
                ];
            }
        }

        $limit = max(1, min((int) ($parameters['limit'] ?? 50), 50));

        return [
            'count' => count($below),
            'records' => array_slice($below, 0, $limit),
            'summary' => null,
        ];
    }

    private function applyDateFilters($query, array $parameters): void
    {
        if (!empty($parameters['date'])) {
            $query->whereDate('attendance_date', $parameters['date']);
        }

        if (!empty($parameters['date_from'])) {
            $query->whereDate('attendance_date', '>=', $parameters['date_from']);
        }

        if (!empty($parameters['date_to'])) {
            $query->whereDate('attendance_date', '<=', $parameters['date_to']);
        }
    }

    private function applyScopeFilters($query, array $parameters): void
    {
        if (!empty($parameters['class_section_id'])) {
            $query->where('class_section_id', $parameters['class_section_id']);
        } elseif (!empty($parameters['class_section_ids'])) {
            $query->whereIn('class_section_id', (array) $parameters['class_section_ids']);
        }

        if (!empty($parameters['student_ids'])) {
            $query->whereIn('student_id', (array) $parameters['student_ids']);
        }

        if (!empty($parameters['student_id'])) {
            $query->where('student_id', $parameters['student_id']);
        }
    }

    private function getActiveAcademicYearId(): ?int
    {
        return \App\Models\AcademicYear::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('is_active', true)
            ->value('id');
    }
}
