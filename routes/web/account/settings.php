<?php

use App\Http\Controllers\Admin\Settings\AppSettingController;
use App\Http\Controllers\Admin\Settings\DocumentSettingsController;
use App\Http\Controllers\Admin\Settings\EmailSettingsController;
use App\Http\Controllers\Admin\Settings\EmployeeIdSettingsController;
use App\Http\Controllers\Admin\Settings\LeaveSettingsController;
use App\Http\Controllers\Admin\Settings\NotificationSettingsController;
use App\Http\Controllers\Admin\Settings\OrganizationDetailsController;
use App\Http\Controllers\Admin\Settings\PerformanceSettingsController;
use App\Http\Controllers\Admin\Settings\ProfileSettingController;
use App\Http\Controllers\Admin\Settings\RecruitmentSettingsController;
use App\Http\Controllers\Admin\Settings\SettingController;
use App\Http\Controllers\Admin\Settings\TermsPolicyController;
use App\Http\Controllers\Admin\Settings\WorkScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/settings/app', [AppSettingController::class, 'appSettings'])->name('admin.settings.app');
Route::post('/admin/settings/app/dashboard', [AppSettingController::class, 'updateDashboard'])->name('admin.settings.app.dashboard');
Route::get('/admin/settings/app/client-signup', [AppSettingController::class, 'clientSignupSettings'])->name('admin.settings.app.client-signup');
Route::get('/admin/settings/app/file-upload', [AppSettingController::class, 'fileUploadSettings'])->name('admin.settings.app.file-upload');
Route::get('/admin/settings/app/google-map', [AppSettingController::class, 'googleMapSettings'])->name('admin.settings.app.google-map');
Route::post('/admin/settings/app/update', [AppSettingController::class, 'update'])->name('admin.settings.app.update');
Route::post('/admin/settings/app/add-field', [AppSettingController::class, 'addField'])->name('admin.settings.app.add-field');

Route::get('/admin/settings/terms-policy', [TermsPolicyController::class, 'index'])
    ->name('admin.settings.terms-policy');
Route::put('/admin/settings/terms-policy', [TermsPolicyController::class, 'update'])
    ->name('admin.settings.terms-policy.update');

Route::get('/admin/settings/profile', [ProfileSettingController::class, 'index'])
    ->name('admin.settings.profile');
Route::post('/admin/settings/profile/store', [ProfileSettingController::class, 'store'])
    ->name('admin.settings.profile.store');
Route::post('/admin/settings/profile/update', [ProfileSettingController::class, 'update'])
    ->name('admin.settings.profile.update');

// Admin Settings Dashboard & 20 Modules
Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');

require dirname(__DIR__, 2).'/workforce-settings.php';

Route::get('/admin/settings/organization-details', [OrganizationDetailsController::class, 'index'])->name('admin.settings.organization-details');
Route::post('/admin/settings/organization-details', [OrganizationDetailsController::class, 'update'])->name('admin.settings.organization-details.update');

Route::get('/admin/settings/employee-id', [EmployeeIdSettingsController::class, 'index'])->name('admin.settings.employee-id');
Route::post('/admin/settings/employee-id', [EmployeeIdSettingsController::class, 'update'])->name('admin.settings.employee-id.update');

Route::get('/admin/settings/work-schedule', [WorkScheduleController::class, 'index'])->name('admin.settings.work-schedule');
Route::post('/admin/settings/work-schedule', [WorkScheduleController::class, 'update'])->name('admin.settings.work-schedule.update');
Route::post('/admin/settings/work-schedule/special-day', [WorkScheduleController::class, 'addSpecialDay'])->name('admin.settings.work-schedule.special-day.store');
Route::delete('/admin/settings/work-schedule/special-day/{id}', [WorkScheduleController::class, 'deleteSpecialDay'])->name('admin.settings.work-schedule.special-day.destroy');
Route::post('/admin/settings/work-schedule/employee-mode', [WorkScheduleController::class, 'updateEmployeeMode'])->name('admin.settings.work-schedule.employee-mode.update');
Route::post('/admin/settings/work-schedule/employee-modes-bulk', [WorkScheduleController::class, 'bulkUpdateEmployeeModes'])->name('admin.settings.work-schedule.employee-modes-bulk.update');
Route::post('/admin/settings/work-schedule/employee-wfh-date', [WorkScheduleController::class, 'addEmployeeWfhDate'])->name('admin.settings.work-schedule.employee-wfh-date.store');
Route::delete('/admin/settings/work-schedule/employee-wfh-date/{id}', [WorkScheduleController::class, 'deleteEmployeeWfhDate'])->name('admin.settings.work-schedule.employee-wfh-date.destroy');

Route::get('/admin/settings/leave', [LeaveSettingsController::class, 'index'])->name('admin.settings.leave');
Route::post('/admin/settings/leave', [LeaveSettingsController::class, 'update'])->name('admin.settings.leave.update');

Route::get('/admin/settings/recruitment', [RecruitmentSettingsController::class, 'index'])->name('admin.settings.recruitment');
Route::post('/admin/settings/recruitment', [RecruitmentSettingsController::class, 'update'])->name('admin.settings.recruitment.update');

Route::get('/admin/settings/performance', [PerformanceSettingsController::class, 'index'])->name('admin.settings.performance');
Route::post('/admin/settings/performance', [PerformanceSettingsController::class, 'update'])->name('admin.settings.performance.update');

Route::get('/admin/settings/notification', [NotificationSettingsController::class, 'index'])->name('admin.settings.notification');
Route::post('/admin/settings/notification', [NotificationSettingsController::class, 'update'])->name('admin.settings.notification.update');

Route::get('/admin/settings/email', [EmailSettingsController::class, 'index'])->name('admin.settings.email');
Route::post('/admin/settings/email', [EmailSettingsController::class, 'update'])->name('admin.settings.email.update');
Route::post('/admin/settings/email/test', [EmailSettingsController::class, 'testEmail'])->name('admin.settings.email.test');

Route::get('/admin/settings/document', [DocumentSettingsController::class, 'index'])->name('admin.settings.document');
Route::post('/admin/settings/document', [DocumentSettingsController::class, 'update'])->name('admin.settings.document.update');
Route::post('/admin/settings/document/type/store', [DocumentSettingsController::class, 'addDocumentType'])->name('admin.settings.document.type.store');
Route::post('/admin/settings/document/type/delete', [DocumentSettingsController::class, 'deleteDocumentType'])->name('admin.settings.document.type.delete');
