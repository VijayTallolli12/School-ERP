<?php

namespace App\Modules\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdmissionReportExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $total;

    public function __construct($data, $total)
    {
        $this->data = $data;
        $this->total = $total;
    }

    public function collection()
    {
        $rows = $this->data->map(function ($item) {
            return [
                'Class' => $item->class_name,
                'Total Admissions' => $item->total_admissions,
            ];
        });

        $rows->push([
            'Class' => 'Total',
            'Total Admissions' => $this->total,
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Class',
            'Total Admissions',
        ];
    }
}