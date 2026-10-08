<?php

// Run with: php tests/regression/designation_company_levels.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Designation;
use App\Models\User;
use App\Services\DesignationLevels;
use App\Http\Controllers\DesignationController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

foreach (['tenant', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
Schema::connection('central')->create('companies', function (Blueprint $t) {
    $t->id(); $t->string('name'); $t->text('settings')->nullable(); $t->softDeletes(); $t->timestamps();
});
Schema::create('users', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('company_id'); });
Schema::create('designations', function (Blueprint $t) {
    $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->unsignedBigInteger('added_by')->nullable();
    $t->unsignedBigInteger('parent_id')->nullable(); $t->string('name'); $t->integer('level');
    $t->string('unique_code')->nullable(); $t->timestamp('archived_at')->nullable(); $t->timestamps();
});
DB::connection('central')->table('companies')->insert([
    ['id' => 1, 'name' => 'Alpha', 'settings' => '{}'],
    ['id' => 2, 'name' => 'Beta', 'settings' => '{"designation_max_level":10}'],
]);
DB::table('users')->insert([['id' => 1, 'company_id' => 1], ['id' => 2, 'company_id' => 2]]);
DB::table('designations')->insert([
    ['id' => 1, 'name' => 'Alpha legacy', 'company_id' => null, 'added_by' => 1, 'level' => 8],
    ['id' => 2, 'name' => 'Beta legacy', 'company_id' => null, 'added_by' => 2, 'level' => 1],
    ['id' => 3, 'name' => 'Beta owned', 'company_id' => 2, 'added_by' => 2, 'level' => 2],
]);
$actor = new User;
$actor->forceFill(['id' => 1, 'company_id' => 1, 'role' => 'hr']);
Auth::guard('web')->setUser($actor);
function checkLevels($ok, $message) { if (! $ok) throw new RuntimeException($message); }
checkLevels(DesignationLevels::maximum() === 6, 'Default limit is not L6.');
checkLevels(Designation::pluck('id')->all() === [1], 'Designation company scope leaked rows.');
checkLevels(! Designation::withinLevelLimit()->whereKey(1)->exists(), 'Legacy L8 was offered under the L6 limit.');
try {
    Designation::create(['name' => 'Rejected L7', 'level' => 7]);
    throw new RuntimeException('L7 accepted under default L6 limit.');
} catch (ValidationException $e) {}
try {
    Designation::create(['name' => 'Rejected', 'level' => 8]);
    throw new RuntimeException('HR created L8 under default limit.');
} catch (ValidationException $e) {}
$valid = Designation::create(['name' => 'Valid L6', 'level' => 6]);
checkLevels((int) $valid->company_id === 1, 'Company ownership was not assigned.');
try {
    Designation::create(['name' => 'Foreign parent', 'level' => 3, 'parent_id' => 3]);
    throw new RuntimeException('Foreign parent accepted.');
} catch (ValidationException $e) {}
try {
    (new DesignationController)->updateLevelSettings(Request::create('/', 'PUT', ['maximum_level' => 8]));
    throw new RuntimeException('HR could change company limit.');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkLevels($e->getStatusCode() === 403, 'Unexpected permission response.');
}
$actor->role = 'admin';
(new DesignationController)->updateLevelSettings(Request::create('/', 'PUT', ['maximum_level' => 8]));
checkLevels(DesignationLevels::maximum() === 8, 'Admin setting was not applied.');
checkLevels(Designation::withinLevelLimit()->whereKey(1)->exists(), 'L8 did not become available after admin raised the limit.');
$actor->role = 'hr';
Designation::create(['name' => 'Allowed L8', 'level' => 8]);
$actor->company_id = 2;
checkLevels(DesignationLevels::maximum() === 10, 'Other company setting changed.');
checkLevels(! Designation::where('name', 'Allowed L8')->exists(), 'Other company can see Alpha designation.');
$actor->company_id = 1;
$actor->role = 'admin';
try {
    (new DesignationController)->updateLevelSettings(Request::create('/', 'PUT', ['maximum_level' => 6]));
    throw new RuntimeException('Limit lowered below existing levels.');
} catch (ValidationException $e) {}
(new DesignationController)->updateLevelSettings(Request::create('/', 'PUT', ['maximum_level' => 101]));
checkLevels(DesignationLevels::maximum() === 101, 'Admin maximum was still capped at 100.');
Designation::create(['name' => 'Allowed L101', 'level' => 101]);
try {
    Designation::create(['name' => 'Rejected L102', 'level' => 102]);
    throw new RuntimeException('L102 accepted above configured L101 limit.');
} catch (ValidationException $e) {}
foreach (['designations/create', 'designations/index', 'designations/hierarchy', 'employees/create', 'employees/edit'] as $view) {
    $compiled = app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/' . $view . '.blade.php')));
    token_get_all($compiled, TOKEN_PARSE);
}
echo "Designation company level and template checks passed.\n";
