<?php

namespace App\Providers;

use App\Services\CompanyContext;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Support/helpers.php');

        $this->app->scoped(CompanyContext::class, fn () => new CompanyContext());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('*', function ($view) {
            $view->with('currentCompany', app(CompanyContext::class)->current());
        });
    }
}
