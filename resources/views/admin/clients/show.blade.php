@extends('admin.layout.app')

@section('title', 'Client Details - ' . $client->name)

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Top Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-light text-dark border font-monospace fs-6">{{ $client->client_uid ?? ('CL-' . $client->id) }}</span>
                <h3 class="fw-bold mb-0 text-dark">{{ $client->salutation }} {{ $client->name }}</h3>
                @if(strtolower((string)($client->status ?? 'active')) == 'active')
                    <span class="badge bg-success px-3 py-2 fs-6">Active Client</span>
                @else
                    <span class="badge bg-secondary px-3 py-2 fs-6">{{ ucfirst($client->status ?? 'Inactive') }}</span>
                @endif
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}" class="text-decoration-none">Clients</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $client->name }}</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                <i class="bx bx-edit me-1"></i> Edit Profile
            </a>
            <a href="{{ route('tickets.create') }}" class="btn btn-primary rounded-pill px-3 shadow-sm">
                <i class="bx bx-plus-circle me-1"></i> Raise Ticket
            </a>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Stat Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100 border-start border-primary border-4">
                <small class="text-muted text-uppercase fw-bold mb-1">Delivered / Active Projects</small>
                <h3 class="fw-extrabold text-primary mb-0">{{ $client->projects->count() }}</h3>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100 border-start border-warning border-4">
                <small class="text-muted text-uppercase fw-bold mb-1">Support Tickets Raised</small>
                <h3 class="fw-extrabold text-warning-emphasis mb-0">{{ $client->tickets->count() }}</h3>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100 border-start border-info border-4">
                <small class="text-muted text-uppercase fw-bold mb-1">Category & Type</small>
                <h5 class="fw-bold text-info-emphasis mb-0 text-truncate">{{ $client->category->name ?? 'Corporate Client' }}</h5>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100 border-start border-success border-4">
                <small class="text-muted text-uppercase fw-bold mb-1">Portal Login Access</small>
                <h5 class="fw-bold text-success mb-0">{{ $client->login_allowed ? 'Enabled' : 'Disabled' }}</h5>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Panel: Profile & Company Info -->
        <div class="col-lg-5">
            <!-- Profile Info Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-user text-primary me-2"></i> Client Contact Profile</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded-3 border">
                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">
                            {{ strtoupper(substr($client->name ?? 'C', 0, 2)) }}
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">{{ $client->salutation }} {{ $client->name }}</h5>
                            <span class="text-muted small"><i class="bx bx-envelope me-1"></i> {{ $client->email }}</span>
                        </div>
                    </div>

                    <div class="mb-3 d-flex align-items-center justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-semibold">Mobile Phone</span>
                        <span class="fw-bold text-dark">{{ $client->mobile ?? '—' }}</span>
                    </div>

                    <div class="mb-3 d-flex align-items-center justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-semibold">Office Phone</span>
                        <span class="fw-bold text-dark">{{ $client->office_phone ?? '—' }}</span>
                    </div>

                    <div class="mb-3 d-flex align-items-center justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-semibold">Gender</span>
                        <span class="fw-bold text-dark">{{ ucfirst($client->gender ?? 'Unspecified') }}</span>
                    </div>

                    <div class="mb-3 d-flex align-items-center justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-semibold">Language</span>
                        <span class="fw-bold text-dark">{{ ucfirst($client->language ?? 'English') }}</span>
                    </div>

                    <div class="mb-0 d-flex align-items-center justify-content-between">
                        <span class="text-muted fw-semibold">Email Notifications</span>
                        <span class="badge {{ $client->email_notifications ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} px-3 py-1">
                            {{ $client->email_notifications ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Company Info Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-building-house text-success me-2"></i> Company & Tax Info</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3 d-flex align-items-center justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-semibold">Company Name</span>
                        <span class="fw-bold text-dark">{{ $client->company_name ?? 'Individual' }}</span>
                    </div>

                    <div class="mb-3 d-flex align-items-center justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-semibold">Official Website</span>
                        @if($client->website)
                            <a href="{{ $client->website }}" target="_blank" class="fw-bold text-primary text-decoration-none">
                                <i class="bx bx-globe me-1"></i> {{ $client->website }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>

                    <div class="mb-3 d-flex align-items-center justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-semibold">Tax / GST Name</span>
                        <span class="fw-bold text-dark">{{ $client->tax_name ?? '—' }}</span>
                    </div>

                    <div class="mb-3 d-flex align-items-center justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-semibold">Tax / GST Number</span>
                        <span class="fw-bold text-dark">{{ $client->tax_number ?? '—' }}</span>
                    </div>

                    <div class="mb-3 border-bottom pb-2">
                        <span class="text-muted fw-semibold d-block mb-1">Company Address</span>
                        <div class="text-dark small">{{ $client->company_address ?? '—' }}</div>
                        <small class="text-muted">{{ $client->city }} {{ $client->state }} {{ $client->postal_code }}</small>
                    </div>

                    @if($client->note)
                        <div>
                            <span class="text-muted fw-semibold d-block mb-1">Requirements / Client Notes</span>
                            <div class="p-3 bg-light rounded-3 border text-secondary small" style="white-space: pre-line;">{{ $client->note }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Panel: Linked Projects & Support Tickets -->
        <div class="col-lg-7">
            <!-- Linked Projects Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-briefcase text-primary me-2"></i> Client Projects & Contracts</h5>
                    <a href="{{ route('projects.create') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">+ New Project</a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Project Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($client->projects as $proj)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $proj->name }}</div>
                                            <small class="text-muted">{{ $proj->project_code }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $proj->category?->name ?? 'Product' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                                {{ ucfirst($proj->status ?? 'Active') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('projects.show', $proj->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bx bx-show me-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No active or historical projects linked to this client yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Support Tickets Raised Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-support text-warning me-2"></i> Client Support Tickets</h5>
                    <a href="{{ route('tickets.create') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">+ Raise Ticket</a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket#</th>
                                    <th>Subject & Project</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($client->tickets as $t)
                                    <tr>
                                        <td class="fw-bold">#{{ $t->id }}</td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ Str::limit($t->subject, 36) }}</div>
                                            <small class="text-muted"><i class="bx bx-folder me-1"></i> {{ $t->project?->name ?? 'General' }}</small>
                                        </td>
                                        <td>
                                            @if(strtolower((string)$t->status) == 'open')
                                                <span class="badge bg-warning text-dark">Open</span>
                                            @elseif(strtolower((string)$t->status) == 'reopened')
                                                <span class="badge bg-danger">REOPENED</span>
                                            @elseif(strtolower((string)$t->status) == 'resolved')
                                                <span class="badge bg-success">Resolved</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($t->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('tickets.show', $t->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bx bx-show me-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No support tickets recorded for this client</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
