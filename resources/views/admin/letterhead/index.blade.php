@extends('admin.layout.app')

@section('title', 'Company Letter Head Management')

@section('content')
<style>
    :root {
        --lh-emerald: #0f744c;
        --lh-emerald-light: #10b981;
        --lh-emerald-soft: #e4f3eb;
        --lh-slate-dark: #0f172a;
        --lh-slate-body: #334155;
        --lh-slate-muted: #64748b;
        --lh-slate-light: #f8fafc;
        --lh-card-bg: linear-gradient(145deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 249, 0.94) 100%);
        --lh-shadow: 0 16px 40px -10px rgba(15, 116, 76, 0.08), 0 4px 14px rgba(0, 0, 0, 0.03);
    }

    /* ===== FORCE WHITE TEXT ON ADD LETTERHEAD BUTTON ===== */
    button.add-lh-btn,
    button.add-lh-btn:hover,
    button.add-lh-btn:focus,
    button.add-lh-btn:active,
    button.add-lh-btn *,
    button.add-lh-btn i,
    button.add-lh-btn span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        fill: #ffffff !important;
    }

    .lh-shell {
        padding: 1.5rem 0 3rem;
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
    }

    .lh-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .lh-page-title h3 {
        color: var(--lh-slate-dark);
        font-size: 1.8rem;
        font-weight: 900;
        letter-spacing: -0.03em;
        margin: 0 0 0.3rem;
    }

    .lh-page-title p {
        color: var(--lh-slate-muted);
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0;
    }

    .lh-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .lh-stat-card {
        background: var(--lh-card-bg);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        box-shadow: var(--lh-shadow);
        display: flex;
        align-items: center;
        gap: 1.2rem;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .lh-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 45px -10px rgba(15, 116, 76, 0.14);
    }

    .lh-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(15, 116, 76, 0.12), rgba(16, 185, 129, 0.12));
        color: var(--lh-emerald);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }

    .lh-stat-info strong {
        display: block;
        font-size: 1.7rem;
        font-weight: 900;
        color: var(--lh-slate-dark);
        line-height: 1.1;
    }

    .lh-stat-info span {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--lh-slate-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Main Table Container */
    .lh-table-card {
        background: var(--lh-card-bg);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 24px;
        box-shadow: var(--lh-shadow);
        overflow: hidden;
    }

    .lh-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .lh-table thead th {
        background: rgba(248, 250, 249, 0.95);
        color: var(--lh-slate-dark);
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 1.25rem 1.5rem;
        border-bottom: 2px solid rgba(226, 232, 240, 0.8);
        white-space: nowrap;
    }

    .lh-table tbody tr {
        transition: background 0.2s ease, box-shadow 0.2s ease;
        border-bottom: 1.5px solid rgba(200, 220, 210, 0.55);
    }

    .lh-table tbody tr:not(:last-child) {
        border-bottom: 1.5px solid #d4ede1;
    }

    .lh-table tbody tr:last-child td {
        border-bottom: none;
    }

    .lh-table tbody tr:nth-child(even) {
        background: rgba(238, 248, 243, 0.45);
    }

    .lh-table tbody tr:hover {
        background: rgba(38, 169, 108, 0.06);
        box-shadow: inset 4px 0 0 #26a96c;
    }

    .lh-table tbody td {
        padding: 1.25rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1.5px solid #d4ede1;
        border-right: 1.5px solid #d4ede1;
        color: var(--lh-slate-body);
        font-size: 0.93rem;
        font-weight: 600;
    }

    .lh-table tbody td:last-child {
        border-right: none;
    }

    .lh-table thead th {
        border-right: 1.5px solid #c8e0d5;
    }

    .lh-table thead th:last-child {
        border-right: none;
    }

    .lh-company-cell {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .lh-company-logo {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        background: #ffffff;
    }

    .lh-company-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #26a96c 0%, #4ecb91 100%);
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 1.1rem;
        box-shadow: 0 4px 12px rgba(50, 189, 147, 0.3);
    }

    .lh-company-avatar * {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .lh-company-name {
        font-weight: 800;
        color: var(--lh-slate-dark);
        font-size: 0.98rem;
    }

    .lh-code-badge {
        font-weight: 900;
        color: var(--lh-slate-dark);
        font-size: 0.95rem;
    }

    .lh-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 800;
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .lh-doc-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .lh-doc-badge.pdf {
        background: rgba(239, 68, 68, 0.12);
        color: #b91c1c;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .lh-doc-badge.docs {
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
        border: 1px solid rgba(37, 99, 235, 0.2);
    }

    /* Enforce pure white text for all green primary buttons */
    button.btn-lh-primary,
    a.btn-lh-primary,
    .btn-lh-primary,
    .btn-lh-primary *,
    .btn-lh-primary i,
    .btn-lh-primary span,
    .btn-primary,
    .btn-primary *,
    .btn-primary i,
    .btn-primary span {
        color: #ffffff !important;
        fill: #ffffff !important;
    }

    .btn-lh-primary {
        background: linear-gradient(135deg, #26a96c 0%, #4ecb91 100%);
        color: #ffffff !important;
        font-weight: 800;
        font-size: 0.88rem;
        padding: 0.55rem 1.25rem;
        border-radius: 999px;
        border: none;
        box-shadow: 0 6px 18px rgba(50, 189, 147, 0.3);
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        text-decoration: none;
    }

    .btn-lh-primary,
    .btn-lh-primary:visited,
    .btn-lh-primary:link,
    .btn-lh-primary:hover,
    .btn-lh-primary:focus,
    .btn-lh-primary:active,
    .btn-lh-primary i,
    .btn-lh-primary span,
    .btn-lh-primary * {
        color: #ffffff !important;
        fill: #ffffff !important;
    }

    button.btn-lh-primary:hover,
    button.btn-lh-primary:focus,
    button.btn-lh-primary:active,
    a.btn-lh-primary:hover,
    a.btn-lh-primary:focus,
    a.btn-lh-primary:active,
    .btn-lh-primary:hover,
    .btn-lh-primary:focus,
    .btn-lh-primary:active,
    .btn-lh-primary:hover *,
    .btn-lh-primary:focus *,
    .btn-lh-primary:active * {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(15, 116, 76, 0.32);
        color: #ffffff !important;
        fill: #ffffff !important;
    }

    .btn-lh-outline {
        background: rgba(15, 116, 76, 0.08);
        color: var(--lh-emerald) !important;
        font-weight: 800;
        font-size: 0.85rem;
        padding: 0.5rem 1.1rem;
        border-radius: 999px;
        border: 1px solid rgba(15, 116, 76, 0.25);
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-lh-outline:hover {
        background: var(--lh-emerald);
        color: #ffffff !important;
        border-color: var(--lh-emerald);
        transform: translateY(-2px);
    }

    .btn-lh-danger {
        background: rgba(239, 68, 68, 0.08);
        color: #dc2626 !important;
        font-weight: 800;
        font-size: 0.85rem;
        padding: 0.5rem 0.85rem;
        border-radius: 999px;
        border: 1px solid rgba(239, 68, 68, 0.2);
        transition: all 0.25s ease;
    }

    .btn-lh-danger:hover {
        background: #dc2626;
        color: #ffffff !important;
    }

    /* Modal Drag and Drop Styles */
    .lh-dropzone {
        border: 2px dashed rgba(16, 185, 129, 0.4);
        border-radius: 20px;
        background: linear-gradient(145deg, rgba(248, 250, 249, 0.8), rgba(241, 248, 244, 0.6));
        padding: 2.5rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .lh-dropzone:hover,
    .lh-dropzone.dragover {
        border-color: var(--lh-emerald-light);
        background: rgba(16, 185, 129, 0.08);
        box-shadow: 0 0 25px rgba(16, 185, 129, 0.2);
    }

    .lh-dropzone-icon {
        font-size: 3.2rem;
        color: var(--lh-emerald);
        margin-bottom: 0.8rem;
        transition: transform 0.3s ease;
    }

    .lh-dropzone:hover .lh-dropzone-icon {
        transform: translateY(-4px) scale(1.1);
    }

    .lh-dropzone-title {
        font-size: 1.1rem;
        font-weight: 900;
        color: var(--lh-slate-dark);
        margin-bottom: 0.4rem;
    }

    .lh-dropzone-subtitle {
        font-size: 0.88rem;
        color: var(--lh-slate-muted);
        font-weight: 600;
    }

    .lh-format-pills {
        display: flex;
        justify-content: center;
        gap: 0.8rem;
        margin-top: 1.25rem;
    }

    .lh-format-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.9rem;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 800;
    }

    .lh-format-pill.pdf-pill {
        background: rgba(239, 68, 68, 0.12);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .lh-format-pill.doc-pill {
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.2);
    }

    .lh-selected-preview {
        margin-top: 1.25rem;
        padding: 1rem 1.25rem;
        background: #ffffff;
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
    }

    .lh-selected-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-align: left;
    }

    .lh-selected-info i {
        font-size: 1.8rem;
        color: var(--lh-emerald);
    }

    .lh-selected-name {
        font-weight: 800;
        color: var(--lh-slate-dark);
        font-size: 0.92rem;
    }

    .lh-selected-size {
        font-size: 0.8rem;
        color: var(--lh-slate-muted);
    }

    /* Template Document Viewer Preview */
    .lh-template-paper {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.07);
        padding: 2.5rem;
        min-height: 420px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .lh-paper-header {
        border-bottom: 2px solid var(--lh-emerald);
        padding-bottom: 1.2rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .lh-paper-body-skeleton {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 1rem 0;
        opacity: 0.25;
    }

    .lh-skeleton-line {
        height: 12px;
        background: #94a3b8;
        border-radius: 6px;
    }

    .lh-paper-footer {
        border-top: 1px solid #e2e8f0;
        padding-top: 1rem;
        display: flex;
        justify-content: space-between;
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y lh-shell">

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert" style="background: rgba(16, 185, 129, 0.12); color: #047857; border: 1px solid rgba(16, 185, 129, 0.3);">
            <div class="d-flex align-items-center">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                <strong>{{ session('success') }}</strong>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-error-circle fs-4 me-2"></i>
                <strong>{{ session('error') }}</strong>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header & Summary -->
    <div class="lh-page-header">
        <div class="lh-page-title">
            <h3>Letterhead Management</h3>
            <p>Manage company identity, prefixes, status, and official letterhead templates.</p>
        </div>
        @if(Route::has('admin.companies.create'))
            <a href="{{ route('admin.companies.create') }}" class="btn-lh-primary">
                <i class="bx bx-plus fs-5"></i> Add Company
            </a>
        @endif
    </div>

    <!-- Summary Stats -->
    <div class="lh-stats-grid">
        <div class="lh-stat-card">
            <div class="lh-stat-icon">
                <i class="bx bx-building-house"></i>
            </div>
            <div class="lh-stat-info">
                <strong>{{ $stats['total_companies'] }}</strong>
                <span>Total Companies</span>
            </div>
        </div>

        <div class="lh-stat-card">
            <div class="lh-stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                <i class="bx bx-file-blank"></i>
            </div>
            <div class="lh-stat-info">
                <strong>{{ $stats['uploaded_count'] }}</strong>
                <span>Letterheads Fixed</span>
            </div>
        </div>

        <div class="lh-stat-card">
            <div class="lh-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                <i class="bx bx-cloud-upload"></i>
            </div>
            <div class="lh-stat-info">
                <strong>{{ $stats['pending_count'] }}</strong>
                <span>Pending Uploads</span>
            </div>
        </div>
    </div>

    <!-- Companies Management & Letterhead Table -->
    <div class="lh-table-card">
        <div class="table-responsive text-nowrap">
            <table class="lh-table">
                <thead>
                    <tr>
                        <th>COMPANY</th>
                        <th>CODE</th>
                        <th>EMAIL</th>
                        <th>EMPLOYEE PREFIX</th>
                        <th>LEAVE</th>
                        <th>PAYROLL</th>
                        <th>STATUS</th>
                        <th class="text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td>
                                <div class="lh-company-cell">
                                    @if($company->logo)
                                        <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" class="lh-company-logo">
                                    @else
                                        <div class="lh-company-avatar">
                                            {{ strtoupper(substr($company->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="lh-company-name">{{ $company->name }}</div>
                                        @if($company->hasLetterhead())
                                            <div class="mt-1">
                                                <span class="lh-doc-badge {{ $company->letterhead_file_type === 'docs' ? 'docs' : 'pdf' }}">
                                                    <i class="bx {{ $company->letterhead_file_type === 'docs' ? 'bxs-file-doc' : 'bxs-file-pdf' }}"></i>
                                                    {{ strtoupper($company->letterhead_file_type ?? 'PDF') }} Letterhead Attached
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="lh-code-badge">{{ $company->company_code ?: 'N/A' }}</span>
                            </td>
                            <td>{{ $company->email ?: 'N/A' }}</td>
                            <td>{{ $company->employee_id_prefix ?: ($company->company_code ? $company->company_code . '-EMP' : 'N/A') }}</td>
                            <td>{{ $company->leave_prefix ?: ($company->company_code ? $company->company_code . '-LV' : 'N/A') }}</td>
                            <td>{{ $company->payroll_prefix ?: ($company->company_code ? $company->company_code . '-PR' : 'N/A') }}</td>
                            <td>
                                <span class="lh-status-pill">
                                    <i class="bx bxs-circle" style="font-size: 0.5rem;"></i>
                                    {{ ucfirst($company->status ?: 'Active') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-2">
                                    @if($company->hasLetterhead())
                                        <!-- View / Preview Button -->
                                        <button class="btn-lh-outline" data-bs-toggle="modal" data-bs-target="#viewLetterheadModal_{{ $company->id }}">
                                            <i class="bx bx-show-alt"></i> View Template
                                        </button>

                                        <!-- Replace Letterhead Button -->
                                        <button class="btn-lh-outline" data-bs-toggle="modal" data-bs-target="#uploadLetterheadModal_{{ $company->id }}">
                                            <i class="bx bx-refresh"></i> Replace
                                        </button>

                                        <!-- Delete Letterhead -->
                                        <form action="{{ route('letterhead.delete', $company) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove the fixed letterhead for {{ $company->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-lh-danger" title="Remove Letterhead">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <!-- Add / Upload Letterhead Button -->
                                        <button class="btn-lh-primary add-lh-btn" data-bs-toggle="modal" data-bs-target="#uploadLetterheadModal_{{ $company->id }}" style="background: linear-gradient(135deg, #26a96c 0%, #4ecb91 100%) !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; box-shadow: 0 6px 18px rgba(50,189,147,0.35);">
                                            <i class="bx bx-cloud-upload fs-5" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;"></i>
                                            <span style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 800;">Add Letterhead</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- ==================== UPLOAD MODAL FOR COMPANY ==================== -->
                        <div class="modal fade" id="uploadLetterheadModal_{{ $company->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content rounded-4 border-0 shadow-lg" style="background: #ffffff;">
                                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($company->logo)
                                                <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" width="48" height="48" class="rounded-3" style="object-fit:cover;">
                                            @else
                                                <div class="lh-company-avatar" style="width: 48px; height: 48px;">
                                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <h5 class="modal-title fw-bold text-dark mb-0">Upload Letterhead for {{ $company->name }}</h5>
                                                <small class="text-muted">Set fixed official document template for {{ $company->company_code }}</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <form action="{{ route('letterhead.upload', $company) }}" method="POST" enctype="multipart/form-data" id="uploadForm_{{ $company->id }}">
                                            @csrf

                                            <div class="p-3 mb-4 rounded-3" style="background: rgba(15, 116, 76, 0.06); border: 1px solid rgba(15, 116, 76, 0.15);">
                                                <div class="d-flex align-items-start gap-2">
                                                    <i class="bx bx-info-circle text-success fs-5 mt-1"></i>
                                                    <div style="font-size: 0.88rem; color: #1e293b; line-height: 1.5;">
                                                        <strong>Letterhead Template Configuration:</strong><br>
                                                        Upload the official blank letterhead document for <strong>{{ $company->name }}</strong>. Only <strong>PDF (.pdf)</strong> and <strong>DOCS (.doc, .docx)</strong> formats are supported. Once uploaded, the letterhead layout structure will be fixed for all official generated documents.
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Drag and Drop Box -->
                                            <div class="lh-dropzone" id="dropzone_{{ $company->id }}" onclick="document.getElementById('fileInput_{{ $company->id }}').click()">
                                                <input type="file" name="letterhead_file" id="fileInput_{{ $company->id }}" class="d-none" accept=".pdf,.doc,.docx" onchange="handleFileSelect(event, {{ $company->id }})">
                                                
                                                <div class="lh-dropzone-icon">
                                                    <i class="bx bx-cloud-upload"></i>
                                                </div>
                                                <div class="lh-dropzone-title">Drag and drop your letterhead file here</div>
                                                <div class="lh-dropzone-subtitle">or click to browse from your computer</div>

                                                <div class="lh-format-pills">
                                                    <span class="lh-format-pill pdf-pill">
                                                        <i class="bx bxs-file-pdf"></i> PDF Document (.pdf)
                                                    </span>
                                                    <span class="lh-format-pill doc-pill">
                                                        <i class="bx bxs-file-doc"></i> Word Document (.doc, .docx)
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Live File Selected Preview -->
                                            <div class="lh-selected-preview d-none" id="selectedPreview_{{ $company->id }}">
                                                <div class="lh-selected-info">
                                                    <i class="bx bx-file-blank" id="previewIcon_{{ $company->id }}"></i>
                                                    <div>
                                                        <div class="lh-selected-name" id="previewName_{{ $company->id }}">filename.pdf</div>
                                                        <div class="lh-selected-size" id="previewSize_{{ $company->id }}">0 KB</div>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" onclick="clearFileSelect({{ $company->id }})"></button>
                                            </div>

                                            <div class="mt-4 text-end">
                                                <button type="button" class="btn btn-light rounded-pill px-4 me-2" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn-lh-primary">
                                                    <i class="bx bx-check-circle fs-5"></i> Save & Fix Letterhead
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== VIEW TEMPLATE MODAL ==================== -->
                        @if($company->hasLetterhead())
                        <div class="modal fade" id="viewLetterheadModal_{{ $company->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content rounded-4 border-0 shadow-lg" style="background: #ffffff;">
                                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($company->logo)
                                                <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" width="44" height="44" class="rounded-3" style="object-fit:cover;">
                                            @endif
                                            <div>
                                                <h5 class="modal-title fw-bold text-dark mb-0">{{ $company->name }} - Fixed Letterhead</h5>
                                                <small class="text-muted">Uploaded on {{ $company->letterhead_uploaded_at?->format('d M, Y \a\t h:i A') }}</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">

                                        <!-- Template Representation -->
                                        <div class="lh-template-paper">
                                            <!-- Letterhead Top Header Structure -->
                                            <div class="lh-paper-header">
                                                <div class="d-flex align-items-center gap-3">
                                                    @if($company->logo)
                                                        <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" height="46">
                                                    @else
                                                        <h4 class="fw-bold text-dark mb-0">{{ $company->name }}</h4>
                                                    @endif
                                                </div>
                                                <div class="text-end">
                                                    <h5 class="fw-extrabold mb-0" style="color: var(--lh-emerald);">{{ strtoupper($company->name) }}</h5>
                                                    <small class="text-muted">{{ $company->email ?: 'info@' . strtolower($company->company_code ?: 'company') . '.com' }}</small>
                                                </div>
                                            </div>

                                            <!-- Letterhead Blank Content Area -->
                                            <div class="lh-paper-body-skeleton">
                                                <div class="text-center py-4 text-muted">
                                                    <i class="bx bx-file fs-1 color-emerald mb-2" style="color: var(--lh-emerald); opacity: 0.6;"></i>
                                                    <p class="fw-bold mb-1" style="color: var(--lh-slate-dark);">[ Official Blank Document Body ]</p>
                                                    <small>Uploaded Letterhead File: <strong>{{ $company->letterhead_original_name }}</strong></small>
                                                </div>
                                                <div class="lh-skeleton-line" style="width: 80%;"></div>
                                                <div class="lh-skeleton-line" style="width: 95%;"></div>
                                                <div class="lh-skeleton-line" style="width: 70%;"></div>
                                                <div class="lh-skeleton-line" style="width: 85%;"></div>
                                            </div>

                                            <!-- Letterhead Footer Structure -->
                                            <div class="lh-paper-footer">
                                                <span>{{ $company->address ?: 'Corporate Office Headquarters' }}</span>
                                                <span>Code: {{ $company->company_code }} | Contact: {{ $company->phone ?: '+91 1800-PMS-HUB' }}</span>
                                            </div>
                                        </div>

                                        <div class="mt-4 d-flex justify-content-between align-items-center">
                                            <span class="lh-doc-badge {{ $company->letterhead_file_type === 'docs' ? 'docs' : 'pdf' }}">
                                                <i class="bx {{ $company->letterhead_file_type === 'docs' ? 'bxs-file-doc' : 'bxs-file-pdf' }}"></i>
                                                Format: {{ strtoupper($company->letterhead_file_type ?? 'PDF') }}
                                            </span>
                                            <div>
                                                <button type="button" class="btn btn-light rounded-pill px-4 me-2" data-bs-dismiss="modal">Close</button>
                                                <a href="{{ route('letterhead.download', $company) }}" class="btn-lh-primary">
                                                    <i class="bx bx-download fs-5"></i> Download Original Letterhead
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bx bx-building fs-1 mb-2 text-secondary"></i>
                                <p class="mb-0 fw-bold">No companies available.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function handleFileSelect(event, companyId) {
        const file = event.target.files[0];
        if (file) {
            displayPreview(file, companyId);
        }
    }

    function displayPreview(file, companyId) {
        const previewBox = document.getElementById('selectedPreview_' + companyId);
        const nameEl = document.getElementById('previewName_' + companyId);
        const sizeEl = document.getElementById('previewSize_' + companyId);
        const iconEl = document.getElementById('previewIcon_' + companyId);

        nameEl.textContent = file.name;
        sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';

        if (file.name.endsWith('.pdf')) {
            iconEl.className = 'bx bxs-file-pdf text-danger';
        } else if (file.name.endsWith('.doc') || file.name.endsWith('.docx')) {
            iconEl.className = 'bx bxs-file-doc text-primary';
        } else {
            iconEl.className = 'bx bx-file-blank text-success';
        }

        previewBox.classList.remove('d-none');
    }

    function clearFileSelect(companyId) {
        const input = document.getElementById('fileInput_' + companyId);
        const previewBox = document.getElementById('selectedPreview_' + companyId);
        input.value = '';
        previewBox.classList.add('d-none');
    }

    // Drag and drop event handling
    document.addEventListener('DOMContentLoaded', () => {
        const dropzones = document.querySelectorAll('.lh-dropzone');
        
        dropzones.forEach(dz => {
            const companyId = dz.id.replace('dropzone_', '');
            const fileInput = document.getElementById('fileInput_' + companyId);

            ['dragenter', 'dragover'].forEach(eventName => {
                dz.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dz.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dz.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dz.classList.remove('dragover');
                }, false);
            });

            dz.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    displayPreview(files[0], companyId);
                }
            }, false);
        });
    });
</script>
@endsection
