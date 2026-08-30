@extends('admin.layout.app')

@section('title', 'Organization & Employee Management')

@section('content')
@php
    $canManageEmployees = in_array(auth()->user()?->role, ['admin', 'hr'], true);
    $userRole = auth()->user()?->role ?? 'employee';
@endphp

<div class="org-page">
    <!-- Breadcrumb -->
    <div class="org-breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
        <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
        <span>Organization</span>
        <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
        <span class="text-dark font-semibold">Employees</span>
    </div>

    <!-- Page Header Card -->
    <div class="org-header-card">
        <div class="org-header-title">
            <div class="org-header-icon">
                <i class="fas fa-sitemap"></i>
            </div>
            <div class="org-header-text">
                <h1>Employee Management</h1>
                <p>Manage employees, organizational structure, roles and reporting relationships.</p>
            </div>
        </div>
        <div class="org-header-actions">
            <!-- Quick Actions Dropdown -->
            <div class="dropdown">
                <button class="org-btn org-btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bolt text-amber-500"></i> Quick Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-xl">
                    @if($canManageEmployees)
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-bold text-slate-700" href="{{ route('employees.create') }}">
                                <i class="fas fa-user-plus text-emerald-600 me-2"></i> Add New Employee
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-bold text-slate-700" href="{{ route('employees.index') }}">
                                <i class="fas fa-users-gear text-blue-600 me-2"></i> HR Employee Records
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                    @endif
                    <li>
                        <a class="dropdown-item py-2 px-3 fw-bold text-slate-700" href="{{ route('employees.archive') }}">
                            <i class="fas fa-box-archive text-slate-500 me-2"></i> Archived Employees
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Export Button -->
            <div class="dropdown">
                <button class="org-btn org-btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download text-blue-600"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-xl">
                    <li><button class="dropdown-item py-2 px-3 fw-bold text-slate-700" onclick="exportDirectory('csv')"><i class="fas fa-file-csv text-emerald-600 me-2"></i> Export to CSV</button></li>
                    <li><button class="dropdown-item py-2 px-3 fw-bold text-slate-700" onclick="exportDirectory('excel')"><i class="fas fa-file-excel text-green-600 me-2"></i> Export to Excel</button></li>
                    <li><button class="dropdown-item py-2 px-3 fw-bold text-slate-700" onclick="window.print()"><i class="fas fa-print text-indigo-600 me-2"></i> Print / Save PDF</button></li>
                </ul>
            </div>

            <!-- Add Employee Button -->
            @if($canManageEmployees)
                <a href="{{ route('employees.create') }}" class="org-btn org-btn-primary">
                    <i class="fas fa-plus"></i> Add Employee
                </a>
            @endif
        </div>
    </div>

    <!-- Executive Summary Cards (6 Cards) -->
    <div class="org-stats-grid">
        <!-- Total Employees -->
        <div class="org-stat-card">
            <div class="org-stat-top">
                <div class="org-stat-icon total"><i class="fas fa-users"></i></div>
                <span class="org-stat-trend up"><i class="fas fa-arrow-up me-1"></i>System</span>
            </div>
            <div class="org-stat-number">{{ number_format($stats['total'] ?? 0) }}</div>
            <div class="org-stat-label">Total Employees</div>
        </div>

        <!-- Active Employees -->
        <div class="org-stat-card">
            <div class="org-stat-top">
                <div class="org-stat-icon active"><i class="fas fa-user-check"></i></div>
                <span class="org-stat-trend up"><i class="fas fa-check me-1"></i>Active</span>
            </div>
            <div class="org-stat-number">{{ number_format($stats['active'] ?? 0) }}</div>
            <div class="org-stat-label">Active Employees</div>
        </div>

        <!-- Inactive / Suspended -->
        <div class="org-stat-card">
            <div class="org-stat-top">
                <div class="org-stat-icon inactive"><i class="fas fa-user-xmark"></i></div>
                <span class="org-stat-trend neutral">Offboarding</span>
            </div>
            <div class="org-stat-number">{{ number_format($stats['inactive'] ?? 0) }}</div>
            <div class="org-stat-label">Inactive Employees</div>
        </div>

        <!-- Departments -->
        <div class="org-stat-card">
            <div class="org-stat-top">
                <div class="org-stat-icon departments"><i class="fas fa-building-user"></i></div>
                <span class="org-stat-trend up">Active</span>
            </div>
            <div class="org-stat-number">{{ number_format($stats['departments'] ?? 0) }}</div>
            <div class="org-stat-label">Departments</div>
        </div>

        <!-- Managers -->
        <div class="org-stat-card">
            <div class="org-stat-top">
                <div class="org-stat-icon managers"><i class="fas fa-user-tie"></i></div>
                <span class="org-stat-trend up">Leadership</span>
            </div>
            <div class="org-stat-number">{{ number_format($stats['managers'] ?? 0) }}</div>
            <div class="org-stat-label">Reporting Managers</div>
        </div>

        <!-- On Leave -->
        <div class="org-stat-card">
            <div class="org-stat-top">
                <div class="org-stat-icon onleave"><i class="fas fa-calendar-minus"></i></div>
                <span class="org-stat-trend neutral">Away</span>
            </div>
            <div class="org-stat-number">{{ number_format($stats['on_leave'] ?? 0) }}</div>
            <div class="org-stat-label">On Leave</div>
        </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="org-toolbar-card">
        <form method="GET" action="{{ route('organization.index') }}" class="org-toolbar-form" id="orgFilterForm">
            <!-- Search Box -->
            <div class="org-search-wrap">
                <i class="fas fa-search org-search-icon"></i>
                <input type="text" name="search" class="org-input" value="{{ request('search') }}" placeholder="Search employee by name, ID, email, skills...">
            </div>

            <!-- Company Filter -->
            @if(!empty($companies) && count($companies) > 0)
                <select name="company_id" class="org-select">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected((string) request('company_id') === (string) $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            @endif

            <!-- Department Filter -->
            <select name="department_id" class="org-select">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->dpt_name }}</option>
                @endforeach
            </select>

            <!-- Designation Filter -->
            <select name="designation_id" class="org-select">
                <option value="">All Designations</option>
                @foreach($designations as $designation)
                    <option value="{{ $designation->id }}" @selected((string) request('designation_id') === (string) $designation->id)>{{ $designation->name }}</option>
                @endforeach
            </select>

            <!-- Status Filter -->
            <select name="status" class="org-select">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                <option value="on_leave" @selected(request('status') === 'on_leave')>On Leave</option>
                <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
            </select>

            <!-- Reporting Manager Filter -->
            @if(!empty($managers) && count($managers) > 0)
                <select name="reporting_to" class="org-select">
                    <option value="">All Managers</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" @selected((string) request('reporting_to') === (string) $manager->id)>{{ $manager->name }}</option>
                    @endforeach
                </select>
            @endif

            <!-- Action Buttons -->
            <button type="submit" class="org-btn org-btn-primary">
                <i class="fas fa-filter"></i> Apply
            </button>

            @if(request()->anyFilled(['search', 'company_id', 'department_id', 'designation_id', 'status', 'reporting_to']))
                <a href="{{ route('organization.index') }}" class="org-btn org-btn-outline">
                    <i class="fas fa-rotate-left"></i> Reset
                </a>
            @endif

            <!-- View Mode Switcher -->
            <div class="org-view-toggle">
                <button type="button" class="org-view-btn active" id="btnTableView" onclick="switchView('table')">
                    <i class="fas fa-list"></i> Table
                </button>
                <button type="button" class="org-view-btn" id="btnGridView" onclick="switchView('grid')">
                    <i class="fas fa-th-large"></i> Grid
                </button>
                <button type="button" class="org-view-btn" id="btnTreeView" onclick="switchView('tree')">
                    <i class="fas fa-sitemap"></i> Structure
                </button>
            </div>
        </form>
    </div>

    <!-- View Container 1: Enterprise Data Table View -->
    <div id="tableViewSection" class="org-view-section">
        @if($employees->count() > 0)
            <!-- Show Entries Controls Bar -->
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 px-1">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-slate-700 font-bold text-xs text-uppercase mb-0">Show</span>
                    <select name="per_page" class="org-select py-1 px-3 text-sm font-bold text-slate-800 border rounded-lg bg-white shadow-xs" style="min-height: 38px; min-width: 90px; cursor: pointer;" onchange="document.getElementById('per_page_toolbar').value = this.value; document.getElementById('orgFilterForm').submit();">
                        <option value="10" @selected((string) request('per_page', 10) === '10')>10</option>
                        <option value="25" @selected((string) request('per_page') === '25')>25</option>
                        <option value="50" @selected((string) request('per_page') === '50')>50</option>
                        <option value="100" @selected((string) request('per_page') === '100')>100</option>
                        <option value="500" @selected((string) request('per_page') === '500')>500 (All)</option>
                    </select>
                    <span class="text-slate-700 font-bold text-xs text-uppercase mb-0">entries per page</span>
                </div>
                <div class="text-slate-500 font-semibold text-xs">
                    Showing <span class="text-slate-900 font-bold">{{ $employees->firstItem() ?? 0 }}</span> to <span class="text-slate-900 font-bold">{{ $employees->lastItem() ?? 0 }}</span> of <span class="text-slate-900 font-bold">{{ $employees->total() }}</span> records
                </div>
            </div>

            <div class="org-table-container">
                <table class="org-table" id="employeeDataTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"></th>
                            <th>Employee</th>
                            <th>Employee ID</th>
                            <th>Designation</th>
                            <th>Department</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Reporting Manager</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            @php
                                $detail = $employee->employeeDetail;
                                $status = strtolower($detail?->status ?? 'active');
                                $avatar = $employee->profile_image ? asset($employee->profile_image) : null;
                                $initials = strtoupper(mb_substr($employee->name, 0, 1));
                                $deptName = $detail?->department?->dpt_name ?? 'Unassigned';
                                $designationName = $detail?->designation?->name ?? $employee->designation ?? 'Team Member';
                                $empCode = $detail?->employee_id ?: 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT);
                                $companyName = $employee->company?->name ?? 'Primary Org';
                                $managerName = $detail?->reportingTo?->name ?? 'N/A';
                            @endphp
                            <tr data-emp-id="{{ $employee->id }}">
                                <td><input type="checkbox" class="emp-checkbox" value="{{ $employee->id }}"></td>
                                <td>
                                    <div class="org-emp-cell">
                                        @if($avatar)
                                            <img src="{{ $avatar }}" alt="{{ $employee->name }}" class="org-emp-avatar">
                                        @else
                                            <div class="org-emp-avatar">{{ $initials }}</div>
                                        @endif
                                        <div>
                                            <a href="javascript:void(0)" onclick="openEmployeeDrawer({{ json_encode([
                                                'id' => $employee->id,
                                                'name' => $employee->name,
                                                'email' => $employee->email,
                                                'mobile' => $employee->mobile ?: $detail?->mobile,
                                                'avatar' => $avatar,
                                                'initials' => $initials,
                                                'emp_code' => $empCode,
                                                'designation' => $designationName,
                                                'department' => $deptName,
                                                'company' => $companyName,
                                                'manager' => $managerName,
                                                'status' => $status,
                                                'joining_date' => $detail?->joining_date?->format('d M Y') ?? 'N/A',
                                                'dob' => $detail?->dob?->format('d M Y') ?? 'N/A',
                                                'skills' => $detail?->skills ?? 'Not specified',
                                                'about' => $detail?->directory_about ?: ($detail?->about ?: 'No bio available.'),
                                                'show_url' => route('organization.show', $employee),
                                                'edit_url' => route('employees.edit', $employee->id),
                                            ]) }})" class="org-emp-name">{{ $employee->name }}</a>
                                            <div class="org-emp-role">{{ $designationName }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="org-id-badge">{{ $empCode }}</span></td>
                                <td><span class="fw-semibold text-slate-700">{{ $designationName }}</span></td>
                                <td><span class="org-dept-tag"><i class="fas fa-building text-sky-500"></i> {{ $deptName }}</span></td>
                                <td>
                                    <div class="org-company-chip">
                                        @if($employee->company?->logo)
                                            <img src="{{ asset($employee->company->logo) }}" class="org-company-logo" alt="">
                                        @else
                                            <i class="fas fa-building-flag text-slate-400"></i>
                                        @endif
                                        <span>{{ $companyName }}</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:{{ $employee->email }}" class="org-email-chip">
                                        <i class="fas fa-envelope text-slate-400"></i> {{ $employee->email }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-slate-600 font-medium">
                                        <i class="fas fa-user-tie me-1 text-slate-400"></i> {{ $managerName }}
                                    </span>
                                </td>
                                <td>
                                    <span class="org-status-pill {{ $status }}">
                                        <span class="org-status-dot"></span> {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="d-inline-flex gap-1">
                                        <!-- View Action Button -->
                                        <a href="{{ route('organization.show', $employee) }}" class="org-btn org-btn-secondary org-btn-icon" title="View Directory Profile">
                                            <i class="fas fa-eye text-emerald-600"></i>
                                        </a>

                                        <!-- Edit Action Button (Protected by RBAC) -->
                                        @if($canManageEmployees)
                                            <a href="{{ route('employees.edit', $employee->id) }}" class="org-btn org-btn-secondary org-btn-icon" title="Edit Employee Details">
                                                <i class="fas fa-pen-to-square text-blue-600"></i>
                                            </a>
                                        @endif

                                        <!-- More Dropdown -->
                                        <div class="dropdown">
                                            <button class="org-btn org-btn-secondary org-btn-icon" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-vertical text-slate-500"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-md border-0 rounded-lg">
                                                <li>
                                                    <a class="dropdown-item fw-semibold text-slate-700" href="{{ route('organization.show', $employee) }}">
                                                        <i class="fas fa-id-card text-emerald-600 me-2"></i> Full Profile
                                                    </a>
                                                </li>
                                                @if($canManageEmployees)
                                                    <li>
                                                        <a class="dropdown-item fw-semibold text-slate-700" href="{{ route('employees.edit', $employee->id) }}">
                                                            <i class="fas fa-user-pen text-blue-600 me-2"></i> HR Edit Record
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-slate-500 font-medium text-sm">Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} employees</span>
                <div>{{ $employees->links() }}</div>
            </div>
        @else
            <!-- Empty State -->
            <div class="org-empty-card">
                <div class="org-empty-icon"><i class="fas fa-users-slash"></i></div>
                <h3>No Employees Found</h3>
                <p>No active employees match your current search or filter criteria. Try clearing filters or creating a new employee profile.</p>
                @if($canManageEmployees)
                    <a href="{{ route('employees.create') }}" class="org-btn org-btn-primary">
                        <i class="fas fa-user-plus"></i> Add Employee
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- View Container 2: Grid Cards View -->
    <div id="gridViewSection" class="org-view-section" style="display: none;">
        @if($employees->count() > 0)
            <div class="org-cards-grid">
                @foreach($employees as $employee)
                    @php
                        $detail = $employee->employeeDetail;
                        $status = strtolower($detail?->status ?? 'active');
                        $avatar = $employee->profile_image ? asset($employee->profile_image) : null;
                        $initials = strtoupper(mb_substr($employee->name, 0, 1));
                        $deptName = $detail?->department?->dpt_name ?? 'Unassigned';
                        $designationName = $detail?->designation?->name ?? $employee->designation ?? 'Team Member';
                        $empCode = $detail?->employee_id ?: 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT);
                        $companyName = $employee->company?->name ?? 'Primary Org';
                        $managerName = $detail?->reportingTo?->name ?? 'N/A';
                    @endphp
                    <div class="org-grid-card">
                        <div class="org-card-header">
                            <div class="org-emp-cell">
                                @if($avatar)
                                    <img src="{{ $avatar }}" alt="{{ $employee->name }}" class="org-emp-avatar">
                                @else
                                    <div class="org-emp-avatar">{{ $initials }}</div>
                                @endif
                                <div>
                                    <a href="{{ route('organization.show', $employee) }}" class="org-emp-name">{{ $employee->name }}</a>
                                    <div class="org-emp-role">{{ $designationName }}</div>
                                    <span class="org-status-pill {{ $status }}">
                                        <span class="org-status-dot"></span> {{ ucfirst($status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="org-card-meta">
                            <div class="org-card-meta-row">
                                <span class="org-meta-label">Employee ID</span>
                                <strong class="org-id-badge">{{ $empCode }}</strong>
                            </div>
                            <div class="org-card-meta-row">
                                <span class="org-meta-label">Department</span>
                                <span class="org-dept-tag"><i class="fas fa-building text-sky-500"></i> {{ $deptName }}</span>
                            </div>
                            <div class="org-card-meta-row">
                                <span class="org-meta-label">Company</span>
                                <strong class="org-meta-val">{{ $companyName }}</strong>
                            </div>
                            <div class="org-card-meta-row">
                                <span class="org-meta-label">Reports To</span>
                                <strong class="org-meta-val">{{ $managerName }}</strong>
                            </div>
                        </div>

                        <div class="org-card-footer">
                            <a href="mailto:{{ $employee->email }}" class="org-email-link">
                                <i class="fas fa-envelope"></i> Email
                            </a>
                            <div class="org-card-actions">
                                <a href="{{ route('organization.show', $employee) }}" class="org-action-icon-btn" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($canManageEmployees)
                                    <a href="{{ route('employees.edit', $employee->id) }}" class="org-action-icon-btn" title="Edit Employee">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-center mt-3">{{ $employees->links() }}</div>
        @endif
    </div>

    <!-- View Container 3: Organization Structure Tree View -->
    <div id="treeViewSection" class="org-view-section" style="display: none;">
        <div class="org-tree-container">
            @forelse($departmentGroups as $dept)
                @php
                    $activeMembers = $dept->employeeDetails->filter(fn ($d) => $d->status === 'Active' && $d->user);
                @endphp
                <div class="org-tree-dept-card">
                    <div class="org-tree-dept-header">
                        <div class="org-tree-dept-info">
                            <div class="org-tree-dept-icon">
                                <i class="fas fa-sitemap"></i>
                            </div>
                            <div>
                                <h4 class="org-tree-dept-name">{{ $dept->dpt_name }}</h4>
                                <span class="org-tree-dept-parent"><i class="fas fa-diagram-project me-1"></i> {{ $dept->parent?->dpt_name ?? 'Main Department' }}</span>
                            </div>
                        </div>
                        <span class="org-tree-count-badge">
                            <i class="fas fa-users me-1"></i> {{ $activeMembers->count() }} Employees
                        </span>
                    </div>

                    <div class="org-tree-member-grid">
                        @foreach($activeMembers as $detail)
                            @php
                                $u = $detail->user;
                                $uAvatar = $u->profile_image ? asset($u->profile_image) : null;
                                $uInitials = strtoupper(mb_substr($u->name, 0, 1));
                                $uDesignation = $detail->designation?->name ?? 'Team Member';
                                $uEmpCode = $detail->employee_id ?: 'EMP-' . str_pad($u->id, 4, '0', STR_PAD_LEFT);
                                $uManager = $detail->reportingTo?->name ?? null;
                            @endphp
                            <div class="org-tree-member-card">
                                <div class="org-tree-member-left">
                                    @if($uAvatar)
                                        <img src="{{ $uAvatar }}" alt="{{ $u->name }}" class="org-tree-avatar">
                                    @else
                                        <div class="org-tree-avatar-initials">{{ $uInitials }}</div>
                                    @endif
                                    <div class="org-tree-member-details">
                                        <a href="{{ route('organization.show', $u) }}" class="org-tree-member-name">{{ $u->name }}</a>
                                        <div class="org-tree-member-role">{{ $uDesignation }}</div>
                                        <div class="org-tree-member-meta">
                                            <span class="org-id-badge text-xs">{{ $uEmpCode }}</span>
                                            @if($uManager)
                                                <span class="org-tree-manager-chip"><i class="fas fa-user-tie me-1"></i> {{ $uManager }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="org-tree-member-actions">
                                    <a href="{{ route('organization.show', $u) }}" class="org-action-icon-btn" title="View Directory Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="org-empty-card">
                    <div class="org-empty-icon"><i class="fas fa-building-circle-exclamation"></i></div>
                    <h3>No Departments Configured</h3>
                    <p>Department structures will render automatically once active employees are assigned.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Slide-over Employee Profile Drawer -->
<div class="org-drawer-backdrop" id="drawerBackdrop" onclick="closeEmployeeDrawer()"></div>
<div class="org-drawer" id="employeeDrawer">
    <div class="org-drawer-header">
        <button type="button" class="org-drawer-close" onclick="closeEmployeeDrawer()"><i class="fas fa-times"></i></button>
        <div class="org-drawer-profile">
            <div id="drawerAvatarContainer"></div>
            <div>
                <h3 class="m-0 text-white font-bold text-lg" id="drawerName">Employee Name</h3>
                <div class="text-emerald-400 text-sm font-semibold mt-1" id="drawerDesignation">Designation</div>
                <div class="text-slate-300 text-xs mt-1" id="drawerCompany">Company</div>
            </div>
        </div>
    </div>

    <!-- Drawer Tabs Navigation -->
    <div class="org-drawer-tabs">
        <button type="button" class="org-drawer-tab active" onclick="switchDrawerTab('overview')">Overview</button>
        <button type="button" class="org-drawer-tab" onclick="switchDrawerTab('personal')">Personal</button>
        <button type="button" class="org-drawer-tab" onclick="switchDrawerTab('employment')">Employment</button>
        <button type="button" class="org-drawer-tab" onclick="switchDrawerTab('organization')">Organization</button>
    </div>

    <!-- Drawer Content Body -->
    <div class="org-drawer-body">
        <!-- Overview Tab Content -->
        <div id="tabOverview" class="org-drawer-tab-content">
            <div class="org-drawer-section">
                <div class="org-drawer-section-title">Contact Information</div>
                <div class="org-info-grid">
                    <div class="org-info-card">
                        <span>Email Address</span>
                        <strong id="drawerEmail">-</strong>
                    </div>
                    <div class="org-info-card">
                        <span>Mobile Phone</span>
                        <strong id="drawerMobile">-</strong>
                    </div>
                </div>
            </div>

            <div class="org-drawer-section">
                <div class="org-drawer-section-title">Directory Bio & Overview</div>
                <div class="p-3 bg-slate-50 border rounded-xl text-slate-700 text-sm" id="drawerAbout">
                    -
                </div>
            </div>

            <div class="org-drawer-section">
                <div class="org-drawer-section-title">Key Skills</div>
                <div class="p-3 bg-slate-50 border rounded-xl text-slate-700 text-sm" id="drawerSkills">
                    -
                </div>
            </div>
        </div>

        <!-- Personal Tab Content -->
        <div id="tabPersonal" class="org-drawer-tab-content" style="display: none;">
            <div class="org-drawer-section">
                <div class="org-drawer-section-title">Personal Details</div>
                <div class="org-info-grid">
                    <div class="org-info-card">
                        <span>Date of Birth</span>
                        <strong id="drawerDob">-</strong>
                    </div>
                    <div class="org-info-card">
                        <span>Joining Date</span>
                        <strong id="drawerJoiningDate">-</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employment Tab Content -->
        <div id="tabEmployment" class="org-drawer-tab-content" style="display: none;">
            <div class="org-drawer-section">
                <div class="org-drawer-section-title">Employment Details</div>
                <div class="org-info-grid">
                    <div class="org-info-card">
                        <span>Employee Code</span>
                        <strong id="drawerEmpCode">-</strong>
                    </div>
                    <div class="org-info-card">
                        <span>Employment Status</span>
                        <strong id="drawerStatus">-</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organization Tab Content -->
        <div id="tabOrganization" class="org-drawer-tab-content" style="display: none;">
            <div class="org-drawer-section">
                <div class="org-drawer-section-title">Reporting Structure</div>
                <div class="org-info-grid">
                    <div class="org-info-card">
                        <span>Department</span>
                        <strong id="drawerDept">-</strong>
                    </div>
                    <div class="org-info-card">
                        <span>Reporting Manager</span>
                        <strong id="drawerManager">-</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Drawer Footer Actions -->
    <div class="p-3 bg-slate-50 border-top d-flex align-items-center justify-content-between">
        <a id="drawerFullProfileBtn" href="#" class="org-btn org-btn-primary w-100 me-2">
            <i class="fas fa-external-link-alt"></i> View Full Directory Profile
        </a>
    </div>
</div>

@include('admin.organization-directory.styles')

<script>
    // View Switcher Handler
    function switchView(mode) {
        document.getElementById('tableViewSection').style.display = (mode === 'table') ? 'block' : 'none';
        document.getElementById('gridViewSection').style.display = (mode === 'grid') ? 'block' : 'none';
        document.getElementById('treeViewSection').style.display = (mode === 'tree') ? 'block' : 'none';

        document.getElementById('btnTableView').classList.toggle('active', mode === 'table');
        document.getElementById('btnGridView').classList.toggle('active', mode === 'grid');
        document.getElementById('btnTreeView').classList.toggle('active', mode === 'tree');
    }

    // Select All Checkboxes
    function toggleSelectAll(master) {
        const checkboxes = document.querySelectorAll('.emp-checkbox');
        checkboxes.forEach(cb => cb.checked = master.checked);
    }

    // Export Directory (CSV / Excel)
    function exportDirectory(format) {
        let table = document.getElementById('employeeDataTable');
        if (!table) return alert('No employee data available to export.');

        let csv = [];
        let rows = table.querySelectorAll('tr');

        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll('td, th');
            for (let j = 1; j < cols.length - 1; j++) {
                let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                row.push('"' + text.replace(/"/g, '""') + '"');
            }
            if (row.length > 0) csv.push(row.join(','));
        }

        let csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
        let downloadLink = document.createElement('a');
        downloadLink.download = 'employee_directory_' + new Date().toISOString().slice(0,10) + '.' + (format === 'excel' ? 'xls' : 'csv');
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
    }

    // Employee Slide-over Drawer
    let currentDrawerData = null;

    function openEmployeeDrawer(data) {
        currentDrawerData = data;

        document.getElementById('drawerName').innerText = data.name;
        document.getElementById('drawerDesignation').innerText = data.designation;
        document.getElementById('drawerCompany').innerText = data.company + ' • ' + data.department;
        document.getElementById('drawerEmail').innerText = data.email || 'N/A';
        document.getElementById('drawerMobile').innerText = data.mobile || 'N/A';
        document.getElementById('drawerAbout').innerText = data.about || 'No bio provided.';
        document.getElementById('drawerSkills').innerText = data.skills || 'Not specified';
        document.getElementById('drawerDob').innerText = data.dob || 'N/A';
        document.getElementById('drawerJoiningDate').innerText = data.joining_date || 'N/A';
        document.getElementById('drawerEmpCode').innerText = data.emp_code || 'N/A';
        document.getElementById('drawerStatus').innerText = data.status || 'Active';
        document.getElementById('drawerDept').innerText = data.department || 'Unassigned';
        document.getElementById('drawerManager').innerText = data.manager || 'N/A';
        document.getElementById('drawerFullProfileBtn').href = data.show_url;

        const avatarContainer = document.getElementById('drawerAvatarContainer');
        if (data.avatar) {
            avatarContainer.innerHTML = `<img src="${data.avatar}" class="org-drawer-avatar" alt="${data.name}">`;
        } else {
            avatarContainer.innerHTML = `<div class="org-drawer-avatar d-grid place-items-center bg-emerald-600 text-white font-bold text-xl">${data.initials}</div>`;
        }

        document.getElementById('drawerBackdrop').classList.add('active');
        document.getElementById('employeeDrawer').classList.add('active');
    }

    function closeEmployeeDrawer() {
        document.getElementById('drawerBackdrop').classList.remove('active');
        document.getElementById('employeeDrawer').classList.remove('active');
    }

    function switchDrawerTab(tabName) {
        const tabs = document.querySelectorAll('.org-drawer-tab');
        tabs.forEach(t => t.classList.remove('active'));

        const contents = document.querySelectorAll('.org-drawer-tab-content');
        contents.forEach(c => c.style.display = 'none');

        if (tabName === 'overview') {
            document.getElementById('tabOverview').style.display = 'block';
            tabs[0].classList.add('active');
        } else if (tabName === 'personal') {
            document.getElementById('tabPersonal').style.display = 'block';
            tabs[1].classList.add('active');
        } else if (tabName === 'employment') {
            document.getElementById('tabEmployment').style.display = 'block';
            tabs[2].classList.add('active');
        } else if (tabName === 'organization') {
            document.getElementById('tabOrganization').style.display = 'block';
            tabs[3].classList.add('active');
        }
    }
</script>
@endsection
