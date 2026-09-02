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
        // Bypass tenant DB switching for SuperAdmin routes and authenticated SuperAdmin users
        if ($request->is('super-admin*') || \Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
            return $next($request);
        }

        $defaultDb = config('database.connections.tenant.database') ?: config('database.connections.mysql.database');
        $tenantDb = session('current_company_db');

        $user = auth()->user();
        if ($user) {
            $company = null;
            if (!empty($user->company_id)) {
                try {
                    $company = \App\Models\Central\Company::on('central')->find($user->company_id);
                } catch (\Throwable $e) {}
            }
            if (!$company && !empty($user->email)) {
                try {
                    $company = \App\Models\Central\Company::on('central')->where('email', $user->email)->first();
                } catch (\Throwable $e) {}
            }

            if ($company && !empty($company->db_name)) {
                $tenantDb = $company->db_name;
                session([
                    'current_company_db'   => $tenantDb,
                    'current_company_id'   => $company->id,
                    'current_company_name' => $company->name,
                ]);
            }
        }

        if (! $tenantDb) {
            $tenantDb = $defaultDb;
        }

        if ($tenantDb) {
            try {
                config([
                    'database.connections.tenant.database' => $tenantDb,
                    'database.connections.mysql.database'  => $tenantDb,
                ]);
                DB::purge('tenant');
                DB::purge('mysql');
                // Test PDO connection
                DB::connection('tenant')->getPdo();
            } catch (\Throwable $e) {
                // If tenant DB cannot be connected to, fall back safely to default database
                config([
                    'database.connections.tenant.database' => $defaultDb,
                    'database.connections.mysql.database'  => $defaultDb,
                ]);
                DB::purge('tenant');
                DB::purge('mysql');
            }
        }

        if (app()->bound(\App\Services\CompanyContext::class)) {
            app(\App\Services\CompanyContext::class)->reset();
        }

        return $next($request);
    }
}
