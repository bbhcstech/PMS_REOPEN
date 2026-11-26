<?php
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$source = file_get_contents(resource_path('views/admin/organization-directory/show.blade.php'));
$start = strpos($source, '<!-- Personal information:');
$end = strpos($source, '<!-- About & Bio -->', $start);
$section = substr($source, $start, $end - $start);
function personalCheck($condition, $message) { if (! $condition) throw new RuntimeException($message); }
$render = function ($detail, $employee, $dob = null, $joiningDate = null) use ($section) {
    return Illuminate\Support\Facades\Blade::render($section, compact('detail', 'employee', 'dob', 'joiningDate'), true);
};
$empty = (object) ['gender' => '', 'country' => null, 'language' => '   '];
$html = $render($empty, $empty);
personalCheck(! str_contains($html, 'org-main-card'), 'Empty personal section must be hidden.');
$html = $render($empty, (object) ['gender' => 'male', 'country' => 'India', 'language' => ''], '2005-05-02', '2026-10-15');
foreach (['Birthday', '02 May (2005)', 'Joining Date', '15 Oct 2026', 'Gender', 'Male', 'Country / Location', 'India'] as $expected) {
    personalCheck(str_contains($html, $expected), 'Missing saved field: '.$expected);
}
personalCheck(! str_contains($html, 'Primary Language') && ! str_contains($html, 'Service Tenure'), 'Unfilled or derived field displayed.');
$html = $render(null, (object) ['gender' => null, 'country' => null, 'language' => '<English>']);
personalCheck(str_contains($html, '&lt;English&gt;') && str_contains($html, 'colspan="3"'), 'Single field must be escaped and fill its row.');
echo "Employee personal information: saved values, empty fields, fallback, escaping and layout passed.\n";
