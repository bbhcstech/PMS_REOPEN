<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetTenantConnection
{
    /**
     * Set the active tenant database connection dynamically for the incoming request.
     *
     * Reads 'current_company_db' from session.
     * If not set, defaults to env('DB_DATABASE', 'pms_last'), ensuring existing
     * logged-in users and public routes continue seamlessly.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantDb = session('current_company_db') ?: env('DB_DATABASE', 'pms_last');

        config(['database.connections.tenant.database' => $tenantDb]);
        DB::purge('tenant');

        return $next($request);
    }
}
