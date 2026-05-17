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
        return $this->data->map(function ($student) {
            $session = $student->sessions->first();
            return [
                'Full Name' => $student->user->first_name . ' ' . $student->user->last_name,
                'Admission No' => $student->admission_no,
                'Class & Section' => $session ? $session->classSection->schoolClass->name . ' - ' . $session->classSection->section->name : '',
                'Guardian' => $student->guardians->pluck('user.first_name')->join(', '),
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