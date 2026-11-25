<?php

// Run with: php tests/regression/payroll_working_days.php
// Payroll working days come from the company's Work Schedule and holidays, never an assumed 22.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PayrollCalculationService;
use Illuminate\Support\Facades\DB;

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant', 'cache.default' => 'array']);
DB::statement('CREATE TABLE holidays (id integer primary key, company_id integer, date text, occassion text, title text)');
DB::statement('CREATE TABLE app_settings (id integer primary key, key text, value text, created_at text, updated_at text)');

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};
$service = app(PayrollCalculationService::class);
$days = fn (int $y, int $m) => $service->workingDaysInMonth($y, $m, 7);
$setting = function (string $key, $value) {
    DB::table('app_settings')->updateOrInsert(['key' => $key], ['value' => is_string($value) ? $value : json_encode($value)]);
    \Illuminate\Support\Facades\Cache::flush();
};

$check($days(2026, 10) === 22, 'October 2026, Monday-Friday week: 22 working days');
$check($days(2026, 2) === 20, 'February 2026, Monday-Friday week: 20 working days');
DB::table('holidays')->insert(['company_id' => 7, 'date' => '2026-10-02', 'occassion' => 'Gandhi Jayanti']);
$check($days(2026, 10) === 21, 'a company holiday on a weekday removes one working day (21)');
DB::table('holidays')->insert(['company_id' => 7, 'date' => '2026-10-04', 'occassion' => 'Sunday event']);
$check($days(2026, 10) === 21, 'a holiday on a weekly off is not counted twice (21)');
DB::table('holidays')->insert(['company_id' => 8, 'date' => '2026-10-05', 'occassion' => 'Other company holiday']);
$check($days(2026, 10) === 21, "another company's holiday does not apply (21)");
$setting('work_special_days', [['type' => 'holiday', 'date' => '2026-10-20', 'title' => 'Diwali']]);
$check($days(2026, 10) === 20, 'a Work Schedule special holiday removes a working day (20)');
$setting('work_working_days', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
$check($days(2026, 10) === 25, 'six-day week (Saturday working) follows the Work Schedule (25)');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: payroll working days follow the Work Schedule, company holidays, special holidays and company isolation.\n";
exit($failures ? 1 : 0);
