@extends('admin.layout.app')
@section('title', 'Add Upper Level Employee')
@push('styles')
<style>
    /* The employee-page theme boxes every <form>; these forms already sit inside cards, so render them flat. */
    html body:has(form[action*="employees"]) .upper-level-page .card-body > form {
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
        overflow: visible !important;
    }
</style>
@endpush
@section('content')
<div class="container-fluid py-4 upper-level-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h3 class="mb-1">Add Upper Level Employee</h3><p class="text-muted mb-0">Manage company roles and employee accounts for {{ $company->name }}.</p></div>
        <a class="btn btn-outline-primary" href="{{ route('employees.index') }}">View employees</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="row g-4 mb-4">
        <div class="col-lg-4"><div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Add company role</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.upper-level-employees.roles.store') }}">@csrf
                    <input type="hidden" name="_form" value="role">
                    <label class="form-label" for="staffRoleName">Role name</label>
                    <input id="staffRoleName" class="form-control mb-3" name="name" value="{{ old('_form') === 'role' ? old('name') : '' }}" maxlength="100" required placeholder="e.g. Project Manager">
                    <label class="form-label" for="staffAccessRole">Permission profile</label>
                    <select id="staffAccessRole" name="access_role" class="form-select mb-2" required>
                        <option value="">Select permission profile</option>@foreach(['hr' => 'HR', 'manager' => 'Manager', 'employee' => 'Employee'] as $key => $label)<option value="{{ $key }}" @selected(old('_form') === 'role' && old('access_role') === $key)>{{ $label }}</option>@endforeach
                    </select>
                    <p class="text-muted small">Accounts in this role use the selected profile’s existing permissions.</p>
                    <button class="btn btn-primary" type="submit">Add role</button>
                </form>
                @if($roles->isNotEmpty())
                    <hr><h6>Company roles</h6>
                    <div class="d-flex flex-wrap gap-2">@foreach($roles as $role)<span class="badge bg-label-primary">{{ $role->name }} · {{ ucfirst($role->access_role) }}</span>@endforeach</div>
                @endif
            </div>
        </div></div>
        <div class="col-lg-8"><div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Add employee account</h5></div>
            <div class="card-body">
                @if($roles->isEmpty())<div class="alert alert-info">Add a company role first, then create an account.</div>@endif
                @if($designations->isEmpty())<div class="alert alert-info">Add a designation in Designation Settings before creating an account.</div>@endif
                <form method="POST" action="{{ route('admin.upper-level-employees.store') }}">@csrf
                    @include('admin.employees.partials.upper-level-fields', ['account' => null, 'fieldPrefix' => 'new'])
                    <button class="btn btn-primary mt-3" type="submit" @disabled($roles->isEmpty() || $designations->isEmpty())>Add Upper Level Employee</button>
                </form>
            </div>
        </div></div>
    </div>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Upper level employees</h5></div>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Employee</th><th>Role</th><th>Designation / Level</th><th>Login</th><th>Action</th></tr></thead>
            <tbody>@forelse($accounts as $account)
                <tr>
                    <td><strong>{{ $account->name }}</strong><div class="text-muted small">{{ $account->email }}</div><div class="text-muted small">{{ $account->employeeDetail?->employee_id }}</div></td>
                    <td>{{ $account->companyStaffRole?->name ?? ucfirst($account->role) }}<div class="text-muted small">{{ ucfirst($account->role) }} permissions</div></td>
                    <td>{{ $account->employeeDetail?->designation?->name ?? 'Not assigned' }}@if($account->employeeDetail?->designation)<div class="text-muted small">Level {{ $account->employeeDetail->designation->level }}</div>@endif</td>
                    <td><span class="badge {{ $account->login_allowed ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $account->login_allowed ? 'Enabled' : 'Disabled' }}</span></td>
                    <td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editStaff{{ $account->id }}" aria-expanded="false" aria-controls="editStaff{{ $account->id }}" type="button">Edit</button></td>
                </tr>
                <tr class="collapse {{ request()->integer('edit') === $account->id || (int) old('_editing_account_id') === $account->id ? 'show' : '' }}" id="editStaff{{ $account->id }}"><td colspan="5"><form method="POST" action="{{ route('admin.upper-level-employees.update', $account->id) }}" class="p-3">@csrf @method('PUT')
                    @include('admin.employees.partials.upper-level-fields', ['fieldPrefix' => 'edit' . $account->id])
                    <button class="btn btn-primary mt-3" type="submit">Save changes</button>
                </form></td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted py-4">No upper level employee accounts yet.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="card-footer">{{ $accounts->links() }}</div>
    </div>
</div>
@endsection
