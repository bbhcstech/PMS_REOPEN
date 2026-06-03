<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Invoice;
use App\Models\Module;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'superadmin', 403);
    }

    public function dashboard(Request $request): View
    {
        $this->authorizeSuperAdmin();

        $stats = [
            'companies' => Company::count(),
            'active_companies' => Company::where('status', 'active')->count(),
            'expiring_soon' => CompanySubscription::where('status', 'active')
                ->whereBetween('ends_at', [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->count(),
            'company_admins' => User::where('role', 'admin')->count(),
            'users' => User::whereIn('role', ['admin', 'employee', 'client'])->count(),
            'monthly_revenue' => Payment::where('status', 'completed')
                ->whereBetween('paid_at', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('amount'),
        ];

        $companies = Company::with(['activeSubscription.plan', 'users' => function ($query) {
                $query->where('role', 'admin')->latest()->limit(2);
            }])
            ->latest()
            ->paginate(8, ['*'], 'companies_page');

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $modules = Module::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $companyOptions = Company::orderBy('name')->get();
        $recentAdmins = User::where('role', 'admin')->with('company')->latest()->take(6)->get();
        $recentInvoices = Invoice::with('company')->latest()->take(5)->get();
        $recentActivities = AuditLog::with(['company', 'user'])->latest()->take(8)->get();

        return view('superadmin.dashboard', compact(
            'stats',
            'companies',
            'plans',
            'modules',
            'companyOptions',
            'recentAdmins',
            'recentInvoices',
            'recentActivities'
        ));
    }

    public function companyAdmins(Request $request): View
    {
        $this->authorizeSuperAdmin();

        $adminPerPage = (int) $request->input('admin_per_page', 10);
        if (! in_array($adminPerPage, [10, 20, 30, 40, 50], true)) {
            $adminPerPage = 10;
        }

        $adminSearch = trim((string) $request->input('admin_search', ''));
        $adminStatus = $request->input('admin_status', 'active');

        $companyAdminsQuery = User::where('role', 'admin')
            ->with('company')
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

        $companyAdmins = $companyAdminsQuery
            ->latest()
            ->paginate($adminPerPage)
            ->appends($request->query());

        $companyOptions = Company::orderBy('name')->get();

        $adminStats = [
            'total' => User::where('role', 'admin')->count(),
            'active' => User::where('role', 'admin')->whereNull('archived_at')->count(),
            'archived' => User::where('role', 'admin')->whereNotNull('archived_at')->count(),
            'blocked' => User::where('role', 'admin')->where('login_allowed', false)->count(),
        ];

        return view('superadmin.company-admins', compact(
            'companyAdmins',
            'companyOptions',
            'adminPerPage',
            'adminSearch',
            'adminStatus',
            'adminStats'
        ));
    }

    public function storeCompany(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:companies,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:companies,domain'],
            'subdomain' => ['nullable', 'string', 'max:255', 'unique:companies,subdomain'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:active,suspended,trial,inactive'],
            'plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required_with:plan_id', 'in:monthly,yearly'],
            'trial_ends_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_projects' => ['nullable', 'integer', 'min:0'],
            'max_clients' => ['nullable', 'integer', 'min:0'],
            'max_storage_mb' => ['nullable', 'integer', 'min:0'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
            'module_ids' => ['array'],
            'module_ids.*' => ['exists:modules,id'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $company = Company::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'domain' => $data['domain'] ?? null,
                'subdomain' => $data['subdomain'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => $data['status'],
                'trial_ends_at' => $data['trial_ends_at'] ?? null,
                'max_users' => $data['max_users'] ?? 10,
                'max_projects' => $data['max_projects'] ?? 5,
                'max_clients' => $data['max_clients'] ?? 50,
                'max_storage_mb' => $data['max_storage_mb'] ?? 1024,
            ]);

            User::create([
                'company_id' => $company->id,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'role' => 'admin',
                'is_active' => true,
                'login_allowed' => true,
                'email_notifications' => true,
            ]);

            if (! empty($data['plan_id'])) {
                $plan = SubscriptionPlan::findOrFail($data['plan_id']);
                $cycle = $data['billing_cycle'] ?? 'monthly';

                CompanySubscription::create([
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'billing_cycle' => $cycle,
                    'starts_at' => now()->toDateString(),
                    'ends_at' => $data['ends_at'] ?? now()->addMonth()->toDateString(),
                    'trial_ends_at' => $data['trial_ends_at'] ?? null,
                    'price' => $plan->getPriceForBillingCycle($cycle),
                    'status' => 'active',
                    'auto_renew' => true,
                ]);
            }

            $moduleIds = $request->input('module_ids', []);
            if (! empty($moduleIds)) {
                $company->modules()->syncWithPivotValues($moduleIds, ['is_enabled' => true]);
            }

            $this->logAction('company.created', $company, ['admin_email' => $data['admin_email']]);
        });

        return back()->with('success', 'Company and company admin created successfully.');
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
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
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'login_allowed' => ['nullable', 'boolean'],
            'email_notifications' => ['nullable', 'boolean'],
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

        $admin->delete();
        $this->logAction('company_admin.deleted', $company, $details);

        return back()->with('success', 'Company admin deleted successfully.');
    }

    public function exportAdmins(Request $request)
    {
        $this->authorizeSuperAdmin();

        $adminSearch = trim((string) $request->input('admin_search', ''));
        $adminStatus = $request->input('admin_status', 'active');

        $admins = User::where('role', 'admin')
            ->with('company')
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
        $company->update(['status' => $data['status']]);

        $this->logAction('company.status_updated', $company, [
            'old_status' => $oldStatus,
            'new_status' => $data['status'],
        ]);

        return back()->with('success', 'Company status updated.');
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
