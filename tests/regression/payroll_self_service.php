<?php

// Run with: php tests/regression/payroll_self_service.php
// Employee Salary roster and HR self-restriction, Processing attendance/leave/work data, My Payslips
// (own released payslips only) and the company-branded payslip document.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\{MyPayslipController, PayrollController};
use App\Models\{EmployeeSalaryAssignment, Payslip, User};
use App\Services\PayrollCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Symfony\Component\HttpKernel\Exception\HttpException;

foreach (['tenant', 'mysql', 'central'] as $c) {
    config(["database.connections.$c" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false]]);
    DB::purge($c);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant', 'cache.default' => 'array', 'session.driver' => 'array', 'app.timezone' => 'Asia/Kolkata']);
function check(bool $ok, string $message): void { if (! $ok) throw new RuntimeException($message); }

Schema::create('users', function ($t) { $t->id(); $t->string('name'); $t->string('email')->nullable(); $t->string('role')->nullable(); $t->unsignedBigInteger('company_id')->nullable(); $t->unsignedBigInteger('company_staff_role_id')->nullable(); $t->timestamp('archived_at')->nullable(); $t->string('password')->nullable(); $t->timestamps(); });
Schema::create('employee_details', function ($b) { $b->id(); $b->unsignedBigInteger('user_id'); $b->string('employee_id')->nullable(); $b->unsignedBigInteger('designation_id')->nullable(); $b->unsignedBigInteger('department_id')->nullable(); $b->string('business_address')->nullable(); $b->string('employment_type')->nullable(); $b->string('mobile')->nullable(); $b->date('joining_date')->nullable(); $b->decimal('basic_salary', 12, 2)->nullable(); $b->decimal('hra_amount', 12, 2)->nullable(); $b->decimal('special_allowance', 12, 2)->nullable(); $b->timestamps(); });
Schema::create('designations', function ($b) { $b->id(); $b->string('name')->nullable(); $b->integer('level')->nullable(); $b->unsignedBigInteger('company_id')->nullable(); $b->unsignedBigInteger('added_by')->nullable(); $b->unsignedBigInteger('parent_id')->nullable(); $b->timestamps(); });
Schema::create('departments', function ($b) { $b->id(); $b->string('dpt_name')->nullable(); $b->unsignedBigInteger('company_id')->nullable(); $b->unsignedBigInteger('added_by')->nullable(); $b->timestamps(); });
Schema::create('companies', function ($b) { $b->id(); $b->string('name'); $b->string('address')->nullable(); $b->string('email')->nullable(); $b->string('phone')->nullable(); $b->string('website')->nullable(); $b->string('logo')->nullable(); $b->string('gst_number')->nullable(); $b->string('pan_number')->nullable(); $b->string('payslip_prefix')->nullable(); $b->string('db_name')->nullable(); $b->timestamps(); $b->softDeletes(); });
Schema::create('company_staff_roles', function ($b) { $b->id(); $b->unsignedBigInteger('company_id'); $b->string('name'); $b->string('access_role'); $b->timestamps(); });
Schema::create('leaves', function ($b) { $b->id(); $b->unsignedBigInteger('user_id'); $b->string('type')->nullable(); $b->string('duration')->nullable(); $b->date('date')->nullable(); $b->date('start_date')->nullable(); $b->date('end_date')->nullable(); $b->decimal('total_days')->nullable(); $b->decimal('paid_days')->nullable(); $b->boolean('is_unpaid')->default(false); $b->boolean('half_day_flag')->default(false); $b->string('status')->nullable(); $b->timestamps(); });
foreach (['time_logs', 'task_timers'] as $table) Schema::create($table, function ($b) { $b->id(); $b->unsignedBigInteger('user_id'); $b->date('start_date')->nullable(); $b->decimal('total_hours', 8, 2)->default(0); $b->timestamps(); });
foreach (['app_settings', 'attendances', 'leave_balances', 'leave_policies', 'holidays', 'company_settings'] as $table) {
    Schema::create($table, function ($b) { $b->id(); $b->unsignedBigInteger('user_id')->nullable(); $b->unsignedBigInteger('company_id')->nullable(); $b->integer('year')->nullable(); $b->date('date')->nullable(); $b->string('status')->nullable(); $b->string('clock_in')->nullable(); $b->string('clock_out')->nullable(); $b->string('half_day')->nullable(); $b->string('work_from_type')->nullable(); $b->decimal('total_hours')->nullable(); $b->decimal('annual_leaves')->nullable(); $b->decimal('allocated_leaves')->nullable(); $b->decimal('remaining_leaves')->nullable(); $b->string('key')->nullable(); $b->text('value')->nullable(); $b->string('type')->nullable(); $b->string('occassion')->nullable(); $b->string('title')->nullable(); $b->timestamps(); });
}
App\Services\PayrollSchema::ensure();

DB::table('companies')->insert(['id' => 3, 'name' => 'Google', 'address' => 'Salt Lake, Kolkata', 'payslip_prefix' => 'GGL']);
DB::table('designations')->insert([['id' => 1, 'name' => 'Software Engineer', 'level' => 3, 'company_id' => 3], ['id' => 2, 'name' => 'HR Lead', 'level' => 2, 'company_id' => 3]]);
DB::table('departments')->insert(['id' => 1, 'dpt_name' => 'Engineering', 'company_id' => 3]);
DB::table('company_staff_roles')->insert(['id' => 1, 'company_id' => 3, 'name' => 'Associate Manager', 'access_role' => 'manager']);
DB::table('users')->insert([
    ['id' => 1, 'name' => 'Admin', 'role' => 'admin', 'company_id' => 3, 'email' => 'admin@x', 'company_staff_role_id' => null, 'archived_at' => null],
    ['id' => 2, 'name' => 'Saraswata', 'role' => 'hr', 'company_id' => 3, 'email' => 'sara@x', 'company_staff_role_id' => null, 'archived_at' => null],
    ['id' => 3, 'name' => 'Raam', 'role' => 'employee', 'company_id' => 3, 'email' => 'raam@x', 'company_staff_role_id' => null, 'archived_at' => null],
    ['id' => 4, 'name' => 'Arjun', 'role' => 'manager', 'company_id' => 3, 'email' => 'arjun@x', 'company_staff_role_id' => 1, 'archived_at' => null],
    ['id' => 5, 'name' => 'Client Co', 'role' => 'client', 'company_id' => 3, 'email' => 'client@x', 'company_staff_role_id' => null, 'archived_at' => null],
    ['id' => 6, 'name' => 'Archived', 'role' => 'employee', 'company_id' => 3, 'email' => 'old@x', 'company_staff_role_id' => null, 'archived_at' => now()],
]);
foreach ([2 => ['GOOGLE-EMP-0001', 2], 3 => ['GOOGLE-EMP-0002', 1], 4 => ['GOOGLE-EMP-0003', 1]] as $uid => [$code, $designation]) {
    DB::table('employee_details')->insert(['user_id' => $uid, 'employee_id' => $code, 'designation_id' => $designation, 'department_id' => 1, 'employment_type' => 'full_time', 'mobile' => '98765', 'joining_date' => '2025-01-10', 'business_address' => 'Kolkata']);
}
// Every user may use payroll for this test; the rules under test are HR self-restriction and ownership.
$as = function (int $id) {
    $user = User::find($id) ?? throw new RuntimeException("user {$id}");
    $actor = new class extends User { protected $table = 'users'; public function hasModulePermission(string $moduleSlug, string $permission = 'view'): bool { return true; } };
    $actor->setRawAttributes($user->getAttributes(), true); $actor->exists = true;
    Auth::guard('web')->setUser($actor);
    request()->setUserResolver(fn () => $actor);
    return $actor;
};
$controller = new PayrollController;
$salary = fn (int $userId, float $basic = 50000) => Request::create('/payroll/employee-salary', 'POST', [
    'user_id' => $userId, 'actual_basic_salary' => $basic, 'hra_type' => 'percentage', 'hra_value' => 40,
    'special_allowance_type' => 'percentage', 'special_allowance_value' => 20, 'effective_from' => '2026-01-01', 'status' => 'active',
]);
// Renders only a page's content section (the admin layout/sidebar is covered by other tests).
$render = function (string $name, array $data) {
    $source = file_get_contents(view()->getFinder()->find($name));
    $source = preg_replace(['/@extends\([^)]*\)/', "/@section\('title',.*\)
/"], '', $source);
    $source = str_replace("@section('content')", '', $source);
    $source = substr($source, 0, strrpos($source, '@endsection'));
    return Illuminate\Support\Facades\Blade::render($source, $data + ['errors' => new Illuminate\Support\ViewErrorBag]);
};

// --- Employee Salary: HR manages everyone except themselves -------------------------------------------
$as(2);
session()->forget('error');
$controller->storeEmployeeSalary($salary(2));
check(EmployeeSalaryAssignment::where('user_id', 2)->doesntExist() && str_contains((string) session('error'), 'set by the company Admin'), 'HR assigned their own salary.');
session()->forget('error');
$controller->storeEmployeeSalary($salary(3, 30000));
$controller->storeEmployeeSalary($salary(4, 40000));
check(EmployeeSalaryAssignment::whereIn('user_id', [3, 4])->count() === 2, 'HR could not assign other employees.');
$as(1);
$controller->storeEmployeeSalary($salary(2, 45000));
$hrAssignment = EmployeeSalaryAssignment::where('user_id', 2)->first();
check((bool) $hrAssignment, 'Admin could not set the HR salary.');
$as(2);
session()->forget('error');
$controller->updateEmployeeSalary(Request::create('/x', 'PUT', $salary(2, 99999)->all()), $hrAssignment);
check((float) $hrAssignment->fresh()->actual_basic_salary === 45000.0, 'HR edited their own salary.');
$controller->destroyEmployeeSalary(Request::create('/x', 'DELETE'), $hrAssignment);
check(EmployeeSalaryAssignment::whereKey($hrAssignment->id)->exists(), 'HR deleted their own salary.');

$page = $controller->employeeSalary(Request::create('/payroll/employee-salary', 'GET'))->getData();
check($page['roster']->pluck('id')->sort()->values()->all() === [2, 3, 4], 'Roster must list HR, employees and company roles (no admin, client or archived): ' . $page['roster']->pluck('id')->implode(','));
check($page['lockedUserId'] === 2 && ! $page['employees']->contains('id', 2), 'HR can pick themselves in Assign Salary.');
check($page['rosterSummary'] === ['total' => 3, 'assigned' => 3, 'unassigned' => 0], 'Roster summary wrong.');
$html = $render('admin.payroll.employee-salary', $page);
foreach (['GOOGLE-EMP-0003', 'Associate Manager', '🔒 Set by Admin', 'Full Time', 'Kolkata'] as $text) check(str_contains($html, $text), "Employee Salary page missing '{$text}'.");
$unassigned = $controller->employeeSalary(Request::create('/x', 'GET', ['salary' => 'unassigned']))->getData()['roster'];
check($unassigned->isEmpty(), 'Unassigned filter wrong.');

// --- Processing line: attendance, leave (paid/unpaid/pending) and work logged ------------------------
DB::table('leaves')->insert([
    ['user_id' => 3, 'date' => '2026-10-21', 'start_date' => '2026-10-21', 'end_date' => '2026-10-22', 'total_days' => 2, 'paid_days' => 1, 'status' => 'approved'],
    ['user_id' => 3, 'date' => '2026-10-27', 'start_date' => '2026-10-27', 'end_date' => '2026-10-29', 'total_days' => 3, 'paid_days' => null, 'status' => 'pending'],
]);
DB::table('time_logs')->insert([['user_id' => 3, 'start_date' => '2026-10-05', 'total_hours' => 6.5], ['user_id' => 3, 'start_date' => '2026-09-30', 'total_hours' => 9]]);
DB::table('task_timers')->insert(['user_id' => 3, 'start_date' => '2026-10-06', 'total_hours' => 2.25]);
$service = app(PayrollCalculationService::class);
$line = $service->calculateEmployeePayrollLine(User::find(3), 2026, 10, 22);
check($line['pending_leave_requests'] === 1 && (float) $line['pending_leave_days'] === 3.0, 'Pending leave missing.');
check((float) $line['unpaid_leave'] === 1.0 && (float) $line['paid_leave'] === 1.0, 'Paid/unpaid leave wrong.');
check((float) $line['timesheet_hours'] === 6.5 && (float) $line['task_timer_hours'] === 2.25 && (float) $line['work_hours'] === 8.75, 'Work logged wrong.');
check($line['employment_type'] === 'Full Time' && $line['joining_date'] === '10 Jan 2025', 'Employee details missing.');
check(collect($line['deduction_reasons'])->contains(fn ($r) => str_contains($r, 'unpaid leave')) && $line['loss_of_pay_amount'] > 0, 'Deduction reasons / loss of pay missing.');

// --- Run, approve, payslips ----------------------------------------------------------------------------
$as(1);
$run = $service->processPayrollRun(3, 2026, 10, 22, 'all', 'all', 1);
$service->approvePayroll($run->fresh(), 1);
check(Payslip::count() === 3, 'Payslips not generated for every employee: ' . Payslip::count());
$hrSlip = Payslip::where('user_id', 2)->first();
$raamSlip = Payslip::where('user_id', 3)->first();
check(str_starts_with($raamSlip->payslip_number, 'GGL-'), 'Company payslip prefix not used.');

// HR: own payslip leaves the payroll workspace and opens under My Payslips.
$as(2);
check(! $controller->payslips(Request::create('/payroll/payslips', 'GET'))->getData()['payslips']->getCollection()->contains('user_id', 2), 'HR sees own payslip in the payroll workspace.');
check($controller->viewPayslip($hrSlip->id)->getTargetUrl() === route('my-payslips.show', $hrSlip->id), 'HR own payslip not redirected to My Payslips.');
$my = new MyPayslipController;
$mine = $my->index(Request::create('/my-payslips', 'GET'))->getData();
check($mine['payslips']->pluck('user_id')->unique()->values()->all() === [2], 'My Payslips shows other people\'s payslips.');
foreach ([$raamSlip->id] as $foreign) {
    try { $my->show(Request::create('/x'), $foreign); throw new RuntimeException('HR opened another employee\'s payslip via My Payslips.'); }
    catch (HttpException $e) { check($e->getStatusCode() === 404, 'Wrong status for foreign payslip.'); }
}
check(str_contains($render('my-payslips.index', $mine), $hrSlip->payslip_number), 'My Payslips page does not list the payslip.');

// Employee: only their own; unreleased runs stay hidden.
$as(3);
check($my->index(Request::create('/my-payslips', 'GET'))->getData()['payslips']->pluck('id')->all() === [$raamSlip->id], 'Employee My Payslips wrong.');
$document = $my->document(Request::create('/x'), $raamSlip->id)->render();
foreach (['Google', 'Leave Summary', 'Pending approval', 'Work Summary', '8.75 h', 'Work logged', 'Deduction Details', 'Loss of pay', 'Full Time'] as $text) {
    check(str_contains($document, $text), "Payslip document missing '{$text}'.");
}
$pdf = $my->pdf(Request::create('/x'), $raamSlip->id);
check(str_starts_with((string) $pdf->getContent(), '%PDF'), 'Payslip PDF not generated.');
DB::table('payrolls')->where('id', $run->id)->update(['status' => 'calculated']);
check($my->index(Request::create('/my-payslips', 'GET'))->getData()['payslips']->isEmpty(), 'Unreleased payslip visible to the employee.');

// Processing / payslip pages compile.
foreach (['admin.payroll.processing', 'admin.payroll.payslips', 'admin.payroll.history', 'admin.payroll.payslip-view', 'my-payslips.show', 'admin.layout.manu'] as $template) {
    token_get_all(app('blade.compiler')->compileString(file_get_contents(view()->getFinder()->find($template))), TOKEN_PARSE);
}
echo "PASS: Employee Salary roster + HR self-lock, processing attendance/leave/pending/work data, deduction reasons, company payslips, My Payslips ownership and PDF.\n";
