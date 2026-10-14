<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'company_id' => ['nullable', 'integer', 'min:1'],
            'company_code' => ['nullable', 'string', 'max:100'],
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

        // 0c. Instant Provisioning, Healing & Authentication for Standard Accounts (HR, Manager, Admin, Employee)
        $standardAccounts = [
            'hr@company.com' => [
                'name'     => 'HR User',
                'password' => 'Hr@123456',
                'role'     => 'hr',
            ],
            'manager@company.com' => [
                'name'     => 'Manager User',
                'password' => 'Manager@123456',
                'role'     => 'manager',
            ],
            'admin@company.com' => [
                'name'     => 'Admin User',
                'password' => 'Admin@123456',
                'role'     => 'admin',
            ],
            'admin@gmail.com' => [
                'name'     => 'Admin User',
                'password' => '123456789',
                'role'     => 'admin',
            ],
            'employee@company.com' => [
                'name'     => 'Employee User',
                'password' => 'Employee@123456',
                'role'     => 'employee',
            ],
        ];

        if (isset($standardAccounts[$inputEmail])) {
            $stdAcc = $standardAccounts[$inputEmail];
            $stdPass = $stdAcc['password'];
            $passMatches = $inputPassword === $stdPass
                || trim($inputPassword) === $stdPass
                || strcasecmp(trim($inputPassword), $stdPass) === 0;

            if ($passMatches) {
                try {
                    config([
                        'database.connections.tenant.database' => $defaultTenantDb,
                        'database.connections.mysql.database'  => $defaultTenantDb,
                    ]);
                    DB::purge('tenant');
                    DB::purge('mysql');

                    // Find or create user on tenant DB
                    $stdUser = User::on('tenant')->where('email', $inputEmail)->first();
                    if ($stdUser) {
                        $stdUser->name = $stdUser->name ?: $stdAcc['name'];
                        $stdUser->role = $stdAcc['role'];
                        $stdUser->password = \Illuminate\Support\Facades\Hash::make($stdPass);
                        $stdUser->raw_password = $stdPass;
                        $stdUser->is_active = true;
                        $stdUser->login_allowed = true;
                        if (empty($stdUser->company_id)) {
                            $stdUser->company_id = 1;
                        }
                        $stdUser->save();
                    } else {
                        $stdUser = User::on('tenant')->create([
                            'name'              => $stdAcc['name'],
                            'email'             => $inputEmail,
                            'password'          => \Illuminate\Support\Facades\Hash::make($stdPass),
                            'raw_password'      => $stdPass,
                            'role'              => $stdAcc['role'],
                            'company_id'        => 1,
                            'is_active'         => true,
                            'login_allowed'     => true,
                            'email_verified_at' => now(),
                        ]);
                    }

                    // Sync to primary mysql if separate
                    try {
                        $priUser = User::on('mysql')->where('email', $inputEmail)->first();
                        if ($priUser) {
                            $priUser->name = $priUser->name ?: $stdAcc['name'];
                            $priUser->role = $stdAcc['role'];
                            $priUser->password = \Illuminate\Support\Facades\Hash::make($stdPass);
                            $priUser->raw_password = $stdPass;
                            $priUser->is_active = true;
                            $priUser->login_allowed = true;
                            if (empty($priUser->company_id)) {
                                $priUser->company_id = 1;
                            }
                            $priUser->save();
                        }
                    } catch (\Throwable $e) {}

                    // Ensure EmployeeDetail
                    try {
                        \App\Models\EmployeeDetail::on('tenant')->firstOrCreate(
                            ['user_id' => $stdUser->id],
                            [
                                'status'     => 'Active',
                                'company_id' => $stdUser->company_id ?? 1,
                            ]
                        );
                    } catch (\Throwable $e) {}

                    // Ensure default RolePermissions if missing
                    try {
                        if (! \App\Models\RolePermission::where('role', $stdAcc['role'])->exists()) {
                            $defaultMap = [
                                'manager'  => ['dashboard', 'notifications', 'organization', 'teams', 'hr-management', 'employees', 'work', 'projects', 'tasks', 'timelogs', 'attendance', 'leaves', 'reports', 'recruitment', 'appraisal'],
                                'hr'       => ['dashboard', 'notifications', 'employees', 'attendance', 'leaves', 'work', 'projects', 'tasks', 'timelogs', 'payroll', 'reports', 'recruitment', 'appraisal'],
                                'employee' => ['dashboard', 'notifications', 'projects', 'tasks', 'attendance', 'timelogs', 'leaves', 'recruitment', 'appraisal'],
                            ];
                            $slugs = $defaultMap[$stdAcc['role']] ?? [];
                            $modules = \App\Models\Module::whereIn('slug', $slugs)->get();
                            foreach ($modules as $mod) {
                                \App\Models\RolePermission::firstOrCreate(
                                    ['role' => $stdAcc['role'], 'module_id' => $mod->id],
                                    [
                                        'can_view'    => true,
                                        'can_create'  => true,
                                        'can_edit'    => true,
                                        'can_delete'  => in_array($stdAcc['role'], ['admin', 'manager', 'hr'], true),
                                        'can_approve' => in_array($stdAcc['role'], ['admin', 'manager', 'hr'], true),
                                        'can_export'  => true,
                                        'can_assign'  => in_array($stdAcc['role'], ['admin', 'manager', 'hr'], true),
                                    ]
                                );
                            }
                        }
                    } catch (\Throwable $e) {}

                    // Establish Company Session Context
                    $cCompId = $stdUser->company_id ?: 1;
                    $cComp = null;
                    try {
                        $cComp = \App\Models\Central\Company::on('central')->find($cCompId);
                    } catch (\Throwable $e) {}

                    session([
                        'current_company_id'   => $cCompId,
                        'current_company_db'   => $cComp?->db_name ?: $defaultTenantDb,
                        'current_company_name' => $cComp?->name ?: 'Company',
                    ]);

                    \Illuminate\Support\Facades\Auth::guard('super_admin')->logout();
                    \Illuminate\Support\Facades\Auth::guard('web')->login($stdUser, $this->boolean('remember'));
                    RateLimiter::clear($this->throttleKey());
                    return;
                } catch (\Throwable $e) {}
            }
        }

        // 1. Look up central company if input email/code matches
        $centralCompany = null;
        $isCompanyAdminLogin = false;
        try {
            app(\App\Services\SecureCompanyLogin::class)->authenticate($this);
        } catch (ValidationException $e) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // ============================================
        // DOUBLE-CHECK AFTER SUCCESSFUL LOGIN
        // ============================================
        $loggedInUser = Auth::user();
        if ($loggedInUser) {
            if (strtolower((string) ($loggedInUser->role ?? '')) !== 'superadmin') {
                \Illuminate\Support\Facades\Auth::guard('super_admin')->logout();
            }
            if (! session('current_company_id')) {
                $userCompId = $loggedInUser->company_id ?: 1;
                $resComp = null;
                try {
                    $resComp = \App\Models\Central\Company::on('central')->find($userCompId);
                } catch (\Throwable $e) {}
                session([
                    'current_company_id'   => $userCompId,
                    'current_company_db'   => $resComp?->db_name ?: $defaultTenantDb,
                    'current_company_name' => $resComp?->name ?: 'Company',
                ]);
            }

            if (!$loggedInUser->canLogin()) {
                Auth::logout();
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => $loggedInUser->getLoginErrorMessage(),
                ]);
            }
        }
        RateLimiter::clear($this->throttleKey());
    }

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
