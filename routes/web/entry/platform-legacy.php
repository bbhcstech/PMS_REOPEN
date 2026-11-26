<?php

use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:super_admin,web', 'platform.superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [SuperAdminController::class, 'profile'])->name('profile');
    Route::post('/profile', [SuperAdminController::class, 'updateProfile'])->name('profile.update');
    Route::get('/subscriptions', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'subscriptions'])->name('subscriptions.index');
    Route::post('/subscriptions', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'assignPlan'])->name('subscriptions.store');
    Route::post('/subscriptions/store', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'assignPlan']);
    Route::post('/plans/toggle-module', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'togglePlanModule'])->name('plans.toggle-module');
    Route::post('/subscriptions/assign', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'assignPlan'])->name('subscriptions.assign');
    Route::post('/subscriptions/toggle-override', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'toggleCompanyOverride'])->name('subscriptions.toggle-override');
    Route::get('/companies', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'index'])->name('companies.list');
    Route::get('/companies/directory', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/suspended', [\App\Http\Controllers\SuperAdmin\CompanySuspensionController::class, 'index'])->name('companies.suspended');
    Route::get('/companies/{company}/suspension', [\App\Http\Controllers\SuperAdmin\CompanySuspensionController::class, 'create'])->whereNumber('company')->name('companies.suspension');
    Route::get('/companies/create', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'create'])->name('companies.create');
    Route::get('/companies/metrics', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'metrics'])->name('companies.metrics');
    Route::get('/companies/{company}', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'show'])->where('company', '[0-9]+')->name('companies.show');
    Route::get('/companies/{company}/edit', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'edit'])->where('company', '[0-9]+')->name('companies.edit');
    Route::match(['PUT', 'PATCH', 'POST'], '/companies/{company}', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'update'])->where('company', '[0-9]+')->name('companies.update');
    Route::match(['PUT', 'PATCH', 'POST'], '/companies/{company}/update', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'update'])->where('company', '[0-9]+');
    Route::post('/companies', [SuperAdminController::class, 'storeCompany'])->name('companies.store');
    Route::post('/companies/{company}/enter', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'enter'])->where('company', '[0-9]+')->name('companies.enter');
    Route::post('/companies/{company}/suspend', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'suspend'])->where('company', '[0-9]+')->name('companies.suspend');
    Route::post('/companies/{company}/activate', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'activate'])->where('company', '[0-9]+')->name('companies.activate');
    Route::delete('/companies/{company}', [SuperAdminController::class, 'deleteCompany'])->where('company', '[0-9]+')->name('companies.delete');
    Route::post('/company-admins', [SuperAdminController::class, 'storeAdmin'])->name('admins.store');
    Route::get('/company-admins', [SuperAdminController::class, 'companyAdmins'])->name('admins.index');
    Route::get('/company-admins/export', [SuperAdminController::class, 'exportAdmins'])->name('admins.export');
    Route::patch('/company-admins/{admin}', [SuperAdminController::class, 'updateAdmin'])->name('admins.update');
    Route::patch('/company-admins/{admin}/archive', [SuperAdminController::class, 'archiveAdmin'])->name('admins.archive');
    Route::patch('/company-admins/{admin}/restore', [SuperAdminController::class, 'restoreAdmin'])->name('admins.restore');
    Route::delete('/company-admins/{admin}', [SuperAdminController::class, 'deleteAdmin'])->name('admins.delete');
    Route::patch('/companies/{company}/status', [SuperAdminController::class, 'updateCompanyStatus'])->name('companies.status');
    Route::get('/alerts', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'alerts'])->name('alerts.index');
    Route::post('/alerts/{id}/read', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'markAlertRead'])->name('alerts.read');
    Route::post('/alerts/{id}/resolve', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'resolveAlert'])->name('alerts.resolve');
    Route::post('/alerts/mark-all-read', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'markAllAlertsRead'])->name('alerts.mark-all-read');
    Route::get('/alerts/details/{id}', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'alertDetails'])->name('alerts.details');

    // SuperAdmin Complaints Routes
    Route::get('/complaints', [\App\Http\Controllers\SuperAdmin\ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/export', [\App\Http\Controllers\SuperAdmin\ComplaintController::class, 'export'])->name('complaints.export');
    Route::get('/complaints/unread-count', [\App\Http\Controllers\SuperAdmin\ComplaintController::class, 'unreadCount'])->name('complaints.unread-count');
    Route::get('/complaints/{id}', [\App\Http\Controllers\SuperAdmin\ComplaintController::class, 'show'])->name('complaints.show');
    Route::get('/complaints/{id}/messages', [\App\Http\Controllers\SuperAdmin\ComplaintController::class, 'messages'])->name('complaints.messages');
    Route::post('/complaints/{id}/respond', [\App\Http\Controllers\SuperAdmin\ComplaintController::class, 'respond'])->name('complaints.respond');
    Route::post('/complaints/{id}/subscription-decision', [\App\Http\Controllers\SuperAdmin\ComplaintController::class, 'subscriptionDecision'])->name('complaints.subscription-decision');
    Route::match(['GET', 'POST', 'PATCH'], '/complaints/{id}/status', [\App\Http\Controllers\SuperAdmin\ComplaintController::class, 'updateStatus'])->name('complaints.status');
    Route::match(['GET', 'POST', 'PATCH'], '/complaints/{id}/assign', [\App\Http\Controllers\SuperAdmin\ComplaintController::class, 'assign'])->name('complaints.assign');
    Route::get('/complaints/{id}/attachment/{attachmentId}', [\App\Http\Controllers\SuperAdmin\ComplaintController::class, 'downloadAttachment'])->name('complaints.attachment');
    Route::match(['GET', 'POST'], '/clear-cache', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');

            $cacheDir = base_path('bootstrap/cache');
            if (is_dir($cacheDir)) {
                foreach (['routes-v7.php', 'config.php', 'events.php', 'packages.php', 'services.php'] as $f) {
                    $p = $cacheDir . DIRECTORY_SEPARATOR . $f;
                    if (file_exists($p)) {
                        @unlink($p);
                    }
                }
            }

            return redirect()->back()->with('success', 'Production server cache and routes cleared successfully!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Cache clear error: ' . $e->getMessage());
        }
    })->name('clear-cache');
});

// Standalone route aliases for super-admin.* and admins.*
Route::middleware(['auth:super_admin,web', 'platform.superadmin'])->group(function () {
    Route::get('/company-admins/export', [\App\Http\Controllers\SuperAdminController::class, 'exportAdmins'])->name('admins.export');
    Route::get('/company-admins', [\App\Http\Controllers\SuperAdminController::class, 'companyAdmins'])->name('admins.index');
    Route::post('/company-admins', [\App\Http\Controllers\SuperAdminController::class, 'storeAdmin'])->name('admins.store');
    Route::patch('/company-admins/{admin}', [\App\Http\Controllers\SuperAdminController::class, 'updateAdmin'])->name('admins.update');
    Route::patch('/company-admins/{admin}/archive', [\App\Http\Controllers\SuperAdminController::class, 'archiveAdmin'])->name('admins.archive');
    Route::patch('/company-admins/{admin}/restore', [\App\Http\Controllers\SuperAdminController::class, 'restoreAdmin'])->name('admins.restore');
    Route::delete('/company-admins/{admin}', [\App\Http\Controllers\SuperAdminController::class, 'deleteAdmin'])->name('admins.delete');

    // Fallback and alias routes for super-admin.*
    Route::get('/super-admin', [SuperAdminController::class, 'dashboard'])->name('super-admin.dashboard');
    Route::get('/super-admin/profile', [SuperAdminController::class, 'profile'])->name('super-admin.profile');
    Route::get('/super-admin/companies', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'index'])->name('super-admin.companies.index');
    Route::get('/super-admin/companies/create', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'create'])->name('super-admin.companies.create');
    Route::get('/super-admin/companies/metrics', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'metrics'])->name('super-admin.companies.metrics');
    Route::get('/super-admin/companies/{company}', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'show'])->where('company', '[0-9]+')->name('super-admin.companies.show');
    Route::get('/super-admin/companies/{company}/edit', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'edit'])->where('company', '[0-9]+')->name('super-admin.companies.edit');
    Route::match(['PUT', 'PATCH', 'POST'], '/super-admin/companies/{company}', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'update'])->where('company', '[0-9]+')->name('super-admin.companies.update');
    Route::match(['PUT', 'PATCH', 'POST'], '/super-admin/companies/{company}/update', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'update'])->where('company', '[0-9]+');
    Route::post('/super-admin/companies', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'store'])->name('super-admin.companies.store');
    Route::post('/super-admin/companies/{company}/enter', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'enter'])->where('company', '[0-9]+')->name('super-admin.companies.enter');
    Route::post('/super-admin/companies/{company}/suspend', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'suspend'])->where('company', '[0-9]+')->name('super-admin.companies.suspend');
    Route::post('/super-admin/companies/{company}/activate', [\App\Http\Controllers\SuperAdmin\CompanyController::class, 'activate'])->where('company', '[0-9]+')->name('super-admin.companies.activate');
    Route::delete('/super-admin/companies/{company}', [SuperAdminController::class, 'deleteCompany'])->where('company', '[0-9]+')->name('super-admin.companies.delete');
    Route::match(['GET', 'POST'], '/super-admin/clear-cache', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');

            $cacheDir = base_path('bootstrap/cache');
            if (is_dir($cacheDir)) {
                foreach (['routes-v7.php', 'config.php', 'events.php', 'packages.php', 'services.php'] as $f) {
                    $p = $cacheDir . DIRECTORY_SEPARATOR . $f;
                    if (file_exists($p)) {
                        @unlink($p);
                    }
                }
            }

            return redirect()->back()->with('success', 'Production server cache and routes cleared successfully!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Cache clear error: ' . $e->getMessage());
        }
    })->name('super-admin.clear-cache');
});
