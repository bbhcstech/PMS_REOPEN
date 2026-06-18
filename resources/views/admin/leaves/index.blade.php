@extends('admin.layout.app')

@section('title', $isAdmin ? 'Leave Management' : 'My Leaves')

@section('content')

<div class="leave-page">
    <!-- Breadcrumb -->
    <div class="leave-breadcrumb">
        <i class="fas fa-calendar-check"></i> Dashboard / {{ $isAdmin ? 'Leave Management' : 'My Leaves' }}
    </div>

    <!-- Header Card -->
    <section class="leave-hero">
        <div class="leave-hero-main">
            <div class="leave-hero-icon"><i class="fas fa-user-clock"></i></div>
            <div>
                <h1>{{ $isAdmin ? 'Leave Management' : 'My Leave Dashboard' }}</h1>
                <p>{{ $isAdmin ? 'Manage policy, balances, approvals, unpaid leaves, and employee leave history.' : 'Apply for leave, track approvals, and monitor your April-March balance.' }}</p>
            </div>
        </div>
        <div class="leave-hero-actions">
            <a href="{{ route('leaves.create') }}" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Apply Leave</a>
            @if(! $isAdmin)
                <a href="{{ route('leaves.apology-letters.create') }}" class="btn btn-light"><i class="fas fa-envelope-open-text"></i> Write Apology Letter</a>
            @endif
            <a href="{{ route('leaves.calendar') }}" class="btn btn-light"><i class="fas fa-calendar-alt"></i> Calendar</a>
            @if($isAdmin)
                <a href="{{ route('leaves.apology-letters.index') }}" class="btn btn-light"><i class="fas fa-envelope-open-text"></i> Apology Letters</a>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#policyModal"><i class="fas fa-sliders-h"></i> Policy</button>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#exportModal"><i class="fas fa-download"></i> Export</button>
                <a href="{{ route('leaves.archive') }}" class="btn btn-light">
                    <i class="fas fa-box-archive"></i> Archived
                    @if(($archivedCount ?? 0) > 0)
                        <span class="archive-count-badge">{{ $archivedCount }}</span>
                    @endif
                </a>
                <form method="POST" action="{{ route('leaves.archive-all') }}" class="d-inline" onsubmit="return confirm('Archive all active leave requests? They can be restored later.');">
                    @csrf
                    <button class="btn btn-light"><i class="fas fa-boxes-packing"></i> Archive All</button>
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
        <div class="notice-icon"><i class="fas fa-info-circle"></i></div>
        <p>{{ $policyNotice }}</p>
    </section>

    <!-- Stats Cards -->
    <section class="leave-stats">
        <div class="stat-card">
            <span>Total Requests</span>
            <strong>{{ $stats['total'] }}</strong>
            <i class="fas fa-list-check"></i>
        </div>
        <div class="stat-card pending">
            <span>Pending</span>
            <strong>{{ $stats['pending'] }}</strong>
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="stat-card approved">
            <span>Approved</span>
            <strong>{{ $stats['approved'] }}</strong>
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-card rejected">
            <span>Rejected</span>
            <strong>{{ $stats['rejected'] }}</strong>
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-card unpaid">
            <span>Unpaid</span>
            <strong>{{ $stats['unpaid'] }}</strong>
            <i class="fas fa-wallet"></i>
        </div>
    </section>

    <!-- Balance Cards -->
    <section class="balance-grid">
        @forelse($balances as $balance)
            <article class="balance-card">
                <div class="balance-head">
                    <div>
                        <h3>{{ $isAdmin ? ($balance->user?->name ?? 'Employee') : 'Your Balance' }}</h3>
                        <p>{{ $balance->leave_year ?: $balance->year }}</p>
                    </div>
                    <span>{{ $balance->remaining_leaves }} left</span>
                </div>
                <div class="balance-meter"><span style="width: {{ $balance->allocated_leaves > 0 ? min(100, ($balance->used_leaves / $balance->allocated_leaves) * 100) : 0 }}%"></span></div>
                <div class="balance-pills">
                    <span>SL {{ $balance->sick_allocated - $balance->sick_used }}/{{ $balance->sick_allocated }}</span>
                    <span>CL {{ $balance->casual_allocated - $balance->casual_used }}/{{ $balance->casual_allocated }}</span>
                    <span>ML {{ $balance->maternity_allocated - $balance->maternity_used }}/{{ $balance->maternity_allocated }}</span>
                    <span>Unpaid {{ $balance->unpaid_used }}</span>
                </div>
                @if($isAdmin)
                    <form method="POST" action="{{ route('leaves.reset-employee-leaves', $balance->user_id) }}" onsubmit="return confirm('Reset leave balance for {{ $balance->user?->name }}?');">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-rotate"></i> Reset Balance</button>
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
    </section>

    <!-- Filter Panel -->
    <section class="filter-panel">
        <form method="GET" action="{{ route('leaves.index') }}" class="filter-grid">
            <input type="hidden" name="per_page" value="{{ $perPage ?? request('per_page', 20) }}">
            @if($isAdmin)
                <div>
                    <label>Employee</label>
                    <select name="employee" class="form-control">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label>Leave Type</label>
                <select name="leave_type_id" class="form-control">
                    <option value="">All Types</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    @foreach(['pending','approved','rejected'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div>
                <label>To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('leaves.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
            </div>
        </form>
    </section>

    <!-- Table Card -->
    <section class="table-card">
        <div class="table-head">
            <div>
                <h2>Leave Requests</h2>
                <p>Approve, reject, convert to unpaid leave, and audit each request.</p>
            </div>
            @if($isAdmin)
                <div class="table-tools">
                    <form method="GET" action="{{ route('leaves.index') }}" class="entry-tools">
                        @foreach(request()->except(['page', 'per_page']) as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="per-page-select">Show</label>
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
        <div class="table-responsive">
            <table class="table leave-table" id="leaveTable">
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
                            $currentStatusValue = $leave->is_unpaid ? 'unpaid' : $leave->status;
                            $statusSelectClass = $currentStatusValue === 'unpaid' ? 'unpaid' : $statusClass;
                        @endphp
                        <tr>
                            @if($isAdmin)<td><input type="checkbox" class="form-check-input leave-checkbox" value="{{ $leave->id }}"></td>@endif
                            <td>
                                <strong>{{ $leave->user?->name ?? 'N/A' }}</strong>
                                <small>{{ $leave->user?->employeeDetail?->department?->dpt_name ?? $leave->user?->designation ?? 'Employee' }}</small>
                            </td>
                            <td><span class="type-badge">{{ $leave->type_label }}</span></td>
                            <td>
                                <strong>{{ optional($leave->start_date)->format('d M Y') }}</strong>
                                <small>{{ optional($leave->end_date)->format('d M Y') }}</small>
                            </td>
                            <td>{{ number_format((float) $leave->total_days, 1) }}</td>
                            <td>
                                @php
                                    $paidDays = (float) ($leave->paid_days ?? 0);
                                    $unpaidDays = (float) ($leave->unpaid_days ?? 0);
                                @endphp
                                <span class="pay-badge {{ $unpaidDays > 0 ? 'unpaid' : 'paid' }}">
                                    @if($paidDays > 0 && $unpaidDays > 0)
                                        Partial Unpaid
                                    @else
                                        {{ $unpaidDays > 0 ? 'Unpaid' : 'Paid' }}
                                    @endif
                                </span>
                                @if($paidDays > 0 || $unpaidDays > 0)
                                    <small>{{ number_format($paidDays, 1) }} paid / {{ number_format($unpaidDays, 1) }} unpaid</small>
                                @endif
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
                                            <option value="unpaid" {{ $currentStatusValue === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                        </select>
                                    </form>
                                @else
                                    <span class="status-badge {{ $statusClass }}">{{ ucfirst($leave->status) }}</span>
                                @endif
                            </td>
                            <td class="reason-cell">{{ \Illuminate\Support\Str::limit($leave->reason, 58) }}</td>
                            <td class="text-end">
                                <div class="action-row">
                                    <a href="{{ route('leaves.show', $leave->id) }}" class="btn btn-sm btn-light" title="View"><i class="fas fa-eye"></i></a>
                                    @if(! $isAdmin)
                                        <a href="{{ route('leaves.apology-letters.create', ['leave_id' => $leave->id]) }}" class="btn btn-sm btn-light" title="Write Apology Letter"><i class="fas fa-envelope-open-text"></i></a>
                                    @endif
                                    @if($leave->status === 'pending' || $isAdmin)
                                        <a href="{{ route('leaves.edit', $leave->id) }}" class="btn btn-sm btn-light" title="Edit"><i class="fas fa-pen"></i></a>
                                    @endif
                                    @if($isAdmin)
                                        <form method="POST" action="{{ route('leaves.updateStatus', $leave->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('leaves.updateStatus', $leave->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="unpaid">
                                        <button class="btn btn-sm btn-warning" title="Unpaid"><i class="fas fa-wallet"></i></button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}" title="Reject"><i class="fas fa-times"></i></button>
                                        <form method="POST" action="{{ route('leaves.archive.action', $leave->id) }}" class="d-inline" onsubmit="return confirm('Archive this leave request? It can be restored later.');">
                                            @csrf
                                            <button class="btn btn-sm btn-secondary" title="Archive"><i class="fas fa-box-archive"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 9 : 8 }}" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-calendar-times"></i><h3>No leave requests found</h3></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $leaves->links() }}</div>
    </section>
</div>

<!-- Reject Modals -->
@foreach($leaves as $leave)
<div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('leaves.updateStatus', $leave->id) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="rejected">
            <div class="modal-header">
                <h5 class="modal-title">Reject Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Rejection Reason</label>
                <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger">Reject</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- Policy Modal -->
@if($isAdmin)
<div class="modal fade" id="policyModal" tabindex="-1" aria-hidden="true">
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
                    <div><label>Maternity Leave Limit</label><input type="number" step="0.5" name="maternity_leave_limit" class="form-control" value="{{ $policy->maternity_leave_limit }}" required></div>
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
                        'maternity_is_paid' => 'Maternity leave is paid',
                        'maternity_requires_document' => 'Maternity document required',
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
                <button class="btn btn-primary">Save Policy</button>
            </div>
        </form>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form class="modal-content export-modal" id="leaveExportForm" method="GET" action="{{ route('leaves.export') }}">
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
@endif

<style>
    /* ===== PREMIUM LEAVE PAGE STYLES - LARGER TEXT & BRIGHTER ICONS ===== */
    .leave-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f0f9f4 0%, #e6f3ec 50%, #f4fbf7 100%);
        color: #0a2e1f;
        position: relative;
    }

    .leave-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.03) 0%, transparent 50%),
                    radial-gradient(circle at 100% 100%, rgba(52, 211, 153, 0.03) 0%, transparent 50%);
        pointer-events: none;
    }

    /* Breadcrumb */
    .leave-breadcrumb {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 26px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(16, 185, 129, 0.15);
        color: #0f744c;
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 28px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
        animation: fadeDown 0.4s ease;
    }

    .leave-breadcrumb i {
        color: #34d399;
        font-size: 1.3rem;
        filter: drop-shadow(0 0 2px rgba(52, 211, 153, 0.2));
    }

    @keyframes fadeDown {
        from { opacity: 0; transform: translateY(-16px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }

    /* Header Card */
    .leave-hero {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        border-radius: 28px;
        padding: 32px 36px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-shadow: 0 20px 40px -12px rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.12);
        margin-bottom: 28px;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        animation: fadeDown 0.5s ease;
    }

    .leave-hero:hover {
        box-shadow: 0 24px 48px -16px rgba(16, 185, 129, 0.18);
        border-color: rgba(16, 185, 129, 0.2);
        transform: translateY(-2px);
    }

    .leave-hero-main {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .leave-hero-icon {
        width: 80px;
        height: 80px;
        display: grid;
        place-items: center;
        border-radius: 24px;
        color: #fff;
        font-size: 38px;
        background: linear-gradient(145deg, #34d399, #059669);
        box-shadow: 0 12px 24px -8px rgba(5, 150, 105, 0.35);
        transition: all 0.3s ease;
        filter: brightness(1.1);
    }

    .leave-hero:hover .leave-hero-icon {
        transform: scale(1.03);
    }

    .leave-hero h1 {
        margin: 0 0 6px;
        font-size: 38px;
        font-weight: 800;
        color: #0a2e1f;
        letter-spacing: -0.5px;
    }

    .leave-hero p {
        margin: 0;
        color: #5a6e63;
        font-weight: 500;
        font-size: 1.1rem;
    }

    .leave-hero-actions {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        align-items: center;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: 14px;
        font-weight: 700;
        border: 0;
        min-height: 50px;
        padding: 0 28px;
        font-size: 1.05rem;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
    }

    .btn i {
        font-size: 1.15rem;
        filter: brightness(1.1);
    }

    .archive-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        margin-left: 2px;
        padding: 0 7px;
        border-radius: 999px;
        background: #ffedd5;
        color: #c2410c;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #059669);
        color: #fff;
        box-shadow: 0 8px 20px -6px rgba(5, 150, 105, 0.35);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(5, 150, 105, 0.45);
    }

    .btn-light {
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.18);
        font-weight: 700;
    }

    .btn-light:hover {
        background: #e6f3ec;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(16, 185, 129, 0.25);
        border-color: #34d399;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
        font-weight: 700;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .btn-success {
        background: linear-gradient(145deg, #34d399, #059669);
        color: #fff;
        min-height: 40px;
        padding: 0 16px;
        font-size: 0.95rem;
        border-radius: 12px;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }

    .btn-warning {
        background: linear-gradient(145deg, #fbbf24, #f59e0b);
        color: #fff;
        min-height: 40px;
        padding: 0 16px;
        font-size: 0.95rem;
        border-radius: 12px;
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    .btn-danger {
        background: linear-gradient(145deg, #ef4444, #dc2626);
        color: #fff;
        min-height: 40px;
        padding: 0 16px;
        font-size: 0.95rem;
        border-radius: 12px;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .btn-sm {
        min-height: 38px;
        padding: 0 16px;
        font-size: 0.9rem;
        border-radius: 10px;
    }

    /* Alerts */
    .alert {
        border-radius: 18px;
        border: none;
        padding: 18px 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        animation: fadeDown 0.4s ease;
        position: relative;
        z-index: 1;
        border-left: 5px solid;
        font-size: 1.05rem;
    }

    .alert-success {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        color: #065f46;
        border-left-color: #10b981;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        color: #991b1b;
        border-left-color: #ef4444;
    }

    .alert i {
        font-size: 1.4rem;
        margin-top: 2px;
        filter: brightness(1.1);
    }

    .alert ul {
        margin-bottom: 0;
        font-size: 1rem;
    }

    /* Policy Notice */
    .policy-notice {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 20px 26px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(16, 185, 129, 0.12);
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
        animation: fadeUp 0.5s ease;
    }

    .notice-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: #dbeafe;
        color: #2563eb;
        flex: 0 0 auto;
        font-size: 1.4rem;
        filter: brightness(1.1);
    }

    .policy-notice p {
        margin: 0;
        color: #475569;
        font-weight: 500;
        font-size: 1.05rem;
    }

    /* Stats Cards */
    .leave-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 18px;
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
        animation: fadeUp 0.55s ease;
    }

    .stat-card {
        position: relative;
        padding: 24px 22px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(16, 185, 129, 0.1);
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -12px rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card:nth-child(1)::after { background: linear-gradient(90deg, #34d399, #059669); }
    .stat-card:nth-child(2)::after { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .stat-card:nth-child(3)::after { background: linear-gradient(90deg, #10b981, #059669); }
    .stat-card:nth-child(4)::after { background: linear-gradient(90deg, #ef4444, #dc2626); }
    .stat-card:nth-child(5)::after { background: linear-gradient(90deg, #8b5cf6, #7c3aed); }

    .stat-card:hover::after {
        transform: scaleX(1);
    }

    .stat-card span {
        display: block;
        color: #6b7280;
        font-size: 0.9rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.06em;
    }

    .stat-card strong {
        display: block;
        font-size: 38px;
        font-weight: 800;
        color: #0a2e1f;
        line-height: 1.2;
        margin-top: 4px;
    }

    .stat-card i {
        position: absolute;
        right: 18px;
        bottom: 14px;
        color: rgba(16, 185, 129, 0.12);
        font-size: 42px;
        filter: brightness(1.2);
    }

    /* Balance Grid */
    .balance-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
        animation: fadeUp 0.6s ease;
    }

    .balance-card {
        padding: 24px 26px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(16, 185, 129, 0.1);
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .balance-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px -10px rgba(16, 185, 129, 0.12);
        border-color: rgba(16, 185, 129, 0.18);
    }

    .balance-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .balance-head h3 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 800;
        color: #0a2e1f;
    }

    .balance-head p {
        margin: 4px 0 0;
        color: #8ba198;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .balance-head span {
        padding: 8px 18px;
        border-radius: 999px;
        color: #047857;
        background: #d1fae5;
        font-weight: 800;
        font-size: 1rem;
        white-space: nowrap;
    }

    .balance-meter {
        height: 10px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .balance-meter span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #34d399, #059669);
        border-radius: 999px;
        transition: width 0.6s ease;
    }

    .balance-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 14px;
    }

    .balance-pills span {
        display: inline-flex;
        padding: 8px 16px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.9rem;
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.08);
    }

    /* Filter Panel */
    .filter-panel {
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(16, 185, 129, 0.1);
        padding: 24px 28px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
        animation: fadeUp 0.65s ease;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 18px;
        align-items: end;
    }

    .leave-page label {
        font-size: 0.85rem;
        color: #6b7280;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.06em;
        margin-bottom: 6px;
        display: block;
    }

    .leave-page .form-control {
        min-height: 48px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        font-weight: 500;
        font-size: 1rem;
        color: #0a2e1f;
        background: #ffffff;
        transition: all 0.2s ease;
        padding: 10px 16px;
        width: 100%;
    }

    .leave-page .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
        outline: none;
    }

    .filter-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-actions .btn {
        min-height: 48px;
        padding: 0 24px;
        font-size: 1rem;
    }

    /* Table Card */
    .table-card {
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(16, 185, 129, 0.1);
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
        animation: fadeUp 0.7s ease;
    }

    .table-card:hover {
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.06);
    }

    .table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 22px 28px;
        background: linear-gradient(135deg, #fafefb, #f0f9f4);
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
    }

    .table-head h2 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 700;
        color: #0a2e1f;
    }

    .table-head p {
        margin: 4px 0 0;
        color: #8ba198;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .table-tools {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 14px;
        flex-wrap: wrap;
    }

    .entry-tools {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border: 1px solid rgba(16, 185, 129, 0.12);
        border-radius: 12px;
        background: #ffffff;
        color: #475569;
        font-weight: 700;
        white-space: nowrap;
    }

    .entry-tools label {
        margin: 0;
        color: #475569;
        font-size: 0.82rem;
        letter-spacing: 0.04em;
    }

    .entry-tools span {
        font-size: 0.9rem;
    }

    .entry-tools .form-control {
        width: 82px;
        min-height: 36px;
        padding: 4px 28px 4px 10px;
        border-radius: 9px;
        font-weight: 800;
    }

    .bulk-tools {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .bulk-tools .form-control {
        min-height: 40px;
        padding: 6px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 500;
        color: #0a2e1f;
        background: #ffffff;
        transition: all 0.2s ease;
        width: 180px;
    }

    .bulk-tools .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
        outline: none;
    }

    /* Table Styling */
    .leave-table {
        margin: 0;
        font-size: 1rem;
        border-collapse: separate;
        border-spacing: 0 4px;
    }

    .leave-table thead th {
        padding: 16px 18px;
        background: #f8fafc !important;
        color: #5a6e63;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    .leave-table thead th i {
        filter: brightness(1.2);
        color: #34d399;
    }

    .leave-table tbody td {
        padding: 16px 18px;
        background: white;
        border-top: 1px solid rgba(16, 185, 129, 0.06);
        border-bottom: 1px solid rgba(16, 185, 129, 0.06);
        vertical-align: middle;
        color: #1e293b;
        font-weight: 500;
        font-size: 1rem;
    }

    .leave-table tbody td:first-child {
        border-left: 1px solid rgba(16, 185, 129, 0.06);
        border-radius: 12px 0 0 12px;
    }

    .leave-table tbody td:last-child {
        border-right: 1px solid rgba(16, 185, 129, 0.06);
        border-radius: 0 12px 12px 0;
    }

    .leave-table tbody tr {
        transition: all 0.2s ease;
    }

    .leave-table tbody tr:hover td {
        background: #fafefb;
        border-color: rgba(16, 185, 129, 0.12);
    }

    .leave-table td strong {
        display: block;
        font-size: 1.05rem;
        color: #0a2e1f;
        font-weight: 700;
    }

    .leave-table td small {
        display: block;
        color: #8ba198;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .leave-table td .type-badge {
        display: inline-flex;
        padding: 8px 16px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.9rem;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.1);
    }

    .leave-table td .pay-badge {
        display: inline-flex;
        padding: 8px 16px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.88rem;
        border: 1px solid transparent;
    }

    .leave-table td .pay-badge.paid {
        background: #d1fae5;
        color: #047857;
        border-color: rgba(16, 185, 129, 0.1);
    }

    .leave-table td .pay-badge.unpaid {
        background: #ffedd5;
        color: #c2410c;
        border-color: rgba(194, 65, 12, 0.1);
    }

    .leave-table td .status-badge {
        display: inline-flex;
        padding: 8px 16px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.88rem;
        border: 1px solid transparent;
    }

    .leave-table td .status-badge.warning {
        background: #fef3c7;
        color: #b45309;
        border-color: rgba(180, 83, 9, 0.1);
    }

    .leave-table td .status-badge.success {
        background: #d1fae5;
        color: #047857;
        border-color: rgba(16, 185, 129, 0.1);
    }

    .leave-table td .status-badge.danger {
        background: #fee2e2;
        color: #b91c1c;
        border-color: rgba(185, 28, 28, 0.1);
    }

    .leave-table td .status-change-form {
        margin: 0;
        min-width: 132px;
    }

    .leave-table td .status-select {
        min-height: 36px;
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: 0.86rem;
        font-weight: 700;
        cursor: pointer;
        padding: 6px 34px 6px 14px;
    }

    .leave-table td .status-select.warning {
        background-color: #fef3c7;
        color: #b45309;
        border-color: rgba(180, 83, 9, 0.1);
    }

    .leave-table td .status-select.success {
        background-color: #d1fae5;
        color: #047857;
        border-color: rgba(16, 185, 129, 0.1);
    }

    .leave-table td .status-select.danger {
        background-color: #fee2e2;
        color: #b91c1c;
        border-color: rgba(185, 28, 28, 0.1);
    }

    .leave-table td .status-select.unpaid {
        background-color: #ffedd5;
        color: #c2410c;
        border-color: rgba(194, 65, 12, 0.1);
    }

    .leave-table td .status-select.secondary {
        background-color: #f1f5f9;
        color: #475569;
        border-color: rgba(71, 85, 105, 0.1);
    }

    .leave-table td .reason-cell {
        max-width: 280px;
        color: #475467;
        font-size: 0.95rem;
        font-weight: 500;
    }

    /* Action Row */
    .action-row {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .action-row .btn {
        min-height: 36px;
        padding: 0 12px;
        font-size: 0.88rem;
        border-radius: 10px;
        font-weight: 600;
    }

    .action-row .btn i {
        font-size: 0.95rem;
        filter: brightness(1.1);
    }

    .action-row .btn-light {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .action-row .btn-light:hover {
        background: #e5e7eb;
        transform: translateY(-1px);
    }

    /* Checkboxes */
    .form-check-input {
        width: 20px;
        height: 20px;
        border: 2px solid #d1d9e6;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .form-check-input:checked {
        background-color: #059669;
        border-color: #059669;
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.15);
    }

    /* Pagination */
    .pagination-wrap {
        padding: 18px 28px;
        background: #fafefb;
        border-top: 1px solid rgba(16, 185, 129, 0.08);
    }

    .pagination-wrap .pagination {
        margin: 0;
        display: flex;
        gap: 6px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .pagination-wrap .page-item .page-link {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        font-size: 1rem;
        padding: 10px 18px;
        background: #ffffff;
        transition: all 0.2s ease;
    }

    .pagination-wrap .page-item.active .page-link {
        background: linear-gradient(145deg, #34d399, #059669);
        border-color: #059669;
        color: white;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
    }

    .pagination-wrap .page-item .page-link:hover:not(.active) {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    /* Empty State */
    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }

    .empty-state i {
        font-size: 52px;
        color: #a7f3d0;
        margin-bottom: 16px;
        display: block;
        filter: brightness(1.1);
    }

    .empty-state h3 {
        color: #0f744c;
        font-weight: 700;
        font-size: 1.3rem;
        margin: 0;
    }

    /* Switch Grid */
    .switch-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 12px;
        margin-top: 20px;
    }

    .switch-line {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        text-transform: none;
        font-size: 1rem;
        font-weight: 500;
        color: #172033;
        background: #fafbfc;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .switch-line:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .switch-line input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: #059669;
        cursor: pointer;
        flex-shrink: 0;
    }

    /* Policy Grid */
    .policy-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
    }

    .policy-grid label {
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        display: block;
        margin-bottom: 4px;
    }

    .policy-grid .form-control {
        min-height: 46px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        font-weight: 500;
        font-size: 1rem;
        color: #0a2e1f;
        background: #ffffff;
        transition: all 0.2s ease;
        padding: 10px 16px;
        width: 100%;
    }

    .policy-grid .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
        outline: none;
    }

    /* Modal */
    .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: 0 24px 48px rgba(0, 0, 0, 0.12);
        overflow: hidden;
    }

    .modal-header {
        padding: 22px 28px;
        background: linear-gradient(135deg, #fafefb, #f0f9f4);
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
    }

    .modal-header .modal-title {
        font-weight: 700;
        color: #0a2e1f;
        font-size: 1.25rem;
    }

    .modal-header .modal-title i {
        filter: brightness(1.1);
    }

    .modal-body {
        padding: 28px;
    }

    .modal-body .form-label {
        font-size: 0.9rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .modal-body .form-control {
        min-height: 46px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        font-weight: 500;
        font-size: 1rem;
        color: #0a2e1f;
        background: #ffffff;
        transition: all 0.2s ease;
        padding: 10px 16px;
        width: 100%;
    }

    .modal-body .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
        outline: none;
    }

    .modal-footer {
        padding: 18px 28px;
        border-top: 1px solid rgba(16, 185, 129, 0.08);
        gap: 12px;
    }

    .modal-footer .btn {
        min-height: 48px;
        padding: 0 28px;
        font-size: 1rem;
    }

    .export-modal .modal-header {
        align-items: flex-start;
    }

    .export-subtitle {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 0.94rem;
        font-weight: 500;
        text-transform: none;
        letter-spacing: 0;
    }

    .export-layout {
        display: grid;
        grid-template-columns: minmax(280px, 1fr) minmax(320px, 0.9fr);
        gap: 22px;
    }

    .export-filters,
    .export-actions-panel {
        border: 1px solid rgba(16, 185, 129, 0.1);
        border-radius: 18px;
        background: #fbfefc;
        padding: 20px;
    }

    .export-filters h6,
    .export-actions-panel h6 {
        margin: 0 0 16px;
        color: #0a2e1f;
        font-weight: 800;
        font-size: 1rem;
    }

    .export-filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .export-filter-grid > div:first-child {
        grid-column: 1 / -1;
    }

    .export-action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .export-tile {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        min-height: 116px;
        padding: 16px;
        text-align: left;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
        color: #172033;
    }

    .export-tile:hover {
        transform: translateY(-2px);
        border-color: #34d399;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
    }

    .export-tile i {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .export-tile span {
        font-size: 1.02rem;
        font-weight: 800;
    }

    .export-tile small {
        color: #64748b;
        font-weight: 600;
    }

    .export-tile.copy i {
        background: #e0f2fe;
        color: #0369a1;
    }

    .export-tile.excel i {
        background: #d1fae5;
        color: #047857;
    }

    .export-tile.csv i {
        background: #fef3c7;
        color: #b45309;
    }

    .export-tile.pdf i {
        background: #fee2e2;
        color: #b91c1c;
    }

    .export-tile.print i {
        background: #ede9fe;
        color: #6d28d9;
    }

    .export-copy-status {
        min-height: 22px;
        margin-top: 16px;
        color: #047857;
        font-weight: 700;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .leave-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 992px) {
        .leave-page {
            padding: 20px 25px;
        }

        .leave-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .leave-hero-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .leave-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .leave-page {
            padding: 16px;
        }

        .leave-hero {
            padding: 20px;
        }

        .leave-hero-icon {
            width: 60px;
            height: 60px;
            font-size: 28px;
        }

        .leave-hero h1 {
            font-size: 28px;
        }

        .leave-hero p {
            font-size: 0.95rem;
        }

        .leave-stats {
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .stat-card {
            padding: 16px;
        }

        .stat-card strong {
            font-size: 30px;
        }

        .stat-card i {
            font-size: 30px;
            right: 12px;
            bottom: 10px;
        }

        .balance-grid {
            grid-template-columns: 1fr;
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            flex-direction: column;
            width: 100%;
        }

        .filter-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .table-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .table-tools,
        .bulk-tools {
            width: 100%;
        }

        .entry-tools {
            width: 100%;
            justify-content: space-between;
        }

        .bulk-tools .form-control {
            width: 100%;
        }

        .leave-table tbody td {
            padding: 12px 14px;
            font-size: 0.9rem;
        }

        .leave-table thead th {
            padding: 12px 14px;
            font-size: 0.8rem;
        }

        .policy-grid {
            grid-template-columns: 1fr 1fr;
        }

        .export-layout {
            grid-template-columns: 1fr;
        }

        .switch-grid {
            grid-template-columns: 1fr;
        }

        .btn {
            font-size: 0.95rem;
            min-height: 44px;
            padding: 0 20px;
        }
    }

    @media (max-width: 576px) {
        .leave-page {
            padding: 12px;
        }

        .leave-hero {
            padding: 16px;
            border-radius: 20px;
        }

        .leave-hero-icon {
            width: 50px;
            height: 50px;
            font-size: 22px;
            border-radius: 18px;
        }

        .leave-hero h1 {
            font-size: 24px;
        }

        .leave-hero p {
            font-size: 0.85rem;
        }

        .leave-stats {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stat-card {
            padding: 14px;
        }

        .stat-card strong {
            font-size: 24px;
        }

        .stat-card span {
            font-size: 0.75rem;
        }

        .stat-card i {
            font-size: 24px;
            right: 10px;
            bottom: 8px;
        }

        .table-card {
            border-radius: 20px;
        }

        .table-head {
            padding: 14px 16px;
        }

        .table-head h2 {
            font-size: 1.1rem;
        }

        .leave-table tbody td {
            padding: 10px 12px;
            font-size: 0.85rem;
        }

        .leave-table td strong {
            font-size: 0.9rem;
        }

        .leave-table td small {
            font-size: 0.75rem;
        }

        .action-row .btn {
            min-height: 32px;
            padding: 0 10px;
            font-size: 0.8rem;
        }

        .policy-grid {
            grid-template-columns: 1fr;
        }

        .export-filter-grid,
        .export-action-grid {
            grid-template-columns: 1fr;
        }

        .pagination-wrap {
            padding: 12px 16px;
        }

        .pagination-wrap .page-item .page-link {
            font-size: 0.85rem;
            padding: 8px 14px;
        }

        .modal-body {
            padding: 16px;
        }

        .modal-header .modal-title {
            font-size: 1.1rem;
        }

        .btn {
            font-size: 0.85rem;
            min-height: 40px;
            padding: 0 16px;
        }
    }
</style>

<style>
    /* Dark mode support */
    html[data-pms-theme="dark"] .leave-page {
        background: linear-gradient(135deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .leave-breadcrumb {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .leave-breadcrumb i {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .leave-hero {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .leave-hero h1 {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .leave-hero p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .btn-light {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-light:hover {
        background: #1f3d30;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .policy-notice {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .policy-notice p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .stat-card {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .stat-card strong {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .balance-card {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .balance-head h3 {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .balance-head p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .balance-head span {
        background: #183026;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .balance-pills span {
        background: #183026;
        color: #34d399;
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .filter-panel {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .leave-page .form-control {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .leave-page .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
    }

    html[data-pms-theme="dark"] .leave-page label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .table-card {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .table-head {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .table-head h2 {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .table-head p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .entry-tools {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.12);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .entry-tools label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .leave-table thead th {
        background: #0d1b14 !important;
        color: #8ba198 !important;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .leave-table tbody td {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .leave-table tbody tr:hover td {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .leave-table td strong {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .leave-table td .type-badge {
        background: #183026;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .leave-table td .pay-badge.paid {
        background: #183026;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .leave-table td .pay-badge.unpaid {
        background: #1a1a0d;
        color: #fbbf24;
    }

    html[data-pms-theme="dark"] .leave-table td .status-badge.warning {
        background: #1a1a0d;
        color: #fbbf24;
    }

    html[data-pms-theme="dark"] .leave-table td .status-badge.success {
        background: #183026;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .leave-table td .status-badge.danger {
        background: #1a0d0d;
        color: #fca5a5;
    }

    html[data-pms-theme="dark"] .leave-table td .status-select.warning {
        background: #1a1a0d;
        color: #fbbf24;
    }

    html[data-pms-theme="dark"] .leave-table td .status-select.success {
        background: #183026;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .leave-table td .status-select.danger {
        background: #1a0d0d;
        color: #fca5a5;
    }

    html[data-pms-theme="dark"] .leave-table td .status-select.unpaid {
        background: #1a1a0d;
        color: #fbbf24;
    }

    html[data-pms-theme="dark"] .leave-table td .status-select.secondary {
        background: #183026;
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .leave-table td .reason-cell {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .action-row .btn-light {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.1);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .action-row .btn-light:hover {
        background: #183026;
        border-color: #34d399;
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .pagination-wrap {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .pagination-wrap .page-item .page-link {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.12);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .pagination-wrap .page-item.active .page-link {
        background: linear-gradient(145deg, #34d399, #059669);
        border-color: #059669;
        color: white;
    }

    html[data-pms-theme="dark"] .pagination-wrap .page-item .page-link:hover:not(.active) {
        background: #183026;
        border-color: #34d399;
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .btn-secondary {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-secondary:hover {
        background: #1f3d30;
    }

    html[data-pms-theme="dark"] .modal-content {
        background: #102119;
    }

    html[data-pms-theme="dark"] .modal-header {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .modal-header .modal-title {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .modal-body .form-control {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .modal-body .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
    }

    html[data-pms-theme="dark"] .modal-body label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .modal-footer {
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .export-subtitle,
    html[data-pms-theme="dark"] .export-tile small {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .export-filters,
    html[data-pms-theme="dark"] .export-actions-panel {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .export-filters h6,
    html[data-pms-theme="dark"] .export-actions-panel h6,
    html[data-pms-theme="dark"] .export-tile {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .export-tile {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .export-tile:hover {
        border-color: #34d399;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.2);
    }

    html[data-pms-theme="dark"] .export-copy-status {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .switch-line {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.12);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .switch-line:hover {
        background: #102119;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .policy-grid .form-control {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .policy-grid .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
    }

    html[data-pms-theme="dark"] .policy-grid label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .bulk-tools .form-control {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.12);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .bulk-tools .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
    }

    html[data-pms-theme="dark"] .empty-state h3 {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .empty-state i {
        color: #183026;
    }

    html[data-pms-theme="dark"] .alert-danger {
        background: #1a0d0d;
        color: #fca5a5;
        border-left-color: #ef4444;
    }

    html[data-pms-theme="dark"] .alert-success {
        background: #0d1b14;
        color: #34d399;
        border-left-color: #34d399;
    }
</style>

@push('js')
@if($isAdmin)
<script>
$(function () {
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
});
</script>
@endif
@endpush

@endsection
