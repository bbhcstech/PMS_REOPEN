<?php

use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\CompanyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Routes (Central DB / Platform Management)
|--------------------------------------------------------------------------
|
| Guard: super_admin (App\Models\Central\SuperAdmin)
| Connection: central (pms_central)
|
*/

Route::prefix('super-admin')->name('super-admin.')->group(function () {

    // Guest routes
    Route::middleware('guest:super_admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Authenticated super admin routes
    Route::middleware('auth:super_admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Company management & impersonation
        Route::get('/', [CompanyController::class, 'index']);
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::post('/companies/{company}/enter', [CompanyController::class, 'enter'])->name('companies.enter');
        Route::post('/leave-impersonation', [CompanyController::class, 'leaveImpersonation'])->name('leave-impersonation');
    });
});
