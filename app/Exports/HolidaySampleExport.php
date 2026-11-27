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
            'day_type',
            'is_special',
            'override_existing',
        ];
    }

    public function array(): array
    {
        $year = (int) date('Y');

        return [
            [Carbon::create($year, 1, 1)->format('Y-m-d'), 'New Year Holiday', 'holiday', 'full_day', 'no', 'yes'],
            [Carbon::create($year, 1, 26)->format('Y-m-d'), 'Republic Day', 'holiday', 'full_day', 'yes', 'yes'],
            [Carbon::create($year, 8, 15)->format('Y-m-d'), 'Independence Day Half Day', 'holiday', 'half_day', 'no', 'yes'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle(1)->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(34);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(20);

        return [];
    }
}
