<?php

namespace App\Models\Concerns;

/**
 * Shared feature entitlement for the central and tenant Company models, so the sidebar, routes and
 * Super Admin "Manage Features" always get the same answer whichever model CompanyContext returns.
 *
 * Order: account state -> Super Admin override (feature, legacy alias rows, parent categories)
 * -> subscription plan entitlement -> active/trial default.
 */
trait ResolvesCompanyFeatures
{
    public function hasFeature(string $featureSlug): bool
    {
        if ($this->isSuspended() || $this->isExpired() || in_array(strtolower((string) $this->status), ['suspended', 'expired'], true)) {
            return false;
        }

        if (in_array($featureSlug, ['home', 'profile'], true)) {
            return true;
        }

        $centralCompanyId = $this->featureCentralCompanyId();

        // 1. Explicit Super Admin decisions (feature, its legacy alias rows and parent categories).
        try {
            $decision = app(\App\Services\FeatureEntitlements::class)->overrideDecision($centralCompanyId, $featureSlug);
            if ($decision !== null) {
                return $decision;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // 2. Active Subscription Plan Module Entitlements
        $slugs = $this->featurePlanSlugs($featureSlug);
        try {
            $centralComp = $this->getConnectionName() === 'central'
                ? $this
                : (\App\Models\Central\Company::on('central')->find($centralCompanyId) ?? $this);

            $sub = $centralComp?->activeSubscription ?? $this->activeSubscription;
            if ($sub && $sub->plan_id) {
                $plan = $sub->plan ?? \App\Models\Central\Plan::on('central')->find($sub->plan_id);
                $planSlug = strtolower($plan?->slug ?? '');

                $planModules = \Illuminate\Support\Facades\DB::connection('central')
                    ->table('plan_modules')
                    ->join('modules', 'modules.id', '=', 'plan_modules.module_id')
                    ->where('plan_modules.plan_id', $sub->plan_id)
                    ->pluck('modules.slug')
                    ->toArray();

                if (! empty($planModules)) {
                    foreach ($slugs as $slug) {
                        if (in_array($slug, $planModules, true)) {
                            return true;
                        }
                    }
                    if (in_array($featureSlug, ['community', 'products', 'orders'], true)) {
                        return true;
                    }
                    return false;
                }

                // Standard paid plans (gold, platinum, diamond) include all base platform features by default if plan_modules not explicitly populated
                if (in_array($planSlug, ['gold', 'platinum', 'diamond'], true)) {
                    return true;
                }

                // Free tier default allowed features
                if (in_array($planSlug, ['free', 'trial', 'starter'], true)) {
                    $freeAllowed = ['dashboard', 'notifications', 'organization', 'hr', 'employees', 'attendance', 'leave-management', 'leaves', 'holidays', 'events', 'my-documents', 'community', 'tickets', 'settings', 'products', 'orders'];
                    foreach ($slugs as $slug) {
                        if (in_array($slug, $freeAllowed, true)) {
                            return true;
                        }
                    }
                    return false;
                }
            }
        } catch (\Throwable $e) {}

        // 3. Default for active or trial accounts without explicit restrictions
        if ($this->isOnTrial() || $this->isActive()) {
            return true;
        }

        return false;
    }

    /** Central company id whose Super Admin overrides apply to this company. */
    protected function featureCentralCompanyId(): ?int
    {
        if ($this instanceof \App\Models\Central\Company || $this->getConnectionName() === 'central') {
            return $this->id ? (int) $this->id : null;
        }
        return method_exists($this, 'getCentralCompanyId') ? $this->getCentralCompanyId() : null;
    }

    /** Plan-module slugs that grant a feature (unchanged plan entitlement mapping). */
    protected function featurePlanSlugs(string $featureSlug): array
    {
        $inheritedFeature = config('workspace_modules.'.$featureSlug.'.inherits');
        $slugs = match ($inheritedFeature ?: $featureSlug) {
            'crm', 'deals', 'crm-deals' => ['crm-deals', 'crm', 'deals', 'leads-contacts'],
            'leads', 'leads-contacts' => ['leads-contacts', 'leads', 'crm'],
            'leaves', 'leave-management', 'leave-settings' => ['leave-management', 'leaves', 'leave-settings'],
            'employees', 'user-management' => ['employees', 'user-management'],
            'designations' => ['designations'],
            'departments', 'parent-departments' => ['departments', 'parent-departments'],
            'attendance', 'attendance-settings' => ['attendance', 'attendance-settings'],
            'holidays', 'holiday-settings' => ['holidays', 'holiday-settings'],
            'awards', 'recognition', 'letterhead' => ['recognition', 'awards', 'letterhead'],
            'recruitment', 'recruitment-settings' => ['recruitment', 'recruitment-settings'],
            'appraisal', 'performance-settings' => ['appraisal', 'performance-settings'],
            'hr', 'hr-management' => ['hr', 'hr-management', 'hr-employees'],
            'projects' => ['projects'],
            'tasks' => ['tasks'],
            'timelogs', 'timesheets' => ['timesheets', 'timelogs'],
            'work' => ['work'],
            'payroll', 'payslips', 'salary-structures', 'employee-salary', 'my-payslips',
            'payroll-processing', 'payroll-history', 'formula-builder' => ['payroll'],
            'expenses' => ['expenses'],
            'billing' => ['billing', 'expenses'],
            'clients', 'client' => ['clients', 'client', 'leads-contacts'],
            'products' => ['products', 'projects', 'work'],
            'orders' => ['orders', 'projects', 'work'],
            'events' => ['events'],
            'my-documents', 'documents' => ['my-documents', 'documents'],
            'community' => ['community'],
            'collaborating-companies', 'collaborating_companies' => ['collaborating-companies', 'collaborating_companies'],
            'reports', 'standard-reports', 'analytics', 'advanced-reports', 'company-complaints', 'activity-logs', 'system-logs' => ['reports', 'standard-reports', 'analytics', 'advanced-reports', 'company-complaints', 'activity-logs', 'system-logs'],
            'organization', 'teams' => ['organization', 'teams'],
            'tickets' => ['tickets'],
            'contracts' => ['contracts'],
            'notifications', 'notification-settings' => ['notifications', 'notification-settings'],
            'settings' => ['settings'],
            'settings-dashboard' => ['settings-dashboard', 'settings'],
            'company-profile-settings' => ['company-profile-settings', 'settings'],
            'organization-details-settings' => ['organization-details-settings', 'settings'],
            'business-address-settings' => ['business-address-settings', 'settings'],
            'work-schedule-settings' => ['work-schedule-settings', 'settings'],
            'security-settings' => ['security-settings', 'settings'],
            'change-password-settings' => ['change-password-settings', 'settings'],
            'role-permissions-settings' => ['role-permissions-settings', 'role-management', 'settings'],
            'localization-settings' => ['localization-settings', 'settings'],
            'terms-policy-settings' => ['terms-policy-settings', 'settings'],
            default => [$featureSlug],
        };
        if ($inheritedFeature) array_unshift($slugs, $featureSlug);
        return $slugs;
    }
}
