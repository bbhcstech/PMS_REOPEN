<?php

use App\Http\Controllers\Admin\Settings\BusinessAddressController;
use App\Http\Controllers\Admin\Settings\CompanySettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/settings/company', [CompanySettingsController::class, 'index'])
    ->name('settings.company');

// Store / Update Company Settings
Route::post('/settings/company', [CompanySettingsController::class, 'store'])
    ->name('settings.company.store');

// Delete Company Settings
Route::delete('/settings/company', [CompanySettingsController::class, 'destroy'])
    ->name('settings.company.destroy');



  // Business Address Routes
Route::middleware(['auth', 'module.access'])->group(function () {
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
});
