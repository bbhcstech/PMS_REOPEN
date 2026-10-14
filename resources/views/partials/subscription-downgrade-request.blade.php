@if($ticket->requested_plan_id)
@if(($platformReview ?? false) && session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
@if(($platformReview ?? false) && session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
<div class="alert alert-info" role="status">
    <strong>Subscription reduction request</strong>
    <div>Requested plan: {{ $ticket->requestedPlan?->name ?? 'Unavailable plan' }} &middot; <span data-live-plan-request-status>{{ ucfirst($ticket->plan_request_status) }}</span></div>
    @if($ticket->plan_request_status === 'pending')
    <div data-live-plan-request-pending>Your current subscription stays active until Super Admin approves this request.</div>
    @if($platformReview ?? false)
    <form method="POST" action="{{ route('superadmin.complaints.subscription-decision', $ticket->id) }}" class="d-flex gap-2 mt-2" data-live-plan-request-pending>
        @csrf
        <button class="btn btn-primary" type="submit" name="decision" value="approved">Approve and change plan</button>
        <button class="btn btn-outline-danger" type="submit" name="decision" value="rejected">Decline request</button>
    </form>
    @endif
    @endif
</div>
@endif
