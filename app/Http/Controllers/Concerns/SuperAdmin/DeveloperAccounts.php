<?php

namespace App\Http\Controllers\Concerns\SuperAdmin;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

trait DeveloperAccounts
{
    public function storeDeveloper(Request $request): RedirectResponse
    {
        @set_time_limit(120);
        config(['mail.mailers.smtp.timeout' => 5]);

        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'personal_email' => ['nullable', 'string', 'max:255', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'mobile' => [
                'nullable',
                'string',
                'max:30',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !\App\Support\CountryPhone::validateFormattedNumber($value)) {
                        $fail(\App\Support\CountryPhone::getValidationErrorMessage($value));
                    }
                }
            ],
            'role' => ['required', 'string', 'max:100'],
            'company_id' => ['nullable', 'integer'],
            'skills' => ['nullable', 'string'],
            'experience' => ['nullable', 'string', 'max:100'],
            'joining_date' => ['nullable', 'date'],
        ], [
            'email.regex' => 'Please enter a valid login email address (e.g. developer@company.com).',
            'personal_email.regex' => 'Please enter a valid personal email address (e.g. name@gmail.com).',
        ]);

        $loginEmail = strtolower(trim($data['email']));
        $personalEmail = !empty($data['personal_email']) ? strtolower(trim($data['personal_email'])) : $loginEmail;

        // Check if developer account ALREADY EXISTS
        $existing = User::where(function ($query) use ($loginEmail) {
            $query->where('email', $loginEmail);
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'personal_email')) {
                $query->orWhere('personal_email', $loginEmail);
            }
        })->first();

        if ($existing) {
            return back()->withErrors(['email' => 'A developer account already exists with email: ' . $loginEmail . '. Use "Assign Work" to assign tasks to this developer.']);
        }

        $compCompanyId = !empty($data['company_id']) ? $data['company_id'] : null;
        $comp = $compCompanyId ? Company::find($compCompanyId) : Company::first();

        $tempPassword = \App\Support\DeveloperPassword::generate();

        $user = User::create([
            'company_id' => $comp?->id,
            'name' => $data['name'],
            'email' => $loginEmail,
            'personal_email' => $personalEmail,
            'mobile' => $data['mobile'] ?? null,
            'role' => 'developer',
            'designation' => $data['role'],
            'password' => Hash::make($tempPassword),
            'raw_password' => $tempPassword,
            'must_change_password' => false,
            'is_active' => true,
            'login_allowed' => true,
            'email_notifications' => true,
            'joining_date' => $data['joining_date'] ?? now()->toDateString(),
        ]);

        $devCode = 'DEV-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);

        try {
            DB::table('employee_details')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'company_id' => $comp?->id,
                    'developer_id' => $devCode,
                    'skills' => $data['skills'] ?? 'Laravel, PHP, MySQL',
                    'experience' => $data['experience'] ?? '2+ Years',
                    'status' => 'available',
                    'joining_date' => $data['joining_date'] ?? now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Throwable $e) {}

        // If company has a tenant database, sync developer record to tenant DB as well
        if ($comp && !empty($comp->db_name)) {
            try {
                config(['database.connections.tenant.database' => $comp->db_name]);
                DB::purge('tenant');

                $tenantDev = User::on('tenant')->where('email', $loginEmail)->first();
                if (!$tenantDev) {
                    User::on('tenant')->create([
                        'company_id' => $comp->id,
                        'name' => $data['name'],
                        'email' => $loginEmail,
                        'personal_email' => $personalEmail,
                        'mobile' => $data['mobile'] ?? null,
                        'role' => 'developer',
                        'designation' => $data['role'],
                        'password' => Hash::make($tempPassword),
                        'raw_password' => $tempPassword,
                        'must_change_password' => false,
                        'is_active' => true,
                        'login_allowed' => true,
                        'joining_date' => $data['joining_date'] ?? now()->toDateString(),
                    ]);
                } else {
                    $tenantDev->role = 'developer';
                    $tenantDev->designation = $data['role'];
                    $tenantDev->password = Hash::make($tempPassword);
                    $tenantDev->raw_password = $tempPassword;
                    $tenantDev->must_change_password = false;
                    $tenantDev->is_active = true;
                    $tenantDev->login_allowed = true;
                    $tenantDev->save();
                }
            } catch (\Throwable $e) {}
        }

        // Send login credentials ONCE to personal email (non-blocking)
        $this->safelyDispatchMail($personalEmail, new \App\Mail\DeveloperAccountCreated($user, $tempPassword, route('login')));

        $this->logAction('developer.created', $comp, [
            'developer_id' => $user->id,
            'developer_email' => $user->email,
        ]);

        return back()->with('success', 'Developer account created successfully for ' . $user->name . '. Temporary Password: ' . $tempPassword);
    }

    public function updateDeveloper(Request $request, $id): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'unique:users,email,' . $id],
            'personal_email' => ['nullable', 'string', 'max:255', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'mobile' => [
                'nullable',
                'string',
                'max:30',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !\App\Support\CountryPhone::validateFormattedNumber($value)) {
                        $fail(\App\Support\CountryPhone::getValidationErrorMessage($value));
                    }
                }
            ],
            'role' => ['required', 'string', 'max:100'],
            'company_id' => ['nullable', 'integer'],
            'skills' => ['nullable', 'string'],
            'experience' => ['nullable', 'string'],
        ], [
            'email.regex' => 'Please enter a valid login email address (e.g. developer@company.com).',
            'personal_email.regex' => 'Please enter a valid personal email address (e.g. name@gmail.com).',
        ]);

        $upCompId = !empty($data['company_id']) ? $data['company_id'] : $user->company_id;

        $user->update([
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'personal_email' => !empty($data['personal_email']) ? strtolower(trim($data['personal_email'])) : $user->personal_email,
            'mobile' => $data['mobile'] ?? $user->mobile,
            'designation' => $data['role'],
            'company_id' => $upCompId,
        ]);

        try {
            DB::table('employee_details')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'company_id' => $user->company_id,
                    'skills' => $data['skills'] ?? 'Laravel, PHP, MySQL',
                    'experience' => $data['experience'] ?? '2+ Years',
                    'updated_at' => now(),
                ]
            );
        } catch (\Throwable $e) {}

        $this->logAction('developer.updated', Company::find($user->company_id), [
            'developer_id' => $user->id,
            'developer_email' => $user->email,
        ]);

        return back()->with('success', 'Developer ' . $user->name . ' updated successfully.');
    }

    public function toggleDeveloperStatus(Request $request, $id): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $user = User::findOrFail($id);
        $newAllowed = !$user->login_allowed;
        $user->update(['login_allowed' => $newAllowed, 'is_active' => $newAllowed]);

        $statusLabel = $newAllowed ? 'activated' : 'deactivated';

        $this->logAction('developer.status_toggled', Company::find($user->company_id), [
            'developer_id' => $user->id,
            'new_status' => $statusLabel,
        ]);

        return back()->with('success', 'Developer ' . $user->name . ' has been ' . $statusLabel . '.');
    }

    /**
     * Enter Developer Workspace in Super Admin Preview Mode
     */
    public function enterDeveloperWorkspace(Request $request, $id): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $developer = User::findOrFail($id);

        session([
            'superadmin_preview_dev_id' => $developer->id,
            'superadmin_preview_active' => true,
        ]);

        try {
            if (Schema::connection('central')->hasTable('super_admin_activity_logs')) {
                DB::connection('central')->table('super_admin_activity_logs')->insert([
                    'super_admin_id' => auth('super_admin')->id() ?? auth()->id() ?? 1,
                    'company_id' => $developer->company_id,
                    'action' => 'workspace_accessed',
                    'description' => 'Super Admin accessed Developer Workspace for ' . $developer->name . ' (' . $developer->email . ')',
                    'meta' => json_encode(['developer_id' => $developer->id, 'action' => 'Workspace Preview']),
                    'ip_address' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 255),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {}

        return redirect()->route('developer.dashboard')->with('success', "Entered {$developer->name}'s developer workspace in Super Admin Preview Mode.");
    }

    /**
     * Exit Developer Workspace Preview Mode
     */
    public function exitDeveloperWorkspace(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        session()->forget(['superadmin_preview_dev_id', 'superadmin_preview_active']);

        return redirect()->route('super-admin.developers.index')->with('success', 'Exited Developer Workspace preview mode.');
    }

    /**
     * Reset Developer Password
     */
    public function resetDeveloperPassword(Request $request, $id): RedirectResponse
    {
        @set_time_limit(120);
        config(['mail.mailers.smtp.timeout' => 5]);

        $this->authorizeSuperAdmin();

        $request->validate([
            'password' => ['nullable', 'string', 'max:128', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()->symbols()],
            'password_confirmation' => ['nullable', 'string', 'same:password'],
        ], [
            'password.min' => 'Password must be at least 8 characters long.',
            'password.max' => 'Password may not be greater than 128 characters.',
            'password_confirmation.same' => 'Password confirmation does not match.',
        ]);

        $developer = User::findOrFail($id);
        $newPassword = $request->input('password') ?: \App\Support\DeveloperPassword::generate();

        $developer->update([
            'password' => Hash::make($newPassword),
            'raw_password' => $newPassword,
            'must_change_password' => false,
        ]);

        if ($developer->company_id) {
            try {
                $devComp = Company::find($developer->company_id);
                if ($devComp && !empty($devComp->db_name)) {
                    config(['database.connections.tenant.database' => $devComp->db_name]);
                    DB::purge('tenant');
                    $tDev = User::on('tenant')->where('email', $developer->email)->first();
                    if ($tDev) {
                        $tDev->password = Hash::make($newPassword);
                        $tDev->raw_password = $newPassword;
                        $tDev->must_change_password = false;
                        $tDev->save();
                    }
                }
            } catch (\Throwable $e) {}
        }

        $targetEmail = $developer->personal_email ?: $developer->email;
        $this->safelyDispatchMail($targetEmail, new \App\Mail\DeveloperAccountCreated($developer, $newPassword, route('login')));

        $this->logAction('developer.password_reset', Company::find($developer->company_id), [
            'developer_id' => $developer->id,
            'developer_name' => $developer->name,
        ]);

        return back()->with('success', "Password for developer {$developer->name} reset successfully. Temporary password: {$newPassword}");
    }

    /**
     * Safely dispatch email without blocking the HTTP request or exceeding execution time limits.
     */
    private function safelyDispatchMail(string $recipientEmail, \Illuminate\Mail\Mailable $mailable): void
    {
        try {
            $queueDriver = config('queue.default');
            if ($queueDriver && $queueDriver !== 'sync') {
                Mail::to($recipientEmail)->queue($mailable);
                return;
            }

            $mailer = config('mail.default');
            if (in_array($mailer, ['log', 'array'], true)) {
                Mail::to($recipientEmail)->send($mailable);
                return;
            }

            if ($mailer === 'smtp') {
                $host = config('mail.mailers.smtp.host');
                $port = (int) (config('mail.mailers.smtp.port') ?: 587);
                if (!empty($host) && $host !== '127.0.0.1' && $host !== 'localhost') {
                    $socket = @fsockopen($host, $port, $errno, $errstr, 1.0);
                    if (!$socket) {
                        \Illuminate\Support\Facades\Log::warning("SMTP server {$host}:{$port} unreachable within 1s. Skipping email to prevent request timeout.");
                        return;
                    }
                    fclose($socket);
                }
            }

            Mail::to($recipientEmail)->send($mailable);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Email dispatch skipped or failed: ' . $e->getMessage());
        }
    }
}
