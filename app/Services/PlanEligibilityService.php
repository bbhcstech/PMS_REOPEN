<?php

namespace App\Services;

use App\Models\Central\Company;
use App\Models\Central\Plan;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PlanEligibilityService
{
    public const LEVEL_FREE     = 0;
    public const LEVEL_GOLD     = 1;
    public const LEVEL_PLATINUM = 2;
    public const LEVEL_DIAMOND  = 3;

    /**
     * Map a plan slug or Plan model to its numerical hierarchy level.
     */
    public static function getPlanLevel(Plan|\App\Models\SubscriptionPlan|string|null $plan): int
    {
        if (!$plan) {
            return self::LEVEL_FREE;
        }

        $slug = is_string($plan) ? strtolower(trim($plan)) : strtolower(trim($plan->slug ?? 'free'));

        return match ($slug) {
            'gold'     => self::LEVEL_GOLD,
            'platinum' => self::LEVEL_PLATINUM,
            'diamond'  => self::LEVEL_DIAMOND,
            default    => self::LEVEL_FREE,
        };
    }

    /**
     * Map a numerical level to canonical plan slug.
     */
    public static function getSlugForLevel(int $level): string
    {
        return match ($level) {
            self::LEVEL_GOLD     => 'gold',
            self::LEVEL_PLATINUM => 'platinum',
            self::LEVEL_DIAMOND  => 'diamond',
            default              => 'free',
        };
    }

    /**
     * Get the highest plan level achieved by a company.
     */
    public static function getHighestLevel(Company|\App\Models\Company $company): int
    {
        $companyLevel = (int) ($company->highest_plan_level ?? 0);
        $sub = $company->activeSubscription ?? $company->subscriptions()->latest()->first();
        $subLevel = $sub ? self::getPlanLevel($sub->plan) : 0;

        return max($companyLevel, $subLevel);
    }

    /**
     * Get the current plan level of a company.
     */
    public static function getCurrentLevel(Company|\App\Models\Company $company): int
    {
        $sub = $company->activeSubscription ?? $company->subscriptions()->latest()->first();
        return $sub ? self::getPlanLevel($sub->plan) : self::LEVEL_FREE;
    }

    /**
     * Get all plans allowed for a company to purchase/renew/upgrade to.
     * FREE is strictly excluded if company has ever been on a paid plan.
     */
    public function getAllowedPlans(Company|\App\Models\Company $company): Collection
    {
        $highest = self::getHighestLevel($company);
        $current = self::getCurrentLevel($company);
        $minLevel = max($highest, $current);

        $plans = Plan::on('central')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('monthly_price', 'asc')
            ->get();

        return $plans->filter(function (Plan $plan) use ($minLevel, $highest) {
            $level = self::getPlanLevel($plan);

            // Once a company enters a paid tier, FREE plan can NEVER be selected
            if ($highest > self::LEVEL_FREE && $level === self::LEVEL_FREE) {
                return false;
            }

            // Cannot select a plan lower than the highest level achieved
            return $level >= $minLevel;
        })->values();
    }

    /**
     * Check if company can upgrade to the target plan.
     */
    public function canUpgrade(Company|\App\Models\Company $company, Plan|\App\Models\SubscriptionPlan $targetPlan): bool
    {
        $targetLevel = self::getPlanLevel($targetPlan);
        $currentLevel = self::getCurrentLevel($company);

        return $targetLevel > $currentLevel;
    }

    /**
     * Check if company can renew the target plan (must match current plan).
     */
    public function canRenew(Company|\App\Models\Company $company, Plan|\App\Models\SubscriptionPlan $targetPlan): bool
    {
        $targetLevel = self::getPlanLevel($targetPlan);
        $currentLevel = self::getCurrentLevel($company);

        return $targetLevel === $currentLevel && $currentLevel > self::LEVEL_FREE;
    }

    /**
     * Downgrading is strictly forbidden under the plan lock system.
     */
    public function canDowngrade(Company|\App\Models\Company $company, Plan|\App\Models\SubscriptionPlan $targetPlan): bool
    {
        return false;
    }

    /**
     * Validate if a plan change is allowed for a company.
     * Throws InvalidArgumentException if invalid.
     */
    public function validatePlanChange(Company|\App\Models\Company $company, Plan|\App\Models\SubscriptionPlan $targetPlan): void
    {
        $targetLevel = self::getPlanLevel($targetPlan);
        $highestLevel = self::getHighestLevel($company);

        if ($highestLevel > self::LEVEL_FREE && $targetLevel === self::LEVEL_FREE) {
            throw new InvalidArgumentException("Plan Lock Policy Violation: Once a company has entered a paid subscription tier, it can NEVER return to the FREE plan.");
        }

        if ($targetLevel < $highestLevel) {
            $highestName = strtoupper(self::getSlugForLevel($highestLevel));
            $targetName = strtoupper($targetPlan->name ?? self::getSlugForLevel($targetLevel));
            throw new InvalidArgumentException("Plan Lock Policy Violation: Cannot downgrade from {$highestName} to {$targetName}. Companies may only renew their current tier or upgrade to a higher tier.");
        }
    }
}
