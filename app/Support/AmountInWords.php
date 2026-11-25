<?php

namespace App\Support;

/**
 * Spells a rupee amount using the Indian numbering system (thousand, lakh, crore),
 * e.g. 1250000.50 → "Rupees Twelve Lakh Fifty Thousand and Fifty Paise Only".
 */
class AmountInWords
{
    private const ONES = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];

    private const TENS = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    public static function rupees(float $amount): string
    {
        $negative = $amount < 0;
        $amount = round(abs($amount), 2);
        $rupees = (int) floor($amount);
        $paise = (int) round(($amount - $rupees) * 100);

        $words = $rupees > 0 ? self::integer($rupees) : 'Zero';
        $text = 'Rupees ' . $words;
        if ($paise > 0) {
            $text .= ' and ' . self::belowHundred($paise) . ' Paise';
        }

        return ($negative ? 'Minus ' : '') . $text . ' Only';
    }

    private static function integer(int $n): string
    {
        $parts = [];
        foreach ([[10000000, 'Crore'], [100000, 'Lakh'], [1000, 'Thousand'], [100, 'Hundred']] as [$unit, $label]) {
            if ($n >= $unit) {
                $count = intdiv($n, $unit);
                // Crores can exceed 99, so spell the multiplier recursively.
                $parts[] = ($count >= 100 ? self::integer($count) : self::belowHundred($count)) . ' ' . $label;
                $n %= $unit;
            }
        }
        if ($n > 0) {
            $parts[] = self::belowHundred($n);
        }

        return implode(' ', $parts);
    }

    private static function belowHundred(int $n): string
    {
        if ($n < 20) {
            return self::ONES[$n];
        }

        return trim(self::TENS[intdiv($n, 10)] . ' ' . self::ONES[$n % 10]);
    }
}
