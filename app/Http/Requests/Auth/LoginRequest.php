<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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

        $inputEmail = strtolower(trim($this->string('email')));
        $inputPassword = (string) $this->string('password');

        $defaultTenantDb = config('database.connections.tenant.database')
            ?: (config('database.connections.mysql.database') ?: env('DB_DATABASE', 'thesmart_lara319'));

        // 0. Check SuperAdmin in central database
        try {
            if (class_exists(\App\Models\Central\SuperAdmin::class)) {
                $superAdmin = \App\Models\Central\SuperAdmin::on('central')->where('email', $inputEmail)->first();
                if ($superAdmin && \Illuminate\Support\Facades\Hash::check($inputPassword, $superAdmin->password)) {
                    \Illuminate\Support\Facades\Auth::guard('super_admin')->login($superAdmin, $this->boolean('remember'));
                    $webUser = User::on('tenant')->where('email', $inputEmail)->first()
                        ?? User::on('mysql')->where('email', $inputEmail)->first();
                    if ($webUser) {
                        \Illuminate\Support\Facades\Auth::guard('web')->login($webUser, $this->boolean('remember'));
                    }
                    RateLimiter::clear($this->throttleKey());
                    return;
                }
            }
        } catch (\Throwable $e) {}

        $centralCompany = null;
        try {
            $centralCompany = \App\Models\Central\Company::on('central')
                ->where('email', $inputEmail)
                ->orWhere('company_code', strtoupper($inputEmail))
                ->first();
        } catch (\Throwable $e) {}

        if ($centralCompany && !empty($centralCompany->db_name)) {
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
            }

            if (app()->bound(\App\Services\CompanyContext::class)) {
                app(\App\Services\CompanyContext::class)->reset();
            }

            // Sync user in Tenant DB connection
            try {
                if ($centralCompany) {
                    User::syncCompanyToConnection('tenant', $centralCompany);
                }

                $tenantAdmin = User::on('tenant')->where('email', $companyEmail)->first();
                if (! $tenantAdmin) {
                    $tenantAdmin = User::on('tenant')
                        ->where(function ($q) {
                            $q->whereIn('role', ['admin', 'superadmin', 'administrator']);
                        })
                        ->first();
                }

                if ($tenantAdmin) {
                    $tenantAdmin->email = $companyEmail;
                    $tenantAdmin->password = \Illuminate\Support\Facades\Hash::make($inputPassword);
                    $tenantAdmin->raw_password = $inputPassword;
                    $tenantAdmin->is_active = true;
                    $tenantAdmin->login_allowed = true;
                    $tenantAdmin->save();
                } else {
                    User::on('tenant')->create([
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
            } catch (\Throwable $e) {}

            // Sync user in primary MySQL connection
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
                } else {
                    User::on('mysql')->create([
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
            } catch (\Throwable $e) {}

            // Always ensure central company record has updated password
            if (empty($centralCompany->password) || $centralCompany->password !== $inputPassword) {
                $centralCompany->password = $inputPassword;
                $centralCompany->save();
            }
        }

        // Locate user: first check active/default tenant connection
        $user = null;
        try {
            $hasPersonalEmailCol = \Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn('users', 'personal_email');
            $userQuery = User::on('tenant')->where('email', $inputEmail);
            if ($centralCompany) {
                $userQuery->orWhere('email', strtolower($centralCompany->email));
            }
            if ($hasPersonalEmailCol) {
                $userQuery->orWhere('personal_email', $inputEmail);
            }
            $user = $userQuery->first();
        } catch (\Throwable $e) {}

        // If not found in current connection and no centralCompany, search other tenant databases
        if (! $user && ! $centralCompany) {
            try {
                $allCompanies = \App\Models\Central\Company::on('central')->whereNotNull('db_name')->get();
                foreach ($allCompanies as $comp) {
                    if (empty($comp->db_name) || $comp->db_name === $defaultTenantDb) {
                        continue;
                    }
                    try {
                        config(['database.connections.tenant.database' => $comp->db_name]);
                        \Illuminate\Support\Facades\DB::purge('tenant');

                        $hasPersEmailCol = \Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn('users', 'personal_email');
                        $tUserQuery = User::on('tenant')->where('email', $inputEmail);
                        if ($hasPersEmailCol) {
                            $tUserQuery->orWhere('personal_email', $inputEmail);
                        }
                        $foundUser = $tUserQuery->first();
                        if ($foundUser) {
                            $user = $foundUser;
                            session([
                                'current_company_db'   => $comp->db_name,
                                'current_company_id'   => $comp->id,
                                'current_company_name' => $comp->name,
                            ]);
                            break;
                        }
                    } catch (\Throwable $e) {}
                }
            } catch (\Throwable $e) {}

            // If not found in any other database, restore default tenant DB
            if (! $user) {
                config(['database.connections.tenant.database' => $defaultTenantDb]);
                \Illuminate\Support\Facades\DB::purge('tenant');
            }
        }

        if ($user) {
            // Self-healing: If raw_password matches input or if stored password is plain text
            if (!empty($user->raw_password) && $user->raw_password === $inputPassword) {
                if (!\Illuminate\Support\Facades\Hash::check($inputPassword, $user->password)) {
                    $user->password = \Illuminate\Support\Facades\Hash::make($inputPassword);
                    $user->save();
                }
            } elseif ($user->password === $inputPassword) {
                // Plain text password migration
                $user->password = \Illuminate\Support\Facades\Hash::make($inputPassword);
                $user->raw_password = $inputPassword;
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

        if (! Auth::attempt($attemptCredentials, $this->boolean('remember'))) {
            // Fallback attempt with central company email if input was company_code or domain
            if ($centralCompany && strtolower($centralCompany->email) !== $inputEmail) {
                $attemptCredentials['email'] = strtolower($centralCompany->email);
                if (Auth::attempt($attemptCredentials, $this->boolean('remember'))) {
                    RateLimiter::clear($this->throttleKey());
                    return;
                }
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
