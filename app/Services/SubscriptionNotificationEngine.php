<?php

namespace App\Services;

use App\Models\Central\Company;
use App\Models\Central\CentralNotification;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

class SubscriptionNotificationEngine
{
    public function __construct(
        protected PlanEligibilityService $eligibilityService,
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Scan all tenant companies and generate intelligent subscription alerts,
     * enforce 7-day daily countdown warnings, handle 0-day auto suspension,
     * and issue daily suspension reminders.
     */
    public function scanAndGenerateAlerts(?int $targetCompanyId = null): int
    {
        $generatedCount = 0;
        $today = now()->startOfDay();

        $query = Company::on('central')->with(['subscriptions.plan']);
        if ($targetCompanyId) {
            $query->where('id', $targetCompanyId);
        }
        $companies = $query->get();

        foreach ($companies as $company) {
            try {
                // 1. Handle Already Suspended Companies -> Issue Daily Suspension Reminder
                if ($this->subscriptionService->isSuspended($company)) {
                    $generatedCount += $this->processSuspendedReminder($company, $today);
                    continue;
                }

                // Resolve expiration date & plan info for active/trial companies
                $sub = $company->subscriptions->where('status', 'active')->first();
                $trialEndsAt = $company->trial_ends_at;

                $expiryDate = null;
                $planName = 'FREE';
                $isTrial = false;

                if ($sub && $sub->ends_at) {
                    $expiryDate = Carbon::parse($sub->ends_at)->startOfDay();
                    $planName = strtoupper($sub->plan?->name ?? ($company->highest_plan_slug ? strtoupper($company->highest_plan_slug) : 'PAID'));
                } elseif ($company->isOnTrial() && $trialEndsAt) {
                    $expiryDate = Carbon::parse($trialEndsAt)->startOfDay();
                    $planName = '30-day FREE Trial';
                    $isTrial = true;
                }

                if (!$expiryDate) {
                    continue;
                }

                $daysLeft = (int) $today->diffInDays($expiryDate, false);

                // 2. Expiration Day (remaining_days <= 0) -> Transition to EXPIRED & SUSPENDED
                if ($daysLeft <= 0) {
                    $reason = $isTrial
                        ? '30-day Free trial period completed without purchasing a paid plan.'
                        : "Paid subscription for {$planName} reached expiration date.";

                    $this->subscriptionService->processExpiration($company, $reason);
                    $generatedCount += $this->processExpiredNotification($company, $planName, $expiryDate, $isTrial, $today);
                    // Also immediately issue suspended reminder if needed
                    $generatedCount += $this->processSuspendedReminder($company, $today);
                    continue;
                }

                // 3. Daily Warning Countdown during final 7 days (7, 6, 5, 4, 3, 2, 1)
                if ($daysLeft <= 7 && $daysLeft >= 1) {
                    $generatedCount += $this->processDailyWarning($company, $planName, $expiryDate, $daysLeft, $isTrial, $today);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("SubscriptionNotificationEngine error for company {$company->id}: " . $e->getMessage());
            }
        }

        return $generatedCount;
    }

    /**
     * Process daily warning notification for days 7, 6, 5, 4, 3, 2, 1.
     */
    protected function processDailyWarning(Company $company, string $planName, Carbon $expiryDate, int $daysLeft, bool $isTrial, Carbon $today): int
    {
        $dedupKey = "EXPIRY_DAY_{$daysLeft}_{$company->id}_" . $today->toDateString();

        // Idempotency check: Ensure only 1 notification per company per calendar day
        $existsToday = CentralNotification::on('central')
            ->where('company_id', $company->id)
            ->where('related_module', 'Subscriptions')
            ->where('related_record_id', $dedupKey)
            ->exists();

        if ($existsToday) {
            return 0;
        }

        // Severity classification based on remaining days
        $severity = match (true) {
            $daysLeft <= 1 => 'CRITICAL',
            $daysLeft <= 4 => 'WARNING',
            default        => 'INFO',
        };

        $expiryDateFormatted = $expiryDate->format('d F Y');
        $allowedPlans = $this->eligibilityService->getAllowedPlans($company);

        // Build plan-specific options text
        $optionsText = $this->buildAllowedOptionsText($company, $allowedPlans, $isTrial);

        if ($isTrial) {
            $title = $daysLeft <= 3 ? "Urgent Free Trial Warning" : "Free Trial Ending Soon";
            $message = "Your 30-day FREE trial expires in {$daysLeft} day(s) on {$expiryDateFormatted}.\n\nChoose a paid subscription before the trial expires to continue using the Project Management System.\n\nAvailable Plans:\n{$optionsText}";
        } else {
            $title = match (true) {
                $daysLeft <= 1 => "Final Subscription Warning",
                $daysLeft <= 3 => "Urgent Subscription Renewal",
                default        => "Subscription Renewal Reminder",
            };

            $consequence = $daysLeft <= 1
                ? "After expiration tomorrow, your organization will be suspended and normal PMS features will no longer be accessible."
                : "If no eligible subscription is activated, your organization will be suspended automatically.";

            $message = "Your {$planName} subscription expires in {$daysLeft} day(s) on {$expiryDateFormatted}.\n\nPlease renew your existing {$planName} subscription or upgrade to a higher plan to continue using the PMS without interruption.\n\nExpiration Date:\n{$expiryDateFormatted}\n\nAvailable Options:\n{$optionsText}\n\n{$consequence}";
        }

        $superAdminActionUrl = Route::has('super-admin.subscriptions.index') ? route('super-admin.subscriptions.index') : url('/superadmin/subscriptions');
        $companyAdminActionUrl = route('subscription.suspended');

        // Create Company Admin notification
        CentralNotification::createNotification([
            'company_id'        => $company->id,
            'type'              => "SUBSCRIPTION_{$daysLeft}_DAYS",
            'title'             => $title,
            'message'           => $message,
            'severity'          => $severity,
            'related_module'    => 'Subscriptions',
            'related_record_id' => $dedupKey,
            'action_url'        => $companyAdminActionUrl,
            'target_audience'   => 'company_admin',
        ]);

        // Create Super Admin notification
        CentralNotification::createNotification([
            'company_id'        => $company->id,
            'type'              => "SUBSCRIPTION_{$daysLeft}_DAYS",
            'title'             => "{$title} — {$company->name}",
            'message'           => "Company '{$company->name}' ({$planName}) expires in {$daysLeft} days on {$expiryDateFormatted}.",
            'severity'          => $severity,
            'related_module'    => 'Subscriptions',
            'related_record_id' => $dedupKey,
            'action_url'        => $superAdminActionUrl,
            'target_audience'   => 'super_admin',
        ]);

        return 1;
    }

    /**
     * Process immediate notification upon subscription expiration.
     */
    protected function processExpiredNotification(Company $company, string $planName, Carbon $expiryDate, bool $isTrial, Carbon $today): int
    {
        $dedupKey = "EXPIRY_EXPIRED_{$company->id}_" . $today->toDateString();

        $exists = CentralNotification::on('central')
            ->where('company_id', $company->id)
            ->where('related_module', 'Subscriptions')
            ->where('related_record_id', $dedupKey)
            ->exists();

        if ($exists) {
            return 0;
        }

        $expiryDateFormatted = $expiryDate->format('d F Y');
        $allowedPlans = $this->eligibilityService->getAllowedPlans($company);
        $optionsText = $this->buildAllowedOptionsText($company, $allowedPlans, $isTrial);

        $message = "Your {$planName} subscription expired on {$expiryDateFormatted}.\n\nYour organization has been suspended because no eligible subscription was renewed or activated.\n\nYour data has not been deleted.\n\nRenew or upgrade your plan to restore access.\n\nAvailable Options:\n{$optionsText}";

        CentralNotification::createNotification([
            'company_id'        => $company->id,
            'type'              => 'SUBSCRIPTION_EXPIRED',
            'title'             => 'Subscription Expired',
            'message'           => $message,
            'severity'          => 'CRITICAL',
            'related_module'    => 'Subscriptions',
            'related_record_id' => $dedupKey,
            'action_url'        => route('subscription.suspended'),
            'target_audience'   => 'company_admin',
        ]);

        CentralNotification::createNotification([
            'company_id'        => $company->id,
            'type'              => 'SUBSCRIPTION_EXPIRED',
            'title'             => "Subscription Expired — {$company->name}",
            'message'           => "Company '{$company->name}' subscription expired on {$expiryDateFormatted} and has been suspended.",
            'severity'          => 'CRITICAL',
            'related_module'    => 'Subscriptions',
            'related_record_id' => $dedupKey,
            'action_url'        => Route::has('super-admin.subscriptions.index') ? route('super-admin.subscriptions.index') : url('/superadmin/subscriptions'),
            'target_audience'   => 'super_admin',
        ]);

        return 1;
    }

    /**
     * Process daily suspension reminder notification for a suspended company.
     */
    protected function processSuspendedReminder(Company $company, Carbon $today): int
    {
        $dedupKey = "SUSPENSION_REMINDER_{$company->id}_" . $today->toDateString();

        $exists = CentralNotification::on('central')
            ->where('company_id', $company->id)
            ->where('related_module', 'Subscriptions')
            ->where('related_record_id', $dedupKey)
            ->exists();

        if ($exists) {
            return 0;
        }

        $currentPlanName = strtoupper($company->highest_plan_slug ?: 'FREE');
        $allowedPlans = $this->eligibilityService->getAllowedPlans($company);
        $allowedNames = $allowedPlans->pluck('name')->map(fn($n) => strtoupper($n))->toArray();
        $optionsText = implode("\n", array_map(fn($n) => "• " . $n, $allowedNames));

        $message = "Your organization is currently suspended because your subscription has expired.\n\nPlease renew your eligible subscription or upgrade to a higher plan to restore access.\n\nCurrent Plan:\n{$currentPlanName}\n\nEligible Plans:\n{$optionsText}";

        CentralNotification::createNotification([
            'company_id'        => $company->id,
            'type'              => 'SUBSCRIPTION_SUSPENSION_REMINDER',
            'title'             => 'Subscription Required',
            'message'           => $message,
            'severity'          => 'CRITICAL',
            'related_module'    => 'Subscriptions',
            'related_record_id' => $dedupKey,
            'action_url'        => route('subscription.suspended'),
            'target_audience'   => 'company_admin',
        ]);

        return 1;
    }

    /**
     * Helper to format plan-specific options for notifications.
     */
    protected function buildAllowedOptionsText(Company $company, $allowedPlans, bool $isTrial): string
    {
        if ($isTrial) {
            $names = $allowedPlans->pluck('name')->map(fn($n) => strtoupper($n))->filter(fn($n) => $n !== 'FREE')->toArray();
            return implode("\n", array_map(fn($n) => "• " . $n, $names));
        }

        $highestLevel = PlanEligibilityService::getHighestLevel($company);
        $currentLevel = PlanEligibilityService::getCurrentLevel($company);
        $minLevel = max($highestLevel, $currentLevel);

        $lines = [];
        foreach ($allowedPlans as $plan) {
            $level = PlanEligibilityService::getPlanLevel($plan);
            $pName = strtoupper($plan->name);
            if ($pName === 'FREE') {
                continue;
            }

            if ($level === $minLevel) {
                $lines[] = "• Renew {$pName}";
            } elseif ($level > $minLevel) {
                $lines[] = "• Upgrade to {$pName}";
            }
        }

        return !empty($lines) ? implode("\n", $lines) : "• Contact Super Admin for eligible plans";
    }
}
