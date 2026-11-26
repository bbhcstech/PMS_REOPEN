@extends('admin.layout.app')

@section('title', 'Client Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Welcome Card --}}
  <div class="row g-4 mb-4">
    <div class="col-xxl-8">
      <div class="card shadow-sm rounded-4">
        <div class="row align-items-center">
          <div class="col-sm-7">
            <div class="card-body">
              <h4 class="card-title text-primary mb-2 fw-semibold">
                Welcome to PMS Client Panel 👋
              </h4>
              <p class="text-muted mb-3">
                Manage your projects, milestones, tickets, and deliverables all in one place.
              </p>
              <a href="{{ route('projects.index') }}" class="btn btn-primary btn-sm rounded-pill">
                View My Projects
              </a>
            </div>
          </div>
          <div class="col-sm-5 text-center">
            <img
              src="{{ asset('admin/assets/img/illustrations/dashboard-ui-preview.png') }}"
              class="img-fluid"
              style="max-height:180px"
              alt="Dashboard Illustration">
          </div>
        </div>
      </div>
    </div>

    {{-- Stat Cards --}}
    <div class="col-xxl-4">
      <div class="row g-4">
        <div class="col-6">
          <div class="card h-100 shadow-sm rounded-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <img src="{{ asset('admin/assets/img/icons/unicons/chart-success.png') }}" height="40" alt="Projects">
                <i class="bx bx-folder text-primary fs-4"></i>
              </div>
              <small class="text-muted">Active Projects</small>
              <h4 class="fw-bold mb-1">{{ \App\Models\Project::where('client_id', auth()->id())->count() }}</h4>
              <small class="text-success fw-medium">
                <i class="bx bx-check-circle"></i> In progress
              </small>
            </div>
          </div>
        </div>

        <div class="col-6">
          <div class="card h-100 shadow-sm rounded-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <img src="{{ asset('admin/assets/img/icons/unicons/wallet-info.png') }}" height="40" alt="Tickets">
                <i class="bx bx-support text-info fs-4"></i>
              </div>
              <small class="text-muted">Open Tickets</small>
              <h4 class="fw-bold mb-1">{{ \App\Models\Ticket::where('user_id', auth()->id())->where('status', '!=', 'closed')->count() }}</h4>
              <small class="text-info fw-medium">
                <i class="bx bx-time"></i> Active
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Performance / Project Overview --}}
  <div class="row g-4 mb-4">
    <div class="col-lg-8">
      <div class="card shadow-sm rounded-4 h-100">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
          <h5 class="fw-semibold mb-0">Project Progress Overview</h5>
          <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Project</th>
                  <th>Status</th>
                  <th>Progress</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse(\App\Models\Project::where('client_id', auth()->id())->latest()->take(5)->get() as $p)
                  <tr>
                    <td>
                      <strong>{{ $p->name }}</strong>
                      <div class="small text-muted">{{ $p->project_code ?? '' }}</div>
                    </td>
                    <td>
                      <span class="badge bg-label-{{ $p->status === 'completed' ? 'success' : 'primary' }}">
                        {{ ucfirst($p->status ?? 'Active') }}
                      </span>
                    </td>
                    <td>
                      <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, (int) ($p->progress ?? 50)) }}%"></div>
                      </div>
                    </td>
                    <td>
                      <a href="{{ route('projects.show', $p->id) }}" class="btn btn-xs btn-outline-secondary">View</a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">No active projects found.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm rounded-4 h-100">
        <div class="card-header bg-transparent">
          <h5 class="fw-semibold mb-0">Recent Support Tickets</h5>
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            @forelse(\App\Models\Ticket::where('user_id', auth()->id())->latest()->take(4)->get() as $t)
              <li class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                  <a href="{{ route('tickets.show', $t->id) }}" class="fw-medium text-dark text-truncate d-block" style="max-width: 180px;">
                    #{{ $t->id }} {{ $t->subject }}
                  </a>
                  <small class="text-muted">{{ $t->created_at->diffForHumans() }}</small>
                </div>
                <span class="badge bg-label-{{ $t->status === 'closed' ? 'success' : 'warning' }}">
                  {{ ucfirst($t->status ?? 'Open') }}
                </span>
              </li>
            @empty
              <li class="text-center text-muted py-3">No recent tickets.</li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
