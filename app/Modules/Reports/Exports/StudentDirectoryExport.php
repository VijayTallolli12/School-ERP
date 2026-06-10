<?php

namespace App\Modules\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class StudentDirectoryExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return array_map(fn($row) => [
            'Admission No' => $row['admission_no'],
            'Student Name' => $row['student_name'],
            'Class & Section' => $row['class_section'],
            'Gender' => $row['gender'],
            'Date of Birth' => $row['date_of_birth'],
            'Parent Name' => $row['parent_name'],
            'Parent Mobile' => $row['parent_mobile'],
            'Email' => $row['email'],
            'Status' => $row['status'],
        ], $this->rows);
    }

    public function headings(): array
    {
        return [
            'Admission No',
            'Student Name',
            'Class & Section',
            'Gender',
            'Date of Birth',
            'Parent Name',
            'Parent Mobile',
            'Email',
            'Status',
        ];
    }

    public function title(): string
    {
        return 'Student Directory';
    }
}
