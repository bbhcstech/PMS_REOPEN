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
    if (!str_contains($exception->errors()['email'][0], 'hr@company.com')) {
        throw new RuntimeException('Duplicate notification omitted the submitted email.');
    }
}
if (DB::connection('tenant')->table('clients')->count() !== 0
    || DB::connection('tenant')->table('users')->count() !== 1) {
    throw new RuntimeException('Duplicate email validation changed account data.');
}
DB::connection('tenant')->table('clients')->insert(['email' => 'client@company.com']);
DB::connection('tenant')->table('users')->insert(['email' => 'client@company.com']);
$request->merge(['email' => 'client@company.com']);
try {
    (new ClientController())->store($request);
    throw new RuntimeException('Existing client email was accepted.');
} catch (ValidationException $exception) {
    $errors = $exception->errors()['email'] ?? [];
    if (count($errors) !== 1 || !str_contains($errors[0], 'client@company.com')) {
        throw new RuntimeException('Duplicate notification was missing, repeated, or omitted the email.');
    }
}
foreach (['create', 'edit'] as $view) {
    $compiled = app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/clients/' . $view . '.blade.php')));
    token_get_all($compiled, TOKEN_PARSE);
}
echo "Client duplicate email checks passed.\n";
