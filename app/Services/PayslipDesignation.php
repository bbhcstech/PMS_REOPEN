<?php

namespace App\Services;

use App\Models\Designation;

class PayslipDesignation
{
    public static function details(array $snapshot, ?Designation $designation): array
    {
        // New payslips keep the real designation at processing time. Legacy slips
        // without a saved level use the employee's actual designation record.
        if (isset($snapshot['designation_level']) && is_numeric($snapshot['designation_level'])) {
            return ['name' => $snapshot['designation'] ?? $designation?->name ?? '—',
                'level' => 'L'.(int) $snapshot['designation_level']];
        }
        return ['name' => $designation?->name ?? $snapshot['designation'] ?? '—',
            'level' => $designation && $designation->level !== null ? 'L'.$designation->level : '—'];
    }
}
