<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{User, CompanyStaffRole};
use Illuminate\Support\Facades\{Auth, Blade};

// Render the original profile breadcrumb, including its role-dependent route calls.
$source = file_get_contents(resource_path('views/profile/edit.blade.php'));
$start = strpos($source, '@if(Auth::check())');
$end = strpos($source, '</li>', $start);
$breadcrumb = substr($source, $start, $end - $start);
foreach (['manager', 'admin'] as $accessRole) {
    $user = new User(['id' => 7, 'company_id' => 2, 'role' => $accessRole, 'company_staff_role_id' => 3]);
    $user->setRelation('companyStaffRole', new CompanyStaffRole(['company_id' => 2, 'name' => 'Associate Manager', 'access_role' => $accessRole]));
    Auth::guard('web')->setUser($user);
    $html = Blade::render($breadcrumb, ['logoVersion' => 1]);
    if (!str_contains($html, 'href="'.route('dashboard').'"')) throw new RuntimeException('Profile breadcrumb did not link to the role-aware dashboard');
    if (!str_contains($html, 'Micro Poem '.ucfirst($accessRole))) throw new RuntimeException('Existing profile label changed');
}
echo "PASS: Associate Manager and Admin profile breadcrumbs render without missing-route errors; dashboard links and labels preserved.\n";
