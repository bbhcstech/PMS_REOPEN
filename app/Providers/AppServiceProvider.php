<?php

namespace App\Providers;

use App\Models\AppSetting;
use App\Services\CompanyContext;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Support/helpers.php');

        if (!class_exists('CountryPhone', false)) {
            class_alias(\App\Support\CountryPhone::class, 'CountryPhone');
        }
        if (!class_exists(\App\Http\Controllers\CountryPhone::class, false)) {
            class_alias(\App\Support\CountryPhone::class, \App\Http\Controllers\CountryPhone::class);
        }

        $this->app->scoped(CompanyContext::class, fn () => new CompanyContext());
        $this->app->afterResolving(\Illuminate\Cache\RateLimiter::class, function ($limiter) {
            $limiter->for('platform-ai', function (\Illuminate\Http\Request $request) {
                $central = \Illuminate\Support\Facades\Auth::guard('super_admin')->user();
                return \Illuminate\Cache\RateLimiting\Limit::perMinute(15)->by(
                    'platform-ai:' . ($central ? 'central:' . $central->id : 'web:' . $request->user()?->id)
                );
            });
            $limiter->for('company-ai', function (\Illuminate\Http\Request $request) {
                return \Illuminate\Cache\RateLimiting\Limit::perMinute(15)->by(
                    'company-ai:' . (int) $request->user()?->company_id . ':' . (int) $request->user()?->id
                );
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        try {
            $tz = AppSetting::valueFor('loc_timezone', config('app.timezone', 'Asia/Kolkata'));
            if ($tz) {
                config(['app.timezone' => $tz]);
                date_default_timezone_set($tz);
            }
        } catch (\Throwable $e) {}

        view()->composer('*', function ($view) {
            try {
                $user = auth()->user();
                $isAdmin = ($user && in_array(strtolower((string) ($user->role ?? '')), ['admin', 'administrator', 'superadmin'], true));

                $view->with([
                    'currentCompany' => app(CompanyContext::class)->current(),
                    'isSettingsReadOnly' => ! $isAdmin,
                    'isAdminUser' => $isAdmin,
                ]);
            } catch (\Throwable $e) {}
        });

        Password::defaults(function () {
            try {
                $min = (int) AppSetting::valueFor('sec_min_password_length', '8');
                $reqUpper = AppSetting::valueFor('sec_require_uppercase', '1') == '1';
                $reqLower = AppSetting::valueFor('sec_require_lowercase', '1') == '1';
                $reqNum = AppSetting::valueFor('sec_require_numbers', '1') == '1';
                $reqSpec = AppSetting::valueFor('sec_require_special_char', '1') == '1';

                $rule = Password::min($min);
                if ($reqUpper && $reqLower) {
                    $rule->mixedCase();
                } elseif ($reqUpper || $reqLower) {
                    $rule->letters();
                }
                if ($reqNum) {
                    $rule->numbers();
                }
                if ($reqSpec) {
                    $rule->symbols();
                }
                return $rule;
            } catch (\Throwable $e) {
                return Password::min(8);
            }
        });
    }
}
