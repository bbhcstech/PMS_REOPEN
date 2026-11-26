<?php

require __DIR__.'/appraisal_review_workflow.php';

use App\Models\{Appraisal, AppSetting, User};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Blade};

Carbon::setTestNow(Carbon::parse('2026-10-14 12:00:00', 'Asia/Kolkata'));
Auth::guard('web')->setUser(User::findOrFail(1));
$before = Appraisal::orderBy('id')->get()->toArray();
$source = file_get_contents(resource_path('views/admin/appraisal/index.blade.php'));
preg_match_all('/<select\b[^>]*\bname="(?:period|appraisal_period)"[^>]*>.*?<\/select>/s', $source, $selects);
$check(count($selects[0]) === 2, 'Filter and evaluation period dropdowns must both exist');
foreach ([
    ['Bi-Annually', '/^\d{4} H[12]$/', 6, '2026 H2', '2026 Q3', 2],
    ['Annually', '/^Annual \d{4}$/', 3, 'Annual 2026', '2026 H1', 1],
    ['Quarterly', '/^\d{4} Q[1-4]$/', 12, '2026 Q4', '2026-10', 4],
    ['Monthly', '/^\d{4}-(0[1-9]|1[0-2])$/', 36, '2026-10', 'Annual 2026', 12],
] as [$cycle, $pattern, $count, $current, $stale, $perYear]) {
    AppSetting::updateOrCreate(['key' => 'perf_appraisal_cycle'], ['value' => $cycle]);
    $periods = $policy->periods();
    $check(count($periods) === $count && $policy->currentPeriod() === $current, $cycle.' period count/current value incorrect');
    foreach ($periods as $period) $check((bool) preg_match($pattern, $period), $cycle.' includes another cycle type: '.$period);
    $check(count(array_filter($periods, fn ($period) => str_contains($period, '2026'))) === $perYear, $cycle.' missing an option in the current year');
    $data = $controller->index(Request::create('/appraisals', 'GET', ['period' => $stale]))->getData();
    $check($data['selectedPeriod'] === $current && $data['periods'] === $periods && !in_array($stale, $data['periods'], true), 'Stale '.$cycle.' URL reintroduced another cycle');
    foreach ($selects[0] as $select) {
        $html = Blade::render($select, $data);
        preg_match_all('/<option value="([^"]+)"/', $html, $options);
        $check($options[1] === $periods, $cycle.' dropdown does not match the saved setting');
    }
    $historical = $periods[count($periods) - 1];
    $data = $controller->index(Request::create('/appraisals', 'GET', ['period' => $historical]))->getData();
    $check($data['selectedPeriod'] === $historical, $cycle.' historical year selection lost');
}
$check($before === Appraisal::orderBy('id')->get()->toArray(), 'Changing period filters changed saved appraisal data');
// Old cycle formats remain parseable for saved payroll/appraisal history.
$check($policy->periodDates('2026 H1')[1]->toDateString() === '2026-06-30', 'Historical period parsing changed');
Carbon::setTestNow();
echo "PASS: cycle-specific H1/H2, Annual, Q1–Q4 and monthly options in both dropdowns; stale selection fallback, historical years, live settings changes and unchanged saved appraisals.\n";
