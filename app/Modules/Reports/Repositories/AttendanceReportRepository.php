<?php

namespace App\Modules\Reports\Repositories;

use App\Modules\Attendance\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class AttendanceReportRepository implements AttendanceReportRepositoryInterface
{
    public function dailyQuery(array $filters = []): Builder
    {
        $query = Attendance::with(['student.user', 'classSection.schoolClass', 'classSection.section'])
            ->when(Arr::get($filters, 'school_id'), fn ($q) => $q->where('school_id', Arr::get($filters, 'school_id')))
            ->when(Arr::get($filters, 'academic_year_id'), fn ($q) => $q->where('academic_year_id', Arr::get($filters, 'academic_year_id')))
            ->when(Arr::get($filters, 'class_section_id'), fn ($q) => $q->where('class_section_id', Arr::get($filters, 'class_section_id')))
            ->when(Arr::get($filters, 'date'), fn ($q) => $q->whereDate('attendance_date', Carbon::parse(Arr::get($filters, 'date'))));

        return $query;
    }

    public function dailySummary(array $filters = []): array
    {
        $query = Attendance::query()
            ->when(Arr::get($filters, 'school_id'), fn ($q) => $q->where('school_id', Arr::get($filters, 'school_id')))
            ->when(Arr::get($filters, 'academic_year_id'), fn ($q) => $q->where('academic_year_id', Arr::get($filters, 'academic_year_id')))
            ->when(Arr::get($filters, 'class_section_id'), fn ($q) => $q->where('class_section_id', Arr::get($filters, 'class_section_id')))
            ->when(Arr::get($filters, 'date'), fn ($q) => $q->whereDate('attendance_date', Carbon::parse(Arr::get($filters, 'date'))));

        // Single query with conditional aggregation instead of 4 separate COUNT queries
        $stats = $query->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = \'absent\' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN status = \'late\' THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN status = \'leave\' THEN 1 ELSE 0 END) as leave_count
        ')->first();

        return [
            'present' => (int) ($stats->present ?? 0),
            'absent' => (int) ($stats->absent ?? 0),
            'late' => (int) ($stats->late ?? 0),
            'leave' => (int) ($stats->leave_count ?? 0),
        ];
    }

    public function monthlySummary(int $classSectionId, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Single query with conditional aggregation for monthly summary
        $stats = Attendance::where('class_section_id', $classSectionId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = \'absent\' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = \'late\' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = \'leave\' THEN 1 ELSE 0 END) as leave_count
            ')->first();

        return [
            'present' => (int) ($stats->present ?? 0),
            'absent' => (int) ($stats->absent ?? 0),
            'late' => (int) ($stats->late ?? 0),
            'leave' => (int) ($stats->leave_count ?? 0),
            'total' => (int) ($stats->total ?? 0),
        ];
    }

    public function monthlyStudentBreakdown(int $classSectionId, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Use SQL GROUP BY instead of loading all records into PHP
        return Attendance::selectRaw('
                student_id,
                SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = \'absent\' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = \'late\' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = \'leave\' THEN 1 ELSE 0 END) as leave_count,
                COUNT(*) as total
            ')
            ->with('student')
            ->where('class_section_id', $classSectionId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->groupBy('student_id')
            ->get()
            ->map(fn ($row) => [
                'student' => $row->student?->full_name ?? 'N/A',
                'present' => (int) $row->present,
                'absent' => (int) $row->absent,
                'late' => (int) $row->late,
                'leave' => (int) $row->leave_count,
                'total' => (int) $row->total,
            ])
            ->values()
            ->toArray();
    }

    public function classWiseSummary(array $filters = []): array
    {
        // Use SQL GROUP BY with conditional aggregation
        $rows = Attendance::selectRaw('
                class_section_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = \'absent\' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = \'late\' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = \'leave\' THEN 1 ELSE 0 END) as leave_count
            ')
            ->with('classSection.schoolClass', 'classSection.section')
            ->when(Arr::get($filters, 'school_id'), fn ($q) => $q->where('school_id', Arr::get($filters, 'school_id')))
            ->when(Arr::get($filters, 'academic_year_id'), fn ($q) => $q->where('academic_year_id', Arr::get($filters, 'academic_year_id')))
            ->when(Arr::get($filters, 'date'), fn ($q) => $q->whereDate('attendance_date', Carbon::parse(Arr::get($filters, 'date'))))
            ->groupBy('class_section_id')
            ->get();

        return $rows->map(fn ($row) => [
            'class_section' => optional($row->classSection)->display_name ?? 'Unknown',
            'present' => (int) $row->present,
            'absent' => (int) $row->absent,
            'late' => (int) $row->late,
            'leave' => (int) $row->leave_count,
            'total' => (int) $row->total,
        ])->values()->toArray();
    }

    public function todaySummary(): array
    {
        $today = Carbon::today();

        $summary = $this->dailySummary(['date' => $today->toDateString()]);
        $total = array_sum($summary);
        $summary['total'] = $total;
        $summary['present_percent'] = $total > 0 ? round(($summary['present'] / $total) * 100, 1) : 0;
        $summary['absent_percent'] = $total > 0 ? round(($summary['absent'] / $total) * 100, 1) : 0;
        $summary['late_percent'] = $total > 0 ? round(($summary['late'] / $total) * 100, 1) : 0;
        $summary['leave_percent'] = $total > 0 ? round(($summary['leave'] / $total) * 100, 1) : 0;

        return $summary;
    }

    public function dailyTrendData(array $filters = []): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(29);

        $records = Attendance::selectRaw('DATE(attendance_date) as date, status, COUNT(*) as count')
            ->when(Arr::get($filters, 'school_id'), fn ($q) => $q->where('school_id', Arr::get($filters, 'school_id')))
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->groupByRaw('DATE(attendance_date), status')
            ->orderBy('date')
            ->get();

        $grouped = $records->groupBy('date');
        $result = [];
        $dateCursor = $startDate->copy();
        while ($dateCursor <= $endDate) {
            $dateStr = $dateCursor->toDateString();
            $items = $grouped->get($dateStr, collect());
            $present = (int) $items->where('status', 'present')->sum('count');
            $absent = (int) $items->where('status', 'absent')->sum('count');
            $total = $items->sum('count');
            $result[] = [
                'date' => $dateStr,
                'present_percent' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                'absent_percent' => $total > 0 ? round(($absent / $total) * 100, 1) : 0,
            ];
            $dateCursor->addDay();
        }

        return $result;
    }
}
