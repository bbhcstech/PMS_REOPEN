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
        $tenantDb = session('current_company_db') ?: env('DB_DATABASE', 'pms_last');

        $company = Company::on('central')->where('db_name', $tenantDb)->first();

        if ($company && ! $company->hasFeature($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => "Feature '{$feature}' is not enabled for your company subscription plan.",
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', "Access Denied: The '{$feature}' module is not included in your subscription plan.");
        }

        return $next($request);
    }
}
