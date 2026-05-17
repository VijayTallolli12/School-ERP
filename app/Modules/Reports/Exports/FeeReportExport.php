<?php

namespace App\Modules\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class FeeReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
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
        return $this->data;
    }

    public function headings(): array
    {
        if (empty($this->data)) {
            return [];
        }

        // Generate headings from the keys of the first row
        $firstRow = reset($this->data);
        $headings = array_keys((array) $firstRow);

        // Format headings
        return array_map(function ($heading) {
            return ucwords(str_replace('_', ' ', $heading));
        }, $headings);
    }

    public function title(): string
    {
        return ucfirst(str_replace('_', ' ', $this->type)) . ' Report';
    }
}