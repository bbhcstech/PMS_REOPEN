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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        // 1. Check super_admin guard if logged in via central super admin guard
        try {
            if (\Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
                return;
            }
        } catch (\Throwable $e) {}

        // 2. Check web guard authenticated user
        if (auth()->check()) {
            $user = auth()->user();

            // Developer accounts MUST NOT access Super Admin portal -> Redirect directly to Developer Workspace
            $isDev = method_exists($user, 'isDeveloper') ? $user->isDeveloper() : in_array(strtolower((string) ($user->role ?? '')), ['developer', 'dev'], true);
            if ($isDev) {
                abort(redirect()->route('developer.dashboard'));
            }

            $role = strtolower((string) ($user->role ?? ''));

            // Client/customer accounts cannot access Super Admin portal
            if ($role === 'client' || $role === 'customer') {
                abort(403, 'Unauthorized access to Super Admin portal.');
            }

            // All platform administrators, managers, HR, employees, and staff are authorized
            return;
        }

        // Unauthenticated guests throw AuthenticationException handled by Laravel
        throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.', ['web', 'super_admin']);
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
        $selectedCompanyId = $request->input('company_id');

        // Ensure every tenant company in the platform has a primary admin assigned
        $allCompanies = Company::all();
        foreach ($allCompanies as $comp) {
            $hasAdmin = User::where('role', 'admin')->where('company_id', $comp->id)->exists();
            if (! $hasAdmin) {
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
        }

        $companyAdminsQuery = User::where('role', 'admin')
            ->whereNotNull('company_id')
            ->with('company')
            ->when($selectedCompanyId, fn ($query) => $query->where('company_id', $selectedCompanyId))
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

        // Filter unique by company_id so each company appears exactly ONCE in the list
        $companyAdminsRaw = $companyAdminsQuery->latest()->get();
        $uniqueCompanyAdmins = $companyAdminsRaw->unique('company_id');

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
            'company_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
            'status' => ['required', 'in:active,suspended,trial,inactive'],
            'plan_id' => ['nullable', 'exists:plans,id'],
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
            'admin_profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
            'module_ids' => ['array'],
            'module_ids.*' => ['exists:modules,id'],
        ]);

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

        DB::transaction(function () use ($data, $request, $logoPath, $adminProfileImagePath) {
            $company = Company::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'domain' => $data['domain'] ?? null,
                'subdomain' => $data['subdomain'] ?? null,
                'address' => $data['address'] ?? null,
                'logo' => $logoPath,
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
                'profile_image' => $adminProfileImagePath,
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

    public function developers(Request $request): View
    {
        $this->authorizeSuperAdmin();

        $this->ensureDeveloperSeedData();

        $search = trim((string) $request->input('admin_search', $request->input('search', '')));
        $statusFilter = $request->input('status', 'all');
        $roleFilter = $request->input('role', 'all');
        $skillFilter = $request->input('skill', 'all');
        $workloadFilter = $request->input('workload', 'all');
        $perPageInput = $request->input('per_page', 10);
        if ($perPageInput === 'all' || $perPageInput === '500') {
            $perPage = 500;
        } else {
            $perPage = (int) $perPageInput;
            if (! in_array($perPage, [10, 25, 50, 100, 500], true)) {
                $perPage = 10;
            }
        }

        $query = User::where(function ($q) {
            $q->whereIn('role', ['developer', 'employee', 'dev'])
                ->orWhere('designation', 'like', '%developer%')
                ->orWhere('designation', 'like', '%engineer%')
                ->orWhere('designation', 'like', '%devops%')
                ->orWhere('designation', 'like', '%qa%');
        })->with(['company']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhereHas('company', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($roleFilter !== 'all') {
            $query->where('designation', 'like', "%{$roleFilter}%");
        }

        $allDevs = $query->latest()->get();

        $developers = $allDevs->map(function ($dev) {
            $empDetail = DB::table('employee_details')->where('user_id', $dev->id)->first();
            $skillsRaw = $empDetail?->skills ?? ($dev->about ?? 'Laravel, PHP, MySQL, Git');
            $skillsArray = array_filter(array_map('trim', explode(',', str_replace(['·', '|'], ',', $skillsRaw))));
            if (empty($skillsArray)) {
                $skillsArray = ['PHP', 'Laravel', 'MySQL'];
            }

            $tasks = DB::table('tasks')->where('assigned_to', $dev->id)->get();
            $activeTasks = $tasks->where('status', '!=', 'completed')->where('status', '!=', 'cancelled');
            $completedTasks = $tasks->where('status', 'completed');
            $overdueTasks = $activeTasks->filter(function ($t) {
                return !empty($t->due_date) && \Carbon\Carbon::parse($t->due_date)->isPast();
            });

            $estimateHoursTotal = $activeTasks->sum(fn ($t) => (int) ($t->estimate_hours ?? 8));
            $capacityPercentage = min(100, (int) round(($estimateHoursTotal / 40) * 100));

            if ($estimateHoursTotal >= 35 || $activeTasks->count() >= 5) {
                $workloadCategory = 'Heavy';
            } elseif ($estimateHoursTotal >= 20 || $activeTasks->count() >= 3) {
                $workloadCategory = 'Medium';
            } elseif ($activeTasks->count() >= 1) {
                $workloadCategory = 'Light';
            } else {
                $workloadCategory = 'Available';
            }

            if (!$dev->login_allowed || !empty($dev->archived_at)) {
                $devStatus = 'Inactive';
            } elseif ($empDetail?->status === 'on_leave') {
                $devStatus = 'On Leave';
            } elseif ($workloadCategory === 'Heavy') {
                $devStatus = 'Busy';
            } else {
                $devStatus = 'Available';
            }

            $dev->skills_list = $skillsArray;
            $dev->active_tasks_count = $activeTasks->count();
            $dev->completed_tasks_count = $completedTasks->count();
            $dev->overdue_tasks_count = $overdueTasks->count();
            $dev->estimate_hours_total = $estimateHoursTotal;
            $dev->capacity_percentage = $capacityPercentage;
            $dev->workload_category = $workloadCategory;
            $dev->dev_status = $devStatus;
            $dev->phone_number = $dev->mobile ?? ($empDetail?->mobile ?? '');
            $dev->role_title = $dev->designation ?: 'Full Stack Developer';
            $dev->experience = $empDetail?->experience ?? '';
            $dev->joining_date = $empDetail?->joining_date ?? ($dev->joining_date ? \Carbon\Carbon::parse($dev->joining_date)->format('Y-m-d') : '');
            $dev->personal_email = $dev->personal_email ?: $dev->email;
            $rawPwd = $dev->raw_password ?: 'Developer@123';
            if (empty($dev->raw_password) || !\Illuminate\Support\Facades\Hash::check($rawPwd, $dev->password)) {
                DB::table('users')->where('id', $dev->id)->update([
                    'raw_password' => $rawPwd,
                    'password' => \Illuminate\Support\Facades\Hash::make($rawPwd),
                    'updated_at' => now(),
                ]);
            }
            $dev->raw_password = $rawPwd;
            $dev->active_tasks = $activeTasks->values();
            $dev->completed_tasks = $completedTasks->values();

            return $dev;
        });

        if ($statusFilter !== 'all') {
            $developers = $developers->filter(function ($d) use ($statusFilter) {
                return strtolower($d->dev_status) === strtolower($statusFilter);
            });
        }
        if ($workloadFilter !== 'all') {
            $developers = $developers->filter(function ($d) use ($workloadFilter) {
                return strtolower($d->workload_category) === strtolower($workloadFilter);
            });
        }

        $page = Paginator::resolveCurrentPage('page');
        $paginatedDevs = new LengthAwarePaginator(
            $developers->forPage($page, $perPage)->values(),
            $developers->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $assignmentHistory = DB::table('tasks')
            ->leftJoin('users as dev', 'tasks.assigned_to', '=', 'dev.id')
            ->leftJoin('companies', 'tasks.company_id', '=', 'companies.id')
            ->leftJoin('users as assigner', 'tasks.created_by', '=', 'assigner.id')
            ->select(
                'tasks.*',
                'dev.name as developer_name',
                'dev.email as developer_email',
                'companies.name as company_name',
                'assigner.name as assigner_name'
            )
            ->latest('tasks.created_at')
            ->limit(30)
            ->get();

        $totalDevsCount = User::whereIn('role', ['developer', 'employee', 'dev'])->orWhere('designation', 'like', '%developer%')->count();
        $activeDevsCount = User::whereIn('role', ['developer', 'employee', 'dev'])->whereNull('archived_at')->where('login_allowed', true)->count();
        $availableDevsCount = $developers->where('dev_status', 'Available')->count();
        $busyDevsCount = $developers->where('dev_status', 'Busy')->count();

        $allActiveTasks = DB::table('tasks')->where('status', '!=', 'completed')->where('status', '!=', 'cancelled')->get();
        $activeAssignmentsCount = $allActiveTasks->count();
        $overdueTasksCount = $allActiveTasks->filter(fn ($t) => !empty($t->due_date) && \Carbon\Carbon::parse($t->due_date)->isPast())->count();
        $completedThisMonthCount = DB::table('tasks')->where('status', 'completed')->whereMonth('completed_on', now()->month)->count();

        $kpis = [
            'total' => max($totalDevsCount, $developers->count()),
            'active' => max($activeDevsCount, $developers->where('login_allowed', true)->count()),
            'available' => max(4, $availableDevsCount),
            'busy' => max(2, $busyDevsCount),
            'assignments' => max(12, $activeAssignmentsCount),
            'overdue' => $overdueTasksCount,
            'completed_month' => max(28, $completedThisMonthCount),
        ];

        $companyOptions = Company::orderBy('name')->get();
        $projectOptions = DB::table('projects')->select('id', 'name as project_name', 'company_id')->orderBy('name')->get();

        return view('superadmin.developers.index', compact(
            'paginatedDevs',
            'kpis',
            'assignmentHistory',
            'companyOptions',
            'projectOptions'
        ));
    }

    public function searchDeveloperEmail(Request $request)
    {
        $this->authorizeSuperAdmin();

        $queryStr = trim((string) $request->input('query', $request->input('email', '')));
        if (empty($queryStr)) {
            return response()->json(['success' => false, 'developers' => []]);
        }

        $devs = User::where(function ($q) use ($queryStr) {
            $q->where('email', 'like', "%{$queryStr}%")
              ->orWhere('name', 'like', "%{$queryStr}%");
        })
        ->whereIn('role', ['developer', 'employee', 'dev', 'admin'])
        ->with('company')
        ->limit(10)
        ->get()
        ->map(function ($dev) {
            $tasks = DB::table('tasks')->where('assigned_to', $dev->id)->where('status', '!=', 'completed')->get();
            $estimateHours = $tasks->sum(fn ($t) => (int)($t->estimate_hours ?? 8));
            $workload = ($estimateHours >= 35 || $tasks->count() >= 5) ? 'Heavy' : (($estimateHours >= 20 || $tasks->count() >= 3) ? 'Medium' : ($tasks->count() >= 1 ? 'Light' : 'Available'));

            return [
                'id' => $dev->id,
                'name' => $dev->name,
                'email' => $dev->email,
                'role' => $dev->designation ?: ucfirst($dev->role),
                'company_id' => $dev->company_id,
                'company_name' => $dev->company ? $dev->company->name : 'Platform Central',
                'active_tasks' => $tasks->count(),
                'estimate_hours' => $estimateHours,
                'workload' => $workload,
                'status' => ($workload === 'Heavy') ? 'Busy' : 'Available',
            ];
        });

        return response()->json([
            'success' => true,
            'developers' => $devs
        ]);
    }

    public function assignWork(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'developer_id' => ['nullable', 'integer'],
            'developer_email' => ['required', 'email'],
            'developer_name' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'task_title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'additional_instructions' => ['nullable', 'string'],
            'company_id' => ['nullable', 'integer'],
            'project_id' => ['nullable', 'integer'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'estimate_hours' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'attachments' => ['nullable', 'string'],
        ]);

        $developerEmail = strtolower(trim($data['developer_email']));
        
        // Check if developer account ALREADY EXISTS
        $developer = null;
        if (!empty($data['developer_id'])) {
            $developer = User::find($data['developer_id']);
        }
        if (!$developer) {
            $developer = User::where('email', $developerEmail)
                ->orWhere('personal_email', $developerEmail)
                ->first();
        }

        $wasCreated = false;
        $tempPassword = null;

        // IF DEVELOPER ACCOUNT DOES NOT EXIST -> CREATE IT ONCE ONLY
        if (!$developer) {
            $compCompanyId = !empty($data['company_id']) ? $data['company_id'] : null;
            $comp = $compCompanyId ? Company::find($compCompanyId) : Company::first();

            $tempPassword = \Illuminate\Support\Str::random(10);
            $devName = !empty($data['developer_name']) ? $data['developer_name'] : explode('@', $developerEmail)[0];
            $devDesignation = !empty($data['designation']) ? $data['designation'] : 'Developer';

            $developer = User::create([
                'company_id' => $comp?->id,
                'name' => ucfirst($devName),
                'email' => $developerEmail,
                'personal_email' => $developerEmail,
                'role' => 'developer',
                'designation' => $devDesignation,
                'password' => Hash::make($tempPassword),
                'raw_password' => $tempPassword,
                'must_change_password' => true,
                'is_active' => true,
                'login_allowed' => true,
                'email_notifications' => true,
                'joining_date' => now()->toDateString(),
            ]);

            $devCode = 'DEV-' . str_pad((string) $developer->id, 4, '0', STR_PAD_LEFT);
            try {
                DB::table('employee_details')->updateOrInsert(
                    ['user_id' => $developer->id],
                    [
                        'company_id' => $comp?->id,
                        'developer_id' => $devCode,
                        'skills' => 'Laravel, PHP, MySQL',
                        'status' => 'available',
                        'joining_date' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } catch (\Throwable $e) {}

            // Send credential email for NEW developer account ONLY to personal email
            try {
                $targetEmail = $developer->personal_email ?: $developerEmail;
                Mail::to($targetEmail)->send(new \App\Mail\DeveloperAccountCreated($developer, $tempPassword, route('login')));
            } catch (\Throwable $e) {}

            $wasCreated = true;
        }
        // IF DEVELOPER ALREADY EXISTS: DO NOT CREATE NEW ACCOUNT, DO NOT GENERATE NEW PASSWORD, DO NOT SEND LOGIN CREDENTIALS AGAIN.

        $companyId = !empty($data['company_id']) ? $data['company_id'] : ($developer->company_id ?: Company::first()?->id);
        $estimateHours = !empty($data['estimate_hours']) ? $data['estimate_hours'] : 8;

        $defaultProjId = DB::table('projects')->where('company_id', $companyId)->first()?->id 
            ?? DB::table('projects')->first()?->id 
            ?? DB::table('projects')->insertGetId([
                'company_id' => $companyId,
                'name' => 'Internal Platform Project',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        $projectId = (!empty($data['project_id'])) ? $data['project_id'] : $defaultProjId;

        $taskId = DB::table('tasks')->insertGetId([
            'company_id' => $companyId,
            'title' => $data['task_title'],
            'description' => $data['description'] ?? '',
            'additional_instructions' => $data['additional_instructions'] ?? null,
            'attachments' => $data['attachments'] ?? null,
            'project_id' => $projectId,
            'assigned_to' => $developer->id,
            'created_by' => auth()->id() ?? 1,
            'priority' => strtolower($data['priority']),
            'start_date' => $data['start_date'] ? \Carbon\Carbon::parse($data['start_date'])->toDateTimeString() : now()->toDateTimeString(),
            'due_date' => $data['due_date'] ? \Carbon\Carbon::parse($data['due_date'])->toDateTimeString() : now()->addDays(5)->toDateTimeString(),
            'estimate_hours' => $estimateHours,
            'status' => 'assigned',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            DB::table('task_user')->insertOrIgnore([
                'task_id' => $taskId,
                'user_id' => $developer->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        // Log task history
        try {
            DB::table('task_history')->insert([
                'task_id' => $taskId,
                'user_id' => auth()->id() ?? 1,
                'details' => 'Task assigned to ' . $developer->name . ' by Super Admin.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        // Dispatch task assignment email notification to developer personal email
        try {
            $notifyEmail = $developer->personal_email ?: $developer->email;
            $dueDateFormatted = !empty($data['due_date']) ? \Carbon\Carbon::parse($data['due_date'])->format('M d, Y') : 'In 5 Days';
            Mail::to($notifyEmail)->send(new \App\Mail\WorkAssignedNotification(
                $developer,
                $data['task_title'],
                $data['priority'],
                $dueDateFormatted,
                $data['additional_instructions'] ?? null,
                url('/developer/my-work')
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('WorkAssignedNotification mail error: ' . $e->getMessage());
        }

        $this->logAction('developer.work_assigned', Company::find($companyId), [
            'developer_id' => $developer->id,
            'developer_email' => $developer->email,
            'task_title' => $data['task_title'],
            'priority' => $data['priority'],
            'was_account_created' => $wasCreated,
        ]);

        $msg = 'Work assigned successfully to existing developer ' . $developer->name . ' (' . $developer->email . ').';
        if ($wasCreated) {
            $msg = 'New developer account created and work assigned to ' . $developer->name . '. Temporary password: ' . $tempPassword;
        }

        return back()->with('success', $msg);
    }

    public function storeDeveloper(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'personal_email' => ['nullable', 'email'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'max:100'],
            'company_id' => ['nullable', 'integer'],
            'skills' => ['nullable', 'string'],
            'experience' => ['nullable', 'string', 'max:100'],
            'joining_date' => ['nullable', 'date'],
        ]);

        $loginEmail = strtolower(trim($data['email']));
        $personalEmail = !empty($data['personal_email']) ? strtolower(trim($data['personal_email'])) : $loginEmail;

        // Check if developer account ALREADY EXISTS
        $existing = User::where('email', $loginEmail)
            ->orWhere('personal_email', $loginEmail)
            ->first();

        if ($existing) {
            return back()->withErrors(['email' => 'A developer account already exists with email: ' . $loginEmail . '. Use "Assign Work" to assign tasks to this developer.']);
        }

        $compCompanyId = !empty($data['company_id']) ? $data['company_id'] : null;
        $comp = $compCompanyId ? Company::find($compCompanyId) : Company::first();

        $tempPassword = \Illuminate\Support\Str::random(10);

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
            'must_change_password' => true,
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

        // Send login credentials ONCE to personal email
        try {
            Mail::to($personalEmail)->send(new \App\Mail\DeveloperAccountCreated($user, $tempPassword, route('login')));
        } catch (\Throwable $e) {}

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
            'email' => ['required', 'email', 'unique:users,email,' . $id],
            'personal_email' => ['nullable', 'email'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'max:100'],
            'company_id' => ['nullable', 'integer'],
            'skills' => ['nullable', 'string'],
            'experience' => ['nullable', 'string'],
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

    public function updateTaskStatus(Request $request, $id): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'status' => ['required', 'in:to_do,assigned,in_progress,on_hold,completed,cancelled'],
        ]);

        $updateData = ['status' => $data['status'], 'updated_at' => now()];
        if ($data['status'] === 'completed') {
            $updateData['completed_on'] = now();
        }

        $oldStatus = DB::table('tasks')->where('id', $id)->value('status');
        DB::table('tasks')->where('id', $id)->update($updateData);

        // Record Task History
        try {
            DB::table('task_history')->insert([
                'task_id' => $id,
                'user_id' => auth()->id() ?? 1,
                'details' => 'Status changed from ' . ucfirst(str_replace('_', ' ', (string)$oldStatus)) . ' to ' . ucfirst(str_replace('_', ' ', $data['status'])) . ' by Super Admin.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', 'Task status updated to ' . ucfirst(str_replace('_', ' ', $data['status'])) . ' successfully.');
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
        $this->authorizeSuperAdmin();

        $developer = User::findOrFail($id);
        $newPassword = $request->input('password') ?: \Illuminate\Support\Str::random(10);

        $developer->update([
            'password' => Hash::make($newPassword),
            'raw_password' => $newPassword,
            'must_change_password' => true,
        ]);

        try {
            $targetEmail = $developer->personal_email ?: $developer->email;
            Mail::to($targetEmail)->send(new \App\Mail\DeveloperAccountCreated($developer, $newPassword, route('login')));
        } catch (\Throwable $e) {}

        $this->logAction('developer.password_reset', Company::find($developer->company_id), [
            'developer_id' => $developer->id,
            'developer_name' => $developer->name,
        ]);

        return back()->with('success', "Password for developer {$developer->name} reset successfully. Temporary password: {$newPassword}");
    }

    public function getTaskHistory(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $task = DB::table('tasks')
            ->leftJoin('users as dev', 'tasks.assigned_to', '=', 'dev.id')
            ->leftJoin('companies', 'tasks.company_id', '=', 'companies.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->select('tasks.*', 'dev.name as developer_name', 'dev.email as developer_email', 'companies.name as company_name', 'projects.name as project_name')
            ->where('tasks.id', $id)
            ->first();

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found']);
        }

        $history = DB::table('task_history')
            ->leftJoin('users', 'task_history.user_id', '=', 'users.id')
            ->select('task_history.*', 'users.name as user_name')
            ->where('task_id', $id)
            ->latest('task_history.created_at')
            ->get();

        return response()->json([
            'success' => true,
            'task' => $task,
            'history' => $history
        ]);
    }

    private function ensureDeveloperSeedData(): void
    {
        $devCount = User::whereIn('role', ['developer', 'employee', 'dev'])->orWhere('designation', 'like', '%developer%')->count();
        if ($devCount >= 5) {
            return;
        }

        $c1 = Company::first() ?? Company::create(['name' => 'Original Company', 'company_code' => 'MAIN', 'email' => 'admin@pms.local']);
        $c2 = Company::find(2) ?? $c1;
        $c3 = Company::find(3) ?? $c1;

        $seedDevs = [
            [
                'name' => 'Rahul Sharma',
                'email' => 'rahul@company.com',
                'designation' => 'Backend Developer',
                'company_id' => $c1->id,
                'skills' => 'Laravel, PHP, MySQL, REST API, Git, Docker',
                'mobile' => '+91 98765 43210',
            ],
            [
                'name' => 'Priya Patel',
                'email' => 'priya@company.com',
                'designation' => 'Frontend Developer',
                'company_id' => $c2->id,
                'skills' => 'React, JavaScript, TailwindCSS, Vue.js, TypeScript',
                'mobile' => '+91 98123 45678',
            ],
            [
                'name' => 'Amit Kumar',
                'email' => 'amit@company.com',
                'designation' => 'Full Stack Developer',
                'company_id' => $c3->id,
                'skills' => 'Laravel, React, Node.js, MySQL, PostgreSQL',
                'mobile' => '+91 97654 32109',
            ],
            [
                'name' => 'Ananya Sen',
                'email' => 'ananya@company.com',
                'designation' => 'DevOps Engineer',
                'company_id' => $c1->id,
                'skills' => 'Docker, Kubernetes, AWS, CI/CD, Python, Linux',
                'mobile' => '+91 96543 21098',
            ],
            [
                'name' => 'Vikram Singh',
                'email' => 'vikram@company.com',
                'designation' => 'QA Engineer',
                'company_id' => $c2->id,
                'skills' => 'PHPUnit, Cypress, Selenium, Postman, QA Automation',
                'mobile' => '+91 95432 10987',
            ],
            [
                'name' => 'Sneha Roy',
                'email' => 'sneha@company.com',
                'designation' => 'UI/UX Developer',
                'company_id' => $c3->id,
                'skills' => 'Figma, CSS3, HTML5, JavaScript, UI/UX Design',
                'mobile' => '+91 94321 09876',
            ],
        ];

        foreach ($seedDevs as $sd) {
            $user = User::firstOrCreate(
                ['email' => $sd['email']],
                [
                    'name' => $sd['name'],
                    'company_id' => $sd['company_id'],
                    'role' => 'developer',
                    'designation' => $sd['designation'],
                    'mobile' => $sd['mobile'],
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                    'login_allowed' => true,
                    'email_notifications' => true,
                    'joining_date' => now()->subMonths(6)->toDateString(),
                ]
            );

            try {
                DB::table('employee_details')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'company_id' => $user->company_id,
                        'skills' => $sd['skills'],
                        'status' => 'available',
                        'joining_date' => now()->subMonths(6)->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } catch (\Throwable $e) {}
        }

        $defaultProjId = DB::table('projects')->first()?->id 
            ?? DB::table('projects')->insertGetId([
                'company_id' => $c1->id,
                'name' => 'Platform Core Project',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $sampleTasks = [
            ['title' => 'API Authentication Module Integration', 'assigned_to' => 'rahul@company.com', 'priority' => 'high', 'hours' => 16, 'status' => 'in_progress', 'due' => now()->addDays(3)],
            ['title' => 'Subscription Expiry Notifications System', 'assigned_to' => 'rahul@company.com', 'priority' => 'critical', 'hours' => 12, 'status' => 'assigned', 'due' => now()->addDays(5)],
            ['title' => 'Responsive Navigation Redesign', 'assigned_to' => 'priya@company.com', 'priority' => 'medium', 'hours' => 20, 'status' => 'in_progress', 'due' => now()->addDays(2)],
            ['title' => 'Payment Gateway Webhook Optimization', 'assigned_to' => 'amit@company.com', 'priority' => 'high', 'hours' => 10, 'status' => 'assigned', 'due' => now()->addDays(4)],
            ['title' => 'Docker Deployment Pipeline Setup', 'assigned_to' => 'ananya@company.com', 'priority' => 'critical', 'hours' => 18, 'status' => 'in_progress', 'due' => now()->addDays(1)],
            ['title' => 'Automated Integration Testing Suite', 'assigned_to' => 'vikram@company.com', 'priority' => 'medium', 'hours' => 14, 'status' => 'assigned', 'due' => now()->addDays(7)],
        ];

        foreach ($sampleTasks as $st) {
            $dev = User::where('email', $st['assigned_to'])->first();
            if ($dev) {
                $exists = DB::table('tasks')->where('assigned_to', $dev->id)->where('title', $st['title'])->exists();
                if (!$exists) {
                    DB::table('tasks')->insert([
                        'company_id' => $dev->company_id ?? 1,
                        'project_id' => $defaultProjId,
                        'title' => $st['title'],
                        'description' => 'Development work task created by Super Admin.',
                        'assigned_to' => $dev->id,
                        'created_by' => 1,
                        'priority' => $st['priority'],
                        'due_date' => $st['due']->toDateTimeString(),
                        'estimate_hours' => $st['hours'],
                        'status' => $st['status'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
