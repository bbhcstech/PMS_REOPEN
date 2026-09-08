@extends('layouts.superadmin')

@section('title', 'Complaints & Support Center')

@push('styles')
<style>
  :root {
    --card-bg: rgba(255, 255, 255, 0.98);
    --border-subtle: #e2e8f0;
    --text-primary: #0f172a;
    --text-secondary: #334155;
    --text-muted: #475569;
    --emerald-main: #0f744c;
    --emerald-light: #10b981;
    --emerald-soft: #e4f3eb;
  }

  .complaints-wrapper {
    padding: 10px 0 40px;
  }

  /* Header Card */
  .page-header-card {
    background: linear-gradient(135deg, #073a26 0%, #0f744c 60%, #10b981 100%);
    border-radius: 16px;
    padding: 28px 32px;
    color: #ffffff;
    margin-bottom: 24px;
    box-shadow: 0 15px 35px -5px rgba(15, 116, 76, 0.25);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
  }

  .page-header-card h1 {
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 6px;
    letter-spacing: -0.3px;
  }

  .page-header-card p {
    font-size: 13.5px;
    opacity: 0.95;
    margin: 0;
  }

  /* KPI Grid */
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }

  .kpi-card {
    background: var(--card-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(15, 116, 76, 0.08);
    border-color: var(--emerald-light);
  }

  .kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
  }

  .kpi-val {
    font-size: 24px;
    font-weight: 800;
    line-height: 1.2;
    color: var(--text-primary);
  }

  .kpi-label {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Filter Toolbar */
  .filter-card {
    background: var(--card-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
  }

  .filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
  }

  .form-control-custom {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
    color: var(--text-primary);
    font-weight: 600;
    outline: none;
    transition: all 0.15s ease;
  }

  .form-control-custom:focus {
    border-color: var(--emerald-main);
    box-shadow: 0 0 0 3px rgba(15, 116, 76, 0.15);
  }

  /* Data Table */
  .table-card {
    background: var(--card-bg);
    border: 1px solid #cbd5e1;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
  }

  /* Responsive Table Scrollbar Line */
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-bottom: 2px solid #cbd5e1;
  }

  .table-responsive::-webkit-scrollbar {
    height: 10px;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 6px;
    border-top: 1px solid #cbd5e1;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: #0f744c;
    border-radius: 6px;
    border: 2px solid #f8fafc;
  }

  .table-responsive::-webkit-scrollbar-thumb:hover {
    background: #073a26;
  }

  .custom-table {
    width: 100%;
    min-width: 1200px;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
    border: 1px solid #cbd5e1;
  }

  .custom-table th {
    background: #f8fafc;
    padding: 14px 16px;
    font-weight: 800;
    color: #334155;
    border-bottom: 2px solid #cbd5e1;
    border-right: 1px solid #cbd5e1;
    text-transform: uppercase;
    font-size: 11.5px;
    letter-spacing: 0.6px;
  }

  .custom-table th:last-child {
    border-right: none;
  }

  .custom-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #cbd5e1;
    border-right: 1px solid #cbd5e1;
    color: var(--text-primary);
    vertical-align: middle;
  }

  .custom-table td:last-child {
    border-right: none;
  }

  .custom-table tbody tr:hover {
    background: rgba(248, 250, 252, 0.95);
  }

  .export-dropdown-menu a:hover {
    background: #f1f5f9;
  }

  /* High Contrast Status Badges */
  .badge-status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    text-transform: uppercase;
    white-space: nowrap;
    letter-spacing: 0.3px;
  }

  .status-OPEN { background: #eff6ff; color: #1d4ed8; border: 1px solid #93c5fd; }
  .status-IN_PROGRESS { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
  .status-WAITING_FOR_COMPANY { background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; }
  .status-RESOLVED { background: #ecfdf5; color: #047857; border: 1px solid #6ee7b7; }
  .status-CLOSED { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
  .status-REOPENED { background: #fff1f2; color: #be123c; border: 1px solid #fda4af; }

  /* High Contrast Priority Indicators */
  .badge-priority {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.5px;
    display: inline-block;
    text-transform: uppercase;
  }

  .prio-LOW { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
  .prio-MEDIUM { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
  .prio-HIGH { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
  .prio-CRITICAL { background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; animation: pulse 2s infinite; }

  /* Slide Drawer */
  .drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(4px);
    z-index: 999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
  }

  .drawer-overlay.active {
    opacity: 1;
    pointer-events: auto;
  }

  .slide-drawer {
    position: fixed;
    top: 0;
    right: 0;
    width: 620px;
    max-width: 90vw;
    height: 100vh;
    background: #ffffff;
    z-index: 1000;
    transform: translateX(100%);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: -10px 0 30px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
  }

  .drawer-overlay.active .slide-drawer {
    transform: translateX(0);
  }

  .drawer-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
  }

  .drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
  }

  .drawer-footer {
    padding: 16px 24px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
  }

  /* Timeline Feed */
  .timeline-feed {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin: 20px 0;
  }

  .timeline-item {
    display: flex;
    gap: 12px;
  }

  .timeline-item.super_admin {
    flex-direction: row-reverse;
  }

  .timeline-bubble {
    max-width: 82%;
    padding: 14px 16px;
    border-radius: 14px;
    font-size: 13px;
    line-height: 1.5;
  }

  .timeline-item.company_admin .timeline-bubble {
    background: #f1f5f9;
    color: #0f172a;
    border-bottom-left-radius: 2px;
  }

  .timeline-item.super_admin .timeline-bubble {
    background: var(--emerald-soft);
    color: #073a26;
    border: 1px solid rgba(15, 116, 76, 0.2);
    border-bottom-right-radius: 2px;
  }

  .timeline-meta {
    font-size: 11px;
    color: #475569;
    margin-bottom: 4px;
    font-weight: 700;
  }

  .btn-primary-emerald {
    background: linear-gradient(135deg, #073a26 0%, #0f744c 100%);
    color: #ffffff;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .btn-primary-emerald:hover {
    box-shadow: 0 6px 16px rgba(15, 116, 76, 0.3);
    color: #ffffff;
  }

  /* Custom Checkbox Styling */
  .custom-checkbox {
    width: 17px;
    height: 17px;
    accent-color: var(--emerald-main);
    cursor: pointer;
    border-radius: 4px;
    vertical-align: middle;
  }
</style>
@endpush

@section('content')
<div class="complaints-wrapper">
  
  <!-- Header Card -->
  <div class="page-header-card">
    <div>
      <h1>Complaints &amp; Support Center</h1>
      <p>Manage company complaints, support requests, technical issues, and service-related tickets across all tenants.</p>
    </div>
    <div>
      <span class="badge" style="background: rgba(255,255,255,0.2); padding: 8px 14px; border-radius: 20px; font-weight: 700; font-size: 12px;">
        <i class="bx bx-check-shield"></i> Live Database Connection
      </span>
    </div>
  </div>

  <!-- Top Statistics KPIs -->
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-icon" style="background: #eff6ff; color: #2563eb;">
        <i class="bx bx-receipt"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['total']) }}</div>
        <div class="kpi-label">Total Tickets</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #e0f2fe; color: #0284c7;">
        <i class="bx bx-folder-open"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['open']) }}</div>
        <div class="kpi-label">Open</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #fef3c7; color: #d97706;">
        <i class="bx bx-time-five"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['in_progress']) }}</div>
        <div class="kpi-label">In Progress</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #ecfdf5; color: #059669;">
        <i class="bx bx-check-circle"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['resolved']) }}</div>
        <div class="kpi-label">Resolved</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #ffe4e6; color: #e11d48;">
        <i class="bx bx-error-alt"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['critical']) }}</div>
        <div class="kpi-label">Critical</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #f3e8ff; color: #9333ea;">
        <i class="bx bx-user-x"></i>
      </div>
      <div>
        <div class="kpi-val">{{ number_format($kpis['unassigned']) }}</div>
        <div class="kpi-label">Unassigned</div>
      </div>
    </div>
  </div>

  <!-- Search & Filters Toolbar -->
  <div class="filter-card">
    <form method="GET" action="{{ route('superadmin.complaints.index') }}" class="filter-form" id="filterForm">
      
      <!-- Show Entries Selector -->
      <div style="display: flex; align-items: center; gap: 6px;">
        <label style="font-size: 13px; font-weight: 700; color: #334155; margin: 0;">Show</label>
        <select name="per_page" class="form-control-custom" onchange="this.form.submit()" style="width: auto; font-weight: 700;">
          <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 entries</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 entries</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 entries</option>
          <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 entries</option>
        </select>
      </div>

      <div style="flex: 1; min-width: 200px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Ticket ID, Company, Admin, Subject..." class="form-control-custom w-100" />
      </div>

      <div>
        <select name="status" class="form-control-custom" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>OPEN</option>
          <option value="IN PROGRESS" {{ request('status') === 'IN PROGRESS' ? 'selected' : '' }}>IN PROGRESS</option>
          <option value="WAITING FOR COMPANY" {{ request('status') === 'WAITING FOR COMPANY' ? 'selected' : '' }}>WAITING FOR COMPANY</option>
          <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>RESOLVED</option>
          <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
          <option value="REOPENED" {{ request('status') === 'REOPENED' ? 'selected' : '' }}>REOPENED</option>
        </select>
      </div>

      <div>
        <select name="priority" class="form-control-custom" onchange="this.form.submit()">
          <option value="">All Priorities</option>
          <option value="LOW" {{ request('priority') === 'LOW' ? 'selected' : '' }}>LOW</option>
          <option value="MEDIUM" {{ request('priority') === 'MEDIUM' ? 'selected' : '' }}>MEDIUM</option>
          <option value="HIGH" {{ request('priority') === 'HIGH' ? 'selected' : '' }}>HIGH</option>
          <option value="CRITICAL" {{ request('priority') === 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
        </select>
      </div>

      <div>
        <select name="category" class="form-control-custom" onchange="this.form.submit()">
          <option value="">All Categories</option>
          @foreach(['Technical Issue', 'Subscription', 'Billing', 'Account', 'Payroll', 'HR', 'Security', 'Performance', 'Feature Request', 'Bug Report', 'Other'] as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <select name="company_id" class="form-control-custom" onchange="this.form.submit()">
          <option value="">All Companies</option>
          @foreach($companies as $comp)
            <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <select name="sort" class="form-control-custom" onchange="this.form.submit()">
          <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Sort: Newest</option>
          <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Sort: Oldest</option>
          <option value="priority" {{ request('sort') === 'priority' ? 'selected' : '' }}>Sort: Priority</option>
          <option value="recently_updated" {{ request('sort') === 'recently_updated' ? 'selected' : '' }}>Sort: Recently Updated</option>
        </select>
      </div>

      <div style="display: flex; align-items: center; gap: 8px;">
        <button type="submit" class="btn-primary-emerald">
          <i class="bx bx-filter-alt"></i> Filter
        </button>
        @if(request()->anyFilled(['search', 'status', 'priority', 'category', 'company_id', 'sort', 'per_page']))
          <a href="{{ route('superadmin.complaints.index') }}" class="btn btn-sm btn-light" style="border:1px solid #cbd5e1; border-radius:8px; padding:8px 12px; font-weight:700; font-size:12px; text-decoration:none; color: #334155;">Reset</a>
        @endif

        <!-- Export Dropdown -->
        <div class="dropdown-export-wrapper" style="position: relative; display: inline-block;">
          <button type="button" onclick="toggleExportMenu(event)" class="btn-export-dropdown" style="background: #ffffff; border: 1px solid #0f744c; border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 700; color: #0f744c; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.2s ease;">
            <i class="bx bx-export" style="font-size: 16px;"></i> Export <i class="bx bx-chevron-down" style="font-size: 14px;"></i>
          </button>
          <div id="exportMenuDropdown" class="export-dropdown-menu" style="display: none; position: absolute; top: calc(100% + 4px); right: 0; min-width: 150px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.12); z-index: 100; overflow: hidden;">
            <a href="{{ route('superadmin.complaints.export', array_merge(request()->query(), ['export_format' => 'csv'])) }}" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; font-size: 13px; font-weight: 600; color: #334155; text-decoration: none;">
              <i class="bx bx-file" style="color: #10b981; font-size: 18px;"></i> CSV Export
            </a>
            <div style="height: 1px; background: #f1f5f9;"></div>
            <a href="{{ route('superadmin.complaints.export', array_merge(request()->query(), ['export_format' => 'pdf'])) }}" target="_blank" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; font-size: 13px; font-weight: 600; color: #334155; text-decoration: none;">
              <i class="bx bxs-file-pdf" style="color: #ef4444; font-size: 18px;"></i> PDF Export
            </a>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Bulk Selection Toolbar (Hidden by Default) -->
  <div id="selectionToolbar" style="display: none; background: #073a26; color: #ffffff; border-radius: 12px; padding: 12px 20px; margin-bottom: 16px; align-items: center; justify-content: space-between; box-shadow: 0 4px 14px rgba(7,58,38,0.25);">
    <div style="font-size: 13.5px; font-weight: 700;">
      <i class="bx bx-check-square me-1"></i> <span id="selectedCount">0</span> ticket(s) selected
    </div>
    <div style="display: flex; gap: 10px;">
      <button type="button" onclick="clearSelection()" class="btn btn-sm btn-outline-light" style="font-weight: 700; font-size: 12px; border-radius: 6px;">
        Clear Selection
      </button>
    </div>
  </div>

  <!-- Complaint Data Table -->
  <div class="table-card">
    <div class="table-responsive">
      <table class="custom-table">
        <thead>
          <tr>
            <th style="width: 38px; text-align: center;">
              <input type="checkbox" id="selectAllComplaints" class="custom-checkbox" onchange="toggleSelectAll(this)" title="Select All Tickets" />
            </th>
            <th>Ticket ID</th>
            <th>Company</th>
            <th>Raised By</th>
            <th>Subject</th>
            <th>Category</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Last Updated</th>
            <th>Assigned To</th>
            <th style="text-align: right; min-width: 90px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tickets as $ticket)
            @php
              $statusKey = str_replace(' ', '_', $ticket->status);
            @endphp
            <tr>
              <td style="text-align: center;">
                <input type="checkbox" class="ticket-checkbox custom-checkbox" value="{{ $ticket->id }}" onchange="updateSelectedCount()" />
              </td>
              <td>
                <a href="javascript:void(0)" onclick="openComplaintDrawer({{ $ticket->id }})" style="font-weight: 800; color: #0f744c; text-decoration: none; border-bottom: 2px solid #10b981; font-size: 13.5px; letter-spacing: -0.2px;">
                  #{{ $ticket->ticket_id }}
                </a>
              </td>
              <td>
                <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;">{{ $ticket->company?->name ?? 'Unknown Company' }}</div>
                <div style="font-size: 11px; font-weight: 600; color: #64748b;">{{ $ticket->company?->company_code ?? 'C-CODE' }}</div>
              </td>
              <td>
                <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;">{{ $ticket->raised_by_name }}</div>
                <div style="font-size: 12px; font-weight: 600; color: #475569;">{{ $ticket->raised_by_email }}</div>
              </td>
              <td style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $ticket->subject }}">
                <span style="font-weight: 700; color: #0f172a; font-size: 13.5px;">{{ $ticket->subject }}</span>
              </td>
              <td>
                <span class="badge" style="background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; font-weight: 700; font-size: 11.5px; padding: 5px 10px; border-radius: 6px; display: inline-block;">
                  {{ $ticket->category }}
                </span>
              </td>
              <td>
                <span class="badge-priority prio-{{ $ticket->priority }}">{{ $ticket->priority }}</span>
              </td>
              <td>
                <span class="badge-status status-{{ $statusKey }}">
                  <i class="bx bx-radio-circle-marked"></i> {{ str_replace('_', ' ', $statusKey) }}
                </span>
              </td>
              <td style="white-space: nowrap; font-size: 12.5px; font-weight: 600; color: #334155;">
                {{ $ticket->created_at?->format('d M Y, h:i A') }}
              </td>
              <td style="white-space: nowrap; font-size: 12.5px; font-weight: 600; color: #334155;">
                {{ $ticket->updated_at?->diffForHumans() }}
              </td>
              <td>
                @if($ticket->assigned_to_name && $ticket->assigned_to_name !== 'Unassigned')
                  <span class="badge" style="background: #f0fdf4; color: #166534; border: 1px solid #86efac; font-weight: 700; font-size: 11.5px; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                    <i class="bx bx-user-check"></i> {{ $ticket->assigned_to_name }}
                  </span>
                @else
                  <span class="badge" style="background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 700; font-size: 11.5px; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                    <i class="bx bx-user-x"></i> Unassigned
                  </span>
                @endif
              </td>
              <td style="text-align: right;">
                <button type="button" onclick="openComplaintDrawer({{ $ticket->id }})" class="btn-primary-emerald" style="padding: 6px 14px; font-size: 12.5px; font-weight: 700; border-radius: 8px;">
                  <i class="bx bx-show"></i> View
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="12" style="text-align: center; padding: 48px 16px; color: #64748b;">
                <i class="bx bx-inbox" style="font-size: 48px; opacity: 0.4; display: block; margin-bottom: 12px;"></i>
                <div style="font-size: 16px; font-weight: 700; color: #0f172a;">No complaints found</div>
                <div style="font-size: 13px;">No company support tickets match your search filters.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="padding: 16px 20px; border-top: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
      <div style="font-size: 13px; font-weight: 600; color: #475569;">
        Showing {{ $tickets->firstItem() ?? 0 }} to {{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }} entries
      </div>
      <div>
        {{ $tickets->links() }}
      </div>
    </div>
  </div>

</div>

<!-- SLIDE-OVER DETAIL DRAWER -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeComplaintDrawer()">
  <div class="slide-drawer" onclick="event.stopPropagation()">
    <div class="drawer-header">
      <div>
        <div id="drawerTicketId" style="font-size: 18px; font-weight: 900; color: var(--emerald-main);">#CMP-00000</div>
        <div id="drawerSubject" style="font-size: 13px; color: var(--text-secondary); font-weight: 600;">Ticket Subject</div>
      </div>
      <button type="button" onclick="closeComplaintDrawer()" style="font-size: 24px; color: var(--text-muted); cursor: pointer; border: none; background: none;">
        <i class="bx bx-x"></i>
      </button>
    </div>

    <div class="drawer-body" id="drawerContentBody">
      <div style="text-align: center; padding: 60px 0;">
        <div class="spinner-border text-success" role="status">
          <span class="visually-hidden">Loading ticket details...</span>
        </div>
        <div style="margin-top: 12px; font-size: 13px; color: var(--text-muted); font-weight: 600;">Fetching live database record...</div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSelectAll(master) {
  const checkboxes = document.querySelectorAll('.ticket-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = master.checked;
  });
  updateSelectedCount();
}

function updateSelectedCount() {
  const checked = document.querySelectorAll('.ticket-checkbox:checked');
  const count = checked.length;
  const toolbar = document.getElementById('selectionToolbar');
  const countDisplay = document.getElementById('selectedCount');

  if (count > 0) {
    toolbar.style.display = 'flex';
    countDisplay.innerText = count;
  } else {
    toolbar.style.display = 'none';
  }
}

function clearSelection() {
  const master = document.getElementById('selectAllComplaints');
  if (master) master.checked = false;
  
  const checkboxes = document.querySelectorAll('.ticket-checkbox');
  checkboxes.forEach(cb => cb.checked = false);
  updateSelectedCount();
}

function openComplaintDrawer(id) {
  const overlay = document.getElementById('drawerOverlay');
  const content = document.getElementById('drawerContentBody');

  overlay.classList.add('active');
  content.innerHTML = `
    <div style="text-align: center; padding: 60px 0;">
      <i class="bx bx-loader-alt bx-spin" style="font-size: 36px; color: var(--emerald-main);"></i>
      <div style="margin-top: 12px; font-size: 13px; color: #64748b; font-weight: 600;">Loading complaint details...</div>
    </div>
  `;

  fetch(`/superadmin/complaints/${id}`, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success && data.html) {
      content.innerHTML = data.html;
      document.getElementById('drawerTicketId').innerText = '#' + data.ticket.ticket_id;
      document.getElementById('drawerSubject').innerText = data.ticket.subject;
    } else {
      content.innerHTML = `<div class="alert alert-danger">Failed to load ticket details.</div>`;
    }
  })
  .catch(err => {
    content.innerHTML = `<div class="alert alert-danger">Error retrieving ticket data.</div>`;
  });
}

function closeComplaintDrawer() {
  document.getElementById('drawerOverlay').classList.remove('active');
}

function toggleExportMenu(e) {
  e.stopPropagation();
  const menu = document.getElementById('exportMenuDropdown');
  if (menu) {
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
  }
}

document.addEventListener('click', function(e) {
  const menu = document.getElementById('exportMenuDropdown');
  if (menu && !menu.contains(e.target)) {
    menu.style.display = 'none';
  }
});
</script>
@endpush
