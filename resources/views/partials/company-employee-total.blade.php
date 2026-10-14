@if(auth()->user()?->company_id)
<div class="card mb-3 p-3" data-live-key="company-employee-total" role="status">
    <span class="text-muted">Total Employees</span>
    <strong class="fs-4">{{ app(\App\Services\CompanyEmployeeDirectory::class)->count() }}</strong>
</div>
@endif
