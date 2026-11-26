<?php

namespace App\Support;

/** Display formats for recruitment requirement values (PDF, list and details). */
class RecruitmentFormat
{
    /** "4" -> "4 Years", "1" -> "1 Year", "2-4" -> "2 - 4 Years"; text that already has a unit is kept. */
    public static function experience(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') return 'Not Specified';
        if (in_array(strtolower($value), ['no', 'none', 'nil', 'na', 'n/a', 'fresher', 'freshers'], true)) {
            return 'Fresher (0 Years)';
        }
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(?:-|–|to)\s*(\d+(?:\.\d+)?)\+?$/i', $value, $m)) {
            return $m[1] . ' - ' . $m[2] . ' Years';
        }
        if (preg_match('/^(\d+(?:\.\d+)?)(\+?)$/', $value, $m)) {
            if ((float) $m[1] === 0.0) return 'Fresher (0 Years)';
            return $m[1] . $m[2] . ' ' . ((float) $m[1] === 1.0 && $m[2] === '' ? 'Year' : 'Years');
        }
        // "2-3yrs", "3 years", "6 months" ... already carry their unit.
        return preg_replace('/(\d)\s*yrs?\b/i', '$1 Years', $value);
    }

    /**
     * Salary Range (CTC): bare figures in the LPA range (the form allows up to 999) are lakhs per annum.
     * Older requirements stored full amounts ("10000", "$ 60,000") and are shown exactly as saved.
     */
    public static function ctc(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') return 'Negotiable';
        if (preg_match('/^(\d+(?:\.\d+)?)(?:\s*-\s*(\d+(?:\.\d+)?))?$/', $value, $m)
            && (float) $m[1] <= 999 && (! isset($m[2]) || (float) $m[2] <= 999)) {
            return $value . ' LPA';
        }
        return $value;
    }

    public static function positions($count): string
    {
        $count = max(1, (int) $count);
        return $count . ' ' . ($count === 1 ? 'Position' : 'Positions');
    }

    /** [label, tone] for the status chip. */
    public static function status(?string $status): array
    {
        return match (strtolower((string) $status)) {
            'open' => ['Open', 'open'],
            'in_progress' => ['In Progress', 'progress'],
            'closed' => ['Closed', 'closed'],
            'cancelled' => ['Cancelled', 'cancelled'],
            default => [ucwords(str_replace('_', ' ', (string) $status)) ?: 'Open', 'closed'],
        };
    }
}
