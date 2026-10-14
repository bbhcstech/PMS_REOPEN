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
        // 1. Super Admin and Developer management routes are bypassed
        if (
            $request->is('super-admin*') ||
            $request->is('superadmin*') ||
            ($request->is('developer*') && !Auth::user()?->company_id && !session('current_company_id'))
        ) {
            return $next($request);
        }

        // 2. Resolve Central Company for current request/session
        $company = null;

        // Check impersonated tenant session first
        if (session('current_company_id')) {
            $company = Company::on('central')->find(session('current_company_id')) ?? \App\Models\Company::find(session('current_company_id'));
        }

        // Check authenticated user's company
        $user = Auth::user();
        if (!$company && $user && !empty($user->company_id)) {
            $company = Company::on('central')->find($user->company_id) ?? \App\Models\Company::find($user->company_id);
        }

        // Check company context
        if (!$company && app()->bound(CompanyContext::class)) {
            $ctxComp = app(CompanyContext::class)->current();
            if ($ctxComp) {
                $company = Company::on('central')->find($ctxComp->id) ?? $ctxComp;
            }
        }

        // Check tenant database session
        if (!$company && session('current_company_db')) {
            try {
                $company = Company::on('central')->where('db_name', session('current_company_db'))->first();
            } catch (\Throwable $e) {}
        }

        if ($company) {
            try {
                /** @var SubscriptionService $subService */
                $subService = app(SubscriptionService::class);

                // Synchronize and persist status at database level
                $subService->syncCompanyStatus($company);

                // Refresh central company model to get fresh status
                $centralComp = Company::on('central')->find($company->id) ?? $company;

                $isManualSuspension = (bool) ($centralComp->manually_suspended ?? false);
                $isSuspended = strtolower((string)($centralComp->status ?? '')) === 'suspended' || $isManualSuspension;
                $isExpired = strtolower((string)($centralComp->status ?? '')) === 'expired' || $subService->isExpired($centralComp);

                if ($isSuspended || $isExpired) {
                    $routeName = (string) $request->route()?->getName();

                    // Base routes always allowed when restricted:
                    $isBaseAllowed = (
                        $routeName === 'subscription.suspended' ||
                        $routeName === 'logout' ||
                        $routeName === 'login' ||
                        $routeName === 'super-admin.leave-impersonation' ||
                        $routeName === 'superadmin.leave-impersonation' ||
                        $request->is('super-admin/leave-impersonation*') ||
                        $request->is('superadmin/leave-impersonation*')
                    );

                    $isAllowedWhenRestricted = $isBaseAllowed;

                    if (!$isAllowedWhenRestricted) {
                        if ($request->expectsJson() || $request->is('api/*')) {
                            return response()->json([
                                'error'               => $isManualSuspension
                                    ? 'Your organization has been suspended by the platform administrator. Please contact support.'
                                    : 'Your subscription has expired. Contact Super Admin to extend your plan and restore access.',
                                'subscription_status' => $isManualSuspension ? 'suspended' : 'expired',
                                'manually_suspended'  => $isManualSuspension,
                                'restriction_url'     => route('subscription.suspended'),
                                'company'             => $centralComp->name ?? 'Organization',
                            ], 402);
                        }

                        return redirect()->route('subscription.suspended');
                    }
                }
            } catch (\Throwable $e) {
                report($e);
                return response('Company subscription could not be verified. Please try again shortly.', 503);
            }
        }

        return $next($request);
    }
}
