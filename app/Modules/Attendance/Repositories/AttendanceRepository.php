<?php

namespace App\Modules\Attendance\Repositories;

use App\Core\Tenant\SchoolContext;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    protected SchoolContext $schoolContext;

    public function __construct(SchoolContext $schoolContext)
    {
        $this->schoolContext = $schoolContext;
    }

    public function query(): Builder
    {
        return Attendance::query()
            ->with([
                'student.sessions.classSection',
                'classSection.schoolClass',
                'classSection.section',
                'academicYear',
                'markedBy',
            ]);
    }

    public function filterQuery(Builder $query, array $filters): Builder
    {
        $table = $query->getModel()->getTable();

        if (! empty($filters['class_section_id'])) {
            $query->where($table.'.class_section_id', $filters['class_section_id']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate($table.'.attendance_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate($table.'.attendance_date', '<=', $filters['to_date']);
        }

        if (! empty($filters['status'])) {
            $query->where($table.'.status', $filters['status']);
        }

        if (! empty($filters['academic_year_id'])) {
            $query->where($table.'.academic_year_id', $filters['academic_year_id']);
        }

        return $query;
    }

    public function getForDataTable(array $filters = []): Paginator
    {
        $query = $this->filterQuery($this->query(), $filters);

        return $query
            ->orderBy('attendance_date', 'DESC')
            ->orderBy('student_id', 'ASC')
            ->paginate(20);
    }

    public function create(array $data): Attendance
    {
        return Attendance::query()->create($data);
    }

    public function update(Attendance $attendance, array $data): Attendance
    {
        $attendance->fill($data)->save();

        return $attendance->refresh();
    }

    public function delete(Attendance $attendance): void
    {
        $attendance->delete();
    }

    public function findByDateAndStudent(string $date, int $studentId): ?Attendance
    {
        return $this->query()
            ->where('attendance_date', $date)
            ->where('student_id', $studentId)
            ->first();
    }

    public function getByClassSectionAndDate(int $classSectionId, string $date): Builder
    {
        return $this->query()
            ->where('class_section_id', $classSectionId)
            ->where('attendance_date', $date);
    }

    public function getMonthlyReport(int $classSectionId, int $month, int $year): array
    {
        $report = [];
        $statuses = Attendance::getStatuses();

        foreach ($statuses as $status => $label) {
            $report[$status] = Attendance::query()
                ->where('class_section_id', $classSectionId)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->where('status', $status)
                ->count();
        }

        return $report;
    }

    public function getStatistics(array $filters = []): array
    {
        $base = $this->filterQuery(Attendance::query(), $filters);
        $statuses = array_keys(Attendance::getStatuses());
        $totals = [];
        foreach ($statuses as $status) {
            $totals[$status] = (clone $base)->where('status', $status)->count();
        }

        $totalRecords = array_sum($totals);
        $presentLike = $totals['present'] + $totals['late'] + $totals['half_day'] + $totals['excused'];
        $attendanceRate = $totalRecords > 0 ? round(($presentLike / $totalRecords) * 100, 1) : 0.0;

        $byClassRows = (clone $base)
            ->selectRaw('class_section_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as present_cnt,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as absent_cnt,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as late_cnt,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as half_day_cnt,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as excused_cnt
            ', ['present', 'absent', 'late', 'half_day', 'excused'])
            ->groupBy('class_section_id')
            ->get();

        $classSections = ClassSection::query()
            ->whereIn('id', $byClassRows->pluck('class_section_id'))
            ->with(['schoolClass', 'section'])
            ->get()
            ->keyBy('id');

        $byClass = [];
        foreach ($byClassRows as $row) {
            $cs = $classSections->get($row->class_section_id);
            $label = $cs ? $cs->schoolClass->name.' - '.$cs->section->name : 'Class #'.$row->class_section_id;
            $total = (int) $row->total;
            $presentCnt = (int) $row->present_cnt;
            $byClass[] = [
                'class_section_id' => (int) $row->class_section_id,
                'label' => $label,
                'total' => $total,
                'present' => $presentCnt,
                'absent' => (int) $row->absent_cnt,
                'late' => (int) $row->late_cnt,
                'half_day' => (int) $row->half_day_cnt,
                'excused' => (int) $row->excused_cnt,
                'rate' => $total > 0 ? round(($presentCnt / $total) * 100, 1) : 0.0,
            ];
        }

        return [
            'totals' => $totals,
            'total_records' => $totalRecords,
            'attendance_rate' => $attendanceRate,
            'by_class' => $byClass,
        ];
    }

    public function getMonthlyStudentBreakdown(int $classSectionId, int $month, int $year): array
    {
        $statusKeys = array_keys(Attendance::getStatuses());

        $students = Student::query()
            ->whereHas('sessions', function ($q) use ($classSectionId): void {
                $q->where('class_section_id', $classSectionId)
                    ->where('status', 'active');
            })
            ->with(['sessions' => function ($q) use ($classSectionId): void {
                $q->where('class_section_id', $classSectionId);
            }])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $attendanceRows = Attendance::query()
            ->where('class_section_id', $classSectionId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->get(['student_id', 'status']);

        $byStudent = $attendanceRows->groupBy('student_id');

        $out = [];
        foreach ($students as $student) {
            $session = $student->sessions->first();
            $rows = $byStudent->get($student->id) ?? collect();
            $counts = array_fill_keys($statusKeys, 0);
            foreach ($rows as $r) {
                if (isset($counts[$r->status])) {
                    $counts[$r->status]++;
                }
            }
            $out[] = [
                'student_id' => $student->id,
                'name' => $student->full_name,
                'roll_no' => $session?->roll_no,
                'counts' => $counts,
                'total_marked' => array_sum($counts),
            ];
        }

        return $out;
    }
}
