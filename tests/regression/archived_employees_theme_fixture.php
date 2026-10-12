<?php

// Prints the Archived Employees page body (real Blade template, sample rows) as JSON for
// archived_employees_theme_contrast.cjs. No database is needed.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$source = file_get_contents(resource_path('views/admin/employees/archive.blade.php'));
$start = strpos($source, "@section('content')") + strlen("@section('content')");
$end = strpos($source, "@push('js')");
$template = substr($source, $start, $end - $start);

$detail = fn (string $id, string $status) => (object) [
    'employee_id' => $id, 'status' => $status, 'mobile' => '9876543210',
    'designation' => (object) ['name' => 'Software Engineer'], 'department' => (object) ['dpt_name' => 'Engineering'], 'parent_dpt' => null,
];
$rows = collect([
    (object) ['id' => 1, 'name' => 'Ananya Bose', 'email' => 'ananya@example.com', 'profile_image' => null, 'archived_at' => now(), 'employeeDetail' => $detail('GOOGLE-EMP-0001', 'Inactive')],
    (object) ['id' => 2, 'name' => 'Arjun Mehta', 'email' => 'arjun@example.com', 'profile_image' => null, 'archived_at' => now()->subMonth(), 'employeeDetail' => $detail('GOOGLE-EMP-0002', 'Active')],
]);
$employees = new Illuminate\Pagination\LengthAwarePaginator($rows, 2, 15, 1);

$html = Illuminate\Support\Facades\Blade::render($template, [
    'employees' => $employees,
    'errors' => new Illuminate\Support\ViewErrorBag,
]);
$html = preg_replace('/<style[^>]*>[\s\S]*?<\/style>/', '', $html);
preg_match_all('/<style[^>]*>([\s\S]*?)<\/style>/', $template, $styles);

$manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
echo json_encode([
    'html' => $html,
    'styles' => implode("\n", $styles[1]),
    'appCss' => public_path('build/' . $manifest['resources/css/app.css']['file']),
]);
