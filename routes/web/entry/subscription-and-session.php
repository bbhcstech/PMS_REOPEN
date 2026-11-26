<?php

use Illuminate\Support\Facades\Route;

Route::get('/subscription/suspended', function (\Illuminate\Http\Request $request) {
    $company = app(\App\Services\CompanyContext::class)->current();
    if (! $company && auth()->check() && auth()->user()?->company_id) {
        $company = \App\Models\Central\Company::on('central')->find(auth()->user()->company_id);
    }
    abort_unless($company, 404);
    $company = \App\Models\Central\Company::on('central')->findOrFail($company->id);
    $status = app(\App\Services\SubscriptionService::class)->syncCompanyStatus($company);
    if ($request->expectsJson()) {
        return response()->json(['status' => $status])->header('Cache-Control', 'private, no-store');
    }
    if (in_array($status, ['active', 'trial'], true)) {
        return redirect()->route('dashboard')->with('success', 'Your company access has been restored. Welcome back.');
    }
    if ($status === 'expired') {
        $subscription = $company->subscriptions()->with('plan')->latest('id')->first();
        return view('subscription.expired', compact('company', 'subscription'));
    }
    return view('subscription.suspended', compact('company'));
})->middleware('auth:web,super_admin')->name('subscription.suspended');

Route::get('/company/session-status', function () {
    return response()->json(['company_id' => \App\Services\TenantScope::companyId()]);
})->middleware('auth:web,super_admin')->name('company.session-status');
