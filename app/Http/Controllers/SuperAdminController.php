<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SuperAdmin\CompanyAdministration;
use App\Http\Controllers\Concerns\SuperAdmin\DeveloperAccounts;
use App\Http\Controllers\Concerns\SuperAdmin\DeveloperAssignments;
use App\Http\Controllers\Concerns\SuperAdmin\DeveloperDirectory;
use App\Http\Controllers\Concerns\SuperAdmin\PlatformProfile;
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
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    use CompanyAdministration;
    use DeveloperDirectory;
    use DeveloperAccounts;
    use DeveloperAssignments;
    use PlatformProfile;

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

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 10, 25, 50, 100, 250], true)) {
            $perPage = 10;
        }

        $companies = Company::with(['activeSubscription.plan', 'latestSubscription.plan', 'users' => function ($query) {
                $query->where('role', 'admin')->latest()->limit(2);
            }])
            ->latest()
            ->paginate($perPage, ['*'], 'companies_page')
            ->withQueryString();

        $dashboardPlanCounts = app(\App\Services\SubscriptionDistribution::class)->currentCounts(
            Company::with('subscriptions.plan')->get()
        );

        $plans = SubscriptionPlan::standard()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $modules = Module::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $companyOptions = Company::orderBy('name')->get();
        $recentAdmins = User::where('role', 'admin')->with('company')->latest()->take(6)->get();
        $recentInvoices = Invoice::with('company')->latest()->take(5)->get();
        $recentActivities = AuditLog::with(['company', 'user'])->latest()->take(100)->get();

        return view('superadmin.dashboard', compact(
            'dashboardPlanCounts',
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

    public function storeCompany(Request $request): RedirectResponse
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

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
            'admin_password' => ['required', 'string', 'min:8', 'max:128'],
            'admin_password_confirmation' => ['nullable', 'string', 'same:admin_password'],
            'admin_profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
            'module_ids' => ['array'],
            'module_ids.*' => ['exists:modules,id'],
        ], [
            'admin_password.min' => 'Admin password must be at least 8 characters long.',
            'admin_password.max' => 'Admin password may not be greater than 128 characters.',
            'admin_password_confirmation.same' => 'Password confirmation does not match.',
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
                'password' => $data['admin_password'] ?? null,
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

            $rawPlanInput = $request->input('subscription_plan')
                ?? $request->input('plan_slug')
                ?? $request->input('plan_id')
                ?? $request->input('plan');

            if ($rawPlanInput) {
                $planSlug = strtolower(trim((string)$rawPlanInput));
                $plan = SubscriptionPlan::where('slug', $planSlug)->first()
                    ?? SubscriptionPlan::find($rawPlanInput)
                    ?? \App\Models\Central\Plan::on('central')->where('slug', $planSlug)->first();
                $cycle = $data['billing_cycle'] ?? 'monthly';

                if ($plan) {
                    $endsAt30 = now()->addDays(30)->toDateString();
                    CompanySubscription::create([
                        'company_id' => $company->id,
                        'plan_id' => $plan->id,
                        'billing_cycle' => $cycle,
                        'starts_at' => now()->toDateString(),
                        'ends_at' => $endsAt30,
                        'trial_ends_at' => $data['trial_ends_at'] ?? $endsAt30,
                        'price' => method_exists($plan, 'getPriceForBillingCycle') ? $plan->getPriceForBillingCycle($cycle) : 0,
                        'status' => 'active',
                        'auto_renew' => true,
                    ]);

                    try {
                        \App\Models\Central\Subscription::on('central')->create([
                            'company_id' => $company->id,
                            'plan_id' => $plan->id,
                            'billing_cycle' => $cycle,
                            'starts_at' => now()->toDateString(),
                            'ends_at' => $endsAt30,
                            'trial_ends_at' => $data['trial_ends_at'] ?? $endsAt30,
                            'price' => method_exists($plan, 'getPriceForBillingCycle') ? $plan->getPriceForBillingCycle($cycle) : 0,
                            'status' => 'active',
                            'auto_renew' => true,
                        ]);
                    } catch (\Throwable $ex) {}

                    try {
                        $targetLevel = \App\Services\PlanEligibilityService::getPlanLevel($plan);
                        $company->update([
                            'trial_ends_at'      => $endsAt30,
                            'highest_plan_level' => $targetLevel,
                            'highest_plan_slug'  => strtolower($plan->slug),
                        ]);
                    } catch (\Throwable $ex) {}
                }
            }

            $moduleIds = $request->input('module_ids', []);
            if (! empty($moduleIds)) {
                $company->modules()->syncWithPivotValues($moduleIds, ['is_enabled' => true]);
                foreach ($moduleIds as $mId) {
                    try {
                        DB::connection('central')->table('company_modules')->updateOrInsert(
                            ['company_id' => $company->id, 'module_id' => $mId],
                            ['is_enabled' => 1, 'updated_at' => now()]
                        );
                    } catch (\Throwable $ex) {}
                }
            }

            $this->logAction('company.created', $company, ['admin_email' => $data['admin_email']]);
        });

        return back()->with('success', 'Company and company admin created successfully.');
    }
}
