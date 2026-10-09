<?php

namespace App\Services;

use App\Models\Central\{Company, SuperAdmin};
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Schema};
use Illuminate\Validation\ValidationException;

class SecureCompanyLogin
{
    public static function passwordMatches(string $input, ?string $stored): bool
    {
        if (!$stored) return false;
        if (!password_get_info($stored)['algo']) return hash_equals($stored, $input);
        try { return Hash::check($input, $stored); } catch (\Throwable $e) { return false; }
    }

    public function authenticate(Request $request): void
    {
        $login = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');
        $primary = config('database.connections.session_db.database') ?: config('database.connections.mysql.database');
        // Discard stale identities before changing a connection with overlapping IDs.
        foreach (['web', 'super_admin'] as $guard) {
            $request->session()->forget(Auth::guard($guard)->getName());
            \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget(Auth::guard($guard)->getRecallerName()));
            Auth::guard($guard)->forgetUser();
        }
        $request->session()->forget(['current_company_id', 'current_company_db', 'current_company_name']);
        app(CompanyContext::class)->reset();
        $this->useDatabase($primary);

        $explicitCompany = $request->filled('company_id') || $request->filled('company_code');
        if (!$explicitCompany && Schema::connection('central')->hasTable('super_admins')) {
            $platform = SuperAdmin::where('email', $login)->first();
            if ($platform) {
                if (!self::passwordMatches($password, $platform->password) || $platform->is_active === false || $platform->is_active === 0) $this->invalid();
                Auth::guard('super_admin')->login($platform, $request->boolean('remember'));
                return;
            }
        }

        if (app(RevokedCompanyCredentials::class)->rejects($login, $password)) $this->invalid();
        $companies = Company::query()->whereNotNull('db_name');
        if ($request->filled('company_id')) $companies->whereKey((int) $request->input('company_id'));
        if ($request->filled('company_code')) $companies->where('company_code', $request->input('company_code'));
        $host = strtolower($request->getHost());
        $hostCompany = Company::where(function ($q) use ($host) { $q->where('domain', $host)->orWhere('subdomain', $host); })->first();
        if ($hostCompany) {
            if ($explicitCompany && (($request->filled('company_id') && (int) $request->input('company_id') !== $hostCompany->id)
                || ($request->filled('company_code') && strcasecmp($request->input('company_code'), $hostCompany->company_code) !== 0))) $this->invalid();
            $companies->whereKey($hostCompany->id);
            $explicitCompany = true;
        }
        $alias = Company::withTrashed()->where(function ($q) use ($login) {
            $q->where('email', $login)->orWhere('company_code', strtoupper($login))->orWhere('domain', $login)->orWhere('subdomain', $login);
        })->first();
        if (!$explicitCompany && $alias) {
            if ($alias->trashed()) $this->invalid();
            $companies->whereKey($alias->id);
        }

        $matches = [];
        foreach ($companies->cursor() as $company) {
            try {
                $this->useDatabase($company->db_name);
                DB::connection('tenant')->getPdo();
                $query = User::where('company_id', $company->id)->where(function ($q) use ($login, $alias, $company) {
                    $q->where('email', $login);
                    if (Schema::connection('tenant')->hasColumn('users', 'personal_email')) $q->orWhere('personal_email', $login);
                    if ($alias && $alias->id === $company->id) $q->orWhere('email', strtolower($company->email));
                });
                foreach ($query->get() as $candidate) {
                    if (self::passwordMatches($password, $candidate->password)) $matches[] = [$company, $candidate];
                }
            } catch (\Throwable $e) {
                // Never fall back to another database after selecting a workspace.
                if ($explicitCompany || $alias) $this->invalid();
            }
        }
        if (count($matches) !== 1) {
            $this->useDatabase($primary);
            // Legacy platform accounts stay on the primary DB and have no company.
            if (!$explicitCompany && !$alias && count($matches) === 0 && Schema::connection('tenant')->hasTable('users')) {
                $platform = User::whereNull('company_id')->where('email', $login)->whereIn('role', ['superadmin', 'super-admin', 'super_admin', 'developer', 'dev'])->first();
                if ($platform && self::passwordMatches($password, $platform->password) && $this->enabled($platform)) {
                    Auth::guard('web')->login($platform, $request->boolean('remember'));
                    return;
                }
            }
            $this->invalid();
        }
        [$company, $candidate] = $matches[0];
        $this->useDatabase($company->db_name);
        DB::connection('central')->transaction(function () use ($company, $candidate, $request, $password) {
            $current = Company::whereKey($company->id)->lockForUpdate()->first();
            if (!$current || $current->db_name !== $company->db_name) $this->invalid();
            $user = User::where('company_id', $current->id)->find($candidate->id);
            if (!$user || !self::passwordMatches($password, $user->password) || !$this->enabled($user)) $this->invalid();
            $request->session()->put(['current_company_id' => $current->id, 'current_company_db' => $current->db_name, 'current_company_name' => $current->name]);
            app(CompanyContext::class)->reset($current);
            if ($user->company_staff_role_id) {
                app(CompanyStaffLogin::class)->authenticate($user, $password, $request->boolean('remember'));
            } else {
                // Upgrade only an exactly verified legacy password; never heal from raw_password.
                if (!password_get_info($user->password)['algo']) { $user->password = Hash::make($password); $user->save(); }
                Auth::guard('web')->login($user, $request->boolean('remember'));
            }
        });
    }

    private function enabled(User $user): bool
    {
        return $user->is_active !== false && $user->is_active !== 0 && $user->is_active !== '0'
            && $user->login_allowed !== false && $user->login_allowed !== 0 && $user->login_allowed !== '0'
            && !$user->archived_at && $user->canLogin();
    }

    private function useDatabase(string $database): void
    {
        config(['database.connections.tenant.database' => $database, 'database.connections.mysql.database' => $database, 'database.connections.mysql.url' => null]);
        DB::purge('tenant'); DB::purge('mysql');
    }

    private function invalid(): never
    {
        session()->forget(['current_company_id', 'current_company_db', 'current_company_name']);
        throw ValidationException::withMessages(['email' => trans('auth.failed')]);
    }
}
