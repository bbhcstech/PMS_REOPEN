@extends('admin.layout.app')

@section('content')
<div class="container-fluid px-4 py-4">

  <!-- Header Banner -->
  <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 16px; color: #fff;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h4 class="fw-bold mb-1 text-white"><i class="bx bx-support me-2"></i>Platform Support &amp; Complaints</h4>
        <p class="mb-0 text-white-50 fs-7">Submit support tickets, report issues, and request help directly from Super Admin.</p>
      </div>
      <div>
        <a href="{{ route('admin.company-complaints.create') }}" class="btn btn-emerald px-4 py-2 fw-bold text-white shadow-sm" style="background: #10b981; border: none; border-radius: 10px;">
          <i class="bx bx-plus-circle me-1"></i> Raise a Complaint
        </a>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background: #ecfdf5; color: #065f46;">
      <i class="bx bx-check-circle me-2 fs-5 align-middle"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- KPI Stat Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-2 col-sm-6">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
        <div class="card-body p-3 text-center">
          <div class="fs-7 text-muted text-uppercase fw-bold mb-1">Total Tickets</div>
          <div class="fs-3 fw-bolder text-dark">{{ number_format($kpis['total']) }}</div>
        </div>
      </div>
    </div>

    <div class="col-md-2 col-sm-6">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
        <div class="card-body p-3 text-center">
          <div class="fs-7 text-primary text-uppercase fw-bold mb-1">Open</div>
          <div class="fs-3 fw-bolder text-primary">{{ number_format($kpis['open']) }}</div>
        </div>
      </div>
    </div>

    <div class="col-md-2 col-sm-6">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
        <div class="card-body p-3 text-center">
          <div class="fs-7 text-warning text-uppercase fw-bold mb-1">In Progress</div>
          <div class="fs-3 fw-bolder text-warning">{{ number_format($kpis['in_progress']) }}</div>
        </div>
      </div>
    </div>

    <div class="col-md-2 col-sm-6">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
        <div class="card-body p-3 text-center">
          <div class="fs-7 text-purple text-uppercase fw-bold mb-1" style="color: #9333ea;">Waiting Reply</div>
          <div class="fs-3 fw-bolder" style="color: #9333ea;">{{ number_format($kpis['waiting']) }}</div>
        </div>
      </div>
    </div>

    <div class="col-md-2 col-sm-6">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
        <div class="card-body p-3 text-center">
          <div class="fs-7 text-success text-uppercase fw-bold mb-1">Resolved</div>
          <div class="fs-3 fw-bolder text-success">{{ number_format($kpis['resolved']) }}</div>
        </div>
      </div>
    </div>

    <div class="col-md-2 col-sm-6">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
        <div class="card-body p-3 text-center">
          <div class="fs-7 text-secondary text-uppercase fw-bold mb-1">Closed</div>
          <div class="fs-3 fw-bolder text-secondary">{{ number_format($kpis['closed']) }}</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter & Search Toolbar -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('admin.company-complaints.index') }}" class="row g-2 align-items-center">
        
        <div class="col-md-2 d-flex align-items-center gap-2">
          <label class="form-label mb-0 fw-bold text-dark fs-7">Show</label>
          <select name="per_page" class="form-select form-select-sm fw-bold" style="border-radius: 8px;" onchange="this.form.submit()">
            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
          </select>
          <span class="fs-7 text-muted fw-bold">entries</span>
        </div>

        <div class="col-md-4">
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Ticket ID, Subject, Category..." class="form-control form-control-sm" style="border-radius: 8px;" />
        </div>

        <div class="col-md-2">
          <select name="status" class="form-select form-select-sm fw-bold" style="border-radius: 8px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>OPEN</option>
            <option value="IN PROGRESS" {{ request('status') === 'IN PROGRESS' ? 'selected' : '' }}>IN PROGRESS</option>
            <option value="WAITING FOR COMPANY" {{ request('status') === 'WAITING FOR COMPANY' ? 'selected' : '' }}>WAITING FOR COMPANY</option>
            <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>RESOLVED</option>
            <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
          </select>
        </div>

        <div class="col-md-2">
          <select name="priority" class="form-select form-select-sm fw-bold" style="border-radius: 8px;" onchange="this.form.submit()">
            <option value="">All Priorities</option>
            <option value="LOW" {{ request('priority') === 'LOW' ? 'selected' : '' }}>LOW</option>
            <option value="MEDIUM" {{ request('priority') === 'MEDIUM' ? 'selected' : '' }}>MEDIUM</option>
            <option value="HIGH" {{ request('priority') === 'HIGH' ? 'selected' : '' }}>HIGH</option>
            <option value="CRITICAL" {{ request('priority') === 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
          </select>
        </div>

        <div class="col-md-2 text-end">
          <button type="submit" class="btn btn-sm btn-dark w-100 fw-bold" style="border-radius: 8px;">Filter</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bulk Selection Toolbar -->
  <div id="companySelectionToolbar" style="display: none; background: #0f172a; color: #ffffff; border-radius: 12px; padding: 10px 18px; margin-bottom: 16px; align-items: center; justify-content: space-between;">
    <div class="fs-7 fw-bold">
      <i class="bx bx-check-square me-1"></i> <span id="companySelectedCount">0</span> ticket(s) selected
    </div>
    <div>
      <button type="button" onclick="clearCompanySelection()" class="btn btn-sm btn-outline-light fw-bold" style="font-size: 11px; border-radius: 6px;">
        Clear Selection
      </button>
    </div>
  </div>

  <!-- Complaints Table -->
  <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
        <thead class="bg-light text-uppercase fs-8 fw-bold text-dark">
          <tr>
            <th style="width: 38px; text-align: center;" class="ps-3">
              <input type="checkbox" id="selectAllCompanyComplaints" onchange="toggleSelectAllCompany(this)" style="accent-color: #10b981; width: 16px; height: 16px; cursor: pointer;" />
            </th>
            <th>Ticket ID</th>
            <th>Subject</th>
            <th>Category</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Created</th>
            <th>Last Updated</th>
            <th class="pe-4 text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tickets as $ticket)
            @php
              $statusBadge = match($ticket->status) {
                'OPEN' => 'bg-primary-subtle text-primary border border-primary',
                'IN PROGRESS' => 'bg-warning-subtle text-dark border border-warning',
                'WAITING FOR COMPANY' => 'bg-purple-subtle text-purple border border-purple',
                'RESOLVED' => 'bg-success-subtle text-success border border-success',
                'CLOSED' => 'bg-secondary-subtle text-secondary border border-secondary',
                'REOPENED' => 'bg-danger-subtle text-danger border border-danger',
                default => 'bg-light text-dark'
              };

              $prioBadge = match($ticket->priority) {
                'CRITICAL' => 'bg-danger text-white',
                'HIGH' => 'bg-warning text-dark',
                'MEDIUM' => 'bg-info text-white',
                default => 'bg-secondary text-white'
              };
            @endphp
            <tr>
              <td style="text-align: center;" class="ps-3">
                <input type="checkbox" class="company-ticket-checkbox" value="{{ $ticket->id }}" onchange="updateCompanySelectedCount()" style="accent-color: #10b981; width: 16px; height: 16px; cursor: pointer;" />
              </td>
              <td>
                <a href="{{ route('admin.company-complaints.show', $ticket->id) }}" class="fw-bolder text-decoration-none text-success" style="font-size: 14px;">
                  #{{ $ticket->ticket_id }}
                </a>
              </td>
              <td>
                <div class="fw-bold text-dark fs-6">{{ $ticket->subject }}</div>
                <div class="text-muted fs-7 fw-semibold">Raised by: {{ $ticket->raised_by_name }}</div>
              </td>
              <td>
                <span class="badge bg-light text-dark border fw-bold px-2 py-1">{{ $ticket->category }}</span>
              </td>
              <td>
                <span class="badge {{ $prioBadge }} fs-8 fw-bold px-2 py-1">{{ $ticket->priority }}</span>
              </td>
              <td>
                <span class="badge {{ $statusBadge }} px-3 py-1 fs-8 fw-bold">
                  {{ $ticket->status }}
                </span>
              </td>
              <td class="text-dark fw-semibold fs-7">{{ $ticket->created_at?->format('d M Y, h:i A') }}</td>
              <td class="text-dark fw-semibold fs-7">{{ $ticket->updated_at?->diffForHumans() }}</td>
              <td class="pe-4 text-end">
                <a href="{{ route('admin.company-complaints.show', $ticket->id) }}" class="btn btn-sm btn-outline-dark fw-bold" style="border-radius: 8px;">
                  View &amp; Reply
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">
                <i class="bx bx-inbox fs-1 d-block mb-2 opacity-50"></i>
                <div class="fw-bold fs-6 text-dark">No support complaints found</div>
                <p class="fs-7 mb-0">Have an issue or need assistance? Click "Raise a Complaint" above to submit a ticket to Super Admin.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div class="fs-7 fw-semibold text-muted">
        Showing {{ $tickets->firstItem() ?? 0 }} to {{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }} entries
      </div>
      <div>
        {{ $tickets->links() }}
      </div>
    </div>
  </div>

</div>

<script>
function toggleSelectAllCompany(master) {
  const checkboxes = document.querySelectorAll('.company-ticket-checkbox');
  checkboxes.forEach(cb => cb.checked = master.checked);
  updateCompanySelectedCount();
}

function updateCompanySelectedCount() {
  const checked = document.querySelectorAll('.company-ticket-checkbox:checked');
  const count = checked.length;
  const toolbar = document.getElementById('companySelectionToolbar');
  const display = document.getElementById('companySelectedCount');

  if (count > 0) {
    toolbar.style.display = 'flex';
    display.innerText = count;
  } else {
    toolbar.style.display = 'none';
  }
}

function clearCompanySelection() {
  const master = document.getElementById('selectAllCompanyComplaints');
  if (master) master.checked = false;
  
  const checkboxes = document.querySelectorAll('.company-ticket-checkbox');
  checkboxes.forEach(cb => cb.checked = false);
  updateCompanySelectedCount();
}
</script>
@endsection
