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

        $defaultTenantDb = config('database.connections.mysql.database') ?: env('DB_DATABASE', 'pms_last');
        config([
            'database.connections.tenant.database' => $defaultTenantDb,
            'database.connections.mysql.database'  => $defaultTenantDb,
        ]);
        \Illuminate\Support\Facades\DB::purge('tenant');
        \Illuminate\Support\Facades\DB::purge('mysql');

        // 0. Check SuperAdmin in central database
        try {
            if (class_exists(\App\Models\Central\SuperAdmin::class)) {
                $superAdmin = \App\Models\Central\SuperAdmin::on('central')->where('email', $inputEmail)->first();
                if ($superAdmin) {
                    $trimmedInputPassword = trim($inputPassword);
                    $isPassValid = false;
                    if (\Illuminate\Support\Facades\Hash::check($inputPassword, $superAdmin->password)
                        || \Illuminate\Support\Facades\Hash::check($trimmedInputPassword, $superAdmin->password)
                        || $superAdmin->password === $inputPassword
                        || $superAdmin->password === $trimmedInputPassword
                        || (isset($superAdmin->raw_password) && ($superAdmin->raw_password === $inputPassword || $superAdmin->raw_password === $trimmedInputPassword))
                    ) {
                        $isPassValid = true;
                    }

                    $isActive = $superAdmin->is_active === null || $superAdmin->is_active == 1 || $superAdmin->is_active === true;

                    if ($isPassValid && $isActive) {
                        if (!\Illuminate\Support\Facades\Hash::check($inputPassword, $superAdmin->password)) {
                            try {
                                $superAdmin->password = \Illuminate\Support\Facades\Hash::make($inputPassword);
                                if (isset($superAdmin->raw_password)) {
                                    $superAdmin->raw_password = $inputPassword;
                                }
                                $superAdmin->save();
                            } catch (\Throwable $e) {}
                        }

                        \Illuminate\Support\Facades\Auth::guard('super_admin')->login($superAdmin, $this->boolean('remember'));

                        try {
                            $webUser = User::where('email', $inputEmail)->first()
                                ?? User::on('mysql')->where('email', $inputEmail)->first();
                            if ($webUser) {
                                \Illuminate\Support\Facades\Auth::guard('web')->login($webUser, $this->boolean('remember'));
                            }
                        } catch (\Throwable $e) {}

                        RateLimiter::clear($this->throttleKey());
                        return;
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 0b. Check SuperAdmin in users table (fallback)
        try {
            $centralSuperUser = User::on('mysql')->where('email', $inputEmail)->whereIn('role', ['superadmin', 'super-admin'])->first()
                ?? User::where('email', $inputEmail)->whereIn('role', ['superadmin', 'super-admin'])->first();
            if ($centralSuperUser) {
                $trimmedInputPassword = trim($inputPassword);
                $isPassValid = \Illuminate\Support\Facades\Hash::check($inputPassword, $centralSuperUser->password)
                    || \Illuminate\Support\Facades\Hash::check($trimmedInputPassword, $centralSuperUser->password)
                    || $centralSuperUser->raw_password === $inputPassword
                    || $centralSuperUser->password === $inputPassword;
                $isActive = $centralSuperUser->is_active === null || $centralSuperUser->is_active == 1 || $centralSuperUser->is_active === true;

                if ($isPassValid && $isActive) {
                    if (!\Illuminate\Support\Facades\Hash::check($inputPassword, $centralSuperUser->password)) {
                        try {
                            $centralSuperUser->password = \Illuminate\Support\Facades\Hash::make($inputPassword);
                            $centralSuperUser->raw_password = $inputPassword;
                            $centralSuperUser->save();
                        } catch (\Throwable $e) {}
                    }

                    \Illuminate\Support\Facades\Auth::guard('web')->login($centralSuperUser, $this->boolean('remember'));
                    if (class_exists(\App\Models\Central\SuperAdmin::class)) {
                        $cSa = \App\Models\Central\SuperAdmin::on('central')->where('email', $inputEmail)->first();
                        if ($cSa) {
                            \Illuminate\Support\Facades\Auth::guard('super_admin')->login($cSa, $this->boolean('remember'));
                        }
                    }
                    RateLimiter::clear($this->throttleKey());
                    return;
                }
            }
        } catch (\Throwable $e) {}

        // 1. Look up central company if input email/code matches
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

        // 2. If not found in central directly, search tenant databases to find the user's company
        $user = null;
        if (! $centralCompany) {
            try {
                $allCompanies = \App\Models\Central\Company::on('central')->whereNotNull('db_name')->get();
                $bestUser = null;
                $bestCompany = null;

                foreach ($allCompanies as $comp) {
                    if (empty($comp->db_name)) {
                        continue;
                    }
                    try {
                        config(['database.connections.tenant.database' => $comp->db_name]);
                        \Illuminate\Support\Facades\DB::purge('tenant');

                        // Test if database can actually be connected to on this host
                        \Illuminate\Support\Facades\DB::connection('tenant')->getPdo();

                        $hasPersEmailCol = \Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn('users', 'personal_email');
                        $tUserQuery = User::on('tenant')->where('email', $inputEmail);
                        if ($hasPersEmailCol) {
                            $tUserQuery->orWhere('personal_email', $inputEmail);
                        }
                        $candidate = $tUserQuery->first();
                        if ($candidate) {
                            $isPassValid = \Illuminate\Support\Facades\Hash::check($inputPassword, $candidate->password)
                                || $candidate->raw_password === $inputPassword
                                || $candidate->password === $inputPassword;

                            if ($isPassValid) {
                                $bestUser = $candidate;
                                $bestCompany = $comp;
                                break;
                            } elseif (! $bestUser) {
                                $bestUser = $candidate;
                                $bestCompany = $comp;
                            }
                        }
                    } catch (\Throwable $e) {
                        // Inaccessible database! Revert immediately to default connection
                        config(['database.connections.tenant.database' => $defaultTenantDb]);
                        \Illuminate\Support\Facades\DB::purge('tenant');
                    }
                }

                if ($bestCompany) {
                    $centralCompany = $bestCompany;
                    $user = $bestUser;
                } else {
                    config([
                        'database.connections.tenant.database' => $defaultTenantDb,
                        'database.connections.mysql.database'  => $defaultTenantDb,
                    ]);
                    \Illuminate\Support\Facades\DB::purge('tenant');
                    \Illuminate\Support\Facades\DB::purge('mysql');
                }
            } catch (\Throwable $e) {}
        }

        // Check if user is a developer
        $isDeveloper = false;
        if ($user) {
            $isDeveloper = method_exists($user, 'isDeveloper') ? $user->isDeveloper() : (
                in_array(strtolower((string)($user->role ?? '')), ['developer', 'dev'], true)
                || str_contains(strtolower((string)($user->designation ?? '')), 'developer')
                || str_contains(strtolower((string)($user->designation ?? '')), 'engineer')
            );
        }

        // If central company not found yet, check via user's company_id
        if (! $centralCompany && $user && !empty($user->company_id)) {
            try {
                $centralCompany = \App\Models\Central\Company::on('central')->find($user->company_id);
                // The user is an individual member/employee of this company, NOT the company admin login.
                $isCompanyAdminLogin = false;
            } catch (\Throwable $e) {}
        }
        // Detect if this is an authentic company admin credential login
        $isCompanyAdminLogin = false;
        if ($centralCompany && ! $isDeveloper) {
            $cmpEmail = strtolower((string) $centralCompany->email);
            $cmpCode  = strtolower((string) $centralCompany->company_code);
            $cmpDom   = strtolower((string) $centralCompany->domain);
            $cmpSub   = strtolower((string) $centralCompany->subdomain);

            if (in_array($inputEmail, array_filter([$cmpEmail, $cmpCode, $cmpDom, $cmpSub]), true)) {
                $isCompanyAdminLogin = true;
            }
        }

        if ($centralCompany && !empty($centralCompany->db_name) && ! $isDeveloper) {
            $companyEmail = strtolower($centralCompany->email);

            // Set dynamic tenant DB connection for this company login with PDO validation
            $targetDb = $centralCompany->db_name;
            try {
                config([
                    'database.connections.tenant.database' => $targetDb,
                    'database.connections.mysql.database'  => $targetDb,
                ]);
                DB::purge('tenant');
                DB::purge('mysql');
                DB::connection('tenant')->getPdo();

                session([
                    'current_company_db'   => $targetDb,
                    'current_company_id'   => $centralCompany->id,
                    'current_company_name' => $centralCompany->name,
                ]);
            } catch (\Throwable $e) {
                // Fall back to default tenant DB if custom tenant DB cannot be connected
                config([
                    'database.connections.tenant.database' => $defaultTenantDb,
                    'database.connections.mysql.database'  => $defaultTenantDb,
                ]);
                DB::purge('tenant');
                DB::purge('mysql');

                session([
                    'current_company_db'   => $defaultTenantDb,
                    'current_company_id'   => $centralCompany->id,
                    'current_company_name' => $centralCompany->name,
                ]);
            }

            if (app()->bound(\App\Services\CompanyContext::class)) {
                app(\App\Services\CompanyContext::class)->reset();
            }

            // Sync user in Tenant DB connection ONLY if this is a company admin login
            if ($isCompanyAdminLogin) {
                try {
                    User::syncCompanyToConnection('tenant', $centralCompany);

                    $tenantAdmin = User::on('tenant')->where('email', $companyEmail)->first();
                    if (! $tenantAdmin) {
                        $tenantAdmin = User::on('tenant')
                            ->where(function ($q) {
                                $q->whereIn('role', ['admin', 'superadmin', 'administrator']);
                            })
                            ->first();
                    }

                    $passwordMatchesCentral = !empty($centralCompany->password) && ($centralCompany->password === $inputPassword || \Illuminate\Support\Facades\Hash::check($inputPassword, $centralCompany->password));
                    $passwordMatchesTenant = $tenantAdmin && (\Illuminate\Support\Facades\Hash::check($inputPassword, $tenantAdmin->password) || $tenantAdmin->raw_password === $inputPassword);

                    if ($passwordMatchesCentral || $passwordMatchesTenant) {
                        if ($tenantAdmin) {
                            $tenantAdmin->email = $companyEmail;
                            // Always stamp correct company_id so SetTenantConnection resolves the right DB
                            $tenantAdmin->company_id = $centralCompany->id;
                            if (!\Illuminate\Support\Facades\Hash::check($inputPassword, $tenantAdmin->password)) {
                                $tenantAdmin->password = \Illuminate\Support\Facades\Hash::make($inputPassword);
                            }
                            $tenantAdmin->raw_password = $inputPassword;
                            $tenantAdmin->is_active = true;
                            $tenantAdmin->login_allowed = true;
                            $tenantAdmin->save();
                        } else {
                            $tenantAdmin = User::on('tenant')->create([
                                'company_id'    => $centralCompany->id,
                                'name'          => $centralCompany->name . ' Admin',
                                'email'         => $companyEmail,
                                'password'      => \Illuminate\Support\Facades\Hash::make($inputPassword),
                                'raw_password'  => $inputPassword,
                                'role'          => 'admin',
                                'is_active'     => true,
                                'login_allowed' => true,
                            ]);
                        }

                        // Also sync user in primary MySQL connection
                        try {
                            $primaryAdmin = User::on('mysql')->where('email', $companyEmail)->first();
                            if (! $primaryAdmin && $centralCompany->id) {
                                $primaryAdmin = User::on('mysql')->where('company_id', $centralCompany->id)->first();
                            }

                            if ($primaryAdmin) {
                                $primaryAdmin->email = $companyEmail;
                                $primaryAdmin->password = \Illuminate\Support\Facades\Hash::make($inputPassword);
                                $primaryAdmin->raw_password = $inputPassword;
                                $primaryAdmin->is_active = true;
                                $primaryAdmin->login_allowed = true;
                                $primaryAdmin->save();
                            }
                        } catch (\Throwable $e) {}

                        // Ensure central company record has updated password if needed
                        if (empty($centralCompany->password) || $centralCompany->password !== $inputPassword) {
                            $centralCompany->password = $inputPassword;
                            $centralCompany->save();
                        }
                    }
                } catch (\Throwable $e) {}
            }
        }

        // Locate user: first check active/default tenant connection
        $user = null;
        try {
            $hasPersonalEmailCol = \Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn('users', 'personal_email');
            $userQuery = User::on('tenant')->where('email', $inputEmail);
            if ($isCompanyAdminLogin && $centralCompany) {
                $userQuery->orWhere('email', strtolower($centralCompany->email));
            }
            if ($hasPersonalEmailCol) {
                $userQuery->orWhere('personal_email', $inputEmail);
            }
            $user = $userQuery->first();
        } catch (\Throwable $e) {}



        if (! $user) {
            // Check if user exists by email or personal_email
            $hasPersonalEmailCol = false;
            try {
                $hasPersonalEmailCol = \Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn('users', 'personal_email');
            } catch (\Throwable $e) {
                config(['database.connections.tenant.database' => $defaultTenantDb]);
                DB::purge('tenant');
                try {
                    $hasPersonalEmailCol = \Illuminate\Support\Facades\Schema::connection('mysql')->hasColumn('users', 'personal_email');
                } catch (\Throwable $e2) {}
            }

            try {
                $userQuery = User::where('email', $inputEmail);
                if ($isCompanyAdminLogin && $centralCompany && !empty($centralCompany->email)) {
                    $userQuery->orWhere('email', strtolower($centralCompany->email));
                }
                if ($hasPersonalEmailCol) {
                    $userQuery->orWhere('personal_email', $inputEmail);
                }
                $user = $userQuery->first();
            } catch (\Throwable $e) {
                // If tenant query fails (e.g. database access error), fall back safely to mysql connection
                try {
                    config(['database.connections.tenant.database' => $defaultTenantDb]);
                    DB::purge('tenant');
                    $user = User::on('mysql')->where('email', $inputEmail)->first();
                } catch (\Throwable $e3) {
                    $user = null;
                }
            }
        }

        if ($user) {
            $trimmedInputPassword = trim($inputPassword);

            // Self-healing: If raw_password matches input (exact, case-insensitive, or trimmed)
            // or if stored password is plain text
            $matchesRaw = false;
            if (!empty($user->raw_password)) {
                $rawTrimmed = trim($user->raw_password);
                if (
                    $user->raw_password === $inputPassword
                    || $rawTrimmed === $trimmedInputPassword
                    || strcasecmp($user->raw_password, $inputPassword) === 0
                    || strcasecmp($rawTrimmed, $trimmedInputPassword) === 0
                ) {
                    $matchesRaw = true;
                }
            }

            if ($matchesRaw) {
                if (!\Illuminate\Support\Facades\Hash::check($trimmedInputPassword, $user->password)) {
                    $user->password = \Illuminate\Support\Facades\Hash::make($trimmedInputPassword);
                    $user->raw_password = $trimmedInputPassword;
                    $user->save();
                }
            } elseif ($user->password === $inputPassword || $user->password === $trimmedInputPassword || (!empty($user->password) && strcasecmp(trim($user->password), $trimmedInputPassword) === 0 && !str_starts_with($user->password, '$2y$'))) {
                // Plain text password migration
                $user->password = \Illuminate\Support\Facades\Hash::make($trimmedInputPassword);
                $user->raw_password = $trimmedInputPassword;
                $user->save();
            }

            // Ensure login_allowed and is_active are enabled if not explicitly blocked
            if ($user->login_allowed === null) {
                $user->login_allowed = true;
                $user->is_active = true;
                $user->save();
            }

            // Check if user can login (including developer task assignment check & exit date logic)
            if (!$user->canLogin()) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => $user->getLoginErrorMessage(),
                ]);
            }
        }

        // Attempt authentication using the primary email
        $attemptCredentials = [
            'email' => $user ? $user->email : $inputEmail,
            'password' => $inputPassword,
        ];

        $authSuccess = false;
        try {
            $authSuccess = Auth::attempt($attemptCredentials, $this->boolean('remember'));
        } catch (\Throwable $e) {
            config([
                'database.connections.tenant.database' => $defaultTenantDb,
                'database.connections.mysql.database'  => $defaultTenantDb,
            ]);
            DB::purge('tenant');
            DB::purge('mysql');
            try {
                $authSuccess = Auth::attempt($attemptCredentials, $this->boolean('remember'));
            } catch (\Throwable $e2) {
                $authSuccess = false;
            }
        }

        if (! $authSuccess) {
            // Check if trimming the password works
            if (trim($inputPassword) !== $inputPassword) {
                $attemptCredentials['password'] = trim($inputPassword);
                try {
                    if (Auth::attempt($attemptCredentials, $this->boolean('remember'))) {
                        RateLimiter::clear($this->throttleKey());
                        return;
                    }
                } catch (\Throwable $e) {}
            }

            // Fallback attempt with central company email if input was company_code or domain
            if ($isCompanyAdminLogin && $centralCompany && !empty($centralCompany->email) && strtolower($centralCompany->email) !== $inputEmail) {
                $attemptCredentials['email'] = strtolower($centralCompany->email);
                $attemptCredentials['password'] = $inputPassword;
                try {
                    if (Auth::attempt($attemptCredentials, $this->boolean('remember'))) {
                        RateLimiter::clear($this->throttleKey());
                        return;
                    }
                } catch (\Throwable $e) {}
            }

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // ============================================
        // DOUBLE-CHECK AFTER SUCCESSFUL LOGIN
        // ============================================
        $loggedInUser = Auth::user();
        if ($loggedInUser && !$loggedInUser->canLogin()) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => $loggedInUser->getLoginErrorMessage(),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
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
