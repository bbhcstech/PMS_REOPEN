<?php

use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\CompanyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Routes (Central DB / Platform Management)
|--------------------------------------------------------------------------
|
| Guard: super_admin (App\Models\Central\SuperAdmin) & web
| Connection: central (pms_central)
|
*/

Route::prefix('super-admin')->name('super-admin.')->group(function () {

    // Guest routes
    Route::middleware('guest:super_admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Authenticated super admin routes (supports super_admin and web auth guards)
    Route::middleware(['auth:super_admin,web'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Company management & impersonation
        Route::get('/', [CompanyController::class, 'index']);
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/metrics', [CompanyController::class, 'metrics'])->name('companies.metrics');
        Route::get('/plans', [CompanyController::class, 'plans'])->name('plans.index');
        Route::post('/plans', [CompanyController::class, 'storePlan'])->name('plans.store');
        Route::put('/plans/{plan}', [CompanyController::class, 'updatePlan'])->name('plans.update');
        Route::delete('/plans/{plan}', [CompanyController::class, 'destroyPlan'])->name('plans.destroy');
        Route::post('/plans/toggle-module', [CompanyController::class, 'togglePlanModule'])->name('plans.toggle-module');
        Route::get('/subscriptions', [CompanyController::class, 'subscriptions'])->name('subscriptions.index');
        Route::post('/subscriptions/assign', [CompanyController::class, 'assignPlan'])->name('subscriptions.assign');
        Route::post('/subscriptions/{id}/extend', [CompanyController::class, 'extendSubscription'])->name('subscriptions.extend');
        Route::post('/subscriptions/{id}/reduce', [CompanyController::class, 'reduceSubscription'])->name('subscriptions.reduce');
        Route::post('/subscriptions/toggle-override', [CompanyController::class, 'toggleCompanyOverride'])->name('subscriptions.toggle-override');
        Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::post('/companies/{company}/enter', [CompanyController::class, 'enter'])->name('companies.enter');
        Route::post('/companies/{company}/suspend', [CompanyController::class, 'suspend'])->name('companies.suspend');
        Route::post('/companies/{company}/deactivate', [CompanyController::class, 'deactivate'])->name('companies.deactivate');
        Route::post('/companies/{company}/activate', [CompanyController::class, 'activate'])->name('companies.activate');
        Route::post('/leave-impersonation', [CompanyController::class, 'leaveImpersonation'])->name('leave-impersonation');

        // Migrations management
        Route::get('/migrations', [CompanyController::class, 'migrations'])->name('migrations.index');
        Route::post('/migrations/run', [CompanyController::class, 'runMigration'])->name('migrations.run');
        Route::post('/migrations/bulk-run', [CompanyController::class, 'bulkRunMigration'])->name('migrations.bulk-run');
        Route::get('/migrations/logs/{company}', [CompanyController::class, 'migrationLogs'])->name('migrations.logs');

        // Backups management
        Route::get('/backups', [CompanyController::class, 'backups'])->name('backups.index');
        Route::post('/backups/create', [CompanyController::class, 'createBackup'])->name('backups.create');
        Route::post('/backups/bulk-create', [CompanyController::class, 'bulkCreateBackup'])->name('backups.bulk-create');
        Route::post('/backups/restore', [CompanyController::class, 'restoreBackup'])->name('backups.restore');
        Route::post('/backups/verify', [CompanyController::class, 'verifyBackup'])->name('backups.verify');
        Route::get('/backups/download/{filename}', [CompanyController::class, 'downloadBackup'])->name('backups.download');
        Route::delete('/backups/delete/{filename}', [CompanyController::class, 'deleteBackup'])->name('backups.delete');
        Route::get('/backups/logs/{company}', [CompanyController::class, 'backupLogs'])->name('backups.logs');

        // Tenant Audit & Activity Monitoring Center
        Route::get('/tenant-audit', [CompanyController::class, 'tenantAudit'])->name('tenant-audit.index');
        Route::get('/activity-logs', [CompanyController::class, 'tenantAudit'])->name('activity-logs.index');
        Route::get('/tenant-audit/event/{id}', [CompanyController::class, 'tenantAuditEvent'])->name('tenant-audit.event');
        Route::get('/tenant-audit/export', [CompanyController::class, 'exportTenantAudit'])->name('tenant-audit.export');

        // Platform System Health Monitoring Center
        Route::get('/system-health', [CompanyController::class, 'systemHealth'])->name('system-health.index');
        Route::get('/system-health/service/{name}', [CompanyController::class, 'systemHealthService'])->name('system-health.service');
        Route::post('/system-health/check', [CompanyController::class, 'runHealthCheck'])->name('system-health.check');

        // Platform Alert & Notification Center
        Route::get('/alerts', [CompanyController::class, 'alerts'])->name('alerts.index');
        Route::post('/alerts/{id}/read', [CompanyController::class, 'markAlertRead'])->name('alerts.read');
        Route::post('/alerts/{id}/resolve', [CompanyController::class, 'resolveAlert'])->name('alerts.resolve');
        Route::post('/alerts/mark-all-read', [CompanyController::class, 'markAllAlertsRead'])->name('alerts.mark-all-read');
        Route::get('/alerts/details/{id}', [CompanyController::class, 'alertDetails'])->name('alerts.details');

        // Company Administrator Management Center
        Route::get('/company-admins', [\App\Http\Controllers\SuperAdminController::class, 'companyAdmins'])->name('admins.index');
        Route::post('/company-admins', [\App\Http\Controllers\SuperAdminController::class, 'storeAdmin'])->name('admins.store');
        Route::get('/company-admins/export', [\App\Http\Controllers\SuperAdminController::class, 'exportAdmins'])->name('admins.export');
        Route::patch('/company-admins/{admin}', [\App\Http\Controllers\SuperAdminController::class, 'updateAdmin'])->name('admins.update');
        Route::patch('/company-admins/{admin}/archive', [\App\Http\Controllers\SuperAdminController::class, 'archiveAdmin'])->name('admins.archive');
        Route::patch('/company-admins/{admin}/restore', [\App\Http\Controllers\SuperAdminController::class, 'restoreAdmin'])->name('admins.restore');
        Route::delete('/company-admins/{admin}', [\App\Http\Controllers\SuperAdminController::class, 'deleteAdmin'])->name('admins.delete');

        // Developer & Work Management Center
        Route::get('/developers', [\App\Http\Controllers\SuperAdminController::class, 'developers'])->name('developers.index');
        Route::post('/developers', [\App\Http\Controllers\SuperAdminController::class, 'storeDeveloper'])->name('developers.store');
        Route::put('/developers/{id}', [\App\Http\Controllers\SuperAdminController::class, 'updateDeveloper'])->name('developers.update');
        Route::post('/developers/{id}/toggle-status', [\App\Http\Controllers\SuperAdminController::class, 'toggleDeveloperStatus'])->name('developers.toggle-status');
        Route::post('/developers/assign-work', [\App\Http\Controllers\SuperAdminController::class, 'assignWork'])->name('developers.assign-work');
        Route::post('/developers/tasks/{id}/status', [\App\Http\Controllers\SuperAdminController::class, 'updateTaskStatus'])->name('developers.task-status');
        Route::get('/developers/search-email', [\App\Http\Controllers\SuperAdminController::class, 'searchDeveloperEmail'])->name('developers.search-email');
        Route::post('/developers/{id}/enter-workspace', [\App\Http\Controllers\SuperAdminController::class, 'enterDeveloperWorkspace'])->name('developers.enter-workspace');
        Route::post('/developers/exit-workspace', [\App\Http\Controllers\SuperAdminController::class, 'exitDeveloperWorkspace'])->name('developers.exit-workspace');
        Route::post('/developers/{id}/reset-password', [\App\Http\Controllers\SuperAdminController::class, 'resetDeveloperPassword'])->name('developers.reset-password');
    });
});

