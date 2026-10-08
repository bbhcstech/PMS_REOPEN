@extends('admin.layout.app')

@section('title', 'Country Codes & Phone Prefixes')

@push('styles')
<style>
    .country-codes-page {
        --cc-card-bg: #FFFFFF;
        --cc-card-border: rgba(47, 107, 255, 0.12);
        --cc-text-title: #0F172A;
        --cc-text-muted: #64748B;
        --cc-primary: #2F6BFF;
    }

    .country-flag-icon {
        width: 24px;
        height: 16px;
        object-fit: cover;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        vertical-align: middle;
    }

    .dial-code-badge {
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        background: rgba(47, 107, 255, 0.08);
        color: #2F6BFF;
        border: 1px solid rgba(47, 107, 255, 0.2);
        display: inline-block;
    }

    .iso-code-badge {
        font-weight: 700;
        font-size: 0.78rem;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        background: #F1F5F9;
        color: #475569;
        display: inline-block;
    }

    .digits-pill {
        font-size: 0.78rem;
        font-weight: 600;
        color: #059669;
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.2);
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        display: inline-block;
    }

    .country-codes-page .stat-metric-card {
        background: var(--cc-card-bg) !important;
        border: 1px solid var(--cc-card-border) !important;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], html.dark, body.dark, body.dark-mode, body[data-pms-theme="dark"], body[data-theme="dark"], body[data-bs-theme="dark"]) .country-codes-page {
        --cc-card-bg: #141b3d;
        --cc-card-border: #334155;
        --cc-text-title: #f1f5f9;
        --cc-text-muted: #cbd5e1;
        --cc-primary: #93c5fd;
    }

    .country-codes-page .stat-metric-card h3 {
        color: var(--cc-text-title) !important;
        -webkit-text-fill-color: var(--cc-text-title) !important;
        overflow-wrap: anywhere;
    }

    .country-codes-page .stat-metric-card .text-muted {
        color: var(--cc-text-muted) !important;
        -webkit-text-fill-color: var(--cc-text-muted) !important;
    }

    .country-codes-page .stat-metric-card h3.text-primary,
    .country-codes-page .stat-metric-card i.bx,
    .country-codes-page .stat-metric-card i.bx::before {
        color: var(--cc-primary) !important;
        -webkit-text-fill-color: var(--cc-primary) !important;
    }

    .country-codes-page .stat-metric-card i.bx,
    .country-codes-page .stat-metric-card i.bx::before {
        font-family: boxicons !important;
        font-weight: normal !important;
        font-style: normal !important;
        background: transparent !important;
    }

    .country-codes-page .stat-metric-card .rounded-circle {
        flex-shrink: 0;
        margin-left: 12px;
    }

    .stat-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(47, 107, 255, 0.08);
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y country-codes-page">
    <!-- Breadcrumb -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt me-1"></i>Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Country Codes</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('admin.settings.country-codes.resync') }}" method="POST" onsubmit="return confirm('Re-sync and restore all standard country codes from master registry?');">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bx bx-sync me-1"></i> Re-Sync All Standards
                </button>
            </form>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addCountryModal">
                <i class="bx bx-plus me-1"></i> Add Country Code
            </button>
        </div>
    </div>

    <!-- Page Header Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%); border: 1px solid rgba(47, 107, 255, 0.12) !important;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background: rgba(47, 107, 255, 0.1); color: #2F6BFF;">
                    <i class="bx bx-phone-call fs-2"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1 text-dark">International Country Codes &amp; Dial Prefixes</h4>
                    <p class="text-muted mb-0 small">
                        Database-managed calling codes (e.g. +91, +1, +44), ISO identifiers, and phone number digit lengths used across all mobile inputs.
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="bx bx-arrow-back me-1"></i> Back to Settings
            </a>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 rounded-3" role="alert">
            <i class="bx bx-check-circle fs-4 me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="bx bx-error-circle me-1"></i> Please correct the following errors:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metric Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="stat-metric-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Countries</span>
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['total']) }}</h3>
                    </div>
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(47, 107, 255, 0.08); color: #2F6BFF;">
                        <i class="bx bx-globe fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="stat-metric-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Unique Dial Codes</span>
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['unique_codes']) }}</h3>
                    </div>
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.08); color: #10B981;">
                        <i class="bx bx-phone fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="stat-metric-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Default Dial Code</span>
                        <h3 class="fw-bold text-primary mb-0">{{ $stats['default_code'] }} <span class="fs-6 text-muted fw-normal">({{ $stats['default_country'] }})</span></h3>
                    </div>
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.08); color: #F59E0B;">
                        <i class="bx bx-star fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Country Codes Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="border: 1px solid rgba(47, 107, 255, 0.12) !important;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-bold text-dark">Active Country Phone Registry</h5>
                <span class="badge bg-label-primary rounded-pill">{{ $countries->total() }} Records</span>
            </div>
            <form action="{{ route('admin.settings.country-codes.index') }}" method="GET" class="d-flex align-items-center gap-2" style="max-width: 320px; width: 100%;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm border-start-0" placeholder="Search country, code, ISO...">
                    @if(request('search'))
                        <a href="{{ route('admin.settings.country-codes.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear search"><i class="bx bx-x"></i></a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">Flag</th>
                        <th>Country Name</th>
                        <th style="width: 140px;">Dial Code</th>
                        <th style="width: 110px;">ISO Code</th>
                        <th style="width: 180px;">Allowed Digits</th>
                        <th class="text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($countries as $country)
                        @php
                            $flag = $country->flag_url ?: ('https://flagcdn.com/w20/' . strtolower($country->iso_code ?? 'in') . '.png');
                        @endphp
                        <tr>
                            <td>
                                <img src="{{ $flag }}" alt="{{ $country->name }}" class="country-flag-icon" onerror="this.style.display='none'">
                            </td>
                            <td>
                                <span class="fw-bold text-dark">{{ $country->name }}</span>
                                @if($country->name === 'India')
                                    <span class="badge bg-primary-subtle text-primary border ms-2" style="font-size: 10px;">System Default</span>
                                @endif
                            </td>
                            <td>
                                <span class="dial-code-badge">{{ $country->phone_code }}</span>
                            </td>
                            <td>
                                <span class="iso-code-badge">{{ $country->iso_code ?: 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="digits-pill">
                                    <i class="bx bx-check-circle me-1"></i>
                                    {{ $country->min_digits === $country->max_digits ? $country->min_digits . ' digits' : $country->min_digits . ' - ' . $country->max_digits . ' digits' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-circle me-1 edit-country-btn"
                                        data-id="{{ $country->id }}"
                                        data-name="{{ $country->name }}"
                                        data-phone_code="{{ $country->phone_code }}"
                                        data-iso_code="{{ $country->iso_code }}"
                                        data-min_digits="{{ $country->min_digits }}"
                                        data-max_digits="{{ $country->max_digits }}"
                                        title="Edit Country Code">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <form action="{{ route('admin.settings.country-codes.destroy', $country->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this country code?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger rounded-circle" title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bx bx-search-alt fs-1 d-block mb-2 text-primary opacity-50"></i>
                                <div class="fw-bold">No country codes found</div>
                                <p class="small text-muted mb-3">Try adjusting your search criteria or click Re-Sync to reload.</p>
                                <form action="{{ route('admin.settings.country-codes.resync') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill">
                                        <i class="bx bx-sync me-1"></i> Re-Sync All Standards
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($countries->hasPages())
            <div class="card-footer bg-white py-3 px-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="small text-muted">
                    Showing {{ $countries->firstItem() }} to {{ $countries->lastItem() }} of {{ $countries->total() }} countries
                </div>
                <div>
                    {{ $countries->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal: Add Country Code -->
<div class="modal fade" id="addCountryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bx bx-plus-circle text-primary me-2"></i>Add New Country Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.settings.country-codes.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Country Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Switzerland" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Dial Code <span class="text-danger">*</span></label>
                            <input type="text" name="phone_code" class="form-control rounded-3" placeholder="e.g. +41" required>
                            <small class="text-muted" style="font-size: 11px;">Must start with +</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">ISO Code <span class="text-danger">*</span></label>
                            <input type="text" name="iso_code" class="form-control rounded-3 text-uppercase" placeholder="e.g. CH" maxlength="4" required>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Min Digits <span class="text-danger">*</span></label>
                            <input type="number" name="min_digits" class="form-control rounded-3" value="10" min="4" max="18" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Max Digits <span class="text-danger">*</span></label>
                            <input type="number" name="max_digits" class="form-control rounded-3" value="10" min="4" max="18" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Country Code</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Country Code -->
<div class="modal fade" id="editCountryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bx bx-edit text-primary me-2"></i>Edit Country Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCountryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Country Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Dial Code <span class="text-danger">*</span></label>
                            <input type="text" name="phone_code" id="edit_phone_code" class="form-control rounded-3" required>
                            <small class="text-muted" style="font-size: 11px;">Must start with +</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">ISO Code <span class="text-danger">*</span></label>
                            <input type="text" name="iso_code" id="edit_iso_code" class="form-control rounded-3 text-uppercase" maxlength="4" required>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Min Digits <span class="text-danger">*</span></label>
                            <input type="number" name="min_digits" id="edit_min_digits" class="form-control rounded-3" min="4" max="18" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Max Digits <span class="text-danger">*</span></label>
                            <input type="number" name="max_digits" id="edit_max_digits" class="form-control rounded-3" min="4" max="18" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Country Code</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.edit-country-btn');
        const editModal = new bootstrap.Modal(document.getElementById('editCountryModal'));
        const form = document.getElementById('editCountryForm');

        editButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                form.action = "{{ url('admin/settings/country-codes') }}/" + id;
                document.getElementById('edit_name').value = this.dataset.name || '';
                document.getElementById('edit_phone_code').value = this.dataset.phone_code || '';
                document.getElementById('edit_iso_code').value = this.dataset.iso_code || '';
                document.getElementById('edit_min_digits').value = this.dataset.min_digits || '10';
                document.getElementById('edit_max_digits').value = this.dataset.max_digits || '10';
                editModal.show();
            });
        });
    });
</script>
@endpush
@endsection
