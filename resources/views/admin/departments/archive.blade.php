@extends('admin.layout.app')

@section('title', 'Archived Sub Departments')

@section('content')
<style>
    .sub-archive-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0f9ff 0%, #e6f7f5 50%, #f0fdf4 100%);
        color: #0f172a;
    }

    .sub-archive-shell {
        position: relative;
        max-width: 1600px;
        margin: 0 auto;
    }

    .archive-header,
    .stat-card,
    .table-card,
    .search-card {
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(255, 255, 255, 0.75);
        box-shadow: 0 10px 34px rgba(15, 23, 42, 0.06);
        backdrop-filter: blur(18px);
    }

    .archive-header {
        border-radius: 26px;
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .header-title h1 {
        margin: 0 0 0.35rem;
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, #1e3a8a, #0ea5a4, #22c55e);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .header-title p {
        margin: 0;
        color: #64748b;
        font-weight: 500;
    }

    .badge-premium {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-left: 0.65rem;
        padding: 0.3rem 0.9rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #64748b, #475569);
        color: #fff;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        vertical-align: middle;
    }

    .back-button,
    .search-button,
    .reset-button {
        border: 0;
        border-radius: 999px;
        min-height: 44px;
        padding: 0.7rem 1.35rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        text-decoration: none;
        font-weight: 700;
        transition: all 0.25s ease;
    }

    .back-button,
    .search-button {
        background: linear-gradient(135deg, #1e3a8a, #0ea5a4);
        color: #fff;
        box-shadow: 0 8px 20px rgba(14, 165, 164, 0.24);
    }

    .back-button:hover,
    .search-button:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(14, 165, 164, 0.32);
    }

    .reset-button {
        background: #f1f5f9;
        color: #475569;
    }

    .reset-button:hover {
        color: #0f172a;
        background: #e2e8f0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        border-radius: 22px;
        padding: 1.35rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        flex-shrink: 0;
    }

    .stat-icon.total { background: #e2e8f0; color: #475569; }
    .stat-icon.parent { background: #dbeafe; color: #1d4ed8; }
    .stat-icon.employee { background: #d1fae5; color: #047857; }
    .stat-icon.month { background: #fef3c7; color: #d97706; }

    .stat-info h6 {
        margin: 0 0 0.25rem;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .stat-info h3 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 900;
        color: #0f172a;
    }

    .alert-premium {
        border-radius: 18px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.2rem;
        border-left: 5px solid;
    }

    .alert-success {
        background: rgba(220, 252, 231, 0.95);
        border-left-color: #22c55e;
        color: #065f46;
    }

    .search-card {
        border-radius: 22px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .archive-search-bar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .search-field {
        flex: 1 1 320px;
        position: relative;
    }

    .search-field i {
        position: absolute;
        top: 50%;
        left: 1rem;
        transform: translateY(-50%);
        color: #0ea5a4;
    }

    .search-field input {
        width: 100%;
        min-height: 48px;
        border-radius: 999px;
        border: 1px solid #dbeafe;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        outline: none;
        color: #0f172a;
    }

    .table-card {
        border-radius: 26px;
        overflow: hidden;
    }

    .card-header {
        padding: 1.35rem 1.6rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .card-header h5 {
        margin: 0;
        font-weight: 800;
        color: #0f172a;
    }

    .total-badge {
        border-radius: 999px;
        padding: 0.45rem 1rem;
        background: linear-gradient(135deg, #64748b, #475569);
        color: #fff;
        font-weight: 800;
        font-size: 0.8rem;
    }

    .table-responsive {
        padding: 1.25rem;
        overflow-x: auto;
    }

    .archive-table {
        width: 100%;
        min-width: 1000px;
        border-collapse: separate;
        border-spacing: 0 0.65rem;
    }

    .archive-table th {
        padding: 0.75rem 1rem;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
    }

    .archive-table td {
        padding: 1rem;
        background: #fff;
        color: #1e293b;
        vertical-align: middle;
    }

    .archive-table tbody tr td:first-child {
        border-radius: 14px 0 0 14px;
    }

    .archive-table tbody tr td:last-child {
        border-radius: 0 14px 14px 0;
    }

    .record-title {
        font-weight: 800;
        color: #0f172a;
    }

    .record-sub {
        color: #64748b;
        font-size: 0.8rem;
        margin-top: 0.2rem;
    }

    .pill {
        border-radius: 999px;
        padding: 0.35rem 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .pill.parent { background: #dbeafe; color: #1d4ed8; }
    .pill.employee { background: #d1fae5; color: #047857; }
    .pill.archived { background: #f1f5f9; color: #475569; }

    .action-group {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.45rem;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 12px;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        color: #fff;
    }

    .btn-view { background: linear-gradient(135deg, #0ea5a4, #0891b2); }
    .btn-restore { background: linear-gradient(135deg, #22c55e, #16a34a); }

    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }

    .empty-icon {
        width: 92px;
        height: 92px;
        border-radius: 24px;
        margin: 0 auto 1.25rem;
        background: linear-gradient(135deg, #64748b, #475569);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.4rem;
    }

    .pagination-container {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .sub-archive-page {
            padding: 1rem;
        }

        .archive-header {
            padding: 1.25rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="sub-archive-page">
    <div class="sub-archive-shell">
        <div class="archive-header">
            <div class="header-title">
                <h1>
                    <i class="fas fa-archive me-2"></i>Archived Sub Departments
                    <span class="badge-premium"><i class="fas fa-history"></i> ARCHIVE</span>
                </h1>
                <p><i class="fas fa-info-circle me-1"></i>Restore archived sub department records whenever they need to return to the active list.</p>
            </div>
            <a href="{{ route('departments.index') }}" class="back-button">
                <i class="fas fa-arrow-left"></i>Back to Active Sub Departments
            </a>
        </div>

        @if (session('success'))
            <div class="alert-premium alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @php
            $archivedCollection = $departments->getCollection();
            $totalArchived = method_exists($departments, 'total') ? $departments->total() : $departments->count();
            $parentCount = $archivedCollection->pluck('parent_dpt_id')->filter()->unique()->count();
            $employeeCount = $archivedCollection->sum('employee_details_count');
            $monthlyArchived = $archivedCollection->filter(fn ($department) => $department->archived_at && $department->archived_at->isCurrentMonth())->count();
        @endphp

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total"><i class="fas fa-archive"></i></div>
                <div class="stat-info">
                    <h6>Total Archived</h6>
                    <h3>{{ $totalArchived }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon parent"><i class="fas fa-building"></i></div>
                <div class="stat-info">
                    <h6>Parent Groups</h6>
                    <h3>{{ $parentCount }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon employee"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h6>Employees</h6>
                    <h3>{{ $employeeCount }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon month"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-info">
                    <h6>This Month</h6>
                    <h3>{{ $monthlyArchived }}</h3>
                </div>
            </div>
        </div>

        <div class="search-card">
            <form method="GET" action="{{ route('departments.archive') }}" class="archive-search-bar">
                <div class="search-field">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search archived sub departments...">
                </div>
                <button type="submit" class="search-button">
                    <i class="fas fa-filter"></i>Search
                </button>
                @if(request('search'))
                    <a href="{{ route('departments.archive') }}" class="reset-button">
                        <i class="fas fa-times"></i>Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="table-card">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i>Archived Sub Department Records</h5>
                <span class="total-badge"><i class="fas fa-database me-1"></i>Total: {{ $totalArchived }}</span>
            </div>

            <div class="table-responsive">
                @if($departments->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-archive"></i></div>
                        <h5 class="fw-bold">No Archived Sub Departments Found</h5>
                        <p class="text-muted">There are no sub departments in the archive at the moment.</p>
                        <a href="{{ route('departments.index') }}" class="back-button mt-3">
                            <i class="fas fa-arrow-left"></i>Back to Active Sub Departments
                        </a>
                    </div>
                @else
                    <table class="archive-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sub Department</th>
                                <th>Code</th>
                                <th>Parent Department</th>
                                <th>Employees</th>
                                <th>Archived On</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $index => $department)
                                <tr>
                                    <td>{{ $departments->firstItem() + $index }}</td>
                                    <td>
                                        <div class="record-title">{{ $department->dpt_name }}</div>
                                        <div class="record-sub">Sub department record</div>
                                    </td>
                                    <td>
                                        <span class="pill archived"><i class="fas fa-code"></i>{{ $department->dpt_code ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="pill parent"><i class="fas fa-building"></i>{{ $department->parent?->dpt_name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="pill employee"><i class="fas fa-users"></i>{{ $department->employee_details_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="pill archived">
                                            <i class="far fa-calendar-alt"></i>
                                            {{ $department->archived_at ? $department->archived_at->format('d M Y h:i A') : 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <a href="{{ route('departments.show', $department->id) }}" class="action-btn btn-view" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('departments.restore', $department->id) }}" method="POST" onsubmit="return confirm('Restore this sub department to the active list?');">
                                                @csrf
                                                <button type="submit" class="action-btn btn-restore" title="Restore">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($departments->hasPages())
                        <div class="pagination-container">
                            <div class="text-muted">
                                Showing {{ $departments->firstItem() ?? 0 }} to {{ $departments->lastItem() ?? 0 }} of {{ $departments->total() }}
                            </div>
                            <div>{{ $departments->links() }}</div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
