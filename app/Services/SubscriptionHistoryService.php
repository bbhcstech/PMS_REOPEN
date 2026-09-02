<?php

namespace App\Services;

use App\Models\Central\Company;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\SubscriptionHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionHistoryService
{
    /**
     * Record a subscription lifecycle transition in central database and audit log.
     */
    public function log(
        Company|\App\Models\Company $company,
        string $action,
        Plan|\App\Models\SubscriptionPlan|null $newPlan = null,
        Plan|\App\Models\SubscriptionPlan|null $previousPlan = null,
        ?Subscription $subscription = null,
        ?string $performedBy = null,
        ?string $reason = null,
        ?string $notes = null
    ): SubscriptionHistory {
        $performedBy = $performedBy ?: (auth('super_admin')->user()?->name ?? auth()->user()?->name ?? 'System Automated Process');

        $history = SubscriptionHistory::on('central')->create([
            'company_id'         => $company->id,
            'subscription_id'    => $subscription?->id,
            'previous_plan_id'   => $previousPlan?->id,
            'new_plan_id'        => $newPlan?->id,
            'previous_plan_name' => $previousPlan?->name ?? ($company->highest_plan_slug ? strtoupper($company->highest_plan_slug) : 'FREE'),
            'new_plan_name'      => $newPlan?->name ?? 'FREE',
            'action'             => $action,
            'performed_by'       => $performedBy,
            'reason'             => $reason ?: $this->defaultReasonForAction($action),
            'start_date'         => $subscription?->starts_at ?? now()->toDateString(),
            'end_date'           => $subscription?->ends_at ?? $company->trial_ends_at?->toDateString(),
            'notes'              => $notes,
        ]);

        // Mirror history into tenant DB if configured
        try {
            if (Schema::hasTable('subscription_histories')) {
                DB::table('subscription_histories')->insert([
                    'company_id'         => $company->id,
                    'subscription_id'    => $subscription?->id,
                    'previous_plan_id'   => $previousPlan?->id,
                    'new_plan_id'        => $newPlan?->id,
                    'previous_plan_name' => $history->previous_plan_name,
                    'new_plan_name'      => $history->new_plan_name,
                    'action'             => $action,
                    'performed_by'       => $performedBy,
                    'reason'             => $history->reason,
                    'start_date'         => $history->start_date,
                    'end_date'           => $history->end_date,
                    'notes'              => $notes,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::info("Tenant history log notice: " . $e->getMessage());
        }

        // Log to Audit Log
        try {
            if (class_exists(\App\Models\AuditLog::class)) {
                \App\Models\AuditLog::create([
                    'company_id'  => $company->id,
                    'user_id'     => auth()->id(),
                    'event'       => 'subscription.' . strtolower($action),
                    'description' => "Subscription event '{$action}' recorded for company {$company->name}.",
                    'properties'  => [
                        'action' => $action,
                        'previous_plan' => $history->previous_plan_name,
                        'new_plan' => $history->new_plan_name,
                        'performed_by' => $performedBy,
                    ],
                ]);
            }
        } catch (\Throwable $e) {}

        return $history;
    }

    private function defaultReasonForAction(string $action): string
    {
        return match ($action) {
            'TRIAL_STARTED'        => 'Company registered on 30-day Free Trial.',
            'PLAN_PURCHASED'       => 'Initial paid subscription plan activated.',
            'PLAN_RENEWED'         => 'Subscription plan renewed for another billing cycle.',
            'PLAN_UPGRADED'        => 'Subscription plan upgraded to higher tier.',
            'SUBSCRIPTION_EXPIRED' => 'Subscription or trial period reached expiration date.',
            'COMPANY_SUSPENDED'    => 'Account automatically suspended due to subscription expiration.',
            'COMPANY_REACTIVATED'   => 'Account reactivated following valid subscription purchase.',
            default                => 'Subscription lifecycle state updated.',
        };
    }
}
