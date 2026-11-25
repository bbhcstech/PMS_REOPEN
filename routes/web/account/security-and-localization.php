<?php

use App\Http\Controllers\Admin\Settings\LocalizationController;
use App\Http\Controllers\Admin\Settings\SecuritySettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/settings/security', [SecuritySettingsController::class, 'index'])->name('admin.settings.security');
Route::post('/admin/settings/security', [SecuritySettingsController::class, 'update'])->name('admin.settings.security.update');

Route::get('/admin/settings/change-password', [\App\Http\Controllers\Admin\Settings\ChangePasswordSettingsController::class, 'index'])->name('admin.settings.change-password');
Route::post('/admin/settings/change-password', [\App\Http\Controllers\Admin\Settings\ChangePasswordSettingsController::class, 'update'])->name('admin.settings.change-password.update');

Route::get('/admin/settings/localization', [LocalizationController::class, 'index'])->name('admin.settings.localization');
Route::post('/admin/settings/localization', [LocalizationController::class, 'update'])->name('admin.settings.localization.update');
