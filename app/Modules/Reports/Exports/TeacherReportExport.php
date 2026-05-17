<?php

namespace App\Modules\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class TeacherReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $data;
    protected $type;

    public function __construct(array $data, string $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function array(): array
    {
        if ($this->type === 'attendance') {
            return array_map(function ($row) {
                return [
                    'teacher_name' => $row['teacher_name'] ?? trim(($row['teacher']['first_name'] ?? '') . ' ' . ($row['teacher']['last_name'] ?? '')),
                    'employee_id' => $row['employee_id'] ?? ($row['teacher']['employee_id'] ?? ''),
                    'status' => $row['status'] ?? ($row['teacher']['status'] ?? ''),
                    'present' => $row['present'] ?? 0,
                    'absent' => $row['absent'] ?? 0,
                    'late' => $row['late'] ?? 0,
                    'half_day' => $row['half_day'] ?? 0,
                    'excused' => $row['excused'] ?? 0,
                    'total' => $row['total'] ?? 0,
                    'percentage' => ($row['percentage'] ?? 0) . '%',
                ];
            }, $this->data);
        }

        return array_map(function ($row) {
            $flat = [];
            foreach ($row as $key => $value) {
                if (is_array($value)) {
                    $flat[$key] = json_encode($value);
                } else {
                    $flat[$key] = $value;
                }
            }
            return $flat;
        }, $this->data);
    }

    public function headings(): array
    {
        if ($this->type === 'attendance') {
            return [
                'Teacher Name',
                'Employee ID',
                'Status',
                'Present',
                'Absent',
                'Late',
                'Half Day',
                'Excused',
                'Total Days',
                'Attendance %',
            ];
        }

        if (empty($this->data)) {
            return [];
        }

        $firstRow = reset($this->data);
        $headings = array_keys((array) $firstRow);

        return array_map(function ($heading) {
            return ucwords(str_replace('_', ' ', $heading));
        }, $headings);
    }

    public function title(): string
    {
        return ucfirst(str_replace('_', ' ', $this->type)) . ' Report';
    }
}
