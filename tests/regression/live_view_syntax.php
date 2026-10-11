<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$views = [
    'admin/layout/app', 'admin/layout/footer', 'layouts/app', 'layouts/developer',
    'layouts/superadmin', 'frontend/layouts-frontend/app', 'partials/live-records-identity',
    'admin/complaints/show', 'admin/complaints/messages', 'superadmin/complaints/show',
    'superadmin/complaints/index', 'superadmin/complaints/partials/drawer_content',
    'superadmin/complaints/partials/messages', 'admin/community/index', 'superadmin/dashboard',
    'admin/complaints/create', 'partials/subscription-downgrade-request',
    'superadmin/companies/suspend', 'superadmin/companies/suspended', 'superadmin/companies/index',
    'superadmin/companies/show', 'superadmin/subscriptions/index', 'subscription/suspended',
    'partials/phone-fields', 'partials/phone-country-select', 'admin/leads/contacts/create',
    'admin/leads/contacts/edit', 'admin/letterhead/index', 'admin/leaves/create',
    'admin/settings/business-address/create', 'admin/settings/business-address/edit',
    'admin/companies/form', 'admin/collaborating-companies/form', 'admin/employees/edit',
    'admin/company-role-dashboard',
];
// Settings page styles are loaded after the shared workspace theme.
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views/admin/settings'))) as $file) {
    if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) continue;
    if (! str_contains(file_get_contents($file->getPathname()), 'pms-settings-page')) continue;
    $relative = substr($file->getPathname(), strlen(resource_path('views')) + 1, -strlen('.blade.php'));
    $views[] = str_replace('\\', '/', $relative);
}
$views = array_unique($views);
foreach ($views as $view) {
    $compiled = app('blade.compiler')->compileString(file_get_contents(resource_path("views/$view.blade.php")));
    $path = tempnam(sys_get_temp_dir(), 'pms-blade-');
    try {
        file_put_contents($path, $compiled);
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' 2>&1', $output, $code);
        if ($code !== 0) throw new RuntimeException($view . ': ' . implode("\n", $output));
    } finally { unlink($path); }
    $output = [];
}
echo 'PASS: '.count($views)." layouts, conversation and settings views compile with valid PHP.\n";
