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
];
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
echo "PASS: live-update layouts and conversation views compile with valid PHP.\n";
