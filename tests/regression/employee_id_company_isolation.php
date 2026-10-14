<?php

// Run with: php tests/regression/employee_id_company_isolation.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\EmployeeController;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

foreach (['tenant', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
Schema::connection('central')->create('companies', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('short_name')->nullable();
    $table->string('employee_id_prefix')->nullable();
    $table->softDeletes();
});
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id');
});
Schema::create('employee_details', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->unsignedBigInteger('company_id')->nullable();
    $table->string('employee_id')->unique();
});
DB::connection('central')->table('companies')->insert([
    ['id' => 1, 'name' => 'Alpha Company', 'employee_id_prefix' => 'ALPHA-EMP'],
    ['id' => 2, 'name' => 'Beta Company', 'employee_id_prefix' => null],
]);
DB::table('users')->insert([['id' => 1, 'company_id' => 1], ['id' => 2, 'company_id' => 2]]);
DB::table('employee_details')->insert([
    ['user_id' => 1, 'company_id' => 1, 'employee_id' => 'ALPHA-EMP-0007'],
    ['user_id' => 2, 'company_id' => 2, 'employee_id' => 'ALPHA-EMP-0099'],
]);
$actor = new User;
$actor->forceFill(['id' => 1, 'company_id' => 1, 'role' => 'hr']);
Auth::guard('web')->setUser($actor);
app(CompanyContext::class)->reset((object) ['id' => 2, 'employee_id_prefix' => 'WRONG']);
$controller = new EmployeeController;
$preview = fn ($parameters = []) => $controller->nextId(Request::create('/employees/next-id', 'GET', $parameters))->getData()->next;
if ($preview() !== 'ALPHA-EMP-0008') {
    throw new RuntimeException('Preview used another company context or sequence.');
}
try {
    $preview(['company_id' => 2]);
    throw new RuntimeException('Foreign company preview was allowed.');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $exception) {
    if ($exception->getStatusCode() !== 403) throw $exception;
}
$actor->company_id = 2;
if ($preview() !== 'BETA-COMPANY-EMP-0001') {
    throw new RuntimeException('Missing prefix did not use the company name.');
}
$actor->company_id = null;
try {
    $preview();
    throw new RuntimeException('Unassigned HR received a company ID.');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $exception) {
    if ($exception->getStatusCode() !== 403) throw $exception;
}
echo "Employee ID company isolation checks passed.\n";
