<?php

// Render the actual Associate Manager page for the offline browser audit.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array', 'database.default' => 'tenant']);
foreach (['central', 'tenant', 'session_db'] as $connection) {
    config(['database.connections.'.$connection => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    Illuminate\Support\Facades\DB::purge($connection);
}
$user = new App\Models\User(['role' => 'manager', 'company_id' => 1]);
$user->setRelation('companyStaffRole', new App\Models\CompanyStaffRole(['company_id' => 1, 'name' => 'Associate Manager', 'access_role' => 'manager']));
Illuminate\Support\Facades\Auth::guard('web')->setUser($user);
app('view')->share('errors', new Illuminate\Support\ViewErrorBag);
session(['success' => 'Company profile saved.', 'error' => 'Example validation message.']);
$company = (object) [
    'id' => 1, 'company_name' => 'Example Company', 'company_email' => 'company@example.test',
    'company_phone' => '+919876543210', 'company_location' => 'Example company address',
    'company_website' => 'https://example.test', 'company_logo' => 'admin/assets/img/favicon/favicon.ico',
];
$source = file_get_contents(resource_path('views/admin/settings/company.blade.php'));
$source = preg_replace('/^@extends\([^\n]+\)\s*$/m', '', $source);
$html = Illuminate\Support\Facades\Blade::render($source."\n@yield('content')", [
    'company' => $company, 'isSettingsReadOnly' => true, 'phoneDigits' => '9876543210',
    'selectedCountry' => 'India', 'selectedCountryCode' => '+91',
    'countryMap' => ['India' => ['dial_code' => '+91', 'iso' => 'in', 'min_digits' => 10, 'max_digits' => 10]],
]);
if (! str_contains($html, 'View-Only Mode') || str_contains($html, 'class="btn-save-address"')) {
    throw new RuntimeException('Associate Manager read-only behavior changed.');
}
echo json_encode(['html' => $html], JSON_THROW_ON_ERROR);
