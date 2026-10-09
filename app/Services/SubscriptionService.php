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
     * Dynamically evaluate the accurate company status from database records.
     */
    public function evaluateCompanyStatus(Company|\App\Models\Company $company): string
    {
        $centralComp = $this->resolveCentralCompany($company);

        // 1. Super Admin manual suspension always takes top priority
        if (!empty($centralComp->manually_suspended) || strtolower((string)$centralComp->status) === 'suspended') {
            return 'suspended';
        }

        // 2. Inactive status set explicitly
        if (strtolower((string)$centralComp->status) === 'inactive') {
            return 'inactive';
        }

        // 3. Check for subscriptions in central DB
        $sub = Subscription::on('central')
            ->where('company_id', $centralComp->id)
            ->whereIn('status', ['active', 'trial', 'expired', 'suspended'])
            ->latest('id')
            ->first();

        if ($sub && $sub->ends_at) {
            $subEnds = is_string($sub->ends_at) ? Carbon::parse($sub->ends_at) : $sub->ends_at;
            // The end date is the last day of access (inclusive), matching activeSubscription and the expiry shown to users.
            if ($sub->status === 'expired' || $subEnds->copy()->endOfDay()->isPast()) {
                return 'expired';
            }
            return 'active';
        }

        // 4. Check trial_ends_at
        if ($centralComp->trial_ends_at) {
            $trialEnds = is_string($centralComp->trial_ends_at) ? Carbon::parse($centralComp->trial_ends_at) : $centralComp->trial_ends_at;
            if ($trialEnds->isPast()) {
                return 'expired';
            }
            return (strtolower((string)$centralComp->status) === 'trial') ? 'trial' : 'active';
        }

        // 5. If no subscription and no trial_ends_at (e.g. provisioned company with 30-day initial period)
        if ($centralComp->created_at) {
            $createdAt = is_string($centralComp->created_at) ? Carbon::parse($centralComp->created_at) : $centralComp->created_at;
            if ($createdAt->copy()->addDays(30)->isPast()) {
                return 'expired';
            }
        }

        return $centralComp->status ?: 'active';
    }

    /**
     * Synchronize company status to the database idempotently.
     */
    /**
     * Bring back subscriptions that a Super Admin suspension paused, once the company is no longer suspended:
     * still-running terms become active again, ended ones expired. Blank statuses are included because legacy
     * enum columns could not store "suspended". Returns the number of subscriptions restored.
     */
    public function restorePausedSubscriptions(Company|\App\Models\Company $company): int
    {
        $centralComp = $this->resolveCentralCompany($company);
        if (!empty($centralComp->manually_suspended) || strtolower((string) $centralComp->status) === 'suspended') {
            return 0;
        }

        $today = now()->toDateString();
        $paused = fn () => Subscription::on('central')
            ->where('company_id', $centralComp->id)
            ->where(function ($q) {
                $q->whereIn('status', ['suspended', ''])->orWhereNull('status');
            });

        $restored = (clone $paused())->where(function ($q) use ($today) {
            $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today);
        })->update(['status' => 'active']);

        $restored += (clone $paused())->whereDate('ends_at', '<', $today)->update(['status' => 'expired']);

        return $restored;
    }

    public function syncCompanyStatus(Company|\App\Models\Company $company): string
    {
        $centralComp = $this->resolveCentralCompany($company);
        // Self-heal companies whose subscriptions stayed paused after a reactivation.
        try {
            $this->restorePausedSubscriptions($centralComp);
        } catch (\Throwable $e) {}
        $newStatus = $this->evaluateCompanyStatus($centralComp);

        if ($centralComp->status !== $newStatus) {
            $centralComp->status = $newStatus;
            if ($newStatus === 'expired') {
                try {
                    Subscription::on('central')
                        ->where('company_id', $centralComp->id)
                        ->whereIn('status', ['active', 'trial'])
                        ->whereDate('ends_at', '<', now()->toDateString())
                        ->update(['status' => 'expired']);
                } catch (\Throwable $e) {}
            }
            $centralComp->save();

            if ($company !== $centralComp) {
                try {
                    $company->status = $newStatus;
                    $company->save();
                } catch (\Throwable $e) {}
            }
        }

        return $newStatus;
    }

    /**
     * Dynamically check real-time expiration of trial or paid subscription.
     */
    public function isExpired(Company|\App\Models\Company $company): bool
    {
        if (strtolower((string)$company->status) === 'expired') {
            return true;
        }

        if (!empty($company->manually_suspended)) {
            return false;
        }

        return $this->evaluateCompanyStatus($company) === 'expired';
    }

    /**
     * Check if company is suspended.
     */
    public function isSuspended(Company|\App\Models\Company $company): bool
    {
        return !empty($company->manually_suspended) || strtolower((string)$company->status) === 'suspended';
    }

    /**
     * Check if company access is restricted (either suspended or expired).
     */
    public function isRestricted(Company|\App\Models\Company $company): bool
    {
        return $this->isSuspended($company) || $this->isExpired($company);
    }

    /**
     * Calculate remaining days dynamically from trial_ends_at or active subscription ends_at.
     */
    public function getRemainingDays(Company|\App\Models\Company $company): int
    {
        if ($this->isSuspended($company) || $this->isExpired($company)) {
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
     * If expired, transitions company to EXPIRED idempotently.
     */
    public function checkRealtimeExpiration(Company|\App\Models\Company $company): void
    {
        $this->syncCompanyStatus($company);
    }

    /**
     * Process expiration for a trial or paid subscription.
     * Transitions the company status to 'expired' at backend database level.
     */
    public function processExpiration(Company|\App\Models\Company $company, string $reason = 'Subscription/Trial period expired.'): void
    {
        $centralComp = $this->resolveCentralCompany($company);

        DB::connection('central')->transaction(function () use ($centralComp, $company, $reason) {
            $centralComp->status = 'expired';
            $centralComp->manually_suspended = false;
            $centralComp->save();

            if ($company !== $centralComp) {
                try {
                    $company->status = 'expired';
                    $company->manually_suspended = false;
                    $company->save();
                } catch (\Throwable $e) {}
            }

            $sub = Subscription::on('central')->where('company_id', $centralComp->id)->where('status', 'active')->latest()->first();
            if ($sub) {
                $sub->status = 'expired';
                $sub->expired_at = now();
                $sub->save();
            }

            // Record history
            $this->historyService->log(
                company: $centralComp,
                action: 'SUBSCRIPTION_EXPIRED',
                subscription: $sub,
                reason: $reason
            );
        });

        Log::info("Company ID {$company->id} ('{$company->name}') marked EXPIRED.");
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
        ?string $reason = null,
        ?int $supportRequestId = null
    ): Subscription {
        $centralComp = $this->resolveCentralCompany($company);

        // Enforce Plan Lock & Downgrade Validation
        $supportRequest = null;
        if ($supportRequestId !== null) {
            abort_unless(TenantScope::isPlatformAdmin(), 403);
            $supportRequest = \App\Models\Central\CompanyComplaint::whereKey($supportRequestId)
                ->where('company_id', $centralComp->id)->where('requested_plan_id', $plan->id)
                ->where('plan_request_status', 'pending')->firstOrFail();
            $active = $centralComp->activeSubscription;
            if (!$active || (int) $active->plan_id !== (int) $supportRequest->requested_from_plan_id) {
                throw new \InvalidArgumentException('The subscription changed after this request. Ask the company to submit a new request.');
            }
        } else {
            $this->eligibilityService->validatePlanChange($centralComp, $plan);
        }

        $targetLevel = PlanEligibilityService::getPlanLevel($plan);
        $currentLevel = PlanEligibilityService::getCurrentLevel($centralComp);
        $highestLevel = PlanEligibilityService::getHighestLevel($centralComp);

        $previousSub = Subscription::on('central')->where('company_id', $centralComp->id)->whereIn('status', ['active', 'trial', 'expired'])->latest()->first();
        $previousPlan = $previousSub?->plan;

        // Determine Lifecycle Action
        if ($supportRequest) {
            $action = 'PLAN_DOWNGRADED_BY_APPROVAL';
        } elseif ($highestLevel === PlanEligibilityService::LEVEL_FREE) {
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
            $startsAt, $endsAt, $action, $previousSub, $previousPlan, $performedBy, $reason, $supportRequest
        ) {
            // SAFETY GUARD: Renewal must NOT automatically remove a Super Admin manual suspension.
            // Subscription is renewed, but company status remains 'suspended' until Super Admin explicitly lifts it.
            $wasSuspended = (strtolower((string) $centralComp->status) === 'suspended' || !empty($centralComp->suspended_at));
            $isManual = !empty($centralComp->manually_suspended);

            // Update company record
            if ($isManual) {
                $centralComp->status = 'suspended';
            } else {
                $centralComp->status = 'active';
                $centralComp->suspended_at = null;
            }
            $centralComp->highest_plan_level = $newHighestLevel;
            $centralComp->highest_plan_slug = $newHighestSlug;
            if ($supportRequest || $centralComp->approved_plan_floor !== null) $centralComp->approved_plan_floor = $targetLevel;
            $centralComp->save();
            if ($supportRequest) $supportRequest->update(['plan_request_status' => 'approved', 'plan_reviewed_by' => auth('super_admin')->id() ?? auth()->id(), 'plan_reviewed_at' => now()]);

            if ($company !== $centralComp) {
                try {
                    $company->status = $isManual ? 'suspended' : 'active';
                    if (!$isManual) {
                        $company->suspended_at = null;
                    }
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
