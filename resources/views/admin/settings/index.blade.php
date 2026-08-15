@extends('admin.layout.app')

@section('title', 'Admin Settings Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold py-1 mb-1">
                        <span class="text-muted fw-light">Admin /</span> Settings Dashboard
                    </h4>
                    <p class="text-muted mb-0 small">Manage and configure all 20 admin settings modules from a single workspace.</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                        <i class="bx bx-refresh me-1"></i> Refresh
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-home me-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Category Filter Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="btn-group flex-wrap" role="group" id="settingsCategoryFilter">
                        <button type="button" class="btn btn-sm btn-primary active" data-filter="all">All Settings ({{ count($settingsGroups) }})</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="Business">Business</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="Organization">Organization</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="HR">HR & Employees</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="Finance">Finance</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="System">System</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="Security">Security</button>
                    </div>
                    <div class="input-group input-group-merge style-search" style="max-width: 260px;">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" id="settingsSearchInput" class="form-control form-control-sm" placeholder="Search settings...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Cards Grid -->
    <div class="row g-4" id="settingsGrid">
        @foreach($settingsGroups as $slug => $group)
            @if($group['has_dropdown'] ?? false)
                <div class="col-xl-3 col-lg-4 col-md-6 setting-card-item" data-category="{{ $group['category'] ?? 'General' }}" data-name="{{ strtolower($group['name']) }}" data-desc="{{ strtolower($group['description']) }}">
                    <div class="card h-100 border-0 shadow-sm settings-hover-card dropdown position-relative">
                        <div class="card-body text-center p-4 cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                            <div class="avatar avatar-lg mb-3 mx-auto" style="width: 58px; height: 58px;">
                                <div class="avatar-initial bg-label-{{ $group['color'] }} rounded-circle d-flex align-items-center justify-content-center w-100 h-100">
                                    <i class="{{ $group['icon'] }} fs-3"></i>
                                </div>
                            </div>
                            <h6 class="card-title fw-semibold text-dark mb-2">{{ $group['name'] }}</h6>
                            <p class="card-text text-muted small mb-3" style="min-height: 40px;">{{ $group['description'] }}</p>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span class="badge bg-label-{{ $group['color'] }} fs-tiny">{{ $group['category'] }}</span>
                                <button type="button" class="btn btn-xs btn-outline-{{ $group['color'] }} rounded-pill px-3 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    Manage <i class="bx bx-chevron-down fs-tiny ms-1"></i>
                                </button>
                            </div>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-center shadow-lg border-0 rounded-4 p-2 w-100 animate slideIn" style="min-width: 250px; margin-top: 5px; box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;">
                            <li class="dropdown-header text-uppercase text-muted fw-bold fs-tiny px-3 py-2">Select Department Option</li>
                            <li><hr class="dropdown-divider my-1"></li>
                            @foreach($group['dropdown_options'] as $opt)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-3 py-2 px-3 rounded-3" href="{{ route($opt['route']) }}">
                                        <div class="avatar avatar-xs bg-label-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; flex-shrink: 0;">
                                            <i class="{{ $opt['icon'] }} fs-5"></i>
                                        </div>
                                        <div class="text-start">
                                            <div class="fw-bold text-dark fs-6 mb-0">{{ $opt['name'] }}</div>
                                            <small class="text-muted d-block" style="font-size: 0.73rem;">{{ $opt['description'] }}</small>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @else
                @php
                    $hasRoute = isset($group['route']) && Route::has($group['route']);
                    $targetUrl = $hasRoute ? route($group['route']) : '#';
                @endphp
                <div class="col-xl-3 col-lg-4 col-md-6 setting-card-item" data-category="{{ $group['category'] ?? 'General' }}" data-name="{{ strtolower($group['name']) }}" data-desc="{{ strtolower($group['description']) }}">
                    <a href="{{ $targetUrl }}" class="card h-100 border-0 shadow-sm text-decoration-none settings-hover-card">
                        <div class="card-body text-center p-4">
                            <div class="avatar avatar-lg mb-3 mx-auto" style="width: 58px; height: 58px;">
                                <div class="avatar-initial bg-label-{{ $group['color'] }} rounded-circle d-flex align-items-center justify-content-center w-100 h-100">
                                    <i class="{{ $group['icon'] }} fs-3"></i>
                                </div>
                            </div>
                            <h6 class="card-title fw-semibold text-dark mb-2">{{ $group['name'] }}</h6>
                            <p class="card-text text-muted small mb-3" style="min-height: 40px;">{{ $group['description'] }}</p>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span class="badge bg-label-{{ $group['color'] }} fs-tiny">{{ $group['category'] }}</span>
                                <span class="btn btn-xs btn-outline-{{ $group['color'] }} rounded-pill px-3">Manage <i class="bx bx-chevron-right fs-tiny me-0"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Quick Stats Footer -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold text-dark"><i class="bx bx-check-shield me-2 text-success"></i>System Configuration Status</h6>
                    <span class="badge bg-success">All 20 Settings Active</span>
                </div>
                <div class="card-body py-3">
                    <div class="row text-center g-3">
                        <div class="col-md-3 col-6">
                            <div class="border-end pe-3">
                                <h5 class="fw-bold text-primary mb-0">6</h5>
                                <small class="text-muted">Business & Organization</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="border-end pe-3">
                                <h5 class="fw-bold text-info mb-0">7</h5>
                                <small class="text-muted">HR, Leave & Schedule</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="border-end pe-3">
                                <h5 class="fw-bold text-warning mb-0">5</h5>
                                <small class="text-muted">System & Notifications</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div>
                                <h5 class="fw-bold text-danger mb-0">2</h5>
                                <small class="text-muted">Security & Access</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-hover-card {
    transition: all 0.25s ease-in-out;
}
.settings-hover-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(149, 157, 165, 0.2) !important;
}
.bg-label-primary { background-color: #e7e7ff !important; color: #696cff !important; }
.bg-label-info { background-color: #d7f5fc !important; color: #03c3ec !important; }
.bg-label-success { background-color: #e8f8ed !important; color: #71dd37 !important; }
.bg-label-warning { background-color: #fff2d6 !important; color: #ffab00 !important; }
.bg-label-danger { background-color: #ffe5e5 !important; color: #ff3e1d !important; }
.fs-tiny { font-size: 0.72rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('settingsSearchInput');
    const filterButtons = document.querySelectorAll('#settingsCategoryFilter button');
    const cards = document.querySelectorAll('.setting-card-item');

    let currentFilter = 'all';

    function filterCards() {
        const query = searchInput.value.toLowerCase().trim();

        cards.forEach(card => {
            const category = card.getAttribute('data-category');
            const name = card.getAttribute('data-name');
            const desc = card.getAttribute('data-desc');

            const matchesCategory = (currentFilter === 'all') || (category === currentFilter);
            const matchesSearch = !query || name.includes(query) || desc.includes(query);

            if (matchesCategory && matchesSearch) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterCards);

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary', 'active');

            currentFilter = this.getAttribute('data-filter');
            filterCards();
        });
    });
});
</script>
@endsection
