<?php

// Run with: php tests/regression/company_leave_payroll.php. Never touches company databases.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{AppSetting, Leave, LeaveBalance, LeavePolicy, LeaveType, User};
use App\Services\{LeaveService, PayrollCalculationService};
use Illuminate\Support\Facades\{Auth, DB, Schema};

config(['database.default' => 'tenant', 'session.driver' => 'array']);
Auth::forgetGuards();
$files = [tempnam(sys_get_temp_dir(), 'leave_alpha_'), tempnam(sys_get_temp_dir(), 'leave_beta_')];
function useLeaveDatabase(string $file): void {
    config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => $file, 'prefix' => '']]);
    DB::purge('tenant');
}
function leaveCheck(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
try {
    foreach ($files as $file) {
        useLeaveDatabase($file);
        foreach ([
            'users' => 'id integer primary key, company_id integer, name text, role text, annual_leave_balance integer, remaining_leaves integer, leaves_taken_this_year integer, last_leave_reset text, updated_at text',
            'leaves' => 'id integer primary key, user_id integer, leave_type_id integer, type text, duration text, date text, start_date text, end_date text, total_days real, paid_days real, unpaid_days real, status text, approved_at text, is_paid integer, is_unpaid integer, payroll_deduction_flag integer, half_day_flag integer, updated_at text, created_at text',
            'leave_balances' => 'id integer primary key, user_id integer, year integer, allocated_leaves real, remaining_leaves real',
            'app_settings' => 'id integer primary key, key text, value text, updated_at text, created_at text',
            'attendances' => 'id integer primary key, user_id integer, date text, status text, half_day text, clock_in text, clock_out text, work_from_type text',
            'task_timers' => 'id integer primary key, user_id integer, start_date text, total_hours real',
        ] as $table => $columns) DB::statement("CREATE TABLE {$table} ({$columns})");
        (require base_path('database/migrations/tenant/2026_02_09_130245_create_leave_policies_table.php'))->up();
        // Create the real leave type and policy schema; leave/balance test tables already contain payroll fields.
        Schema::drop('leaves'); Schema::drop('leave_balances');
        (require base_path('database/migrations/tenant/2026_06_17_010000_upgrade_leave_management_system.php'))->up();
        DB::statement('CREATE TABLE leaves (id integer primary key, user_id integer, leave_type_id integer, type text, duration text, date text, start_date text, end_date text, total_days real, paid_days real, unpaid_days real, status text, approved_at text, is_paid integer, is_unpaid integer, payroll_deduction_flag integer, half_day_flag integer, updated_at text, created_at text)');
        DB::statement('CREATE TABLE leave_balances (id integer primary key, user_id integer, year integer, allocated_leaves real, remaining_leaves real)');
        DB::table('users')->insert(['id' => 1, 'company_id' => 1, 'name' => 'Employee', 'role' => 'employee']);
        $service = new LeaveService;
        leaveCheck($service->leaveTypes()->pluck('code')->all() === ['SL', 'CL'], 'Default types must be only Sick and Casual.');
        leaveCheck((float) $service->policy()->annual_leaves === 18.0, 'Default paid allowance must be 18.');
    }
    useLeaveDatabase($files[0]);
    $service = new LeaveService;
    $custom = LeaveType::create(['name' => 'Bereavement Leave', 'code' => 'BL', 'is_paid' => true, 'is_active' => true]);
    $sick = LeaveType::where('code', 'SL')->firstOrFail();
    foreach ([
        [1, $sick->id, 'sick', '2026-04-01', '2026-04-17', 17],
        [2, $custom->id, 'bl', '2026-04-30', '2026-05-02', 3],
    ] as [$id, $typeId, $type, $start, $end, $days]) {
        DB::table('leaves')->insert(['id' => $id, 'user_id' => 1, 'leave_type_id' => $typeId, 'type' => $type, 'date' => $start, 'start_date' => $start, 'end_date' => $end, 'total_days' => $days, 'status' => 'approved']);
    }
    $normalize = new ReflectionMethod(LeaveService::class, 'normalizeApprovedLeavePayroll');
    $normalize->invoke($service, Leave::orderBy('id')->get(), new LeaveBalance(['allocated_leaves' => 18]));
    leaveCheck((float) Leave::find(1)->paid_days === 17.0, 'Sick category incorrectly limited the shared allowance.');
    leaveCheck((float) Leave::find(2)->paid_days === 1.0 && (float) Leave::find(2)->unpaid_days === 2.0, 'Partial paid/unpaid split failed.');
    $payroll = new PayrollCalculationService;
    $user = User::findOrFail(1);
    $april = $payroll->getLeaveData($user, 2026, 4);
    $may = $payroll->getLeaveData($user, 2026, 5);
    leaveCheck($april['paid_leave'] === 18.0 && $april['unpaid_leave'] === 0.0, 'April leave counted outside its month.');
    leaveCheck($may['paid_leave'] === 0.0 && $may['unpaid_leave'] === 2.0, 'May lost the unpaid portion.');
    AppSetting::create(['key' => 'leave_unpaid_deduction_percentage', 'value' => '2']);
    $attendance = $payroll->getAttendanceData($user, 2026, 5, 25);
    leaveCheck($attendance['payable_days'] === 24.0, '2 unpaid days at 2% must deduct 4% of monthly salary.');
    DB::table('attendances')->insert(['user_id' => 1, 'date' => '2026-05-01', 'status' => 'absent', 'half_day' => 'no']);
    leaveCheck($payroll->getAttendanceData($user, 2026, 5, 25)['payable_days'] === 24.0, 'Unpaid leave was deducted twice.');
    useLeaveDatabase($files[1]);
    LeavePolicy::first()->update(['annual_leaves' => 24]);
    AppSetting::create(['key' => 'leave_unpaid_deduction_percentage', 'value' => '5']);
    leaveCheck(!(new LeaveService)->leaveTypes()->contains('code', 'BL'), 'Custom leave type leaked into another company.');
    useLeaveDatabase($files[0]);
    leaveCheck((float) (new LeaveService)->policy()->annual_leaves === 18.0, 'Another company changed the allowance.');
    leaveCheck(AppSetting::valueFor('leave_unpaid_deduction_percentage') === '2', 'Another company changed the salary deduction.');
    foreach (['index', 'create', 'edit'] as $view) token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path("views/admin/leaves/{$view}.blade.php"))), TOKEN_PARSE);
    foreach (['payslip-view', 'payslip-pdf', 'payslip-print'] as $view) token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path("views/admin/payroll/{$view}.blade.php"))), TOKEN_PARSE);
    echo "Company isolation, default/custom types, paid exhaustion, partial requests, monthly clipping, deduction percentage and templates passed.\n";
} finally {
    DB::purge('tenant');
    foreach ($files as $file) if (is_file($file)) unlink($file);
}
