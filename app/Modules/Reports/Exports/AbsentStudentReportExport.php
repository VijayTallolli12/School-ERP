<?php

namespace App\Modules\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class AbsentStudentReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
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
        return 'Absent Students Report';
    }
}
