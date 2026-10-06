<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Carbon\Carbon;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'terms_accepted' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'terms_accepted.accepted' => 'Please accept the Terms & Conditions before logging in.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        session()->forget(['current_company_id', 'current_company_db', 'current_company_name']);

        $inputEmail = strtolower(trim($this->string('email')));
        $inputPassword = (string) $this->string('password');
        $trimmedInputPassword = trim($inputPassword);

        $primaryDb = trim((string) (config('database.connections.session_db.database') ?: config('database.connections.mysql.database', env('DB_DATABASE', 'thesmart_lara319'))));
        $defaultTenantDb = $primaryDb;

        // Reset tenant and mysql connections to primary platform database
        config([
            'database.connections.tenant.database' => $primaryDb,
            'database.connections.mysql.database'  => $primaryDb,
        ]);
        DB::purge('tenant');
        DB::purge('mysql');

        // Helper: Validate input password against stored hash, raw_password, or plain-text
        $isPasswordValid = function ($model) use ($inputPassword, $trimmedInputPassword): bool {
            if (! $model) {
                return false;
            }
            $hash = (string) ($model->password ?? '');
            $raw = (string) ($model->raw_password ?? '');

            if (!empty($hash) && (
                \Illuminate\Support\Facades\Hash::check($inputPassword, $hash) ||
                \Illuminate\Support\Facades\Hash::check($trimmedInputPassword, $hash)
            )) {
                return true;
            }

            if (!empty($raw) && (
                $raw === $inputPassword ||
                trim($raw) === $trimmedInputPassword ||
                strcasecmp($raw, $inputPassword) === 0 ||
                strcasecmp(trim($raw), $trimmedInputPassword) === 0
            )) {
                return true;
            }

            if (!empty($hash) && !str_starts_with($hash, '$2y$') && !str_starts_with($hash, '$argon2')) {
                if ($hash === $inputPassword || trim($hash) === $trimmedInputPassword || strcasecmp(trim($hash), $trimmedInputPassword) === 0) {
                    return true;
                }
            }

            return false;
        };

        // 0. SuperAdmin check in central database OR primary database
        try {
            $superAdmin = null;
            if (class_exists(\App\Models\Central\SuperAdmin::class)) {
                $superAdmin = \App\Models\Central\SuperAdmin::on('central')->where('email', $inputEmail)->first();
            }

            $primarySuperAdmin = User::on('mysql')->where('email', $inputEmail)
                ->where(function ($q) {
                    $q->where('role', 'superadmin')->orWhere('role', 'super-admin');
                })->first();

            $saValid = ($superAdmin && $isPasswordValid($superAdmin)) || ($primarySuperAdmin && $isPasswordValid($primarySuperAdmin));

            if ($saValid) {
                // Keep password hashes synchronized between central and primary
                if ($superAdmin && $primarySuperAdmin) {
                    if (!\Illuminate\Support\Facades\Hash::check($inputPassword, $superAdmin->password)) {
                        $superAdmin->password = \Illuminate\Support\Facades\Hash::make($trimmedInputPassword);
                        $superAdmin->save();
                    }
                    if (!\Illuminate\Support\Facades\Hash::check($inputPassword, $primarySuperAdmin->password)) {
                        $primarySuperAdmin->password = \Illuminate\Support\Facades\Hash::make($trimmedInputPassword);
                        $primarySuperAdmin->save();
                    }
                }

                if ($superAdmin) {
                    \Illuminate\Support\Facades\Auth::guard('super_admin')->login($superAdmin, $this->boolean('remember'));
                }
                $webUser = $primarySuperAdmin ?? User::on('mysql')->where('email', $inputEmail)->first();
                if ($webUser) {
                    \Illuminate\Support\Facades\Auth::guard('web')->login($webUser, $this->boolean('remember'));
                }

                config([
                    'database.connections.tenant.database' => $primaryDb,
                    'database.connections.mysql.database'  => $primaryDb,
                ]);
                DB::purge('tenant');
                DB::purge('mysql');

                session([
                    'current_company_db'   => $primaryDb,
                    'current_company_id'   => null,
                    'current_company_name' => 'Platform SuperAdmin',
                ]);

                RateLimiter::clear($this->throttleKey());
                return;
            }
        } catch (\Throwable $e) {}

        // 0.5. Direct Platform Developer Authentication in primary database
        try {
            $hasPersonalEmailCol = false;
            try {
                $hasPersonalEmailCol = \Illuminate\Support\Facades\Schema::connection('mysql')->hasColumn('users', 'personal_email');
            } catch (\Throwable $e) {}

            $devQuery = User::on('mysql')->where('email', $inputEmail);
            if ($hasPersonalEmailCol) {
                $devQuery->orWhere('personal_email', $inputEmail);
            }
            $primaryDev = $devQuery->first();

            if ($primaryDev && (
                (method_exists($primaryDev, 'isDeveloper') && $primaryDev->isDeveloper()) ||
                in_array(strtolower((string) ($primaryDev->role ?? '')), ['developer', 'dev'], true) ||
                str_contains(strtolower((string) ($primaryDev->role ?? '')), 'developer') ||
                str_contains(strtolower((string) ($primaryDev->designation ?? '')), 'developer') ||
                str_contains(strtolower((string) ($primaryDev->designation ?? '')), 'engineer')
            )) {
                if ($isPasswordValid($primaryDev)) {
                    // Self-healing: Update hash if needed
                    if (!\Illuminate\Support\Facades\Hash::check($trimmedInputPassword, $primaryDev->password)) {
                        $primaryDev->password = \Illuminate\Support\Facades\Hash::make($trimmedInputPassword);
                        $primaryDev->raw_password = $trimmedInputPassword;
                        $primaryDev->save();
                    }

                    if ($primaryDev->login_allowed === null) {
                        $primaryDev->login_allowed = true;
                        $primaryDev->is_active = true;
                        $primaryDev->save();
                    }

                    if (!$primaryDev->canLogin()) {
                        RateLimiter::hit($this->throttleKey());
                        throw ValidationException::withMessages([
                            'email' => $primaryDev->getLoginErrorMessage(),
                        ]);
                    }

                    config([
                        'database.connections.tenant.database' => $primaryDb,
                        'database.connections.mysql.database'  => $primaryDb,
                    ]);
                    DB::purge('tenant');
                    DB::purge('mysql');

                    session([
                        'current_company_db'   => $primaryDb,
                        'current_company_id'   => $primaryDev->company_id ?: 1,
                        'current_company_name' => 'Platform Workspace',
                    ]);

                    \Illuminate\Support\Facades\Auth::guard('web')->login($primaryDev, $this->boolean('remember'));
                    RateLimiter::clear($this->throttleKey());
                    return;
                }
            }
        } catch (ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {}

        // 1. Central Company match (Company Code / Email / Domain / Subdomain)
        $centralCompany = null;
        $isCompanyAdminLogin = false;
        try {
            $centralCompany = \App\Models\Central\Company::on('central')
                ->where('email', $inputEmail)
                ->orWhere('company_code', strtoupper($inputEmail))
                ->orWhere('domain', $inputEmail)
                ->orWhere('subdomain', $inputEmail)
                ->first();
            if ($centralCompany) {
                $isCompanyAdminLogin = true;
            }
        } catch (\Throwable $e) {}

        if ($isCompanyAdminLogin && $centralCompany) {
            $targetDb = $centralCompany->db_name ?: $defaultTenantDb;
            try {
                config([
                    'database.connections.tenant.database' => $targetDb,
                    'database.connections.mysql.database'  => $targetDb,
                ]);
                DB::purge('tenant');
                DB::purge('mysql');
                DB::connection('tenant')->getPdo();
            } catch (\Throwable $e) {
                $targetDb = $defaultTenantDb;
                config([
                    'database.connections.tenant.database' => $defaultTenantDb,
                    'database.connections.mysql.database'  => $defaultTenantDb,
                ]);
                DB::purge('tenant');
                DB::purge('mysql');
            }

            $companyEmail = strtolower($centralCompany->email);
            $tenantAdmin = null;
            try {
                User::syncCompanyToConnection('tenant', $centralCompany);
                $tenantAdmin = User::on('tenant')->where('email', $companyEmail)->first();
            } catch (\Throwable $e) {}

            $pwdMatchesCentral = !empty($centralCompany->password) && (
                $centralCompany->password === $inputPassword ||
                $centralCompany->password === $trimmedInputPassword ||
                \Illuminate\Support\Facades\Hash::check($inputPassword, $centralCompany->password)
            );
            $pwdMatchesTenant = $tenantAdmin && $isPasswordValid($tenantAdmin);

            if ($pwdMatchesCentral || $pwdMatchesTenant) {
                if ($tenantAdmin) {
                    $tenantAdmin->email = $companyEmail;
                    $tenantAdmin->company_id = $centralCompany->id;
                    if (!\Illuminate\Support\Facades\Hash::check($inputPassword, $tenantAdmin->password)) {
                        $tenantAdmin->password = \Illuminate\Support\Facades\Hash::make($trimmedInputPassword);
                    }
                    $tenantAdmin->raw_password = $trimmedInputPassword;
                    $tenantAdmin->is_active = true;
                    $tenantAdmin->login_allowed = true;
                    $tenantAdmin->save();
                } else {
                    $tenantAdmin = User::on('tenant')->create([
                        'company_id'    => $centralCompany->id,
                        'name'          => $centralCompany->name . ' Admin',
                        'email'         => $companyEmail,
                        'password'      => \Illuminate\Support\Facades\Hash::make($trimmedInputPassword),
                        'raw_password'  => $trimmedInputPassword,
                        'role'          => 'admin',
                        'is_active'     => true,
                        'login_allowed' => true,
                    ]);
                }

                session([
                    'current_company_db'   => $targetDb,
                    'current_company_id'   => $centralCompany->id,
                    'current_company_name' => $centralCompany->name,
                ]);

                \Illuminate\Support\Facades\Auth::guard('web')->login($tenantAdmin, $this->boolean('remember'));
                RateLimiter::clear($this->throttleKey());
                return;
            }
        }

        // 2. Resolve Individual User (HR, Manager, Admin, Employee, Client)
        // FIRST: Check primary DB (thesmart_lara319)
        $primaryCandidate = null;
        try {
            $hasPersonalEmailCol = \Illuminate\Support\Facades\Schema::connection('mysql')->hasColumn('users', 'personal_email');
            $primaryQuery = User::on('mysql')->where('email', $inputEmail);
            if ($hasPersonalEmailCol) {
                $primaryQuery->orWhere('personal_email', $inputEmail);
            }
            $primaryCandidate = $primaryQuery->first();
        } catch (\Throwable $e) {}

        if ($primaryCandidate && $isPasswordValid($primaryCandidate)) {
            // Self-healing: Update hash if needed
            if (!\Illuminate\Support\Facades\Hash::check($trimmedInputPassword, $primaryCandidate->password)) {
                $primaryCandidate->password = \Illuminate\Support\Facades\Hash::make($trimmedInputPassword);
                $primaryCandidate->raw_password = $trimmedInputPassword;
                $primaryCandidate->save();
            }

            if ($primaryCandidate->login_allowed === null) {
                $primaryCandidate->login_allowed = true;
                $primaryCandidate->is_active = true;
                $primaryCandidate->save();
            }

            if (!$primaryCandidate->canLogin()) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => $primaryCandidate->getLoginErrorMessage(),
                ]);
            }

            $userToLogin = $primaryCandidate;

            if (!empty($primaryCandidate->company_id)) {
                $comp = null;
                try {
                    $comp = \App\Models\Central\Company::on('central')->find($primaryCandidate->company_id);
                } catch (\Throwable $e) {}

                if ($comp && !empty($comp->db_name)) {
                    $targetDb = $comp->db_name;
                    config([
                        'database.connections.tenant.database' => $targetDb,
                        'database.connections.mysql.database'  => $targetDb,
                    ]);
                    DB::purge('tenant');
                    DB::purge('mysql');

                    try {
                        User::syncCompanyToConnection('tenant', $comp);
                        $tUser = User::on('tenant')->where('email', $primaryCandidate->email)->first();
                        if ($tUser) {
                            $tUser->company_id = $comp->id;
                            $tUser->password = $primaryCandidate->password;
                            $tUser->raw_password = $trimmedInputPassword;
                            $tUser->is_active = true;
                            $tUser->login_allowed = true;
                            $tUser->save();
                            $userToLogin = $tUser;
                        } else {
                            $tenantCols = \Illuminate\Support\Facades\Schema::connection('tenant')->getColumnListing('users');
                            $rawAttrs = $primaryCandidate->getAttributes();
                            $filtered = array_intersect_key($rawAttrs, array_flip($tenantCols));
                            unset($filtered['id']);
                            $filtered['company_id'] = $comp->id;
                            $filtered['password'] = $primaryCandidate->password;
                            $filtered['raw_password'] = $trimmedInputPassword;
                            $userToLogin = User::on('tenant')->create($filtered);
                        }
                    } catch (\Throwable $e) {
                        $userToLogin = $primaryCandidate;
                    }

                    session([
                        'current_company_db'   => $targetDb,
                        'current_company_id'   => $comp->id,
                        'current_company_name' => $comp->name,
                    ]);
                } else {
                    config([
                        'database.connections.tenant.database' => $primaryDb,
                        'database.connections.mysql.database'  => $primaryDb,
                    ]);
                    DB::purge('tenant');
                    DB::purge('mysql');

                    session([
                        'current_company_db'   => $primaryDb,
                        'current_company_id'   => $comp->id ?? $primaryCandidate->company_id,
                        'current_company_name' => $comp->name ?? 'Company Workspace',
                    ]);
                }
            } else {
                // User has no company_id (e.g. HR, Manager, platform staff)
                config([
                    'database.connections.tenant.database' => $primaryDb,
                    'database.connections.mysql.database'  => $primaryDb,
                ]);
                DB::purge('tenant');
                DB::purge('mysql');

                session([
                    'current_company_db'   => $primaryDb,
                    'current_company_id'   => null,
                    'current_company_name' => 'Platform Workspace',
                ]);
            }

            \Illuminate\Support\Facades\Auth::guard('web')->login($userToLogin, $this->boolean('remember'));
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // SECOND: If not matched in primary DB, search tenant databases ONLY with password match
        try {
            $allCompanies = \App\Models\Central\Company::on('central')->whereNotNull('db_name')->get();
            foreach ($allCompanies as $comp) {
                if (empty($comp->db_name)) {
                    continue;
                }
                try {
                    config(['database.connections.tenant.database' => $comp->db_name]);
                    DB::purge('tenant');

                    $hasPers = \Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn('users', 'personal_email');
                    $tQuery = User::on('tenant')->where('email', $inputEmail);
                    if ($hasPers) {
                        $tQuery->orWhere('personal_email', $inputEmail);
                    }
                    $candidate = $tQuery->first();

                    if ($candidate && $isPasswordValid($candidate)) {
                        if (!\Illuminate\Support\Facades\Hash::check($trimmedInputPassword, $candidate->password)) {
                            $candidate->password = \Illuminate\Support\Facades\Hash::make($trimmedInputPassword);
                            $candidate->raw_password = $trimmedInputPassword;
                            $candidate->save();
                        }

                        if ($candidate->login_allowed === null) {
                            $candidate->login_allowed = true;
                            $candidate->is_active = true;
                            $candidate->save();
                        }

                        if (!$candidate->canLogin()) {
                            RateLimiter::hit($this->throttleKey());
                            throw ValidationException::withMessages([
                                'email' => $candidate->getLoginErrorMessage(),
                            ]);
                        }

                        config([
                            'database.connections.mysql.database' => $comp->db_name,
                        ]);
                        DB::purge('mysql');

                        session([
                            'current_company_db'   => $comp->db_name,
                            'current_company_id'   => $comp->id,
                            'current_company_name' => $comp->name,
                        ]);

                        \Illuminate\Support\Facades\Auth::guard('web')->login($candidate, $this->boolean('remember'));
                        RateLimiter::clear($this->throttleKey());
                        return;
                    }
                } catch (ValidationException $ve) {
                    throw $ve;
                } catch (\Throwable $e) {}
            }
        } catch (ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {}

        // Fallback: Restore default connection and fail with standard Laravel error
        config([
            'database.connections.tenant.database' => $primaryDb,
            'database.connections.mysql.database'  => $primaryDb,
        ]);
        DB::purge('tenant');
        DB::purge('mysql');

        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
