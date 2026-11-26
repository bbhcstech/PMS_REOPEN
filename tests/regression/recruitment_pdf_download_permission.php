<?php

// Run with: php tests/regression/recruitment_pdf_download_permission.php
// Employees who can view Recruitment can download a job requirement PDF (to share for referrals).
// That download only needs "view"; every other download/export route still needs "export".
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Middleware\EnsureModuleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};
$permission = fn (string $routeName, string $method = 'GET') => EnsureModuleAccess::permissionForRequest(Request::create('/x', $method), $routeName);

$check(Route::has('recruitment.download'), 'recruitment PDF download route exists');
$check($permission('recruitment.download') === 'view', 'downloading a job requirement PDF needs only view permission');
$check($permission('recruitment.index') === 'view' && $permission('recruitment.show') === 'view', 'listing and opening requirements need view');
$check($permission('recruitment.store', 'POST') === 'create', 'creating a requirement still needs create');
$check($permission('recruitment.destroy', 'DELETE') === 'delete', 'deleting a requirement still needs delete');
$check($permission('payroll.export') === 'export', 'other exports still need export permission');
$check($permission('letterhead.download') === 'export', 'other downloads still need export permission');
$check($permission('recruitment.download', 'POST') !== 'view', 'only a read-only GET of the PDF is view-level');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: job requirement PDF download is view-level; other downloads/exports unchanged.\n";
exit($failures ? 1 : 0);
