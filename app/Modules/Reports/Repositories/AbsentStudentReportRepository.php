<?php

namespace App\Modules\Reports\Repositories;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Students\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class AbsentStudentReportRepository
{
    public function query(array $filters = []): Builder
    {
        return Attendance::with([
            'student.user',
            'student.guardians' => fn ($q) => $q->where('is_primary', true),
            'classSection.schoolClass',
            'classSection.section',
        ])
            ->when(Arr::get($filters, 'school_id'), fn ($q, $v) => $q->where('school_id', $v))
            ->when(Arr::get($filters, 'academic_year_id'), fn ($q, $v) => $q->where('academic_year_id', $v))
            ->when(Arr::get($filters, 'class_section_id'), fn ($q, $v) => $q->where('class_section_id', $v))
            ->when(Arr::get($filters, 'student_id'), fn ($q, $v) => $q->where('student_id', $v))
            ->when(Arr::get($filters, 'from_date'), fn ($q, $v) => $q->whereDate('attendance_date', '>=', Carbon::parse($v)))
            ->when(Arr::get($filters, 'to_date'), fn ($q, $v) => $q->whereDate('attendance_date', '<=', Carbon::parse($v)));
    }

    public function getAttendanceRecords(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->query($filters)
            ->orderBy('student_id')
            ->orderBy('attendance_date')
            ->get();
    }

    public function getSummary(array $filters = []): array
    {
        $query = Attendance::query()
            ->when(Arr::get($filters, 'school_id'), fn ($q, $v) => $q->where('school_id', $v))
            ->when(Arr::get($filters, 'academic_year_id'), fn ($q, $v) => $q->where('academic_year_id', $v))
            ->when(Arr::get($filters, 'class_section_id'), fn ($q, $v) => $q->where('class_section_id', $v))
            ->when(Arr::get($filters, 'student_id'), fn ($q, $v) => $q->where('student_id', $v))
            ->when(Arr::get($filters, 'from_date'), fn ($q, $v) => $q->whereDate('attendance_date', '>=', Carbon::parse($v)))
            ->when(Arr::get($filters, 'to_date'), fn ($q, $v) => $q->whereDate('attendance_date', '<=', Carbon::parse($v)));

        $allRecords = $query->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) as leave_count
        ")->first();

        $total = (int) ($allRecords->total ?? 0);
        $present = (int) ($allRecords->present ?? 0);
        $absent = (int) ($allRecords->absent ?? 0);
        $late = (int) ($allRecords->late ?? 0);
        $leave = (int) ($allRecords->leave_count ?? 0);
        $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        return compact('total', 'present', 'absent', 'late', 'leave', 'percentage');
    }

    public function getClassWiseAbsenceData(array $filters = []): array
    {
        return Attendance::selectRaw('class_section_id, COUNT(*) as count')
            ->where('status', 'absent')
            ->when(Arr::get($filters, 'school_id'), fn ($q, $v) => $q->where('school_id', $v))
            ->when(Arr::get($filters, 'academic_year_id'), fn ($q, $v) => $q->where('academic_year_id', $v))
            ->when(Arr::get($filters, 'from_date'), fn ($q, $v) => $q->whereDate('attendance_date', '>=', Carbon::parse($v)))
            ->when(Arr::get($filters, 'to_date'), fn ($q, $v) => $q->whereDate('attendance_date', '<=', Carbon::parse($v)))
            ->with('classSection.schoolClass', 'classSection.section')
            ->groupBy('class_section_id')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->classSection?->display_name ?? 'Unknown',
                'count' => (int) $row->count,
            ])
            ->values()
            ->toArray();
    }

    public function getTrendData(array $filters = []): array
    {
        return Attendance::where('status', 'absent')
            ->when(Arr::get($filters, 'school_id'), fn ($q, $v) => $q->where('school_id', $v))
            ->when(Arr::get($filters, 'academic_year_id'), fn ($q, $v) => $q->where('academic_year_id', $v))
            ->when(Arr::get($filters, 'class_section_id'), fn ($q, $v) => $q->where('class_section_id', $v))
            ->when(Arr::get($filters, 'student_id'), fn ($q, $v) => $q->where('student_id', $v))
            ->when(Arr::get($filters, 'from_date'), fn ($q, $v) => $q->whereDate('attendance_date', '>=', Carbon::parse($v)))
            ->when(Arr::get($filters, 'to_date'), fn ($q, $v) => $q->whereDate('attendance_date', '<=', Carbon::parse($v)))
            ->selectRaw('attendance_date, COUNT(*) as count')
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->attendance_date instanceof Carbon ? $row->attendance_date->format('Y-m-d') : $row->attendance_date,
                'count' => (int) $row->count,
            ])
            ->toArray();
    }

    public function getStudentsByClass(?int $classSectionId, ?int $schoolId): \Illuminate\Support\Collection
    {
        return Student::with('user')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($classSectionId, fn ($q) => $q->whereHas('sessions', fn ($sq) => $sq->where('class_section_id', $classSectionId)->where('status', 'active')))
            ->orderBy('first_name')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->full_name . ' (' . $s->admission_no . ')',
            ]);
    }
}

