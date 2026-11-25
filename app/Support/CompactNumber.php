<?php

namespace App\Support;

class CompactNumber
{
    /** Presentation only: callers keep the original amount for calculations and full-value labels. */
    public static function format(int|float|string|null $value): string
    {
        $amount = (float) ($value ?? 0);
        $suffixes = ['', 'K', 'M', 'B', 'T', 'Q', 'Qi'];
        $unit = 0;
        while (round(abs($amount), 2) >= 1000 && $unit < count($suffixes) - 1) {
            $amount /= 1000;
            $unit++;
        }
        if ($unit === 0) return number_format($amount, 2);
        return rtrim(rtrim(number_format($amount, 2, '.', ','), '0'), '.') . $suffixes[$unit];
    }
}
