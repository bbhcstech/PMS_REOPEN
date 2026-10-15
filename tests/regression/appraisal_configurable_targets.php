<?php
require __DIR__.'/appraisal_review_workflow.php';

use App\Models\{Appraisal, AppSetting, User};
use Illuminate\Support\Facades\{Auth, DB, Schema, Blade};
use Illuminate\Http\Request;
use Carbon\Carbon;

Carbon::setTestNow(Carbon::parse('2026-10-14 12:00:00', 'Asia/Kolkata'));
Auth::guard('web')->setUser(User::findOrFail(1));
$approved = Appraisal::where('employee_id', 2)->where('appraisal_period', '2026-10')->firstOrFail();
$approvedBefore = $approved->toArray();
$targets = $validPolicy + ['attendance_full_marks_at' => 100, 'work_full_marks_at' => 50, 'behaviour_full_marks_at' => 8];
$settingsController->update($request($targets));
$check($policy->settings()['work_full_marks_at'] == 50 && $policy->settings()['behaviour_full_marks_at'] == 8, 'Full-marks targets were not saved');
foreach (['attendance_full_marks_at' => 101, 'work_full_marks_at' => 0, 'behaviour_full_marks_at' => 11] as $key => $value) {
    try { $settingsController->update($request(array_replace($targets, [$key => $value]))); throw new RuntimeException('Invalid target accepted'); }
    catch (Illuminate\Validation\ValidationException $e) { $check(isset($e->errors()[$key]), 'Wrong target validation'); }
}
DB::table('users')->insert(['id' => 7, 'company_id' => 1, 'name' => 'Developer Staff', 'email' => 'dev@example.test', 'role' => 'developer']);
foreach (['01', '02', '05', '06', '07', '08', '09', '12', '13', '14'] as $day) DB::table('attendances')->insert([
    'company_id' => 1, 'user_id' => 7, 'date' => '2026-10-'.$day, 'status' => 'present',
]);
foreach ([true, false] as $completed) DB::table('tasks')->insert([
    'company_id' => 1, 'assigned_to' => '7', 'due_date' => '2026-10-05', 'is_completed' => $completed,
    'status' => $completed ? 'completed' : 'open',
]);
$scores = $metrics->calculate(User::findOrFail(7), '2026-10');
$check($scores['attendance_percentage'] === 100.0 && $scores['attendance_score'] === 100.0, 'Full scheduled attendance did not earn full marks');
$check($scores['project_score'] === 100.0, 'Work completion target did not apply');
$check($policy->criterionScore(40, 'work') === 80.0 && $policy->behaviourScore([4, 4, 4]) === 50.0 && $policy->behaviourScore([10, 10, 10]) === 100.0, 'Proportional scoring/cap failed');
$selected = ['period' => '2026-10', 'selected_staff_calculation' => '1', 'employee_ids' => [7]];
$controller->autoCalculate($request($selected));
$draft = Appraisal::where('employee_id', 7)->firstOrFail();
$check($draft->project_score === 100.0 && $draft->status === 'draft', 'Selected developer staff was not added/calculated');
$controller->saveBehaviour($request(['employee_id' => 7, 'appraisal_period' => '2026-10', 'teamwork_score' => 8, 'communication_score' => 8,
    'punctuality_score' => 8, 'behaviour_remarks' => 'Strong collaboration and conduct.']));
$check($draft->fresh()->behaviour_score === 100.0 && $draft->fresh()->overall_score === 100.0, 'Target ratings did not yield weighted full marks');
$controller->autoCalculate($request($selected));
$check($draft->fresh()->behaviour_score === 100.0, 'Recalculation ignored behaviour target');
$check($approved->fresh()->toArray() === $approvedBefore, 'Configuration/calculation changed approved review');
try { $controller->autoCalculate($request(['selected_staff_calculation' => '1', 'period' => '2026-10'])); throw new RuntimeException('Empty selection calculated everyone'); }
catch (Illuminate\Validation\ValidationException $e) { $check(isset($e->errors()['employee_ids']), 'Empty selection validation missing'); }
$denied(fn () => $controller->autoCalculate($request(array_replace($selected, ['employee_ids' => [7, 5]]))));
Auth::guard('web')->setUser(User::findOrFail(2));
foreach ([2, 6] as $hrId) {
    $check(!$policy->canEvaluate(User::findOrFail(2), User::findOrFail($hrId)), 'HR may evaluate HR');
    $denied(fn () => $controller->autoCalculate($request(array_replace($selected, ['employee_ids' => [$hrId]]))));
}
$hrData = $controller->index(Request::create('/appraisals', 'GET', ['period' => '2026-10']))->getData();
$check(in_array(7, $hrData['editableEmployeeIds'], true) && !in_array(6, $hrData['editableEmployeeIds'], true), 'HR staff picker scope incorrect');
Auth::guard('web')->setUser(User::findOrFail(7));
$data = $controller->index(Request::create('/appraisals', 'GET', ['period' => '2026-10']))->getData();
$check($data['appraisals']->pluck('employee_id')->all() === [7], 'Staff saw other appraisals');
$html = Blade::render("@include('admin.appraisal.personal-breakdown')", ['record' => $draft->fresh()]);
foreach (['Your appraisal breakdown', 'Work delivery', 'Attendance', 'Cultural behaviour', 'Strong collaboration and conduct.', '100.00 / 100', '50%'] as $text) $check(str_contains($html, $text), 'Missing employee result: '.$text);
Schema::table('users', fn ($t) => $t->timestamp('archived_at')->nullable());
DB::table('users')->where('id', 7)->update(['archived_at' => now()]);
$check(!$policy->canEvaluate(User::findOrFail(1), User::findOrFail(7)), 'Archived staff could be evaluated');
Carbon::setTestNow();
echo "PASS: configurable targets, full attendance, proportional/capped scoring, selected staff calculation, HR boundaries, archived staff, employee breakdown and unchanged approvals.\n";
