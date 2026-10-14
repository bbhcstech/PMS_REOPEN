<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{User, CompanyStaffRole};
use Illuminate\Support\Facades\{Auth, Blade, DB, Schema};
use Illuminate\Http\Request;

// Isolate database access before any profile rendering or view composers run.
config(['database.default' => 'tenant', 'session.driver' => 'array']);
foreach (['tenant', 'central', 'session_db'] as $connection) {
    config(['database.connections.'.$connection => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}

// Render the original profile breadcrumb, including its role-dependent route calls.
$source = file_get_contents(resource_path('views/profile/edit.blade.php'));
$start = strpos($source, '@if(Auth::check())');
$end = strpos($source, '</li>', $start);
$breadcrumb = substr($source, $start, $end - $start);
foreach (['manager', 'admin'] as $accessRole) {
    $user = new User(['id' => 7, 'company_id' => 2, 'role' => $accessRole, 'company_staff_role_id' => 3]);
    $user->setRelation('companyStaffRole', new CompanyStaffRole(['company_id' => 2, 'name' => 'Associate Manager', 'access_role' => $accessRole]));
    Auth::guard('web')->setUser($user);
    $html = Blade::render($breadcrumb, ['logoVersion' => 1]);
    if (!str_contains($html, 'href="'.route('dashboard').'"')) throw new RuntimeException('Profile breadcrumb did not link to the role-aware dashboard');
    if (!str_contains($html, 'Micro Poem '.ucfirst($accessRole))) throw new RuntimeException('Existing profile label changed');
}
echo "PASS: Associate Manager and Admin profile breadcrumbs render without missing-route errors; dashboard links and labels preserved.\n";

// Exercise the real controller and all profile forms on a tenant with no designation table.
// The prior unconditional query failed before even displaying a manager's profile.
app('view')->share('errors', new Illuminate\Support\ViewErrorBag);
$controller = new App\Http\Controllers\ProfileController;
foreach (['manager', 'admin', 'hr'] as $role) {
    $user = (new User)->forceFill([
        'id' => 7, 'company_id' => 2, 'role' => $role,
        'name' => 'Associate Manager', 'email' => 'associate@example.test', 'mobile' => '+919876543210',
    ]);
    $user->setRelation('employeeDetail', null);
    $user->setRelation('companyStaffRole', new CompanyStaffRole(['company_id' => 2, 'name' => 'Associate Manager', 'access_role' => $role]));
    Auth::guard('web')->setUser($user);
    $request = Request::create('/profile');
    $request->setUserResolver(fn () => $user);
    $view = $controller->edit($request);
    if ($view->getData()['user'] !== $user || ! $view->getData()['designations']->isEmpty()) throw new RuntimeException('Non-employee profile changed or loaded unrelated designations.');
    $body = preg_replace('/^@extends\([^\n]+\)\s*$/m', '', $source);
    $html = Blade::render($body."\n@yield('content')", $view->getData());
    foreach (['associate@example.test', route('profile.update'), route('password.update'), route('profile.destroy'), '9876543210'] as $expected) {
        if (! str_contains($html, $expected)) throw new RuntimeException('Profile content or existing settings form missing: '.$expected);
    }
    if (str_contains($html, 'id="designation"')) throw new RuntimeException('Designation picker unexpectedly exposed to manager/admin/HR.');
}
if (Schema::hasTable('designations')) throw new RuntimeException('Profile opening unexpectedly created designation data.');

Schema::create('designations', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id'); $t->unsignedBigInteger('added_by')->nullable();
    $t->string('name'); $t->string('status'); $t->integer('order');
});
Schema::create('users', function ($t) { $t->id(); $t->unsignedBigInteger('company_id'); });
DB::table('designations')->insert([
    ['company_id' => 2, 'name' => 'Employee Designation', 'status' => 'active', 'order' => 1],
    ['company_id' => 2, 'name' => 'Inactive', 'status' => 'inactive', 'order' => 2],
    ['company_id' => 3, 'name' => 'Other company', 'status' => 'active', 'order' => 1],
]);
$user = (new User)->forceFill(['id' => 8, 'company_id' => 2, 'role' => 'employee']);
Auth::guard('web')->setUser($user);
$request = Request::create('/profile');
$request->setUserResolver(fn () => $user);
$designations = $controller->edit($request)->getData()['designations'];
if ($designations->pluck('name')->all() !== ['Employee Designation']) throw new RuntimeException('Existing employee designation filtering or company isolation changed.');
echo "PASS: manager/admin/HR profile forms render without designation dependencies; employee picker retains active, company-isolated options.\n";
