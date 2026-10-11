<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PayrollSchema;
use Illuminate\Support\Facades\{DB, Schema, Auth};
use Illuminate\Http\Request;

config(['database.default' => 'central', 'cache.default' => 'array', 'session.driver' => 'array']);
foreach (['central', 'tenant'] as $connection) {
    config(['database.connections.'.$connection => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
Schema::connection('central')->create('sentinel', fn ($t) => $t->id());
DB::setDefaultConnection('tenant');
Schema::create('users', function ($t) {
    $t->id(); $t->string('name'); $t->string('email'); $t->string('role'); $t->unsignedBigInteger('company_id');
});
Schema::create('employee_details', function ($t) {
    $t->id(); $t->unsignedBigInteger('user_id'); $t->string('employee_id')->nullable();
    $t->unsignedBigInteger('designation_id')->nullable(); $t->unsignedBigInteger('department_id')->nullable();
});
foreach (['departments', 'designations'] as $table) Schema::create($table, function ($t) { $t->id(); $t->string('name'); });
DB::table('users')->insert([
    ['id' => 1, 'name' => 'HR', 'email' => 'hr@example.test', 'role' => 'hr', 'company_id' => 5],
    ['id' => 2, 'name' => 'Employee', 'email' => 'employee@example.test', 'role' => 'employee', 'company_id' => 5],
]);
$base = require database_path('migrations/tenant/2026_06_20_000006_create_enterprise_payroll_tables.php');
$base->up();
DB::table('payrolls')->insert(['id' => 1, 'status' => 'approved', 'generated_by' => 1, 'period_start' => '2026-10-01', 'period_end' => '2026-10-31']);
Schema::drop('payslips'); // Partial deployment: payrolls exists, but Payslips does not.
DB::setDefaultConnection('central');
$interrupt = true;
DB::listen(function ($query) use (&$interrupt) {
    if ($interrupt && str_contains($query->sql, 'create table "payslips"')) {
        $interrupt = false;
        throw new RuntimeException('Simulated interrupted repair');
    }
});
try {
    PayrollSchema::ensure();
    throw new RuntimeException('Repair interruption was hidden.');
} catch (RuntimeException $e) {
    if ($e->getMessage() !== 'Simulated interrupted repair') throw $e;
}
if (DB::getDefaultConnection() !== 'central') throw new RuntimeException('Failed repair did not restore default connection.');
PayrollSchema::ensure();
if (DB::getDefaultConnection() !== 'central' || Schema::hasTable('payrolls')) throw new RuntimeException('Tenant repair touched central or failed to restore connection.');
DB::setDefaultConnection('tenant');
if ((int) DB::table('payrolls')->value('company_id') !== 5) throw new RuntimeException('Existing payroll ownership was lost.');
DB::table('employee_salary_assignments')->insert(['user_id' => 2, 'company_id' => 5, 'actual_basic_salary' => 25000]);
Schema::table('payroll_formulas', fn ($t) => $t->dropColumn('effective_from'));
PayrollSchema::ensure(); // Previously cached success must not hide a missing effective date.
if (! Schema::hasColumn('payroll_formulas', 'effective_from')) throw new RuntimeException('Stale schema cache prevented repair.');
Schema::drop('payslips');
PayrollSchema::ensure();
PayrollSchema::ensure();
if (DB::table('employee_salary_assignments')->count() !== 1 || (float) DB::table('employee_salary_assignments')->value('actual_basic_salary') !== 25000.0) throw new RuntimeException('Repair changed salary records.');

$hr = new class extends App\Models\User {
    public function hasModulePermission(string $moduleSlug, string $permission = 'view'): bool { return true; }
};
$hr->forceFill(['id' => 1, 'name' => 'HR', 'role' => 'hr', 'company_id' => 5]);
Auth::guard('web')->setUser($hr);
DB::table('payroll_histories')->insert([
    'payroll_id' => 1, 'user_id' => 2, 'company_id' => 5, 'payroll_status' => 'approved',
    'snapshot' => json_encode(['basic' => 25000, 'gross_salary' => 25000, 'net_pay' => 25000]),
]);
$controller = new App\Http\Controllers\PayrollController;
$view = $controller->payslips(Request::create('/payroll/payslips', 'GET', ['month' => 10, 'year' => 2026]));
if ($view->name() !== 'admin.payroll.payslips' || $view->getData()['payslips']->total() !== 1) throw new RuntimeException('HR Payslips failed after repair.');
$slip = App\Models\Payslip::firstOrFail();
if ((int) $slip->company_id !== 5 || (float) $slip->net_salary !== 25000.0) throw new RuntimeException('Payslip generation failed or changed the amount.');
app('view')->share('errors', new Illuminate\Support\ViewErrorBag);
Schema::connection('central')->create('companies', function ($t) {
    $t->id(); $t->string('name'); $t->string('address')->nullable(); $t->softDeletes();
});
DB::connection('central')->table('companies')->insert(['id' => 5, 'name' => 'Company 5']);
foreach ([
    'admin.payroll.payslips' => $view->getData(),
    'admin.payroll.payslip-view' => $controller->viewPayslip($slip->id)->getData(),
] as $name => $data) {
    $body = preg_replace('/^@extends\([^\n]+\)\s*$/m', '', file_get_contents(app('view')->getFinder()->find($name)));
    $html = Illuminate\Support\Facades\Blade::render($body."\n@yield('content')", $data);
    if (! str_contains($html, 'Employee')) throw new RuntimeException('Payslip view content did not render.');
}
DB::table('users')->insert(['id' => 3, 'name' => 'Other company', 'email' => 'other@example.test', 'role' => 'employee', 'company_id' => 6]);
DB::table('salary_structures')->insert([
    ['name' => 'Known owner', 'code' => 'KNOWN', 'created_by' => 1, 'company_id' => null],
    ['name' => 'Ambiguous owner', 'code' => 'UNKNOWN', 'created_by' => null, 'company_id' => null],
    ['name' => 'Existing owner', 'code' => 'EXPLICIT', 'created_by' => 1, 'company_id' => 6],
]);
(require database_path('migrations/tenant/2026_10_12_000001_add_payroll_company_scope.php'))->up();
$owners = DB::table('salary_structures')->pluck('company_id', 'code');
if ((int) $owners['KNOWN'] !== 5 || $owners['UNKNOWN'] !== null || (int) $owners['EXPLICIT'] !== 6) throw new RuntimeException('Ownership inference crossed companies or overwrote existing ownership.');
echo "PASS: partial payroll deployment and stale cache repaired in tenant only; records preserved; HR Payslips generates correct amounts and renders list/document.\n";
