<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\{AppraisalController, Settings\PerformanceSettingsController};
use App\Models\{Appraisal, AppSetting, RolePermission, User};
use App\Services\{AppraisalMetrics, AppraisalPolicy, CompanyContext};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Blade, DB, Schema};
use Carbon\Carbon;

config(['database.default' => 'tenant', 'database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'app.timezone' => 'Asia/Kolkata']);
DB::purge('tenant');
Carbon::setTestNow(Carbon::parse('2026-10-14 12:00:00', 'Asia/Kolkata'));
$app->instance(CompanyContext::class, new class extends CompanyContext {
    public function current() { return new class { public function hasFeature($slug) { return true; } }; }
});
Schema::create('users', function ($t) {
    $t->id(); $t->integer('company_id'); $t->string('name'); $t->string('email'); $t->string('role');
    $t->boolean('is_active')->default(true); $t->string('designation')->nullable(); $t->date('joining_date')->nullable();
});
Schema::create('modules', function ($t) { $t->id(); $t->string('slug'); $t->boolean('is_active')->default(true); });
DB::table('modules')->insert(['slug' => 'appraisal', 'is_active' => true]);
Schema::create('role_permissions', function ($t) {
    $t->id(); $t->string('role'); $t->integer('module_id');
    foreach (['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'] as $action) $t->boolean('can_'.$action)->default(false);
    $t->timestamps();
});
Schema::create('app_settings', function ($t) {
    $t->id(); $t->string('key'); $t->string('value'); $t->string('label')->nullable(); $t->string('page')->nullable();
    $t->string('section')->nullable(); $t->string('type')->nullable(); $t->timestamps();
});
Schema::create('attendances', function ($t) { $t->id(); $t->integer('company_id'); $t->integer('user_id'); $t->date('date'); $t->string('status'); $t->timestamp('archived_at')->nullable(); });
Schema::create('tasks', function ($t) {
    $t->id(); $t->integer('company_id'); $t->integer('project_id')->nullable(); $t->string('assigned_to');
    $t->date('due_date')->nullable(); $t->date('start_date')->nullable(); $t->boolean('is_completed')->default(false);
    $t->string('status'); $t->timestamp('completed_on')->nullable(); $t->timestamps();
});
Schema::create('assigned_task_user', function ($t) { $t->integer('task_id'); $t->integer('user_id'); });
foreach (['2026_08_15_000006_create_appraisals_table.php', '2026_10_08_000004_add_deleted_at_to_appraisals_table.php', '2026_10_14_000002_add_appraisal_policy_snapshot.php'] as $file) (require database_path('migrations/tenant/'.$file))->up();
foreach ([[1, 1, 'admin'], [2, 1, 'hr'], [3, 1, 'employee'], [4, 1, 'manager'], [5, 2, 'employee'], [6, 1, 'hr']] as [$id, $company, $role]) {
    DB::table('users')->insert(['id' => $id, 'company_id' => $company, 'role' => $role, 'name' => 'Person '.$id, 'email' => 'person'.$id.'@example.test']);
}
$check = function ($condition, $message) { if (!$condition) throw new RuntimeException($message); };
$denied = function ($call, $status = 403) use ($check) {
    try { $call(); throw new RuntimeException('Unauthorized appraisal mutation succeeded'); }
    catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { $check($e->getStatusCode() === $status, 'Unexpected denial status'); }
    catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) { $check($status === 404, 'Unexpected missing model'); }
};
$controller = new AppraisalController;
$policy = app(AppraisalPolicy::class);
$metrics = app(AppraisalMetrics::class);
$hr = User::findOrFail(2); Auth::guard('web')->setUser($hr);
$check($policy->currentPeriod() === '2026 Q4', 'Current quarter is hard-coded');
// The following fixtures exercise monthly reviews, so select that cycle explicitly.
AppSetting::updateOrCreate(['key' => 'perf_appraisal_cycle'], ['value' => 'Monthly']);
$check($metrics->calculate(User::findOrFail(3), '2026-10')['project_score'] === 0, 'Missing work invented a score');
DB::table('attendances')->insert([
    ['company_id' => 1, 'user_id' => 3, 'date' => '2026-10-01', 'status' => 'present'],
    ['company_id' => 1, 'user_id' => 3, 'date' => '2026-10-01', 'status' => 'present'],
    ['company_id' => 1, 'user_id' => 3, 'date' => '2026-10-02', 'status' => 'half_day'],
    ['company_id' => 1, 'user_id' => 3, 'date' => '2026-10-03', 'status' => 'present'],
    ['company_id' => 2, 'user_id' => 3, 'date' => '2026-10-05', 'status' => 'present'],
]);
foreach ([[1, 1, '3', 1, '2026-10-01'], [2, 1, '1,3', 0, '2026-10-02'], [3, 1, '13', 1, '2026-10-02'], [4, 2, '3', 1, '2026-10-02'], [5, 1, '3', 1, '2026-09-01'], [6, 1, '4', 1, '2026-10-06']] as [$id, $company, $assigned, $complete, $date]) {
    DB::table('tasks')->insert(['id' => $id, 'company_id' => $company, 'project_id' => 1, 'assigned_to' => $assigned, 'is_completed' => $complete, 'status' => $complete ? 'completed' : 'open', 'due_date' => $date]);
}
DB::table('assigned_task_user')->insert(['task_id' => 6, 'user_id' => 3]);
$computed = $metrics->calculate(User::findOrFail(3), '2026-10');
$check($computed['completed_tasks'] === 2 && abs($computed['project_score'] - 66.67) < .01, 'Task assignment/period/company filtering failed');
$check($computed['present_days'] === 2 && $computed['total_working_days'] === 10 && $computed['attendance_score'] === 15.0, 'Attendance duplicated sessions or included weekends/future/foreign rows');
Schema::create('leaves', function ($t) {
    $t->id(); $t->integer('company_id'); $t->integer('user_id'); $t->string('status'); $t->date('date')->nullable();
    $t->date('start_date')->nullable(); $t->date('end_date')->nullable(); $t->boolean('half_day_flag')->default(false);
});
DB::table('leaves')->insert(['company_id' => 1, 'user_id' => 3, 'status' => 'approved', 'date' => '2026-10-05']);
$withLeave = $metrics->calculate(User::findOrFail(3), '2026-10');
$check($withLeave['total_working_days'] === 9 && $withLeave['attendance_score'] === 16.67, 'Approved leave penalized attendance');
DB::table('leaves')->delete();
$own = Appraisal::create(['company_id' => 1, 'employee_id' => 2, 'appraisal_period' => '2026-10', 'overall_score' => 75, 'status' => 'approved']);
$before = Appraisal::count();
$data = $controller->index(Request::create('/appraisals', 'GET', ['period' => '2026-10']))->getData();
$check(Appraisal::count() === $before, 'Opening appraisals wrote data');
$check(!in_array($own->id, $data['editableAppraisalIds'], true) && !in_array(2, $data['editableEmployeeIds'], true), 'HR self-review exposed in UI data');
$evaluation = ['employee_id' => 2, 'appraisal_period' => '2026-10', 'project_score' => 90, 'attendance_score' => 95, 'teamwork_score' => 9, 'communication_score' => 9, 'punctuality_score' => 9, 'behaviour_remarks' => 'Documented teamwork and conduct.', 'project_remarks' => 'Reviewed completed milestones.', 'attendance_remarks' => 'Verified attendance evidence.'];
$request = fn ($data) => Request::create('/appraisals', 'POST', $data);
$denied(fn () => $controller->store($request($evaluation)));
$denied(fn () => $controller->update($request($evaluation + ['company_id' => 2]), $own->id));
$denied(fn () => $controller->destroy($own->id));
$denied(fn () => $controller->store($request(array_replace($evaluation, ['employee_id' => 5]))), 404);
$controller->autoCalculate($request(['period' => '2026-10']));
$check($own->fresh()->overall_score === 75.0, 'Bulk calculation changed HR self-appraisal');
$check(Appraisal::where('employee_id', 5)->count() === 0, 'Bulk calculation crossed company');
$draft = Appraisal::where('employee_id', 3)->firstOrFail();
$check($draft->status === 'draft' && $draft->behaviour_score === 0.0 && $draft->recommended_increment_percent === null, 'Auto calculation invented behaviour or a salary increase');
foreach ([3, 4] as $employeeId) {
    $controller->store($request(array_replace($evaluation, ['employee_id' => $employeeId])));
    $record = Appraisal::where('employee_id', $employeeId)->firstOrFail();
    $check($record->status === 'submitted' && $record->evaluated_by === 2, 'HR could not review employee/manager');
}
$review = Appraisal::where('employee_id', 3)->firstOrFail();
$score = $review->overall_score;
$controller->autoCalculate($request(['period' => '2026-10']));
$check($review->fresh()->overall_score === $score, 'Auto calculation overwrote submitted review');
Auth::guard('web')->setUser(User::findOrFail(4));
$denied(fn () => $controller->store($request(array_replace($evaluation, ['employee_id' => 3]))));
Auth::guard('web')->setUser(User::findOrFail(1));
$controller->update($request(array_replace($evaluation, ['employee_id' => 5])), $own->id);
$check($own->fresh()->evaluated_by === 1 && $own->fresh()->status === 'approved' && $own->fresh()->employee_id === 2, 'Admin did not review HR or route identity was forged');
AppSetting::updateOrCreate(['key' => 'perf_work_weight'], ['value' => '20']);
AppSetting::updateOrCreate(['key' => 'perf_attendance_weight'], ['value' => '50']);
AppSetting::updateOrCreate(['key' => 'perf_behaviour_weight'], ['value' => '30']);
$controller->update($request($evaluation), $review->id);
$check($review->fresh()->overall_score === 92.5 && $review->fresh()->recommended_increment_percent === 10.0, 'Configured weights/increments not used');
$snapshotScore = $review->fresh()->computeOverallScore();
AppSetting::where('key', 'perf_work_weight')->update(['value' => '40']);
$check($review->fresh()->computeOverallScore() === $snapshotScore, 'Saved evaluation changed with later policy edits');
$settingsController = new PerformanceSettingsController;
Auth::guard('web')->setUser($hr);
$denied(fn () => $settingsController->update($request([])));
Auth::guard('web')->setUser(User::findOrFail(1));
try {
    $settingsController->update($request(['appraisal_cycle' => 'Quarterly', 'work_weight' => 40, 'attendance_weight' => 40, 'behaviour_weight' => 30, 'increment_a_plus' => 10, 'increment_a' => 7, 'increment_b' => 3, 'increment_c' => 0, 'increment_d' => 0]));
    throw new RuntimeException('Invalid policy sum accepted');
} catch (Illuminate\Validation\ValidationException $e) { $check(isset($e->errors()['work_weight']), 'Invalid policy did not fail weight validation'); }
$validPolicy = ['appraisal_cycle' => 'Monthly', 'work_weight' => 50, 'attendance_weight' => 20, 'behaviour_weight' => 30,
    'increment_a_plus' => 12.25, 'increment_a' => 8.5, 'increment_b' => 5, 'increment_c' => 1, 'increment_d' => 0, 'increment_enabled' => '1', 'admin_approval_required' => '1'];
$settingsController->update($request($validPolicy));
$check($policy->settings()['work_weight'] == 50 && $policy->settings()['increment_a_plus'] == 12.25 && $policy->currentPeriod() === '2026-10', 'Admin policy save did not affect the appraisal process');
$formData = $settingsController->index()->getData();
$policyHtml = Blade::render("@include('admin.settings.partials.appraisal-policy-form')", $formData + ['isSettingsReadOnly' => false]);
$check(str_contains($policyHtml, 'Salary increase recommendations') && str_contains($policyHtml, 'value="12.25"'), 'Saved policy values did not render');
Auth::guard('web')->setUser($hr);
$controller->update($request($evaluation), $review->id);
$check($review->fresh()->recommended_increment_percent === 12.25 && $review->fresh()->status === 'submitted', 'New evaluation did not use saved policy or invalidate previous approval');
$deniedRequest = array_replace($evaluation, ['employee_id' => 3, 'behaviour_remarks' => '']);
try {
    $controller->store($request($deniedRequest));
    throw new RuntimeException('Missing cultural behaviour evidence accepted');
} catch (Illuminate\Validation\ValidationException $e) { $check(isset($e->errors()['behaviour_remarks']), 'Wrong behaviour validation'); }
$permission = RolePermission::create(['role' => 'hr', 'module_id' => 1, 'can_view' => true, 'can_create' => true, 'can_edit' => false]);
$denied(fn () => $controller->update($request($evaluation), $review->id));
$permission->delete();
$data = $controller->index(Request::create('/appraisals', 'GET', ['period' => '2026-10']))->getData();
$source = preg_replace('/^@extends\([^\n]+\)\s*$/m', '', file_get_contents(resource_path('views/admin/appraisal/index.blade.php')));
app('view')->share('errors', new Illuminate\Support\ViewErrorBag);
$html = Blade::render($source."\n@yield('content')", $data);
$check(!str_contains($html, 'id="editAppraisalModal-'.$own->id.'"'), 'HR self-edit modal exists');
$check(!str_contains($html, 'action="'.route('appraisal.destroy', $own->id).'"'), 'HR self-delete form exists');
$check(str_contains($html, 'id="editAppraisalModal-'.$review->id.'"') && str_contains($html, 'Recommended increase:'), 'Other employee editing/increment UI missing');
Carbon::setTestNow();
echo "PASS: HR self-review blocked on create/update/delete/bulk/UI; admin HR review; employee/manager reviews; company isolation; real period metrics; no fabricated behaviour; policy weights/increments/snapshots; approval workflow; read-only GET and policy validation.\n";
