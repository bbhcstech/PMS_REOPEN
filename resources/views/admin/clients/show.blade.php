@extends('admin.layout.app')

@section('title', 'Client Profile - ' . ($client->name ?? 'Details'))

@section('content')
<style>
    .client-detail-box {
        background: #fbfdfc;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 12px;
        padding: 0.85rem 1.1rem;
        margin-bottom: 0.85rem;
    }
    .client-detail-label {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 0.25rem;
    }
    .client-detail-val {
        font-size: 0.93rem;
        font-weight: 700;
        color: #0f172a;
    }
    .segmented-tabs-wrapper {
        background: #f1f5f9;
        padding: 5px;
        border-radius: 14px;
        display: inline-flex;
        border: 1px solid #e2e8f0;
    }
    .custom-segmented-pills {
        display: inline-flex;
        gap: 5px;
        margin: 0;
        padding: 0;
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    .custom-segmented-pills .nav-item {
        margin: 0;
    }
    .custom-segmented-pills .nav-link {
        border: none !important;
        background: transparent !important;
        color: #475569 !important;
        font-weight: 700 !important;
        font-size: 0.9rem !important;
        padding: 0.6rem 1.25rem !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.45rem !important;
        transition: all 0.2s ease-in-out !important;
        box-shadow: none !important;
        cursor: pointer !important;
    }
    .custom-segmented-pills .nav-link i,
    .custom-segmented-pills .nav-link span {
        color: inherit !important;
    }
    .custom-segmented-pills .nav-link:hover:not(.active) {
        color: #0f172a !important;
        background: rgba(255, 255, 255, 0.75) !important;
    }
    .custom-segmented-pills .nav-link.active {
        background: #0f744c !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 12px rgba(15, 116, 76, 0.25) !important;
    }
    .custom-segmented-pills .nav-link.active,
    .custom-segmented-pills .nav-link.active *,
    .custom-segmented-pills .nav-link.active i,
    .custom-segmented-pills .nav-link.active span {
        color: #ffffff !important;
    }
    .custom-segmented-pills .nav-link .tab-count-badge {
        background: #cbd5e1;
        color: #1e293b;
        font-size: 0.75rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-weight: 800;
        transition: all 0.2s ease;
    }
    .custom-segmented-pills .nav-link.active .tab-count-badge {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
    }
    .status-badge-custom {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 700;
        border-radius: 9999px;
        color: #000000 !important;
        text-transform: capitalize;
        letter-spacing: 0.02em;
    }
    .status-in-progress { background-color: #fef3c7 !important; border: 1px solid #fde68a; }
    .status-completed   { background-color: #d1fae5 !important; border: 1px solid #a7f3d0; }
    .status-on-hold     { background-color: #fee2e2 !important; border: 1px solid #fecaca; }
    .status-not-started { background-color: #e2e8f0 !important; border: 1px solid #cbd5e1; }
    .status-pending     { background-color: #e0f2fe !important; border: 1px solid #bae6fd; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Header Banner --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8fbf9 100%); border: 1px solid rgba(226, 232, 240, 0.85);">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if($client->profile_picture)
                        <img src="{{ asset($client->profile_picture) }}" alt="{{ $client->name }}" class="rounded-circle border shadow-sm" style="width: 64px; height: 64px; object-fit: cover;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 64px; height: 64px; background: linear-gradient(135deg, #0f744c, #10b981); font-size: 1.5rem;">
                            {{ strtoupper(substr($client->name ?? 'C', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <span class="badge bg-light text-dark border fw-bold">{{ $client->client_uid ?? 'XINK-CL-' . str_pad($client->id, 4, '0', STR_PAD_LEFT) }}</span>
                            <span class="badge {{ strtolower($client->status) === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-dark border border-warning-subtle' }} rounded-pill px-2 py-1 text-capitalize">
                                {{ $client->status ?? 'Active' }}
                            </span>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">{{ $client->salutation ? $client->salutation . ' ' : '' }}{{ $client->name }}</h4>
                        <div class="text-muted small">
                            <i class="bx bx-envelope me-1"></i>{{ $client->email }} &bull; <i class="bx bx-phone me-1"></i>{{ $client->mobile ?? '--' }}
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-warning rounded-pill px-3 shadow-sm">
                        <i class="bx bx-edit me-1"></i> Edit Client
                    </a>
                    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="bx bx-arrow-back me-1"></i> Back to Clients
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Control Bar --}}
    <div class="mb-4">
        <div class="segmented-tabs-wrapper shadow-sm">
            <ul class="nav custom-segmented-pills" id="clientTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview-pane" type="button" role="tab" aria-controls="overview-pane" aria-selected="true">
                        <i class="bx bx-user"></i> <span>Profile &amp; Company Overview</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="projects-tab" data-bs-toggle="pill" data-bs-target="#projects-pane" type="button" role="tab" aria-controls="projects-pane" aria-selected="false">
                        <i class="bx bx-folder"></i> <span>Projects</span> <span class="tab-count-badge">{{ $client->projects->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    {{-- Tab Content --}}
    <div class="tab-content" id="clientTabsContent">
        {{-- Overview Pane --}}
        <div class="tab-pane fade show active" id="overview-pane" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row g-4">
                {{-- Profile Info --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-light border-bottom px-4 py-3">
                            <h5 class="fw-bold text-dark mb-0"><i class="bx bx-user me-2 text-primary"></i> Personal & Account Info</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Full Name</div>
                                        <div class="client-detail-val">{{ $client->salutation ? $client->salutation . ' ' : '' }}{{ $client->name }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Email Address</div>
                                        <div class="client-detail-val text-truncate">{{ $client->email }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Mobile Phone</div>
                                        <div class="client-detail-val">{{ $client->mobile ?? '--' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Gender</div>
                                        <div class="client-detail-val text-capitalize">{{ $client->gender ?: '--' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Language</div>
                                        <div class="client-detail-val">{{ $client->language ? ucfirst($client->language) : '--' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Category</div>
                                        <div class="client-detail-val">{{ $client->category->name ?? '--' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Subcategory</div>
                                        <div class="client-detail-val">{{ $client->subcategory->name ?? '--' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Login Allowed</div>
                                        <div class="client-detail-val">
                                            <span class="badge {{ $client->login_allowed ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill">
                                                {{ $client->login_allowed ? 'Yes' : 'No' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Company Info --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-light border-bottom px-4 py-3">
                            <h5 class="fw-bold text-dark mb-0"><i class="bx bx-buildings me-2 text-success"></i> Company & Billing Info</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Company Name</div>
                                        <div class="client-detail-val">{{ $client->company_name ?? '--' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Official Website</div>
                                        <div class="client-detail-val text-truncate">
                                            @if($client->website)
                                                <a href="{{ $client->website }}" target="_blank" class="text-primary text-decoration-none"><i class="bx bx-link-external me-1"></i>{{ $client->website }}</a>
                                            @else
                                                --
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Tax Name / Type</div>
                                        <div class="client-detail-val">{{ $client->tax_name ?? '--' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">GST / VAT Number</div>
                                        <div class="client-detail-val">{{ $client->tax_number ?? '--' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Office Phone</div>
                                        <div class="client-detail-val">{{ $client->office_phone ?? '--' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">City / State / Postal</div>
                                        <div class="client-detail-val">{{ implode(', ', array_filter([$client->city, $client->state, $client->postal_code])) ?: '--' }}</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="client-detail-box">
                                        <div class="client-detail-label">Company Address</div>
                                        <div class="client-detail-val text-muted" style="font-weight: 500;">{{ $client->company_address ?? '--' }}</div>
                                    </div>
                                </div>
                                @if($client->shipping_address)
                                    <div class="col-12">
                                        <div class="client-detail-box">
                                            <div class="client-detail-label">Shipping Address</div>
                                            <div class="client-detail-val text-muted" style="font-weight: 500;">{{ $client->shipping_address }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if($client->note)
                                    <div class="col-12">
                                        <div class="client-detail-box">
                                            <div class="client-detail-label">Notes</div>
                                            <div class="client-detail-val text-muted" style="font-weight: 500;">{{ $client->note }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Projects Pane --}}
        <div class="tab-pane fade" id="projects-pane" role="tabpanel" aria-labelledby="projects-tab">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light border-bottom px-4 py-3 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h5 class="fw-bold text-dark mb-0"><i class="bx bx-folder-open me-2 text-primary"></i> Projects for {{ $client->name }}</h5>
                        <small class="text-muted">Total {{ $client->projects->count() }} project{{ $client->projects->count() === 1 ? '' : 's' }} associated with this client</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('projects.create', ['client_id' => $client->id]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="bx bx-plus me-1"></i> Add Project
                        </a>
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="bx bx-grid-alt me-1"></i> All Projects
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($client->projects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width: 50px;">#</th>
                                        <th>CODE</th>
                                        <th>PROJECT NAME</th>
                                        <th>MEMBERS</th>
                                        <th>TASKS</th>
                                        <th>PROGRESS</th>
                                        <th>TIMELINE</th>
                                        <th>STATUS</th>
                                        <th class="text-end pe-4">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($client->projects as $index => $project)
                                        @php
                                            $rawStatus = strtolower(trim((string) $project->status));
                                            $badgeClass = match ($rawStatus) {
                                                'completed' => 'status-completed',
                                                'in progress', 'doing' => 'status-in-progress',
                                                'on hold', 'on-hold' => 'status-on-hold',
                                                'pending', 'waiting for approval' => 'status-pending',
                                                default => 'status-not-started',
                                            };
                                            $progress = (int) ($project->completion_percent ?? 0);
                                        @endphp
                                        <tr>
                                            <td class="ps-4 text-muted small fw-bold">{{ $index + 1 }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark border fw-bold">{{ $project->project_code ?? ('PRJ-' . $project->id) }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('projects.show', $project->id) }}" class="fw-bold text-dark text-decoration-none">
                                                    {{ $project->name }}
                                                </a>
                                                @if($project->description)
                                                    <div class="text-muted small text-truncate" style="max-width: 250px;">
                                                        {{ Str::limit(strip_tags($project->description), 50) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @forelse($project->users->take(4) as $member)
                                                        <span class="avatar avatar-xs rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center border border-white me-n2" 
                                                              style="width: 28px; height: 28px; font-size: 0.75rem;" 
                                                              title="{{ $member->name }}">
                                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                                        </span>
                                                    @empty
                                                        <span class="text-muted small">No members</span>
                                                    @endforelse
                                                    @if($project->users->count() > 4)
                                                        <span class="badge rounded-circle bg-light text-muted border ms-2" style="font-size: 0.7rem;">+{{ $project->users->count() - 4 }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $project->tasks->count() }} Tasks</span>
                                            </td>
                                            <td style="min-width: 140px;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 6px; border-radius: 9999px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="small fw-bold text-muted">{{ $progress }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small text-dark fw-semibold">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '--' }}</div>
                                                <div class="small text-muted">Due: {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d M Y') : 'No deadline' }}</div>
                                            </td>
                                            <td>
                                                <span class="status-badge-custom {{ $badgeClass }}">
                                                    {{ $project->status ?: 'Not Started' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex align-items-center justify-content-end gap-1">
                                                    <a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-outline-primary" title="View Project">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                    <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit Project">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="p-3 rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="bx bx-folder text-muted" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="fw-bold text-dark">No Projects Found for {{ $client->name }}</h5>
                            <p class="text-muted small mb-3">This client doesn't have any projects assigned yet.</p>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="{{ route('projects.create', ['client_id' => $client->id]) }}" class="btn btn-primary rounded-pill px-4">
                                    <i class="bx bx-plus me-1"></i> Create First Project
                                </a>
                                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="bx bx-grid-alt me-1"></i> View All Projects
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function activateProjectsTab() {
        const projectsTabBtn = document.getElementById('projects-tab');
        if (projectsTabBtn) {
            const tab = new bootstrap.Tab(projectsTabBtn);
            tab.show();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (window.location.hash === '#projects') {
            activateProjectsTab();
        }
    });
</script>
@endsection

