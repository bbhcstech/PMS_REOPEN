@extends('admin.layout.app')

@section('title', 'Company Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Company Management</h4>
            <p class="text-muted mb-0">Manage company identity, prefixes, status, and branding.</p>
        </div>
        <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Company
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Code</th>
                        <th>Email</th>
                        <th>Employee Prefix</th>
                        <th>Leave</th>
                        <th>Payroll</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($company->logo)
                                        <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" width="32" height="32" class="rounded" style="object-fit:cover;">
                                    @endif
                                    <span class="fw-semibold">{{ $company->name }}</span>
                                </div>
                            </td>
                            <td>{{ $company->company_code }}</td>
                            <td>{{ $company->email }}</td>
                            <td>{{ $company->employee_id_prefix }}</td>
                            <td>{{ $company->leave_prefix }}</td>
                            <td>{{ $company->payroll_prefix }}</td>
                            <td>
                                <span class="badge bg-label-{{ $company->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($company->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-edit"></i>
                                </a>
                                @if($company->status === 'active')
                                    <form action="{{ route('admin.companies.deactivate', $company) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-secondary" type="submit">Deactivate</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.companies.activate', $company) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-success" type="submit">Activate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No companies found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $companies->links() }}
    </div>
</div>
@endsection
