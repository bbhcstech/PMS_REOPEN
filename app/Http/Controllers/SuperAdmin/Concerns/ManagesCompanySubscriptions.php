<?php

namespace App\Http\Controllers\SuperAdmin\Concerns;

use App\Models\Central\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait ManagesCompanySubscriptions
{
    /**
     * Store a newly created subscription plan.
     */
    public function storePlan(Request $request): RedirectResponse
    {
        if (is_string($request->input('name'))) {
            $request->merge(['name' => strtoupper(trim($request->input('name')))]);
        }
        $data = $request->validate([
            'name'           => ['required', 'string', \Illuminate\Validation\Rule::in(['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'])],
            'description'    => 'nullable|string',
            'monthly_price'  => 'required|numeric|min:0',
            'max_users'      => 'required|integer|min:0',
            'max_storage_gb' => 'required|numeric|min:0',
            'is_active'      => 'required|boolean',
        ], [
            'name.in' => 'Only FREE, GOLD, PLATINUM, and DIAMOND plans are supported. Edit an existing tier to change its settings.',
        ]);

        $slug = Str::slug($data['name']);
        if (\App\Models\Central\Plan::where('slug', $slug)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => "The {$data['name']} plan already exists. Edit the existing plan to change its settings.",
            ]);
        }
        $storageMb = (int) ($data['max_storage_gb'] * 1024);

        $planData = [
            'name'           => strtoupper($data['name']),
            'slug'           => $slug,
            'description'    => $data['description'] ?? "Plan tier for {$data['name']}",
            'monthly_price'  => $data['monthly_price'],
            'yearly_price'   => $data['monthly_price'] * 10,
            'max_users'      => $data['max_users'],
            'max_projects'   => 0,
            'max_clients'    => 0,
            'max_storage_mb' => $storageMb,
            'is_active'      => $data['is_active'],
            'sort_order'     => 0,
        ];

        try {
            \App\Models\Central\Plan::on('central')->create($planData);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => "The {$data['name']} plan already exists. Edit the existing plan to change its settings.",
            ]);
        }

        return redirect()->route('super-admin.plans.index')
            ->with('success', "Subscription Plan '{$data['name']}' created successfully!");
    }

    /**
     * Update an existing subscription plan.
     */
    public function updatePlan(Request $request, $id): RedirectResponse
    {
        if (is_string($request->input('name'))) {
            $request->merge(['name' => strtoupper(trim($request->input('name')))]);
        }
        $data = $request->validate([
            'name'           => ['required', 'string', \Illuminate\Validation\Rule::in(['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'])],
            'description'    => 'nullable|string',
            'monthly_price'  => 'required|numeric|min:0',
            'max_users'      => 'required|integer|min:0',
            'max_storage_gb' => 'required|numeric|min:0',
            'is_active'      => 'required|boolean',
        ], [
            'name.in' => 'Only FREE, GOLD, PLATINUM, and DIAMOND plans are supported. Edit an existing tier to change its settings.',
        ]);

        try {
            $plan = \App\Models\Central\Plan::on('central')->findOrFail($id);
        } catch (\Throwable $e) {
            $plan = \App\Models\SubscriptionPlan::findOrFail($id);
        }

        if (Str::slug($data['name']) !== $plan->slug) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => 'The plan tier cannot be changed. Edit the settings of the selected tier instead.',
            ]);
        }

        $plan->name = strtoupper($data['name']);
        $plan->monthly_price = $data['monthly_price'];
        $plan->yearly_price = $data['monthly_price'] * 10;
        $plan->max_users = $data['max_users'];
        $plan->max_storage_mb = (int) ($data['max_storage_gb'] * 1024);
        $plan->is_active = $data['is_active'];
        if (!empty($data['description'])) {
            $plan->description = $data['description'];
        }
        $plan->save();

        return redirect()->route('super-admin.plans.index')
            ->with('success', "Subscription Plan '{$plan->name}' updated successfully!");
    }

    /**
     * Remove a subscription plan.
     */
    public function destroyPlan($id): RedirectResponse
    {
        try {
            $plan = \App\Models\Central\Plan::on('central')->findOrFail($id);
            $plan->delete();
        } catch (\Throwable $e) {
            $plan = \App\Models\SubscriptionPlan::findOrFail($id);
            $plan->delete();
        }

        return redirect()->route('super-admin.plans.index')
            ->with('success', "Subscription plan deleted successfully.");
    }

    /**
     * Toggle a module access for a subscription plan (Feature Access Matrix).
     */
    public function togglePlanModule(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless(\App\Services\TenantScope::isPlatformAdmin(), 403);
        $request->validate([
            'plan_id' => 'required|integer|exists:central.plans,id',
            'module_id' => 'required|integer|exists:central.modules,id',
            'enabled' => 'required|boolean',
        ]);

        $planId = $request->input('plan_id');
        $moduleId = $request->input('module_id');
        $enabled = $request->boolean('enabled');

        try {
            $plan = \App\Models\Central\Plan::on('central')->find($planId) ?? \App\Models\SubscriptionPlan::find($planId);
            $module = \App\Models\Module::on('central')->find($moduleId) ?? \App\Models\Module::find($moduleId);

            if ($plan && $module) {
                app(\App\Services\PlanFeatureEntitlements::class)->set($plan, $module, $enabled);

                // Log Audit
                try {
                    \App\Models\AuditLog::create([
                        'user_id' => auth()->id(),
                        'action' => 'plan_module.toggled',
                        'entity_type' => 'Plan',
                        'entity_id' => $plan->id,
                        'new_values' => ['plan' => $plan->name, 'module' => $module->name, 'enabled' => $enabled],
                        'ip_address' => request()->ip(),
                        'user_agent' => substr((string) request()->userAgent(), 0, 255),
                    ]);
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            $states = \App\Models\Module::on('central')->get()->mapWithKeys(fn ($item) => [
                $item->id => app(\App\Services\PlanFeatureEntitlements::class)->allows($plan, $item->slug) ? 1 : 0,
            ]);
            return response()->json(['success' => true, 'message' => 'Plan module access updated successfully.', 'states' => $states]);
        }

        return back()->with('success', 'Plan module access updated successfully.');
    }

    /**
     * Assign / Change a company's subscription plan.
     */
    public function assignPlan(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless(\App\Services\TenantScope::isPlatformAdmin(), 403);
        $request->validate([
            'company_id' => 'required|integer|exists:central.companies,id',
            'plan_id' => ['required', \Illuminate\Validation\Rule::exists('central.plans', 'id')->where(fn ($q) => $q->whereIn('slug', \App\Support\SupportedPlans::SLUGS)->where('is_active', true))],
            'billing_cycle' => 'nullable|in:monthly,yearly',
        ]);

        $companyId = $request->input('company_id');
        $planId = $request->input('plan_id');
        $cycle = $request->input('billing_cycle') ?: 'monthly';

        try {
            \App\Services\SubscriptionChangeSchema::ensure();
            // Resolve Company
            $company = Company::on('central')->find($companyId) ?? \App\Models\Company::find($companyId);
            if (!$company) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Tenant company not found.'], 422);
                }
                return back()->withErrors(['error' => 'Tenant company not found.']);
            }

            $plan = \App\Models\Central\Plan::on('central')->standard()->where('is_active', true)->findOrFail($planId);

            if ($company && $plan) {
                /** @var \App\Services\SubscriptionService $subService */
                $subService = app(\App\Services\SubscriptionService::class);

                $isManual = (bool) ($company->manually_suspended ?? false);

                $sub = DB::connection('central')->transaction(function () use (&$company, $plan, $cycle, $subService, &$isManual) {
                    $company = Company::on('central')->whereKey($company->id)->lockForUpdate()->firstOrFail();
                    $isManual = (bool) $company->manually_suspended;
                    $sub = $subService->activateOrUpgradePlan(
                        company: $company,
                        plan: $plan,
                        billingCycle: $cycle,
                        performedBy: auth('super_admin')->user()?->name ?? auth()->user()?->name ?? 'Super Admin Command Center'
                    );

                    // Preserve manual suspension while applying the chosen limits.
                    $company->update([
                        'max_users'      => $plan->max_users > 0 ? $plan->max_users : 999999,
                        'max_storage_mb' => $plan->max_storage_mb > 0 ? $plan->max_storage_mb : 512000,
                        'status'         => $isManual ? 'suspended' : 'active',
                        'trial_ends_at'  => $sub->ends_at,
                    ]);
                    return $sub;
                });

                $startFmt = $sub->starts_at->format('M d, Y, h:i A');
                $endFmt = $sub->ends_at->format('M d, Y, h:i A');
                $msg = "Subscription plan '{$plan->name}' activated for {$company->name}. Valid until {$endFmt}.";

                if ($request->wantsJson()) {
                    return response()->json([
                        'success'             => true,
                        'message'             => $msg,
                        'plan_name'           => strtoupper($plan->name),
                        'plan_class'          => strtolower($plan->name),
                        'company_id'          => $company->id,
                        'starts_at_formatted' => $startFmt,
                        'ends_at_formatted'   => $endFmt,
                        'status'              => $isManual ? 'suspended' : 'active',
                    ]);
                }

                return back()->with('success', $msg);
            }
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Failed to resolve tenant company or plan tier.'], 422);
        }

        return back()->withErrors(['error' => 'Failed to resolve tenant company or plan tier.']);
    }

    /**
     * Extend a company's subscription by 30 days.
     */
    public function extendSubscription(Request $request, $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless(\App\Services\TenantScope::isPlatformAdmin(), 403);
        $days = (int) $request->input('days', 30);
        if ($days <= 0) $days = 30;

        try {
            $company = Company::on('central')->find($id) ?? \App\Models\Company::find($id);

            if ($company) {
                $sub = \App\Models\Central\Subscription::on('central')
                    ->where('company_id', $company->id)
                    ->orderBy('id', 'desc')
                    ->first();

                $currentEndsAt = $sub?->ends_at ?? $company->trial_ends_at ?? now();
                $baseDate = ($currentEndsAt && $currentEndsAt->isFuture()) ? $currentEndsAt : now();
                $newEndsAt = $baseDate->copy()->addDays($days);

                if ($sub) {
                    $sub->update([
                        'ends_at' => $newEndsAt,
                        'status'  => 'active',
                    ]);
                } else {
                    $defaultPlan = \App\Models\Central\Plan::on('central')->where('slug', 'gold')->first()
                        ?? \App\Models\Central\Plan::on('central')->first();

                    $sub = \App\Models\Central\Subscription::on('central')->create([
                        'company_id'    => $company->id,
                        'plan_id'       => $defaultPlan?->id ?? 1,
                        'billing_cycle' => 'monthly',
                        'starts_at'     => now(),
                        'ends_at'       => $newEndsAt,
                        'price'         => 4999,
                        'status'        => 'active',
                        'auto_renew'    => true,
                    ]);
                }

                $isManual = (bool) ($company->manually_suspended ?? false);
                $newCompanyStatus = $isManual ? 'suspended' : 'active';

                $company->update([
                    'status'        => $newCompanyStatus,
                    'trial_ends_at' => $newEndsAt,
                ]);

                try {
                    $tenantComp = \App\Models\Company::find($company->id);
                    if ($tenantComp) {
                        $tenantComp->update([
                            'status'        => $newCompanyStatus,
                            'trial_ends_at' => $newEndsAt,
                        ]);
                    }
                } catch (\Throwable $e) {}

                // Log Audit Action
                try {
                    \App\Models\AuditLog::create([
                        'user_id'     => auth()->id(),
                        'company_id'  => $company->id,
                        'action'      => 'subscription.extended',
                        'entity_type' => 'Company',
                        'entity_id'   => $company->id,
                        'new_values'  => ['extended_days' => $days, 'new_ends_at' => $newEndsAt->toDateTimeString()],
                        'ip_address'  => request()->ip(),
                        'user_agent'  => substr((string) request()->userAgent(), 0, 255),
                    ]);
                } catch (\Throwable $e) {}

                $startFmt = ($sub->starts_at ?? now())->format('M d, Y, h:i A');
                $endFmt = $newEndsAt->format('M d, Y, h:i A');

                if ($request->wantsJson()) {
                    return response()->json([
                        'success'             => true,
                        'message'             => "Subscription for '{$company->name}' extended by {$days} days until {$endFmt}.",
                        'company_id'          => $company->id,
                        'starts_at_formatted' => $startFmt,
                        'ends_at_formatted'   => $endFmt,
                        'status'              => 'active',
                    ]);
                }

                return back()->with('success', "Subscription for '{$company->name}' extended by {$days} days until {$endFmt}.");
            }
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Tenant company not found.'], 422);
        }

        return back()->withErrors(['error' => 'Tenant company not found.']);
    }

    /**
     * Reduce / Shorten a company's subscription duration by X days (default 7 days).
     */
    public function reduceSubscription(Request $request, $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $days = (int) $request->input('days', 7);
        if ($days <= 0) $days = 7;

        try {
            $company = Company::on('central')->find($id) ?? \App\Models\Company::find($id);

            if ($company) {
                if (strtolower($company->status ?? '') === 'suspended' || (method_exists($company, 'isSuspended') && $company->isSuspended())) {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Cannot reduce subscription for '{$company->name}' because this company is SUSPENDED. Please activate the company first."
                        ], 422);
                    }
                    return back()->withErrors(['error' => "Cannot reduce subscription for '{$company->name}' because this company is SUSPENDED. Please activate the company first."]);
                }

                $sub = \App\Models\Central\Subscription::on('central')
                    ->where('company_id', $company->id)
                    ->orderBy('id', 'desc')
                    ->first();

                $currentEndsAt = $sub?->ends_at ?? $company->trial_ends_at ?? now();
                $newEndsAt = \Carbon\Carbon::parse($currentEndsAt)->subDays($days);

                $isExpiredNow = $newEndsAt->isPast();
                $newStatus = $isExpiredNow ? 'expired' : 'active';

                if ($sub) {
                    $sub->update([
                        'ends_at' => $newEndsAt,
                        'status'  => $newStatus,
                    ]);
                }

                $company->update([
                    'status'        => $newStatus,
                    'trial_ends_at' => $newEndsAt,
                ]);

                // Log Audit Action
                try {
                    \App\Models\AuditLog::create([
                        'user_id'     => auth()->id(),
                        'company_id'  => $company->id,
                        'action'      => 'subscription.reduced',
                        'entity_type' => 'Company',
                        'entity_id'   => $company->id,
                        'new_values'  => ['reduced_days' => $days, 'new_ends_at' => $newEndsAt->toDateTimeString(), 'status' => $newStatus],
                        'ip_address'  => request()->ip(),
                        'user_agent'  => substr((string) request()->userAgent(), 0, 255),
                    ]);
                } catch (\Throwable $e) {}

                $startFmt = ($sub?->starts_at ?? now())->format('M d, Y, h:i A');
                $endFmt = $newEndsAt->format('M d, Y, h:i A');

                $msg = $isExpiredNow
                    ? "Subscription for '{$company->name}' reduced by {$days} days and has EXPIRED (Suspended). Expiry date: {$endFmt}."
                    : "Subscription for '{$company->name}' reduced by {$days} days. New expiry: {$endFmt}.";

                if ($request->wantsJson()) {
                    return response()->json([
                        'success'             => true,
                        'message'             => $msg,
                        'company_id'          => $company->id,
                        'starts_at_formatted' => $startFmt,
                        'ends_at_formatted'   => $endFmt,
                        'status'              => $newStatus,
                    ]);
                }

                return back()->with('success', $msg);
            }
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Tenant company not found.'], 422);
        }

        return back()->withErrors(['error' => 'Tenant company not found.']);
    }

    /**
     * Toggle a company custom feature override (Company-level module override).
     */
    public function toggleCompanyOverride(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless(\App\Services\TenantScope::isPlatformAdmin(), 403);
        $request->validate([
            'company_id' => 'required|integer|exists:central.companies,id',
            'module_id' => 'required|integer|exists:central.modules,id',
            'enabled' => 'required|boolean',
        ]);

        $companyId = $request->input('company_id');
        $moduleId = $request->input('module_id');
        $enabled = $request->boolean('enabled');

        try {
            DB::connection('central')->transaction(function () use ($companyId, $moduleId, $enabled) {
            DB::connection('central')->table('company_modules')->updateOrInsert(
                ['company_id' => $companyId, 'module_id' => $moduleId],
                ['is_enabled' => $enabled, 'updated_at' => now()]
            );

            // Legacy alias rows of the same feature (e.g. "deals" for Deals) must not keep an older
            // decision alive, otherwise the feature stays on/off whatever the Super Admin selects here.
            $entitlements = app(\App\Services\FeatureEntitlements::class);
            $moduleSlug = DB::connection('central')->table('modules')->where('id', $moduleId)->value('slug');
            if ($moduleSlug && ! $entitlements->isLegacyAlias($moduleSlug) && $aliases = $entitlements->aliasesOf($moduleSlug)) {
                DB::connection('central')->table('company_modules')
                    ->where('company_id', $companyId)
                    ->whereIn('module_id', DB::connection('central')->table('modules')->whereIn('slug', $aliases)->pluck('id'))
                    ->delete();
            }
            $entitlements->forget((int) $companyId);
            });

            // Log Audit Action
            try {
                \App\Models\AuditLog::create([
                    'user_id' => auth()->id(),
                    'company_id' => $companyId,
                    'action' => 'company_module.override_toggled',
                    'entity_type' => 'Company',
                    'entity_id' => $companyId,
                    'new_values' => ['module_id' => $moduleId, 'enabled' => $enabled],
                    'ip_address' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 255),
                ]);
            } catch (\Throwable $e) {}
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            $company = Company::on('central')->find($companyId);
            return response()->json([
                'success' => true,
                'message' => 'Company feature override updated successfully.',
                'states' => $company ? $this->featureStatesFor($company) : [],
            ]);
        }

        return back()->with('success', 'Company feature override updated successfully.');
    }

    /**
     * Effective feature access for one company exactly as its workspace resolves it (sidebar and routes),
     * so Manage Features never shows a guessed state.
     */
    public function companyFeatureStates($companyId): \Illuminate\Http\JsonResponse
    {
        $company = Company::on('central')->findOrFail($companyId);
        return response()->json(['success' => true, 'states' => $this->featureStatesFor($company)]);
    }

    /** @return array<int, array{effective: int, override: ?int, blocked_by: ?string}> keyed by central module id */
    private function featureStatesFor($company): array
    {
        $entitlements = app(\App\Services\FeatureEntitlements::class);
        $entitlements->forget((int) $company->id);
        $overrides = DB::connection('central')->table('company_modules')
            ->where('company_id', $company->id)->pluck('is_enabled', 'module_id');
        $states = [];
        $plan = $company->activeSubscription?->plan;
        foreach (app(\App\Services\AdminWorkspaceModules::class)->all(false) as $module) {
            if ($entitlements->isLegacyAlias($module->slug)) continue;
            $blockedBy = $entitlements->disabledAncestor((int) $company->id, $module->slug);
            $states[$module->id] = [
                'effective' => $company->hasFeature($module->slug) ? 1 : 0,
                'plan' => $plan ? (int) app(\App\Services\PlanFeatureEntitlements::class)->allows($plan, $module->slug)
                    : (int) ($company->isOnTrial() || $company->isActive()),
                'override' => $overrides->has($module->id) ? (int) $overrides[$module->id] : null,
                'blocked_by' => $blockedBy ? (config('workspace_modules.'.$blockedBy.'.name') ?? \Illuminate\Support\Str::headline($blockedBy)) : null,
            ];
        }
        return $states;
    }
}
