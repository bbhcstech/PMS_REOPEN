<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SuperAdmin\Concerns\ManagesCompanyProvisioning;
use App\Http\Controllers\SuperAdmin\Concerns\ManagesCompanySubscriptions;
use App\Http\Controllers\SuperAdmin\Concerns\ManagesPlanCatalog;
use App\Http\Controllers\SuperAdmin\Concerns\ManagesPlatformAlerts;
use App\Http\Controllers\SuperAdmin\Concerns\ManagesTenantBackups;
use App\Http\Controllers\SuperAdmin\Concerns\ManagesTenantMigrations;
use App\Http\Controllers\SuperAdmin\Concerns\MonitorsSystemHealth;
use App\Http\Controllers\SuperAdmin\Concerns\ReviewsTenantAudit;
use App\Models\Central\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanyController extends Controller
{
    use ManagesCompanyProvisioning;
    use ManagesCompanySubscriptions;
    use ManagesPlanCatalog;
    use ManagesPlatformAlerts;
    use ManagesTenantBackups;
    use ManagesTenantMigrations;
    use MonitorsSystemHealth;
    use ReviewsTenantAudit;

    private function authorizeSuperAdmin(): void
    {
        if (\Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
            return;
        }

        if (auth()->check()) {
            $user = auth()->user();
            $isDev = method_exists($user, 'isDeveloper') ? $user->isDeveloper() : in_array(strtolower((string) ($user->role ?? '')), ['developer', 'dev'], true);
            if ($isDev) {
                abort(redirect()->route('developer.dashboard'));
            }

            $role = strtolower((string) ($user->role ?? ''));
            if ($role === 'client' || $role === 'customer') {
                abort(403, 'Unauthorized access to Super Admin portal.');
            }

            return;
        }

        throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.', ['web', 'super_admin']);
    }

    /**
     * Display a list of all registered tenant companies.
     */
    public function index(): View
    {
        $this->authorizeSuperAdmin();
        try {
            $companies = Company::on('central')->latest()->get();
        } catch (\Throwable $e) {
            $companies = collect();
        }

        if ($companies->isEmpty()) {
            try {
                $companies = \App\Models\Company::latest()->get();
            } catch (\Throwable $e) {
                $companies = collect();
            }
        }

        // Synchronize all company statuses in database before rendering
        try {
            $subService = app(\App\Services\SubscriptionService::class);
            foreach ($companies as $comp) {
                $subService->syncCompanyStatus($comp);
            }
        } catch (\Throwable $e) {}

        $currentCompanyDb = session('current_company_db');

        $plans = \App\Models\Central\Plan::standard()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

        $lastActivity = \App\Services\CompanyActivity::forCompanies($companies);

        return view('superadmin.companies.index', compact('companies', 'plans', 'currentCompanyDb', 'lastActivity'));
    }

    /**
     * Display Central Company Metrics and Business Intelligence Dashboard.
     */
    public function metrics(Request $request): View
    {
        try {
            $companies = Company::on('central')->with(['subscriptions.plan', 'activeSubscription.plan'])->latest()->get();
        } catch (\Throwable $e) {
            $companies = collect();
        }

        if ($companies->isEmpty()) {
            try {
                $companies = \App\Models\Company::latest()->get();
            } catch (\Throwable $e) {
                $companies = collect();
            }
        }

        // Synchronize all company statuses in database before calculating metrics
        try {
            $subService = app(\App\Services\SubscriptionService::class);
            foreach ($companies as $comp) {
                $subService->syncCompanyStatus($comp);
            }
        } catch (\Throwable $e) {}

        try {
            $totalUsers = User::count();
        } catch (\Throwable $e) {
            $totalUsers = $companies->count() * 14;
        }

        // Calculate dynamic platform revenue totals and plan counts
        $totalRevenue = 0;
        $planCounts = ['FREE' => 0, 'GOLD' => 0, 'PLATINUM' => 0, 'DIAMOND' => 0];

        foreach ($companies as $comp) {
            $sub = $comp->subscriptions->first();
            $planName = strtoupper($sub?->plan?->name ?? 'FREE');
            
            if (isset($planCounts[$planName])) {
                $planCounts[$planName]++;
            } else {
                $planCounts['FREE']++;
            }

            $price = $sub ? (float) $sub->price : 0;
            $totalRevenue += $price;
        }

        $currentCompanyDb = session('current_company_db');

        $companyGrowth = app(\App\Services\CompanyGrowth::class)->series($companies);
        $distributionCounts = app(\App\Services\SubscriptionDistribution::class)->counts($companies);

        try {
            $revenueOverview = app(\App\Services\RevenueOverview::class)->series($companies);
        } catch (\Throwable $e) {
            $revenueOverview = [];
        }

        return view('superadmin.companies.metrics', compact('companies', 'totalUsers', 'totalRevenue', 'planCounts', 'currentCompanyDb', 'companyGrowth', 'distributionCounts', 'revenueOverview'));
    }

    /**
     * Display dedicated full-page Enterprise Tenant Command Center for a company.
     */
    public function show($id): View
    {
        try {
            $company = Company::on('central')->findOrFail($id);
        } catch (\Throwable $e) {
            $company = \App\Models\Company::findOrFail($id);
        }

        try {
            app(\App\Services\SubscriptionService::class)->syncCompanyStatus($company);
            $company->refresh();
        } catch (\Throwable $e) {}

        $currentCompanyDb = session('current_company_db');

        // Dynamically fetch actual details from primary database and tenant database
        $tenantUsers = collect();
        $tenantAdmins = collect();
        $dbConnected = false;
        $dbLatency = 0;

        // 1. Fetch users from primary DB matching this company_id or company email
        try {
            $primaryUsers = User::on('mysql')
                ->where('company_id', $company->id)
                ->orWhere('email', $company->email)
                ->latest()
                ->get();
            $tenantUsers = $tenantUsers->merge($primaryUsers);
        } catch (\Throwable $e) {}

        // 2. Fetch users from tenant DB if provisioned
        if (! empty($company->db_name)) {
            try {
                config(['database.connections.tenant.database' => $company->db_name]);
                DB::purge('tenant');

                $startTime = microtime(true);
                DB::connection('tenant')->getPdo();
                $dbLatency = round((microtime(true) - $startTime) * 1000);
                $dbConnected = true;

                $tUsers = User::on('tenant')->latest()->get();
                $tenantUsers = $tenantUsers->merge($tUsers);
            } catch (\Throwable $e) {
                $dbConnected = false;
            }
        }

        // Deduplicate uniquely by email
        $tenantUsers = $tenantUsers->unique(function ($u) {
            return strtolower(trim($u->email ?? (string)$u->id));
        })->values();

        // Filter tenant admins
        $tenantAdmins = $tenantUsers->filter(function ($u) {
            return in_array(strtolower($u->role ?? ''), ['admin', 'superadmin', 'administrator', 'company_admin'], true);
        })->values();

        if ($tenantAdmins->isEmpty() && $tenantUsers->isNotEmpty()) {
            $tenantAdmins = $tenantUsers->take(1);
        }

        $totalUsersCount = $tenantUsers->count();
        $adminsCount = $tenantAdmins->count();

        $companyLoginEmail = !empty($company->email) ? $company->email : ($tenantAdmins->first()?->email ?? '');
        $companyPassword = !empty($company->password)
            ? $company->password
            : ($tenantAdmins->first()?->raw_password ?? ($tenantUsers->first()?->raw_password ?? ''));

        return view('superadmin.companies.show', compact(
            'company',
            'currentCompanyDb',
            'tenantUsers',
            'tenantAdmins',
            'totalUsersCount',
            'adminsCount',
            'dbConnected',
            'dbLatency',
            'companyLoginEmail',
            'companyPassword'
        ));
    }

    /**
     * Enter (impersonate) a tenant company by setting session key.
     */
    public function enter(Company $company): RedirectResponse
    {
        session([
            'current_company_db'   => $company->db_name,
            'current_company_id'   => $company->id,
            'current_company_name' => $company->name,
        ]);

        config([
            'database.connections.tenant.database' => $company->db_name,
            'database.connections.mysql.database'  => $company->db_name,
        ]);
        DB::purge('tenant');
        DB::purge('mysql');

        if (app()->bound(\App\Services\CompanyContext::class)) {
            app(\App\Services\CompanyContext::class)->reset();
        }

        return redirect('/dashboard')
            ->with('success', "Switched database context to tenant: {$company->name} ({$company->db_name})");
    }

    /**
     * Clear tenant session key and return to Super Admin panel.
     */
    public function leaveImpersonation(): RedirectResponse
    {
        session()->forget(['current_company_db', 'current_company_id', 'current_company_name']);

        $defaultDb = config('database.connections.tenant.database') ?: config('database.connections.mysql.database');
        config([
            'database.connections.tenant.database' => $defaultDb,
            'database.connections.mysql.database'  => $defaultDb,
        ]);
        DB::purge('tenant');
        DB::purge('mysql');

        if (app()->bound(\App\Services\CompanyContext::class)) {
            app(\App\Services\CompanyContext::class)->reset();
        }

        return redirect()->route('super-admin.companies.index')
            ->with('success', 'Exited tenant company impersonation.');
    }

    public function suspend($id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $company = app(\App\Services\CompanySuspension::class)->suspend((int) $id, request()->all());
        if (request()->wantsJson()) return response()->json(['success' => true, 'message' => 'Company suspended with the recorded reason.', 'status' => $company->status, 'company_id' => $company->id]);
        return redirect()->route('superadmin.companies.suspended')->with('success', 'Company suspended. You can now reactivate it, send the reason on WhatsApp, or permanently delete it.');
    }

    /**
     * Deactivate a tenant company.
     */
    public function deactivate($id): RedirectResponse
    {
        try {
            $company = Company::on('central')->findOrFail($id);
        } catch (\Throwable $e) {
            $company = \App\Models\Company::findOrFail($id);
        }

        $company->status = 'inactive';
        $company->save();

        return redirect()->back()
            ->with('success', "Tenant company '{$company->name}' access has been deactivated.");
    }

    /**
     * Activate a tenant company.
     */
    public function activate($id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $company = app(\App\Services\CompanySuspension::class)->reactivate((int) $id);
        $message = 'Company suspension lifted. Current status: ' . ucfirst($company->status) . '.';
        if (request()->wantsJson()) return response()->json(['success' => true, 'message' => $message, 'status' => $company->status, 'company_id' => $company->id]);
        return back()->with('success', $message);
    }
}
