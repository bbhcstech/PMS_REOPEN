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

        $routeName = (string) $request->route()?->getName();
        $allowedRoutes = [
            'subscription.suspended',
            'super-admin.subscriptions.index',
            'super-admin.subscriptions.store',
            'superadmin.subscriptions.index',
            'subscriptions.index',
            'subscriptions.plans',
            'subscriptions.checkout',
            'subscriptions.store',
            'subscriptions.update',
            'subscriptions.toggle-override',
            'super-admin.subscriptions.toggle-override',
            'superadmin.subscriptions.toggle-override',
            'notifications.all',
            'notifications.index',
            'notifications.open',
            'notifications.read',
            'notifications.readAll',
            'notifications.unreadCount',
            'notifications.latest',
            'notifications.sidebar',
            'admin.company-notifications.index',
            'admin.company-notifications.read',
            'admin.company-notifications.read-all',
            'admin.company-notifications.unread-count',
            'admin.company-complaints.index',
            'admin.company-complaints.create',
            'admin.company-complaints.store',
            'admin.company-complaints.show',
            'admin.company-complaints.reply',
            'admin.company-complaints.reopen',
            'logout',
            'login',
            'profile.edit',
            'profile.update',
        ];

        if (
            in_array($routeName, $allowedRoutes, true) ||
            str_starts_with($routeName, 'notifications.') ||
            str_starts_with($routeName, 'admin.company-notifications.') ||
            str_starts_with($routeName, 'admin.company-complaints.') ||
            str_contains($routeName, 'subscription')
        ) {
            return $next($request);
        }

        $company = app(CompanyContext::class)->current();
        if (! $company && Auth::check() && Auth::user()?->company_id) {
            $company = Company::on('central')->find(Auth::user()->company_id) ?? \App\Models\Company::find(Auth::user()->company_id);
        }

        if ($company) {
            /** @var SubscriptionService $subService */
            $subService = app(SubscriptionService::class);

            // Execute real-time dynamic expiration check
            $subService->checkRealtimeExpiration($company);

            if ($subService->isSuspended($company)) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'error'               => 'Your subscription has expired and your organization has been suspended. Please select a paid plan to restore access.',
                        'subscription_status' => 'suspended',
                        'company'             => $company->name,
                    ], 402);
                }

                return redirect()->route('subscription.suspended');
            }
        }

        return $next($request);
    }
}
