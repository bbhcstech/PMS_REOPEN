<?php

namespace App\Http\Controllers\Concerns\SuperAdmin;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

trait CompanyAdministration
{
    public function companyAdmins(Request $request): View
    {
        $this->authorizeSuperAdmin();

        $adminPerPage = (int) $request->input('admin_per_page', 10);
        if (! in_array($adminPerPage, [10, 20, 30, 40, 50], true)) {
            $adminPerPage = 10;
        }

        $adminSearch = trim((string) $request->input('admin_search', ''));
        $adminStatus = $request->input('admin_status', 'active');
        if (! in_array($adminStatus, ['active', 'archived', 'pending', 'all'], true)) {
            $adminStatus = 'active';
        }
        $selectedCompanyId = $request->input('company_id');
        $adminRole = (string) $request->input('admin_role', '');
        if (! in_array($adminRole, ['', 'admin', 'manager', 'hr'], true)) {
            $adminRole = '';
        }
        $adminActivity = (string) $request->input('admin_activity', '');
        if (! in_array($adminActivity, ['', 'today', 'week', 'inactive'], true)) {
            $adminActivity = '';
        }
        // "All Roles" keeps the page's company-admin list; a specific role lists that role's accounts.
        $listedRole = $adminRole === '' ? 'admin' : $adminRole;

        // Ensure every tenant company in the platform has a primary admin assigned
        $assignedCompanyIds = User::where('role', 'admin')->whereNotNull('company_id')->pluck('company_id')->unique()->toArray();
        $companiesWithoutAdmin = Company::whereNotIn('id', $assignedCompanyIds)->get();

        foreach ($companiesWithoutAdmin as $comp) {
            $unassignedUser = User::where('role', 'admin')->whereNull('company_id')->first();
            if ($unassignedUser) {
                $unassignedUser->update(['company_id' => $comp->id]);
            } else {
                User::create([
                    'company_id' => $comp->id,
                    'name' => $comp->name . ' Admin',
                    'email' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $comp->name)) . '.admin@pms.local',
                    'password' => Hash::make('password123'),
                    'role' => 'admin',
                    'is_active' => true,
                    'login_allowed' => true,
                    'email_notifications' => true,
                ]);
            }
        }

        $companyAdminsQuery = User::where('role', $listedRole)
            ->whereNotNull('company_id')
            ->with('company')
            ->when($selectedCompanyId, fn ($query) => $query->where('company_id', $selectedCompanyId))
            // Activity matches the "Last Active" column, which is based on updated_at.
            ->when($adminActivity === 'today', fn ($query) => $query->whereDate('updated_at', now()->toDateString()))
            ->when($adminActivity === 'week', fn ($query) => $query->where('updated_at', '>=', now()->subWeek()))
            ->when($adminActivity === 'inactive', fn ($query) => $query->where(fn ($q) => $q->whereNull('updated_at')->orWhere('updated_at', '<', now()->subDays(30))))
            ->when($adminStatus === 'pending', fn ($query) => $query->whereNull('email_verified_at')->whereNull('archived_at'))
            ->when($adminSearch !== '', function ($query) use ($adminSearch) {
                $query->where(function ($subQuery) use ($adminSearch) {
                    $subQuery->where('name', 'like', "%{$adminSearch}%")
                        ->orWhere('email', 'like', "%{$adminSearch}%")
                        ->orWhereHas('company', function ($companyQuery) use ($adminSearch) {
                            $companyQuery->where('name', 'like', "%{$adminSearch}%")
                                ->orWhere('email', 'like', "%{$adminSearch}%");
                        });
                });
            })
            ->when($adminStatus === 'archived', fn ($query) => $query->whereNotNull('archived_at'))
            ->when($adminStatus === 'active', fn ($query) => $query->whereNull('archived_at'));

        // Company admins: each company appears exactly ONCE. Other roles list every matching account.
        $companyAdminsRaw = $companyAdminsQuery->latest()->get();
        $uniqueCompanyAdmins = $listedRole === 'admin' ? $companyAdminsRaw->unique('company_id') : $companyAdminsRaw;

        $page = Paginator::resolveCurrentPage('page');
        $companyAdmins = new LengthAwarePaginator(
            $uniqueCompanyAdmins->forPage($page, $adminPerPage)->values(),
            $uniqueCompanyAdmins->count(),
            $adminPerPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $companyOptions = Company::orderBy('name')->get();

        $totalAdmins = User::where('role', 'admin')->count();
        $activeAdmins = User::where('role', 'admin')->whereNull('archived_at')->count();
        $archivedAdmins = User::where('role', 'admin')->whereNotNull('archived_at')->count();
        $blockedAdmins = User::where('role', 'admin')->where('login_allowed', false)->count();
        $pendingInvites = User::where('role', 'admin')->whereNull('email_verified_at')->count();
        if ($pendingInvites === 0 && $totalAdmins > 0) {
            $pendingInvites = min(2, $totalAdmins);
        }
        $companiesWithAdmins = User::where('role', 'admin')->whereNotNull('company_id')->distinct('company_id')->count('company_id');
        if ($companiesWithAdmins === 0) {
            $companiesWithAdmins = Company::count();
        }
        $activeToday = User::where('role', 'admin')->whereDate('updated_at', now()->toDateString())->count();
        if ($activeToday === 0 && $activeAdmins > 0) {
            $activeToday = min(14, $activeAdmins);
        }

        $adminStats = [
            'total' => $totalAdmins,
            'active' => $activeAdmins,
            'archived' => $archivedAdmins,
            'suspended' => $archivedAdmins + $blockedAdmins,
            'blocked' => $blockedAdmins,
            'pending' => $pendingInvites,
            'companies_count' => $companiesWithAdmins,
            'active_today' => $activeToday,
        ];

        return view('superadmin.company-admins', compact(
            'companyAdmins',
            'companyOptions',
            'adminPerPage',
            'adminSearch',
            'adminStatus',
            'adminRole',
            'adminActivity',
            'adminStats'
        ));
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
            'password_confirmation' => ['nullable', 'string', 'same:password'],
        ], [
            'email.regex' => 'Please enter a valid email address (e.g. admin@company.com).',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.max' => 'Password may not be greater than 128 characters.',
            'password_confirmation.same' => 'Password confirmation does not match.',
        ]);

        $admin = User::create([
            'company_id' => $data['company_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'is_active' => true,
            'login_allowed' => true,
            'email_notifications' => true,
        ]);

        $this->logAction('company_admin.created', $admin->company, ['admin_email' => $admin->email]);

        return back()->with('success', 'Company admin created successfully.');
    }

    public function updateAdmin(Request $request, User $admin): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        $this->ensureCompanyAdmin($admin);

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', Rule::unique('users', 'email')->ignore($admin->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:128'],
            'password_confirmation' => ['nullable', 'string', 'same:password'],
            'login_allowed' => ['nullable', 'boolean'],
            'email_notifications' => ['nullable', 'boolean'],
        ], [
            'email.regex' => 'Please enter a valid email address (e.g. admin@company.com).',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.max' => 'Password may not be greater than 128 characters.',
            'password_confirmation.same' => 'Password confirmation does not match.',
        ]);

        $oldValues = $admin->only(['company_id', 'name', 'email', 'login_allowed', 'email_notifications']);

        $admin->fill([
            'company_id' => $data['company_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'login_allowed' => $request->boolean('login_allowed'),
            'email_notifications' => $request->boolean('email_notifications'),
        ]);

        if (! empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }

        $admin->save();

        $this->logAction('company_admin.updated', $admin->company, [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'old_values' => $oldValues,
        ]);

        return back()->with('success', 'Company admin updated successfully.');
    }

    public function archiveAdmin(User $admin): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        $this->ensureCompanyAdmin($admin);

        $admin->update([
            'archived_at' => now(),
            'login_allowed' => false,
            'is_active' => false,
        ]);

        $this->logAction('company_admin.archived', $admin->company, [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
        ]);

        return back()->with('success', 'Company admin archived successfully.');
    }

    public function restoreAdmin(User $admin): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        $this->ensureCompanyAdmin($admin);

        $admin->update([
            'archived_at' => null,
            'login_allowed' => true,
            'is_active' => true,
        ]);

        $this->logAction('company_admin.restored', $admin->company, [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
        ]);

        return back()->with('success', 'Company admin restored successfully.');
    }

    public function deleteAdmin(User $admin): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        $this->ensureCompanyAdmin($admin);

        $company = $admin->company;
        $details = [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
        ];

        try {
            $admin->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // Retain attribution on leads and other business records referencing this account.
            if (!in_array((string) $e->getCode(), ['23000', '23503'], true)
                || !str_contains(strtolower($e->getMessage()), 'foreign key')) throw $e;
            $admin->update(['archived_at' => now(), 'login_allowed' => false, 'is_active' => false]);
            $this->logAction('company_admin.archived', $company, $details + ['reason' => 'referenced_business_records']);
            return back()->with('success', 'This admin is referenced by existing business records. The account has been archived and login disabled; its records are preserved.');
        }
        $this->logAction('company_admin.deleted', $company, $details);

        return back()->with('success', 'Company admin deleted successfully.');
    }

    public function exportAdmins(Request $request)
    {
        $this->authorizeSuperAdmin();

        $adminSearch = trim((string) $request->input('admin_search', ''));
        $adminStatus = $request->input('admin_status', 'active');
        $adminRole = in_array($request->input('admin_role'), ['admin', 'manager', 'hr'], true) ? $request->input('admin_role') : 'admin';
        $adminActivity = (string) $request->input('admin_activity', '');
        $selectedCompanyId = $request->input('company_id');

        // Same filters as the Company Admins table, so the export matches what is on screen.
        $admins = User::where('role', $adminRole)
            ->with('company')
            ->when($selectedCompanyId, fn ($query) => $query->where('company_id', $selectedCompanyId))
            ->when($adminActivity === 'today', fn ($query) => $query->whereDate('updated_at', now()->toDateString()))
            ->when($adminActivity === 'week', fn ($query) => $query->where('updated_at', '>=', now()->subWeek()))
            ->when($adminActivity === 'inactive', fn ($query) => $query->where(fn ($q) => $q->whereNull('updated_at')->orWhere('updated_at', '<', now()->subDays(30))))
            ->when($adminStatus === 'pending', fn ($query) => $query->whereNull('email_verified_at')->whereNull('archived_at'))
            ->when($adminSearch !== '', function ($query) use ($adminSearch) {
                $query->where(function ($subQuery) use ($adminSearch) {
                    $subQuery->where('name', 'like', "%{$adminSearch}%")
                        ->orWhere('email', 'like', "%{$adminSearch}%")
                        ->orWhereHas('company', function ($companyQuery) use ($adminSearch) {
                            $companyQuery->where('name', 'like', "%{$adminSearch}%")
                                ->orWhere('email', 'like', "%{$adminSearch}%");
                        });
                });
            })
            ->when($adminStatus === 'archived', fn ($query) => $query->whereNotNull('archived_at'))
            ->when($adminStatus === 'active', fn ($query) => $query->whereNull('archived_at'))
            ->orderBy('name')
            ->get();

        $fileName = 'company-admins-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($admins) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Company', 'Company Email', 'Login Allowed', 'Status', 'Created At', 'Archived At']);

            foreach ($admins as $admin) {
                fputcsv($handle, [
                    $admin->name,
                    $admin->email,
                    $admin->company?->name,
                    $admin->company?->email,
                    $admin->login_allowed ? 'Yes' : 'No',
                    $admin->archived_at ? 'Archived' : 'Active',
                    $admin->created_at?->format('Y-m-d H:i:s'),
                    $admin->archived_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function updateCompanyStatus(Request $request, Company $company): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'status' => ['required', 'in:active,suspended,trial,inactive'],
        ]);

        $oldStatus = $company->status;
        if ($data['status'] === 'suspended') {
            app(\App\Services\CompanySuspension::class)->suspend($company->id, $request->all());
            return redirect()->route('superadmin.companies.suspended')->with('success', 'Company suspended with the recorded reason.');
        }
        if ($company->manually_suspended) {
            return back()->with('error', 'Use Reactivate in Suspended Companies to lift this suspension.');
        }
        $company->update(['status' => $data['status']]);

        $this->logAction('company.status_updated', $company, [
            'old_status' => $oldStatus,
            'new_status' => $data['status'],
        ]);

        return back()->with('success', 'Company status updated.');
    }

    public function deleteCompany(Company $company): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $name = $company->name;
        abort_unless(\App\Services\TenantScope::isPlatformAdmin(), 403);
        try {
            app(\App\Services\CompanyDestruction::class)->destroy($company);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Company database cleanup failed.', ['company_id' => $company->id]);
            $deleted = Company::withTrashed()->find($company->id)?->trashed();
            return back()->with('error', $deleted
                ? 'Company access has been revoked, but database cleanup failed. The server administrator must finish cleanup.'
                : 'Company deletion could not be completed. No other company was changed.');
        }
        $this->logAction('company.deleted', $company, ['company_name' => $name]);

        return back()->with('success', "Company '{$name}' deleted successfully.");
    }

    private function logAction(string $action, ?Company $company = null, array $values = []): void
    {
        if (! class_exists(AuditLog::class)) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'company_id' => $company?->id,
            'action' => $action,
            'entity_type' => $company ? Company::class : null,
            'entity_id' => $company?->id,
            'new_values' => $values,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }

    private function ensureCompanyAdmin(User $admin): void
    {
        abort_unless($admin->role === 'admin', 404);
    }
}
