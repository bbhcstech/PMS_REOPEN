@extends('admin.layout.app')

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
<div class="employee-dashboard !min-h-screen !overflow-x-hidden !px-3 !py-4 sm:!px-5 sm:!py-5 lg:!px-8 lg:!py-7">

    {{-- Breadcrumb with Animation --}}
    <div class="breadcrumb-wrapper animate-slideDown !mb-4 !overflow-x-auto !rounded-[18px]">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="fas fa-users me-1"></i> Employees
                </li>
            </ol>
        </nav>
    </div>

    @php
        // Calculate employee counts for statistics - FUNCTIONALITY UNCHANGED
        $totalEmployees = $employees->count();
        $activeEmployees = $employees->where('employeeDetail.status', 'Active')->count();
        $inactiveEmployees = $employees->where('employeeDetail.status', 'Inactive')->count();
        $noticeEmployees = $employees->filter(function($emp) {
            return $emp->employeeDetail?->employment_status === 'notice';
        })->count();
        $internEmployees = $employees->filter(function($emp) {
            $designation = strtolower($emp->employeeDetail?->designation?->name ?? '');
            return strpos($designation, 'intern') !== false;
        })->count();

        // Filter visible employees - FUNCTIONALITY UNCHANGED
        $visibleEmployees = $employees->filter(function($emp) {
            $detail = $emp->employeeDetail ?? null;
            $status = $detail?->employment_status ?? null;
            $hasNotice = !empty($detail?->notice_end_date);
            $hasProbation = !empty($detail?->probation_end_date);

            if (in_array($status, ['notice','probation'])) return false;
            if ($hasNotice || $hasProbation) return false;
            return true;
        });
    @endphp

    {{-- Header Section --}}
    <div class="header-section animate-fadeIn !rounded-[22px] !p-4 sm:!p-5 lg:!p-6">
        <div class="header-content !flex !flex-col !gap-4 lg:!flex-row lg:!items-center lg:!justify-between">
            <div class="title-wrapper !flex !items-center !gap-3 sm:!gap-4">
                <div class="icon-circle animate-float !shrink-0">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h1 class="!text-xl !font-black !leading-tight sm:!text-2xl lg:!text-3xl">Employee Management</h1>
                    <p class="subtitle !mt-1 !text-sm sm:!text-base">Manage all employees, their details, and permissions</p>
                </div>
            </div>
            <div class="action-buttons !flex !w-full !flex-wrap !items-center !gap-2 sm:!gap-3 lg:!w-auto lg:!justify-end">
                <!-- Quick Actions Dropdown -->
                <div class="quick-actions">
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle !w-full !justify-center sm:!w-auto" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('employees.archive') }}"><i class="fas fa-box-archive me-2 text-success"></i>Archived Employees</a></li>
                            <li><a class="dropdown-item" href="#" id="bulkDeleteTrigger"><i class="fas fa-box-archive me-2 text-danger"></i>Archive Inactive</a></li>
                            <li><hr class="dropdown-divider"></li>
                        </ul>
                    </div>
                </div>

                <!-- Archived Employees Button -->
                <a href="{{ route('employees.archive') }}" class="btn btn-archive-employees !min-w-[150px] !justify-center">
                    <i class="fas fa-box-archive me-2"></i>
                    <span>Archived</span>
                </a>

                <!-- Add Employee Button -->
                <a href="{{ route('employees.create') }}" class="btn btn-add animate-pulse !min-w-[140px] !justify-center" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">
                    <i class="fas fa-user-plus me-2" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;"></i>
                    <span style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">Add Employee</span>
                </a>

                <!-- Invite Button -->
                <button type="button" class="btn btn-invite animate-pulse !min-w-[110px] !justify-center" data-bs-toggle="modal" data-bs-target="#inviteModal" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">
                    <i class="fas fa-envelope me-2" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;"></i>
                    <span style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">Invite</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Cards - 5 Cards Perfect Alignment with Animation --}}
    <div class="stats-grid !grid !grid-cols-1 !gap-3 sm:!grid-cols-2 sm:!gap-4 xl:!grid-cols-4 xl:!gap-5">
        <!-- Total Employees Card -->
        <div class="stat-card total animate-slideUp" style="animation-delay: 0.1s;">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Employees</span>
                <span class="stat-value">{{ $totalEmployees }}</span>
            </div>
            <div class="stat-glow"></div>
        </div>

        <!-- Active Employees Card -->
        <div class="stat-card active animate-slideUp" style="animation-delay: 0.2s;">
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Active</span>
                <span class="stat-value">{{ $activeEmployees }}</span>
            </div>
            <div class="stat-glow"></div>
        </div>

        <!-- Inactive Employees Card -->
        <div class="stat-card inactive animate-slideUp" style="animation-delay: 0.3s;">
            <div class="stat-icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Inactive</span>
                <span class="stat-value">{{ $inactiveEmployees }}</span>
            </div>
            <div class="stat-glow"></div>
        </div>

        <!-- On Notice Card -->
        <div class="stat-card notice animate-slideUp" style="animation-delay: 0.4s;">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">On Notice</span>
                <span class="stat-value">{{ $noticeEmployees }}</span>
            </div>
            <div class="stat-glow"></div>
        </div>
    </div>

    @if(auth()->user()?->normalizedRole() === 'admin')
    {{-- Company Selector - SaaS company workspace filter --}}
    <div class="company-switcher-grid !grid !grid-cols-1 !gap-3 sm:!grid-cols-2 lg:!grid-cols-3 2xl:!grid-cols-6 !mb-5">
        <a href="{{ route('employees.index', request()->except(['company_id', 'page'])) }}"
           class="company-switch-card {{ empty($selectedCompanyId) ? 'active' : '' }}">
            <span class="company-switch-icon"><i class="fas fa-layer-group"></i></span>
            <span class="company-switch-meta">
                <strong>All Companies</strong>
                <small>{{ $companyStats->sum('employees') }} employees</small>
            </span>
        </a>
        @foreach($companyStats as $stat)
            @php
                $company = $stat['company'];
                $companyQuery = array_merge(request()->except('page'), ['company_id' => $company->id]);
            @endphp
            <a href="{{ route('employees.index', $companyQuery) }}"
               class="company-switch-card {{ (string) $selectedCompanyId === (string) $company->id ? 'active' : '' }}">
                <span class="company-switch-icon">
                    @if($company->logo)
                        <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}">
                    @else
                        {{ strtoupper(substr($company->short_name ?: $company->company_code ?: $company->name, 0, 2)) }}
                    @endif
                </span>
                <span class="company-switch-meta">
                    <strong>{{ $company->name }}</strong>
                    <small>{{ $stat['employees'] }} employees • {{ $stat['active'] }} active</small>
                </span>
            </a>
        @endforeach
    </div>
    @if($selectedCompanyId)
        <div class="company-module-rail !mb-5">
            <span>Company workspace</span>
            <a href="{{ route('attendance.index', ['company_id' => $selectedCompanyId]) }}"><i class="fas fa-calendar-check"></i> Attendance</a>
            <a href="{{ route('leaves.index', ['company_id' => $selectedCompanyId]) }}"><i class="fas fa-calendar-days"></i> Leaves</a>
            <a href="{{ route('payroll.index', ['company_id' => $selectedCompanyId]) }}"><i class="fas fa-wallet"></i> Payroll</a>
            <a href="{{ route('payroll.payslips.index', ['company_id' => $selectedCompanyId]) }}"><i class="fas fa-file-invoice-dollar"></i> Payslips</a>
        </div>
    @endif
    @endif

    {{-- Invite Modal - FUNCTIONALITY UNCHANGED --}}
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-paper-plane me-2"></i>
                        Invite to Xinksoft Technologies
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs" id="inviteTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="invite-email-tab" data-bs-toggle="tab" data-bs-target="#invite-email" type="button" role="tab">
                                <i class="fas fa-envelope me-2"></i>Invite by Email
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="invite-link-tab" data-bs-toggle="tab" data-bs-target="#invite-link" type="button" role="tab">
                                <i class="fas fa-link me-2"></i>Invite by Link
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="inviteTabsContent">
                        <!-- Invite by Email -->
                        <div class="tab-pane fade show active" id="invite-email" role="tabpanel">
                            <div class="alert alert-info bg-light">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Employees will receive an email to log in and update their profile.
                            </div>

                            <form id="inviteEmailForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                        <input type="email" name="email" id="inviteEmail" class="form-control" placeholder="e.g. johndoe@xinksoft.com" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Personal Message <span class="text-muted">(Optional)</span></label>
                                    <textarea name="message" id="inviteMessage" class="form-control" rows="3" placeholder="Add a welcome message..."></textarea>
                                </div>
                            </form>
                        </div>

                        <!-- Invite by Link -->
                        <div class="tab-pane fade" id="invite-link" role="tabpanel">
                            <div class="alert alert-light border">
                                <i class="fas fa-link text-primary me-2"></i>
                                Create an invitation link that can be shared with multiple people.
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold mb-3">Link Restrictions:</label>
                                <div class="list-group">
                                    <label class="list-group-item border-0 bg-light">
                                        <input class="form-check-input me-2" type="radio" name="linkOption" id="anyEmail" checked>
                                        <span class="fw-medium">Allow any email address</span>
                                        <small class="text-muted d-block mt-1">Anyone with the link can join</small>
                                    </label>
                                    <label class="list-group-item border-0 bg-light mt-2">
                                        <input class="form-check-input me-2" type="radio" name="linkOption" id="domainEmail">
                                        <span class="fw-medium">Restrict to company domain</span>
                                        <small class="text-muted d-block mt-1">Only @xinksoft.com emails allowed</small>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary px-4" id="createLinkBtn">
                                    <i class="fas fa-plus-circle me-2"></i>Generate Invite Link
                                </button>
                            </div>

                            <div class="mt-4" id="linkContainer" style="display:none;">
                                <div class="alert alert-success border-0 bg-success bg-opacity-10">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span class="fw-medium">Invitation link created successfully!</span>
                                    <small class="d-block mt-1">This link will expire in 7 days</small>
                                </div>

                                <div class="input-group">
                                    <input type="text" class="form-control border-end-0" id="inviteLink" readonly>
                                    <button class="btn btn-outline-primary" type="button" id="copyLinkBtn">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button class="btn btn-outline-success" type="button" id="shareLinkBtn" title="Share via Email">
                                        <i class="fas fa-share-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="inviteEmailForm" id="sendInviteBtn" class="btn btn-primary">
                        <span id="sendInviteSpinner" class="spinner-border spinner-border-sm me-2 d-none"></span>
                        <i class="fas fa-paper-plane me-2"></i>Send Invitation
                    </button>
                </div>
                <div class="mt-3 px-3" id="inviteEmailAlert" style="display:none;"></div>
            </div>
        </div>
    </div>

    {{-- Blocked Delete Modal - FUNCTIONALITY UNCHANGED --}}
    <div class="modal fade" id="blockedDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Delete Restricted
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-users-slash fa-4x text-warning mb-3"></i>
                    <h5 id="blockedEmployeeName" class="fw-bold mb-2"></h5>
                    <p class="text-muted mb-0" id="blockedEmployeeReason"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <a href="#" id="blocked-view-subordinates" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-2"></i>View Team Members
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts Section - FUNCTIONALITY UNCHANGED --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 animate-shake" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 animate-slideDown" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Filter & Export Section - PERFECT ALIGNMENT --}}
    <div class="filter-export-card employee-filter-polish animate-fadeIn !overflow-visible !rounded-[22px]">
        <div class="filter-header !flex !flex-col !gap-4 !p-4 sm:!p-5 lg:!flex-row lg:!items-center lg:!justify-between">
            <div class="header-left !flex !items-start !gap-3 sm:!items-center">
                <div class="header-icon-wrapper !shrink-0">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div class="header-text">
                    <h3>Filter Employees</h3>
                    <p>Narrow down your employee list</p>
                </div>
            </div>
            <div class="header-right !flex !w-full !flex-col !gap-2 sm:!w-auto sm:!flex-row sm:!flex-wrap sm:!items-center sm:!justify-end">
                <span id="export-selected-count" class="selected-badge !inline-flex !justify-center">0 selected</span>
                <button type="button" class="btn btn-export-all !justify-center" id="export-all">
                    <i class="fas fa-download me-2"></i>Export All
                </button>
                <div class="btn-group !flex !flex-wrap !gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="export-copy" disabled title="Copy">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success" id="export-csv" disabled title="CSV">
                        <i class="fas fa-file-csv"></i>
                    </button>
                    <button type="button" class="btn btn-outline-info" id="export-excel" disabled title="Excel">
                        <i class="fas fa-file-excel"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="export-pdf" disabled title="PDF">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="export-print" disabled title="Print">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="filter-body employee-filter-body !p-4 sm:!p-5">
            <form method="GET" action="{{ route('employees.index') }}" class="filter-form">
                <div class="filter-grid employee-filter-grid !grid !grid-cols-1 !gap-4 sm:!grid-cols-2 xl:!grid-cols-4">
                    @if($selectedCompanyId)
                        <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}">
                    @endif
                    <!-- Employee ID -->
                    <div class="filter-item employee-filter-field">
                        <label class="filter-label">
                            <i class="fas fa-id-card me-1"></i>Employee ID
                        </label>
                        <select name="employee_id" class="form-select select2">
                            <option value="">All Employees</option>
                            @php
                                $edOptions = $employeeDetails->filter(function($d){
                                    $status = $d->employment_status ?? null;
                                    $hasNotice = !empty($d->notice_end_date);
                                    $hasProb = !empty($d->probation_end_date);
                                    if (in_array($status, ['notice','probation'])) return false;
                                    if ($hasNotice || $hasProb) return false;
                                    return true;
                                });
                            @endphp
                            @foreach($edOptions as $detail)
                                <option value="{{ $detail->employee_id }}" {{ request('employee_id') == $detail->employee_id ? 'selected' : '' }}>
                                    {{ $detail->employee_id }} - {{ $detail->user->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Designation -->
                    <div class="filter-item employee-filter-field">
                        <label class="filter-label">
                            <i class="fas fa-briefcase me-1"></i>Designation
                        </label>
                        <select name="designation_id" class="form-select select2">
                            <option value="">All Designations</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                                    {{ $designation->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Name/Email -->
                    <div class="filter-item employee-filter-field">
                        <label class="filter-label">
                            <i class="fas fa-user me-1"></i>Search Name/Email
                        </label>
                        <select name="user_id" class="form-select select2">
                            <option value="">Search Employee...</option>
                            @foreach($edOptions as $detail)
                                <option value="{{ $detail->user_id }}" {{ request('user_id') == $detail->user_id ? 'selected' : '' }}>
                                    {{ $detail->user->name ?? 'N/A' }} - {{ $detail->user->email ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="filter-actions employee-filter-actions !flex !flex-col !gap-2 sm:!flex-row sm:!items-end xl:!justify-end">
                        <button type="submit" class="btn btn-apply !justify-center">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-reset !justify-center">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table Card - PERFECT ALIGNMENT --}}
    <div class="table-card animate-slideUp !overflow-hidden !rounded-[22px]">
        <div class="table-header !flex !flex-col !gap-4 !p-4 sm:!p-5 lg:!flex-row lg:!items-center lg:!justify-between">
            <div class="table-title !flex !items-start !gap-3 sm:!items-center">
                <div class="title-icon-wrapper !shrink-0">
                    <i class="fas fa-list"></i>
                </div>
                <div class="title-text">
                    <h3>Employee List</h3>
                    <span class="employee-count">{{ $visibleEmployees->count() }} employees</span>
                </div>
            </div>
            <div class="table-actions !flex !w-full !flex-wrap !items-center !gap-2 sm:!w-auto sm:!justify-end">
                <button id="btn-bulk-delete" class="btn btn-bulk-delete !justify-center" disabled>
                    <i class="fas fa-box-archive me-2"></i>Archive Inactive
                </button>
                <span id="bulk-selected-count" class="selected-badge !inline-flex !justify-center">0 selected</span>
            </div>
        </div>

        <div class="table-responsive !w-full !overflow-x-auto">
            <table id="employeeTable" class="table table-hover align-middle mb-0 employee-list-table !min-w-[1200px] md:!min-w-full">
                <thead>
                    <tr>
                        <th width="50">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th width="140">Emp ID</th>
                        <th>Employee Name</th>
                        <th width="190">Company</th>
                        <th width="200">Email</th>
                        <th width="220">Role & Reporting</th>
                        <th width="120">Status</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($visibleEmployees->isEmpty())
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Employees Found</h5>
                                    <p class="text-muted mb-3">Try adjusting your filters or add new employees</p>
                                    <a href="{{ route('employees.create') }}" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-2"></i>Add First Employee
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @else
                        @foreach($visibleEmployees as $employee)
                        <tr id="employee-row-{{ $employee->id }}"
                            class="@if(isset($employee->subordinate_count) && $employee->subordinate_count > 0) table-warning @endif">
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input employee-checkbox"
                                           value="{{ $employee->id }}"
                                           @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0) disabled @endif
                                           data-employee-id="{{ $employee->employeeDetail?->employee_id ?? '-' }}"
                                           data-name="{{ htmlspecialchars($employee->name, ENT_QUOTES, 'UTF-8') }}"
                                           data-email="{{ $employee->email ?? '-' }}"
                                           data-company="{{ htmlspecialchars($employee->company?->name ?? 'Unassigned', ENT_QUOTES, 'UTF-8') }}"
                                           data-designation="{{ htmlspecialchars($employee->employeeDetail?->designation?->name ?? '-', ENT_QUOTES, 'UTF-8') }}"
                                           data-reporting-to="{{ htmlspecialchars($employee->employeeDetail?->reportingTo?->name ?? 'N/A', ENT_QUOTES, 'UTF-8') }}"
                                           data-status="{{ $employee->employeeDetail?->status === 'Active' ? 'Active' : ($employee->employeeDetail?->status === 'Inactive' ? 'Inactive' : 'N/A') }}">
                                    </div>
                            </td>
                            <td>
                                <span class="employee-id-badge">
                                    {{ $employee->employeeDetail?->employee_id ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div class="employee-info">
                                    @if(!empty($employee->profile_image))
                                        <img src="{{ asset($employee->profile_image) }}" alt="Profile" class="profile-image">
                                    @else
                                        <div class="profile-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <div class="employee-details">
                                        <h6 class="employee-name">{{ $employee->name }}</h6>
                                        <span class="employee-designation">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</span>
                                        <div class="employee-badges">
                                            @php
                                                $detail = $employee->employeeDetail;
                                                $status = $detail?->employment_status ?? null;
                                                $designationName = strtolower($detail?->designation?->name ?? '');
                                            @endphp

                                            @if ($status === 'notice')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i> Notice
                                                </span>
                                            @elseif (strpos($designationName, 'intern') !== false)
                                                <span class="badge bg-info">
                                                    <i class="fas fa-graduation-cap me-1"></i> Intern
                                                </span>
                                            @else
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-user-tie me-1"></i> Employee
                                                </span>
                                            @endif

                                            @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0)
                                                <span class="badge bg-warning text-dark ms-1">
                                                    <i class="fas fa-users me-1"></i> Lead
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="employee-company-chip">
                                    <span class="employee-company-logo">
                                        @if($employee->company?->logo)
                                            <img src="{{ asset($employee->company->logo) }}" alt="{{ $employee->company->name }}">
                                        @else
                                            {{ strtoupper(substr($employee->company?->short_name ?: $employee->company?->company_code ?: $employee->company?->name ?: 'NA', 0, 2)) }}
                                        @endif
                                    </span>
                                    <span>{{ $employee->company?->name ?? 'Unassigned' }}</span>
                                </span>
                            </td>
                            <td>
                                <div class="email-wrapper">
                                    <i class="fas fa-envelope email-icon"></i>
                                    <a href="mailto:{{ $employee->email }}" class="email-link">
                                        {{ Str::limit($employee->email, 25) }}
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="role-info">
                                    <span class="role-name">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</span>
                                    <span class="reports-to">
                                        <i class="fas fa-user-friends"></i>
                                        {{ $employee->employeeDetail?->reportingTo?->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($employee->employeeDetail?->status === 'Active')
                                    <span class="status-badge status-active">
                                        <span class="status-dot"></span>
                                        Active
                                    </span>
                                @elseif($employee->employeeDetail?->status === 'Inactive')
                                    <span class="status-badge status-inactive">
                                        <span class="status-dot"></span>
                                        Inactive
                                    </span>
                                @else
                                    <span class="status-badge status-unknown">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('employees.show', $employee->id) }}">
                                                <i class="fas fa-eye text-primary me-2"></i> View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('employees.edit', $employee->id) }}">
                                                <i class="fas fa-edit text-info me-2"></i> Edit Profile
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0)
                                                <button class="dropdown-item text-warning blocked-delete-btn" type="button"
                                                    data-employee-name="{{ $employee->name }}"
                                                    data-subordinate-count="{{ $employee->subordinate_count }}"
                                                    data-employee-id="{{ $employee->id }}">
                                                    <i class="fas fa-ban me-2"></i> Archive Restricted
                                                </button>
                                            @elseif($employee->employeeDetail?->status !== 'Inactive')
                                                <button class="dropdown-item text-muted" type="button" disabled>
                                                    <i class="fas fa-lock me-2"></i> Archive only inactive
                                                </button>
                                            @else
                                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
                                                      onsubmit="return confirm('Archive this inactive employee? The data will move to Archived Employees and can be restored.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit">
                                                        <i class="fas fa-box-archive me-2"></i> Archive Employee
                                                    </button>
                                                </form>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination & Show Entries - PERFECT ALIGNMENT --}}
        @if(!$visibleEmployees->isEmpty())
        <div class="table-footer !flex !flex-col !gap-4 !p-4 sm:!p-5 lg:!flex-row lg:!items-center lg:!justify-between">
            <div class="footer-left !flex !w-full !justify-center lg:!w-auto lg:!justify-start">
                <div class="show-entries !flex !flex-wrap !items-center !justify-center !gap-2">
                    <span>Show</span>
                    <select class="form-select form-select-sm" id="showEntries">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>entries</span>
                </div>
            </div>

            @if(method_exists($employees, 'total'))
            <div class="footer-center !flex !w-full !justify-center !text-center lg:!w-auto">
                <span class="pagination-info">
                    Showing {{ ($employees->currentPage() - 1) * $employees->perPage() + 1 }}
                    - {{ min($employees->currentPage() * $employees->perPage(), $employees->total()) }}
                    of {{ $employees->total() }}
                </span>
            </div>
            @endif

            @if(method_exists($employees, 'currentPage'))
            <div class="footer-right !flex !w-full !justify-center lg:!w-auto lg:!justify-end">
                <div class="pagination !flex !flex-wrap !justify-center !gap-2">
                    @if($employees->onFirstPage())
                        <button class="page-btn disabled">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    @else
                        <a href="{{ $employees->previousPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                           class="page-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    @php
                        $currentPage = $employees->currentPage();
                        $lastPage = $employees->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @for ($i = $start; $i <= $end; $i++)
                        @if ($i == $currentPage)
                            <span class="page-btn active">{{ $i }}</span>
                        @else
                            <a href="{{ $employees->url($i) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                               class="page-btn">{{ $i }}</a>
                        @endif
                    @endfor

                    @if($lastPage > $end)
                        <span class="page-dots">...</span>
                        <a href="{{ $employees->url($lastPage) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                           class="page-btn">{{ $lastPage }}</a>
                    @endif

                    @if($employees->hasMorePages())
                        <a href="{{ $employees->nextPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                           class="page-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button class="page-btn disabled">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- LIGHT PURPLE THEME - SOFT & ANIMATED --}}
<style>
    /* ===== LIGHT PURPLE THEME - SOFT, ANIMATED & EYE-FRIENDLY ===== */

    /* Main Background - Light Purple Gradient */
    .employee-dashboard {
        padding: 30px 35px;
        background: linear-gradient(145deg, #f9f5ff 0%, #f3ebff 50%, #f1e7ff 100%);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        position: relative;
        overflow-x: hidden;
    }

    /* Soft Purple Overlay Effect */
    .employee-dashboard::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 10% 20%, rgba(170, 140, 250, 0.08) 0%, transparent 40%),
                    radial-gradient(circle at 90% 70%, rgba(150, 120, 250, 0.08) 0%, transparent 40%),
                    radial-gradient(circle at 30% 80%, rgba(180, 150, 255, 0.06) 0%, transparent 45%);
        pointer-events: none;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes slideDown {
        0% { opacity: 0; transform: translateY(-20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
        100% { transform: translateY(0px); }
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(139, 92, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
        20%, 40%, 60%, 80% { transform: translateX(2px); }
    }

    .animate-slideDown { animation: slideDown 0.6s ease forwards; }
    .animate-slideUp { animation: slideUp 0.6s ease forwards; }
    .animate-fadeIn { animation: fadeIn 0.8s ease forwards; }
    .animate-float { animation: float 4s ease-in-out infinite; }
    .animate-pulse { animation: pulse 2s infinite; }
    .animate-shake { animation: shake 0.5s ease forwards; }

    /* Breadcrumb - Light Glassmorphism */
    .breadcrumb-wrapper {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(139, 92, 246, 0.15);
        border-radius: 16px;
        padding: 16px 24px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.05);
    }

    .breadcrumb {
        display: flex;
        flex-wrap: wrap;
        padding: 0;
        margin-bottom: 0;
        list-style: none;
        background: transparent;
    }

    .breadcrumb-item {
        font-size: 0.95rem;
        font-weight: 500;
    }

    .breadcrumb-item a {
        color: #6d28d9;
        text-decoration: none;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: #5b21b6;
    }

    .breadcrumb-item.active {
        color: #4c1d95;
        font-weight: 600;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: #a78bfa;
        font-size: 1.2rem;
        line-height: 1;
        margin: 0 10px;
    }

    /* Header Section - Light Glassmorphism */
    .header-section {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(139, 92, 246, 0.2);
        border-radius: 20px;
        padding: 25px 30px;
        margin-bottom: 35px;
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.08);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .title-wrapper {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .icon-circle {
        width: 65px;
        height: 65px;
        background: linear-gradient(145deg, #c4b5fd, #a78bfa);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        box-shadow: 0 10px 20px rgba(167, 139, 250, 0.25);
    }

    .title-wrapper h1 {
        font-size: 1.9rem;
        font-weight: 700;
        color: #2d1b4e;
        letter-spacing: -0.02em;
        margin: 0;
    }

    .subtitle {
        color: #6b4e9e;
        font-size: 0.95rem;
        margin-top: 5px;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        border: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-outline-light {
        background: white;
        border: 1px solid #e0d7ff;
        color: #5b4b7a;
    }

    .btn-outline-light:hover {
        background: #f5f0ff;
        border-color: #a78bfa;
        color: #6d28d9;
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(167, 139, 250, 0.15);
    }

    .btn-add {
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        box-shadow: 0 8px 18px rgba(16, 185, 129, 0.2);
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(16, 185, 129, 0.3);
    }

    .btn-invite {
        background: linear-gradient(145deg, #c084fc, #a78bfa);
        color: white;
        box-shadow: 0 8px 18px rgba(167, 139, 250, 0.25);
    }

    .btn-invite:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(167, 139, 250, 0.35);
    }

    /* Stats Grid - PERFECT 5 COLUMN ALIGNMENT */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 22px;
        margin-bottom: 35px;
    }

    .stat-card {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.2);
        border-radius: 20px;
        padding: 22px 18px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.06);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: linear-gradient(to bottom, #c084fc, #a78bfa);
    }

    .stat-card.total::before { background: linear-gradient(to bottom, #818cf8, #6366f1); }
    .stat-card.active::before { background: linear-gradient(to bottom, #34d399, #10b981); }
    .stat-card.inactive::before { background: linear-gradient(to bottom, #f87171, #ef4444); }
    .stat-card.notice::before { background: linear-gradient(to bottom, #fbbf24, #f59e0b); }
    .stat-card.interns::before { background: linear-gradient(to bottom, #60a5fa, #3b82f6); }

    .stat-card:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow: 0 15px 35px rgba(139, 92, 246, 0.12);
        border-color: rgba(167, 139, 250, 0.4);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }

    .stat-card.total .stat-icon {
        background: #e0e7ff;
        color: #4f46e5;
    }

    .stat-card.active .stat-icon {
        background: #d1fae5;
        color: #059669;
    }

    .stat-card.inactive .stat-icon {
        background: #fee2e2;
        color: #b91c1c;
    }

    .stat-card.notice .stat-icon {
        background: #fef3c7;
        color: #d97706;
    }

    .stat-card.interns .stat-icon {
        background: #dbeafe;
        color: #2563eb;
    }

    .stat-content {
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .stat-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }

    .stat-glow {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        filter: blur(30px);
        opacity: 0.1;
        pointer-events: none;
    }

    .stat-card.total .stat-glow { background: #6366f1; }
    .stat-card.active .stat-glow { background: #10b981; }
    .stat-card.inactive .stat-glow { background: #ef4444; }
    .stat-card.notice .stat-glow { background: #f59e0b; }
    .stat-card.interns .stat-glow { background: #3b82f6; }

    /* Filter & Export Card - PERFECT ALIGNMENT */
    .filter-export-card {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.2);
        border-radius: 20px;
        margin-bottom: 30px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.06);
        transition: all 0.3s ease;
    }

    .filter-export-card:hover {
        box-shadow: 0 12px 35px rgba(139, 92, 246, 0.1);
        border-color: rgba(167, 139, 250, 0.3);
    }

    .filter-header {
        padding: 18px 25px;
        background: #faf7ff;
        border-bottom: 1px solid rgba(167, 139, 250, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-icon-wrapper {
        width: 44px;
        height: 44px;
        background: #ede9fe;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8b5cf6;
        font-size: 1.2rem;
    }

    .header-text h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d1b4e;
        margin: 0;
    }

    .header-text p {
        font-size: 0.8rem;
        color: #6b4e9e;
        margin: 2px 0 0;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .selected-badge {
        padding: 6px 14px;
        background: #ede9fe;
        color: #6d28d9;
        border-radius: 100px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .btn-export-all {
        background: linear-gradient(145deg, #c084fc, #a78bfa);
        color: white;
        padding: 8px 20px;
        border-radius: 10px;
        font-size: 0.9rem;
        border: none;
        box-shadow: 0 4px 12px rgba(167, 139, 250, 0.2);
    }

    .btn-export-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(167, 139, 250, 0.3);
    }

    .btn-group {
        display: flex;
        gap: 5px;
        background: white;
        padding: 5px;
        border-radius: 12px;
        border: 1px solid rgba(167, 139, 250, 0.2);
    }

    .btn-group .btn {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        color: #6b7280;
    }

    .btn-group .btn:hover:not(:disabled) {
        background: #f5f0ff;
        color: #8b5cf6;
        transform: scale(1.1);
    }

    .btn-group .btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .filter-body {
        padding: 25px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        align-items: flex-end;
    }

    .filter-item {
        display: flex;
        flex-direction: column;
    }

    .filter-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }

    .filter-select {
        width: 100%;
        height: 44px;
        background: white;
        border: 1px solid #e0d7ff;
        border-radius: 10px;
        padding: 0 16px;
        font-size: 0.9rem;
        color: #1f2937;
        transition: all 0.2s ease;
    }

    .filter-select:focus {
        border-color: #a78bfa;
        box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
        outline: none;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .btn-apply {
        background: linear-gradient(145deg, #8b5cf6, #7c3aed);
        color: white;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        flex: 1;
        border: none;
    }

    .btn-apply:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
    }

    .btn-reset {
        background: white;
        color: #6b7280;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        border: 1px solid #e0d7ff;
        flex: 1;
        text-decoration: none;
        text-align: center;
    }

    .btn-reset:hover {
        background: #f9fafb;
        color: #1f2937;
        border-color: #a78bfa;
    }

    /* Table Card - PERFECT ALIGNMENT */
    .table-card {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.2);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.06);
        transition: all 0.3s ease;
    }

    .table-card:hover {
        box-shadow: 0 12px 35px rgba(139, 92, 246, 0.1);
        border-color: rgba(167, 139, 250, 0.3);
    }

    .table-header {
        padding: 18px 25px;
        background: #faf7ff;
        border-bottom: 1px solid rgba(167, 139, 250, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .title-icon-wrapper {
        width: 44px;
        height: 44px;
        background: #f3e8ff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9b5de5;
        font-size: 1.2rem;
    }

    .title-text h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d1b4e;
        margin: 0;
    }

    .employee-count {
        font-size: 0.8rem;
        color: #6b4e9e;
    }

    .table-actions {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .btn-bulk-delete {
        background: #fee2e2;
        color: #b91c1c;
        padding: 8px 20px;
        border-radius: 10px;
        font-size: 0.9rem;
        border: 1px solid #fecaca;
    }

    .btn-bulk-delete:hover:not(:disabled) {
        background: #fecaca;
        color: #991b1b;
        transform: translateY(-2px);
    }

    .btn-bulk-delete:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .employee-list-table {
        border-collapse: collapse;
        table-layout: fixed;
    }

    .employee-list-table th,
    .employee-list-table td {
        box-sizing: border-box;
    }

    .table thead th {
        background: #faf7ff;
        color: #5b4b7a;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 18px 16px;
        border-bottom: 2px solid rgba(167, 139, 250, 0.2);
        text-align: left;
        white-space: nowrap;
    }

    .employee-list-table thead th,
    .employee-list-table tbody td {
        padding: 15px 16px;
    }

    .employee-list-table tbody td {
        border-bottom: 1px solid rgba(167, 139, 250, 0.1);
        color: #374151;
        vertical-align: middle;
        line-height: 1.35;
    }

    .employee-list-table tbody tr {
        height: 82px;
    }

    .employee-list-table thead th:first-child,
    .employee-list-table tbody td:first-child {
        padding-left: 18px;
        padding-right: 10px;
        text-align: center;
    }

    .employee-list-table thead th:last-child,
    .employee-list-table tbody td:last-child {
        padding-left: 10px;
        padding-right: 18px;
        text-align: center;
    }

    .employee-list-table .form-check {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        margin: 0;
    }

    .employee-list-table .dropdown {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: #fcf9ff;
    }

    .table tbody tr.selected {
        background: #f5f0ff;
        border-left: 3px solid #8b5cf6;
    }

    .table-warning {
        background-color: #fffbeb !important;
    }

    .table-warning:hover {
        background-color: #fef3c7 !important;
    }

    /* Employee ID Badge */
    .employee-id-badge {
        display: inline-block;
        padding: 6px 12px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #4b5563;
    }

    /* Employee Info */
    .employee-info {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .profile-image {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid white;
        box-shadow: 0 4px 10px rgba(139, 92, 246, 0.1);
    }

    .profile-placeholder {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #ede9fe;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8b5cf6;
        font-size: 1.1rem;
        border: 2px solid white;
        box-shadow: 0 4px 10px rgba(139, 92, 246, 0.1);
    }

    .employee-details {
        flex: 1;
        min-width: 0;
    }

    .employee-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-designation {
        font-size: 0.8rem;
        color: #6b7280;
        display: block;
        margin-bottom: 6px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 100px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .company-switcher-grid {
        margin-top: 18px;
    }

    .company-switch-card {
        align-items: center;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(15, 116, 76, 0.12);
        border-radius: 18px;
        box-shadow: 0 14px 34px rgba(15, 116, 76, 0.08);
        color: #07130d;
        display: flex;
        gap: 12px;
        min-height: 86px;
        padding: 14px;
        text-decoration: none;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .company-switch-card:hover {
        border-color: rgba(15, 116, 76, 0.28);
        box-shadow: 0 20px 42px rgba(15, 116, 76, 0.14);
        color: #07130d;
        transform: translateY(-2px);
    }

    .company-switch-card.active {
        background: linear-gradient(135deg, #ecfdf5, #ffffff);
        border-color: rgba(15, 116, 76, 0.42);
        box-shadow: 0 22px 46px rgba(15, 116, 76, 0.16);
    }

    .company-switch-icon,
    .employee-company-logo {
        align-items: center;
        background: linear-gradient(135deg, #0f744c, #22c55e);
        border-radius: 14px;
        color: #ffffff;
        display: inline-flex;
        flex: 0 0 auto;
        font-weight: 900;
        justify-content: center;
        overflow: hidden;
    }

    .company-switch-icon {
        height: 48px;
        width: 48px;
    }

    .company-switch-icon img,
    .employee-company-logo img {
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .company-switch-meta {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .company-switch-meta strong {
        color: #07130d;
        font-size: .9rem;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .company-switch-meta small {
        color: #52645a;
        font-size: .76rem;
        font-weight: 700;
        margin-top: 4px;
    }

    .employee-company-chip {
        align-items: center;
        display: inline-flex;
        gap: 8px;
        max-width: 180px;
        min-width: 0;
    }

    .employee-company-chip > span:last-child {
        color: #07130d;
        font-size: .82rem;
        font-weight: 800;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-company-logo {
        height: 32px;
        width: 32px;
        font-size: .72rem;
    }

    .company-module-rail {
        align-items: center;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(15, 116, 76, 0.12);
        border-radius: 16px;
        box-shadow: 0 14px 34px rgba(15, 116, 76, 0.08);
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 12px 14px;
    }

    .company-module-rail span {
        color: #52645a;
        font-size: .82rem;
        font-weight: 900;
        margin-right: 4px;
        text-transform: uppercase;
    }

    .company-module-rail a {
        align-items: center;
        background: #f4faf6;
        border: 1px solid rgba(15, 116, 76, 0.12);
        border-radius: 12px;
        color: #0f744c;
        display: inline-flex;
        font-size: .84rem;
        font-weight: 900;
        gap: 7px;
        min-height: 38px;
        padding: 8px 12px;
        text-decoration: none;
    }

    .company-module-rail a:hover {
        background: #ecfdf5;
        color: #0f744c;
    }

    .badge.bg-primary {
        background: #ede9fe !important;
        color: #6d28d9;
    }

    .badge.bg-info {
        background: #dbeafe !important;
        color: #2563eb;
    }

    .badge.bg-warning {
        background: #fef3c7 !important;
        color: #b45309;
    }

    /* Email */
    .email-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .email-icon {
        color: #8b5cf6;
        font-size: 0.9rem;
    }

    .email-link {
        color: #6b7280;
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-decoration: none;
        font-size: 0.9rem;
        text-overflow: ellipsis;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .email-link:hover {
        color: #6d28d9;
        text-decoration: underline;
    }

    /* Role Info */
    .role-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .role-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .reports-to {
        font-size: 0.8rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 5px;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .status-unknown {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .status-active .status-dot {
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }

    .status-inactive .status-dot {
        background: #ef4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
    }

    /* Action Dropdown */
    .btn-icon {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 10px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .btn-icon:hover {
        background: #ede9fe;
        border-color: #a78bfa;
        color: #6d28d9;
        transform: scale(1.1);
    }

    .dropdown-menu {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.3);
        border-radius: 14px;
        padding: 8px;
        box-shadow: 0 20px 40px rgba(139, 92, 246, 0.1);
    }

    .dropdown-item {
        padding: 10px 16px;
        color: #374151;
        font-size: 0.9rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .dropdown-item i {
        width: 20px;
        margin-right: 10px;
    }

    .dropdown-item:hover {
        background: #f5f0ff;
        color: #6d28d9;
    }

    .dropdown-divider {
        border-top: 1px solid #f0e7ff;
        margin: 8px 0;
    }

    /* Empty State */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-state i {
        color: #d4c2ff;
    }

    .empty-state h5 {
        color: #6b4e9e;
        font-weight: 600;
    }

    .empty-state p {
        color: #9ca3af;
    }

    /* Table Footer - PERFECT ALIGNMENT */
    .table-footer {
        padding: 18px 25px;
        background: #faf7ff;
        border-top: 1px solid rgba(167, 139, 250, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .footer-left, .footer-center, .footer-right {
        display: flex;
        align-items: center;
    }

    .show-entries {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .show-entries select {
        background: white;
        border: 1px solid #e0d7ff;
        border-radius: 8px;
        color: #1f2937;
        padding: 6px 12px;
        width: 70px;
    }

    .show-entries select:focus {
        border-color: #a78bfa;
        box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
    }

    .pagination-info {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .pagination {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .page-btn {
        min-width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: white;
        border: 1px solid #e0d7ff;
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .page-btn:hover:not(.disabled) {
        background: #ede9fe;
        border-color: #a78bfa;
        color: #6d28d9;
        transform: scale(1.05);
    }

    .page-btn.active {
        background: #8b5cf6;
        border-color: #8b5cf6;
        color: white;
    }

    .page-btn.disabled {
        background: #f3f4f6;
        color: #9ca3af;
        border-color: #e5e7eb;
        cursor: not-allowed;
    }

    .page-dots {
        color: #9ca3af;
        padding: 0 5px;
    }

    /* Modal Styles */
    .modal-content {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.3);
        border-radius: 20px;
    }

    .modal-header {
        background: #faf7ff;
        border-bottom: 1px solid rgba(167, 139, 250, 0.2);
        padding: 20px 25px;
    }

    .modal-header.bg-warning {
        background: #fffbeb !important;
    }

    .modal-title {
        color: #2d1b4e;
        font-weight: 600;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        border-top: 1px solid rgba(167, 139, 250, 0.2);
        padding: 20px 25px;
    }

    .form-control, .input-group-text {
        background: white;
        border: 1px solid #e0d7ff;
        color: #1f2937;
    }

    .form-control:focus {
        border-color: #a78bfa;
        box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
    }

    .input-group-text {
        background: #f5f0ff;
        color: #8b5cf6;
    }

    .nav-tabs {
        border-bottom: 1px solid #e0d7ff;
    }

    .nav-tabs .nav-link {
        color: #6b7280;
        border: none;
        padding: 12px 20px;
    }

    .nav-tabs .nav-link.active {
        background: transparent;
        color: #6d28d9;
        border-bottom: 2px solid #8b5cf6;
    }

    .list-group-item {
        background: white;
        border: 1px solid #e0d7ff;
        color: #374151;
    }

    /* Form Check */
    .form-check-input {
        background: white;
        border: 2px solid #d4c2ff;
    }

    .form-check-input:checked {
        background-color: #8b5cf6;
        border-color: #8b5cf6;
    }

    .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        border-color: #8b5cf6;
    }

    .form-check-input:disabled {
        background: #f3f4f6;
        border-color: #e5e7eb;
    }

    /* Select2 Customization */
    .select2-container--default .select2-selection--single {
        background: white;
        border: 1px solid #e0d7ff;
        border-radius: 10px;
        height: 44px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1f2937;
        line-height: 44px;
        padding-left: 16px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }

    .select2-dropdown {
        background: white;
        border: 1px solid #e0d7ff;
    }

    .select2-results__option {
        color: #1f2937;
    }

    .select2-results__option--highlighted {
        background: #f5f0ff !important;
        color: #6d28d9 !important;
    }

    /* Responsive - PERFECT ALIGNMENT */
    @media (max-width: 1400px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 1200px) {
        .employee-dashboard {
            padding: 25px 30px;
        }
    }

    @media (max-width: 992px) {
        .employee-dashboard {
            padding: 20px 25px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-buttons {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .employee-dashboard {
            padding: 15px 20px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .table thead {
            display: none;
        }

        .employee-list-table tbody tr {
            display: block;
            height: auto;
            margin-bottom: 14px;
            border: 1px solid rgba(167, 139, 250, 0.2);
            border-radius: 16px;
            padding: 14px 16px;
        }

        .employee-list-table tbody td {
            display: block;
            padding: 9px 0 9px 45%;
            border: none;
            position: relative;
        }

        .employee-list-table tbody td:before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            width: 40%;
            font-weight: 600;
            color: #5b4b7a;
        }

        .table-footer {
            flex-direction: column;
            align-items: center;
        }

        .footer-left, .footer-center, .footer-right {
            width: 100%;
            justify-content: center;
        }
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f5f0ff;
    }

    ::-webkit-scrollbar-thumb {
        background: #d4c2ff;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #b79aff;
    }

    /* Final employee action polish */
    .employee-dashboard .action-buttons {
        align-items: center !important;
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
        justify-content: flex-end !important;
        margin-left: auto !important;
        overflow: visible !important;
    }

    .employee-dashboard .quick-actions {
        display: inline-flex !important;
        position: relative !important;
        top: auto !important;
        z-index: 25 !important;
    }

    .employee-dashboard .quick-actions .dropdown {
        display: inline-flex !important;
        position: relative !important;
        z-index: 30 !important;
    }

    .employee-dashboard .quick-actions .dropdown-menu {
        left: auto !important;
        margin-top: 0.45rem !important;
        min-width: 210px !important;
        right: 0 !important;
        transform: none !important;
        z-index: 4000 !important;
    }

    .employee-dashboard a.btn.btn-add,
    .employee-dashboard button.btn.btn-invite {
        background: linear-gradient(135deg, #0f744c, #15885a) !important;
        border-color: #0f744c !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .employee-dashboard a.btn.btn-add i,
    .employee-dashboard a.btn.btn-add span,
    .employee-dashboard button.btn.btn-invite i,
    .employee-dashboard button.btn.btn-invite span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        opacity: 1 !important;
    }

    .employee-dashboard .btn-archive-employees {
        border: 1px solid rgba(15, 116, 76, 0.18) !important;
        background: #ffffff !important;
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
        box-shadow: 0 10px 20px rgba(22, 39, 30, 0.07) !important;
    }

    .employee-dashboard .btn-archive-employees i,
    .employee-dashboard .btn-archive-employees span {
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
        font-weight: 900 !important;
    }

    .employee-dashboard .btn-archive-employees:hover {
        border-color: #0f744c !important;
        background: #edf8f2 !important;
        transform: translateY(-2px) !important;
    }

    .employee-dashboard .quick-actions .dropdown-toggle {
        align-items: center !important;
        display: inline-flex !important;
        justify-content: center !important;
        min-height: 42px !important;
        min-width: 150px !important;
        padding: 0.54rem 0.9rem !important;
        font-size: 0.84rem !important;
        border-radius: 999px !important;
        white-space: nowrap !important;
    }

    .employee-dashboard .btn-archive-employees,
    .employee-dashboard a.btn.btn-add,
    .employee-dashboard button.btn.btn-invite {
        align-items: center !important;
        display: inline-flex !important;
        justify-content: center !important;
        min-height: 42px !important;
        white-space: nowrap !important;
    }

    @media (max-width: 991.98px) {
        .employee-dashboard .action-buttons {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            justify-content: stretch !important;
            margin-left: 0 !important;
            width: 100% !important;
        }

        .employee-dashboard .quick-actions,
        .employee-dashboard .quick-actions .dropdown,
        .employee-dashboard .quick-actions .dropdown-toggle,
        .employee-dashboard .btn-archive-employees,
        .employee-dashboard a.btn.btn-add,
        .employee-dashboard button.btn.btn-invite {
            min-width: 0 !important;
            width: 100% !important;
        }
    }

    @media (max-width: 575.98px) {
        .employee-dashboard .action-buttons {
            grid-template-columns: 1fr !important;
        }
    }

    .employee-dashboard #bulkDeleteTrigger {
        min-height: 32px !important;
        padding: 0.42rem 0.65rem !important;
        font-size: 0.82rem !important;
        border-radius: 10px !important;
    }

    /* Filter Employees card polish */
    .employee-dashboard .employee-filter-polish {
        border: 1px solid rgba(15, 116, 76, 0.12) !important;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 248, 0.96)) !important;
        box-shadow: 0 18px 42px rgba(22, 39, 30, 0.09) !important;
        overflow: visible !important;
    }

    .employee-dashboard .employee-filter-polish .filter-header {
        border-bottom: 1px solid rgba(15, 116, 76, 0.1) !important;
        background: linear-gradient(135deg, rgba(15, 116, 76, 0.09), rgba(255, 255, 255, 0.82)) !important;
    }

    .employee-dashboard .employee-filter-polish .header-icon-wrapper {
        width: 48px !important;
        height: 48px !important;
        border-radius: 15px !important;
        background: linear-gradient(135deg, #0f744c, #1a9a68) !important;
        color: #ffffff !important;
        box-shadow: 0 12px 22px rgba(15, 116, 76, 0.22) !important;
    }

    .employee-dashboard .employee-filter-polish .header-icon-wrapper i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-size: 1.08rem !important;
    }

    .employee-dashboard .employee-filter-polish .header-text h3 {
        color: #0a1f16 !important;
        -webkit-text-fill-color: #0a1f16 !important;
        font-size: 1.18rem !important;
        font-weight: 900 !important;
        letter-spacing: 0 !important;
        margin-bottom: 0.16rem !important;
    }

    .employee-dashboard .employee-filter-polish .header-text p {
        color: #53645b !important;
        -webkit-text-fill-color: #53645b !important;
        font-size: 0.9rem !important;
        font-weight: 700 !important;
    }

    .employee-dashboard .employee-filter-polish .header-right {
        align-items: stretch !important;
    }

    .employee-dashboard .employee-filter-polish .selected-badge {
        min-height: 38px !important;
        align-items: center !important;
        border: 1px solid rgba(15, 116, 76, 0.16) !important;
        background: #edf8f2 !important;
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
        font-weight: 900 !important;
        padding: 0.48rem 0.78rem !important;
        white-space: nowrap !important;
    }

    .employee-dashboard .employee-filter-polish .btn-export-all,
    .employee-dashboard .employee-filter-polish .btn-apply {
        min-height: 42px !important;
        border: 1px solid #0f744c !important;
        background: linear-gradient(135deg, #0f744c, #188b5e) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 900 !important;
        padding: 0.58rem 1rem !important;
        box-shadow: 0 12px 22px rgba(15, 116, 76, 0.16) !important;
    }

    .employee-dashboard .employee-filter-polish .btn-export-all *,
    .employee-dashboard .employee-filter-polish .btn-apply * {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .employee-dashboard .employee-filter-polish .btn-group .btn {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        padding: 0 !important;
        border-radius: 12px !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid rgba(15, 116, 76, 0.14) !important;
        background: #ffffff !important;
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
        box-shadow: 0 8px 16px rgba(22, 39, 30, 0.06) !important;
    }

    .employee-dashboard .employee-filter-polish .btn-group .btn:disabled {
        opacity: 0.65 !important;
        color: #7f8d85 !important;
        -webkit-text-fill-color: #7f8d85 !important;
    }

    .employee-dashboard .employee-filter-polish .filter-body {
        background: rgba(255, 255, 255, 0.78) !important;
    }

    .employee-dashboard .employee-filter-polish .filter-grid {
        align-items: end !important;
    }

    .employee-dashboard .employee-filter-polish .filter-item {
        min-width: 0 !important;
    }

    .employee-dashboard .employee-filter-polish .filter-label {
        display: flex !important;
        align-items: center !important;
        gap: 0.35rem !important;
        color: #13241b !important;
        -webkit-text-fill-color: #13241b !important;
        font-size: 0.86rem !important;
        font-weight: 900 !important;
        margin-bottom: 0.5rem !important;
    }

    .employee-dashboard .employee-filter-polish .filter-label i {
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
    }

    .employee-dashboard .employee-filter-polish .form-select,
    .employee-dashboard .employee-filter-polish .select2-container--default .select2-selection--single {
        min-height: 46px !important;
        border: 1px solid rgba(15, 116, 76, 0.18) !important;
        border-radius: 14px !important;
        background: #ffffff !important;
        color: #111c16 !important;
        -webkit-text-fill-color: #111c16 !important;
        box-shadow: 0 8px 18px rgba(22, 39, 30, 0.05) !important;
    }

    .employee-dashboard .employee-filter-polish .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111c16 !important;
        -webkit-text-fill-color: #111c16 !important;
        font-weight: 800 !important;
        line-height: 44px !important;
        padding-left: 14px !important;
        padding-right: 34px !important;
    }

    .employee-dashboard .employee-filter-polish .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        right: 8px !important;
    }

    .employee-dashboard .employee-filter-polish .btn-reset {
        min-height: 42px !important;
        border: 1px solid rgba(15, 116, 76, 0.16) !important;
        background: #ffffff !important;
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
        font-weight: 900 !important;
        padding: 0.58rem 0.95rem !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish {
        border-color: rgba(122, 240, 181, 0.22) !important;
        background: linear-gradient(180deg, #102119, #0c1913) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .filter-header {
        border-color: rgba(122, 240, 181, 0.18) !important;
        background: linear-gradient(135deg, rgba(64, 212, 140, 0.14), rgba(16, 33, 25, 0.96)) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .header-text h3,
    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .filter-label {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .header-text p {
        color: #d9f1e4 !important;
        -webkit-text-fill-color: #d9f1e4 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .filter-body {
        background: rgba(16, 33, 25, 0.82) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .form-select,
    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .select2-container--default .select2-selection--single {
        border-color: rgba(122, 240, 181, 0.2) !important;
        background: #183026 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    @media (max-width: 640px) {
        .employee-dashboard .employee-filter-polish .header-right,
        .employee-dashboard .employee-filter-polish .btn-export-all,
        .employee-dashboard .employee-filter-polish .btn-apply,
        .employee-dashboard .employee-filter-polish .btn-reset {
            width: 100% !important;
        }

        .employee-dashboard .employee-filter-polish .btn-group {
            width: 100% !important;
            justify-content: space-between !important;
        }

        .employee-dashboard .employee-filter-polish .btn-group .btn {
            flex: 1 1 40px !important;
        }
    }

    /* Exact filter body section polish */
    .employee-dashboard .employee-filter-body {
        border-top: 1px solid rgba(15, 116, 76, 0.08) !important;
        background:
            linear-gradient(135deg, rgba(15, 116, 76, 0.035), rgba(255, 255, 255, 0.86)),
            #ffffff !important;
    }

    .employee-dashboard .employee-filter-grid {
        align-items: stretch !important;
    }

    .employee-dashboard .employee-filter-field {
        display: flex !important;
        min-width: 0 !important;
        min-height: 112px !important;
        flex-direction: column !important;
        justify-content: flex-end !important;
        border: 1px solid rgba(15, 116, 76, 0.12) !important;
        border-radius: 18px !important;
        background: rgba(255, 255, 255, 0.92) !important;
        padding: 0.9rem !important;
        box-shadow: 0 10px 24px rgba(22, 39, 30, 0.055) !important;
    }

    .employee-dashboard .employee-filter-field .filter-label {
        display: inline-flex !important;
        width: 100% !important;
        align-items: center !important;
        gap: 0.48rem !important;
        color: #0a1f16 !important;
        -webkit-text-fill-color: #0a1f16 !important;
        font-size: 0.9rem !important;
        font-weight: 950 !important;
        line-height: 1.2 !important;
        margin: 0 0 0.68rem !important;
        white-space: normal !important;
    }

    .employee-dashboard .employee-filter-field .filter-label i {
        display: inline-flex !important;
        width: 28px !important;
        height: 28px !important;
        flex: 0 0 28px !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 10px !important;
        background: #e7f5ee !important;
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
        font-size: 0.82rem !important;
        margin-right: 0 !important;
    }

    .employee-dashboard .employee-filter-field .form-select,
    .employee-dashboard .employee-filter-field .select2-container {
        width: 100% !important;
        max-width: 100% !important;
    }

    .employee-dashboard .employee-filter-field .form-select,
    .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single {
        height: 48px !important;
        min-height: 48px !important;
        border: 1px solid rgba(15, 116, 76, 0.18) !important;
        border-radius: 14px !important;
        background: #f9fcfa !important;
        color: #08150f !important;
        -webkit-text-fill-color: #08150f !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9), 0 8px 18px rgba(22, 39, 30, 0.045) !important;
    }

    .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single .select2-selection__rendered {
        overflow: hidden !important;
        color: #08150f !important;
        -webkit-text-fill-color: #08150f !important;
        font-size: 0.92rem !important;
        font-weight: 850 !important;
        line-height: 46px !important;
        padding-left: 14px !important;
        padding-right: 38px !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #637268 !important;
        -webkit-text-fill-color: #637268 !important;
    }

    .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 9px !important;
    }

    .employee-dashboard .employee-filter-field:focus-within {
        border-color: rgba(15, 116, 76, 0.34) !important;
        box-shadow: 0 14px 30px rgba(15, 116, 76, 0.1) !important;
    }

    .employee-dashboard .employee-filter-actions {
        min-height: 112px !important;
        align-self: stretch !important;
        border: 1px solid rgba(15, 116, 76, 0.12) !important;
        border-radius: 18px !important;
        background: linear-gradient(135deg, #f3faf6, #ffffff) !important;
        padding: 0.9rem !important;
        box-shadow: 0 10px 24px rgba(22, 39, 30, 0.055) !important;
    }

    .employee-dashboard .employee-filter-actions .btn {
        min-height: 48px !important;
        flex: 1 1 auto !important;
        border-radius: 14px !important;
        font-size: 0.9rem !important;
        font-weight: 950 !important;
        line-height: 1.1 !important;
        white-space: nowrap !important;
    }

    .employee-dashboard .employee-filter-actions .btn-apply {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .employee-dashboard .employee-filter-actions .btn-apply i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .employee-dashboard .employee-filter-actions .btn-reset i {
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-body {
        border-top-color: rgba(122, 240, 181, 0.14) !important;
        background: linear-gradient(135deg, rgba(64, 212, 140, 0.06), rgba(16, 33, 25, 0.92)) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field,
    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-actions {
        border-color: rgba(122, 240, 181, 0.16) !important;
        background: #102119 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field .filter-label {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field .filter-label i {
        background: rgba(64, 212, 140, 0.16) !important;
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field .form-select,
    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single {
        border-color: rgba(122, 240, 181, 0.2) !important;
        background: #183026 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees {
        border-color: rgba(122, 240, 181, 0.24) !important;
        background: #183026 !important;
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees i,
    html[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees span {
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
    }

    @media (max-width: 640px) {
        .employee-dashboard .employee-filter-field,
        .employee-dashboard .employee-filter-actions {
            min-height: auto !important;
            padding: 0.82rem !important;
        }

        .employee-dashboard .employee-filter-actions .btn {
            width: 100% !important;
        }
    }

    /* Final employee index card polish */
    .employee-dashboard {
        background: linear-gradient(145deg, #f7fbf9 0%, #eef7f2 52%, #fafdfb 100%) !important;
    }

    .employee-dashboard .breadcrumb-wrapper,
    .employee-dashboard .header-section,
    .employee-dashboard .stat-card,
    .employee-dashboard .filter-export-card,
    .employee-dashboard .table-card,
    .employee-dashboard .employee-filter-polish {
        border: 1px solid rgba(15, 116, 76, 0.12) !important;
        box-shadow: 0 18px 44px rgba(22, 39, 30, 0.08) !important;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .employee-dashboard .header-section {
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(241, 250, 245, 0.94)) !important;
        overflow: visible !important;
    }

    .employee-dashboard .icon-circle,
    .employee-dashboard .stat-icon {
        background: linear-gradient(135deg, #0f744c, #188b5e) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 12px 24px rgba(15, 116, 76, 0.2) !important;
    }

    .employee-dashboard .icon-circle i,
    .employee-dashboard .stat-icon i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .employee-dashboard .header-section h1,
    .employee-dashboard .stat-value,
    .employee-dashboard .table-card h3,
    .employee-dashboard .employee-name,
    .employee-dashboard .table strong {
        color: #07130d !important;
        -webkit-text-fill-color: #07130d !important;
        letter-spacing: 0 !important;
    }

    .employee-dashboard .subtitle,
    .employee-dashboard .stat-label,
    .employee-dashboard .employee-email,
    .employee-dashboard .table td {
        color: #52645a !important;
        -webkit-text-fill-color: #52645a !important;
    }

    .employee-dashboard .stat-card {
        border-radius: 20px !important;
        background: linear-gradient(135deg, #ffffff, #f8fcfa) !important;
        overflow: hidden;
    }

    .employee-dashboard .stat-card:hover,
    .employee-dashboard .table-card:hover,
    .employee-dashboard .employee-filter-polish:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 52px rgba(22, 39, 30, 0.11) !important;
    }

    .employee-dashboard .table-card {
        border-radius: 24px !important;
        background: rgba(255, 255, 255, 0.98) !important;
        overflow: hidden;
    }

    .employee-dashboard .table thead th {
        background: #f4faf6 !important;
        color: #07130d !important;
        -webkit-text-fill-color: #07130d !important;
        border-bottom: 1px solid rgba(15, 116, 76, 0.12) !important;
        letter-spacing: 0 !important;
    }

    .employee-dashboard .table tbody tr {
        transition: background 0.18s ease, transform 0.18s ease;
    }

    .employee-dashboard .table tbody tr:hover {
        background: #f5fbf7 !important;
    }

    .employee-dashboard .employee-list-table {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
    }

    .employee-dashboard .employee-list-table tbody tr {
        height: auto !important;
        margin: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        transform: none !important;
    }

    .employee-dashboard .employee-list-table thead th {
        padding: 14px 16px !important;
        vertical-align: middle !important;
    }

    .employee-dashboard .employee-list-table tbody td {
        padding: 10px 16px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid rgba(15, 116, 76, 0.08) !important;
    }

    .employee-dashboard .employee-list-table tbody td:first-child,
    .employee-dashboard .employee-list-table tbody td:last-child {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    .employee-dashboard .employee-list-table .employee-info {
        min-height: 52px !important;
    }

    .employee-dashboard .employee-avatar,
    .employee-dashboard .avatar,
    .employee-dashboard img.rounded-circle {
        border: 2px solid #ffffff !important;
        box-shadow: 0 8px 16px rgba(22, 39, 30, 0.12) !important;
    }

    .employee-dashboard .btn,
    .employee-dashboard .action-btn {
        border-radius: 12px !important;
        font-weight: 850 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard {
        background: linear-gradient(145deg, #07130d, #102119) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .header-section,
    html[data-pms-theme="dark"] .employee-dashboard .stat-card,
    html[data-pms-theme="dark"] .employee-dashboard .table-card {
        background: #102119 !important;
        border-color: rgba(122, 240, 181, 0.18) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .header-section h1,
    html[data-pms-theme="dark"] .employee-dashboard .stat-value,
    html[data-pms-theme="dark"] .employee-dashboard .table-card h3,
    html[data-pms-theme="dark"] .employee-dashboard .table strong {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .subtitle,
    html[data-pms-theme="dark"] .employee-dashboard .stat-label,
    html[data-pms-theme="dark"] .employee-dashboard .table td {
        color: #d9f1e4 !important;
        -webkit-text-fill-color: #d9f1e4 !important;
    }

    /* ===== Employee list row gap: same distance for every row ===== */
    @media (min-width: 769px) {
        .employee-dashboard .employee-list-table {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: max-content !important;
            min-width: 1200px !important;
        }

        .employee-dashboard .employee-list-table thead tr,
        .employee-dashboard .employee-list-table tbody tr {
            display: table-row !important;
        }

        .employee-dashboard .employee-list-table thead th {
            padding: 13px 16px !important;
            border-bottom: 1px solid rgba(15, 116, 76, 0.1) !important;
        }

        .employee-dashboard .employee-list-table tbody td {
            height: 72px !important;
            padding: 10px 16px !important;
            background: #ffffff !important;
            border-top: 1px solid rgba(15, 116, 76, 0.06) !important;
            border-bottom: 1px solid rgba(15, 116, 76, 0.06) !important;
            vertical-align: middle !important;
        }

        .employee-dashboard .employee-list-table thead th:nth-child(2),
        .employee-dashboard .employee-list-table tbody td:nth-child(2) {
            width: 140px !important;
            min-width: 140px !important;
            max-width: 140px !important;
            padding-left: 14px !important;
            padding-right: 14px !important;
            text-align: left !important;
        }

        .employee-dashboard .employee-list-table .employee-id-badge {
            max-width: 100% !important;
            overflow: visible !important;
            text-overflow: clip !important;
            white-space: nowrap !important;
        }

        .employee-dashboard .employee-list-table thead th:nth-child(3),
        .employee-dashboard .employee-list-table tbody td:nth-child(3) {
            width: 300px !important;
            min-width: 300px !important;
            max-width: 300px !important;
            padding-right: 10px !important;
        }

        .employee-dashboard .employee-list-table thead th:nth-child(4),
        .employee-dashboard .employee-list-table tbody td:nth-child(4) {
            width: 190px !important;
            min-width: 190px !important;
            max-width: 190px !important;
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .employee-dashboard .employee-list-table thead th:nth-child(5),
        .employee-dashboard .employee-list-table tbody td:nth-child(5) {
            width: 210px !important;
            min-width: 210px !important;
            max-width: 210px !important;
            padding-left: 10px !important;
        }

        .employee-dashboard .employee-list-table tbody td:first-child {
            border-left: 1px solid rgba(15, 116, 76, 0.06) !important;
            border-radius: 14px 0 0 14px !important;
            padding-left: 18px !important;
            padding-right: 10px !important;
        }

        .employee-dashboard .employee-list-table tbody td:last-child {
            border-right: 1px solid rgba(15, 116, 76, 0.06) !important;
            border-radius: 0 14px 14px 0 !important;
            padding-left: 10px !important;
            padding-right: 18px !important;
        }

        .employee-dashboard .employee-list-table .employee-info {
            min-height: 48px !important;
        }
    }
</style>

{{-- JavaScript - COMPLETELY UNCHANGED FUNCTIONALITY --}}
@push('js')
<script>
$(document).ready(function () {
    // ===== ALL EXISTING FUNCTIONALITY - 100% UNCHANGED =====
    // Store selected employee data
    let selectedEmployees = [];
    let showEntries = localStorage.getItem('employeeShowEntries') || '10';

    // Set initial show entries value
    $('#showEntries').val(showEntries);

    // Show entries change
    $('#showEntries').on('change', function() {
        const value = $(this).val();
        localStorage.setItem('employeeShowEntries', value);
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        window.location.href = url.toString();
    });

    // Select all checkboxes
    $('#selectAll').on('click', function() {
        const isChecked = $(this).prop('checked');
        const enabledCheckboxes = $('.employee-checkbox:not(:disabled)');
        enabledCheckboxes.prop('checked', isChecked).trigger('change');
    });

    // Individual checkbox change
    $(document).on('change', '.employee-checkbox', function() {
        updateSelectedEmployees();
        updateSelectAllCheckbox();
    });

    // Row click to select
    $(document).on('click', 'tbody tr', function(e) {
        if ($(e.target).is('input[type="checkbox"]') ||
            $(e.target).closest('.dropdown, a, button, form').length ||
            $(e.target).is('a, button, .dropdown-item, img, i')) {
            return;
        }
        const checkbox = $(this).find('.employee-checkbox:not(:disabled)');
        if (checkbox.length) {
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });

    // Update select all checkbox
    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.employee-checkbox:not(:disabled)').length;
        const checkedCheckboxes = $('.employee-checkbox:checked:not(:disabled)').length;
        const selectAll = $('#selectAll');

        if (totalCheckboxes === 0) {
            selectAll.prop('checked', false).prop('indeterminate', false);
            return;
        }

        if (checkedCheckboxes === 0) {
            selectAll.prop('checked', false).prop('indeterminate', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            selectAll.prop('checked', true).prop('indeterminate', false);
        } else {
            selectAll.prop('checked', false).prop('indeterminate', true);
        }
    }

    // Update selected employees
    function updateSelectedEmployees() {
        selectedEmployees = [];
        $('.employee-checkbox:checked:not(:disabled)').each(function() {
            selectedEmployees.push({
                id: $(this).val(),
                data: {
                    employee_id: $(this).data('employee-id') || '-',
                    name: cleanHTML($(this).data('name')) || '-',
                    email: $(this).data('email') || '-',
                    company: cleanHTML($(this).data('company')) || '-',
                    designation: cleanHTML($(this).data('designation')) || '-',
                    reporting_to: cleanHTML($(this).data('reporting-to')) || 'N/A',
                    status: $(this).data('status') || 'N/A'
                }
            });
        });

        const selectedCount = selectedEmployees.length;
        $('#export-selected-count').text(selectedCount + ' selected');
        $('#bulk-selected-count').text(selectedCount + ' selected');

        const exportButtons = ['#export-copy', '#export-csv', '#export-excel', '#export-pdf', '#export-print'];
        exportButtons.forEach(btn => {
            $(btn).prop('disabled', selectedCount === 0);
        });

        $('#btn-bulk-delete').prop('disabled', selectedCount === 0);
        $('tbody tr').removeClass('selected');
        $('.employee-checkbox:checked:not(:disabled)').closest('tr').addClass('selected');
    }

    // Clean HTML function
    function cleanHTML(text) {
        if (!text) return '';
        return String(text).replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
    }

    // Export functions
    function getDataForExport(exportAll = false) {
        const data = [];
        const headers = ['Employee ID', 'Name', 'Email', 'Company', 'Designation', 'Reporting To', 'Status'];
        data.push(headers);

        if (exportAll) {
            $('.employee-checkbox:not(:disabled)').each(function() {
                data.push([
                    $(this).data('employee-id') || '-',
                    cleanHTML($(this).data('name')) || '-',
                    $(this).data('email') || '-',
                    cleanHTML($(this).data('company')) || '-',
                    cleanHTML($(this).data('designation')) || '-',
                    cleanHTML($(this).data('reporting-to')) || 'N/A',
                    $(this).data('status') || 'N/A'
                ]);
            });
        } else {
            selectedEmployees.forEach(emp => {
                data.push([
                    emp.data.employee_id,
                    emp.data.name,
                    emp.data.email,
                    emp.data.company,
                    emp.data.designation,
                    emp.data.reporting_to,
                    emp.data.status
                ]);
            });
        }
        return data;
    }

    // Copy
    $('#export-copy').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to copy.');
            return;
        }
        const data = getDataForExport(false);
        const csvContent = data.map(row =>
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join('\t')
        ).join('\n');
        navigator.clipboard.writeText(csvContent).then(() => {
            alert('Selected rows copied to clipboard!');
        });
    });

    // CSV
    $('#export-csv').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }
        const data = getDataForExport(false);
        const csvContent = data.map(row =>
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
        ).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `employees_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
    });

    // Excel
    $('#export-excel').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }
        const data = getDataForExport(false);
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Employees");
        XLSX.writeFile(wb, `employees_${new Date().toISOString().split('T')[0]}.xlsx`);
    });

    // PDF
    $('#export-pdf').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }
        const data = getDataForExport(false);
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.text('Employee List', 14, 15);
        doc.autoTable({
            head: [data[0]],
            body: data.slice(1),
            startY: 30,
            theme: 'grid',
            headStyles: { fillColor: [124, 58, 237] }
        });
        doc.save(`employees_${new Date().toISOString().split('T')[0]}.pdf`);
    });

    // Print
    $('#export-print').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to print.');
            return;
        }
        window.print();
    });

    // Export all
    $('#export-all').on('click', function() {
        const totalEmployees = $('.employee-checkbox:not(:disabled)').length;
        if (totalEmployees === 0) {
            alert('No employees available to export.');
            return;
        }
        if (!confirm(`Export ALL ${totalEmployees} employees to Excel?`)) return;
        const data = getDataForExport(true);
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "All Employees");
        XLSX.writeFile(wb, `all_employees_${new Date().toISOString().split('T')[0]}.xlsx`);
    });

    // Bulk archive inactive employees
    $('#btn-bulk-delete, #bulkDeleteTrigger').on('click', function() {
        let inactiveSelection = selectedEmployees.filter(emp => emp.data.status === 'Inactive');
        let selectedIds = inactiveSelection.map(emp => emp.id);
        let skippedCount = selectedEmployees.length - selectedIds.length;

        if (!selectedEmployees.length) {
            alert('Please select at least one employee.');
            return;
        }

        if (!selectedIds.length) {
            alert('Only inactive employees can be archived. Please select inactive employees only.');
            return;
        }

        const skipText = skippedCount > 0 ? ` ${skippedCount} active/non-inactive employee(s) will be skipped.` : '';
        if (!confirm(`Archive ${selectedIds.length} inactive employee(s)? Their data will move to Archived Employees and can be restored.${skipText}`)) return;

        $.ajax({
            url: '{{ route("employees.bulk.delete") }}',
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
                employee_ids: selectedIds
            },
            success: function(response) {
                alert(response?.message || 'Employees archived successfully');
                location.reload();
            },
            error: function(xhr) {
                alert(xhr?.responseJSON?.message || 'Failed to archive employees.');
            }
        });
    });

    // Invite by email
    $('#inviteEmailForm').on('submit', function(e) {
        e.preventDefault();
        const email = $('#inviteEmail').val().trim();
        const message = $('#inviteMessage').val().trim();

        $.ajax({
            url: '{{ route("employees.sendInvite") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email: email,
                message: message
            },
            success: function(res) {
                alert('Invitation sent successfully!');
                $('#inviteModal').modal('hide');
                $('#inviteEmailForm')[0].reset();
            },
            error: function(xhr) {
                alert(xhr?.responseJSON?.message || 'Failed to send invite.');
            }
        });
    });

    // Generate invite link
    $('#createLinkBtn').on('click', function() {
        $('#linkContainer').hide();
        $('#inviteLink').val('');
        alert('Public registration is disabled. Please create employee accounts from the employee management form.');
    });

    // Copy link
    $('#copyLinkBtn').on('click', function() {
        $('#inviteLink').select();
        document.execCommand('copy');
        alert('Link copied to clipboard!');
    });

    // Share link
    $('#shareLinkBtn').on('click', function() {
        const link = $('#inviteLink').val();
        window.location.href = `mailto:?subject=Join Xinksoft Technologies&body=${encodeURIComponent(link)}`;
    });

    // Blocked delete
    $(document).on('click', '.blocked-delete-btn', function (e) {
        e.preventDefault();
        const name = $(this).data('employee-name');
        const id = $(this).data('employee-id');
        const subordinateCount = $(this).data('subordinate-count');
        $('#blockedEmployeeName').text(name);
        $('#blockedEmployeeReason').text(`Cannot archive because ${subordinateCount} team member(s) report to this employee.`);
        $('#blocked-view-subordinates').attr('href', '{{ url("/") }}/employees/' + id);
        new bootstrap.Modal(document.getElementById('blockedDeleteModal')).show();
    });

    // Initialize
    updateSelectedEmployees();
    updateSelectAllCheckbox();

    // Mobile labels
    if ($(window).width() < 768) {
        $('#employeeTable thead th').each(function(index) {
            const headerText = $(this).text().trim();
            $('#employeeTable tbody tr').each(function() {
                $(this).find('td').eq(index).attr('data-label', headerText);
            });
        });
    }
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
@endpush

@endsection
