<?php

namespace App\Http\Middleware;

use App\Services\CompanyMailSettings;
use Closure;
use Illuminate\Http\Request;

class ConfigureCompanyMail
{
    public function handle(Request $request, Closure $next)
    {
        app(CompanyMailSettings::class)->apply();
        return $next($request);
    }
}
