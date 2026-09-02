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
        $middleware->web(append: [
            \App\Http\Middleware\EnsureCompanySubscriptionActive::class,
        ]);
        $middleware->web(prepend: [
            SetTenantConnection::class,
        ]);
        $middleware->alias([
            'tenant' => SetTenantConnection::class,
            'module.access' => EnsureModuleAccess::class,
            'role' => RoleMiddleware::class,
            'feature' => \App\Http\Middleware\CheckFeatureAccess::class,
            'developer.access' => \App\Http\Middleware\EnsureDeveloperAccess::class,
            'subscription.active' => \App\Http\Middleware\EnsureCompanySubscriptionActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 419) {
                return redirect()->back()->with('error', 'Your session expired. Please refresh and try again.');
            }
        });
    })->create();
