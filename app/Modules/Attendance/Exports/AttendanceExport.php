<?php

namespace App\Modules\Attendance\Exports;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class AttendanceExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        private readonly array $filters,
    ) {}

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        $repo = app(AttendanceRepositoryInterface::class);

        return $repo->filterQuery($repo->query(), $this->filters)
            ->orderByDesc('attendance_date')
            ->orderBy('student_id')
            ->limit(5000);
    }

    public function headings(): array
    {
        return [
            'Student',
            'Roll No',
            'Class Section',
            'Academic Year',
            'Date',
            'Status',
            'Marked By',
            'Remarks',
        ];
    }

    /**
     * @param  Attendance  $row
     */
    public function map($row): array
    {
        $session = $row->student?->sessions
            ->firstWhere('class_section_id', $row->class_section_id);

        return [
            $row->student?->full_name ?? '',
            $session?->roll_no ?? '',
            $row->classSection
                ? $row->classSection->schoolClass->name.' - '.$row->classSection->section->name
                : '',
            $row->academicYear?->name ?? '',
            $row->attendance_date?->toDateString() ?? '',
            $row->status_label,
            $row->markedBy?->name ?? '',
            $row->remarks ?? '',
        ];
    }

    public function title(): string
    {
        return 'Attendance';
    }
}
