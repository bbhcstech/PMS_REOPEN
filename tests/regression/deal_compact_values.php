<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Support\CompactNumber;

foreach ([['0','0.00'],[null,'0.00'],['12.5','12.50'],['999.99','999.99'],['1000','1K'],['1250000','1.25M'],['5000000000','5B'],['1250000000','1.25B'],['1000000000000','1T'],['999999','1M'],['999999999','1B'],['-1250000000','-1.25B'],['1000000000000000','1Q']] as [$input,$expected]) {
    if (CompactNumber::format($input) !== $expected) throw new RuntimeException('Unexpected compact number for '.var_export($input,true));
}
$source=file_get_contents(dirname(__DIR__,2).'/resources/views/admin/deals/index.blade.php');
foreach (['pipeline_value','weighted_value'] as $key) {
    if (!preg_match('/<h3[^>]*title="[^"\n]*\[\''.$key.'\'\][^>]*aria-label=[^>]*>[^\n]*CompactNumber::format\([^\n]*\[\''.$key.'\'\]/', $source)) throw new RuntimeException('Card must retain its exact value in accessible labels and use compact visible formatting.');
}
echo "PASS: compact thousands/millions/billions/trillions, boundary rounding, negative/small amounts and exact-value card labels.\n";
