<?php

$source = file_get_contents(dirname(__DIR__, 2).'/resources/views/dashboard.blade.php');
if (! preg_match('/\$formatCompactMoney = function.*?\n\s*};/s', $source, $match)) throw new RuntimeException('Dashboard formatter missing');
$currency = '₹';
eval($match[0]);
foreach ([
    [0, '₹0'], [123, '₹123'], [1500, '₹1.5K'], [2500000, '₹2.5M'],
    [1024842904, '₹1.02B'], [-1024842904, '₹-1.02B'], [1000000000000, '₹1T'],
    [999999, '₹1M'], [999999999, '₹1B'],
] as [$value, $expected]) {
    if ($formatCompactMoney($value) !== $expected) throw new RuntimeException('Incorrect compact amount for '.$value);
}
if (!str_contains($source, 'title="{{ $formatMoney($card[\'value\']) }}"')) throw new RuntimeException('Full amount tooltip missing');
echo "PASS: compact dashboard amounts, zero, negatives, K/M/B/T, boundary rounding and full-value tooltip.\n";
