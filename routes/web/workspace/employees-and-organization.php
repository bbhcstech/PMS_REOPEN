<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ParentDepartmentController;
use Illuminate\Support\Facades\Route;

Route::put('designations/level-settings', [DesignationController::class, 'updateLevelSettings'])
    ->name('designations.level-settings');

Route::get('designations/archive', [DesignationController::class, 'archive'])
    ->name('designations.archive');
Route::post('designations/bulk-archive', [DesignationController::class, 'bulkArchive'])
    ->name('designations.bulk-archive');
Route::post('designations/{designation}/archive', [DesignationController::class, 'archiveDesignation'])
    ->name('designations.archive.action');
Route::post('designations/{id}/restore', [DesignationController::class, 'restore'])
    ->name('designations.restore');
Route::delete('designations/{id}/permanent', [DesignationController::class, 'deleteArchived'])
    ->name('designations.delete-archived');

// Bulk delete designations
Route::post('designations/bulk-delete', [DesignationController::class, 'bulkDelete'])
    ->name('designations.bulk-delete');

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
Route::delete('parent-departments/{id}/permanent', [ParentDepartmentController::class, 'deletePermanently'])
    ->name('parent-departments.delete-permanent');
Route::resource('parent-departments', ParentDepartmentController::class);

// Departments
Route::get('departments/archive', [DepartmentController::class, 'archive'])
    ->name('departments.archive');
Route::post('departments/{id}/restore', [DepartmentController::class, 'restore'])
    ->name('departments.restore');
Route::delete('departments/{id}/permanent', [DepartmentController::class, 'deletePermanently'])
    ->name('departments.delete-permanent');
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
Route::post('employees/{id}/inactive', [EmployeeController::class, 'markInactive'])
    ->name('employees.inactive.update');
Route::delete('employees/{id}/permanent', [EmployeeController::class, 'deletePermanently'])
    ->name('employees.delete-permanent');
Route::post('employees/{id}/restore', [EmployeeController::class, 'restore'])
    ->name('employees.restore');

// Employee Self-Onboarding Applications & HR Review Workflow
Route::prefix('employees/onboarding')->name('employees.onboarding.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\EmployeeOnboardingController::class, 'index'])->name('index');
    Route::get('/{application}/review', [\App\Http\Controllers\Admin\EmployeeOnboardingController::class, 'review'])
        ->whereNumber('application')->name('review');
    Route::post('/{application}/approve', [\App\Http\Controllers\Admin\EmployeeOnboardingController::class, 'approve'])
        ->whereNumber('application')->name('approve');
    Route::post('/{application}/request-correction', [\App\Http\Controllers\Admin\EmployeeOnboardingController::class, 'requestCorrection'])
        ->whereNumber('application')->name('request-correction');
    Route::post('/{application}/reject', [\App\Http\Controllers\Admin\EmployeeOnboardingController::class, 'reject'])
        ->whereNumber('application')->name('reject');
    Route::post('/{application}/resend-activation', [\App\Http\Controllers\Admin\EmployeeOnboardingController::class, 'resendActivation'])
        ->whereNumber('application')->name('resend-activation');
    Route::get('/{application}/documents/{type}', [\App\Http\Controllers\Admin\EmployeeOnboardingController::class, 'previewDocument'])
        ->whereNumber('application')->whereIn('type', ['profile_picture', 'cv_file', 'government_id_card'])->name('document');
});

Route::prefix('employees/invitations')->name('employees.invitations.')->group(function () {
    Route::post('/{invitation}/resend', [\App\Http\Controllers\Admin\EmployeeOnboardingController::class, 'resendInvitation'])
        ->whereNumber('invitation')->name('resend');
    Route::post('/{invitation}/regenerate', [\App\Http\Controllers\Admin\EmployeeOnboardingController::class, 'regenerateLink'])
        ->whereNumber('invitation')->name('regenerate');
    Route::post('/{invitation}/revoke', [\App\Http\Controllers\Admin\EmployeeOnboardingController::class, 'revokeInvitation'])
        ->whereNumber('invitation')->name('revoke');
    Route::get('/{invitation}/copy-link', [\App\Http\Controllers\Admin\EmployeeOnboardingController::class, 'copyLink'])
        ->whereNumber('invitation')->name('copy-link');
});

Route::get('employees/org-tree', [EmployeeController::class, 'orgTree'])
    ->name('employees.org-tree');
Route::get('employees/org-tree/{employee}/profile', [EmployeeController::class, 'orgTreeProfile'])
    ->whereNumber('employee')->name('employees.org-tree.profile');

Route::resource('employees', EmployeeController::class);


    Route::post('designations/check-exists', [DesignationController::class, 'checkExists'])->name('designations.check-exists');

    // For AJAX updates (used in edit.blade.php)
    Route::post('/designations/{designation}/ajax-update', [DesignationController::class, 'update'])->name('designations.ajax.update');




// Employee invites
// Employee invite acceptance is public (the invitee is not signed in): see the signed routes below.
Route::post('employees/send-invite', [EmployeeController::class, 'sendInvite'])
    ->name('employees.sendInvite');
Route::post('employees/invite-link', [EmployeeController::class, 'generateInviteLink'])
    ->name('employees.inviteLink');
// add this
Route::post('employees/store-department', [\App\Http\Controllers\EmployeeController::class, 'storeDepartment'])
->name('employees.storeDepartment');

//employee email and mobile validation routes inside admin group

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'role:admin,hr,manager']], function() {
// ... other routes ...

Route::post('/employees/check-email', [EmployeeController::class, 'checkEmail'])->name('employees.check-email');
Route::post('/employees/check-mobile', [EmployeeController::class, 'checkMobile'])->name('employees.check-mobile');

// ... other routes ...


    Route::post('/designations/store-ajax', [EmployeeController::class, 'storeDesignation'])->name('designations.store.ajax');
    Route::post('/parent-departments/quick-create', [EmployeeController::class, 'storeParentDepartment'])->name('parent-departments.quick-create');
    Route::post('/departments/store-ajax', [EmployeeController::class, 'storeSubDepartment'])->name('departments.store.ajax');
    Route::post('/countries/quick-create', [EmployeeController::class, 'storeCountry'])->name('countries.quick-create');


});
