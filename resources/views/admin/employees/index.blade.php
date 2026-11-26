@extends('admin.layout.app')

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
<div id="employeeDirectoryPage" class="employee-dashboard employee-directory-modern">

    {{-- Top Utility Breadcrumb (aligned opposite global Back link) --}}
    <div class="emp-breadcrumb-row">
        <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">
                            <i class="bx bx-home-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-users me-1"></i> Employees
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    @php
        // Calculate employee counts for statistics - FUNCTIONALITY UNCHANGED
        $totalEmployees = $companyEmployeeTotal ?? $employees->count();
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

        $emptyCompanyName = ($selectedCompanyId ? $companies->firstWhere('id', $selectedCompanyId)?->name : null)
            ?: (session('current_company_name') ?: (auth()->user()?->company?->name ?? config('app.name')));
    @endphp

    {{-- Header Section --}}
    <div class="header-section">
        <div class="header-content">
            <div class="title-wrapper">
                <div class="icon-circle">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h1>Employee Management</h1>
                    <p class="subtitle">Manage all employees, their details, and permissions</p>
                </div>
            </div>
            <div class="action-buttons">
                <!-- Quick Actions Dropdown -->
                <div class="quick-actions">
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            <i class="fas fa-bolt"></i><span>Quick Actions</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('employees.archive') }}"><i class="fas fa-box-archive me-2 text-primary"></i>Archived Employees</a></li>
                            <li><a class="dropdown-item" href="#" id="bulkDeleteTrigger"><i class="fas fa-box-archive me-2 text-danger"></i>Archive Inactive</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Archived Employees Button -->
                <a href="{{ route('employees.archive') }}" class="btn btn-archive-employees">
                    <i class="fas fa-box-archive"></i>
                    <span class="btn-archive-text">Archived</span>
                </a>

                <!-- Add Employee Button -->
                <a href="{{ route('employees.create') }}" class="btn btn-add">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Employee</span>
                </a>

                <!-- Invite Button -->
                <button type="button" class="btn btn-invite" data-bs-toggle="modal" data-bs-target="#inviteModal">
                    <i class="fas fa-envelope"></i>
                    <span>Invite</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Cards - 4 Cards --}}
    <div class="stats-grid">
        <!-- Total Employees Card -->
        <div class="stat-card total">
            <div class="stat-content">
                <span class="stat-label">Total Employees</span>
                <span class="stat-value">{{ $totalEmployees }}</span>
                <span class="stat-meta">Headcount in organization</span>
            </div>
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <!-- Active Employees Card -->
        <div class="stat-card active">
            <div class="stat-content">
                <span class="stat-label">Active</span>
                <span class="stat-value">{{ $activeEmployees }}</span>
                <span class="stat-meta stat-meta-active"><i class="far fa-check-circle"></i> Currently operational</span>
            </div>
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
        </div>

        <!-- Inactive Employees Card -->
        <div class="stat-card inactive">
            <div class="stat-content">
                <span class="stat-label">Inactive</span>
                <span class="stat-value">{{ $inactiveEmployees }}</span>
                <span class="stat-meta">Deactivated or suspended</span>
            </div>
            <div class="stat-icon">
                <i class="fas fa-user-slash"></i>
            </div>
        </div>

        <!-- On Notice Card -->
        <div class="stat-card notice">
            <div class="stat-content">
                <span class="stat-label">On Notice</span>
                <span class="stat-value">{{ $noticeEmployees }}</span>
                <span class="stat-meta">Serving notice period</span>
            </div>
            <div class="stat-icon">
                <i class="far fa-clock"></i>
            </div>
        </div>
    </div>

    @if(\App\Services\TenantScope::isPlatformAdmin())
    {{-- Company Selector - SaaS company workspace filter (platform Super Admin only; company users see only their own company) --}}
    <div class="company-switcher-grid !grid !grid-cols-1 !gap-3 sm:!grid-cols-2 lg:!grid-cols-3 2xl:!grid-cols-6 !mb-5" style="position: relative !important; z-index: 1 !important;">
        <a href="{{ route('employees.index', request()->except(['company_id', 'page'])) }}"
           class="company-switch-card {{ empty($selectedCompanyId) ? 'active' : '' }}">
            <span class="company-switch-icon" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">
                <i class="fas fa-layer-group" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;"></i>
            </span>
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
                <span class="company-switch-icon" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">
                    @if($company->logo)
                        <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}">
                    @else
                        <span style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">{{ strtoupper(substr($company->short_name ?: $company->company_code ?: $company->name, 0, 2)) }}</span>
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
                        Invite to {{ session('current_company_name') ?: (auth()->user()?->company?->name ?? config('app.name')) }}
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
                                        <input type="email" name="email" id="inviteEmail" class="form-control" placeholder="e.g. name@example.com" autocomplete="email" required>
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
                                Create a signed link for one employee. It expires in 7 days and can be used once. Email delivery is not required.
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold" for="inviteLinkEmail">Employee Email Address *</label>
                                <input type="email" class="form-control" id="inviteLinkEmail" placeholder="name@example.com" autocomplete="email" required>
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
                                        <i class="fas fa-copy" aria-hidden="true"></i><span class="visually-hidden">Copy invitation link</span>
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

    {{-- Filter & Export Section --}}
    <div class="filter-export-card employee-filter-polish">
        <div class="filter-header">
            <div class="header-left">
                <div class="header-icon-wrapper">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div class="header-text">
                    <h3>Filter Employees</h3>
                    <p>Narrow down your employee list</p>
                </div>
            </div>
            <div class="header-right">
                <span id="export-selected-count" class="selected-badge">0 selected</span>
                <button type="button" class="btn btn-export-all" id="export-all">
                    <i class="fas fa-download"></i><span>Export All</span>
                </button>
                <div class="btn-group" role="group" aria-label="Export options">
                    <button type="button" class="btn btn-outline-secondary" id="export-copy" disabled title="Copy">
                        <i class="far fa-copy"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success" id="export-csv" disabled title="CSV">
                        <i class="far fa-file-alt"></i>
                    </button>
                    <button type="button" class="btn btn-outline-info" id="export-excel" disabled title="Excel">
                        <i class="far fa-file-excel"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="export-pdf" disabled title="PDF">
                        <i class="far fa-file-pdf"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="export-print" disabled title="Print">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="filter-body employee-filter-body">
            <form method="GET" action="{{ route('employees.index') }}" class="filter-form">
                <div class="filter-grid employee-filter-grid">
                    @if($selectedCompanyId)
                        <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}">
                    @endif
                    <!-- Employee ID -->
                    <div class="filter-item employee-filter-field">
                        <label class="filter-label">
                            <i class="far fa-check-circle"></i>Employee ID
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
                            <i class="fas fa-briefcase"></i>Designation
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
                            <i class="far fa-user"></i>Search Name/Email
                        </label>
                        <div class="emp-search-select-wrap">
                            <select name="user_id" class="form-select select2">
                                <option value="">Search Employee...</option>
                                @foreach($edOptions as $detail)
                                    <option value="{{ $detail->user_id }}" {{ request('user_id') == $detail->user_id ? 'selected' : '' }}>
                                        {{ $detail->user->name ?? 'N/A' }} - {{ $detail->user->email ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fas fa-search emp-search-select-icon" aria-hidden="true"></i>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="filter-actions employee-filter-actions">
                        <button type="submit" class="btn btn-apply">
                            <i class="fas fa-search"></i><span>Apply Filters</span>
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-reset">
                            <i class="fas fa-redo"></i><span>Reset</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <div class="title-icon-wrapper">
                    <i class="fas fa-list"></i>
                </div>
                <div class="title-text">
                    <h3>Employee List</h3>
                    <span class="employee-count">{{ $visibleEmployees->count() }} employees</span>
                </div>
            </div>
            <div class="table-actions">
                <button id="btn-bulk-delete" class="btn btn-bulk-delete is-awaiting-selection" aria-disabled="true" title="Select inactive employees to archive">
                    <i class="far fa-trash-alt"></i><span>Archive Inactive</span>
                </button>
            </div>
        </div>

        @if($visibleEmployees->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h5>No employees found</h5>
                <p>Get started by adding your first employee to {{ $emptyCompanyName }} or invite team members via corporate email.</p>
                <div class="empty-state-actions">
                    <a href="{{ route('employees.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i><span>Add First Employee</span>
                    </a>
                    <button type="button" class="btn btn-outline-secondary emp-empty-invite-btn" data-bs-toggle="modal" data-bs-target="#inviteModal">
                        <i class="far fa-envelope"></i><span>Send Invitations</span>
                    </button>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table id="employeeTable" data-pms-export="off" class="table table-hover align-middle mb-0 employee-list-table">
                    <thead>
                        <tr>
                            <th width="58" class="no-export" data-pms-selection-column aria-label="Select rows">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th width="170">Emp ID</th>
                            <th>Employee Name</th>
                            <th width="190">Company</th>
                            <th width="200">Email</th>
                            <th width="220">Role & Reporting</th>
                            <th width="120">Status</th>
                            <th width="80">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visibleEmployees as $employee)
                        <tr id="employee-row-{{ $employee->id }}"
                            class="@if(isset($employee->subordinate_count) && $employee->subordinate_count > 0) table-warning @endif">
                            <td class="no-export" data-pms-selection-column>
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
                                            <span class="profile-initials">{{ strtoupper(substr($employee->name ?? 'E', 0, 2)) }}</span>
                                        </div>
                                    @endif
                                    <div class="employee-details">
                                        <h6 class="employee-name">{{ $employee->name }}</h6>
                                        <span class="employee-designation" style="color: var(--emp-designation-color, #64748b) !important; -webkit-text-fill-color: var(--emp-designation-color, #64748b) !important;">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</span>
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
                                    <span class="employee-company-logo" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">
                                        @if($employee->company?->logo)
                                             <img src="{{ asset($employee->company->logo) }}" alt="{{ $employee->company->name }}">
                                        @else
                                            {{ strtoupper(substr($employee->company?->short_name ?: $employee->company?->company_code ?: $employee->company?->name ?: 'NA', 0, 2)) }}
                                        @endif
                                    </span>
                                    <span class="employee-company-name">{{ $employee->company?->name ?? 'Unassigned' }}</span>
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
                                    <span class="role-name" style="color: var(--emp-role-color, #1f2937) !important; -webkit-text-fill-color: var(--emp-role-color, #1f2937) !important;">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</span>
                                    <span class="reports-to" style="color: var(--emp-reports-color, #64748b) !important; -webkit-text-fill-color: var(--emp-reports-color, #64748b) !important;">
                                        <i class="fa-solid fa-users fas" style="color: #22D3EE !important; -webkit-text-fill-color: #22D3EE !important; font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free' !important; font-weight: 900 !important; font-style: normal !important; display: inline-block !important;"></i>
                                        {{ $employee->employeeDetail?->reportingTo?->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($employee->employeeDetail?->status === 'Active')
                                    <span class="status-badge status-active">
                                        <span class="status-dot"></span>
                                        <span class="status-text">Active</span>
                                    </span>
                                @elseif($employee->employeeDetail?->status === 'Inactive')
                                    <span class="status-badge status-inactive">
                                        <span class="status-dot"></span>
                                        <span class="status-text">Inactive</span>
                                    </span>
                                @else
                                    <span class="status-badge status-unknown"><span class="status-text">N/A</span></span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn-action-dots" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('employees.show', $employee->id) }}">
                                                <i class="fas fa-eye text-primary me-2"></i> View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ $employee->company_staff_role_id ? route('admin.upper-level-employees.index', ['edit' => $employee->id]) : route('employees.edit', $employee->id) }}">
                                                <i class="fas fa-edit text-info me-2"></i> Edit Profile
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-warning" onclick="openResetPasswordModal({{ $employee->id }}, '{{ addslashes($employee->name) }}')">
                                                <i class="fas fa-key me-2"></i> Reset Password
                                            </button>
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
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Pagination & Show Entries --}}
        @if(!$visibleEmployees->isEmpty())
        <div class="table-footer">
            <div class="footer-left">
                <div class="show-entries">
                    <span>Show</span>
                    <select class="form-select form-select-sm" id="showEntries">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="30" {{ request('per_page', 30) == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 100) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>entries</span>
                </div>
            </div>

            @if(method_exists($employees, 'total'))
            <div class="footer-center">
                <span class="pagination-info">
                    Showing {{ ($employees->currentPage() - 1) * $employees->perPage() + 1 }}
                    - {{ min($employees->currentPage() * $employees->perPage(), $employees->total()) }}
                    of {{ $employees->total() }}
                </span>
            </div>
            @endif

            @if(method_exists($employees, 'currentPage'))
            <div class="footer-right">
                <div class="pagination">
                    @if($employees->onFirstPage())
                        <button class="page-btn disabled" type="button" disabled>
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
                        <button class="page-btn disabled" type="button" disabled>
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

{{-- Modern SaaS Employee Directory Styles --}}
@include('admin.employees.partials.directory-styles')

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

        // Stays tappable with no selection so the button explains what to do (a disabled button gave no feedback on mobile).
        $('#btn-bulk-delete').toggleClass('is-awaiting-selection', selectedCount === 0).attr('aria-disabled', selectedCount === 0 ? 'true' : 'false');
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
        const pdfDocument = {
            pageSize: 'A4', pageOrientation: 'landscape',
            content: [{ text: 'Employee List', style: 'title' }, {
                table: { headerRows: 1, body: [data[0].map(text => ({ text, style: 'tableHeader' })), ...data.slice(1)] }
            }]
        };
        window.PmsPdfExports.styleDocument(pdfDocument);
        window.pdfMake.createPdf(pdfDocument).download(`employees_${new Date().toISOString().split('T')[0]}.pdf`);
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
            alert('Select the inactive employees to archive first: tick their checkboxes (or tap their rows), then press Archive Inactive.');
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

    // Keep Bootstrap's modal above its backdrop, outside transformed page cards.
    document.body.appendChild(document.getElementById('inviteModal'));
    function inviteNotice(message, success = false) {
        $('#inviteEmailAlert').attr('class', 'alert mx-3 mb-3 ' + (success ? 'alert-success' : 'alert-danger'))
            .attr('role', 'status').text(message).show();
    }
    $('#invite-link-tab, #invite-email-tab').on('shown.bs.tab', function() {
        $('#sendInviteBtn').toggle(this.id === 'invite-email-tab');
        $('#inviteEmailAlert').hide();
    });

    // Invite by email
    $('#inviteEmailForm').on('submit', function(e) {
        e.preventDefault();
        const email = $('#inviteEmail').val().trim();
        const message = $('#inviteMessage').val().trim();
        if (!this.reportValidity() || $('#sendInviteBtn').prop('disabled')) return;
        $('#inviteEmailAlert').hide();
        $('#sendInviteBtn').prop('disabled', true);
        $('#sendInviteSpinner').removeClass('d-none');

        $.ajax({
            url: '{{ route("employees.sendInvite") }}',
            type: 'POST',
            timeout: 30000,
            data: {
                _token: '{{ csrf_token() }}',
                email: email,
                message: message
            },
            success: function(res) {
                inviteNotice(res.message, true);
                $('#inviteEmailForm')[0].reset();
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                inviteNotice(errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || 'Could not send the invitation. Check your connection and try again.'));
            },
            complete: function() {
                $('#sendInviteBtn').prop('disabled', false);
                $('#sendInviteSpinner').addClass('d-none');
            }
        });
    });

    // Generate invite link
    $('#createLinkBtn').on('click', function() {
        const input = document.getElementById('inviteLinkEmail');
        if (!input.reportValidity() || $(this).prop('disabled')) return;
        $('#linkContainer').hide();
        $('#inviteLink').val('');
        $('#inviteEmailAlert').hide();
        $(this).prop('disabled', true);
        $.ajax({
            url: '{{ route("employees.inviteLink") }}',
            type: 'POST',
            timeout: 30000,
            data: { _token: '{{ csrf_token() }}', email: input.value.trim() },
            success: function(res) {
                $('#inviteLink').val(res.url);
                $('#linkContainer').show();
                inviteNotice(res.message, true);
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                inviteNotice(errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || 'Could not create the invitation link. Please try again.'));
            },
            complete: function() { $('#createLinkBtn').prop('disabled', false); }
        });
    });

    // Copy link
    $('#copyLinkBtn').on('click', async function() {
        const link = $('#inviteLink').val();
        if (!link) return;
        try {
            if (navigator.clipboard && window.isSecureContext) await navigator.clipboard.writeText(link);
            else {
                $('#inviteLink').select();
                if (!document.execCommand('copy')) throw new Error('Copy failed');
            }
            inviteNotice('Invitation link copied.', true);
        } catch (error) {
            inviteNotice('Select and copy the invitation link manually.');
        }
    });

    // Share link
    $('#shareLinkBtn').on('click', function() {
        const link = $('#inviteLink').val();
        window.location.href = `mailto:?subject=${encodeURIComponent(@json('Join ' . (session('current_company_name') ?: config('app.name'))))}&body=${encodeURIComponent(link)}`;
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
    $('#employeeTable thead th').each(function(index) {
            const headerText = $(this).text().trim();
            $('#employeeTable tbody tr').each(function() {
                $(this).find('td:not([colspan])').eq(index).attr('data-label', headerText);
            });
    });
});
</script>

<!-- Quick Password Reset Modal -->
<div class="modal fade" id="quickResetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-key text-warning me-2"></i> Reset Employee Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickResetPasswordForm" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <p class="small text-muted mb-3">Resetting password for <strong id="quickResetEmployeeName" class="text-dark">Employee</strong>. They will receive an on-screen notification and be automatically logged out after 1 minute.</p>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">New Password</label>
                        <input type="password" name="new_password" class="form-control rounded-3" placeholder="Enter new password (min 8 chars)" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control rounded-3" placeholder="Confirm new password" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark font-weight-bold rounded-pill px-4">
                        <i class="fas fa-lock me-1"></i> Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openResetPasswordModal(id, name) {
    document.getElementById('quickResetEmployeeName').textContent = name;
    document.getElementById('quickResetPasswordForm').action = "{{ url('/users') }}/" + id + "/change-password";
    new bootstrap.Modal(document.getElementById('quickResetPasswordModal')).show();
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
@endpush

@endsection
