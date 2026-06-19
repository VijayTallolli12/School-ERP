<?php

namespace App\Modules\Library\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class LibraryReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected array $data,
        protected string $type,
    ) {
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

        return array_map(fn (string $h) => ucwords(str_replace('_', ' ', $h)), array_keys((array) $firstRow));
    }

    public function title(): string
    {
        return ucfirst(str_replace('_', ' ', $this->type)).' Report';
    }
}
