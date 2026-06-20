<?php

use App\Http\Controllers\AdminActivityController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\ClientCategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientSubCategoryController;
use App\Http\Controllers\CollaboratingCompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Admin\GovernmentIdVerificationController;
use App\Http\Controllers\Admin\CompanyManagementController;
use App\Http\Controllers\Admin\ModuleManagementController;
use App\Http\Controllers\Admin\RoleAccountController;
use App\Http\Controllers\Admin\RolePermissionController;
// use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\FrontendUIController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizationDirectoryController;
use App\Http\Controllers\ParentDepartmentController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectMilestoneController;
use App\Http\Controllers\ProjectNoteController;
use App\Http\Controllers\ProjectUserController;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TaskCategoryController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskLabelController;
use App\Http\Controllers\TaskTimerController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TimeLogController;
use App\Mail\EmployeeInvite;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Settings\SettingController;
use App\Http\Controllers\Admin\Settings\CompanySettingsController;
use App\Http\Controllers\Admin\Settings\BusinessAddressController;
use App\Http\Controllers\Admin\Settings\AppSettingController;
use App\Http\Controllers\Admin\Settings\ProfileSettingController;
use App\Http\Controllers\Admin\Settings\TermsPolicyController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\ContractTemplateController;
use App\Http\Controllers\Admin\LeadContactController;
use App\Exports\AttendanceExport;
use App\Http\Controllers\DealController;






Route::middleware(['auth'])->group(function () {
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->name('attendance.clockIn');
});



Route::get('/smtp-test', function () {
    try {
        Mail::raw('SMTP Test Successful', function ($msg) {
            $msg->to('pallabk825@gmail.com')
                ->subject('SMTP Working - PMS');
        });

        return 'Email sent. Check your Gmail inbox or spam.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});


Route::get('designations/next-code', [DesignationController::class, 'nextCode'])
     ->name('designations.next-code');

Route::get('/employees/next-id', [EmployeeController::class, 'nextId'])
    ->name('employees.next-id')
    ->middleware('auth');

Route::get('attendance/export/excel', [AttendanceExport::class, 'exportExcel'])
    ->name('attendance.export.excel');

Route::get('attendance/export/pdf', [AttendanceExport::class, 'exportPdf'])
    ->name('attendance.export.pdf');

// Add this route for bulk delete
Route::delete('designations/bulk-delete', [DesignationController::class, 'bulkDelete'])->name('designations.bulk-delete');



Route::get('attendance/filter', [App\Http\Controllers\AttendanceController::class, 'filter'])->name('attendance.filter');


// Route::post('/attendance/get-employee-timeline', [AttendanceController::class, 'getEmployeeTimeline'])
//     ->name('attendance.getEmployeeTimeline');

// Route::post('/attendance/employee-locations', 'AttendanceController@getEmployeeLocations')
//     ->name('attendance.getEmployeeLocations');

// Route::get('/attendance/export-employee-locations', 'AttendanceController@exportEmployeeLocations')
//     ->name('attendance.exportEmployeeLocations');
// Employee Location Tracking Routes


Route::post('/attendance/employee-locations', [AttendanceController::class, 'getEmployeeLocations'])
    ->name('attendance.getEmployeeLocations');

// Add these routes
Route::post('/attendance/employee-locations', [AttendanceController::class, 'getEmployeeLocations'])->name('attendance.getEmployeeLocations');
Route::post('/attendance/get-employee-timeline', [AttendanceController::class, 'getEmployeeTimeline'])->name('attendance.getEmployeeTimeline');






Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('/attendance/filter', [AttendanceController::class, 'filter'])->name('attendance.filter');
Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');

// Admin-only routes - controller will check role
Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
Route::get('/attendance/by-member', [AttendanceController::class, 'byMember'])->name('attendance.byMember');
Route::get('/attendance/by-hour', [AttendanceController::class, 'byHour'])->name('attendance.byHour');
Route::get('/attendance/map-view', [AttendanceController::class, 'todayAttendanceByMap'])->name('attendance.today.map');
Route::get('/attendance/report', [AttendanceController::class, 'attendanceReport'])->name('attendance.report');
Route::get('/attendance/export/excel', [AttendanceController::class, 'exportExcel'])->name('attendance.export.excel');
Route::get('/attendance/export/pdf', [AttendanceController::class, 'exportPdf'])->name('attendance.export.pdf');
Route::get('/attendance/export/multi-pdf', [AttendanceController::class, 'exportMultiPdf'])->name('attendance.export.multi.pdf');
Route::post('/attendance/mark', [AttendanceController::class, 'markAttendance'])->name('attendance.mark');
Route::get('/attendance/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');

// Employee-only route (optional separate view)
Route::get('/my-attendance', [AttendanceController::class, 'employeeIndex'])->name('attendance.employee.index');




/*
|--------------------------------------------------------------------------
| Utility / Debug routes (mostly dev helpers)
|--------------------------------------------------------------------------
*/

Route::get('attendance/export/multi-pdf', [AttendanceController::class, 'exportMultiPdf'])
    ->name('attendance.export.multi_pdf');


Route::get('/get-subdepartments/{parentId}', function ($parentId) {
    return \App\Models\Department::where('parent_dpt_id', $parentId)->get();
})->name('get.subdepartments');





Route::get(
    '/employees/parent-departments/{id}/sub-departments',
    [EmployeeController::class, 'getSubDepartments']
)->name('employees.sub-departments');

Route::delete('/tickets/bulk-delete', [TicketController::class, 'bulkDelete'])
     ->name('tickets.bulk-delete');




Route::delete('timelogs/bulk-delete', [TimeLogController::class, 'bulkDelete'])
    ->name('timelogs.bulk-delete');


Route::get('/test-email', function () {
    $fakeUser = new User([
        'id'    => 999,
        'name'  => 'Test User',
        'email' => 'pallabk825@gmail.com',
    ]);

    $inviteUrl = url('/dummy-invite-link');

    try {
        Mail::to($fakeUser->email)->send(
            new EmployeeInvite($fakeUser, "This is a test email", $inviteUrl)
        );

        return "Email sent successfully!";
    } catch (\Exception $e) {
        return "Failed: " . $e->getMessage();
    }
});

Route::get('/debug-whos-logged', function () {
    $u = auth()->user();

    return response()->json([
        'auth_id' => $u?->id,
        'class'   => $u ? (method_exists($u, 'getMorphClass') ? $u->getMorphClass() : get_class($u)) : null,
        'email'   => $u?->email,
    ]);
})->middleware(['web', 'auth']);

Route::get('/debug-create-notif', function () {
    $u = auth()->user();
    if (! $u) {
        return response()->json(['error' => 'not logged in'], 401);
    }

    $task = Task::first() ?: Task::create([
        'task_short_code' => 'DBG1',
        'title'           => 'Debug Task',
        'project_id'      => 1,
    ]);

    $u->notify(new \App\Notifications\TaskAssignedNotification($task));

    return response()->json([
        'status'  => 'ok',
        'message' => 'notification created for current user',
        'user_id' => $u->id,
    ]);
})->middleware(['web', 'auth']);


// // Notification routes (works for both admin and employee)
// Route::middleware(['auth'])->group(function () {
//     Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
//     Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
//     Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
//     Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');
//     Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');
//     Route::get('/notifications/latest', [NotificationController::class, 'getLatest'])->name('notifications.latest');
// });

// Route::middleware(['auth'])->group(function () {
//     // 🔥 FIX: Remove duplicate route for /employee/notifications
//     // Keep only this one:
//     Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

//     Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
//     Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
//     Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');
//     Route::delete('/notifications/delete/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');

//     // Send notification routes
//     Route::post('/notifications/admin-to-employees', [NotificationController::class, 'adminToEmployees'])->name('notifications.adminToEmployees');
//     Route::post('/notifications/employee-to-admins', [NotificationController::class, 'employeeToAdmins'])->name('notifications.employeeToAdmins');
//     Route::post('/notifications/send-to-users', [NotificationController::class, 'sendToUsers'])->name('notifications.sendToUsers');
// });

// Admin specific routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Your existing admin routes
    // Example: Route::get('/dashboard', ...)->name('admin.dashboard');
});

// Employee specific routes (without duplicate notifications route)
Route::middleware(['auth'])->prefix('employee')->group(function () {
    // Other employee routes
    // Example: Route::get('/dashboard', ...)->name('employee.dashboard');

    // 🔥 REMOVE THIS DUPLICATE LINE:
    // Route::get('/notifications', [NotificationController::class, 'index'])->name('employee.notifications.index');
});




/*
|--------------------------------------------------------------------------
| Public / auth scaffolding routes
|--------------------------------------------------------------------------
*/

Route::get('/verify-otp', function () {
    return view('auth.verify-otp');
})->name('verify-otp');

Route::get('/hr-login', function () {
    return view('auth.login', ['loginTitle' => 'HR Login']);
})->middleware('guest')->name('hr.login');

Route::get('/manager-login', function () {
    return view('auth.login', ['loginTitle' => 'Manager Login']);
})->middleware('guest')->name('manager.login');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'module.access'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/companies', [SuperAdminController::class, 'storeCompany'])->name('companies.store');
    Route::post('/company-admins', [SuperAdminController::class, 'storeAdmin'])->name('admins.store');
    Route::get('/company-admins', [SuperAdminController::class, 'companyAdmins'])->name('admins.index');
    Route::get('/company-admins/export', [SuperAdminController::class, 'exportAdmins'])->name('admins.export');
    Route::patch('/company-admins/{admin}', [SuperAdminController::class, 'updateAdmin'])->name('admins.update');
    Route::patch('/company-admins/{admin}/archive', [SuperAdminController::class, 'archiveAdmin'])->name('admins.archive');
    Route::patch('/company-admins/{admin}/restore', [SuperAdminController::class, 'restoreAdmin'])->name('admins.restore');
    Route::delete('/company-admins/{admin}', [SuperAdminController::class, 'deleteAdmin'])->name('admins.delete');
    Route::patch('/companies/{company}/status', [SuperAdminController::class, 'updateCompanyStatus'])->name('companies.status');
});

// Front Controller (landing)
// Route::get('/', [FrontendController::class, 'index'])->name('home');


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home Route
Route::get('/', [FrontendUIController::class, 'index'])->name('home');

// ===========================================
// Product Routes
// ===========================================
Route::prefix('product')->name('product.')->group(function () {
    Route::get('/tasks', [FrontendUIController::class, 'productTasks'])->name('tasks');
    Route::get('/gantt', [FrontendUIController::class, 'productGantt'])->name('gantt');
    Route::get('/kanban', [FrontendUIController::class, 'productKanban'])->name('kanban');
    Route::get('/attendance', [FrontendUIController::class, 'productAttendance'])->name('attendance');
    Route::get('/leave', [FrontendUIController::class, 'productLeave'])->name('leave');
    Route::get('/performance', [FrontendUIController::class, 'productPerformance'])->name('performance');
    Route::get('/reports', [FrontendUIController::class, 'productReports'])->name('reports');
    Route::get('/dashboard', [FrontendUIController::class, 'productDashboard'])->name('dashboard');
    Route::get('/analytics', [FrontendUIController::class, 'productAnalytics'])->name('analytics');
});

// ===========================================
// Solutions Routes
// ===========================================
Route::prefix('solutions')->name('solutions.')->group(function () {
    Route::get('/enterprise', [FrontendUIController::class, 'solutionsEnterprise'])->name('enterprise');
    Route::get('/startups', [FrontendUIController::class, 'solutionsStartups'])->name('startups');
    Route::get('/hr', [FrontendUIController::class, 'solutionsHr'])->name('hr');
    Route::get('/developers', [FrontendUIController::class, 'solutionsDevelopers'])->name('developers');
    Route::get('/remote', [FrontendUIController::class, 'solutionsRemote'])->name('remote');
});

// ===========================================
// Features & Pricing
// ===========================================
Route::get('/features', [FrontendUIController::class, 'features'])->name('features');
Route::get('/pricing', [FrontendUIController::class, 'pricing'])->name('pricing');

// ===========================================
// Resources Routes
// ===========================================
Route::prefix('resources')->name('resources.')->group(function () {
    Route::get('/blog', [FrontendUIController::class, 'blog'])->name('blog');
    Route::get('/blog/{slug}', [FrontendUIController::class, 'blogSingle'])->name('blog.single');
    Route::get('/docs', [FrontendUIController::class, 'documentation'])->name('docs');
    Route::get('/api', [FrontendUIController::class, 'api'])->name('api');
    Route::get('/help', [FrontendUIController::class, 'helpCenter'])->name('help');
    Route::get('/faq', [FrontendUIController::class, 'faq'])->name('faq');
});

// ===========================================
// Company Routes
// ===========================================
Route::prefix('company')->name('company.')->group(function () {
    Route::get('/about', [FrontendUIController::class, 'about'])->name('about');
    Route::get('/careers', [FrontendUIController::class, 'careers'])->name('careers');
    Route::get('/contact', [FrontendUIController::class, 'contact'])->name('contact');
    Route::post('/contact', [FrontendUIController::class, 'contactSubmit'])->name('contact.submit');
    Route::get('/privacy', [FrontendUIController::class, 'privacy'])->name('privacy');
    Route::get('/terms', [FrontendUIController::class, 'terms'])->name('terms');
});





// Simple logout that clears custom session key
Route::get('/logout', function () {
    Session::forget('auth_id');
    return redirect()->route('home');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'module.access'])->group(function () {
    Route::prefix('admin/settings')->name('admin.')->group(function () {
        Route::get('/companies', [CompanyManagementController::class, 'index'])->name('companies.index');
        Route::get('/companies/create', [CompanyManagementController::class, 'create'])->name('companies.create');
        Route::post('/companies', [CompanyManagementController::class, 'store'])->name('companies.store');
        Route::get('/companies/{company}/edit', [CompanyManagementController::class, 'edit'])->name('companies.edit');
        Route::put('/companies/{company}', [CompanyManagementController::class, 'update'])->name('companies.update');
        Route::patch('/companies/{company}/activate', [CompanyManagementController::class, 'activate'])->name('companies.activate');
        Route::patch('/companies/{company}/deactivate', [CompanyManagementController::class, 'deactivate'])->name('companies.deactivate');

        Route::get('/modules', [ModuleManagementController::class, 'index'])->name('modules.index');
        Route::post('/modules', [ModuleManagementController::class, 'store'])->name('modules.store');
        Route::put('/modules/{module}', [ModuleManagementController::class, 'update'])->name('modules.update');
        Route::delete('/modules/{module}', [ModuleManagementController::class, 'destroy'])->name('modules.destroy');

        Route::get('/role-permissions', [RolePermissionController::class, 'index'])->name('role-permissions.index');
        Route::post('/role-permissions', [RolePermissionController::class, 'update'])->name('role-permissions.update');

        Route::get('/accounts/{role}', [RoleAccountController::class, 'index'])->name('role-accounts.index');
        Route::post('/accounts/{role}', [RoleAccountController::class, 'store'])->name('role-accounts.store');
        Route::put('/accounts/{role}/{user}', [RoleAccountController::class, 'update'])->name('role-accounts.update');
        Route::post('/accounts/{role}/{user}/reset-password', [RoleAccountController::class, 'resetPassword'])->name('role-accounts.reset-password');
    });

    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');

        Route::get('/architectures', [PayrollController::class, 'architectures'])->name('architectures.index');
        Route::post('/architectures', [PayrollController::class, 'storeArchitecture'])->name('architectures.store');
        Route::patch('/architectures/{architecture}/activate', [PayrollController::class, 'activateArchitecture'])->name('architectures.activate');

        Route::get('/salary-structures', [PayrollController::class, 'salaryStructures'])->name('salary-structures.index');
        Route::post('/salary-structures', [PayrollController::class, 'storeSalaryStructure'])->name('salary-structures.store');
        Route::post('/salary-components', [PayrollController::class, 'storeSalaryComponent'])->name('salary-components.store');

        Route::get('/deduction-rules', [PayrollController::class, 'deductionRules'])->name('deduction-rules.index');
        Route::post('/deduction-rules', [PayrollController::class, 'storeDeductionRule'])->name('deduction-rules.store');
        Route::get('/bonus-rules', [PayrollController::class, 'bonusRules'])->name('bonus-rules.index');
        Route::post('/bonus-rules', [PayrollController::class, 'storeBonusRule'])->name('bonus-rules.store');
        Route::get('/tax-rules', [PayrollController::class, 'taxRules'])->name('tax-rules.index');
        Route::post('/tax-rules', [PayrollController::class, 'storeTaxRule'])->name('tax-rules.store');
        Route::get('/overtime-rules', [PayrollController::class, 'overtimeRules'])->name('overtime-rules.index');
        Route::post('/overtime-rules', [PayrollController::class, 'storeOvertimeRule'])->name('overtime-rules.store');

        Route::get('/cycles', [PayrollController::class, 'cycles'])->name('cycles.index');
        Route::post('/cycles', [PayrollController::class, 'storeCycle'])->name('cycles.store');
        Route::post('/cycles/{cycle}/process', [PayrollController::class, 'process'])->name('cycles.process');

        Route::get('/payslips', [PayrollController::class, 'payslips'])->name('payslips.index');
        Route::get('/reports', [PayrollController::class, 'reports'])->name('reports.index');
        Route::get('/audit-logs', [PayrollController::class, 'auditLogs'])->name('audit-logs.index');

        Route::get('/policies', [PayrollController::class, 'policies'])->name('policies.index');
        Route::get('/settings', [PayrollController::class, 'settings'])->name('settings.index');
        Route::get('/import-export', [PayrollController::class, 'importExport'])->name('import-export.index');
        Route::get('/archive', [PayrollController::class, 'archive'])->name('archive.index');
        Route::get('/formula-builder', [PayrollController::class, 'formulaBuilder'])->name('formula-builder.index');
    });

    Route::get('/organization', [OrganizationDirectoryController::class, 'index'])->name('organization.index');
    Route::get('/organization/employees/{employee}', [OrganizationDirectoryController::class, 'show'])->name('organization.show');
    Route::patch('/organization/employees/{employee}/directory-profile', [OrganizationDirectoryController::class, 'updateDirectoryProfile'])->name('organization.directory-profile.update');

    /*
    |----------------------------------------------------------------------
    | Notifications
    |----------------------------------------------------------------------
    */

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.all');
    Route::get('/notifications/{id}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::post('/notifications/section/{section}/read', [NotificationController::class, 'markSectionAsRead'])->name('notifications.section.read');
    Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::get('/notifications/latest', [NotificationController::class, 'latest'])->name('notifications.latest');
    Route::get('/notifications/sidebar', [NotificationController::class, 'sidebar'])->name('notifications.sidebar');

    /*
    |----------------------------------------------------------------------
    | Dashboard helpers / sticky notes / timers / search
    |----------------------------------------------------------------------
    */

    Route::post('/sticky-notes', [DashboardController::class, 'notestore'])->name('sticky_notes.store');
    Route::patch('/sticky-notes/{stickyNote}/complete', [DashboardController::class, 'stickyNoteComplete'])->name('sticky_notes.complete');
    Route::delete('/sticky-notes/{stickyNote}', [DashboardController::class, 'stickyNoteDestroy'])->name('sticky_notes.destroy');
    Route::post('/timers/store', [DashboardController::class, 'timersstore'])->name('dashboard-timers.store');

    Route::post('/dashboard/clock-in', [DashboardController::class, 'clockIn'])->name('dashboard.clockin');
    Route::post('/dashboard/clock-out', [DashboardController::class, 'clockOut'])->name('dashboard.clockout');
    Route::post('/dashboard/employee-welcome-seen', [DashboardController::class, 'markEmployeeWelcomeSeen'])->name('dashboard.employeeWelcomeSeen');

    Route::get('/search', [DashboardController::class, 'globalSearch'])->name('dashboard.search');

    /*
    |----------------------------------------------------------------------
    | User profile
    |----------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |----------------------------------------------------------------------
    | Designations / Departments / Employees (HR)
    |----------------------------------------------------------------------
    */

    // Designation hierarchy
    Route::get('designations/designation-hierarchy', [DesignationController::class, 'hierarchy'])
        ->name('designations.hierarchy');
    Route::get('designations/chart-data', [DesignationController::class, 'chartData'])
        ->name('designations.chart-data');
    Route::post('designations/save-hierarchy', [DesignationController::class, 'saveHierarchy'])
        ->name('designations.save-hierarchy');
    Route::get('designations/archive', [DesignationController::class, 'archive'])
        ->name('designations.archive');
    Route::post('designations/bulk-archive', [DesignationController::class, 'bulkArchive'])
        ->name('designations.bulk-archive');
    Route::post('designations/{designation}/archive', [DesignationController::class, 'archiveDesignation'])
        ->name('designations.archive.action');
    Route::post('designations/{id}/restore', [DesignationController::class, 'restore'])
        ->name('designations.restore');

    // Bulk delete designations
Route::post('designations/bulk-delete', [DesignationController::class, 'bulkDelete'])
    ->name('designations.bulk-delete');

Route::resource('designations', DesignationController::class);


    // Ajax create designation from employee form
    Route::post('/designations/ajax-store', [EmployeeController::class, 'storeDesignation'])
        ->name('designations.ajax.store');

    // Resource
    Route::resource('designations', DesignationController::class);

    // Parent departments
    Route::get('parent-departments/archive', [ParentDepartmentController::class, 'archive'])
        ->name('parent-departments.archive');
    Route::post('parent-departments/{id}/restore', [ParentDepartmentController::class, 'restore'])
        ->name('parent-departments.restore');
    Route::post('parent-departments/bulk-delete', [ParentDepartmentController::class, 'bulkDestroy'])
        ->name('parent-departments.bulk-delete');
    Route::resource('parent-departments', ParentDepartmentController::class);

    // Departments
    Route::get('departments/archive', [DepartmentController::class, 'archive'])
        ->name('departments.archive');
    Route::post('departments/{id}/restore', [DepartmentController::class, 'restore'])
        ->name('departments.restore');
    Route::post('departments/bulk-delete', [DepartmentController::class, 'bulkDestroy'])
        ->name('departments.bulk-delete');
    Route::resource('departments', DepartmentController::class);

    Route::delete('departments/bulk-destroy', [DepartmentController::class, 'bulkDestroy'])
    ->name('departments.bulkDestroy');

    // Employees
    Route::delete('/employees/bulk-delete', [EmployeeController::class, 'bulkDelete'])
        ->name('employees.bulk.delete');
    Route::post('employees/bulk-update-status', [EmployeeController::class, 'bulkUpdateStatus'])
        ->name('employees.bulkUpdateStatus');
    Route::get('employees/archive', [EmployeeController::class, 'archive'])
        ->name('employees.archive');
    Route::post('employees/archive/bulk-restore', [EmployeeController::class, 'bulkRestore'])
        ->name('employees.archive.bulkRestore');
    Route::post('employees/{id}/restore', [EmployeeController::class, 'restore'])
        ->name('employees.restore');

    Route::resource('employees', EmployeeController::class);
    Route::get('employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');


    // Add this route in your routes/web.php file
        Route::post('designations/check-exists', [DesignationController::class, 'checkExists'])->name('designations.check-exists');

        // For AJAX updates (used in edit.blade.php)
        Route::post('/designations/{designation}/ajax-update', [DesignationController::class, 'update'])->name('designations.ajax.update');




    // Employee invites
    Route::get('employees/invite/accept', [EmployeeController::class, 'acceptInvite'])
        ->name('employees.invite.accept')
        ->middleware('signed');
    Route::post('employees/invite/accept', [EmployeeController::class, 'acceptInviteSubmit'])
        ->name('employees.invite.complete');
    Route::post('employees/send-invite', [EmployeeController::class, 'sendInvite'])
        ->name('employees.sendInvite');
    // add this
    Route::post('employees/store-department', [\App\Http\Controllers\EmployeeController::class, 'storeDepartment'])
    ->name('employees.storeDepartment');

    //employee email and mobile validation routes inside admin group

    Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function() {
    // ... other routes ...

    // Add these validation routes
    Route::post('/employees/check-email', [EmployeeController::class, 'checkEmail'])->name('employees.check-email');
    Route::post('/employees/check-mobile', [EmployeeController::class, 'checkMobile'])->name('employees.check-mobile');

    // ... other routes ...


        Route::post('/designations/store-ajax', [EmployeeController::class, 'storeDesignation'])->name('designations.store.ajax');
        Route::post('/parent-departments/quick-create', [EmployeeController::class, 'storeParentDepartment'])->name('parent-departments.quick-create');
        Route::post('/departments/store-ajax', [EmployeeController::class, 'storeSubDepartment'])->name('departments.store.ajax');
        Route::post('/countries/quick-create', [EmployeeController::class, 'storeCountry'])->name('countries.quick-create');


});

    /*
    |----------------------------------------------------------------------
    | Attendance
    |----------------------------------------------------------------------
    */

    Route::middleware(['auth'])->group(function () {

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/mark', [AttendanceController::class, 'markAttendance'])->name('attendance.mark');

    // Filter (remove duplicate below)
    Route::get('/attendance/filter', [AttendanceController::class, 'filter'])->name('attendance.filter');

    Route::get('/attendance/details', [AttendanceController::class, 'showAttendanceDetails'])->name('attendance.details');

    // Settings
    Route::get('/attendance/settings', [AttendanceController::class, 'settings'])->name('attendance.settings');
    Route::post('/attendance/settings', [AttendanceController::class, 'updateSettings'])->name('attendance.settings.update');

    // Remove this duplicate ↓ (same name + same controller)
    // Route::get('/admin/attendance/filter', [AttendanceController::class, 'filter'])->name('attendance.filter');

    // Create / Store
    Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');

    // Old single-user report
    Route::get('/attendance-report', [AttendanceController::class, 'attendanceReport'])->name('attendance.report');

    // Archive
    Route::get('/attendance/archive', [AttendanceController::class, 'archive'])->name('attendance.archive');
    Route::post('/attendance/{id}/restore', [AttendanceController::class, 'restore'])->name('attendance.restore');

    // Exports (keep your URL & names exactly SAME)
    Route::get('/attendance-export-excel', [AttendanceController::class, 'exportExcel'])->name('attendance.export.excel');
    Route::get('/attendance-export-pdf',   [AttendanceController::class, 'exportPdf'])->name('attendance.export.pdf');

    // Edit
    Route::get('attendance/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::put('attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::post('/attendance/month/archive', [AttendanceController::class, 'archiveMonth'])->name('attendance.month.archive');

    // Map view
    Route::get('/attendance/today/map', [AttendanceController::class, 'todayAttendanceByMap'])->name('attendance.today.map');

    // Member-wise view
    Route::get('/attendance/member', [AttendanceController::class, 'byMember'])->name('attendance.byMember');

    // By-hour view
    Route::get('/by-hour', [AttendanceController::class, 'byHour'])->name('attendance.byHour');
});

    /*
    |----------------------------------------------------------------------
    | Leaves
    |----------------------------------------------------------------------
    */

    Route::post('/leaves/bulk-delete', [LeaveController::class, 'bulkDelete'])
        ->name('leaves.bulk-delete');
    Route::post('/leaves/bulk-action', [LeaveController::class, 'bulkAction'])
        ->name('leaves.bulkAction');
    Route::post('/leaves/bulk-archive', [LeaveController::class, 'bulkArchive'])
        ->name('leaves.bulk-archive');
    Route::post('/leaves/archive-all', [LeaveController::class, 'archiveAll'])
        ->name('leaves.archive-all');
    Route::post('/leaves/update-paid-status', [LeaveController::class, 'updatePaidStatus'])
        ->name('leaves.updatePaidStatus');

    Route::get('leaves/calendar', [LeaveController::class, 'calendar'])->name('leaves.calendar');
    Route::get('/leaves/calendar/data', [LeaveController::class, 'calendarData'])->name('leaves.calendar.data');
    Route::get('/leaves/archive/list', [LeaveController::class, 'archive'])->name('leaves.archive');
    Route::post('/leaves/archive/{id}/restore', [LeaveController::class, 'restore'])->name('leaves.restore');

    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/apology-letters', [LeaveController::class, 'apologyLetters'])->name('leaves.apology-letters.index');
    Route::get('/leaves/apology-letters/create', [LeaveController::class, 'createApologyLetter'])->name('leaves.apology-letters.create');
    Route::post('/leaves/apology-letters', [LeaveController::class, 'storeApologyLetter'])->name('leaves.apology-letters.store');
    Route::get('/leaves/apology-letters/sample/download', [LeaveController::class, 'downloadApologySample'])->name('leaves.apology-letters.sample.download');
    Route::get('/leaves/apology-letters/archive/list', [LeaveController::class, 'archivedApologyLetters'])->name('leaves.apology-letters.archive');
    Route::post('/leaves/apology-letters/bulk-archive', [LeaveController::class, 'archiveApologyLetters'])->name('leaves.apology-letters.bulk-archive');
    Route::post('/leaves/apology-letters/bulk-restore', [LeaveController::class, 'restoreApologyLetters'])->name('leaves.apology-letters.bulk-restore');
    Route::post('/leaves/apology-letters/{letter}/archive', [LeaveController::class, 'archiveApologyLetter'])->name('leaves.apology-letters.archive.action');
    Route::post('/leaves/apology-letters/{letter}/restore', [LeaveController::class, 'restoreApologyLetter'])->name('leaves.apology-letters.restore');
    Route::get('/leaves/apology-letters/{letter}', [LeaveController::class, 'showApologyLetter'])->name('leaves.apology-letters.show');
    Route::patch('/leaves/apology-letters/{letter}/review', [LeaveController::class, 'reviewApologyLetter'])->name('leaves.apology-letters.review');
    Route::get('/leaves/create', [LeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves/store', [LeaveController::class, 'store'])->name('leaves.store');
    Route::post('/leaves/{leave}/archive', [LeaveController::class, 'archiveLeave'])->name('leaves.archive.action');
    Route::delete('/leaves/{id}', [LeaveController::class, 'destroy'])->name('leaves.destroy');
    Route::get('leaves/{leave}/edit', [LeaveController::class, 'edit'])->name('leaves.edit');
    Route::put('leaves/{leave}', [LeaveController::class, 'update'])->name('leaves.update');
    Route::get('/leaves/{leave}', [LeaveController::class, 'show'])->name('leaves.show');
    Route::patch('/leaves/{leave}/status', [LeaveController::class, 'updateStatus'])->name('leaves.updateStatus');
    Route::get('/admin/leaves/report', [LeaveController::class, 'leaveReport'])->name('admin.leave.report');



    Route::post('/leaves/policy', [LeaveController::class, 'updatePolicy'])->name('leaves.update-policy');
    Route::post('/leaves/reset/{id}', [LeaveController::class, 'resetEmployeeLeaves'])->name('leaves.reset-employee-leaves');
    Route::get('/leaves/export', [LeaveController::class, 'export'])->name('leaves.export');
   /*
|----------------------------------------------------------------------
| Holidays
|----------------------------------------------------------------------
*/

Route::post('/holidays/bulk-action', [HolidayController::class, 'bulkAction'])->name('holiday.bulkAction');
Route::get('/holidays/export/list', [HolidayController::class, 'export'])->name('holidays.export');
Route::get('/holidays/import/sample', [HolidayController::class, 'sample'])->name('holidays.sample');
Route::post('/holidays/import/excel', [HolidayController::class, 'importExcel'])->name('holidays.import.excel');
Route::post('/holidays/import/image', [HolidayController::class, 'importImage'])->name('holidays.import.image');
Route::get('/holidays/archive/list', [HolidayController::class, 'archive'])->name('holidays.archive');
Route::post('/holidays/bulk-archive', [HolidayController::class, 'bulkArchive'])->name('holidays.bulk-archive');
Route::post('/holidays/{holiday}/archive', [HolidayController::class, 'archiveHoliday'])->name('holidays.archive.action');
Route::post('/holidays/archive/{id}/restore', [HolidayController::class, 'restore'])->name('holidays.restore');
Route::resource('holidays', HolidayController::class)->except(['show']);

Route::get('employee-holidays', [HolidayController::class, 'employeeView'])->name('employee.holidays');

// ✅ FIXED: Change 'calendarView' to 'calendar'
Route::get('calendar-holidays', [HolidayController::class, 'calendar'])->name('holidays.calendar');

Route::post('/holidays/mark', [HolidayController::class, 'markHoliday'])->name('holidays.mark');

// ✅ Optional: Add Employee Calendar route
Route::get('employee/calendar-holidays', [HolidayController::class, 'calendar'])->name('employee.holidays.calendar');

    /*
    |----------------------------------------------------------------------
    | Awards / Appreciations
    |----------------------------------------------------------------------
    */

    // Route::post('/awards/bulk-action', [AwardController::class, 'bulkAction'])->name('awards.bulkAction');
    // Route::post('/apreciation/bulk-action', [AwardController::class, 'apreciationbulkAction'])->name('apreciation.bulkAction');
    // // Route::post('awards/bulk-delete', [AwardController::class, 'bulkDeleteAwards'])->name('awards.bulk-delete');
    // Route::post('awards/bulk-delete', [AwardController::class, 'bulkDelete'])->name('awards.bulk-delete');
    // Route::post('/appreciations/{id}/status', [AwardController::class, 'updateStatus'])->name('appreciations.updateStatus');
    // Route::resource('awards', AwardController::class)->except(['show']);
    // Route::get('my-awards', [AwardController::class, 'myAwards'])->name('employee.awards');

    // Route::post('/awards/appreciation-store', [AwardController::class, 'appreciationstore'])->name('awards.appreciation-store');

    // // Add this TEMPORARY route at the TOP of your routes file
    //     Route::get('/awards/appreciation-index', [AwardController::class, 'apreciationIndex'])->name('awards.apreciation-index');


    // Route::get('/awards/appreciation/edit/{id}', [AwardController::class, 'appreciationedit'])->name('awards.appreciation-edit');
    // Route::get('/awards/appreciation-create', [AwardController::class, 'appreciationcreate'])->name('awards.appreciation-create');
    // Route::put('/awards/appreciation/update/{id}', [AwardController::class, 'appreciationupdate'])->name('awards.appreciation-update');
    // Route::delete('/awards/appreciation/{id}', [AwardController::class, 'appreciationdestroy'])->name('awards.appreciation-destroy');






    Route::post('/awards/bulk-action', [AwardController::class, 'bulkAction'])->name('awards.bulkAction');
Route::post('/awards/bulk-delete', [AwardController::class, 'bulkDelete'])->name('awards.bulk-delete');
Route::post('/appreciations/{id}/status', [AwardController::class, 'updateStatus'])->name('appreciations.updateStatus');

// Appreciation routes with CORRECT spelling (double 'p')
Route::get('/awards/appreciation-index', [AwardController::class, 'appreciationIndex'])->name('awards.appreciation-index');
Route::get('/awards/appreciation-create', [AwardController::class, 'appreciationCreate'])->name('awards.appreciation-create');
Route::post('/awards/appreciation-store', [AwardController::class, 'appreciationStore'])->name('awards.appreciation-store');
Route::get('/awards/appreciation/edit/{id}', [AwardController::class, 'appreciationEdit'])->name('awards.appreciation-edit');
Route::put('/awards/appreciation/update/{id}', [AwardController::class, 'appreciationUpdate'])->name('awards.appreciation-update');
Route::delete('/awards/appreciation/{id}', [AwardController::class, 'appreciationDestroy'])->name('awards.appreciation-destroy');
Route::post('/awards/appreciation-bulk-action', [AwardController::class, 'appreciationBulkAction'])->name('awards.appreciation-bulk-action');

// Resource routes
Route::resource('awards', AwardController::class)->except(['show']);
Route::get('my-awards', [AwardController::class, 'myAwards'])->name('employee.awards');
Route::get('/my-awards', [AwardController::class, 'myAwards'])->name('awards.my-awards');



    /*
    |----------------------------------------------------------------------
    | Clients
    |----------------------------------------------------------------------
    */

    Route::post('clients/bulk-delete', [ClientController::class, 'bulkDelete'])->name('clients.bulk-delete');
    Route::post('clients/bulk-action', [ClientController::class, 'bulkAction'])->name('clients.bulkAction');

    Route::get('/clients/pending', [ClientController::class, 'pending'])->name('clients.pending');
    Route::post('/clients/pending/bulk-action', [ClientController::class, 'pendingBulkAction'])->name('clients.pendingbulkAction');

    Route::resource('clients', ClientController::class);
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::resource('collaborating-companies', CollaboratingCompanyController::class);

    // client categories
    Route::resource('client-categories', ClientCategoryController::class)->only(['store', 'index']);
    Route::resource('client-sub-categories', ClientSubCategoryController::class)->only(['store', 'index']);

    /*
    |----------------------------------------------------------------------
    | Projects
    |----------------------------------------------------------------------
    */

    Route::post('/projects/bulk-delete', [ProjectController::class, 'bulkDelete'])->name('projects.bulk-delete');
    Route::post('/projects/bulk-status', [ProjectController::class, 'bulkStatus'])->name('projects.bulk-status');
    Route::post('/projects/import', [ProjectController::class, 'import'])->name('projects.import');
    Route::patch('admin/projects/{project}/status', [ProjectController::class, 'toggleStatus'])->name('projects.toggleStatus');
    Route::post('projects/{project}/updates', [ProjectController::class, 'storeUpdate'])->name('projects.updates.store');

    Route::get('projects/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('projects/{project}/archive', [ProjectController::class, 'archiveProject'])->name('projects.archive.action');

    Route::get('projects/project-calendar', [ProjectController::class, 'projectCalendar'])->name('projects.calendar');
    Route::put('projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    Route::delete('projects/{project}/force-delete', [ProjectController::class, 'forceDelete'])->name('projects.forceDelete');

    Route::resource('projects', ProjectController::class);
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

    // Project additional routes
    Route::post('/project-categories', [ProjectController::class, 'categorystore'])->name('project-categories.store');
    Route::delete('/project-categories/{id}', [ProjectController::class, 'categorydestroy'])->name('project-categories.destroy');
    Route::post('/project/store', [ProjectController::class, 'clientstore'])->name('project.clientstore');
    Route::post('/projects/{id}/duplicate', [ProjectController::class, 'duplicate'])->name('projects.duplicate');

    // Archive-related
    Route::get('projects/{project}/tasks/board', [TaskController::class, 'taskBoard'])->name('projects.tasks.board');

    // Project members
    Route::prefix('projects/{project}/members')->name('project-members.')->group(function () {
        Route::get('/', [ProjectUserController::class, 'index'])->name('index');
        Route::get('/add', [ProjectUserController::class, 'create'])->name('create');
        Route::post('/', [ProjectUserController::class, 'store'])->name('store');
        Route::delete('/{user}', [ProjectUserController::class, 'destroy'])->name('destroy');
    });

    // Project files
    Route::prefix('projects/{project}/files')->name('project-files.')->group(function () {
        Route::get('/', [ProjectFileController::class, 'index'])->name('index');
        Route::post('/', [ProjectFileController::class, 'store'])->name('store');
        Route::delete('/{file}', [ProjectFileController::class, 'destroy'])->name('destroy');
    });

    // Project milestones
    Route::get('/projects/{project}/milestones', [ProjectMilestoneController::class, 'index'])->name('milestones.index');
    Route::post('/milestones/store', [ProjectMilestoneController::class, 'store'])->name('milestones.store');
    Route::delete('/milestones/{id}', [ProjectMilestoneController::class, 'destroy'])->name('milestones.destroy');

    // Project notes
    Route::prefix('projects/{project}/notes')->name('projects.notes.')->group(function () {
        Route::get('/', [ProjectNoteController::class, 'index'])->name('index');
        Route::get('/create', [ProjectNoteController::class, 'create'])->name('create');
        Route::post('/', [ProjectNoteController::class, 'store'])->name('store');
        Route::get('{note}/view', [ProjectNoteController::class, 'show'])->name('noteshow');
        Route::get('{note}/edit', [ProjectNoteController::class, 'edit'])->name('edit');
        Route::put('{note}', [ProjectNoteController::class, 'update'])->name('update');
        Route::delete('{note}', [ProjectNoteController::class, 'destroy'])->name('destroy');
    });

    // Project discussions
    Route::prefix('projects/{project}/discussions')->name('projects.discussions.')->group(function () {
        Route::get('/', [DiscussionController::class, 'index'])->name('index');
        Route::get('/create', [DiscussionController::class, 'create'])->name('create');
        Route::post('/', [DiscussionController::class, 'store'])->name('store');
        Route::get('{discussion}', [DiscussionController::class, 'show'])->name('show');
        Route::delete('{discussion}', [DiscussionController::class, 'destroy'])->name('destroy');
    });

    Route::post('/discussion-categories', [DiscussionController::class, 'disscatstore'])->name('discussion-categories.store');
    Route::delete('/discussion-categories/{id}', [DiscussionController::class, 'disscatdestroy'])->name('discussion-categories.destroy');
    Route::post('projects/{project}/discussions/{discussion}/replies', [DiscussionController::class, 'repliesstore'])->name('projects.discussions.replies.store');

    // Project reports / advanced dashboards
    Route::get('account/projects/{project}/burndown-chart', [ProjectController::class, 'burndown'])->name('projects.burndown');
    Route::get('/admin/activity-log/project/{project}', [AdminActivityController::class, 'projectActivity'])->name('admin.activities.project');

    Route::get('/account/dashboard-project', [DashboardController::class, 'project'])->name('dashproject');
    Route::get('/account/dashboard-advanced', [DashboardController::class, 'clientDashboard'])->name('dashboard.client');
    Route::get('/dashboard-advanced', [DashboardController::class, 'ticketDashboard'])->name('dashboard.ticket');
    Route::get('/hr-dashboard', [DashboardController::class, 'hrindex'])->name('hr.dashboard');

    // Gantt
    Route::get('/projects/{project}/gantt', [ProjectController::class, 'ganttChart'])->name('projects.gantt');
    Route::get('/projects/{project}/gantt-tasks', [ProjectController::class, 'getGanttTasks'])->name('projects.gantt-tasks');
    Route::get('/projects/{project}/public-gantt', [ProjectController::class, 'publicGantt'])->name('projects.public-gantt');

    /*
    |----------------------------------------------------------------------
    | Tasks
    |----------------------------------------------------------------------
    */

    Route::delete('/tasks/bulk-delete', [TaskController::class, 'bulkDelete'])->name('tasks.bulkDelete');
    Route::post('/tasks/bulk-status-update', [TaskController::class, 'bulkStatusUpdate'])->name('tasks.bulkStatusUpdate');

    Route::resource('tasks', TaskController::class)->except(['show']);
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index'])->name('projects.tasks.index');
    Route::get('projects/{project}/tasks/board', [TaskController::class, 'taskBoard'])->name('projects.tasks.board');
    Route::post('/tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::post('/tasks/{task}/toggle-pin', [TaskController::class, 'togglePin'])->name('tasks.toggle-pin');

    Route::post('/tasks/{task}/notes', [TaskController::class, 'storeNote'])->name('tasks.notes.store');

    Route::get('/projects/{id}/tasks', [TimeLogController::class, 'getTasks']); // helper for timelog forms

    // Task calendar + boards
    Route::get('/tasks/calendar', [TaskController::class, 'calendarView'])->name('tasks.calendar');
    Route::get('/users/tasks/board', [TaskController::class, 'userTaskBoard'])->name('users.tasks.board');
    Route::get('/tasks/waiting-approval', [TaskController::class, 'waitingApproval'])->name('tasks.waiting-approval');

    // Task labels / categories
    Route::post('/labels', [TaskController::class, 'storeLabel'])->name('labels.store');
    Route::resource('task-categories', TaskCategoryController::class)->only(['store']);
    Route::post('task-categories/{task_category}/delete', [TaskCategoryController::class, 'destroy'])->name('task-categories.force-delete');

    // Second labels routes (overrides previous, kept exactly as original)
    Route::post('/labels', [TaskLabelController::class, 'store'])->name('labels.store');
    Route::post('/labels/{id}', [TaskLabelController::class, 'destroy'])->name('labels.destroy');

    // Task show
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');

    // Task timers
    Route::post('/tasks/{task}/timer/start', [TaskTimerController::class, 'start'])->name('task-timer.start');
    Route::post('/timers/start', [TaskTimerController::class, 'store'])->name('timers.store');
    Route::post('/globaltaskstimer/stop', [TaskTimerController::class, 'globalstop'])->name('globaltasktimer.stop');
    Route::post('/tasks/{task}/timer/stop', [TaskTimerController::class, 'stop'])->name('task-timer.stop');
    Route::post('/tasks/{task}/timer/pause', [TaskTimerController::class, 'pause'])->name('task-timer.pause');
    Route::post('/tasks/{task}/timer/resume', [TaskTimerController::class, 'resume'])->name('task-timer.resume');

    Route::get('/tasks/{task}/mark-complete', [TaskController::class, 'markComplete'])->name('tasks.markComplete');

    // Task files
    Route::post('/tasks/{task}/upload-file', [TaskController::class, 'uploadFile'])->name('tasks.uploadFile');
    Route::delete('/tasks/{task}/file-delete', [TaskController::class, 'deleteFile'])->name('tasks.deleteFile');

    // Subtasks
    Route::post('/tasks/{task}/subtasks', [SubTaskController::class, 'store'])->name('subtasks.store');
    Route::get('subtasks/{subtask}/edit', [SubTaskController::class, 'edit'])->name('subtasks.edit');
    Route::put('subtasks/{subtask}', [SubTaskController::class, 'update'])->name('subtasks.update');
    Route::delete('subtasks/{subtask}', [SubTaskController::class, 'destroy'])->name('subtasks.destroy');
    Route::delete('/subtasks/{subtask}/file', [SubTaskController::class, 'deleteFile'])->name('subtask.file.delete');

    // Task comments
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('task-comments.store');

    /*
    |----------------------------------------------------------------------
    | Timelogs
    |----------------------------------------------------------------------
    */

    Route::post('/timelogs/bulk-status-update', [TimeLogController::class, 'bulkStatusUpdate'])->name('timelogs.bulkStatusUpdate');

    Route::prefix('projects/{project}')->name('projects.')->group(function () {
        Route::get('timelogs', [TimeLogController::class, 'index'])->name('timelogs.index');
        Route::get('timelogs/create', [TimeLogController::class, 'create'])->name('timelogs.create');
    });

    Route::get('timelogs/calendar', [TimeLogController::class, 'calendar'])->name('timelogs.calendar');
    Route::get('timelogs/by-employee', [TimeLogController::class, 'byEmployee'])->name('timelogs.byEmployee');

    Route::resource('timelogs', TimeLogController::class);
    Route::get('/projects/{project}/timelogs', [TimeLogController::class, 'index'])->name('projects.timelogs.index');

    Route::get('/timelogs/get-task-employee/{taskId}', [TimeLogController::class, 'getTaskEmployee']);
    Route::get('/project/{projectId}/tasks', [TimeLogController::class, 'getTasksByProject'])->name('timelogs.tasks.byProject');

    /*
    |----------------------------------------------------------------------
    | Expenses (per project)
    |----------------------------------------------------------------------
    */

    Route::prefix('projects/{project}')->group(function () {
        Route::resource('expenses', ExpenseController::class)->except(['show']);
    });

    /*
    |----------------------------------------------------------------------
    | Tickets
    |----------------------------------------------------------------------
    */

    Route::resource('tickets', TicketController::class);
    Route::post('/tickets/change-status', [TicketController::class, 'changeStatus'])->name('tickets.change-status');
    Route::post('/ticket-groups/store', [TicketController::class, 'storeGroup'])->name('ticket-groups.store');
    Route::get('/ticket-groups/fetch', [TicketController::class, 'fetchGroups'])->name('ticket-groups.fetch');
    Route::delete('/ticket-groups/{id}', [TicketController::class, 'destroygroup'])->name('ticket-groups.destroy');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{id}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::put('/tickets/{id}/update-details', [TicketController::class, 'updateDetails'])->name('tickets.updateDetails');
    Route::get('/admin/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('tickets/bulk-action', [TicketController::class, 'bulkAction'])->name('tickets.bulk-action');

    /*
    |----------------------------------------------------------------------
    | Misc dashboards
    |----------------------------------------------------------------------
    */

    Route::get('/account/dashboard-project', [DashboardController::class, 'project'])->name('dashproject');
    Route::get('/account/dashboard-advanced', [DashboardController::class, 'clientDashboard'])->name('dashboard.client');
    Route::get('/dashboard-advanced', [DashboardController::class, 'ticketDashboard'])->name('dashboard.ticket');
    Route::get('/hr-dashboard', [DashboardController::class, 'hrindex'])->name('hr.dashboard');
});





/// Lead Contacts Routes
 Route::get('leads/contacts', [LeadContactController::class, 'index'])->name('leads.contacts.index');
Route::get('leads/contacts/create', [LeadContactController::class, 'create'])->name('leads.contacts.create');
Route::post('leads/contacts/store', [LeadContactController::class, 'store'])->name('leads.contacts.store');
Route::get('leads/contacts/{id}', [LeadContactController::class, 'show'])->name('leads.contacts.show');
Route::get('leads/contacts/{id}/edit', [LeadContactController::class, 'edit'])->name('leads.contacts.edit');
Route::put('leads/contacts/{id}', [LeadContactController::class, 'update'])->name('leads.contacts.update');
Route::delete('leads/contacts/{id}', [LeadContactController::class, 'destroy'])->name('leads.contacts.destroy');

// Bulk actions
// Route::post('leads/contacts/bulk-delete', [LeadContactController::class, 'bulkDelete'])->name('leads.contacts.bulk.delete');
// Bulk delete
Route::post('/leads/contacts/bulk-delete', [LeadContactController::class, 'bulkDelete'])
    ->name('leads.contacts.bulk.delete');
Route::post('leads/contacts/convert', [LeadContactController::class, 'convertToClient'])->name('leads.contacts.convert');

// Import/Export
// Route::get('leads/contacts/export', [LeadContactController::class, 'export'])->name('leads.contacts.export');

Route::get('/leads/contacts/export', [LeadContactController::class, 'export'])->name('leads.contacts.export');
Route::get('leads/contacts/template', [LeadContactController::class, 'downloadTemplate'])->name('leads.contacts.template');
Route::post('leads/contacts/import', [LeadContactController::class, 'import'])->name('leads.contacts.import');

// Convert lead to client and vice versa
Route::post('/leads/contacts/convert', [LeadContactController::class, 'convert'])
    ->name('leads.contacts.convert');



// // Deal Routes - IMPORTANT: Exact routes FIRST
Route::get('admin/deals/index', [DealController::class, 'index'])->name('admin.deals.index');
Route::get('admin/deals/create', [DealController::class, 'create'])->name('admin.deals.create');
Route::get('admin/deals/export', [DealController::class, 'export'])->name('admin.deals.export');

// // POST routes (no parameters)
Route::post('admin/deals', [DealController::class, 'store'])->name('admin.deals.store');
Route::post('admin/deals/import', [DealController::class, 'import'])->name('admin.deals.import');
Route::post('admin/deals/bulk-action', [DealController::class, 'bulkAction'])->name('admin.deals.bulk.action');

// // Parameter routes - MUST be LAST
Route::get('admin/deals/{deal}', [DealController::class, 'show'])->name('admin.deals.show');
Route::get('admin/deals/{deal}/edit', [DealController::class, 'edit'])->name('admin.deals.edit');
Route::put('admin/deals/{deal}', [DealController::class, 'update'])->name('admin.deals.update');
Route::delete('admin/deals/{deal}', [DealController::class, 'destroy'])->name('admin.deals.destroy');
Route::post('admin/deals/{deal}/update-stage', [DealController::class, 'updateStage'])->name('admin.deals.update.stage');
// Inside your deals route group
Route::post('/{deal}/add-follow-up', [DealController::class, 'addFollowUp'])->name('deals.add-follow-up');




// Admin Contracts Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('government-id-verifications', [GovernmentIdVerificationController::class, 'index'])->name('government-id-verifications.index');
    Route::patch('government-id-verifications/{verification}/approve', [GovernmentIdVerificationController::class, 'approve'])->name('government-id-verifications.approve');
    Route::patch('government-id-verifications/{verification}/reject', [GovernmentIdVerificationController::class, 'reject'])->name('government-id-verifications.reject');

    // Contracts
    Route::get('contracts/export', [ContractController::class, 'export'])->name('contracts.export');
    Route::post('contracts/{contract}/update-status', [ContractController::class, 'updateStatus'])->name('contracts.update-status');
    Route::post('contracts/{contract}/sign', [ContractController::class, 'signContract'])->name('contracts.sign');
    Route::get('contracts/by-client/{client}', [ContractController::class, 'getByClient'])->name('contracts.by-client');
    Route::resource('contracts', ContractController::class);

    // Contract Templates
    Route::post('contract-templates/{contractTemplate}/toggle-status', [ContractTemplateController::class, 'toggleStatus'])->name('contract-templates.toggle-status');
    Route::get('contract-templates/{contractTemplate}/content', [ContractTemplateController::class, 'getTemplateContent'])->name('contract-templates.content');
    Route::resource('contract-templates', ContractTemplateController::class);
});

/*
|--------------------------------------------------------------------------
| Auth scaffolding (Breeze / Jetstream / etc)
|--------------------------------------------------------------------------


// setting section router



// */


//Company settings page .

// Show Company Settings page
Route::get('/settings/company', [CompanySettingsController::class, 'index'])
    ->name('settings.company');

// Store / Update Company Settings
Route::post('/settings/company', [CompanySettingsController::class, 'store'])
    ->name('settings.company.store');

// Delete Company Settings
Route::delete('/settings/company', [CompanySettingsController::class, 'destroy'])
    ->name('settings.company.destroy');



  // Business Address Routes
Route::get('/admin/settings/business-address', [BusinessAddressController::class, 'index'])
    ->name('admin.settings.business-address.index');

Route::get('/admin/settings/business-address/create', [BusinessAddressController::class, 'create'])
    ->name('admin.settings.business-address.create');

Route::post('/admin/settings/business-address/store', [BusinessAddressController::class, 'store'])
    ->name('admin.settings.business-address.store');

Route::get('/admin/settings/business-address/{businessAddress}/edit', [BusinessAddressController::class, 'edit'])
    ->name('admin.settings.business-address.edit');

Route::put('/admin/settings/business-address/{businessAddress}', [BusinessAddressController::class, 'update'])
    ->name('admin.settings.business-address.update');

Route::delete('/admin/settings/business-address/{businessAddress}', [BusinessAddressController::class, 'destroy'])
    ->name('admin.settings.business-address.destroy');

Route::put('/admin/settings/business-address/{businessAddress}/make-default', [BusinessAddressController::class, 'makeDefault'])
    ->name('admin.settings.business-address.make-default');

/*
|--------------------------------------------------------------------------
| Admin App Settings Routes
|--------------------------------------------------------------------------
| Laravel 11 compatible
| No middleware, no prefix
| Admin check handled in controller constructor
*/



// App Settings Routes

// Route::get('/admin/settings', [AppSettingController::class, 'index'])->name('admin.settings');
// Route::post('/admin/settings/update', [AppSettingController::class, 'update'])->name('admin.settings.app.update');
// Route::post('/admin/settings/app/add-field', [AppSettingController::class, 'addField'])->name('admin.settings.app.add-field');



// <?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Admin\Settings\AppSettingController;

// 4 Separate Pages for Settings

// routes/web.php
Route::get('/admin/settings/app', [AppSettingController::class, 'appSettings'])->name('admin.settings.app');

Route::get('/admin/settings/app/client-signup', [AppSettingController::class, 'clientSignupSettings'])->name('admin.settings.app.client-signup');

Route::get('/admin/settings/app/file-upload', [AppSettingController::class, 'fileUploadSettings'])->name('admin.settings.app.file-upload');

Route::get('/admin/settings/app/google-map', [AppSettingController::class, 'googleMapSettings'])->name('admin.settings.app.google-map');

// Common routes for all pages
Route::post('/admin/settings/app/update', [AppSettingController::class, 'update'])->name('admin.settings.app.update');
Route::post('/admin/settings/app/add-field', [AppSettingController::class, 'addField'])->name('admin.settings.app.add-field');
    //profile setting

Route::get('/admin/settings/terms-policy', [TermsPolicyController::class, 'index'])
    ->name('admin.settings.terms-policy');

Route::put('/admin/settings/terms-policy', [TermsPolicyController::class, 'update'])
    ->name('admin.settings.terms-policy.update');

/*
|--------------------------------------------------------------------------
| Profile Settings (Manual Routes)
|--------------------------------------------------------------------------
*/

Route::get('/admin/settings/profile', [ProfileSettingController::class, 'index'])
    ->name('admin.settings.profile');

Route::post('/admin/settings/profile/store', [ProfileSettingController::class, 'store'])
    ->name('admin.settings.profile.store');

Route::post('/admin/settings/profile/update', [ProfileSettingController::class, 'update'])
    ->name('admin.settings.profile.update');



require __DIR__.'/auth.php';
