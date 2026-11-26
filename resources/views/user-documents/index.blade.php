@extends('admin.layout.app')

@section('title', 'My Documents & File Storage')

@section('content')
<style>
    #user-docs-workspace.user-docs-page {
        min-height: calc(100vh - 80px);
        padding: 1.5rem 1.75rem 2.5rem;
        background: var(--bx-bg, #F8FAFC) !important;
        color: var(--bx-ink, #0F172A);
    }

    #user-docs-workspace .user-docs-shell {
        position: relative;
        max-width: 1180px;
        margin: 0 auto;
    }

    /* Hero Header Card */
    #user-docs-workspace .header-card-elevated {
        background: var(--bx-surface, #FFFFFF) !important;
        border: 1px solid var(--bx-border, #E2E8F0) !important;
        border-radius: 16px !important;
        padding: 1.35rem 1.5rem !important;
        margin-bottom: 1.25rem !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    }

    #user-docs-workspace .header-card-elevated .header-icon-badge {
        width: 46px !important;
        height: 46px !important;
        border-radius: 12px !important;
        background: #2563EB !important;
        color: #FFFFFF !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25) !important;
        flex-shrink: 0;
    }

    #user-docs-workspace .header-card-elevated .header-icon-badge i {
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        -webkit-text-stroke: 0 !important;
    }

    #user-docs-workspace .doc-hero-title {
        font-size: 1.22rem !important;
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
        color: var(--bx-ink, #0F172A) !important;
        -webkit-text-fill-color: var(--bx-ink, #0F172A) !important;
        margin: 0 0 0.2rem !important;
    }

    #user-docs-workspace .doc-hero-subtitle {
        font-size: 0.82rem !important;
        font-weight: 500 !important;
        color: var(--bx-ink-muted, #64748B) !important;
        -webkit-text-fill-color: var(--bx-ink-muted, #64748B) !important;
        margin: 0 !important;
    }

    #user-docs-workspace .doc-storage-pill {
        background: #ECFDF5 !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
        border: 1px solid #A7F3D0 !important;
        border-radius: 999px !important;
        font-size: 0.74rem !important;
        font-weight: 650 !important;
        padding: 0.42rem 0.9rem !important;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    #user-docs-workspace .doc-storage-pill i {
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
        -webkit-text-stroke: 0 !important;
        font-size: 0.9rem !important;
    }

    /* Panel Cards */
    #user-docs-workspace .doc-panel-card {
        background: var(--bx-surface, #FFFFFF) !important;
        border: 1px solid var(--bx-border, #E2E8F0) !important;
        border-radius: 16px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03) !important;
        padding: 1.35rem 1.5rem !important;
    }

    #user-docs-workspace .doc-section-heading {
        font-size: 0.96rem !important;
        font-weight: 750 !important;
        color: var(--bx-ink, #0F172A) !important;
        -webkit-text-fill-color: var(--bx-ink, #0F172A) !important;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    #user-docs-workspace .doc-section-heading i {
        color: #2563EB !important;
        -webkit-text-fill-color: #2563EB !important;
        -webkit-text-stroke: 0 !important;
        font-size: 1.15rem !important;
    }

    #user-docs-workspace .doc-field-label {
        font-size: 0.78rem !important;
        font-weight: 650 !important;
        color: var(--bx-ink, #0F172A) !important;
        -webkit-text-fill-color: var(--bx-ink, #0F172A) !important;
        margin-bottom: 0.4rem !important;
        display: block;
    }

    #user-docs-workspace .doc-max-size {
        font-size: 0.72rem !important;
        font-weight: 500 !important;
        color: var(--bx-ink-muted, #64748B) !important;
        -webkit-text-fill-color: var(--bx-ink-muted, #64748B) !important;
    }

    #user-docs-workspace .form-control,
    #user-docs-workspace .form-select {
        min-height: 42px !important;
        height: 42px;
        border-radius: 10px !important;
        border: 1px solid var(--bx-border, #E2E8F0) !important;
        background: var(--bx-surface, #FFFFFF) !important;
        color: var(--bx-ink, #0F172A) !important;
        font-size: 0.82rem !important;
        padding: 0.45rem 0.85rem !important;
        box-shadow: none !important;
    }

    #user-docs-workspace .form-control:focus,
    #user-docs-workspace .form-select:focus {
        border-color: #2563EB !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
    }

    #user-docs-workspace .form-control::placeholder {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    /* File input vertical alignment & clean button */
    #user-docs-workspace input[type="file"].form-control,
    #user-docs-workspace .doc-file-input {
        display: flex !important;
        align-items: center !important;
        min-height: 42px !important;
        height: 42px !important;
        padding: 4px 10px !important;
        line-height: 1.4 !important;
        background: var(--bx-surface, #FFFFFF) !important;
        border: 1px solid var(--bx-border, #E2E8F0) !important;
        color: var(--bx-ink-muted, #64748B) !important;
        overflow: hidden !important;
        cursor: pointer !important;
    }

    #user-docs-workspace input[type="file"].form-control::file-selector-button,
    #user-docs-workspace input[type="file"].form-control::-webkit-file-upload-button,
    #user-docs-workspace .doc-file-input::file-selector-button,
    #user-docs-workspace .doc-file-input::-webkit-file-upload-button {
        margin: 0 !important;
        margin-inline-end: 10px !important;
        padding: 5px 12px !important;
        border-radius: 7px !important;
        border: 1px solid #CBD5E1 !important;
        background: #F1F5F9 !important;
        color: #334155 !important;
        -webkit-text-fill-color: #334155 !important;
        font-weight: 600 !important;
        font-size: 0.78rem !important;
        line-height: 1.2 !important;
        cursor: pointer !important;
        transition: all 0.18s ease !important;
    }

    #user-docs-workspace input[type="file"].form-control:hover::file-selector-button,
    #user-docs-workspace .doc-file-input:hover::file-selector-button {
        background: #E2E8F0 !important;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
    }

    #user-docs-workspace .btn-upload-submit {
        background: #2563EB !important;
        border: 1px solid #2563EB !important;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        border-radius: 10px !important;
        min-height: 42px !important;
        height: 42px;
        font-size: 0.84rem !important;
        font-weight: 650 !important;
        padding: 0.5rem 1.25rem !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        transition: all 0.18s ease;
    }

    #user-docs-workspace .btn-upload-submit i {
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        -webkit-text-stroke: 0 !important;
    }

    #user-docs-workspace .btn-upload-submit:hover {
        background: #1D4ED8 !important;
        border-color: #1D4ED8 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.32) !important;
    }

    /* Clean Standalone Tabs (no white pill container) */
    #user-docs-workspace .doc-nav-pills {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap;
        gap: 8px !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        margin: 0.25rem 0 1.15rem !important;
        list-style: none !important;
    }

    #user-docs-workspace .doc-tab-btn {
        border: 1px solid transparent !important;
        background: transparent !important;
        color: var(--bx-ink-body, #475569) !important;
        -webkit-text-fill-color: var(--bx-ink-body, #475569) !important;
        font-weight: 600 !important;
        font-size: 0.82rem !important;
        border-radius: 10px !important;
        padding: 0.55rem 1.1rem !important;
        min-height: 38px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.45rem !important;
        box-shadow: none !important;
        cursor: pointer !important;
        transition: all 0.18s ease !important;
    }

    #user-docs-workspace .doc-tab-btn i {
        color: var(--bx-ink-muted, #64748B) !important;
        -webkit-text-fill-color: var(--bx-ink-muted, #64748B) !important;
        -webkit-text-stroke: 0 !important;
        font-size: 1rem !important;
    }

    #user-docs-workspace .doc-tab-btn:hover:not(.active) {
        background: #F1F5F9 !important;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
    }

    #user-docs-workspace .doc-tab-btn.active {
        background: #2563EB !important;
        border-color: #2563EB !important;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        font-weight: 650 !important;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25) !important;
    }

    #user-docs-workspace .doc-tab-btn.active i {
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
    }

    /* Files Counter Pill */
    #user-docs-workspace .doc-total-pill {
        background: var(--bx-surface-2, #F8FAFC);
        border: 1px solid var(--bx-border, #E2E8F0);
        color: var(--bx-ink-muted, #64748B) !important;
        -webkit-text-fill-color: var(--bx-ink-muted, #64748B) !important;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 650;
        padding: 0.26rem 0.72rem;
    }

    /* Empty State & Footer */
    #user-docs-workspace .doc-empty-state {
        padding: 3.25rem 1rem 2.75rem;
        text-align: center;
    }

    #user-docs-workspace .doc-empty-icon {
        font-size: 3rem !important;
        color: #2563EB !important;
        -webkit-text-fill-color: #2563EB !important;
        -webkit-text-stroke: 0 !important;
        margin-bottom: 0.85rem;
        display: inline-block;
    }

    #user-docs-workspace .doc-empty-title {
        font-size: 0.92rem !important;
        font-weight: 700 !important;
        color: var(--bx-ink, #0F172A) !important;
        -webkit-text-fill-color: var(--bx-ink, #0F172A) !important;
        margin-bottom: 0.35rem;
    }

    #user-docs-workspace .doc-empty-sub {
        font-size: 0.78rem !important;
        font-weight: 500 !important;
        color: var(--bx-ink-muted, #64748B) !important;
        -webkit-text-fill-color: var(--bx-ink-muted, #64748B) !important;
        max-width: 390px;
        margin: 0 auto;
        line-height: 1.5;
    }

    #user-docs-workspace .doc-panel-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding-top: 0.85rem;
        border-top: 1px solid var(--bx-border, #F1F5F9);
        font-size: 0.72rem;
        font-weight: 500;
        color: var(--bx-ink-muted, #64748B) !important;
        -webkit-text-fill-color: var(--bx-ink-muted, #64748B) !important;
    }

    /* Document Slot Cards */
    #user-docs-workspace .doc-slot-card {
        background: var(--bx-surface, #FFFFFF) !important;
        border-radius: 14px !important;
        border: 1px solid var(--bx-border, #E2E8F0) !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03) !important;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    #user-docs-workspace .doc-slot-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06) !important;
        border-color: #93C5FD !important;
    }

    #user-docs-workspace .doc-file-preview {
        background: var(--bx-surface-2, #F8FAFC) !important;
        border: 1px solid var(--bx-border, #E2E8F0) !important;
        border-radius: 10px !important;
    }

    #user-docs-workspace .doc-title,
    #user-docs-workspace .doc-filename {
        color: var(--bx-ink, #0F172A) !important;
        -webkit-text-fill-color: var(--bx-ink, #0F172A) !important;
    }

    #user-docs-workspace .doc-title i,
    #user-docs-workspace .doc-filename i {
        color: #2563EB !important;
        -webkit-text-fill-color: #2563EB !important;
        -webkit-text-stroke: 0 !important;
    }

    #user-docs-workspace .doc-file-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        min-width: 0;
    }

    #user-docs-workspace .doc-file-actions > a {
        flex: 1 1 0;
        width: auto;
        min-width: 0;
        border-radius: 8px !important;
        font-size: 0.78rem !important;
        font-weight: 650 !important;
    }

    #user-docs-workspace .doc-file-actions > form {
        flex: 0 0 auto;
        margin: 0;
    }

    #user-docs-workspace .doc-table-head {
        background: var(--bx-surface-2, #F8FAFC) !important;
        color: var(--bx-ink, #0F172A) !important;
    }

    /* Dark Mode Support */
    html[data-pms-theme="dark"] #user-docs-workspace.user-docs-page {
        background: #070B1A !important;
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] #user-docs-workspace .header-card-elevated,
    html[data-pms-theme="dark"] #user-docs-workspace .doc-panel-card,
    html[data-pms-theme="dark"] #user-docs-workspace .doc-slot-card {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.1) !important;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35) !important;
    }

    html[data-pms-theme="dark"] #user-docs-workspace .doc-storage-pill {
        background: rgba(16, 185, 129, 0.16) !important;
        border-color: rgba(52, 211, 153, 0.32) !important;
        color: #34D399 !important;
        -webkit-text-fill-color: #34D399 !important;
    }

    html[data-pms-theme="dark"] #user-docs-workspace .doc-storage-pill i {
        color: #34D399 !important;
        -webkit-text-fill-color: #34D399 !important;
    }

    html[data-pms-theme="dark"] #user-docs-workspace .form-control,
    html[data-pms-theme="dark"] #user-docs-workspace .form-select,
    html[data-pms-theme="dark"] #user-docs-workspace input[type="file"].form-control,
    html[data-pms-theme="dark"] #user-docs-workspace .doc-file-input {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] #user-docs-workspace input[type="file"].form-control::file-selector-button,
    html[data-pms-theme="dark"] #user-docs-workspace .doc-file-input::file-selector-button {
        background: #1A2247 !important;
        border-color: rgba(238, 241, 251, 0.22) !important;
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] #user-docs-workspace .doc-tab-btn:hover:not(.active) {
        background: #141B3D !important;
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] #user-docs-workspace .doc-total-pill,
    html[data-pms-theme="dark"] #user-docs-workspace .doc-file-preview,
    html[data-pms-theme="dark"] #user-docs-workspace .doc-table-head,
    html[data-pms-theme="dark"] #user-docs-workspace thead th {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] #globalDocInfoModal .modal-content {
        background: #0F1530 !important;
        border: 1px solid rgba(238, 241, 251, 0.12) !important;
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] #globalDocInfoModal .bg-light,
    html[data-pms-theme="dark"] #globalDocInfoModal .list-group-item {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.1) !important;
        color: #CBD5E1 !important;
    }
</style>

<div id="user-docs-workspace" class="user-docs-page">
    <div class="user-docs-shell">

        <!-- Page Header -->
        <div class="header-card-elevated d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-badge header-icon-badge">
                    <i class="bx bx-check-shield"></i>
                </div>
                <div>
                    <h1 class="doc-hero-title">My Official Documents</h1>
                    <p class="doc-hero-subtitle">Upload any document into your role storage database ({{ strtoupper($userRole) }} DB).</p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="doc-storage-pill">
                    <i class="bx bx-data"></i> {{ strtoupper($userRole) }} Storage Database
                </span>
            </div>
        </div>

        <!-- Alert Notifications -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" style="background: rgba(220, 252, 231, 0.95); color: #065f46; border-left: 4px solid #10b981 !important;" role="alert">
                <i class="fas fa-check-circle fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" role="alert">
                <i class="fas fa-exclamation-circle fs-5 me-2"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Upload Any Document Card -->
        <div class="card doc-panel-card mb-3">
            <h5 class="doc-section-heading mb-3"><i class="bx bx-cloud-upload"></i> Upload New Document</h5>
            <form method="POST" action="{{ route('my-documents.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="doc-field-label">Document Category / Title <span class="text-danger">*</span></label>
                        <input type="text" name="document_type" class="form-control" placeholder="Enter document title (e.g. Passport, NID, Certificate, Contract)..." required>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="doc-field-label mb-0">Select File <span class="text-danger">*</span></label>
                            <span class="doc-max-size">Max: {{ $maxSizeMb }} MB</span>
                        </div>
                        <input type="file" name="document_file" class="form-control doc-file-input" required>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-upload-submit w-100">
                            <i class="bx bx-upload"></i> Upload Document
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @php
            $activeTab = request('tab', 'my-docs');
            // Old notification links open the repository tab; other roles only have their own documents.
            if (! $canViewAllDocuments) $activeTab = 'my-docs';
        @endphp

        <!-- Navigation Tabs (company Admin / Super Admin: every role's documents) -->
        @if($canViewAllDocuments)
            <ul class="nav doc-nav-pills" id="userDocTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link doc-tab-btn {{ $activeTab !== 'employee' ? 'active' : '' }}" id="my-docs-tab" data-bs-toggle="tab" data-bs-target="#my-docs-pane" type="button" role="tab">
                        <i class="bx bx-folder"></i> My Uploaded Documents ({{ count($myDocs) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link doc-tab-btn {{ $activeTab === 'employee' ? 'active' : '' }}" id="emp-docs-tab" data-bs-toggle="tab" data-bs-target="#emp-docs-pane" type="button" role="tab">
                        <i class="bx bx-data"></i> Role Storage Databases Repository ({{ count($repositoryDocs) }})
                    </button>
                </li>
            </ul>
        @endif

        <div class="tab-content" id="userDocTabContent">

            <!-- TAB 1: My Documents -->
            <div class="tab-pane fade {{ $activeTab !== 'employee' ? 'show active' : '' }}" id="my-docs-pane" role="tabpanel">
                <div class="card doc-panel-card">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <h5 class="doc-section-heading"><i class="bx bx-folder-open"></i> My Uploaded Files</h5>
                        <span class="doc-total-pill">Total: {{ count($myDocs) }} {{ \Illuminate\Support\Str::plural('file', count($myDocs)) }}</span>
                    </div>

                    @if($myDocs->count() > 0)
                        <div class="row g-3 mt-1">
                            @foreach($myDocs as $doc)
                                <div class="col-md-6 col-lg-4">
                                    <div class="doc-slot-card p-3">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="fw-bold doc-title fs-6 text-truncate" title="{{ $doc->document_type }}">
                                                    <i class="fas fa-file-invoice me-1.5"></i> {{ $doc->document_type }}
                                                </div>

                                                <!-- Info Button (Seen By Who) -->
                                                <button type="button" class="btn btn-sm btn-outline-info rounded-circle btn-show-doc-info" style="width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                    data-title="{{ $doc->document_type }}"
                                                    data-file="{{ $doc->file_name }}"
                                                    data-uploader="{{ $doc->user->name ?? 'Me' }}"
                                                    data-history-url="{{ route('my-documents.history', ['type' => $doc->table_type ?? $userRole, 'id' => $doc->id]) }}"
                                                    title="Seen By Who">
                                                    <i class="fas fa-info"></i>
                                                </button>
                                            </div>

                                            <div class="p-3 doc-file-preview mb-3">
                                                <div class="fw-bold doc-filename text-truncate" title="{{ $doc->file_name }}">
                                                    <i class="fas fa-paperclip me-1"></i> {{ $doc->file_name }}
                                                </div>
                                                <div class="small text-muted mt-1 d-flex justify-content-between">
                                                    <span>{{ strtoupper($doc->file_type ?? 'FILE') }} • {{ round(($doc->file_size ?? 0) / 1024, 1) }} KB</span>
                                                    <span>{{ $doc->uploaded_at ? $doc->uploaded_at->format('M d, Y') : '' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="doc-file-actions">
                                            <a href="{{ route('my-documents.download', ['type' => $doc->table_type ?? $userRole, 'id' => $doc->id]) }}" class="btn btn-sm btn-outline-primary py-2 fw-semibold">
                                                <i class="fas fa-download me-1"></i> Download
                                            </a>
                                            <form method="POST" action="{{ route('my-documents.destroy', ['type' => $doc->table_type ?? $userRole, 'id' => $doc->id]) }}" onsubmit="return confirm('Delete this uploaded document?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 p-2" title="Delete Upload">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="doc-empty-state">
                            <i class="bx bxs-folder doc-empty-icon"></i>
                            <div class="doc-empty-title">You haven't uploaded any documents yet.</div>
                            <p class="doc-empty-sub">Your submitted identity papers, agreements, certificates, and payroll records will appear right here once uploaded.</p>
                        </div>
                    @endif

                    <div class="doc-panel-footer">
                        <span>Security encryption: AES-256 Bit</span>
                        <span>All uploads subject to role verification</span>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Role Storage Databases Repository (company Admin / Super Admin only) -->
            @if($canViewAllDocuments)
                <div class="tab-pane fade {{ $activeTab === 'employee' ? 'show active' : '' }}" id="emp-docs-pane" role="tabpanel">
                    <div class="card doc-panel-card">

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                            <div>
                                <h5 class="doc-section-heading mb-1"><i class="bx bx-data"></i> Stored Staff & Employee Documents</h5>
                                <p class="text-muted small mb-0">Search and download uploaded documents across accessible role databases.</p>
                            </div>
                        </div>

                        <!-- Filter Bar -->
                        <form method="GET" action="{{ route('my-documents.index') }}" class="mb-4">
                            <input type="hidden" name="tab" value="employee">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-7">
                                    <div class="input-group rounded-3 border overflow-hidden">
                                        <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" name="search" class="form-control border-0" placeholder="Search by staff name, email, or document title..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <select name="role_type" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Accessible Databases</option>
                                        <option value="employee" {{ request('role_type') === 'employee' ? 'selected' : '' }}>Employee Database</option>
                                        @if($canViewAllDocuments)
                                             <option value="hr" {{ request('role_type') === 'hr' ? 'selected' : '' }}>HR Database</option>
                                        @endif
                                        @if($canViewAllDocuments)
                                             <option value="manager" {{ request('role_type') === 'manager' ? 'selected' : '' }}>Manager Database</option>
                                             <option value="admin" {{ request('role_type') === 'admin' ? 'selected' : '' }}>Admin Database</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <a href="{{ route('my-documents.index', ['tab' => 'employee']) }}" class="btn btn-outline-secondary w-100 rounded-3 py-2" title="Reset Filters"><i class="fas fa-undo"></i></a>
                                </div>
                            </div>
                        </form>

                        <!-- Repository Table -->
                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="doc-table-head">
                                    <tr>
                                        <th class="py-3 px-4 fw-bold">Staff Details</th>
                                        <th class="py-3 px-4 fw-bold">Document Title</th>
                                        <th class="py-3 px-4 fw-bold">Uploaded File</th>
                                        <th class="py-3 px-4 fw-bold">Upload Date</th>
                                        <th class="py-3 px-4 fw-bold text-end">Download & Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($repositoryDocs as $doc)
                                        <tr>
                                            <td class="py-3 px-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-circle" style="width: 38px; height: 38px; border-radius: 10px; background: #2563EB; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">
                                                        {{ strtoupper(substr($doc->user->name ?? 'U', 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark small">{{ $doc->user->name ?? 'Unknown Staff' }}</div>
                                                        <small class="text-muted text-xs">
                                                            {{ $doc->user->email ?? '' }} •
                                                            <span class="badge bg-light text-dark border">{{ ucfirst($doc->user->role ?? $doc->table_type) }}</span>
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">{{ strtoupper($doc->table_type) }} DB</span>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 fw-semibold text-dark small">
                                                <i class="fas fa-file-alt text-primary me-1.5"></i> {{ $doc->document_type }}
                                            </td>
                                            <td class="py-3 px-4 small">
                                                <div class="fw-medium text-secondary text-truncate" style="max-width: 220px;" title="{{ $doc->file_name }}">
                                                    {{ $doc->file_name }}
                                                </div>
                                                <small class="text-muted text-xs">{{ strtoupper($doc->file_type ?? 'FILE') }} • {{ round(($doc->file_size ?? 0) / 1024, 1) }} KB</small>
                                            </td>
                                            <td class="py-3 px-4 text-muted small">
                                                {{ $doc->uploaded_at ? $doc->uploaded_at->format('M d, Y g:i A') : 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-end">
                                                <div class="d-inline-flex gap-2 align-items-center">
                                                    <a href="{{ route('my-documents.download', ['type' => $doc->table_type, 'id' => $doc->id]) }}" class="btn btn-sm btn-outline-primary rounded-3 px-3" title="Download File">
                                                        <i class="fas fa-download me-1"></i> Download
                                                    </a>

                                                    <!-- Info Button (Seen By Who) -->
                                                    <button type="button" class="btn btn-sm btn-outline-info rounded-circle btn-show-doc-info" style="width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                        data-title="{{ $doc->document_type }}"
                                                        data-file="{{ $doc->file_name }}"
                                                        data-uploader="{{ $doc->user->name ?? 'Staff' }}"
                                                        data-history-url="{{ route('my-documents.history', ['type' => $doc->table_type, 'id' => $doc->id]) }}"
                                                        title="Seen By Who">
                                                        <i class="fas fa-info"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bx bxs-folder-open fs-1 text-primary mb-2 d-block"></i>
                                                <p class="mb-0 fw-medium">No uploaded documents match your filter query.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>
</div>

<!-- Reusable Global Info Modal (Outside any transformed container to prevent blinking) -->
<div class="modal fade" id="globalDocInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-info-circle text-info me-2"></i> Document Access History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-start" id="globalDocInfoBody">
                <!-- Content dynamically populated via JS -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    var historyRequest = 0;
    document.addEventListener('click', async function(e) {
        var btn = e.target.closest('.btn-show-doc-info');
        if (!btn) return;

        var title = btn.getAttribute('data-title') || '';
        var filename = btn.getAttribute('data-file') || '';
        var uploader = btn.getAttribute('data-uploader') || '';
        var views = [];
        var requestId = ++historyRequest;
        var body = document.getElementById('globalDocInfoBody');
        var modalEl = document.getElementById('globalDocInfoModal');
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        body.innerHTML = '<p class="text-muted mb-0">Loading document access history...</p>';
        modal.show();
        try {
            var response = await fetch(btn.getAttribute('data-history-url'), {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });
            if (!response.ok) throw new Error('History request failed');
            views = await response.json();
            if (!Array.isArray(views)) throw new Error('Invalid history response');
        } catch(err) {
            if (requestId === historyRequest) {
                body.innerHTML = '<p class="text-danger mb-0">Unable to load document access history. Please try again.</p>';
            }
            return;
        }
        if (requestId !== historyRequest) return;

        var html = '<div class="mb-3 p-3 bg-light rounded-3">';
        html += '<div class="fw-bold text-dark fs-6">' + escapeHtml(title) + '</div>';
        html += '<small class="text-muted">' + escapeHtml(filename) + ' • Uploaded by ' + escapeHtml(uploader) + '</small>';
        html += '</div>';
        html += '<h6 class="fw-bold fs-6 text-secondary mb-2">Seen / Downloaded By:</h6>';

        if (views && views.length > 0) {
            html += '<div class="list-group list-group-flush rounded-3 border">';
            views.forEach(function(v) {
                var name = (v.viewer && v.viewer.name) ? v.viewer.name : 'System User';
                var email = (v.viewer && v.viewer.email) ? v.viewer.email : '';
                var role = (v.viewer && v.viewer.role) ? v.viewer.role : 'User';
                var time = v.viewed_at ? window.PMSTime.format(v.viewed_at, { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }) : '';

                html += '<div class="list-group-item d-flex justify-content-between align-items-center py-2.5">';
                html += '<div><div class="fw-semibold text-dark small">' + escapeHtml(name) + '</div>';
                html += '<small class="text-muted text-xs">' + escapeHtml(email) + ' • <span class="badge bg-secondary-subtle text-secondary">' + escapeHtml(role) + '</span></small></div>';
                html += '<span class="text-muted text-xs font-monospace">' + escapeHtml(time) + '</span>';
                html += '</div>';
            });
            html += '</div>';
        } else {
            html += '<div class="text-center py-4 text-muted bg-light rounded-3">';
            html += '<i class="fas fa-eye-slash fs-3 mb-2 opacity-50"></i>';
            html += '<p class="mb-0 small fw-medium">No one has viewed or downloaded this document yet.</p>';
            html += '</div>';
        }

        document.getElementById('globalDocInfoBody').innerHTML = html;
    });

    // Auto-switch tab if specified in URL query string or hash
    var urlParams = new URLSearchParams(window.location.search);
    var tabParam = urlParams.get('tab');
    var hash = window.location.hash;

    if (tabParam === 'employee' || hash === '#emp-docs-pane' || hash === '#employee') {
        var empTabBtn = document.getElementById('emp-docs-tab');
        if (empTabBtn) {
            var tab = bootstrap.Tab.getInstance(empTabBtn) || new bootstrap.Tab(empTabBtn);
            tab.show();
        }
    } else if (tabParam === 'my-docs' || hash === '#my-docs-pane') {
        var myTabBtn = document.getElementById('my-docs-tab');
        if (myTabBtn) {
            var tab = bootstrap.Tab.getInstance(myTabBtn) || new bootstrap.Tab(myTabBtn);
            tab.show();
        }
    }
});
</script>
@endsection
