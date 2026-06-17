<?php

namespace App\Imports;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class HolidaysImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $date = $this->parseDate($row['date'] ?? null);
            $occasion = trim((string) ($row['occassion'] ?? $row['occasion'] ?? $row['title'] ?? ''));
            $type = trim((string) ($row['type'] ?? 'holiday')) ?: 'holiday';
            $override = in_array(strtolower(trim((string) ($row['override_existing'] ?? 'yes'))), ['yes', 'y', '1', 'true'], true);

            if (! $date || $occasion === '') {
                $this->skipped++;
                $this->errors[] = "Row {$line}: date and occassion are required.";
                continue;
            }

            $payload = [
                'group_id' => (string) Str::uuid(),
                'title' => $occasion,
                'date' => $date,
                'occassion' => $occasion,
                'type' => in_array($type, ['holiday', 'weekly_holiday'], true) ? $type : 'holiday',
                'recurring_day' => null,
                'department_id_json' => $this->listToJson($row['department_ids'] ?? null),
                'designation_id_json' => $this->listToJson($row['designation_ids'] ?? null),
                'employment_type_json' => $this->listToJson($row['employment_types'] ?? null),
            ];

            $existing = Holiday::whereNull('archived_at')->whereDate('date', $date)->first();

            if ($existing && ! $override) {
                $this->skipped++;
                $this->errors[] = "Row {$line}: {$date} already exists and override_existing is not yes.";
                continue;
            }

            if ($existing) {
                unset($payload['group_id']);
                $existing->update($payload);
                $this->updated++;
            } else {
                Holiday::create($payload);
                $this->created++;
            }
        }
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function listToJson($value): ?string
    {
        $items = collect(explode(',', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();

        return $items ? json_encode($items) : null;
    }
}
