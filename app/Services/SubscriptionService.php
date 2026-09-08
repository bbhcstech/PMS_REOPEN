<?php

namespace App\Services;

use App\Models\Central\Company;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function __construct(
        protected PlanEligibilityService $eligibilityService,
        protected SubscriptionHistoryService $historyService
    ) {}

    /**
     * Resolve Central Company instance if tenant model passed.
     */
    protected function resolveCentralCompany(Company|\App\Models\Company $company): Company
    {
        if ($company instanceof Company) {
            return $company;
        }

        return Company::on('central')->find($company->id) ?? $company;
    }

    /**
     * Check if subscription or trial is currently active and unexpired.
     */
    public function isSubscriptionActive(Company|\App\Models\Company $company): bool
    {
        if ($this->isSuspended($company)) {
            return false;
        }

        if ($this->isExpired($company)) {
            return false;
        }

        return in_array($company->status, ['active', 'trial'], true);
    }

    /**
     * Check if the company is currently on an active Free Trial.
     */
    public function isTrialActive(Company|\App\Models\Company $company): bool
    {
        if ($company->status !== 'trial') {
            return false;
        }

        if (!$company->trial_ends_at) {
            return true;
        }

        $endsAt = is_string($company->trial_ends_at) ? Carbon::parse($company->trial_ends_at) : $company->trial_ends_at;

        return $endsAt->isFuture();
    }

    /**
     * Dynamically check real-time expiration of trial or paid subscription.
     */
    public function isExpired(Company|\App\Models\Company $company): bool
    {
        if ($company->status === 'suspended') {
            return true;
        }

        if ($company->status === 'trial') {
            if (!$company->trial_ends_at) {
                return false;
            }
            $trialEnds = is_string($company->trial_ends_at) ? Carbon::parse($company->trial_ends_at) : $company->trial_ends_at;
            return $trialEnds->isPast();
        }

        $sub = $company->activeSubscription ?? Subscription::on('central')->where('company_id', $company->id)->where('status', 'active')->latest()->first();
        if ($sub && $sub->ends_at) {
            $subEnds = is_string($sub->ends_at) ? Carbon::parse($sub->ends_at) : $sub->ends_at;
            return $subEnds->isPast();
        }

        return false;
    }

    /**
     * Check if company is suspended.
     */
    public function isSuspended(Company|\App\Models\Company $company): bool
    {
        return strtolower((string)$company->status) === 'suspended';
    }

    /**
     * Calculate remaining days dynamically from trial_ends_at or active subscription ends_at.
     */
    public function getRemainingDays(Company|\App\Models\Company $company): int
    {
        if ($this->isSuspended($company)) {
            return 0;
        }

        if ($company->status === 'trial' && $company->trial_ends_at) {
            $trialEnds = is_string($company->trial_ends_at) ? Carbon::parse($company->trial_ends_at) : $company->trial_ends_at;
            return max(0, (int) ceil(now()->diffInDays($trialEnds, false)));
        }

        $sub = $company->activeSubscription ?? Subscription::on('central')->where('company_id', $company->id)->where('status', 'active')->latest()->first();
        if ($sub && $sub->ends_at) {
            $subEnds = is_string($sub->ends_at) ? Carbon::parse($sub->ends_at) : $sub->ends_at;
            return max(0, (int) ceil(now()->diffInDays($subEnds, false)));
        }

        return 0;
    }

    /**
     * Real-time expiration check executed on incoming web requests.
     * If expired, transitions company to SUSPENDED idempotently.
     */
    public function checkRealtimeExpiration(Company|\App\Models\Company $company): void
    {
        if ($this->isSuspended($company)) {
            return;
        }

        if ($this->isExpired($company)) {
            $this->processExpiration($company);
        }
    }

    /**
     * Process expiration for a trial or paid subscription.
     * Suspends the company immediately while preserving all data.
     */
    public function processExpiration(Company|\App\Models\Company $company, string $reason = 'Subscription/Trial period expired.'): void
    {
        if ($this->isSuspended($company)) {
            return;
        }

        $centralComp = $this->resolveCentralCompany($company);

        DB::connection('central')->transaction(function () use ($centralComp, $company, $reason) {
            $previousStatus = $centralComp->status;
            $centralComp->status = 'suspended';
            $centralComp->suspended_at = now();
            $centralComp->save();

            if ($company !== $centralComp) {
                try {
                    $company->status = 'suspended';
                    $company->suspended_at = now();
                    $company->save();
                } catch (\Throwable $e) {}
            }

            $sub = Subscription::on('central')->where('company_id', $centralComp->id)->where('status', 'active')->latest()->first();
            if ($sub) {
                $sub->status = 'expired';
                $sub->expired_at = now();
                $sub->suspended_at = now();
                $sub->save();
            }

            // Record history
            $this->historyService->log(
                company: $centralComp,
                action: 'SUBSCRIPTION_EXPIRED',
                subscription: $sub,
                reason: $reason
            );

            $this->historyService->log(
                company: $centralComp,
                action: 'COMPANY_SUSPENDED',
                subscription: $sub,
                reason: 'Account automatically suspended due to subscription expiration.'
            );
        });

        Log::info("Company ID {$company->id} ('{$company->name}') automatically suspended due to expiration.");
    }

    /**
     * Initialize 30-day Free Trial for a newly created company.
     */
    public function initializeTrial(Company|\App\Models\Company $company): Subscription
    {
        $centralComp = $this->resolveCentralCompany($company);

        $freePlan = Plan::on('central')->where('slug', 'free')->first()
            ?? Plan::on('central')->orderBy('id')->first();

        $trialEndsAt = now()->addDays(30);

        $centralComp->status = 'trial';
        $centralComp->trial_ends_at = $trialEndsAt;
        $centralComp->highest_plan_level = PlanEligibilityService::LEVEL_FREE;
        $centralComp->highest_plan_slug = 'free';
        $centralComp->save();

        if ($company !== $centralComp) {
            try {
                $company->status = 'trial';
                $company->trial_ends_at = $trialEndsAt;
                $company->highest_plan_level = PlanEligibilityService::LEVEL_FREE;
                $company->highest_plan_slug = 'free';
                $company->save();
            } catch (\Throwable $e) {}
        }

        $subscription = Subscription::on('central')->create([
            'company_id'         => $centralComp->id,
            'plan_id'            => $freePlan->id,
            'billing_cycle'      => 'monthly',
            'starts_at'          => now()->toDateString(),
            'ends_at'            => $trialEndsAt->toDateString(),
            'trial_ends_at'      => $trialEndsAt->toDateString(),
            'price'              => 0,
            'status'             => 'trial',
            'auto_renew'         => false,
            'highest_plan_level' => PlanEligibilityService::LEVEL_FREE,
            'current_plan_level' => PlanEligibilityService::LEVEL_FREE,
            'activated_at'       => now(),
        ]);

        $this->historyService->log(
            company: $centralComp,
            action: 'TRIAL_STARTED',
            newPlan: $freePlan,
            subscription: $subscription,
            reason: 'Company created with 30-day Free Trial entitlement.'
        );

        return $subscription;
    }

    /**
     * Activate, renew, or upgrade a paid subscription plan for a company.
     * Enforces the zero-downgrade plan lock rule.
     */
    public function activateOrUpgradePlan(
        Company|\App\Models\Company $company,
        Plan|\App\Models\SubscriptionPlan $plan,
        string $billingCycle = 'monthly',
        ?string $performedBy = null,
        ?string $reason = null
    ): Subscription {
        $centralComp = $this->resolveCentralCompany($company);

        // Enforce Plan Lock & Downgrade Validation
        $this->eligibilityService->validatePlanChange($centralComp, $plan);

        $targetLevel = PlanEligibilityService::getPlanLevel($plan);
        $currentLevel = PlanEligibilityService::getCurrentLevel($centralComp);
        $highestLevel = PlanEligibilityService::getHighestLevel($centralComp);

        $previousSub = Subscription::on('central')->where('company_id', $centralComp->id)->whereIn('status', ['active', 'trial', 'expired'])->latest()->first();
        $previousPlan = $previousSub?->plan;

        // Determine Lifecycle Action
        if ($highestLevel === PlanEligibilityService::LEVEL_FREE) {
            $action = 'PLAN_PURCHASED';
        } elseif ($targetLevel > $currentLevel) {
            $action = 'PLAN_UPGRADED';
        } else {
            $action = 'PLAN_RENEWED';
        }

        $startsAt = now();
        $endsAt = $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth();

        $newHighestLevel = max($highestLevel, $targetLevel);
        $newHighestSlug = PlanEligibilityService::getSlugForLevel($newHighestLevel);

        return DB::connection('central')->transaction(function () use (
            $centralComp, $company, $plan, $billingCycle, $targetLevel, $newHighestLevel, $newHighestSlug,
            $startsAt, $endsAt, $action, $previousSub, $previousPlan, $performedBy, $reason
        ) {
            // Update company record
            $wasSuspended = $centralComp->status === 'suspended';
            $centralComp->status = 'active';
            $centralComp->suspended_at = null;
            $centralComp->highest_plan_level = $newHighestLevel;
            $centralComp->highest_plan_slug = $newHighestSlug;
            $centralComp->save();

            if ($company !== $centralComp) {
                try {
                    $company->status = 'active';
                    $company->suspended_at = null;
                    $company->highest_plan_level = $newHighestLevel;
                    $company->highest_plan_slug = $newHighestSlug;
                    $company->save();
                } catch (\Throwable $e) {}
            }

            // Deactivate existing active subscriptions
            Subscription::on('central')->where('company_id', $centralComp->id)->where('status', 'active')->update(['status' => 'cancelled']);

            // Create new active subscription
            $price = method_exists($plan, 'getPriceForCycle') ? $plan->getPriceForCycle($billingCycle) : ($billingCycle === 'yearly' ? ($plan->yearly_price ?? 0) : ($plan->monthly_price ?? 0));

            $subscription = Subscription::on('central')->create([
                'company_id'         => $centralComp->id,
                'plan_id'            => $plan->id,
                'previous_plan_id'   => $previousPlan?->id,
                'billing_cycle'      => $billingCycle,
                'starts_at'          => $startsAt->toDateString(),
                'ends_at'            => $endsAt->toDateString(),
                'price'              => $price,
                'status'             => 'active',
                'auto_renew'         => true,
                'highest_plan_level' => $newHighestLevel,
                'current_plan_level' => $targetLevel,
                'activated_at'       => now(),
                'renewed_at'         => $action === 'PLAN_RENEWED' ? now() : null,
                'upgraded_at'        => $action === 'PLAN_UPGRADED' ? now() : null,
            ]);

            // Sync plan modules to company_modules in central DB
            $planSlug = strtolower($plan->slug ?? '');
            $isPaidTier = in_array($planSlug, ['gold', 'platinum', 'diamond'], true);

            $planModuleIds = DB::connection('central')->table('plan_modules')
                ->where('plan_id', $plan->id)
                ->pluck('module_id');

            if ($isPaidTier || $planModuleIds->isEmpty()) {
                $allModuleIds = DB::connection('central')->table('modules')->pluck('id');
                if ($isPaidTier && $allModuleIds->isNotEmpty()) {
                    foreach ($allModuleIds as $mId) {
                        DB::connection('central')->table('plan_modules')->updateOrInsert(
                            ['plan_id' => $plan->id, 'module_id' => $mId],
                            ['updated_at' => now()]
                        );
                    }
                }
                $targetModuleIds = $allModuleIds;
            } else {
                $targetModuleIds = $planModuleIds;
            }

            if ($targetModuleIds->isNotEmpty()) {
                foreach ($targetModuleIds as $modId) {
                    DB::connection('central')->table('company_modules')->updateOrInsert(
                        ['company_id' => $centralComp->id, 'module_id' => $modId],
                        ['is_enabled' => 1, 'updated_at' => now()]
                    );
                }
            }

            // Log Lifecycle History
            $this->historyService->log(
                company: $centralComp,
                action: $action,
                newPlan: $plan,
                previousPlan: $previousPlan,
                subscription: $subscription,
                performedBy: $performedBy,
                reason: $reason
            );

            if ($wasSuspended) {
                $this->historyService->log(
                    company: $centralComp,
                    action: 'COMPANY_REACTIVATED',
                    newPlan: $plan,
                    previousPlan: $previousPlan,
                    subscription: $subscription,
                    performedBy: $performedBy,
                    reason: 'Company reactivated following subscription activation.'
                );
            }

            try {
                if (class_exists(\App\Models\Central\CentralNotification::class)) {
                    \App\Models\Central\CentralNotification::createNotification([
                        'company_id'        => $centralComp->id,
                        'type'              => 'SUBSCRIPTION_ACTIVATED',
                        'title'             => 'Subscription Activated',
                        'message'           => "Your {$plan->name} subscription has been successfully activated.\n\nYour organization is now active.\n\nExpires:\n" . $endsAt->format('d F Y'),
                        'severity'          => 'SUCCESS',
                        'related_module'    => 'Subscriptions',
                        'related_record_id' => 'ACTIVATION_' . $subscription->id,
                        'action_url'        => route('notifications.all'),
                        'target_audience'   => 'company_admin',
                    ]);
                }
            } catch (\Throwable $e) {}

            return $subscription;
        });
    }
}
