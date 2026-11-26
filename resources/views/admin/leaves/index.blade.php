@extends('admin.layout.app')

@section('title', ! empty($ownLeavesView) ? 'My Leave Requests' : ($isAdmin ? (request('staff_category') === 'authority' ? 'Higher-level leaves' : 'Leave Management') : 'My Leaves'))

@section('content')
@php $leaveRoute = ! empty($ownLeavesView) ? 'leaves.mine' : (request('staff_category') === 'authority' ? 'admin.authority-leaves' : 'leaves.index'); @endphp

<div id="leavePage" class="leave-page">
    <!-- Breadcrumb -->
    <div class="leave-breadcrumb">
        <i class="far fa-calendar-check"></i>
        <span>Dashboard</span>
        <span class="crumb-sep">/</span>
        <strong class="crumb-current">{{ ! empty($ownLeavesView) ? 'My Leave Requests' : ($isAdmin ? (request('staff_category') === 'authority' ? 'Higher-level leaves' : 'Leave Management') : 'My Leaves') }}</strong>
    </div>

    <!-- Header Card -->
    <section class="leave-hero">
        <div class="leave-hero-main">
            <div class="leave-hero-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="leave-hero-text">
                <h1>{{ ! empty($ownLeavesView) ? 'My Leave Requests' : ($isAdmin ? 'Leave Management' : 'My Leave Dashboard') }}</h1>
                <p>{{ ! empty($ownLeavesView) ? 'Your own leave requests to the company admin: status, approver and remaining balance.' : ($isAdmin ? 'Manage policy, balances, approvals, unpaid leaves, and employee leave history.' : 'Apply for leave, track approvals, and monitor your April-March balance.') }}</p>
            </div>
        </div>
        <div class="leave-hero-actions">
            @if(\App\Services\WorkforceAccess::isAdmin(auth()->user()) && auth()->user()->canViewModule('higher-level-leaves'))
                <a href="{{ route(request('staff_category') === 'authority' ? 'leaves.index' : 'admin.authority-leaves') }}" class="btn btn-light">
                    <i class="fas fa-users"></i> {{ request('staff_category') === 'authority' ? 'Employee Leaves' : 'Higher-level Leaves' }}
                    @if(request('staff_category') !== 'authority' && $authorityPendingCount)<span class="badge bg-primary">{{ $authorityPendingCount }} pending</span>@endif
                </a>
            @endif
            <a href="{{ route('leaves.create', ['staff_category' => request('staff_category')]) }}" class="btn btn-primary"><i class="fas fa-plus"></i> Apply Leave</a>
            @if(! $isAdmin)
                <a href="{{ route('leaves.apology-letters.create') }}" class="btn btn-light"><i class="fas fa-envelope-open-text"></i> Write Apology Letter</a>
            @endif
            <a href="{{ route('leaves.calendar', ['staff_category' => request('staff_category')]) }}" class="btn btn-light"><i class="far fa-calendar"></i> Calendar</a>
            @if($isAdmin)
                <a href="{{ route('leaves.apology-letters.index') }}" class="btn btn-light"><i class="far fa-file-alt"></i> Apology Letters</a>
                @if(\App\Services\WorkforceAccess::isAdmin(auth()->user()))
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#policyModal"><i class="fas fa-shield-alt"></i> Policy</button>
                @endif
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#exportModal"><i class="fas fa-cloud-upload-alt"></i> Export</button>
                <a href="{{ route('leaves.archive', ['staff_category' => request('staff_category')]) }}" class="btn btn-light">
                    <i class="fas fa-box-archive"></i> Archived
                    @if(($archivedCount ?? 0) > 0)
                        <span class="archive-count-badge">{{ $archivedCount }}</span>
                    @endif
                </a>
                <form method="POST" action="{{ route('leaves.archive-all', ['staff_category' => request('staff_category')]) }}" class="leave-inline-form" onsubmit="return confirm('Archive all active leave requests? They can be restored later.');">
                    @csrf
                    <button type="submit" class="btn btn-light"><i class="fas fa-boxes-packing"></i> Archive All</button>
                </form>
            @endif
        </div>
    </section>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Please fix these issues:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Policy Notice -->
    <section class="policy-notice">
        <div class="notice-icon"><i class="fas fa-info"></i></div>
        <div class="notice-content">
            @if(str_starts_with((string) $policyNotice, 'Company Leave Policy:'))
                <div class="notice-title-row">
                    <strong>Company Leave Policy:</strong>
                </div>
                <p>{{ trim(\Illuminate\Support\Str::after((string) $policyNotice, 'Company Leave Policy:')) }}</p>
            @else
                <p>{{ $policyNotice }}</p>
            @endif
        </div>
    </section>

    <!-- Stats Cards -->
    <section class="leave-stats">
        <div class="stat-card stat-total">
            <div class="stat-body">
                <span>Total Requests</span>
                <strong>{{ $stats['total'] }}</strong>
            </div>
            <div class="stat-icon-box"><i class="fas fa-list-ul"></i></div>
        </div>
        <div class="stat-card pending">
            <div class="stat-body">
                <span>Pending</span>
                <strong>{{ $stats['pending'] }}</strong>
            </div>
            <div class="stat-icon-box"><i class="fas fa-hourglass-half"></i></div>
        </div>
        <div class="stat-card approved">
            <div class="stat-body">
                <span>Approved</span>
                <strong>{{ $stats['approved'] }}</strong>
            </div>
            <div class="stat-icon-box"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-card rejected">
            <div class="stat-body">
                <span>Rejected</span>
                <strong>{{ $stats['rejected'] }}</strong>
            </div>
            <div class="stat-icon-box"><i class="fas fa-times-circle"></i></div>
        </div>
        <div class="stat-card unpaid">
            <div class="stat-body">
                <span>Unpaid</span>
                <strong>{{ $stats['unpaid'] }}</strong>
            </div>
            <div class="stat-icon-box"><i class="fas fa-credit-card"></i></div>
        </div>
    </section>

    @if(! $isAdmin)
    <!-- Personal Balance Card -->
    <section class="balance-grid balance-grid--personal">
        @forelse($balances as $balance)
            <article class="balance-card">
                <div class="balance-head">
                    <div>
                        <h3>Your Balance</h3>
                        <p>{{ $balance->leave_year ?: $balance->year }}</p>
                    </div>
                    <span>{{ $balance->remaining_leaves }} left</span>
                </div>
                <div class="balance-meter"><span style="width: {{ $balance->allocated_leaves > 0 ? min(100, ($balance->used_leaves / $balance->allocated_leaves) * 100) : 0 }}%"></span></div>
                <div class="balance-pills">
                    <span>Sick used: {{ $balance->sick_used }}</span>
                    <span>Casual used: {{ $balance->casual_used }}</span>
                    <span>Unpaid {{ $balance->unpaid_used }}</span>
                </div>
            </article>
        @empty
            <article class="balance-card">
                <div class="balance-head">
                    <div>
                        <h3>No Balance Yet</h3>
                        <p>Open or create leave requests to initialize balances.</p>
                    </div>
                </div>
            </article>
        @endforelse
    </section>
    @endif

    <!-- Filter Panel -->
    <section class="filter-panel">
        <form method="GET" action="{{ route($leaveRoute) }}" class="filter-grid">
            <input type="hidden" name="staff_category" value="{{ request('staff_category', 'employee') }}">
            <input type="hidden" name="per_page" value="{{ $perPage ?? request('per_page', 20) }}">
            @if($isAdmin)
                <div class="filter-field">
                    <label>Employee</label>
                    <select name="employee" class="form-control">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="filter-field">
                <label>Leave Type</label>
                <select name="leave_type_id" class="form-control">
                    <option value="">All Types</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'unpaid' => 'Unpaid'] as $statusVal => $statusLabel)
                        <option value="{{ $statusVal }}" {{ request('status') === $statusVal ? 'selected' : '' }}>{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label>From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="filter-field">
                <label>To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route($leaveRoute) }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
            </div>
        </form>
    </section>

    <!-- Table Card -->
    <section class="table-card">
        <div class="table-head" data-pms-table-header>
            <div class="table-head-info">
                <h2>Leave Requests</h2>
                <p>Approve, reject, convert to unpaid leave, and audit each request.</p>
            </div>
            <div class="table-tools-cluster">
                <div id="leaveTableToolbarHost" class="leave-toolbar-host"></div>
                @if($isAdmin)
                    <div class="table-tools">
                        <form method="GET" action="{{ route($leaveRoute) }}" class="entry-tools">
                            <input type="hidden" name="staff_category" value="{{ request('staff_category', 'employee') }}">
                            @foreach(request()->except(['page', 'per_page']) as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $item)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label for="per-page-select">SHOW</label>
                            <select id="per-page-select" name="per_page" class="form-control form-control-sm" onchange="this.form.submit()">
                                @foreach([10, 20, 30, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ ($perPage ?? 20) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                            <span>entries</span>
                        </form>
                        <div class="bulk-tools">
                            <select id="bulk-action" class="form-control form-control-sm">
                                <option value="">Bulk Action</option>
                                <option value="archive">Archive Selected</option>
                                <option value="change_status">Change Status</option>
                                <option value="delete">Delete</option>
                            </select>
                            <select id="status-dropdown" class="form-control form-control-sm" style="display:none">
                                <option value="">Status</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="unpaid">Convert to Unpaid</option>
                                <option value="pending">Pending</option>
                            </select>
                            <button type="button" id="apply-action" class="btn btn-sm btn-primary">Apply</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table leave-table" id="leaveTable" data-pms-toolbar-host="leaveTableToolbarHost">
                <thead>
                    <tr>
                        @if($isAdmin)<th width="44"><input type="checkbox" id="select-all" class="form-check-input"></th>@endif
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                        @php
                            $statusClass = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$leave->status] ?? 'secondary';
                            $currentStatusValue = $leave->status;
                            $statusSelectClass = $statusClass;

                            $empName = trim((string) ($leave->user?->name ?? 'N/A'));
                            $nameParts = preg_split('/\s+/', $empName, -1, PREG_SPLIT_NO_EMPTY);
                            $initials = count($nameParts) >= 2
                                ? strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[count($nameParts) - 1], 0, 1))
                                : strtoupper(mb_substr($empName, 0, 2));
                            $avatarPalettes = ['av-blue', 'av-emerald', 'av-indigo', 'av-amber', 'av-rose'];
                            $avatarClass = $avatarPalettes[(int) ($leave->user_id ?? $leave->id ?? 0) % count($avatarPalettes)];

                            $typeLower = strtolower((string) $leave->type_label);
                            $typePillClass = str_contains($typeLower, 'sick')
                                ? 'type-sick'
                                : (str_contains($typeLower, 'casual')
                                    ? 'type-casual'
                                    : (str_contains($typeLower, 'unpaid') ? 'type-unpaid' : 'type-default'));
                            $empCode = $leave->user?->employeeDetail?->employee_id;
                        @endphp
                        <tr>
                            @if($isAdmin)<td><input type="checkbox" class="form-check-input leave-checkbox" value="{{ $leave->id }}"></td>@endif
                            <td>
                                <div class="leave-emp-cell">
                                    <span class="leave-emp-avatar {{ $avatarClass }}">{{ $initials }}</span>
                                    <div class="leave-emp-meta">
                                        <strong>{{ $empName }}</strong>
                                        @if($empCode)
                                            <small class="emp-code">{{ $empCode }}</small>
                                        @endif
                                        @if($leave->staff_category === 'authority')
                                            <small>{{ $leave->staff_role_name }} · {{ $leave->staff_designation }} @if($leave->staff_designation_level !== null) · Level {{ $leave->staff_designation_level }} @endif</small>
                                        @endif
                                        <small>{{ $leave->user?->employeeDetail?->department?->dpt_name ?? $leave->user?->designation ?? 'Employee' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="type-badge {{ $typePillClass }}">{{ $leave->type_label }}</span></td>
                            <td class="dates-cell">
                                <strong>{{ optional($leave->start_date)->format('M d, Y') }}@if($leave->end_date && optional($leave->end_date)->ne($leave->start_date)) - {{ optional($leave->end_date)->format('M d, Y') }}@endif</strong>
                                <small>
                                    {{ optional($leave->start_date)->format('D') }}@if($leave->end_date && optional($leave->end_date)->ne($leave->start_date)) - {{ optional($leave->end_date)->format('D') }}@endif
                                    ({{ $leave->half_day_flag ? 'Half Day' : 'Full Day' }})
                                </small>
                            </td>
                            <td class="days-cell">
                                <strong>{{ number_format((float) $leave->total_days, 1) }}</strong>
                                <small>Days</small>
                            </td>
                            <td class="pay-cell">
                                <div class="pay-stack">
                                @if($leave->status === 'rejected')
                                    <span class="pay-badge rejected">N/A</span>
                                    <small class="pay-sub">Rejected</small>
                                @elseif($leave->status === 'pending')
                                    <span class="pay-badge pending">Pending</span>
                                @else
                                    @php
                                        $paidDays = (float) ($leave->paid_days ?? 0);
                                        $unpaidDays = (float) ($leave->unpaid_days ?? 0);
                                    @endphp
                                    <span class="pay-badge {{ $unpaidDays > 0 ? 'unpaid' : 'paid' }}">
                                        @if($paidDays > 0 && $unpaidDays > 0)
                                            Partial Unpaid
                                        @else
                                            @if($unpaidDays <= 0)<i class="fas fa-check"></i>@endif
                                            {{ $unpaidDays > 0 ? 'Unpaid' : 'Paid' }}
                                        @endif
                                    </span>
                                    @if($paidDays > 0 || $unpaidDays > 0)
                                        <small class="pay-split"><span>{{ number_format($paidDays, 1) }} paid</span><span>{{ number_format($unpaidDays, 1) }} unpaid</span></small>
                                    @endif
                                @endif
                                </div>
                            </td>
                            <td>
                                @if($isAdmin)
                                    <form method="POST" action="{{ route('leaves.updateStatus', $leave->id) }}" class="status-change-form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status"
                                                class="form-control form-control-sm status-select {{ $statusSelectClass }}"
                                                data-original-status="{{ $currentStatusValue }}"
                                                aria-label="Change leave status">
                                            <option value="pending" {{ $currentStatusValue === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $currentStatusValue === 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ $currentStatusValue === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                            <option value="unpaid">Convert to Unpaid</option>
                                        </select>
                                    </form>
                                @else
                                    <span class="status-badge {{ $statusClass }}">{{ ucfirst($leave->status) }}</span>
                                @endif
                            </td>
                            <td class="reason-cell">
                                <span class="reason-text">{{ \Illuminate\Support\Str::limit($leave->reason, 58) }}</span>
                                @if($leave->status === 'rejected' && $leave->rejection_reason)
                                    <small class="reason-sub text-danger">{{ \Illuminate\Support\Str::limit($leave->rejection_reason, 48) }}</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="action-row">
                                    <a href="{{ route('leaves.show', $leave->id) }}" class="btn btn-sm btn-light" title="View"><i class="fas fa-eye"></i></a>
                                    @if(! $isAdmin)
                                        <a href="{{ route('leaves.apology-letters.create', ['leave_id' => $leave->id]) }}" class="btn btn-sm btn-light" title="Write Apology Letter"><i class="fas fa-envelope-open-text"></i></a>
                                    @endif
                                    {{-- HR's own requests are changed by the company admin, so My Leave Requests is read only. --}}
                                    @if(($leave->status === 'pending' || $isAdmin) && empty($ownLeavesView))
                                        <a href="{{ route('leaves.edit', $leave->id) }}" class="btn btn-sm btn-light" title="Edit"><i class="fas fa-pen"></i></a>
                                    @endif
                                    @if($isAdmin)
                                        <form method="POST" action="{{ route('leaves.updateStatus', $leave->id) }}" class="d-inline" onsubmit="return confirm('Approve this leave request?');">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve Leave"><i class="fas fa-check"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('leaves.updateStatus', $leave->id) }}" class="d-inline" onsubmit="return confirm('Convert this leave request to unpaid leave?');">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="unpaid">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Convert to Unpaid"><i class="fas fa-wallet"></i></button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger btn-reject-trigger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}" data-toggle="modal" data-target="#rejectModal{{ $leave->id }}" data-reject-leave-id="{{ $leave->id }}" title="Reject Leave"><i class="fas fa-times"></i></button>
                                        <form method="POST" action="{{ route('leaves.archive.action', $leave->id) }}" class="d-inline" onsubmit="return confirm('Archive this leave request? It can be restored later.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-secondary" title="Archive"><i class="fas fa-box-archive"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100" class="p-0 border-0">
                                <div class="empty-state">
                                    <div class="empty-icon-box"><i class="fas fa-calendar-times"></i></div>
                                    <h3>No leave requests found</h3>
                                    <p>Try adjusting your filters or submit a new leave request.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $leaves->links() }}</div>
    </section>

    @if($isAdmin)
    <!-- Collapsible Employee Leave Balances -->
    <section class="balance-drawer">
        <div class="balance-drawer-head">
            <div class="balance-drawer-title">
                <span class="balance-drawer-icon"><i class="fas fa-wallet"></i></span>
                <div>
                    <h3>Employee Leave Balances</h3>
                    <p>{{ $balances->count() }} active balance {{ $balances->count() === 1 ? 'record' : 'records' }} for this leave year</p>
                </div>
            </div>
            <button type="button" class="btn btn-light btn-sm balance-drawer-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#employeeBalancesPanel" aria-expanded="false" aria-controls="employeeBalancesPanel">
                <i class="fas fa-chevron-down"></i>
                <span>View Balances</span>
            </button>
        </div>
        <div id="employeeBalancesPanel" class="collapse">
            <div class="balance-grid balance-grid--drawer">
                @forelse($balances as $balance)
                    <article class="balance-card">
                        <div class="balance-head">
                            <div>
                                <h3>{{ $balance->user?->name ?? 'Employee' }}</h3>
                                <p>{{ $balance->leave_year ?: $balance->year }}</p>
                            </div>
                            <span>{{ $balance->remaining_leaves }} left</span>
                        </div>
                        <div class="balance-meter"><span style="width: {{ $balance->allocated_leaves > 0 ? min(100, ($balance->used_leaves / $balance->allocated_leaves) * 100) : 0 }}%"></span></div>
                        <div class="balance-pills">
                            <span>Sick used: {{ $balance->sick_used }}</span>
                            <span>Casual used: {{ $balance->casual_used }}</span>
                            <span>Unpaid {{ $balance->unpaid_used }}</span>
                        </div>
                        @if(\App\Services\WorkforceAccess::isAdmin(auth()->user()))
                            <form method="POST" action="{{ route('leaves.reset-employee-leaves', $balance->user_id) }}" onsubmit="return confirm('Reset leave balance for {{ $balance->user?->name }}?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-rotate"></i> Reset Balance</button>
                            </form>
                        @endif
                    </article>
                @empty
                    <article class="balance-card">
                        <div class="balance-head">
                            <div>
                                <h3>No Balance Yet</h3>
                                <p>Open or create leave requests to initialize balances.</p>
                            </div>
                        </div>
                    </article>
                @endforelse
            </div>
        </div>
    </section>
    @endif
</div>

<!-- Reject Modals -->
@if($isAdmin)
@foreach($leaves as $leave)
<div class="modal fade leave-ui-modal" id="rejectModal{{ $leave->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $leave->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('leaves.updateStatus', $leave->id) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="rejected">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel{{ $leave->id }}"><i class="fas fa-times-circle text-danger me-2"></i>Reject Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Are you sure you want to reject the leave request for <strong>{{ $leave->user?->name ?? 'Employee' }}</strong> ({{ number_format((float) $leave->total_days, 1) }} days)?</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Rejection Reason <small class="text-muted fw-normal">(optional)</small></label>
                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Provide a reason for rejecting this leave request..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i> Confirm Reject</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endif

<!-- Policy Modal -->
@if($isAdmin)
<div class="modal fade leave-ui-modal" id="policyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('leaves.update-policy') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-sliders-h me-2"></i>Leave Policy Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="policy-grid">
                    <div><label>Annual Leaves</label><input type="number" step="0.5" name="annual_leaves" class="form-control" value="{{ $policy->annual_leaves }}" required></div>
                    <div><label>Sick Leave Limit</label><input type="number" step="0.5" name="sick_leave_limit" class="form-control" value="{{ $policy->sick_leave_limit }}" required></div>
                    <div><label>Casual Leave Limit</label><input type="number" step="0.5" name="casual_leave_limit" class="form-control" value="{{ $policy->casual_leave_limit }}" required></div>
                    <div><label>Monthly salary deduction per unpaid day (%)</label><input type="number" step="0.01" min="0" max="100" name="unpaid_deduction_percentage" class="form-control" value="{{ \App\Models\AppSetting::valueFor('leave_unpaid_deduction_percentage', '3.33') }}" required><small>Example: 2% × 2 unpaid days = 4% of monthly salary. Maximum total deduction is the salary available.</small></div>
                    <div><label>CL Advance Days</label><input type="number" name="casual_advance_days" class="form-control" value="{{ $policy->casual_advance_days }}" required></div>
                    <div><label>Manual Review Days</label><input type="number" name="casual_manual_review_days" class="form-control" value="{{ $policy->casual_manual_review_days }}" required></div>
                    <div><label>Max Carry Forward</label><input type="number" name="max_carry_forward" class="form-control" value="{{ $policy->max_carry_forward }}"></div>
                    <div><label>Leave Value</label><input type="number" step="0.01" name="leave_monetary_value" class="form-control" value="{{ $policy->leave_monetary_value }}"></div>
                    <div><label>Unpaid Handling</label><select name="unpaid_leave_handling" class="form-control"><option value="unpaid_leave" {{ $policy->unpaid_leave_handling === 'unpaid_leave' ? 'selected' : '' }}>Unpaid Leave</option><option value="absent" {{ $policy->unpaid_leave_handling === 'absent' ? 'selected' : '' }}>Absent</option></select></div>
                </div>
                <div class="switch-grid">
                    @foreach([
                        'auto_approve_casual_leave' => 'Auto approve eligible Casual Leave',
                        'hr_approval_required' => 'HR approval mandatory',
                        'allow_sick_apology' => 'Allow Sick Leave apology',
                        'allow_carry_forward' => 'Allow carry forward',
                    ] as $field => $label)
                        <label class="switch-line">
                            <input type="checkbox" name="{{ $field }}" value="1" {{ $policy->{$field} ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addLeaveTypeModal">Add Leave Type</button>
                <button class="btn btn-primary">Save Policy</button>
            </div>
        </form>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade leave-ui-modal" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form class="modal-content export-modal" id="leaveExportForm" method="GET" action="{{ route('leaves.export') }}">
            <input type="hidden" name="staff_category" value="{{ request('staff_category', 'employee') }}">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-download me-2"></i>Export Leave Report</h5>
                    <p class="export-subtitle">Export all active leave requests, or narrow the report with filters.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="export-layout">
                    <section class="export-filters">
                        <h6>Report Filters</h6>
                        <div class="export-filter-grid">
                            <div>
                                <label class="form-label">Employee</label>
                                <select name="employee" class="form-control">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Leave Type</label>
                                <select name="leave_type_id" class="form-control">
                                    <option value="">All Types</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">From</label>
                                <input type="date" name="from" class="form-control">
                            </div>
                            <div>
                                <label class="form-label">To</label>
                                <input type="date" name="to" class="form-control">
                            </div>
                        </div>
                    </section>

                    <section class="export-actions-panel">
                        <h6>Export Format</h6>
                        <div class="export-action-grid">
                            <button type="button" class="export-tile copy" id="copyLeaveExport">
                                <i class="fas fa-copy"></i>
                                <span>Copy</span>
                                <small>Clipboard text</small>
                            </button>
                            <button class="export-tile excel" name="type" value="excel">
                                <i class="fas fa-file-excel"></i>
                                <span>Excel</span>
                                <small>.xls file</small>
                            </button>
                            <button class="export-tile csv" name="type" value="csv">
                                <i class="fas fa-file-csv"></i>
                                <span>CSV</span>
                                <small>Spreadsheet CSV</small>
                            </button>
                            <button class="export-tile pdf" name="type" value="pdf">
                                <i class="fas fa-file-pdf"></i>
                                <span>PDF</span>
                                <small>Download report</small>
                            </button>
                            <button class="export-tile print" name="type" value="print" formtarget="_blank">
                                <i class="fas fa-print"></i>
                                <span>Print</span>
                                <small>Printable view</small>
                            </button>
                        </div>
                    </section>
                </div>
                <div class="export-copy-status" id="exportCopyStatus" aria-live="polite"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade leave-ui-modal" id="addLeaveTypeModal" tabindex="-1" aria-labelledby="addLeaveTypeTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('leaves.store-type') }}">
            @csrf
            <div class="modal-header"><h5 id="addLeaveTypeTitle" class="modal-title">Add Company Leave Type</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <p class="text-muted">This type is available only in your company and shares its annual paid leave allowance. Days beyond that allowance are unpaid.</p>
                <label for="newLeaveTypeName">Name</label><input id="newLeaveTypeName" name="name" class="form-control mb-3" maxlength="100" required placeholder="e.g. Bereavement Leave">
                <label for="newLeaveTypeCode">Code</label><input id="newLeaveTypeCode" name="code" class="form-control mb-3" maxlength="20" pattern="[A-Z][A-Z0-9_]{1,19}" required placeholder="e.g. BL">
                <label class="switch-line mt-2"><input type="checkbox" name="requires_document" value="1"> <span>Supporting document required</span></label>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add Leave Type</button></div>
        </form>
    </div>
</div>
@endif

<style>
    /* ===== CLEAN ENTERPRISE SAAS THEME FOR LEAVE MANAGEMENT ===== */
    :root {
        --lp-bg: #f8fafc;
        --lp-surface: #ffffff;
        --lp-surface-subtle: #f8fafc;
        --lp-surface-hover: #f8fafc;
        --lp-border: #e2e8f0;
        --lp-border-subtle: #f1f5f9;
        --lp-text: #0f172a;
        --lp-text-body: #334155;
        --lp-text-muted: #64748b;
        --lp-text-faint: #94a3b8;
        --lp-primary: #2563eb;
        --lp-primary-hover: #1d4ed8;
        --lp-primary-soft: #eff6ff;
        --lp-primary-border: #dbeafe;
        --lp-notice-bg: #f4f8ff;
        --lp-shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
        --lp-shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    html:is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark),
    body:is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) {
        --lp-bg: #070b1a;
        --lp-surface: #0f1530;
        --lp-surface-subtle: #141b3d;
        --lp-surface-hover: rgba(37, 99, 235, 0.1);
        --lp-border: rgba(148, 163, 184, 0.18);
        --lp-border-subtle: rgba(148, 163, 184, 0.12);
        --lp-text: #f8fafc;
        --lp-text-body: #e2e8f0;
        --lp-text-muted: #94a3b8;
        --lp-text-faint: #64748b;
        --lp-primary: #3b82f6;
        --lp-primary-hover: #60a5fa;
        --lp-primary-soft: rgba(59, 130, 246, 0.16);
        --lp-primary-border: rgba(96, 165, 250, 0.3);
        --lp-notice-bg: rgba(37, 99, 235, 0.12);
        --lp-shadow-xs: 0 4px 12px rgba(0, 0, 0, 0.25);
        --lp-shadow-sm: 0 12px 28px rgba(0, 0, 0, 0.36);
    }

    body:has(#leavePage),
    body:has(#leavePage) .layout-page,
    body:has(#leavePage) .content-wrapper {
        background: var(--lp-bg) !important;
        background-image: none !important;
    }

    #leavePage.leave-page {
        padding: 18px 28px 34px !important;
        min-height: 100vh;
        background: var(--lp-bg) !important;
        background-image: none !important;
        color: var(--lp-text);
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        position: relative;
    }

    #leavePage.leave-page::before {
        display: none !important;
        content: none !important;
    }

    /* Breadcrumb Pill */
    #leavePage .leave-breadcrumb {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 999px;
        background: var(--lp-surface) !important;
        backdrop-filter: none !important;
        border: 1px solid var(--lp-border) !important;
        box-shadow: var(--lp-shadow-xs) !important;
        margin-bottom: 14px;
        font-size: 0.78rem;
        font-weight: 500;
        color: var(--lp-text-muted);
    }

    #leavePage .leave-breadcrumb i {
        color: var(--lp-primary) !important;
        font-size: 0.78rem;
    }

    #leavePage .leave-breadcrumb .crumb-sep {
        color: var(--lp-text-faint);
    }

    #leavePage .leave-breadcrumb .crumb-current {
        color: var(--lp-text) !important;
        font-weight: 600;
    }

    /* Header Card */
    #leavePage .leave-hero {
        background: var(--lp-surface) !important;
        backdrop-filter: none !important;
        border: 1px solid var(--lp-border) !important;
        border-radius: 14px !important;
        padding: 1.2rem 1.45rem !important;
        margin-bottom: 1rem !important;
        box-shadow: var(--lp-shadow-xs) !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }

    #leavePage .leave-hero:hover {
        transform: none !important;
        box-shadow: var(--lp-shadow-xs) !important;
    }

    #leavePage .leave-hero-main {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
        flex: 1 1 320px;
    }

    #leavePage .leave-hero-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 11px !important;
        background: var(--lp-primary-soft) !important;
        background-image: none !important;
        border: 1px solid var(--lp-primary-border) !important;
        color: var(--lp-primary) !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem !important;
        box-shadow: none !important;
        filter: none !important;
        flex-shrink: 0;
    }

    #leavePage .leave-hero-icon > i,
    #leavePage .leave-hero-icon > i::before {
        color: var(--lp-primary) !important;
        -webkit-text-fill-color: var(--lp-primary) !important;
    }

    #leavePage .leave-hero-text {
        min-width: 0;
    }

    #leavePage .leave-hero h1 {
        margin: 0 0 0.2rem 0 !important;
        font-size: 1.22rem !important;
        font-weight: 700 !important;
        line-height: 1.25 !important;
        color: var(--lp-text) !important;
        -webkit-text-fill-color: var(--lp-text) !important;
        letter-spacing: -0.02em;
    }

    #leavePage .leave-hero p {
        margin: 0 !important;
        max-width: 380px;
        color: var(--lp-text-muted) !important;
        -webkit-text-fill-color: var(--lp-text-muted) !important;
        font-size: 0.8rem !important;
        font-weight: 400 !important;
        line-height: 1.45 !important;
    }

    #leavePage .leave-hero-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.45rem;
        max-width: 580px;
    }

    #leavePage .leave-inline-form {
        display: inline-flex;
        margin: 0;
    }

    /* Buttons */
    #leavePage .btn,
    .leave-ui-modal .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.42rem;
        min-height: 35px;
        padding: 0 0.82rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        white-space: nowrap;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.16s ease, border-color 0.16s ease, color 0.16s ease;
    }

    #leavePage .btn i,
    .leave-ui-modal .btn i {
        font-size: 0.82rem;
        filter: none !important;
    }

    #leavePage .btn-primary,
    .leave-ui-modal .btn-primary {
        background: var(--lp-primary) !important;
        background-image: none !important;
        border: 1px solid var(--lp-primary) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: var(--lp-shadow-xs) !important;
    }

    #leavePage .btn-primary:hover,
    .leave-ui-modal .btn-primary:hover {
        background: var(--lp-primary-hover) !important;
        border-color: var(--lp-primary-hover) !important;
        transform: none !important;
    }

    #leavePage .btn-primary i,
    #leavePage .btn-primary i::before {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #leavePage .btn-light,
    #leavePage .btn-secondary,
    .leave-ui-modal .btn-secondary {
        background: var(--lp-surface) !important;
        border: 1px solid var(--lp-border) !important;
        color: var(--lp-text-body) !important;
        -webkit-text-fill-color: var(--lp-text-body) !important;
        box-shadow: var(--lp-shadow-xs) !important;
    }

    #leavePage .btn-light:hover,
    #leavePage .btn-secondary:hover,
    .leave-ui-modal .btn-secondary:hover {
        background: var(--lp-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        color: var(--lp-text) !important;
        -webkit-text-fill-color: var(--lp-text) !important;
        transform: none !important;
    }

    #leavePage .btn-light i,
    #leavePage .btn-light i::before {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #leavePage .archive-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 19px;
        height: 19px;
        padding: 0 5px;
        border-radius: 999px;
        background: #f59e0b;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-size: 0.68rem;
        font-weight: 700;
        line-height: 1;
    }

    /* Alerts */
    #leavePage .alert {
        border-radius: 12px;
        border: 1px solid var(--lp-border);
        padding: 0.85rem 1.15rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.85rem;
    }

    #leavePage .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }

    #leavePage .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }

    /* Policy Notice Callout */
    #leavePage .policy-notice {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 0.9rem 1.15rem;
        border-radius: 12px;
        background: var(--lp-notice-bg) !important;
        border: 1px solid var(--lp-primary-border) !important;
        margin-bottom: 1rem;
        box-shadow: none !important;
    }

    #leavePage .notice-icon {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--lp-primary) !important;
        border: 0 !important;
        color: #ffffff !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.68rem;
        flex: 0 0 22px;
        margin-top: 2px;
        filter: none !important;
    }

    #leavePage .notice-icon i,
    #leavePage .notice-icon i::before {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #leavePage .notice-content {
        min-width: 0;
        flex: 1 1 auto;
    }

    #leavePage .notice-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }

    #leavePage .notice-title-row strong {
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--lp-text);
    }

    #leavePage .policy-notice p {
        margin: 0 !important;
        color: var(--lp-text-body) !important;
        -webkit-text-fill-color: var(--lp-text-body) !important;
        font-size: 0.8rem !important;
        font-weight: 400 !important;
        line-height: 1.5 !important;
    }

    /* ===== 5 EQUAL KPI CARDS ===== */
    #leavePage .leave-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.9rem;
        margin-bottom: 1rem;
    }

    #leavePage .leave-stats > .stat-card,
    #leavePage .leave-stats > .stat-card:first-of-type {
        background: var(--lp-surface) !important;
        background-image: none !important;
        border: 1px solid var(--lp-border) !important;
        border-radius: 12px !important;
        padding: 0.95rem 1.1rem !important;
        box-shadow: var(--lp-shadow-xs) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.75rem;
        position: relative;
        overflow: hidden;
        transition: border-color 0.16s ease, box-shadow 0.16s ease;
    }

    #leavePage .leave-stats > .stat-card::after {
        display: none !important;
        content: none !important;
    }

    #leavePage .leave-stats > .stat-card:hover {
        transform: none !important;
        border-color: #cbd5e1 !important;
        box-shadow: var(--lp-shadow-sm) !important;
    }

    #leavePage .leave-stats .stat-body {
        min-width: 0;
    }

    #leavePage .leave-stats .stat-card span {
        display: block;
        color: var(--lp-text-muted) !important;
        -webkit-text-fill-color: var(--lp-text-muted) !important;
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        line-height: 1.3;
    }

    #leavePage .leave-stats .stat-card strong {
        display: block;
        font-size: 1.45rem !important;
        font-weight: 700 !important;
        line-height: 1.15 !important;
        margin-top: 0.25rem;
        letter-spacing: -0.02em;
    }

    #leavePage .leave-stats .stat-card.stat-total strong {
        color: var(--lp-text) !important;
        -webkit-text-fill-color: var(--lp-text) !important;
    }

    #leavePage .leave-stats .stat-card.pending strong {
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    #leavePage .leave-stats .stat-card.approved strong {
        color: #16a34a !important;
        -webkit-text-fill-color: #16a34a !important;
    }

    #leavePage .leave-stats .stat-card.rejected strong {
        color: #dc2626 !important;
        -webkit-text-fill-color: #dc2626 !important;
    }

    #leavePage .leave-stats .stat-card.unpaid strong {
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
    }

    #leavePage .leave-stats .stat-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    #leavePage .leave-stats .stat-icon-box i {
        position: static !important;
        font-size: 0.95rem !important;
        filter: none !important;
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #leavePage .leave-stats .stat-total .stat-icon-box {
        background: #eff6ff;
        color: #2563eb !important;
    }

    #leavePage .leave-stats .pending .stat-icon-box {
        background: #fffbeb;
        color: #d97706 !important;
    }

    #leavePage .leave-stats .approved .stat-icon-box {
        background: #ecfdf5;
        color: #16a34a !important;
    }

    #leavePage .leave-stats .rejected .stat-icon-box {
        background: #fef2f2;
        color: #dc2626 !important;
    }

    #leavePage .leave-stats .unpaid .stat-icon-box {
        background: #eef2ff;
        color: #4f46e5 !important;
    }

    #leavePage .leave-stats .stat-icon-box i::before {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }

    /* ===== FILTER PANEL ===== */
    #leavePage .filter-panel {
        border-radius: 12px !important;
        background: var(--lp-surface) !important;
        border: 1px solid var(--lp-border) !important;
        padding: 0.95rem 1.2rem !important;
        margin-bottom: 1rem !important;
        box-shadow: var(--lp-shadow-xs) !important;
    }

    #leavePage .filter-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr)) auto;
        gap: 0.75rem;
        align-items: end;
    }

    #leavePage .filter-field {
        min-width: 0;
    }

    #leavePage label {
        display: block;
        font-size: 0.66rem !important;
        color: var(--lp-text-muted) !important;
        text-transform: uppercase;
        font-weight: 700 !important;
        letter-spacing: 0.05em;
        margin-bottom: 0.32rem !important;
    }

    #leavePage .form-control,
    .leave-ui-modal .form-control {
        min-height: 36px !important;
        height: 36px;
        border-radius: 8px !important;
        border: 1px solid var(--lp-border) !important;
        background: var(--lp-surface) !important;
        color: var(--lp-text) !important;
        font-size: 0.82rem !important;
        font-weight: 500 !important;
        padding: 0.3rem 0.65rem !important;
        width: 100%;
        box-shadow: none !important;
    }

    #leavePage textarea.form-control,
    .leave-ui-modal textarea.form-control {
        height: auto;
        min-height: 92px !important;
        padding: 0.55rem 0.75rem !important;
    }

    #leavePage .form-control:focus,
    .leave-ui-modal .form-control:focus {
        border-color: var(--lp-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }

    #leavePage .filter-actions {
        display: flex;
        gap: 0.45rem;
        align-items: center;
        flex-wrap: nowrap;
    }

    #leavePage .filter-actions .btn {
        min-height: 36px;
        padding: 0 0.9rem;
        font-size: 0.8rem;
    }

    /* ===== MAIN TABLE CARD ===== */
    #leavePage .table-card {
        border-radius: 14px !important;
        background: var(--lp-surface) !important;
        border: 1px solid var(--lp-border) !important;
        box-shadow: var(--lp-shadow-xs) !important;
        overflow: hidden;
        padding: 0 !important;
    }

    #leavePage .table-card:has(.pms-table-export.is-open) {
        overflow: visible !important;
    }

    #leavePage .table-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1rem 1.35rem !important;
        background: var(--lp-surface) !important;
        background-image: none !important;
        border-bottom: 1px solid var(--lp-border) !important;
    }

    #leavePage .table-head-info {
        min-width: 240px;
        flex: 1 1 260px;
    }

    #leavePage .table-head h2 {
        margin: 0 !important;
        font-size: 1rem !important;
        font-weight: 700 !important;
        color: var(--lp-text) !important;
        -webkit-text-fill-color: var(--lp-text) !important;
        line-height: 1.25;
    }

    #leavePage .table-head p {
        margin: 0.22rem 0 0 !important;
        color: var(--lp-text-muted) !important;
        -webkit-text-fill-color: var(--lp-text-muted) !important;
        font-size: 0.78rem !important;
        font-weight: 400 !important;
        max-width: 290px;
        line-height: 1.4;
    }

    #leavePage .table-tools-cluster {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.65rem;
        flex: 1 1 auto;
    }

    /* Integrated pms-table-tools inside header */
    #leavePage .leave-toolbar-host {
        display: flex;
        align-items: center;
    }

    #leavePage .leave-toolbar-host .pms-table-tools,
    #leavePage .table-card .pms-table-tools {
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        min-height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        width: auto !important;
        gap: 0.65rem !important;
        flex-wrap: wrap;
    }

    #leavePage .table-card .pms-table-tools__status {
        display: inline-flex;
        align-items: center;
        background: var(--lp-primary-soft) !important;
        border: 1px solid var(--lp-primary-border) !important;
        color: var(--lp-primary) !important;
        -webkit-text-fill-color: var(--lp-primary) !important;
        border-radius: 999px;
        padding: 0.24rem 0.7rem;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        margin-right: 0 !important;
    }

    #leavePage .table-card .pms-table-export {
        margin-left: 0 !important;
    }

    #leavePage .table-card .pms-table-export__toggle {
        background: var(--lp-surface) !important;
        border: 1px solid var(--lp-border) !important;
        color: var(--lp-text-body) !important;
        border-radius: 8px !important;
        min-height: 34px !important;
        height: 34px;
        padding: 0 0.72rem !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
    }

    #leavePage .table-card .pms-table-export__toggle:hover,
    #leavePage .table-card .pms-table-export.is-open .pms-table-export__toggle {
        background: var(--lp-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        color: var(--lp-text) !important;
        transform: none !important;
        box-shadow: none !important;
    }

    #leavePage .table-card .pms-table-export__toggle svg {
        color: inherit !important;
        stroke: currentColor !important;
    }

    #leavePage .table-tools {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.55rem;
    }

    #leavePage .entry-tools {
        display: inline-flex;
        align-items: center;
        gap: 0.42rem;
        padding: 0;
        border: 0;
        background: transparent;
        color: var(--lp-text-muted);
        font-size: 0.76rem;
        font-weight: 600;
        white-space: nowrap;
        margin: 0;
    }

    #leavePage .entry-tools label {
        margin: 0 !important;
        color: var(--lp-text-muted) !important;
        font-size: 0.7rem !important;
    }

    #leavePage .entry-tools .form-control {
        width: 66px !important;
        min-height: 34px !important;
        height: 34px !important;
        padding: 0.2rem 0.45rem !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
    }

    #leavePage .bulk-tools {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    #leavePage .bulk-tools .form-control {
        width: 146px !important;
        min-height: 34px !important;
        height: 34px !important;
        padding: 0.2rem 0.55rem !important;
        font-size: 0.8rem !important;
    }

    #leavePage #apply-action {
        min-height: 34px;
        height: 34px;
        padding: 0 0.85rem;
    }

    /* Flat Clean Table */
    #leavePage .table-responsive,
    #leavePage .table-card .pms-table-scroll-host {
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    #leavePage .leave-table {
        margin: 0 !important;
        width: 100%;
        min-width: 980px;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
    }

    #leavePage .leave-table thead th {
        padding: 0.75rem 0.9rem !important;
        background: var(--lp-surface-subtle) !important;
        color: var(--lp-text-muted) !important;
        -webkit-text-fill-color: var(--lp-text-muted) !important;
        font-size: 0.69rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-top: 0 !important;
        border-bottom: 1px solid var(--lp-border) !important;
        white-space: nowrap;
    }

    #leavePage .leave-table tbody td {
        padding: 0.85rem 0.9rem !important;
        background: var(--lp-surface) !important;
        border-top: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-bottom: 1px solid var(--lp-border-subtle) !important;
        border-radius: 0 !important;
        vertical-align: middle;
        color: var(--lp-text-body) !important;
        font-size: 0.82rem !important;
        font-weight: 500;
    }

    /* Preserve left gutter for pms-table-tools injected selection checkbox when no legacy checkbox column exists. */
    #leavePage .leave-table > :is(thead, tbody) > tr > .pms-table-selection-cell:not([data-pms-selection-column]) {
        position: relative;
        padding-left: 58px !important;
    }

    @media (max-width: 575.98px) {
        #leavePage .leave-table > :is(thead, tbody) > tr > .pms-table-selection-cell:not([data-pms-selection-column]) {
            padding-left: 52px !important;
        }
    }

    #leavePage .leave-table tbody tr:hover td,
    #leavePage .leave-table.pms-exportable-table > tbody > tr:hover > :is(th, td) {
        background: var(--lp-surface-hover) !important;
    }

    #leavePage .leave-table tbody tr.pms-row-selected td {
        background: var(--lp-primary-soft) !important;
    }

    /* Employee Cell with Avatar */
    #leavePage .leave-emp-cell {
        display: flex;
        align-items: center;
        gap: 0.68rem;
        min-width: 175px;
    }

    #leavePage .leave-emp-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
        /* The theme sets -webkit-text-fill-color on table cells; it wins over `color`, so the
           initials must set both or they render dark on the coloured circle. */
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        flex-shrink: 0;
        letter-spacing: 0.02em;
        line-height: 1;
        text-transform: uppercase;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.85), 0 2px 6px rgba(15, 23, 42, 0.12);
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #leavePage .leave-emp-avatar {
        box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.6), 0 2px 6px rgba(0, 0, 0, 0.3);
    }

    #leavePage .leave-emp-avatar.av-blue { background: #2563eb; }
    #leavePage .leave-emp-avatar.av-emerald { background: #059669; }
    #leavePage .leave-emp-avatar.av-indigo { background: #4f46e5; }
    #leavePage .leave-emp-avatar.av-amber { background: #d97706; }
    #leavePage .leave-emp-avatar.av-rose { background: #e11d48; }

    #leavePage .leave-emp-meta {
        min-width: 0;
    }

    #leavePage .leave-emp-meta strong,
    #leavePage .dates-cell strong,
    #leavePage .days-cell strong {
        display: block;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        color: var(--lp-text) !important;
        -webkit-text-fill-color: var(--lp-text) !important;
        line-height: 1.3;
    }

    #leavePage .leave-emp-meta small,
    #leavePage .dates-cell small,
    #leavePage .days-cell small,
    #leavePage .pay-sub {
        display: block;
        color: var(--lp-text-muted) !important;
        -webkit-text-fill-color: var(--lp-text-muted) !important;
        font-size: 0.72rem !important;
        font-weight: 400 !important;
        line-height: 1.35;
    }

    #leavePage .leave-emp-meta .emp-code {
        color: var(--lp-text-muted) !important;
        font-weight: 600 !important;
        font-size: 0.7rem !important;
    }

    /* Type Pill Badges with Dot */
    #leavePage .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.38rem;
        padding: 0.25rem 0.62rem;
        border-radius: 7px;
        font-weight: 600;
        font-size: 0.74rem;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    #leavePage .type-badge::before {
        content: '';
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
        flex-shrink: 0;
    }

    #leavePage .type-badge.type-casual {
        background: #fffbeb;
        color: #b45309;
        border-color: #fde68a;
    }

    #leavePage .type-badge.type-sick {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    #leavePage .type-badge.type-unpaid {
        background: #eef2ff;
        color: #4338ca;
        border-color: #c7d2fe;
    }

    #leavePage .type-badge.type-default {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #dbeafe;
    }

    /* Payment Badges */
    #leavePage .pay-cell {
        min-width: 118px;
    }

    #leavePage .pay-stack {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    #leavePage .pay-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.22rem 0.6rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.74rem;
        border: 1px solid transparent;
        white-space: nowrap;
        line-height: 1.2;
    }

    #leavePage .pay-badge i,
    #leavePage .pay-badge i::before {
        font-size: 0.68rem;
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #leavePage .pay-badge.paid {
        background: #ecfdf5;
        color: #15803d;
        border-color: #bbf7d0;
    }

    #leavePage .pay-badge.unpaid {
        background: #fff7ed;
        color: #c2410c;
        border-color: #fed7aa;
    }

    #leavePage .pay-badge.rejected {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    #leavePage .pay-badge.pending {
        background: var(--lp-surface-subtle);
        color: var(--lp-text-muted);
        border-color: var(--lp-border);
    }

    #leavePage .pay-split {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
        font-size: 0.7rem !important;
        color: var(--lp-text-muted) !important;
    }

    #leavePage .pay-split span + span::before {
        content: "·";
        margin-right: 5px;
        opacity: 0.7;
    }

    /* Status Pill & Select */
    #leavePage .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.68rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.74rem;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    #leavePage .status-badge.warning {
        background: #fffbeb;
        color: #b45309;
        border-color: #fde68a;
    }

    #leavePage .status-badge.success {
        background: #ecfdf5;
        color: #15803d;
        border-color: #bbf7d0;
    }

    #leavePage .status-badge.danger {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    #leavePage .status-change-form {
        margin: 0;
        min-width: 120px;
    }

    #leavePage .status-select {
        min-height: 30px !important;
        height: 30px !important;
        border-radius: 999px !important;
        font-size: 0.74rem !important;
        font-weight: 600 !important;
        cursor: pointer;
        padding: 0.15rem 1.75rem 0.15rem 0.68rem !important;
    }

    #leavePage .status-select.warning {
        background-color: #fffbeb !important;
        color: #b45309 !important;
        border-color: #fde68a !important;
    }

    #leavePage .status-select.success {
        background-color: #ecfdf5 !important;
        color: #15803d !important;
        border-color: #bbf7d0 !important;
    }

    #leavePage .status-select.danger {
        background-color: #fef2f2 !important;
        color: #b91c1c !important;
        border-color: #fecaca !important;
    }

    #leavePage .status-select.unpaid {
        background-color: #fff7ed !important;
        color: #c2410c !important;
        border-color: #fed7aa !important;
    }

    #leavePage .status-select.secondary {
        background-color: var(--lp-surface-subtle) !important;
        color: var(--lp-text-muted) !important;
        border-color: var(--lp-border) !important;
    }

    /* Reason Cell */
    #leavePage .reason-cell {
        max-width: 240px;
        min-width: 165px;
    }

    #leavePage .reason-text {
        display: block;
        color: var(--lp-text-body);
        font-size: 0.8rem;
        line-height: 1.4;
    }

    #leavePage .reason-sub {
        display: block;
        font-size: 0.7rem;
        margin-top: 2px;
    }

    /* Compact Row Actions */
    #leavePage .action-row {
        display: inline-flex;
        gap: 0.3rem;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    #leavePage .action-row .btn {
        min-height: 29px;
        width: 29px;
        height: 29px;
        padding: 0;
        border-radius: 7px;
        font-size: 0.74rem;
    }

    #leavePage .action-row .btn i,
    #leavePage .action-row .btn i::before {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #leavePage .action-row .btn-light {
        background: var(--lp-surface-subtle) !important;
        border: 1px solid var(--lp-border) !important;
        color: var(--lp-text-body) !important;
        -webkit-text-fill-color: var(--lp-text-body) !important;
    }

    #leavePage .action-row .btn-light:hover {
        background: var(--lp-surface) !important;
        border-color: #cbd5e1 !important;
        color: var(--lp-text) !important;
        -webkit-text-fill-color: var(--lp-text) !important;
    }

    #leavePage .action-row .btn-success {
        background: #ecfdf5 !important;
        border: 1px solid #a7f3d0 !important;
        color: #15803d !important;
    }

    #leavePage .action-row .btn-success:hover {
        background: #16a34a !important;
        color: #ffffff !important;
    }

    #leavePage .action-row .btn-warning {
        background: #fffbeb !important;
        border: 1px solid #fde68a !important;
        color: #b45309 !important;
    }

    #leavePage .action-row .btn-warning:hover {
        background: #d97706 !important;
        color: #ffffff !important;
    }

    #leavePage .action-row .btn-danger {
        background: #fef2f2 !important;
        border: 1px solid #fecaca !important;
        color: #dc2626 !important;
    }

    #leavePage .action-row .btn-danger:hover {
        background: #dc2626 !important;
        color: #ffffff !important;
    }

    #leavePage .action-row .btn-secondary {
        background: var(--lp-surface-subtle) !important;
        border: 1px solid var(--lp-border) !important;
        color: var(--lp-text-muted) !important;
    }

    /* Checkboxes */
    #leavePage input.form-check-input[type="checkbox"] {
        appearance: auto !important;
        -webkit-appearance: checkbox !important;
        accent-color: var(--lp-primary);
        width: 16px !important;
        height: 16px !important;
        min-width: 16px !important;
        min-height: 16px !important;
        margin: 0 !important;
        cursor: pointer;
    }

    /* Pagination */
    #leavePage .pagination-wrap {
        padding: 0.85rem 1.35rem;
        background: var(--lp-surface);
        border-top: 1px solid var(--lp-border);
    }

    #leavePage .pagination-wrap:empty {
        display: none;
    }

    #leavePage .pagination-wrap .pagination {
        margin: 0;
        display: flex;
        gap: 0.35rem;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    #leavePage .pagination-wrap .page-item .page-link {
        border-radius: 8px;
        border: 1px solid var(--lp-border);
        color: var(--lp-text-body);
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.38rem 0.72rem;
        background: var(--lp-surface);
    }

    #leavePage .pagination-wrap .page-item.active .page-link {
        background: var(--lp-primary);
        border-color: var(--lp-primary);
        color: #ffffff;
    }

    /* Empty State */
    #leavePage .empty-state {
        padding: 3.4rem 1.5rem;
        text-align: center;
    }

    #leavePage .empty-icon-box {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        background: var(--lp-primary-soft);
        border: 1px solid var(--lp-primary-border);
        color: var(--lp-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin-bottom: 0.9rem;
    }

    #leavePage .empty-icon-box i,
    #leavePage .empty-icon-box i::before,
    #leavePage .balance-drawer-icon i,
    #leavePage .balance-drawer-icon i::before {
        color: var(--lp-primary) !important;
        -webkit-text-fill-color: var(--lp-primary) !important;
    }

    #leavePage .empty-state h3 {
        color: var(--lp-text);
        font-size: 0.98rem;
        font-weight: 700;
        margin: 0 0 0.3rem 0;
    }

    #leavePage .empty-state p {
        color: var(--lp-text-muted);
        font-size: 0.8rem;
        margin: 0;
    }

    /* Balances Cards & Collapsible Admin Drawer */
    #leavePage .balance-drawer {
        margin-top: 1rem;
        background: var(--lp-surface);
        border: 1px solid var(--lp-border);
        border-radius: 14px;
        box-shadow: var(--lp-shadow-xs);
        overflow: hidden;
    }

    #leavePage .balance-drawer-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 1.35rem;
    }

    #leavePage .balance-drawer-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    #leavePage .balance-drawer-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: var(--lp-primary-soft);
        border: 1px solid var(--lp-primary-border);
        color: var(--lp-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    #leavePage .balance-drawer-title h3 {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--lp-text);
    }

    #leavePage .balance-drawer-title p {
        margin: 0;
        font-size: 0.74rem;
        color: var(--lp-text-muted);
    }

    #leavePage .balance-drawer-toggle i {
        transition: transform 0.2s ease;
    }

    #leavePage .balance-drawer-toggle[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    #leavePage .balance-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
        gap: 0.85rem;
    }

    #leavePage .balance-grid--personal {
        margin-bottom: 1rem;
    }

    #leavePage .balance-grid--drawer {
        padding: 0 1.35rem 1.2rem;
        border-top: 1px solid var(--lp-border-subtle);
        padding-top: 1rem;
    }

    #leavePage .balance-card {
        padding: 0.95rem 1.1rem;
        border-radius: 12px;
        background: var(--lp-surface);
        border: 1px solid var(--lp-border);
    }

    #leavePage .balance-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.7rem;
    }

    #leavePage .balance-head h3 {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--lp-text);
    }

    #leavePage .balance-head p {
        margin: 2px 0 0;
        color: var(--lp-text-muted);
        font-size: 0.74rem;
    }

    #leavePage .balance-head span {
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        color: var(--lp-primary);
        background: var(--lp-primary-soft);
        border: 1px solid var(--lp-primary-border);
        font-weight: 700;
        font-size: 0.74rem;
        white-space: nowrap;
    }

    #leavePage .balance-meter {
        height: 7px;
        border-radius: 999px;
        background: var(--lp-border-subtle);
        overflow: hidden;
        margin-bottom: 0.7rem;
    }

    #leavePage .balance-meter span {
        display: block;
        height: 100%;
        background: var(--lp-primary);
        border-radius: 999px;
    }

    #leavePage .balance-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-bottom: 0.65rem;
    }

    #leavePage .balance-pills span {
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.72rem;
        background: var(--lp-surface-subtle);
        color: var(--lp-text-body);
        border: 1px solid var(--lp-border);
    }

    /* Modals */
    .leave-ui-modal .modal-content {
        border-radius: 16px;
        border: 1px solid var(--lp-border);
        background: var(--lp-surface);
        color: var(--lp-text);
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }

    .leave-ui-modal .modal-header {
        padding: 1.1rem 1.4rem;
        background: var(--lp-surface);
        border-bottom: 1px solid var(--lp-border);
    }

    .leave-ui-modal .modal-header .modal-title {
        font-weight: 700;
        color: var(--lp-text);
        font-size: 1.05rem;
    }

    .leave-ui-modal .modal-body {
        padding: 1.35rem 1.4rem;
    }

    .leave-ui-modal .modal-footer {
        padding: 0.95rem 1.4rem;
        border-top: 1px solid var(--lp-border);
        gap: 0.55rem;
    }

    .leave-ui-modal .policy-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0.9rem;
    }

    .leave-ui-modal .policy-grid label,
    .leave-ui-modal .modal-body .form-label {
        font-size: 0.72rem;
        color: var(--lp-text-muted);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: block;
        margin-bottom: 0.3rem;
    }

    .leave-ui-modal .switch-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 0.65rem;
        margin-top: 1rem;
    }

    .leave-ui-modal .switch-line {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.7rem 0.9rem;
        border: 1px solid var(--lp-border);
        border-radius: 10px;
        background: var(--lp-surface-subtle);
        color: var(--lp-text);
        font-size: 0.84rem;
        font-weight: 500;
        cursor: pointer;
    }

    .leave-ui-modal .switch-line input[type="checkbox"] {
        width: 17px;
        height: 17px;
        accent-color: var(--lp-primary);
    }

    .leave-ui-modal .export-subtitle {
        margin: 4px 0 0;
        color: var(--lp-text-muted);
        font-size: 0.82rem;
    }

    .leave-ui-modal .export-layout {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) minmax(300px, 0.9fr);
        gap: 1.15rem;
    }

    .leave-ui-modal .export-filters,
    .leave-ui-modal .export-actions-panel {
        border: 1px solid var(--lp-border);
        border-radius: 12px;
        background: var(--lp-surface-subtle);
        padding: 1rem;
    }

    .leave-ui-modal .export-filters h6,
    .leave-ui-modal .export-actions-panel h6 {
        margin: 0 0 0.85rem;
        color: var(--lp-text);
        font-weight: 700;
        font-size: 0.9rem;
    }

    .leave-ui-modal .export-filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.8rem;
    }

    .leave-ui-modal .export-filter-grid > div:first-child {
        grid-column: 1 / -1;
    }

    .leave-ui-modal .export-action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .leave-ui-modal .export-tile {
        border: 1px solid var(--lp-border);
        border-radius: 12px;
        background: var(--lp-surface);
        min-height: 98px;
        padding: 0.85rem;
        text-align: left;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.35rem;
        color: var(--lp-text);
        cursor: pointer;
    }

    .leave-ui-modal .export-tile:hover {
        border-color: var(--lp-primary);
        box-shadow: var(--lp-shadow-sm);
    }

    .leave-ui-modal .export-tile i {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }

    .leave-ui-modal .export-tile span { font-size: 0.86rem; font-weight: 700; }
    .leave-ui-modal .export-tile small { color: var(--lp-text-muted); font-size: 0.74rem; }
    .leave-ui-modal .export-tile.copy i { background: #e0f2fe; color: #0369a1; }
    .leave-ui-modal .export-tile.excel i { background: #eff6ff; color: #2563eb; }
    .leave-ui-modal .export-tile.csv i { background: #fef3c7; color: #b45309; }
    .leave-ui-modal .export-tile.pdf i { background: #fee2e2; color: #b91c1c; }
    .leave-ui-modal .export-tile.print i { background: #ede9fe; color: #6d28d9; }
    .leave-ui-modal .export-copy-status { min-height: 20px; margin-top: 0.75rem; color: var(--lp-primary); font-weight: 600; font-size: 0.84rem; }

    /* Dark Mode Overrides for Tinted Pills & Icons */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .leave-stats .stat-total .stat-icon-box {
        background: rgba(59, 130, 246, 0.16); color: #60a5fa !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .leave-stats .pending .stat-icon-box {
        background: rgba(245, 158, 11, 0.16); color: #fbbf24 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .leave-stats .approved .stat-icon-box {
        background: rgba(16, 185, 129, 0.16); color: #34d399 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .leave-stats .rejected .stat-icon-box {
        background: rgba(239, 68, 68, 0.16); color: #f87171 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .leave-stats .unpaid .stat-icon-box {
        background: rgba(99, 102, 241, 0.18); color: #a5b4fc !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .type-badge.type-casual {
        background: rgba(245, 158, 11, 0.16); color: #fbbf24; border-color: rgba(251, 191, 36, 0.3);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .type-badge.type-sick {
        background: rgba(239, 68, 68, 0.16); color: #fca5a5; border-color: rgba(248, 113, 113, 0.3);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .type-badge.type-default {
        background: rgba(59, 130, 246, 0.16); color: #93c5fd; border-color: rgba(96, 165, 250, 0.3);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .pay-badge.paid,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .status-select.success,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .status-badge.success {
        background: rgba(16, 185, 129, 0.16) !important; color: #34d399 !important; -webkit-text-fill-color: #34d399 !important; border-color: rgba(52, 211, 153, 0.3) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .status-select.warning,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .status-badge.warning {
        background: rgba(245, 158, 11, 0.16) !important; color: #fbbf24 !important; -webkit-text-fill-color: #fbbf24 !important; border-color: rgba(251, 191, 36, 0.3) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .status-select.danger,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .status-badge.danger,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #leavePage .pay-badge.rejected {
        background: rgba(239, 68, 68, 0.16) !important; color: #f87171 !important; -webkit-text-fill-color: #f87171 !important; border-color: rgba(248, 113, 113, 0.3) !important;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        #leavePage .leave-stats {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        #leavePage .filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        #leavePage .filter-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 992px) {
        #leavePage.leave-page {
            padding: 16px 20px 28px !important;
        }

        #leavePage .leave-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        #leavePage .leave-hero-actions {
            max-width: none;
            width: 100%;
            justify-content: flex-start;
        }

        #leavePage .leave-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        #leavePage .filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .leave-ui-modal .export-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        #leavePage.leave-page {
            padding: 12px !important;
        }

        #leavePage .leave-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        #leavePage .filter-grid {
            grid-template-columns: 1fr;
        }

        #leavePage .table-head {
            flex-direction: column;
            align-items: stretch;
        }

        #leavePage .table-tools-cluster,
        #leavePage .table-tools,
        #leavePage .bulk-tools {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 576px) {
        #leavePage .leave-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

@push('js')
@if($isAdmin)
<script>
$(function () {
    @if(request('open_policy') || request('policy') || session('open_policy'))
        var policyModalEl = document.getElementById('policyModal');
        if (policyModalEl) {
            var policyModal = new bootstrap.Modal(policyModalEl);
            policyModal.show();
        }
    @endif

    $('#bulk-action').on('change', function () {
        $('#status-dropdown').toggle(this.value === 'change_status');
    });
    $('#select-all').on('change', function () {
        $('.leave-checkbox').prop('checked', this.checked);
    });
    $('.status-select').on('change', function () {
        const select = this;
        const label = select.options[select.selectedIndex].text;
        if (!confirm('Change this leave status to ' + label + '?')) {
            select.value = select.dataset.originalStatus;
            return;
        }
        select.form.submit();
    });
    $('#copyLeaveExport').on('click', function () {
        const button = $(this);
        const status = $('#exportCopyStatus');
        const query = $('#leaveExportForm').serialize() + '&type=copy';

        button.prop('disabled', true);
        status.text('Preparing clipboard data...');

        $.get('{{ route("leaves.export") }}?' + query)
            .done(function (text) {
                const fallbackCopy = function () {
                    const textarea = $('<textarea>').val(text).appendTo('body').select();
                    document.execCommand('copy');
                    textarea.remove();
                    status.text('Leave report copied to clipboard.');
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text)
                        .then(function () {
                            status.text('Leave report copied to clipboard.');
                        })
                        .catch(fallbackCopy);
                } else {
                    fallbackCopy();
                }
            })
            .fail(function () {
                status.text('Unable to copy leave report.');
            })
            .always(function () {
                button.prop('disabled', false);
            });
    });
    $('#apply-action').on('click', function () {
        const ids = $('.leave-checkbox:checked').map(function () { return this.value; }).get();
        const action = $('#bulk-action').val();
        const status = $('#status-dropdown').val();
        if (!ids.length || !action) { alert('Select leave requests and an action.'); return; }
        if (action === 'change_status' && !status) { alert('Select status.'); return; }
        if (!confirm('Apply this bulk action?')) return;
        $.post('{{ route("leaves.bulkAction") }}', {
            _token: '{{ csrf_token() }}',
            ids: ids,
            action: action,
            status: status
        }).done(function (response) {
            alert(response.message || 'Updated');
            location.reload();
        }).fail(function () {
            alert('Unable to apply bulk action.');
        });
    });

    $(document).on('click', '.btn-reject-trigger', function (e) {
        e.preventDefault();
        var targetId = $(this).data('bs-target') || $(this).data('target');
        if (targetId) {
            var modalEl = document.querySelector(targetId);
            if (modalEl) {
                if (window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                } else if (window.jQuery && $(modalEl).modal) {
                    $(modalEl).modal('show');
                }
            }
        }
    });
});
</script>
@endif
@endpush

@endsection
