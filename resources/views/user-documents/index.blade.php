@extends('admin.layout.app')

@section('title', 'My Documents & File Storage')

@push('styles')
<style>
    .user-docs-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .user-docs-shell {
        position: relative;
        max-width: 1350px;
        margin: 0 auto;
    }

    .header-card-elevated {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
    }

    .doc-slot-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(15px);
        border-radius: 24px;
        border: 1px solid rgba(16, 185, 129, 0.18);
        box-shadow: 0 8px 25px -5px rgba(16, 185, 129, 0.06);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .doc-slot-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 35px -5px rgba(16, 185, 129, 0.12);
        border-color: #34d399;
    }

    .doc-slot-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(16, 185, 129, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .doc-slot-body {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .btn-upload-submit {
        background: linear-gradient(145deg, #10b981, #059669);
        color: white !important;
        border: none;
        border-radius: 30px;
        font-weight: 700;
        padding: 0.6rem 1.4rem;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
        transition: all 0.25s ease;
    }

    .btn-upload-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(5, 150, 105, 0.4);
    }

    .doc-nav-pills {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(16, 185, 129, 0.2) !important;
        padding: 8px 12px !important;
        border-radius: 50px !important;
        box-shadow: 0 8px 25px -5px rgba(16, 185, 129, 0.08) !important;
        margin-bottom: 2rem !important;
        list-style: none !important;
    }

    .doc-tab-btn {
        border: 1px solid transparent !important;
        background: transparent !important;
        color: #475569 !important;
        font-weight: 800 !important;
        font-size: 0.92rem !important;
        border-radius: 40px !important;
        padding: 0.7rem 1.6rem !important;
        transition: all 0.25s ease !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        cursor: pointer !important;
    }

    .doc-tab-btn.active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 8px 22px -4px rgba(5, 150, 105, 0.45) !important;
    }
</style>
@endpush

@section('content')
<div class="user-docs-page">
    <div class="user-docs-shell">

        <!-- Page Header -->
        <div class="header-card-elevated d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-badge" style="width: 52px; height: 52px; border-radius: 18px; background: linear-gradient(135deg, #10b981, #059669); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 8px 20px -4px rgba(5, 150, 105, 0.4);">
                    <i class="fas fa-file-shield"></i>
                </div>
                <div>
                    <h1 class="fs-4 fw-bold text-dark mb-1">My Official Documents</h1>
                    <p class="text-muted small mb-0">Upload any document into your role storage database (<strong>{{ strtoupper($userRole) }} DB</strong>).</p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold">
                    <i class="fas fa-database me-1.5"></i> {{ strtoupper($userRole) }} Storage Database
                </span>
            </div>
        </div>

        <!-- Alert Notifications -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" style="background: rgba(220, 252, 231, 0.95); color: #065f46; border-left: 5px solid #10b981 !important;" role="alert">
                <i class="fas fa-check-circle fs-4 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" role="alert">
                <i class="fas fa-exclamation-circle fs-4 me-2"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Upload Any Document Card -->
        <div class="card rounded-4 border-0 shadow-sm mb-4 p-4" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-cloud-upload-alt text-emerald-600 me-2"></i> Upload New Document</h5>
            <form method="POST" action="{{ route('my-documents.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-dark small mb-1">Document Category / Title <span class="text-danger">*</span></label>
                        <input type="text" name="document_type" class="form-control rounded-3" placeholder="Enter document title (e.g. Passport, NID, Certificate, Contract)..." required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark small mb-1">Select File <span class="text-danger">*</span> (Max: {{ $maxSizeMb }} MB)</label>
                        <input type="file" name="document_file" class="form-control rounded-3" required>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-upload-submit w-100 py-2">
                            <i class="fas fa-upload me-1.5"></i> Upload Document
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @php
            $activeTab = request('tab', 'my-docs');
        @endphp

        <!-- Navigation Tabs (For HR, Manager, Admin) -->
        @if(in_array($userRole, ['admin', 'hr', 'manager']))
            <ul class="nav doc-nav-pills" id="userDocTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link doc-tab-btn {{ $activeTab !== 'employee' ? 'active' : '' }}" id="my-docs-tab" data-bs-toggle="tab" data-bs-target="#my-docs-pane" type="button" role="tab">
                        <i class="fas fa-folder me-2"></i> My Uploaded Documents ({{ count($myDocs) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link doc-tab-btn {{ $activeTab === 'employee' ? 'active' : '' }}" id="emp-docs-tab" data-bs-toggle="tab" data-bs-target="#emp-docs-pane" type="button" role="tab">
                        <i class="fas fa-database me-2"></i> Role Storage Databases Repository ({{ count($repositoryDocs) }})
                    </button>
                </li>
            </ul>
        @endif

        <div class="tab-content" id="userDocTabContent">

            <!-- TAB 1: My Documents -->
            <div class="tab-pane fade {{ $activeTab !== 'employee' ? 'show active' : '' }}" id="my-docs-pane" role="tabpanel">
                <div class="card rounded-4 border-0 shadow-sm p-4" style="background: rgba(255, 255, 255, 0.95);">
                    <h5 class="fw-bold text-dark mb-4"><i class="fas fa-folder-open text-emerald-600 me-2"></i> My Uploaded Files</h5>

                    @if($myDocs->count() > 0)
                        <div class="row g-4">
                            @foreach($myDocs as $doc)
                                <div class="col-md-6 col-lg-4">
                                    <div class="doc-slot-card p-4">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="fw-bold text-dark fs-6 text-truncate" title="{{ $doc->document_type }}">
                                                    <i class="fas fa-file-invoice text-emerald-600 me-1.5"></i> {{ $doc->document_type }}
                                                </div>
                                                
                                                <!-- Info Button (Seen By Who) -->
                                                <button type="button" class="btn btn-sm btn-outline-info rounded-circle btn-show-doc-info" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" 
                                                    data-title="{{ $doc->document_type }}"
                                                    data-file="{{ $doc->file_name }}"
                                                    data-uploader="{{ $doc->user->name ?? 'Me' }}"
                                                    data-views='@json($doc->views)'
                                                    title="Seen By Who">
                                                    <i class="fas fa-info"></i>
                                                </button>
                                            </div>

                                            <div class="p-3 bg-light rounded-4 border mb-3">
                                                <div class="fw-bold text-dark text-truncate" title="{{ $doc->file_name }}">
                                                    <i class="fas fa-paperclip me-1 text-emerald-600"></i> {{ $doc->file_name }}
                                                </div>
                                                <div class="small text-muted mt-1 d-flex justify-content-between">
                                                    <span>{{ strtoupper($doc->file_type ?? 'FILE') }} • {{ round(($doc->file_size ?? 0) / 1024, 1) }} KB</span>
                                                    <span>{{ $doc->uploaded_at ? $doc->uploaded_at->format('M d, Y') : '' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 align-items-center">
                                            <a href="{{ route('my-documents.download', ['type' => $userRole, 'id' => $doc->id]) }}" class="btn btn-sm btn-outline-success w-100 rounded-pill py-2 font-weight-bold">
                                                <i class="fas fa-download me-1"></i> Download
                                            </a>

                                            <form method="POST" action="{{ route('my-documents.destroy', ['type' => $userRole, 'id' => $doc->id]) }}" onsubmit="return confirm('Delete this uploaded document?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Delete Upload">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fs-1 text-emerald-300 mb-2"></i>
                            <p class="mb-0 fw-medium">You haven't uploaded any documents yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 2: Role Storage Databases Repository (For HR, Manager, Admin) -->
            @if(in_array($userRole, ['admin', 'hr', 'manager']))
                <div class="tab-pane fade {{ $activeTab === 'employee' ? 'show active' : '' }}" id="emp-docs-pane" role="tabpanel">
                    <div class="card rounded-4 border-0 shadow-sm p-4" style="background: rgba(255, 255, 255, 0.95);">
                        
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                            <div>
                                <h5 class="fw-bold text-dark mb-1"><i class="fas fa-database text-emerald-600 me-2"></i> Stored Staff & Employee Documents</h5>
                                <p class="text-muted small mb-0">Search and download uploaded documents across accessible role databases.</p>
                            </div>
                        </div>

                        <!-- Filter Bar -->
                        <form method="GET" action="{{ route('my-documents.index') }}" class="mb-4">
                            <input type="hidden" name="tab" value="employee">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-7">
                                    <div class="input-group rounded-4 border overflow-hidden">
                                        <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" name="search" class="form-control border-0" placeholder="Search by staff name, email, or document title..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <select name="role_type" class="form-select rounded-4 py-2" onchange="this.form.submit()">
                                        <option value="">All Accessible Databases</option>
                                        <option value="employee" {{ request('role_type') === 'employee' ? 'selected' : '' }}>Employee Database</option>
                                        @if(in_array($userRole, ['admin', 'hr', 'manager']))
                                            <option value="hr" {{ request('role_type') === 'hr' ? 'selected' : '' }}>HR Database</option>
                                        @endif
                                        @if(in_array($userRole, ['admin', 'hr', 'manager']))
                                            <option value="manager" {{ request('role_type') === 'manager' ? 'selected' : '' }}>Manager Database</option>
                                        @endif
                                        @if($userRole === 'admin')
                                            <option value="admin" {{ request('role_type') === 'admin' ? 'selected' : '' }}>Admin Database</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <a href="{{ route('my-documents.index', ['tab' => 'employee']) }}" class="btn btn-outline-secondary w-100 rounded-4 py-2" title="Reset Filters"><i class="fas fa-undo"></i></a>
                                </div>
                            </div>
                        </form>

                        <!-- Repository Table -->
                        <div class="table-responsive rounded-4 border border-emerald-subtle shadow-sm">
                            <table class="table table-hover align-middle mb-0">
                                <thead style="background: linear-gradient(90deg, #ecfdf5, #f0fdf4); color: #0a2e1f;">
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
                                                    <div class="avatar-circle" style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">
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
                                                <i class="fas fa-file-alt text-emerald-600 me-1.5"></i> {{ $doc->document_type }}
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
                                                    <a href="{{ route('my-documents.download', ['type' => $doc->table_type, 'id' => $doc->id]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3" title="Download File">
                                                        <i class="fas fa-download me-1"></i> Download
                                                    </a>

                                                    <!-- Info Button (Seen By Who) -->
                                                    <button type="button" class="btn btn-sm btn-outline-info rounded-circle btn-show-doc-info" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                        data-title="{{ $doc->document_type }}"
                                                        data-file="{{ $doc->file_name }}"
                                                        data-uploader="{{ $doc->user->name ?? 'Staff' }}"
                                                        data-views='@json($doc->views)'
                                                        title="Seen By Who">
                                                        <i class="fas fa-info"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fas fa-folder-open fs-1 text-emerald-300 mb-2"></i>
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

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-show-doc-info');
        if (!btn) return;

        var title = btn.getAttribute('data-title') || '';
        var filename = btn.getAttribute('data-file') || '';
        var uploader = btn.getAttribute('data-uploader') || '';
        var views = [];
        try {
            views = JSON.parse(btn.getAttribute('data-views') || '[]');
        } catch(err) {
            views = [];
        }

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
                var time = v.viewed_at ? new Date(v.viewed_at).toLocaleString() : '';

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
        var modalEl = document.getElementById('globalDocInfoModal');
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
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
