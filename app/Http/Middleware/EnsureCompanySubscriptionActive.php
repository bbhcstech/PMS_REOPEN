<?php

namespace App\Http\Middleware;

use App\Models\Central\Company;
use App\Services\CompanyContext;
use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Platform Super Admin bypasses subscription checks
        if (Auth::guard('super_admin')->check()) {
            return $next($request);
        }

        // Resolve Central Company for current request/user
        $company = null;
        $user = Auth::user();
        if ($user && !empty($user->company_id)) {
            $company = Company::on('central')->find($user->company_id) ?? \App\Models\Company::find($user->company_id);
        }

        if (!$company && session('current_company_id')) {
            $company = Company::on('central')->find(session('current_company_id')) ?? \App\Models\Company::find(session('current_company_id'));
        }

        if (!$company) {
            $ctxComp = app(CompanyContext::class)->current();
            if ($ctxComp) {
                $company = Company::on('central')->find($ctxComp->id) ?? $ctxComp;
            }
        }

        if ($company) {
            /** @var SubscriptionService $subService */
            $subService = app(SubscriptionService::class);

            // Execute real-time dynamic expiration check
            $subService->checkRealtimeExpiration($company);

            // Refresh central company model to get fresh status
            $centralComp = Company::on('central')->find($company->id) ?? $company;

            $isSuspended = strtolower((string)($centralComp->status ?? '')) === 'suspended'
                || $subService->isSuspended($centralComp)
                || $subService->isExpired($centralComp);

            if ($isSuspended) {
                $routeName = (string) $request->route()?->getName();

                // If company subscription is finished/suspended, strictly allow ONLY:
                // 1. Notification section routes
                // 2. Subscription suspended & renewal/assignment routes
                // 3. Auth logout/login routes
                $isAllowedWhenSuspended = (
                    $routeName === 'subscription.suspended' ||
                    $routeName === 'logout' ||
                    $routeName === 'login' ||
                    str_starts_with($routeName, 'notifications.') ||
                    str_starts_with($routeName, 'admin.company-notifications.') ||
                    str_starts_with($routeName, 'super-admin.subscriptions.') ||
                    str_starts_with($routeName, 'superadmin.subscriptions.') ||
                    str_starts_with($routeName, 'subscriptions.')
                );

                if (!$isAllowedWhenSuspended) {
                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json([
                            'error'               => 'Your subscription has expired and your organization access is restricted until Super Admin extends your subscription.',
                            'subscription_status' => 'suspended',
                            'company'             => $centralComp->name ?? 'Organization',
                        ], 402);
                    }

                    return redirect()->route('subscription.suspended');
                }
            }
        }

        return $next($request);
    }
}
