<?php

use Illuminate\Support\Facades\Route;

// Registration order preserves existing aliases and fallback matching.
require __DIR__.'/web/entry/subscription-and-session.php';

require __DIR__.'/web/entry/company-collaboration.php';

require __DIR__.'/web/entry/workspace-utilities.php';

require __DIR__.'/web/entry/attendance.php';

require __DIR__.'/web/entry/workspace-legacy.php';

require __DIR__.'/web/entry/sign-in-and-dashboard.php';

require __DIR__.'/web/entry/platform-legacy.php';

require __DIR__.'/web/entry/public-site.php';

require __DIR__.'/web/entry/sign-out.php';

Route::middleware(['auth', 'module.access'])->group(function () {
    require __DIR__.'/web/workspace/settings-access.php';
    require __DIR__.'/web/workspace/letterhead.php';
    require __DIR__.'/web/workspace/organization-directory.php';
    require __DIR__.'/web/workspace/notifications.php';
    require __DIR__.'/web/workspace/dashboard-and-profile.php';
    require __DIR__.'/web/workspace/employees-and-organization.php';
    require __DIR__.'/web/workspace/attendance.php';
    require __DIR__.'/web/workspace/leave.php';
    require __DIR__.'/web/workspace/holidays.php';
    require __DIR__.'/web/workspace/recognition.php';
    require __DIR__.'/web/workspace/clients.php';
    require __DIR__.'/web/workspace/projects-and-reports.php';
    require __DIR__.'/web/workspace/tasks.php';
    require __DIR__.'/web/workspace/time-logs-and-expenses.php';
    require __DIR__.'/web/workspace/tickets.php';
    require __DIR__.'/web/workspace/commerce-and-dashboards.php';
});

require __DIR__.'/web/entry/leads.php';

require __DIR__.'/web/entry/payroll.php';

require __DIR__.'/web/entry/deals.php';

require __DIR__.'/web/entry/contracts.php';

require __DIR__.'/web/entry/company-settings.php';

Route::middleware(['auth'])->group(function () {
    require __DIR__.'/web/account/settings.php';
    require __DIR__.'/web/account/documents-and-payslips.php';
    require __DIR__.'/web/account/security-and-localization.php';
    require __DIR__.'/web/account/recruitment-and-appraisals.php';
    require __DIR__.'/web/account/platform-support.php';
});

require __DIR__.'/web/entry/developer.php';

require __DIR__.'/auth.php';

require __DIR__.'/web/entry/platform-fallback.php';

require __DIR__.'/web/entry/employee-invitations.php';
