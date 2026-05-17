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
        $query = $this->dailyQuery($filters);

        $present = (clone $query)->where('status', 'present')->count();
        $absent = (clone $query)->where('status', 'absent')->count();
        $late = (clone $query)->where('status', 'late')->count();
        $leave = (clone $query)->where('status', 'leave')->count();

        return [
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'leave' => $leave,
        ];
    }

    public function monthlySummary(int $classSectionId, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = Attendance::where('class_section_id', $classSectionId)
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        return [
            'present' => (clone $query)->where('status', 'present')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
            'leave' => (clone $query)->where('status', 'leave')->count(),
            'total' => $query->count(),
        ];
    }

    public function monthlyStudentBreakdown(int $classSectionId, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return Attendance::with('student.user')
            ->where('class_section_id', $classSectionId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn ($attendance) => optional($attendance->student->user)->id)
            ->map(fn ($group) => [
                'student' => optional($group->first()->student->user)->full_name ?? optional($group->first()->student)->name,
                'present' => $group->where('status', 'present')->count(),
                'absent' => $group->where('status', 'absent')->count(),
                'late' => $group->where('status', 'late')->count(),
                'leave' => $group->where('status', 'leave')->count(),
                'total' => $group->count(),
            ])
            ->values()
            ->toArray();
    }

    public function classWiseSummary(array $filters = []): array
    {
        $query = Attendance::with(['classSection.schoolClass', 'classSection.section'])
            ->when(Arr::get($filters, 'school_id'), fn ($q) => $q->where('school_id', Arr::get($filters, 'school_id')))
            ->when(Arr::get($filters, 'academic_year_id'), fn ($q) => $q->where('academic_year_id', Arr::get($filters, 'academic_year_id')))
            ->when(Arr::get($filters, 'date'), fn ($q) => $q->whereDate('attendance_date', Carbon::parse(Arr::get($filters, 'date'))));

        return $query->get()
            ->groupBy(fn ($attendance) => $attendance->class_section_id)
            ->map(fn ($group) => [
                'class_section' => optional($group->first()->classSection)->display_name ?? 'Unknown',
                'present' => $group->where('status', 'present')->count(),
                'absent' => $group->where('status', 'absent')->count(),
                'late' => $group->where('status', 'late')->count(),
                'leave' => $group->where('status', 'leave')->count(),
                'total' => $group->count(),
            ])
            ->values()
            ->toArray();
    }

    public function todaySummary(): array
    {
        $today = Carbon::today();

        return $this->dailySummary(['date' => $today->toDateString()]);
    }
}
