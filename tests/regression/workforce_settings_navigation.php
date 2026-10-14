<?php

// Uses isolated in-memory databases; never changes company records.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\{DepartmentController, DesignationController, ParentDepartmentController};
use App\Http\Controllers\Admin\Settings\SettingController;
use App\Http\Middleware\{EnsureModuleAccess, RoleMiddleware};
use App\Models\{Department, Designation, ParentDepartment, User};
use App\Support\WorkforceSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};

function checkSettings(bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
}

function settingsRequest(string $name, string $method = 'GET', array $data = [], array $parameters = []): Request
{
    global $app;
    $request = Request::create(route($name, $parameters), $method, $data);
    $route = $app['router']->getRoutes()->match($request);
    $request->setRouteResolver(fn () => $route);
    $request->setUserResolver(fn () => Auth::user());
    $request->setLaravelSession($app['session.store']);
    $app->instance('request', $request);
    $app['url']->setRequest($request);
    return $request;
}

function settingsRedirect($response, string $target): void
{
    checkSettings(parse_url($response->getTargetUrl(), PHP_URL_PATH) === parse_url(route($target), PHP_URL_PATH), 'Redirect escaped its workspace: '.$target);
}

foreach (['tenant', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
$app['session']->start();
Schema::connection('central')->create('companies', function ($t) {
    $t->id(); $t->string('name'); $t->text('settings')->nullable(); $t->softDeletes(); $t->timestamps();
});
DB::connection('central')->table('companies')->insert(['id' => 1, 'name' => 'Fixture', 'settings' => '{}']);
Schema::create('users', function ($t) { $t->id(); $t->unsignedBigInteger('company_id'); });
DB::table('users')->insert(['id' => 1, 'company_id' => 1]);
Schema::create('modules', function ($t) {
    $t->id(); $t->string('slug'); $t->string('name'); $t->boolean('is_active')->default(true);
});
foreach (['department-parent', 'department-sub', 'designations'] as $slug) {
    DB::table('modules')->insert(['slug' => $slug, 'name' => $slug, 'is_active' => true]);
}
foreach (['departments', 'parent_departments'] as $table) {
    Schema::create($table, function ($t) use ($table) {
        $t->id(); $t->string('dpt_name'); $t->string('dpt_code')->unique();
        if ($table === 'departments') $t->unsignedBigInteger('parent_dpt_id')->nullable();
        $t->unsignedBigInteger('added_by')->nullable(); $t->unsignedBigInteger('last_updated_by')->nullable();
        $t->timestamp('archived_at')->nullable(); $t->timestamps();
    });
}
Schema::create('designations', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->string('name'); $t->integer('level');
    $t->string('unique_code')->nullable(); $t->unsignedBigInteger('parent_id')->nullable();
    $t->unsignedBigInteger('added_by')->nullable(); $t->unsignedBigInteger('last_updated_by')->nullable();
    $t->timestamp('archived_at')->nullable(); $t->timestamps();
});
Schema::create('employee_details', function ($t) {
    $t->id(); $t->unsignedBigInteger('department_id')->nullable();
    $t->unsignedBigInteger('parent_dpt_id')->nullable(); $t->unsignedBigInteger('designation_id')->nullable();
});
$actor = new User;
$actor->forceFill(['id' => 1, 'company_id' => 1, 'role' => 'admin']);
Auth::guard('web')->setUser($actor);

// Match every Settings endpoint, including route order and binding parameter names.
$routesChecked = 0;
foreach ($app['router']->getRoutes() as $route) {
    $name = (string) $route->getName();
    if (! preg_match('/^admin\.settings\.(departments|parent-departments|designations)\./', $name)) continue;
    checkSettings(in_array('role:admin', $route->gatherMiddleware(), true), 'Settings endpoint missing admin guard: '.$name);
    checkSettings(in_array('module.access', $route->gatherMiddleware(), true), 'Settings endpoint missing module guard: '.$name);
    $parameters = array_fill_keys($route->parameterNames(), 42);
    $request = settingsRequest($name, $route->methods()[0], [], $parameters);
    checkSettings($request->route()->getName() === $name, 'Route shadowed by resource binding: '.$name);
    $hrName = substr($name, strlen('admin.settings.'));
    $hrRoute = $app['router']->getRoutes()->getByName($hrName);
    checkSettings($hrRoute && $hrRoute->getActionName() === $route->getActionName(), 'Feature differs between Settings and HR: '.$name);
    checkSettings(WorkforceSettings::route($hrName, $parameters) === route($name, $parameters), 'Settings URL escaped: '.$name);
    checkSettings(WorkforceSettings::name('dashboard') === 'dashboard', 'Unrelated route was changed');
    $expectedModule = str_contains($name, '.parent-departments.') ? 'department-parent' : (str_contains($name, '.departments.') ? 'department-sub' : 'designations');
    checkSettings((new EnsureModuleAccess)->moduleForRoute($name)?->slug === $expectedModule, 'Settings lost the feature permission identity: '.$name);
    $routesChecked++;
}
checkSettings($routesChecked === 39, 'Expected all 39 Settings feature endpoints, got '.$routesChecked);

foreach (['parent-departments', 'departments', 'designations'] as $feature) {
    settingsRequest($feature.'.index');
    checkSettings(! WorkforceSettings::active(), 'HR detected as Settings');
    checkSettings(WorkforceSettings::route($feature.'.create') === route($feature.'.create'), 'HR URL changed');
}

// Verify the actual Settings dashboard destinations and non-admin visibility.
settingsRequest('admin.settings.index');
$groups = (new SettingController)->index()->getData()['settingsGroups'];
checkSettings($groups['departments']['dropdown_options'][0]['route'] === 'admin.settings.parent-departments.index', 'Settings parent link points to HR');
checkSettings($groups['departments']['dropdown_options'][1]['route'] === 'admin.settings.departments.index', 'Settings sub link points to HR');
checkSettings($groups['designations']['route'] === 'admin.settings.designations.index', 'Settings designation link points to HR');
foreach (['hr', 'manager', 'employee'] as $role) {
    $actor->role = $role;
    $groups = (new SettingController)->index()->getData()['settingsGroups'];
    checkSettings(! isset($groups['departments'], $groups['designations']), 'Non-admin sees master-data settings');
    $request = settingsRequest('admin.settings.designations.index');
    $response = (new RoleMiddleware)->handle($request, fn () => response('UNEXPECTED', 204), 'admin');
    checkSettings($response->getStatusCode() === 302, 'Non-admin passed admin Settings guard');
}
$actor->role = 'admin';
checkSettings((new RoleMiddleware)->handle(settingsRequest('admin.settings.designations.index'), fn () => response('', 204), 'admin')->getStatusCode() === 204, 'Admin cannot access Settings');
DB::table('modules')->where('slug', 'designations')->update(['is_active' => false]);
try {
    (new EnsureModuleAccess)->handle(settingsRequest('admin.settings.designations.index'), fn () => response('', 204));
    throw new RuntimeException('Settings bypassed a disabled feature');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkSettings($e->getStatusCode() === 403, 'Wrong disabled-feature response');
}
DB::table('modules')->where('slug', 'designations')->update(['is_active' => true]);

// Execute real controller mutations and inspect persisted records and redirects.
foreach (['admin.settings.', ''] as $prefix) {
    $parentController = new ParentDepartmentController;
    $request = settingsRequest($prefix.'parent-departments.store', 'POST', ['dpt_name' => 'Parent '.$prefix, 'code_generation_mode' => 'custom', 'dpt_code' => 'P'.($prefix ? 'SET' : 'HR')]);
    settingsRedirect($parentController->store($request), $prefix.'parent-departments.index');
    $parent = ParentDepartment::orderByDesc('id')->firstOrFail();
    $request = settingsRequest($prefix.'parent-departments.update', 'PUT', ['dpt_name' => 'Updated parent '.$prefix, 'dpt_code' => $parent->dpt_code], [$parent->id]);
    settingsRedirect($parentController->update($request, $parent), $prefix.'parent-departments.index');
    checkSettings(str_starts_with($parent->fresh()->dpt_name, 'Updated'), 'Parent update not saved');

    $departmentController = new DepartmentController;
    $data = ['dpt_name' => 'Sub '.$prefix, 'parent_dpt_id' => $parent->id, 'code_generation_mode' => 'auto'];
    if ($prefix) $data['return_url'] = route('departments.index');
    $request = settingsRequest($prefix.'departments.store', 'POST', $data);
    settingsRedirect($departmentController->store($request), $prefix.'departments.index');
    $department = Department::orderByDesc('id')->firstOrFail();
    $request = settingsRequest($prefix.'departments.update', 'PUT', ['dpt_name' => 'Updated sub '.$prefix, 'parent_dpt_id' => $parent->id, 'dpt_code' => $department->dpt_code], [$department->id]);
    settingsRedirect($departmentController->update($request, $department), $prefix.'departments.index');
    checkSettings(str_starts_with($department->fresh()->dpt_name, 'Updated'), 'Sub update not saved');
    $request = settingsRequest($prefix.'departments.destroy', 'DELETE', [], [$department->id]);
    settingsRedirect($departmentController->destroy($request, $department), $prefix.'departments.index');
    checkSettings($department->fresh()->archived_at !== null, 'Sub archive not saved');
    settingsRequest($prefix.'departments.restore', 'POST', [], [$department->id]);
    settingsRedirect($departmentController->restore($department->id), $prefix.'departments.archive');
    checkSettings($department->fresh()->archived_at === null, 'Sub restore not saved');

    $request = settingsRequest($prefix.'parent-departments.destroy', 'DELETE', [], [$parent->id]);
    settingsRedirect($parentController->destroy($request, $parent), $prefix.'parent-departments.index');
    checkSettings($parent->fresh()->archived_at !== null, 'Parent archive not saved');
    settingsRequest($prefix.'parent-departments.restore', 'POST', [], [$parent->id]);
    settingsRedirect($parentController->restore($parent->id), $prefix.'parent-departments.archive');
    checkSettings($parent->fresh()->archived_at === null, 'Parent restore not saved');

    $designationController = new DesignationController;
    $request = settingsRequest($prefix.'designations.store', 'POST', ['name' => 'Title '.$prefix, 'level' => 1, 'code_generation_mode' => 'auto']);
    settingsRedirect($designationController->store($request), $prefix.'designations.index');
    $designation = Designation::orderByDesc('id')->firstOrFail();
    $request = settingsRequest($prefix.'designations.update', 'PUT', ['name' => 'Updated title '.$prefix, 'level' => 2], [$designation->id]);
    settingsRedirect($designationController->update($request, $designation), $prefix.'designations.index');
    checkSettings($designation->fresh()->level === 2, 'Designation update not saved');
    settingsRequest($prefix.'designations.archive.action', 'POST', [], [$designation->id]);
    settingsRedirect($designationController->archiveDesignation($designation), $prefix.'designations.index');
    checkSettings($designation->fresh()->archived_at !== null, 'Designation archive not saved');
    settingsRequest($prefix.'designations.restore', 'POST', [], [$designation->id]);
    settingsRedirect($designationController->restore($designation->id), $prefix.'designations.archive');
    checkSettings($designation->fresh()->archived_at === null, 'Designation restore not saved');
}

$templatesChecked = 0;
foreach (['departments', 'parent_departments', 'designations'] as $directory) {
    foreach (glob(resource_path('views/admin/'.$directory.'/*.blade.php')) as $file) {
        $source = file_get_contents($file);
        token_get_all(app('blade.compiler')->compileString($source), TOKEN_PARSE);
        checkSettings(! preg_match('/(?<!::)\broute\([\'"](?:departments|parent-departments|designations)\./', $source), 'Template has a link escaping Settings: '.$file);
        $templatesChecked++;
    }
}
echo "PASS: {$routesChecked} admin Settings endpoints, role guards, dashboard links, HR compatibility, create/edit/archive/restore workflows in both workspaces, and {$templatesChecked} templates.\n";
