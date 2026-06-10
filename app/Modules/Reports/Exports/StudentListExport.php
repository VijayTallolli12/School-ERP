<?php

namespace App\Modules\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentListExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($row) {
            return [
                'Full Name' => $row->full_name,
                'Admission No' => $row->admission_no,
                'Class & Section' => $row->class_section,
                'Guardian' => $row->guardian,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Admission No',
            'Class & Section',
            'Guardian',
        ];
    }
}