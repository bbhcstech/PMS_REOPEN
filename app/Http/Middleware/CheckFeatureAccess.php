<?php

namespace App\Http\Middleware;

use App\Models\Central\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureAccess
{
    /**
     * Handle an incoming request to verify feature access for the active company.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $feature
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Platform Super Admin (central guard) bypasses feature checks
        if (\Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();
        if (in_array($routeName, ['home', 'login', 'logout'], true) || in_array($feature, ['home'], true)) {
            return $next($request);
        }

        $company = app(\App\Services\CompanyContext::class)->current();
        if (! $company && auth()->check() && auth()->user()?->company_id) {
            $company = Company::on('central')->find(auth()->user()->company_id) ?? \App\Models\Company::find(auth()->user()->company_id);
        }
        if (! $company) {
            $tenantDb = session('current_company_db') ?: env('DB_DATABASE', 'pms_last');
            $company = Company::on('central')->where('db_name', $tenantDb)->first();
        }

        if ($company && method_exists($company, 'hasFeature') && ! $company->hasFeature($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => "Feature '{$feature}' is not enabled for your company subscription plan.",
                ], 403);
            }

            if ($feature === 'dashboard' || $routeName === 'dashboard') {
                return redirect()->route('profile.edit')
                    ->with('error', "Access Denied: The 'Dashboard' module has been turned off by Super Admin for your company.");
            }

            return redirect()->route('dashboard')
                ->with('error', "Access Denied: The '{$feature}' module is not included in your subscription plan.");
        }

        return $next($request);
    }
}
