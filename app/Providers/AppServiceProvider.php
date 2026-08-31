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

        $this->app->scoped(CompanyContext::class, fn () => new CompanyContext());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        view()->composer('*', function ($view) {
            $user = auth()->user();
            $isAdmin = ($user && $user->role === 'admin');

            $view->with([
                'currentCompany' => app(CompanyContext::class)->current(),
                'isSettingsReadOnly' => ! $isAdmin,
                'isAdminUser' => $isAdmin,
            ]);
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
