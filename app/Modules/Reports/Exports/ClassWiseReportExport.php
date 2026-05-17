<?php

namespace App\Modules\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClassWiseReportExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                'Class' => $item->class_name,
                'Total Students' => $item->total_students,
                'Male' => $item->male_count,
                'Female' => $item->female_count,
                'Active' => $item->active_count,
                'Inactive' => $item->inactive_count,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Class',
            'Total Students',
            'Male',
            'Female',
            'Active',
            'Inactive',
        ];
    }
}