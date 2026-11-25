<?php

use App\Http\Controllers\Admin\CompanyManagementController;
use App\Http\Controllers\ClientCategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientSubCategoryController;
use App\Http\Controllers\CollaboratingCompanyController;
use Illuminate\Support\Facades\Route;

Route::post('clients/bulk-delete', [ClientController::class, 'bulkDelete'])->name('clients.bulk-delete');
Route::post('clients/bulk-action', [ClientController::class, 'bulkAction'])->name('clients.bulkAction');

Route::get('/clients/pending', [ClientController::class, 'pending'])->name('clients.pending');
Route::post('/clients/pending/bulk-action', [ClientController::class, 'pendingBulkAction'])->name('clients.pendingbulkAction');

Route::get('/clients/lookup-pincode', [ClientController::class, 'lookupPincode'])->name('clients.lookup-pincode');
Route::resource('clients', ClientController::class);
Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
Route::resource('collaborating-companies', CollaboratingCompanyController::class);
Route::resource('admin/companies', CompanyManagementController::class)->names('admin.companies');
Route::post('admin/companies/{company}/activate', [CompanyManagementController::class, 'activate'])->name('admin.companies.activate');
Route::post('admin/companies/{company}/deactivate', [CompanyManagementController::class, 'deactivate'])->name('admin.companies.deactivate');

// client categories
Route::resource('client-categories', ClientCategoryController::class)->only(['store', 'index']);
Route::resource('client-sub-categories', ClientSubCategoryController::class)->only(['store', 'index']);
