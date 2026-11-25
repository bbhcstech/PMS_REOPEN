<?php

// Inherits isolated SQLite fixtures; no company production data is touched.
require __DIR__.'/appraisal_review_workflow.php';

use App\Models\{Appraisal, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Blade, DB};
use Carbon\Carbon;

Carbon::setTestNow(Carbon::parse('2026-10-14 12:00:00', 'Asia/Kolkata'));
$admin = User::findOrFail(1);
Auth::guard('web')->setUser($admin);
$rating = ['employee_id' => 3, 'appraisal_period' => '2026-10', 'teamwork_score' => 8,
    'communication_score' => 9, 'punctuality_score' => 7, 'behaviour_remarks' => 'Helps teammates, communicates clearly and follows professional standards.',
    'behaviour_score' => 100, 'overall_score' => 100, 'status' => 'approved'];
$before = Appraisal::where('employee_id', 3)->where('appraisal_period', '2026-10')->firstOrFail();
$workScore = $before->project_score;
$attendanceScore = $before->attendance_score;
$controller->saveBehaviour(Request::create('/ratings', 'POST', $rating));
$saved = $before->fresh();
$check($saved->behaviour_score === 80.0 && $saved->teamwork_score === 8.0 && $saved->communication_score === 9.0 && $saved->punctuality_score === 7.0, 'Manual ratings not used or client forged behaviour total');
$check($saved->project_score === $workScore && $saved->attendance_score === $attendanceScore, 'Behaviour-only save changed work/attendance scores');
$check($saved->status === 'draft' && $saved->overall_grade === 'Pending' && $saved->recommended_increment_percent === null && $saved->evaluated_by === 1, 'Changing ratings did not invalidate old approval');
$expected = $policy->score($workScore, $attendanceScore, 80.0, $policy->settings());
$check(abs($saved->overall_score - $expected) < .01, 'Manual score did not feed policy weighting');
$controller->autoCalculate(Request::create('/calculate', 'POST', ['period' => '2026-10']));
$saved->refresh();
$check($saved->behaviour_score === 80.0 && $saved->behaviour_remarks === $rating['behaviour_remarks'] && $saved->evaluated_by === 1, 'Recalculation erased manual ratings or reviewer');
foreach ([2, 4, 6] as $employeeId) {
    $controller->saveBehaviour(Request::create('/ratings', 'POST', array_replace($rating, ['employee_id' => $employeeId, 'appraisal_period' => '2026 Q3'])));
    $check(Appraisal::where('employee_id', $employeeId)->where('appraisal_period', '2026 Q3')->firstOrFail()->behaviour_score === 80.0, 'Admin could not rate HR/Manager with no existing appraisal');
}
$count = Appraisal::count();
foreach ([['teamwork_score' => 0], ['communication_score' => 11], ['punctuality_score' => 'invalid'], ['behaviour_remarks' => ''], ['appraisal_period' => 'bad']] as $invalid) {
    try { $controller->saveBehaviour(Request::create('/ratings', 'POST', array_replace($rating, $invalid))); throw new RuntimeException('Invalid ratings accepted'); }
    catch (\Illuminate\Validation\ValidationException $e) { $check(count($e->errors()) > 0, 'Validation error missing'); }
}
$denied(fn () => $controller->saveBehaviour(Request::create('/ratings', 'POST', array_replace($rating, ['employee_id' => 5]))), 404);
foreach ([3, 4] as $actorId) {
    Auth::guard('web')->setUser(User::findOrFail($actorId));
    $denied(fn () => $controller->saveBehaviour(Request::create('/ratings', 'POST', $rating)));
}
Auth::guard('web')->setUser($admin);
DB::table('users')->insert(['id' => 7, 'company_id' => 1, 'name' => 'Not rated employee', 'email' => 'notrated@example.test', 'role' => 'employee', 'is_active' => true]);
$data = $controller->index(Request::create('/appraisals', 'GET', ['period' => '2026-10']))->getData();
$html = Blade::render("@include('admin.appraisal.behaviour-table') @stack('appraisal-behaviour-modals')", $data);
$check(str_contains($html, 'behaviourRating-7') && str_contains($html, 'Not rated') && str_contains($html, '80.0 / 100'), 'Admin table missing unrated employees, direct inputs or saved score');
$check(str_contains($html, 'action="'.route('appraisal.behaviour.save').'"') && str_contains($html, 'name="punctuality_score"'), 'Manual rating form or conduct input missing');
Auth::guard('web')->setUser(User::findOrFail(2));
$hrData = $controller->index(Request::create('/appraisals', 'GET', ['period' => '2026-10']))->getData();
$hrHtml = Blade::render("@include('admin.appraisal.behaviour-table') @stack('appraisal-behaviour-modals')", $hrData);
$check(str_contains($hrHtml, 'data-behaviour-rating-form') && !str_contains($hrHtml, 'id="behaviourRating-2"') && !str_contains($hrHtml, 'id="behaviourRating-6"'), 'HR rating permissions do not match eligible staff');
$controller->saveBehaviour(Request::create('/ratings', 'POST', $rating));
foreach ([2, 6] as $hrId) $denied(fn () => $controller->saveBehaviour(Request::create('/ratings', 'POST', array_replace($rating, ['employee_id' => $hrId]))));
$check(Appraisal::count() === $count, 'Validation or opening the table wrote appraisal rows');
Carbon::setTestNow();
echo "PASS: admin staff ratings; HR ratings for employees/managers only; derived score, preserved metrics, approval invalidation, recalculation, validation and tenant boundaries.\n";
