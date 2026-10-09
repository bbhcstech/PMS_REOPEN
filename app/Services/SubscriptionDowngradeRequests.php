<?php
namespace App\Services;

use App\Models\Central\{Company, CompanyComplaint, Plan};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionDowngradeRequests
{
    public function create(array $data, Company $company, $user): CompanyComplaint
    {
        abort_unless($user && in_array(strtolower($user->role), ['admin', 'administrator'], true) && (int) $user->company_id === (int) $company->id, 403);
        return DB::connection('central')->transaction(function () use ($data, $company, $user) {
            $company = Company::whereKey($company->id)->lockForUpdate()->firstOrFail();
            $plan = Plan::standard()->where('is_active', true)->findOrFail($data['requested_plan_id']);
            $subscription = $company->activeSubscription;
            if (!$subscription || PlanEligibilityService::getPlanLevel($plan) >= PlanEligibilityService::getPlanLevel($subscription->plan)) {
                throw ValidationException::withMessages(['requested_plan_id' => 'Choose a plan below your current subscription.']);
            }
            if (CompanyComplaint::where('company_id', $company->id)->where('plan_request_status', 'pending')->exists()) {
                throw ValidationException::withMessages(['requested_plan_id' => 'A subscription change request is already awaiting Super Admin review.']);
            }
            $data['category'] = 'Subscription';
            $data['subject'] = 'Subscription reduction to ' . $plan->name . ': ' . \Illuminate\Support\Str::limit($data['subject'], 180, '');
            $ticket = app(ComplaintService::class)->createComplaint($data, $company, $user);
            $ticket->update(['requested_plan_id' => $plan->id, 'requested_from_plan_id' => $subscription->plan_id, 'plan_request_status' => 'pending']);
            return $ticket;
        });
    }

    public function decide(int $ticketId, string $decision): CompanyComplaint
    {
        abort_unless(TenantScope::isPlatformAdmin(), 403);
        abort_unless(in_array($decision, ['approved', 'rejected'], true), 422);
        SubscriptionChangeSchema::ensure();
        return DB::connection('central')->transaction(function () use ($ticketId, $decision) {
            $ticket = CompanyComplaint::findOrFail($ticketId);
            $company = Company::whereKey($ticket->company_id)->lockForUpdate()->firstOrFail();
            $ticket = CompanyComplaint::whereKey($ticketId)->lockForUpdate()->firstOrFail();
            if ($ticket->plan_request_status !== 'pending') throw new \InvalidArgumentException('This request has already been reviewed or is not a subscription request.');
            $actor = auth('super_admin')->user() ?? auth()->user();
            if ($decision === 'approved' && (int) $company->activeSubscription?->plan_id !== (int) $ticket->requested_from_plan_id) {
                $ticket->update(['plan_request_status' => 'stale', 'plan_reviewed_by' => $actor?->id, 'plan_reviewed_at' => now()]);
                $message = 'Your subscription changed after this request was submitted. This request is now closed; please submit a new request for the current plan.';
            } elseif ($decision === 'approved') {
                $plan = Plan::standard()->where('is_active', true)->findOrFail($ticket->requested_plan_id);
                $subscription = app(SubscriptionService::class)->activateOrUpgradePlan($company, $plan,
                    $company->activeSubscription?->billing_cycle ?? 'monthly', $actor?->name,
                    'Approved Support Desk request ' . $ticket->ticket_id, $ticket->id);
                $company->update(['max_users' => $plan->max_users > 0 ? $plan->max_users : 999999,
                    'max_projects' => $plan->max_projects > 0 ? $plan->max_projects : 999999,
                    'max_clients' => $plan->max_clients > 0 ? $plan->max_clients : 999999,
                    'max_storage_mb' => $plan->max_storage_mb > 0 ? $plan->max_storage_mb : 512000,
                    'trial_ends_at' => $subscription->ends_at]);
                $message = 'Your subscription reduction request was approved. Your company is now on the ' . $plan->name . ' plan.';
            } else {
                $ticket->update(['plan_request_status' => 'rejected', 'plan_reviewed_by' => $actor?->id, 'plan_reviewed_at' => now()]);
                $message = 'Your subscription reduction request was declined. Your existing subscription remains unchanged.';
            }
            app(ComplaintService::class)->addResponse($ticket, $message, $actor, 'super_admin');
            return $ticket->fresh();
        });
    }
}
