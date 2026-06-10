<?php

namespace App\Modules\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class GenderWiseExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $rows;
    protected $summary;

    public function __construct(array $rows, array $summary)
    {
        $this->rows = $rows;
        $this->summary = $summary;
    }

    public function array(): array
    {
        return array_map(fn($row) => [
            'Class' => $row['class_name'],
            'Total Students' => $row['total'],
            'Male' => $row['male'],
            'Female' => $row['female'],
            'Other' => $row['other'],
            'Male %' => $row['male_pct'] . '%',
            'Female %' => $row['female_pct'] . '%',
        ], $this->rows);
    }

    public function headings(): array
    {
        return [
            'Class',
            'Total Students',
            'Male',
            'Female',
            'Other',
            'Male %',
            'Female %',
        ];
    }

    public function title(): string
    {
        return 'Gender-wise Student Report';
    }
}
