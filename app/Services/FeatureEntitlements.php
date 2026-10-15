<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for Super Admin "Manage Features" decisions (central.company_modules).
 *
 * Feature identity comes from config/workspace_modules.php (the tenant sidebar registry):
 * a feature is switched off when its own override, a legacy alias row of it, or any of its
 * parent categories is switched off for that company. Sibling features are never coupled,
 * so turning one feature off or on does not change any other feature.
 */
class FeatureEntitlements
{
    /** Legacy module slugs (not in the sidebar registry) that represent a registry feature. */
    private const LEGACY_ALIASES = [
        'deals' => 'crm-deals',
        'crm' => 'leads',
        'leave-management' => 'leaves',
        'user-management' => 'employees',
        'parent-departments' => 'departments',
        'hr-management' => 'hr',
        'hr-employees' => 'hr',
        'timesheets' => 'timelogs',
        'client' => 'clients',
        'collaborating_companies' => 'collaborating-companies',
        'documents' => 'my-documents',
        'standard-reports' => 'reports',
        'analytics' => 'reports',
        'advanced-reports' => 'reports',
        'activity-logs' => 'reports',
        'system-logs' => 'reports',
        'teams' => 'organization',
        'role-management' => 'role-permissions-settings',
    ];

    /** Parent categories that are not expressed in the sidebar registry but were always enforced. */
    private const EXTRA_PARENTS = [
        'products' => 'work',
        'orders' => 'work',
        'expenses' => 'payroll',
        'billing' => 'payroll',
    ];

    /** Per-request memo of override rows per central company id: slug => is_enabled. */
    private array $overrides = [];

    /** Registry slug a (possibly legacy) module slug stands for. */
    public function canonical(string $slug): string
    {
        return self::LEGACY_ALIASES[$slug] ?? $slug;
    }

    /** Legacy alias slugs that stand for the given registry feature. */
    public function aliasesOf(string $slug): array
    {
        return array_keys(array_filter(self::LEGACY_ALIASES, fn ($target) => $target === $slug));
    }

    public function isLegacyAlias(string $slug): bool
    {
        return isset(self::LEGACY_ALIASES[$slug]) && config('workspace_modules.'.$slug) === null;
    }

    /** The feature itself followed by its parent categories (registry parent / inherited module). */
    public function ancestry(string $slug): array
    {
        $chain = [];
        $current = $this->canonical($slug);
        while ($current && ! in_array($current, $chain, true)) {
            $chain[] = $current;
            $item = config('workspace_modules.'.$current);
            $current = ($item ? ($item['inherits'] ?: $item['parent']) : null) ?? (self::EXTRA_PARENTS[$current] ?? null);
        }
        return $chain;
    }

    /**
     * Explicit Super Admin decision for a feature: false when it or a parent is switched off,
     * true when it is explicitly switched on (and no parent is off), null when not configured.
     */
    public function overrideDecision(?int $centralCompanyId, string $slug): ?bool
    {
        if (! $centralCompanyId) {
            return null;
        }
        $rows = $this->overridesFor($centralCompanyId);
        $explicitOn = null;
        foreach ($this->ancestry($slug) as $depth => $feature) {
            foreach (array_merge([$feature], $this->aliasesOf($feature)) as $candidate) {
                if (! array_key_exists($candidate, $rows)) continue;
                if (! $rows[$candidate]) return false;
                if ($depth === 0) $explicitOn = true;
            }
        }
        return $explicitOn;
    }

    /** Parent category (registry slug) whose Super Admin override switches this feature off, if any. */
    public function disabledAncestor(?int $centralCompanyId, string $slug): ?string
    {
        if (! $centralCompanyId) {
            return null;
        }
        $rows = $this->overridesFor($centralCompanyId);
        foreach (array_slice($this->ancestry($slug), 1) as $parent) {
            foreach (array_merge([$parent], $this->aliasesOf($parent)) as $candidate) {
                if (array_key_exists($candidate, $rows) && ! $rows[$candidate]) {
                    return $parent;
                }
            }
        }
        return null;
    }

    /** Clears per-request memo after an override is written. */
    public function forget(?int $centralCompanyId = null): void
    {
        if ($centralCompanyId === null) $this->overrides = [];
        else unset($this->overrides[$centralCompanyId]);
    }

    private function overridesFor(int $centralCompanyId): array
    {
        if (! array_key_exists($centralCompanyId, $this->overrides)) {
            $this->overrides[$centralCompanyId] = DB::connection('central')->table('company_modules')
                ->join('modules', 'modules.id', '=', 'company_modules.module_id')
                ->where('company_modules.company_id', $centralCompanyId)
                ->get(['modules.slug', 'company_modules.is_enabled'])
                // A disabled row wins if a slug was ever stored twice.
                ->groupBy('slug')
                ->map(fn ($group) => $group->every(fn ($row) => (int) $row->is_enabled === 1))
                ->all();
        }
        return $this->overrides[$centralCompanyId];
    }

    /** Plan-module slugs that grant a feature (unchanged plan entitlement mapping). */
    public function planSlugs(string $featureSlug): array
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
