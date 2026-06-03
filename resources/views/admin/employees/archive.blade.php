@extends('admin.layout.app')

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
<div class="employee-dashboard archived-employee-page !min-h-screen !overflow-x-hidden !px-3 !py-4 sm:!px-5 sm:!py-5 lg:!px-8 lg:!py-7">

    {{-- Ambient Animated Background --}}
    <div class="ambient-bg-archive">
        <div class="orb-archive orb-1"></div>
        <div class="orb-archive orb-2"></div>
        <div class="orb-archive orb-3"></div>
    </div>

    {{-- Breadcrumb --}}
    <div class="breadcrumb-wrapper animate-slideDown !mb-4 !overflow-x-auto !rounded-[18px]">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('employees.index') }}">
                        <i class="fas fa-users me-1"></i> Employees
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="fas fa-box-archive me-1"></i> Archived
                </li>
            </ol>
        </nav>
    </div>

    @if($errors->any())
        <div class="alert-premium alert-error animate-slideDown" style="animation-delay: 0.05s;">
            <i class="fas fa-exclamation-circle"></i>
            {{ $errors->first() }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert-premium alert-success animate-slideDown" style="animation-delay: 0.05s;">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Archive Header --}}
    <div class="archive-hero animate-fadeIn !mb-5 !rounded-[24px] !p-4 sm:!p-5 lg:!p-6">
        <div class="!flex !flex-col !gap-4 lg:!flex-row lg:!items-center lg:!justify-between">
            <!-- <div class="!flex !items-center !gap-3 sm:!gap-4"> -->
                <div class="stat-card-archive">
            <div class="stat-icon-archive total">
                <i class="fas fa-archive"></i>
            </div>
                <div>
                    <h1 class="!text-xl !font-black !leading-tight sm:!text-2xl lg:!text-3xl">Archived Employees</h1>
                    <p class="!mb-0 !mt-1 !text-sm sm:!text-base">Restore archived employee data when you need it back in the employee list.</p>
                </div>
            </div>
            <div class="!flex !w-full !flex-col !gap-2 sm:!w-auto sm:!flex-row">
                <a href="{{ route('employees.index') }}" class="archive-btn archive-btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Employees
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid-archive animate-slideUp" style="animation-delay: 0.1s;">
        <div class="stat-card-archive">
            <div class="stat-icon-archive total">
                <i class="fas fa-archive"></i>
            </div>
            <div class="stat-info-archive">
                <h6>Total Archived</h6>
                <h3>{{ $employees->total() }}</h3>
            </div>
        </div>
        <div class="stat-card-archive">
            <div class="stat-icon-archive active">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info-archive">
                <h6>Previously Active</h6>
                <h3>{{ $employees->filter(function($e) { return $e->employeeDetail?->status === 'Active'; })->count() }}</h3>
            </div>
        </div>
        <div class="stat-card-archive">
            <div class="stat-icon-archive mobile">
                <i class="fas fa-phone-alt"></i>
            </div>
            <div class="stat-info-archive">
                <h6>With Mobile</h6>
                <h3>{{ $employees->filter(function($e) { return !empty($e->employeeDetail?->mobile); })->count() }}</h3>
            </div>
        </div>
        <div class="stat-card-archive">
            <div class="stat-icon-archive month">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-info-archive">
                <h6>Archived This Month</h6>
                <h3>{{ $employees->filter(function($e) { return $e->archived_at && $e->archived_at->isCurrentMonth(); })->count() }}</h3>
            </div>
        </div>
    </div>

    {{-- Main Archive Card --}}
    <div class="archive-card animate-slideUp" style="animation-delay: 0.15s;">
        <div class="archive-card-header !flex !flex-col !gap-3 !p-4 sm:!p-5 lg:!flex-row lg:!items-center lg:!justify-between">
            <div>
                <h3 class="!mb-1 !text-lg !font-black"><i class="fas fa-history me-2"></i>Archived Employee Records</h3>
                <p class="!mb-0 !text-sm">Total: {{ $employees->total() }} archived employee(s)</p>
            </div>
            <form method="GET" action="{{ route('employees.archive') }}" class="archive-search !flex !w-full !flex-col !gap-2 sm:!w-auto sm:!flex-row">
                <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, email, or ID...">
                <button type="submit" class="archive-btn archive-btn-primary">
                    <i class="fas fa-search me-2"></i>Search
                </button>
                @if(request('search'))
                    <a href="{{ route('employees.archive') }}" class="archive-btn archive-btn-light">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="archive-toolbar !flex !flex-col !gap-3 !p-4 sm:!p-5 lg:!flex-row lg:!items-center lg:!justify-between">
            <div class="archive-show-entries">
                <span>Show</span>
                <select class="form-select form-select-sm" id="archiveShowEntries">
                    @foreach([10, 15, 20, 30, 50, 100] as $size)
                        <option value="{{ $size }}" {{ (int) request('per_page', 15) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <span>entries</span>
            </div>
            <div class="archive-bulk-actions">
                <span id="archive-selected-count" class="archive-selected-badge">0 selected</span>
                <button type="button" id="bulkRestoreBtn" class="archive-btn archive-btn-primary" disabled>
                    <i class="fas fa-rotate-left me-2"></i>Restore Selected
                </button>
            </div>
        </div>

        <div class="!w-full !overflow-x-auto">
            <table class="table archive-table mb-0 !min-w-[920px] md:!min-w-full">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" class="form-check-input" id="archiveSelectAll">
                        </th>
                        <th>Employee</th>
                        <th>Employee ID</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Archived At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr id="archive-row-{{ $employee->id }}">
                            <td>
                                <input type="checkbox" class="form-check-input archive-checkbox" value="{{ $employee->id }}">
                            </td>
                            <td>
                                <div class="archive-employee-cell">
                                    @if(!empty($employee->profile_image))
                                        <img src="{{ asset($employee->profile_image) }}" alt="Profile" class="archive-avatar">
                                    @else
                                        <div class="archive-avatar archive-avatar-empty">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $employee->name }}</strong>
                                        <span>{{ $employee->email ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="employee-id-badge">{{ $employee->employeeDetail?->employee_id ?? '-' }}</span></td>
                            <td>{{ $employee->employeeDetail?->designation?->name ?? '-' }}</td>
                            <td>{{ $employee->employeeDetail?->department?->dpt_name ?? ($employee->employeeDetail?->parent_dpt?->dpt_name ?? '-') }}</td>
                            <td>
                                <span class="archive-status {{ strtolower($employee->employeeDetail?->status ?? '') === 'active' ? 'status-active' : 'status-inactive' }}">
                                    {{ $employee->employeeDetail?->status ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="deleted-badge">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    {{ $employee->archived_at?->format('d M Y, h:i A') ?? '-' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="archive-row-actions">
                                    <a href="{{ route('employees.show', $employee->id) }}" class="archive-icon-btn" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee->id) }}" class="archive-icon-btn" title="Edit Profile">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('employees.restore', $employee->id) }}" method="POST" onsubmit="return confirm('Restore this employee to the active employee list?');">
                                        @csrf
                                        <button type="submit" class="archive-icon-btn archive-icon-primary" title="Restore">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="archive-empty">
                                    <div class="empty-icon-wrapper">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                    <h5>No Archived Employees Found</h5>
                                    <p>Inactive employees you archive will appear here.</p>
                                    <a href="{{ route('employees.index') }}" class="archive-btn archive-btn-primary mt-3">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Employees
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->total() > 0)
        <div class="archive-footer !flex !flex-col !gap-3 !p-4 sm:!p-5 lg:!flex-row lg:!items-center lg:!justify-between">
            <div class="archive-pagination-info">
                Showing {{ $employees->firstItem() }} - {{ $employees->lastItem() }} of {{ $employees->total() }}
            </div>
            <div class="archive-pagination-controls">
                @if($employees->onFirstPage())
                    <button type="button" class="archive-page-btn disabled" disabled>
                        <i class="fas fa-chevron-left me-2"></i>Prev
                    </button>
                @else
                    <a href="{{ $employees->previousPageUrl() }}" class="archive-page-btn">
                        <i class="fas fa-chevron-left me-2"></i>Prev
                    </a>
                @endif

                <span class="archive-page-current">Page {{ $employees->currentPage() }} of {{ $employees->lastPage() }}</span>

                @if($employees->hasMorePages())
                    <a href="{{ $employees->nextPageUrl() }}" class="archive-page-btn">
                        Next<i class="fas fa-chevron-right ms-2"></i>
                    </a>
                @else
                    <button type="button" class="archive-page-btn disabled" disabled>
                        Next<i class="fas fa-chevron-right ms-2"></i>
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    /* =============================================
       ENTERPRISE ARCHIVE PAGE DESIGN SYSTEM
       ============================================= */
    :root {
        --primary: #0f744c;
        --secondary: #188b5e;
        --accent: #22c55e;
        --mint: #d1fae5;
        --text-main: #07130d;
        --text-muted: #52645a;
        --bg-base: #f7fbf9;
        --surface: #ffffff;
        --grad-cta: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 40%, var(--accent) 80%, var(--mint) 100%);
        --grad-bg: linear-gradient(135deg, rgba(15, 116, 76, 0.12) 0%, rgba(24, 139, 94, 0.12) 40%, rgba(34, 197, 94, 0.12) 80%, rgba(209, 250, 229, 0.2) 100%);
        --shadow-soft: 0 10px 30px -10px rgba(15, 116, 76, 0.08);
        --shadow-hover: 0 20px 40px -12px rgba(15, 116, 76, 0.2);
        --spring: cubic-bezier(0.34, 1.56, 0.64, 1);
        --smooth: cubic-bezier(0.4, 0, 0.2, 1);
    }

    .archived-employee-page {
        position: relative;
        background: linear-gradient(145deg, #f5faf7 0%, #edf6f1 52%, #f8fbf9 100%) !important;
        overflow: hidden;
    }

    /* Ambient Background */
    .ambient-bg-archive {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: -1;
        overflow: hidden;
        pointer-events: none;
    }

    .orb-archive {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.25;
        will-change: transform;
        animation: floatOrb 20s infinite alternate var(--smooth);
    }

    .orb-1 { width: 50vw; height: 50vw; background: var(--secondary); top: -20%; right: -10%; }
    .orb-2 { width: 40vw; height: 40vw; background: var(--accent); bottom: -10%; left: -10%; animation-delay: -5s; }
    .orb-3 { width: 35vw; height: 35vw; background: var(--primary); top: 40%; left: 30%; animation-delay: -10s; }

    @keyframes floatOrb {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(-50px, 30px) scale(1.1); }
    }

    /* Animations */
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .animate-slideDown { animation: slideDown 0.6s ease forwards; }
    .animate-slideUp { animation: slideUp 0.6s ease forwards; }
    .animate-fadeIn { animation: fadeIn 0.8s ease forwards; }

    /* Breadcrumb */
    .breadcrumb-wrapper {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(15, 116, 76, 0.12);
        border-radius: 16px;
        padding: 12px 24px;
        box-shadow: var(--shadow-soft);
    }
    .breadcrumb-item a { color: var(--primary); text-decoration: none; font-weight: 600; }
    .breadcrumb-item.active { color: var(--secondary); font-weight: 700; }

    /* Alerts */
    .alert-premium {
        border-radius: 16px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }
    .alert-success { background: rgba(209, 250, 229, 0.95); border-left: 4px solid var(--accent); color: #065f46; }
    .alert-error { background: rgba(254, 226, 226, 0.95); border-left: 4px solid #ef4444; color: #991b1b; }

    /* Archive Hero */
    .archive-hero {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(241, 250, 245, 0.94)) !important;
        border: 1px solid rgba(15, 116, 76, 0.12);
        box-shadow: var(--shadow-soft);
        position: relative;
        overflow: hidden;
    }
    .archive-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(90deg, rgba(15, 116, 76, 0.08), transparent 34%),
                    radial-gradient(circle at 86% 16%, rgba(15, 116, 76, 0.1), transparent 18rem);
    }
    .archive-hero-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 14px 26px rgba(15, 116, 76, 0.22);
    }
    .archive-hero h1 { color: var(--text-main); font-weight: 800; letter-spacing: -0.02em; }
    .archive-hero p { color: var(--text-muted); font-weight: 600; }

    /* Stats Grid */
    .stats-grid-archive {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }
    .stat-card-archive {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 1.25rem;
        border: 1px solid rgba(15, 116, 76, 0.12);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s var(--spring);
    }
    .stat-card-archive:hover { transform: translateY(-4px); box-shadow: var(--shadow-hover); }
    .stat-icon-archive {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
    .stat-icon-archive.total { background: #e2e8f0; color: #475569; }
    .stat-icon-archive.active { background: #d1fae5; color: #047857; }
    .stat-icon-archive.mobile { background: #dbeafe; color: #2563eb; }
    .stat-icon-archive.month { background: #fef3c7; color: #d97706; }
    .stat-info-archive h6 { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 0.25rem; }
    .stat-info-archive h3 { font-size: 1.8rem; font-weight: 800; color: var(--text-main); margin: 0; }

    /* Archive Card */
    .archive-card {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(15, 116, 76, 0.12);
        border-radius: 24px;
        box-shadow: 0 18px 42px rgba(22, 39, 30, 0.09);
        overflow: hidden;
    }
    .archive-card-header {
        background: linear-gradient(135deg, rgba(15, 116, 76, 0.08), rgba(255, 255, 255, 0.92));
        border-bottom: 1px solid rgba(15, 116, 76, 0.1);
        padding: 1.25rem 1.5rem;
    }
    .archive-card-header h3 { color: var(--text-main); font-weight: 800; font-size: 1.1rem; }
    .archive-card-header p { color: var(--text-muted); font-weight: 600; }

    /* Toolbar */
    .archive-toolbar {
        background: rgba(247, 250, 248, 0.82);
        border-bottom: 1px solid rgba(15, 116, 76, 0.1);
        padding: 0.75rem 1.5rem;
    }
    .archive-show-entries { display: flex; align-items: center; gap: 0.5rem; }
    .archive-show-entries span { color: var(--text-muted); font-weight: 600; font-size: 0.85rem; }
    .archive-show-entries .form-select { width: 75px; border-radius: 20px; border: 1px solid rgba(15, 116, 76, 0.16); font-weight: 600; }
    .archive-bulk-actions { display: flex; align-items: center; gap: 0.75rem; }
    .archive-selected-badge {
        background: #edf8f2;
        color: var(--primary);
        padding: 0.35rem 1rem;
        border-radius: 40px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    /* Search */
    .archive-search { gap: 0.5rem; }
    .archive-search .form-control { border-radius: 40px; border: 1px solid rgba(15, 116, 76, 0.16); padding: 0.5rem 1rem; min-width: 260px; }

    /* Buttons */
    .archive-btn {
        padding: 0.5rem 1.25rem;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.3s var(--spring);
        cursor: pointer;
    }
    .archive-btn-primary { background: linear-gradient(135deg, var(--primary), var(--secondary)); border: none; color: white !important; box-shadow: 0 4px 12px rgba(15, 116, 76, 0.2); }
    .archive-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15, 116, 76, 0.3); color: white !important; }
    .archive-btn-light { background: white; border: 1px solid rgba(15, 116, 76, 0.16); color: var(--primary) !important; }
    .archive-btn-light:hover { background: #edf8f2; border-color: var(--primary); transform: translateY(-2px); }

    /* Table */
    .archive-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .archive-table th { background: #f4faf7; color: var(--text-main); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; padding: 1rem 1rem; border-bottom: 1px solid rgba(15, 116, 76, 0.1); }
    .archive-table td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid rgba(15, 116, 76, 0.06); color: var(--text-main); font-weight: 500; }
    .archive-table tbody tr { transition: all 0.2s ease; }
    .archive-table tbody tr:hover { background: #f5fbf7; }
    .archive-table tr.archive-selected { background: rgba(15, 116, 76, 0.06); }

    .archive-employee-cell { display: flex; align-items: center; gap: 0.75rem; }
    .archive-avatar { width: 42px; height: 42px; border-radius: 12px; object-fit: cover; border: 2px solid white; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); }
    .archive-avatar-empty { background: #e8f5ef; display: flex; align-items: center; justify-content: center; color: var(--primary); }
    .archive-employee-cell strong { display: block; font-weight: 800; color: var(--text-main); }
    .archive-employee-cell span { font-size: 0.75rem; color: var(--text-muted); }

    .employee-id-badge { background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; color: var(--primary); }

    .archive-status { display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 40px; font-size: 0.75rem; font-weight: 700; }
    .archive-status.status-active { background: #d1fae5; color: #047857; }
    .archive-status.status-inactive { background: #fee2e2; color: #b91c1c; }

    .deleted-badge { background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.7rem; color: #64748b; display: inline-flex; align-items: center; gap: 4px; }

    .archive-row-actions { display: flex; gap: 0.5rem; justify-content: flex-end; }
    .archive-icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: white;
        border: 1px solid rgba(15, 116, 76, 0.16);
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s var(--spring);
        cursor: pointer;
    }
    .archive-icon-btn:hover { transform: translateY(-2px); background: #edf8f2; border-color: var(--primary); }
    .archive-icon-primary { background: linear-gradient(135deg, var(--primary), var(--secondary)); border: none; color: white !important; }
    .archive-icon-primary:hover { background: linear-gradient(135deg, #0b5f3e, #12754c); transform: translateY(-2px); color: white !important; }

    /* Pagination */
    .archive-footer { background: linear-gradient(135deg, #ffffff, #f6fbf8); border-top: 1px solid rgba(15, 116, 76, 0.1); padding: 1rem 1.5rem; }
    .archive-pagination-info { color: var(--text-muted); font-size: 0.85rem; font-weight: 600; }
    .archive-pagination-controls { display: flex; align-items: center; gap: 0.5rem; }
    .archive-page-btn {
        padding: 0.4rem 1rem;
        border-radius: 40px;
        background: white;
        border: 1px solid rgba(15, 116, 76, 0.16);
        color: var(--primary);
        text-decoration: none;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
    }
    .archive-page-btn:hover:not(.disabled) { background: #edf8f2; transform: translateY(-2px); }
    .archive-page-btn.disabled { opacity: 0.5; cursor: not-allowed; }
    .archive-page-current { color: var(--text-main); font-weight: 700; font-size: 0.85rem; }

    /* Empty State */
    .archive-empty { text-align: center; padding: 3rem 2rem; }
    .empty-icon-wrapper { width: 80px; height: 80px; background: #e8f5ef; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
    .empty-icon-wrapper i { font-size: 2rem; color: var(--primary); }
    .archive-empty h5 { font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem; }
    .archive-empty p { color: var(--text-muted); }

    /* Checkbox */
    .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }

    /* Responsive */
    @media (max-width: 1200px) { .stats-grid-archive { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) { .stats-grid-archive { grid-template-columns: 1fr; } .archive-bulk-actions { flex-wrap: wrap; } .archive-search { width: 100%; } .archive-search .form-control { width: 100%; } }

    /* Dark Mode Support */
    html[data-pms-theme="dark"] .archived-employee-page { background: linear-gradient(145deg, #07110d 0%, #102119 100%) !important; }
    html[data-pms-theme="dark"] .archive-hero, html[data-pms-theme="dark"] .archive-card { background: #102119; border-color: rgba(122, 240, 181, 0.18); }
    html[data-pms-theme="dark"] .archive-card-header, html[data-pms-theme="dark"] .archive-toolbar { background: rgba(64, 212, 140, 0.08); }
    html[data-pms-theme="dark"] .archive-table th { background: rgba(64, 212, 140, 0.08); color: white; }
    html[data-pms-theme="dark"] .archive-table td { color: #d9f1e4; }
    html[data-pms-theme="dark"] .archive-btn-light { background: #183026; color: #7af0b5 !important; }
    html[data-pms-theme="dark"] .archive-icon-btn { background: #183026; color: #7af0b5; }
    html[data-pms-theme="dark"] .archive-hero h1, html[data-pms-theme="dark"] .archive-card-header h3,
    html[data-pms-theme="dark"] .archive-employee-cell strong, html[data-pms-theme="dark"] .stat-info-archive h3 { color: white; }
    html[data-pms-theme="dark"] .archive-hero p, html[data-pms-theme="dark"] .archive-card-header p,
    html[data-pms-theme="dark"] .archive-employee-cell span, html[data-pms-theme="dark"] .archive-pagination-info { color: #d9f1e4; }
</style>

@push('js')
<script>
$(document).ready(function () {
    function archiveSelectedIds() {
        return $('.archive-checkbox:checked').map(function () {
            return $(this).val();
        }).get();
    }

    function updateArchiveSelection() {
        const selectedIds = archiveSelectedIds();
        const total = $('.archive-checkbox').length;
        const checked = selectedIds.length;

        $('#archive-selected-count').text(checked + ' selected');
        $('#bulkRestoreBtn').prop('disabled', checked === 0);
        $('.archive-checkbox').closest('tr').removeClass('archive-selected');
        $('.archive-checkbox:checked').closest('tr').addClass('archive-selected');

        $('#archiveSelectAll')
            .prop('checked', total > 0 && checked === total)
            .prop('indeterminate', checked > 0 && checked < total);
    }

    $('#archiveSelectAll').on('change', function () {
        $('.archive-checkbox').prop('checked', $(this).is(':checked'));
        updateArchiveSelection();
    });

    $(document).on('change', '.archive-checkbox', updateArchiveSelection);

    $('#archiveShowEntries').on('change', function () {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', $(this).val());
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });

    $('#bulkRestoreBtn').on('click', function () {
        const selectedIds = archiveSelectedIds();

        if (!selectedIds.length) {
            alert('Please select at least one archived employee to restore.');
            return;
        }

        if (!confirm('Restore ' + selectedIds.length + ' selected employee(s) back to the active employee list?')) {
            return;
        }

        $.ajax({
            url: '{{ route("employees.archive.bulkRestore") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                employee_ids: selectedIds
            },
            success: function (response) {
                alert(response?.message || 'Employees restored successfully.');
                location.reload();
            },
            error: function (xhr) {
                alert(xhr?.responseJSON?.message || 'Failed to restore selected employees. Please try again.');
            }
        });
    });

    updateArchiveSelection();
});
</script>
@endpush
@endsection
