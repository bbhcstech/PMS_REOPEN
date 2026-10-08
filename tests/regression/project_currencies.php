<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
Schema::connection('tenant')->create('currencies', function ($table) {
    $table->id();
    $table->string('currency_name');
    $table->string('currency_code');
    $table->string('currency_symbol')->nullable();
    $table->unsignedInteger('no_of_decimal');
    $table->string('thousand_separator')->nullable();
    $table->string('decimal_separator')->nullable();
    $table->timestamps();
});
$existing = DB::connection('tenant')->table('currencies')->insertGetId([
    'currency_name' => 'Custom dollar label', 'currency_code' => 'usd', 'no_of_decimal' => 3,
]);
$migration = require dirname(__DIR__, 2) . '/database/migrations/tenant/2026_10_08_130000_populate_project_currencies.php';
$migration->up();
$migration->up();
$table = DB::connection('tenant')->table('currencies');
if ($table->count() !== 9 || (clone $table)->where('id', $existing)->value('no_of_decimal') !== 3) {
    throw new RuntimeException('Currency setup duplicated or overwrote existing currencies.');
}
$rule = ['currency_id' => 'nullable|integer|exists:tenant.currencies,id'];
if (Validator::make(['currency_id' => $existing], $rule)->fails()
    || !Validator::make(['currency_id' => 99999], $rule)->fails()) {
    throw new RuntimeException('Project currency validation failed.');
}
echo "Project currency checks passed.\n";
