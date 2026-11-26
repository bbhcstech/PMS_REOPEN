<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Rules\CompanyPhoneNumber;
use Illuminate\Support\Facades\DB;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'database.default' => 'tenant']);
DB::purge('tenant');
(require database_path('migrations/tenant/2026_10_09_150000_ensure_tenant_country_phone_codes.php'))->up();
DB::connection('tenant')->table('countries')->insert(['name' => 'Test custom country', 'phone_code' => '+9988', 'iso_code' => 'ZZ', 'min_digits' => 5, 'max_digits' => 7]);
foreach ([
    '+91 9876543210' => true, '+91 98765' => false, '+91 98765432101' => false,
    '+44 2012345678' => true, '+44 20123456789' => true, '+44 201234567890' => false,
    '+33 123456789' => true, '+33 1234567890' => false,
    '+9988 12345' => true, '+9988 1234567' => true, '+9988 12345678' => false,
    '+9999 123456789' => false, '+91 abcdefghij' => false, '+91 98765abc10' => false,
    '9876543210' => false, '' => true,
] as $phone => $expected) {
    $valid = validator(['phone' => $phone], ['phone' => ['nullable', 'string', new CompanyPhoneNumber]])->passes();
    if ($valid !== $expected) throw new RuntimeException('Unexpected company phone validation: ' . $phone);
}
$view = file_get_contents(resource_path('views/superadmin/companies/show.blade.php'));
token_get_all(app('blade.compiler')->compileString($view), TOKEN_PARSE);
echo "PASS: server phone validation, country-specific and database-configured ranges, invalid prefixes/letters, optional values and template compilation.\n";
