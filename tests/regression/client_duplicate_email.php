<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
foreach (['users', 'clients'] as $name) {
    Schema::connection('tenant')->create($name, function ($table) {
        $table->id(); $table->string('email')->unique();
    });
}
DB::connection('tenant')->table('users')->insert(['email' => 'hr@company.com']);
$request = Request::create('/clients', 'POST', [
    'name' => 'John', 'email' => 'hr@company.com', 'password' => 'Valid@12345',
    'mobile' => '9876543210', 'country' => 'India',
]);
try {
    (new ClientController())->store($request);
    throw new RuntimeException('Existing employee email was accepted.');
} catch (ValidationException $exception) {
    if (!isset($exception->errors()['email'])) throw $exception;
}
if (DB::connection('tenant')->table('clients')->count() !== 0
    || DB::connection('tenant')->table('users')->count() !== 1) {
    throw new RuntimeException('Duplicate email validation changed account data.');
}
echo "Client duplicate email checks passed.\n";
