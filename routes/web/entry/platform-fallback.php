<?php

use Illuminate\Support\Facades\Route;

Route::fallback(function (\Illuminate\Http\Request $request) {
    $path = trim(strtolower($request->path()), '/');

    // 1. SuperAdmin Profile fallback
    if (in_array($path, ['superadmin/profile', 'super-admin/profile'], true)) {
        if (!\Illuminate\Support\Facades\Auth::guard('super_admin')->check() && !auth()->check()) {
            return redirect()->route('login');
        }
        $controller = app(\App\Http\Controllers\SuperAdminController::class);
        return $request->isMethod('POST')
            ? $controller->updateProfile($request)
            : $controller->profile($request);
    }

    // 2. SuperAdmin Companies fallback
    if (in_array($path, ['superadmin/companies', 'super-admin/companies', 'superadmin/companies/directory'], true)) {
        if (!\Illuminate\Support\Facades\Auth::guard('super_admin')->check() && !auth()->check()) {
            return redirect()->route('login');
        }
        return app(\App\Http\Controllers\SuperAdmin\CompanyController::class)->index($request);
    }

    // 2b. SuperAdmin Company Metrics fallback
    if (in_array($path, ['superadmin/companies/metrics', 'super-admin/companies/metrics'], true)) {
        if (!\Illuminate\Support\Facades\Auth::guard('super_admin')->check() && !auth()->check()) {
            return redirect()->route('login');
        }
        return app(\App\Http\Controllers\SuperAdmin\CompanyController::class)->metrics($request);
    }

    // 2c. SuperAdmin Provision Company fallback
    if (in_array($path, ['superadmin/companies/create', 'super-admin/companies/create'], true)) {
        if (!\Illuminate\Support\Facades\Auth::guard('super_admin')->check() && !auth()->check()) {
            return redirect()->route('login');
        }
        return app(\App\Http\Controllers\SuperAdmin\CompanyController::class)->create();
    }

    // 2d. SuperAdmin Edit Company fallback
    if (preg_match('#^(superadmin|super-admin)/companies/([0-9]+)/edit$#', $path, $matches)) {
        if (!\Illuminate\Support\Facades\Auth::guard('super_admin')->check() && !auth()->check()) {
            return redirect()->route('login');
        }
        return app(\App\Http\Controllers\SuperAdmin\CompanyController::class)->edit($matches[2]);
    }

    // 3. SuperAdmin Dashboard fallback
    if (in_array($path, ['superadmin', 'super-admin'], true)) {
        if (!\Illuminate\Support\Facades\Auth::guard('super_admin')->check() && !auth()->check()) {
            return redirect()->route('login');
        }
        return app(\App\Http\Controllers\SuperAdminController::class)->dashboard($request);
    }

    abort(404);
});
