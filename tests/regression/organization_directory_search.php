<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Http\Request;

config(['database.default' => 'tenant', 'session.driver' => 'array']);
foreach (['tenant', 'central', 'session_db'] as $connection) {
    config(['database.connections.'.$connection => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::statement('PRAGMA case_sensitive_like = ON');
Schema::connection('central')->create('companies', function ($t) { $t->id(); $t->string('name'); $t->softDeletes(); });
DB::connection('central')->table('companies')->insert(['id' => 1, 'name' => 'Own company']);
Schema::create('users', function ($t) {
    $t->id(); $t->integer('company_id'); $t->string('name'); $t->string('email');
    $t->string('role'); $t->timestamp('archived_at')->nullable();
});
Schema::create('departments', function ($t) {
    $t->id(); $t->integer('company_id'); $t->string('dpt_name');
    $t->integer('parent_dpt_id')->nullable(); $t->timestamp('archived_at')->nullable();
});
Schema::create('designations', function ($t) {
    $t->id(); $t->integer('company_id'); $t->integer('added_by')->nullable();
    $t->string('name'); $t->timestamp('archived_at')->nullable();
});
Schema::create('employee_details', function ($t) {
    $t->id(); $t->integer('user_id'); $t->integer('company_id');
    $t->integer('department_id'); $t->integer('designation_id');
    $t->integer('reporting_to')->nullable(); $t->string('employee_id');
    $t->string('skills'); $t->string('status');
});
DB::table('departments')->insert(['id' => 1, 'company_id' => 1, 'dpt_name' => 'Engineering']);
DB::table('designations')->insert(['id' => 1, 'company_id' => 1, 'name' => 'Developer']);
foreach ([1 => 1, 2 => 2] as $id => $companyId) {
    DB::table('users')->insert(['id' => $id, 'company_id' => $companyId, 'name' => 'Ananya Bose', 'email' => 'Ananya@Example.test', 'role' => 'employee']);
    DB::table('employee_details')->insert(['user_id' => $id, 'company_id' => $companyId,
        'department_id' => 1, 'designation_id' => 1, 'reporting_to' => null,
        'employee_id' => 'GOOGLE-EMP-0005', 'skills' => 'Laravel', 'status' => 'Active']);
}
Auth::guard('web')->setUser((new User)->forceFill(['id' => 9, 'company_id' => 1, 'role' => 'manager']));
$controller = new App\Http\Controllers\OrganizationDirectoryController;
foreach (['Ananya', 'ananya', 'ANANYA', ' aNaNyA ', 'ANANYA@EXAMPLE', 'google-emp', 'LARAVEL', 'engineering', 'DEVELOPER'] as $search) {
    $data = $controller->index(Request::create('/organization', 'GET', ['search' => $search]))->getData();
    if ($data['employees']->pluck('id')->all() !== [1]) throw new RuntimeException('Directory search failed or leaked company data: '.$search);
    // Structure view uses a separate relation query; it must match table/grid results.
    if ($data['departmentGroups']->flatMap(fn ($d) => $d->employeeDetails)->pluck('user_id')->all() !== [1]) {
        throw new RuntimeException('Structure search failed or leaked company data: '.$search);
    }
}
foreach ([['designation_id' => 999], ['department_id' => 999], ['reporting_to' => 999], ['status' => 'inactive'], ['search' => "ananya' OR 1=1 --"]] as $filter) {
    $data = $controller->index(Request::create('/organization', 'GET', $filter + ['search' => 'ananya']))->getData();
    if ($data['employees']->total() !== 0 || $data['departmentGroups']->isNotEmpty()) throw new RuntimeException('Existing filters or bound search values were bypassed.');
}
try {
    $controller->index(Request::create('/organization', 'GET', ['search' => 'ananya', 'company_id' => 2]));
    throw new RuntimeException('Cross-company filter was accepted.');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e->getStatusCode() !== 403) throw $e;
}
// Older tenant tables do not have archive columns. The directory still loads their active records.
Schema::table('designations', fn ($table) => $table->dropColumn('archived_at'));
Schema::table('departments', fn ($table) => $table->dropColumn('archived_at'));
$viewer = (new User)->forceFill(['id' => 9, 'company_id' => 1, 'role' => 'admin']);
Auth::guard('web')->setUser($viewer);
foreach ([[], ['search' => 'Ananya']] as $filters) {
    $request = Request::create('/organization', 'GET', $filters);
    $request->setUserResolver(fn () => $viewer);
    $data = $controller->index($request)->getData();
    if ($data['designations']->pluck('name')->all() !== ['Developer'] || array_column($data['treePayload']['nodes'], 'id') !== [1]) {
        throw new RuntimeException('Organization lost a designation or tree member on a legacy tenant.');
    }
    if ($filters && ($data['departments']->pluck('dpt_name')->all() !== ['Engineering']
        || $data['departmentGroups']->pluck('dpt_name')->all() !== ['Engineering'])) {
        throw new RuntimeException('Organization lost its department data on a legacy tenant.');
    }
}

// Render the complete directory template and its stacks independently of the shared workspace shell.
$source = file_get_contents(resource_path('views/admin/organization-directory/index.blade.php'));
$source = str_replace("@extends('admin.layout.app')", '', $source)."\n@stack('styles') @yield('content') @stack('scripts')";
$html = \Illuminate\Support\Facades\Blade::render($source, $data, true);
foreach ([
    'Employee Hierarchy Structure',
    '.orgd-viewport',
    'orgTreeInitialData',
    'data-org-mode="grid"',
    'data-org-mode="cards"',
    'id="orgLevelGrid"',
    'id="orgCardsGrid"',
    'pms:records-updated',
    'loadProfileDetails',
] as $needle) {
    if (!str_contains($html, $needle)) {
        throw new RuntimeException('Organization page is missing expected hierarchy/card/detail element: '.$needle);
    }
}

// Organization tree/cards automatically expose profile endpoints and drop employees deleted or deactivated from Employees.
$treeRequest = Request::create('/organization/tree', 'GET');
$treeRequest->setUserResolver(fn () => $viewer);
$treeResponse = $controller->tree($treeRequest)->getData(true);
$node = $treeResponse['nodes'][0] ?? null;
if (!$node || empty($node['profile_url']) || empty($node['org_show_url'])) {
    throw new RuntimeException('Organization tree node is missing profile_url or org_show_url.');
}
$profileRequest = Request::create($node['profile_url'], 'GET');
$profileRequest->setUserResolver(fn () => $viewer);
$profileData = $controller->profile($profileRequest, (int) $node['id'])->getData(true);
if (($profileData['id'] ?? null) !== 1 || !isset($profileData['sections']['Work profile'], $profileData['sections']['Personal information'])) {
    throw new RuntimeException('Organization profile endpoint did not return full employee details.');
}

// Archiving/deleting an employee from the Employee section immediately removes them from Organization.
DB::table('users')->where('id', 1)->update(['archived_at' => now()]);
$afterDeleteTree = $controller->tree($treeRequest)->getData(true);
if (!empty($afterDeleteTree['nodes'])) {
    throw new RuntimeException('Deleted/archived employee remained visible in Organization.');
}
try {
    $controller->profile($profileRequest, 1);
    throw new RuntimeException('Deleted employee profile should not remain accessible.');
} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    // expected
}

// Restored but marked Inactive in Employee section is also excluded from Organization.
DB::table('users')->where('id', 1)->update(['archived_at' => null]);
DB::table('employee_details')->where('user_id', 1)->update(['status' => 'Inactive']);
$afterInactiveTree = $controller->tree($treeRequest)->getData(true);
if (!empty($afterInactiveTree['nodes'])) {
    throw new RuntimeException('Inactive employee remained visible in Organization.');
}

echo "PASS: Organization search, filters, company isolation, tree/cards sync, full details, delete removal, and legacy schema support.\n";
