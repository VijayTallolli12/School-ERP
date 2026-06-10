<?php

namespace App\Modules\Reports\Repositories;

use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use App\Modules\Teachers\Models\TeacherTimetableSlot;
use App\Modules\Academics\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class TeacherReportRepository implements TeacherReportRepositoryInterface
{
    protected function getSchoolId()
    {
        return app(\App\Core\Tenant\SchoolContext::class)->id() ?? auth()->user()->school_id ?? null;
    }

    public function dashboardStats(): array
    {
        $schoolId = $this->getSchoolId();

        $totalTeachers = Teacher::where('school_id', $schoolId)->count();
        $activeTeachers = Teacher::where('school_id', $schoolId)->where('status', 'active')->count();
        
        $classTeachers = Teacher::where('school_id', $schoolId)
            ->whereHas('classTeacherSections')
            ->count();

        $subjectAllocationCount = DB::table('teacher_subject')
            ->join('teachers', 'teacher_subject.teacher_id', '=', 'teachers.id')
            ->where('teachers.school_id', $schoolId)
            ->count();

        return [
            'total_teachers' => $totalTeachers,
            'active_teachers' => $activeTeachers,
            'class_teachers' => $classTeachers,
            'subject_allocations' => $subjectAllocationCount,
        ];
    }

    public function teacherList(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();
        
        $query = Teacher::with(['subjects', 'classSections.schoolClass', 'classSections.section'])
            ->where('school_id', $schoolId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['subject_id'])) {
            $query->whereHas('subjects', function ($q) use ($filters) {
                $q->where('subjects.id', $filters['subject_id']);
            });
        }

        if (!empty($filters['class_section_id'])) {
            $query->whereHas('classSections', function ($q) use ($filters) {
                $q->where('class_sections.id', $filters['class_section_id']);
            });
        }

        if (!empty($filters['joining_date_from']) && !empty($filters['joining_date_to'])) {
            $query->whereBetween('joining_date', [$filters['joining_date_from'], $filters['joining_date_to']]);
        }

        return $query->get()->toArray();
    }

    public function attendance(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();

        // Get filtered teacher IDs first
        $teacherQuery = Teacher::where('school_id', $schoolId);

        $teacherQuery
            ->when(Arr::get($filters, 'teacher_id'), fn ($q, $teacherId) => $q->where('id', $teacherId))
            ->when(Arr::get($filters, 'status'), fn ($q, $status) => $q->where('status', $status));

        $teacherIds = $teacherQuery->pluck('id');

        if ($teacherIds->isEmpty()) {
            return [];
        }

        // Use SQL aggregation with GROUP BY to compute attendance stats per teacher in one query
        $attendanceQuery = TeacherAttendance::select([
            'teacher_id',
            DB::raw('SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) as present'),
            DB::raw('SUM(CASE WHEN status = \'absent\' THEN 1 ELSE 0 END) as absent'),
            DB::raw('SUM(CASE WHEN status = \'late\' THEN 1 ELSE 0 END) as late'),
            DB::raw('SUM(CASE WHEN status = \'half_day\' THEN 1 ELSE 0 END) as half_day'),
            DB::raw('SUM(CASE WHEN status = \'excused\' THEN 1 ELSE 0 END) as excused'),
        ])
            ->whereIn('teacher_id', $teacherIds);

        $attendanceQuery
            ->when(Arr::get($filters, 'attendance_status'), fn ($q, $status) => $q->where('status', $status))
            ->when(Arr::get($filters, 'month'), fn ($q, $month) => $q->whereMonth('attendance_date', $month))
            ->when(Arr::get($filters, 'year'), fn ($q, $year) => $q->whereYear('attendance_date', $year))
            ->when(Arr::get($filters, 'from_date'), fn ($q, $date) => $q->whereDate('attendance_date', '>=', $date))
            ->when(Arr::get($filters, 'to_date'), fn ($q, $date) => $q->whereDate('attendance_date', '<=', $date));

        $attendanceAgg = $attendanceQuery->groupBy('teacher_id')->get()->keyBy('teacher_id');

        $teachers = Teacher::whereIn('id', $teacherIds)->get()->keyBy('id');
        $result = [];

        foreach ($teacherIds as $tid) {
            $teacher = $teachers->get($tid);
            $stats = $attendanceAgg->get($tid);

            $present = (int) ($stats->present ?? 0);
            $absent = (int) ($stats->absent ?? 0);
            $late = (int) ($stats->late ?? 0);
            $halfDay = (int) ($stats->half_day ?? 0);
            $excused = (int) ($stats->excused ?? 0);

            $total = $present + $absent + $late + $halfDay + $excused;
            $presentEquivalent = $present + $late + ($halfDay * 0.5);
            $percentage = $total > 0 ? round(($presentEquivalent / $total) * 100, 2) : 0;

            $result[] = [
                'teacher_id' => $tid,
                'teacher_name' => $teacher?->full_name ?? 'N/A',
                'employee_id' => $teacher?->employee_id ?? '',
                'status' => $teacher?->status ?? '',
                'teacher' => $teacher?->toArray() ?? [],
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'half_day' => $halfDay,
                'excused' => $excused,
                'total' => $total,
                'percentage' => $percentage,
            ];
        }

        return $result;
    }

    public function subjectAllocation(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();

        $query = Teacher::with(['subjects'])
            ->where('school_id', $schoolId)
            ->whereHas('subjects');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['subject_id'])) {
            $query->whereHas('subjects', function ($q) use ($filters) {
                $q->where('subjects.id', $filters['subject_id']);
            });
        }

        return $query->get()->toArray();
    }

    public function classTeacherMapping(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();

        $query = Teacher::with(['classTeacherSections.schoolClass', 'classTeacherSections.section'])
            ->where('school_id', $schoolId)
            ->whereHas('classTeacherSections');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['class_section_id'])) {
            $query->whereHas('classTeacherSections', function ($q) use ($filters) {
                $q->where('class_sections.id', $filters['class_section_id']);
            });
        }

        return $query->get()->toArray();
    }

    public function workload(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();

        $query = Teacher::where('school_id', $schoolId);

        if (!empty($filters['teacher_id'])) {
            $query->where('id', $filters['teacher_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $withCountConfig = ['subjects', 'classSections'];
        $withCountConfig['timetableSlots'] = function ($q) use ($filters) {
            if (!empty($filters['academic_year_id'])) {
                $q->where('academic_year_id', $filters['academic_year_id']);
            }
        };
        $query->withCount($withCountConfig);

        if (!empty($filters['subject_id'])) {
            $query->whereHas('subjects', fn($q) => $q->where('subjects.id', $filters['subject_id']));
        }
        if (!empty($filters['class_section_id'])) {
            $query->whereHas('classSections', fn($q) => $q->where('class_sections.id', $filters['class_section_id']));
        }

        $teachers = $query->orderBy('first_name')->orderBy('last_name')->get();
        $teacherIds = $teachers->pluck('id')->toArray();

        $rows = [];
        $subjectCounts = [];
        $workloadValues = [];

        foreach ($teachers as $teacher) {
            $subjectsCount = (int) $teacher->subjects_count;
            $classesCount = (int) $teacher->class_sections_count;
            $periodsCount = (int) $teacher->timetable_slots_count;
            $workloadScore = $subjectsCount + $classesCount + $periodsCount;

            $rows[] = [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->full_name,
                'employee_id' => $teacher->employee_id,
                'status' => $teacher->status,
                'assigned_subjects' => $subjectsCount,
                'assigned_classes' => $classesCount,
                'weekly_periods' => $periodsCount,
                'workload_score' => $workloadScore,
            ];

            $workloadValues[] = $workloadScore;
        }

        // Subject allocation chart data
        if (!empty($teacherIds)) {
            $subjectAllocations = DB::table('teacher_subject')
                ->join('subjects', 'teacher_subject.subject_id', '=', 'subjects.id')
                ->whereIn('teacher_subject.teacher_id', $teacherIds)
                ->select('subjects.name', DB::raw('COUNT(*) as teacher_count'))
                ->groupBy('subjects.name')
                ->orderByDesc('teacher_count')
                ->get();
            foreach ($subjectAllocations as $item) {
                $subjectCounts[] = [
                    'label' => $item->name,
                    'value' => (int) $item->teacher_count,
                ];
            }
        }

        $totalTeachers = count($rows);
        $avgWorkload = $totalTeachers > 0 ? round(array_sum($workloadValues) / $totalTeachers, 1) : 0;
        $avgClasses = $totalTeachers > 0 ? round(array_sum(array_column($rows, 'assigned_classes')) / $totalTeachers, 1) : 0;
        $avgSubjects = $totalTeachers > 0 ? round(array_sum(array_column($rows, 'assigned_subjects')) / $totalTeachers, 1) : 0;

        $summary = [
            'total_teachers' => $totalTeachers,
            'avg_workload' => $avgWorkload,
            'avg_classes' => $avgClasses,
            'avg_subjects' => $avgSubjects,
        ];

        $chartData = [
            'workload_distribution' => array_map(fn($r) => ['label' => $r['teacher_name'], 'value' => $r['workload_score']], $rows),
            'subject_allocation' => $subjectCounts,
        ];

        return compact('rows', 'summary', 'chartData');
    }
}
