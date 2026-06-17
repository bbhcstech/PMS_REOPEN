<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HolidaySampleExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'date',
            'occassion',
            'type',
            'department_ids',
            'designation_ids',
            'employment_types',
            'override_existing',
        ];
    }

    public function array(): array
    {
        $year = (int) date('Y');

        return [
            [Carbon::create($year, 1, 1)->format('Y-m-d'), 'New Year Holiday', 'holiday', '', '', 'full_time,internship', 'yes'],
            [Carbon::create($year, 1, 26)->format('Y-m-d'), 'Republic Day', 'holiday', '', '', '', 'yes'],
            [Carbon::create($year, 8, 15)->format('Y-m-d'), 'Independence Day', 'holiday', '', '', '', 'yes'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle(1)->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(34);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(28);
        $sheet->getColumnDimension('G')->setWidth(20);

        return [];
    }
}
