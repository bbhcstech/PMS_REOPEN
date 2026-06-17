<?php

namespace App\Exports;

use App\Models\Holiday;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HolidaysExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private ?int $year = null,
        private ?int $month = null
    ) {
    }

    public function collection()
    {
        return Holiday::query()
            ->whereNull('archived_at')
            ->when($this->year, fn ($query) => $query->whereYear('date', $this->year))
            ->when($this->month, fn ($query) => $query->whereMonth('date', $this->month))
            ->orderBy('date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Day',
            'Occasion',
            'Type',
            'Recurring Day',
            'Department IDs',
            'Designation IDs',
            'Employment Types',
        ];
    }

    public function map($holiday): array
    {
        $date = Carbon::parse($holiday->date);

        return [
            $date->format('Y-m-d'),
            $date->format('l'),
            $holiday->occassion ?: $holiday->title,
            $holiday->type,
            $holiday->recurring_day,
            $this->jsonList($holiday->department_id_json),
            $this->jsonList($holiday->designation_id_json),
            $this->jsonList($holiday->employment_type_json),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle(1)->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(34);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(22);
        $sheet->getColumnDimension('H')->setWidth(28);

        return [];
    }

    private function jsonList(?string $value): string
    {
        if (! $value) {
            return '';
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? implode(',', $decoded) : '';
    }
}
