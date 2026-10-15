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
        try {
            $centralComp = $this->getConnectionName() === 'central'
                ? $this
                : (\App\Models\Central\Company::on('central')->find($centralCompanyId) ?? $this);

            $sub = $centralComp?->activeSubscription;
            if ($sub && $sub->plan_id) {
                $plan = $sub->plan ?? \App\Models\Central\Plan::on('central')->find($sub->plan_id);
                return app(\App\Services\PlanFeatureEntitlements::class)->allows($plan, $featureSlug);
            }
        } catch (\Throwable $e) {
            report($e);
            return false;
        }

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

}
