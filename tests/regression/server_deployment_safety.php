<?php

// In-memory registry and an intentionally absent SQLite tenant; never touches live DBs.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\{Artisan, DB, Schema};
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

config([
    'database.default' => 'central',
    'database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
    'database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
    'session.driver' => 'array',
]);
DB::purge('central'); DB::purge('tenant');
Schema::connection('central')->create('companies', function ($table) {
    $table->id(); $table->string('name'); $table->string('company_code');
    $table->string('db_name'); $table->string('status'); $table->text('settings')->nullable();
    $table->boolean('manually_suspended')->default(false); $table->softDeletes();
});
$missing = storage_path('framework/testing/nonexistent-'.bin2hex(random_bytes(8)).'.sqlite');
DB::connection('central')->table('companies')->insert([
    'name' => 'Unavailable tenant', 'company_code' => 'BROKEN', 'db_name' => $missing, 'status' => 'active',
]);
$original = config('database.connections.tenant');
$runner = new class {
    public int $calls = 0;
    public function call(...$arguments): int { $this->calls++; throw new RuntimeException('Unexpected migration against fallback database.'); }
};
Artisan::swap($runner); // No migrations/seeders may run against a fallback DB.
$command = new App\Console\Commands\TenantMigrateCommand;
$command->setLaravel($app);
$output = new BufferedOutput;
$exit = $command->run(new ArrayInput(['--company' => 'BROKEN']), $output);
if ($exit !== 1 || $runner->calls !== 0 || !str_contains($output->fetch(), '0 Succeeded, 1 Failed')) throw new RuntimeException('Unavailable tenant did not fail migration safely.');
if (config('database.connections.tenant') !== $original || DB::getDefaultConnection() !== 'central') throw new RuntimeException('Migration leaked its connection configuration.');

$writes = [];
DB::listen(function ($query) use (&$writes) {
    if (preg_match('/^\s*(insert|update|delete|alter|create|drop|truncate)\s/i', $query->sql)) $writes[] = $query->sql;
});
$doctor = new App\Console\Commands\CheckServerDeployment;
$doctor->setLaravel($app);
$output = new BufferedOutput;
$exit = $doctor->run(new ArrayInput(['--company' => 'BROKEN']), $output);
if ($exit !== 1 || !str_contains($output->fetch(), 'Company database access/schema check failed')) throw new RuntimeException('Doctor did not report inaccessible company.');
if ($writes || config('database.connections.tenant') !== $original || DB::getDefaultConnection() !== 'central') throw new RuntimeException('Read-only diagnostic mutated data or leaked configuration.');
try {
    App\Models\User::syncCompanyToConnection('tenant', (object) ['id' => 1], strict: true);
    throw new RuntimeException('Registry sync incorrectly reported success without a companies table.');
} catch (RuntimeException $e) {
    if ($e->getMessage() !== 'Tenant companies table is missing.') throw $e;
}
echo "PASS: inaccessible company cannot trigger fallback migrations, connection state restored, server diagnostic fails clearly without writes.\n";
