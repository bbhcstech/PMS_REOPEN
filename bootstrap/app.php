<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureModuleAccess;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetTenantConnection;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/super-admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectTo(
            guests: '/login',
            users: function (\Illuminate\Http\Request $request) {
                $user = auth()->user();
                if ($user?->company_staff_role_id) return route('dashboard');
                if ($user && (
                    (method_exists($user, 'isDeveloper') && $user->isDeveloper()) ||
                    in_array(strtolower((string) ($user->role ?? '')), ['developer', 'dev'], true) ||
                    str_contains(strtolower((string) ($user->role ?? '')), 'developer') ||
                    str_contains(strtolower((string) ($user->designation ?? '')), 'developer') ||
                    str_contains(strtolower((string) ($user->designation ?? '')), 'engineer')
                )) {
                    return route('developer.dashboard');
                }
                if ($user && in_array(strtolower((string) ($user->role ?? '')), ['superadmin', 'super-admin', 'super_admin'], true)) {
                    return route('superadmin.dashboard');
                }
                return route('dashboard');
            }
        );
        $middleware->prependToPriorityList(
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            SetTenantConnection::class
        );
        $middleware->web(append: [
            SetTenantConnection::class,
            \App\Http\Middleware\EnsureCompanySubscriptionActive::class,
            \App\Http\Middleware\NormalizePhoneCountryCodes::class,
            \App\Http\Middleware\BlockTicketsInAdminWorkspace::class,
        ]);
        $middleware->alias([
            'admin' => RoleMiddleware::class,
            'tenant' => SetTenantConnection::class,
            'module.access' => EnsureModuleAccess::class,
            'role' => RoleMiddleware::class,
            'feature' => \App\Http\Middleware\CheckFeatureAccess::class,
            'developer.access' => \App\Http\Middleware\EnsureDeveloperAccess::class,
            'platform.superadmin' => \App\Http\Middleware\EnsurePlatformSuperAdmin::class,
            'subscription.active' => \App\Http\Middleware\EnsureCompanySubscriptionActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->is('logout') || $request->is('*/logout') || $request->routeIs('logout*')) {
                    \Illuminate\Support\Facades\Auth::guard('web')->logout();
                    if (\Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
                        \Illuminate\Support\Facades\Auth::guard('super_admin')->logout();
                    }
                    \Illuminate\Support\Facades\Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect('/login');
                }
                return redirect('/login')->with('error', 'Your session expired. Please log in again.');
            }
        });
    })->create();
