<?php

namespace App\Http\Controllers\SuperAdmin\Concerns;

use App\Models\Central\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

trait ManagesCompanyProvisioning
{
    /**
     * Get tenant database prefix (supports cPanel prefix and TENANT_DB_PREFIX env).
     */
    public function getTenantDbPrefix(): string
    {
        $prefix = env('TENANT_DB_PREFIX');
        if ($prefix !== null && $prefix !== '') {
            return $prefix;
        }

        // Auto-detect cPanel prefix from central or default database name (e.g. thesmart_lara319 -> thesmart_)
        $dbName = (string) (config('database.connections.central.database') ?: config('database.connections.mysql.database', ''));
        if (str_contains($dbName, '_')) {
            $parts = explode('_', $dbName);
            return $parts[0] . '_';
        }

        $dbUser = (string) (config('database.connections.central.username') ?: config('database.connections.mysql.username', ''));
        if (str_contains($dbUser, '_')) {
            $parts = explode('_', $dbUser);
            return $parts[0] . '_';
        }

        return 'pms_';
    }

    /**
     * Show form to create a new tenant company.
     */
    public function create(): View
    {
        $dbPrefix = $this->getTenantDbPrefix();
        return view('superadmin.companies.create', compact('dbPrefix'));
    }

    /**
     * Store and provision a new tenant company database and default admin.
     */
    public function store(Request $request): RedirectResponse
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'slug'                => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'email'               => ['required', 'string', 'max:255', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'unique:central.companies,email'],
            'phone'               => [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !\App\Support\CountryPhone::validateFormattedNumber($value)) {
                        $fail(\App\Support\CountryPhone::getValidationErrorMessage($value));
                    }
                }
            ],
            'address'             => ['nullable', 'string', 'max:1000'],
            'company_logo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
            'admin_name'          => ['required', 'string', 'max:255'],
            'admin_email'         => ['required', 'string', 'max:255', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'admin_password'      => ['required', 'string', 'min:8', 'max:128'],
            'admin_password_confirmation' => ['nullable', 'string', 'same:admin_password'],
            'admin_profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
        ], [
            'email.regex'         => 'Please enter a valid company contact email address (e.g. contact@company.com).',
            'admin_email.regex'   => 'Please enter a valid admin email address (e.g. admin@company.com).',
            'admin_password.min'  => 'Admin password must be at least 8 characters long.',
            'admin_password.max'  => 'Admin password may not be greater than 128 characters.',
            'admin_password_confirmation.same' => 'Password confirmation does not match.',
        ]);

        // Resolve before provisioning so an invalid paid selection cannot become Free.
        $targetPlan = app(\App\Services\ProvisioningPlan::class)->resolve($request);
        $rawSlug = strtolower(trim($data['slug']));
        $slug = preg_replace('/[^a-z0-9_]/', '', $rawSlug);

        $dbPrefix = $this->getTenantDbPrefix();
        if ($dbPrefix && str_starts_with($slug, $dbPrefix)) {
            $dbName = $slug;
        } else {
            $dbName = $dbPrefix . $slug;
        }

        // Check if DB name or company code already exists in central registry
        $existing = Company::on('central')->where('db_name', $dbName)
            ->orWhere('company_code', strtoupper($slug))
            ->first();

        if ($existing) {
            return back()->withErrors([
                'slug' => "Company with database identifier '{$dbName}' already exists.",
            ])->withInput();
        }

        // Handle Company Logo upload
        $logoPath = null;
        if ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $dir = public_path('uploads/company_logos');
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $filename = uniqid('logo_', true) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $logoPath = 'uploads/company_logos/' . $filename;
        }

        // Handle Admin Profile Image upload
        $adminProfileImagePath = null;
        if ($request->hasFile('admin_profile_image')) {
            $file = $request->file('admin_profile_image');
            $dir = public_path('uploads/admin_avatars');
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $filename = uniqid('admin_', true) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $adminProfileImagePath = 'uploads/admin_avatars/' . $filename;
        }

        // 1. Create or verify physical MySQL database matching utf8mb4_general_ci charset
        $dbVerified = false;
        try {
            $pdo = DB::connection('central')->getPdo();
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $dbVerified = true;
        } catch (\Throwable $e) {
            // On shared hosting (cPanel), MySQL user might not have CREATE DATABASE SQL privilege.
            // Check if database was already created in cPanel MySQL Databases
            try {
                $baseConn = config('database.connections.central') ?: (config('database.connections.tenant') ?: config('database.connections.mysql'));
                config([
                    'database.connections.test_tenant' => array_merge($baseConn, [
                        'database' => $dbName,
                    ])
                ]);
                DB::purge('test_tenant');
                DB::connection('test_tenant')->getPdo();
                $dbVerified = true;
            } catch (\Throwable $ex) {
                try {
                    $baseConn = config('database.connections.tenant') ?: config('database.connections.mysql');
                    config([
                        'database.connections.test_tenant' => array_merge($baseConn, [
                            'database' => $dbName,
                        ])
                    ]);
                    DB::purge('test_tenant');
                    DB::connection('test_tenant')->getPdo();
                    $dbVerified = true;
                } catch (\Throwable $ex2) {
                    $dbVerified = false;
                }
            }

            if (! $dbVerified) {
                $tenantUser = config('database.connections.tenant.username') ?: config('database.connections.mysql.username', 'thesmart_lara319');
                return back()->withErrors([
                    'error' => "Cannot create MySQL database '{$dbName}' automatically due to cPanel shared hosting privileges.\n\n" .
                               "To complete provisioning:\n" .
                               "1. Go to your cPanel -> 'MySQL Databases'.\n" .
                               "2. Under 'Create New Database', create: '{$dbName}'\n" .
                               "3. Under 'Add User To Database', select user '{$tenantUser}' and database '{$dbName}', check 'ALL PRIVILEGES' and save.\n" .
                               "4. Submit this form again — PMS will automatically migrate and configure your company!",
                ])->withInput();
            }
        }

        // 2. Register Company in central database
        $company = Company::on('central')->create([
            'company_code'   => strtoupper($slug),
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => $data['admin_password'],
            'phone'          => $data['phone'] ?? null,
            'address'        => $data['address'] ?? null,
            'logo'           => $logoPath,
            'db_name'        => $dbName,
            'status'         => 'active',
            'max_users'      => 100,
            'max_projects'   => 50,
            'max_clients'    => 100,
            'max_storage_mb' => 10000,
        ]);

        // Provision central subscription record for the new tenant based on selected plan
        try {
            DB::connection('central')->transaction(function () use ($company, $targetPlan) {
                $startsAt = now();
                $endsAt = now()->addDays(30);

                /** @var \App\Services\SubscriptionService $subService */
                $subService = app(\App\Services\SubscriptionService::class);

                if ($targetPlan && strtolower($targetPlan->slug) !== 'free') {
                    $sub = $subService->activateOrUpgradePlan(
                        company: $company,
                        plan: $targetPlan,
                        billingCycle: 'monthly',
                        performedBy: auth('super_admin')->user()?->name ?? auth()->user()?->name ?? 'Super Admin Provisioning'
                    );

                    $sub->update([
                        'starts_at' => $startsAt->toDateString(),
                        'ends_at'   => $endsAt->toDateString(),
                        'status'    => 'active',
                    ]);

                    $company->update([
                        'max_users'          => $targetPlan->max_users > 0 ? $targetPlan->max_users : 999999,
                        'max_storage_mb'     => $targetPlan->max_storage_mb > 0 ? $targetPlan->max_storage_mb : 512000,
                        'status'             => 'active',
                        'trial_ends_at'      => $endsAt,
                        'highest_plan_level' => \App\Services\PlanEligibilityService::getPlanLevel($targetPlan),
                        'highest_plan_slug'  => strtolower($targetPlan->slug),
                    ]);
                } else {
                    $sub = $subService->initializeTrial($company);
                    $sub->update([
                        'starts_at'     => $startsAt->toDateString(),
                        'ends_at'       => $endsAt->toDateString(),
                        'trial_ends_at' => $endsAt->toDateString(),
                        'status'        => 'trial',
                    ]);
                    $company->update([
                        'status'        => 'trial',
                        'trial_ends_at' => $endsAt,
                        'max_users'     => $targetPlan->max_users > 0 ? $targetPlan->max_users : 999999,
                        'max_storage_mb' => $targetPlan->max_storage_mb > 0 ? $targetPlan->max_storage_mb : 512000,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Central subscription creation info: " . $e->getMessage());

            if (isset($targetPlan) && $targetPlan) {
                $company->refresh();
                $targetLevel = \App\Services\PlanEligibilityService::getPlanLevel($targetPlan);
                \App\Models\Central\Subscription::on('central')->create([
                    'company_id'         => $company->id,
                    'plan_id'            => $targetPlan->id,
                    'billing_cycle'      => 'monthly',
                    'starts_at'          => now()->toDateString(),
                    'ends_at'            => now()->addDays(30)->toDateString(),
                    'price'              => $targetPlan->monthly_price ?? 0,
                    'status'             => strtolower($targetPlan->slug) === 'free' ? 'trial' : 'active',
                    'auto_renew'         => true,
                    'highest_plan_level' => $targetLevel,
                    'current_plan_level' => $targetLevel,
                    'activated_at'       => now(),
                ]);

                $company->forceFill([
                    'status'             => strtolower($targetPlan->slug) === 'free' ? 'trial' : 'active',
                    'trial_ends_at'      => now()->addDays(30),
                    'highest_plan_level' => $targetLevel,
                    'highest_plan_slug'  => strtolower($targetPlan->slug),
                    'max_users'          => $targetPlan->max_users > 0 ? $targetPlan->max_users : 999999,
                    'max_storage_mb'     => $targetPlan->max_storage_mb > 0 ? $targetPlan->max_storage_mb : 512000,
                ])->save();
            }
        }

        // 3. Configure tenant connection dynamically and run tenant migrations
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');

        $exitCode = Artisan::call('migrate', [
            '--path'     => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force'    => true,
        ]);

        if ($exitCode !== 0) {
            return back()->withErrors([
                'error' => "Migration failed for database '{$dbName}':\n" . Artisan::output(),
            ])->withInput();
        }

        // 4. Seed default admin user into the new tenant DB and primary DB
        $companyEmail = strtolower(trim($data['email']));
        $adminEmail = !empty($data['admin_email']) ? strtolower(trim($data['admin_email'])) : $companyEmail;
        $adminName = !empty($data['admin_name']) ? trim($data['admin_name']) : ($data['name'] . ' Admin');

        // Create Admin User in Primary MySQL DB (if applicable)
        try {
            User::syncCompanyToConnection('mysql', $company);
            User::on('mysql')->create([
                'company_id'    => $company->id,
                'name'          => $adminName,
                'email'         => $adminEmail,
                'password'      => Hash::make($data['admin_password']),
                'raw_password'  => $data['admin_password'],
                'profile_image' => $adminProfileImagePath,
                'role'          => 'admin',
                'is_active'     => true,
                'login_allowed' => true,
                'email_notifications' => true,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info("Primary DB admin creation info: " . $e->getMessage());
        }

        // Ensure Company record exists in Tenant DB
        User::syncCompanyToConnection('tenant', $company);

        // Create Admin User in Tenant DB
        User::on('tenant')->create([
            'company_id'    => $company->id,
            'name'          => $adminName,
            'email'         => $adminEmail,
            'password'      => Hash::make($data['admin_password']),
            'raw_password'  => $data['admin_password'],
            'profile_image' => $adminProfileImagePath,
            'role'          => 'admin',
            'is_active'     => true,
            'login_allowed' => true,
            'email_notifications' => true,
        ]);

        if ($adminEmail !== $companyEmail) {
            User::on('tenant')->firstOrCreate(
                ['email' => $companyEmail],
                [
                    'company_id'    => $company->id,
                    // The company-email login belongs to the same admin: use the name entered on the provision form.
                    'name'          => $adminName,
                    'password'      => Hash::make($data['admin_password']),
                    'raw_password'  => $data['admin_password'],
                    'profile_image' => $adminProfileImagePath,
                    'role'          => 'admin',
                    'is_active'     => true,
                    'login_allowed' => true,
                ]
            );
        }

        return redirect()->route('super-admin.companies.index')
            ->with('success', "Tenant Company '{$company->name}' created successfully with database '{$dbName}'.");
    }

    /**
     * Show edit screen or redirect to company workspace settings.
     */
    public function edit($id): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        return redirect()->route('super-admin.companies.show', ['company' => $id, 'tab' => 'tab-settings']);
    }

    /**
     * Update tenant company profile, limits, branding, and prefixes.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        try {
            $company = Company::on('central')->findOrFail($id);
        } catch (\Throwable $e) {
            $company = \App\Models\Company::findOrFail($id);
        }

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'short_name'          => ['nullable', 'string', 'max:100'],
            'company_code'        => ['nullable', 'string', 'max:50'],
            'email'               => [
                'required',
                'string',
                'max:255',
                'email:rfc',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                \Illuminate\Validation\Rule::unique('central.companies', 'email')->ignore($company->id),
            ],
            'phone'               => ['nullable', 'string', 'max:50', new \App\Rules\CompanyPhoneNumber],
            'website'             => ['nullable', 'string', 'max:255'],
            'domain'              => ['nullable', 'string', 'max:255'],
            'subdomain'           => ['nullable', 'string', 'max:100'],
            'address'             => ['nullable', 'string', 'max:1000'],
            'company_logo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
            'max_users'           => ['nullable', 'integer', 'min:1'],
            'max_projects'        => ['nullable', 'integer', 'min:0'],
            'max_clients'         => ['nullable', 'integer', 'min:0'],
            'max_storage_mb'      => ['nullable', 'integer', 'min:50'],
            'gst_number'          => ['nullable', 'string', 'max:50'],
            'pan_number'          => ['nullable', 'string', 'max:50'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'employee_id_prefix'  => ['nullable', 'string', 'max:20'],
            'leave_prefix'        => ['nullable', 'string', 'max:20'],
            'payroll_prefix'      => ['nullable', 'string', 'max:20'],
            'payslip_prefix'      => ['nullable', 'string', 'max:20'],
            'password'            => ['nullable', 'string', 'min:8', 'max:128'],
            'password_confirmation' => ['nullable', 'string', 'same:password'],
        ], [
            'email.regex'  => 'Please enter a valid company contact email address.',
            'email.unique' => 'A company with this email address is already registered.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.max' => 'Password may not be greater than 128 characters.',
            'password_confirmation.same' => 'Password confirmation does not match.',
        ]);

        // Handle Company Logo upload
        if ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $dir = public_path('uploads/company_logos');
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $filename = uniqid('logo_', true) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $data['logo'] = 'uploads/company_logos/' . $filename;
        }

        // Clean & prepare update array
        $updateFields = [
            'name'                => $data['name'],
            'short_name'          => $data['short_name'] ?? null,
            'company_code'        => !empty($data['company_code']) ? strtoupper(trim($data['company_code'])) : null,
            'email'               => $data['email'],
            'phone'               => $data['phone'] ?? null,
            'website'             => $data['website'] ?? null,
            'domain'              => $data['domain'] ?? null,
            'subdomain'           => $data['subdomain'] ?? null,
            'address'             => $data['address'] ?? null,
            'max_users'           => isset($data['max_users']) ? (int) $data['max_users'] : ($company->max_users ?? 10),
            'max_projects'        => isset($data['max_projects']) ? (int) $data['max_projects'] : ($company->max_projects ?? 5),
            'max_clients'         => isset($data['max_clients']) ? (int) $data['max_clients'] : ($company->max_clients ?? 50),
            'max_storage_mb'      => isset($data['max_storage_mb']) ? (int) $data['max_storage_mb'] : ($company->max_storage_mb ?? 1024),
            'gst_number'          => $data['gst_number'] ?? null,
            // PAN is no longer edited on this form; keep the stored value unless a value is explicitly submitted.
            'pan_number'          => array_key_exists('pan_number', $data) ? $data['pan_number'] : $company->pan_number,
            'registration_number' => $data['registration_number'] ?? null,
            'employee_id_prefix'  => $data['employee_id_prefix'] ?? null,
            'leave_prefix'        => $data['leave_prefix'] ?? null,
            'payroll_prefix'      => $data['payroll_prefix'] ?? null,
            'payslip_prefix'      => $data['payslip_prefix'] ?? null,
        ];

        if (isset($data['logo'])) {
            $updateFields['logo'] = $data['logo'];
        }

        if (!empty($data['password'])) {
            $updateFields['password'] = $data['password'];
        }

        // 1. Update Central Company
        $company->update($updateFields);

        try {
            \App\Models\Company::where('id', $company->id)->update($updateFields);
        } catch (\Throwable $e) {}

        // 2. Sync to Tenant DB if available and connected
        if (!empty($company->db_name)) {
            try {
                config(['database.connections.tenant.database' => $company->db_name]);
                DB::purge('tenant');

                if (DB::connection('tenant')->getSchemaBuilder()->hasTable('companies')) {
                    $tenantCompany = DB::connection('tenant')->table('companies')->first();
                    if ($tenantCompany) {
                        $tenantFields = [];
                        foreach ($updateFields as $col => $val) {
                            if (DB::connection('tenant')->getSchemaBuilder()->hasColumn('companies', $col)) {
                                $tenantFields[$col] = $val;
                            }
                        }
                        if (!empty($tenantFields)) {
                            DB::connection('tenant')->table('companies')->where('id', $tenantCompany->id)->update($tenantFields);
                        }
                    }
                }

                // If password was updated, update admin user password in tenant database
                if (!empty($data['password'])) {
                    $hashed = Hash::make($data['password']);
                    if (DB::connection('tenant')->getSchemaBuilder()->hasTable('users')) {
                        DB::connection('tenant')->table('users')
                            ->where(function($q) use ($company) {
                                $q->where('email', $company->email)
                                  ->orWhere('role', 'admin');
                            })
                            ->update([
                                'password' => $hashed,
                                'raw_password' => $data['password'],
                            ]);
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 3. If password was updated, update in primary mysql connection users
        if (!empty($data['password'])) {
            try {
                $hashed = Hash::make($data['password']);
                User::on('mysql')->where(function($q) use ($company) {
                    $q->where('company_id', $company->id)
                      ->orWhere('email', $company->email);
                })->update([
                    'password' => $hashed,
                    'raw_password' => $data['password'],
                ]);
            } catch (\Throwable $e) {}
        }

        // 4. Log super admin activity
        try {
            \App\Models\Central\SuperAdminActivityLog::create([
                'super_admin_id' => auth('super_admin')->id() ?? (auth()->id() ?? 1),
                'company_id'     => $company->id,
                'action'         => 'company.updated',
                'description'    => "Updated company profile & workspace settings for '{$company->name}' (Tenant #{$company->id})",
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {}

        return redirect()->route('super-admin.companies.show', ['company' => $company->id, 'tab' => 'tab-settings'])
            ->with('success', "Company '{$company->name}' updated successfully.");
    }
}
