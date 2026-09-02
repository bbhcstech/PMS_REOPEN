@extends('admin.layout.app')

@section('title', 'Send ' . ($currentTemplate['name'] ?? 'Official') . ' Letter')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap');

    .leave-form-page {
        padding: 24px 30px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f0f9f4 0%, #f7fbff 100%);
        color: #102119;
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .leave-breadcrumb, .leave-form-hero, .form-card {
        border: 1px solid rgba(16, 185, 129, 0.14);
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 16px 36px -20px rgba(15, 23, 42, 0.18);
    }

    .leave-breadcrumb {
        display: inline-flex;
        gap: 8px;
        align-items: center;
        padding: 10px 18px;
        border-radius: 14px;
        color: #0f744c;
        font-weight: 800;
        font-size: 0.88rem;
        margin-bottom: 20px;
    }
    .leave-breadcrumb a {
        color: #0f744c;
        text-decoration: none;
    }

    .leave-form-hero {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: center;
        padding: 24px 28px;
        border-radius: 20px;
        margin-bottom: 22px;
        position: relative;
        overflow: hidden;
    }
    .leave-form-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981 0%, #0f744c 50%, #1e40af 100%);
    }

    .leave-form-hero h1 {
        margin: 0 0 6px;
        font-size: 30px;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.02em;
    }
    .leave-form-hero p {
        margin: 0;
        color: #64748b;
        font-weight: 600;
        font-size: 0.94rem;
    }

    .form-card {
        padding: 26px;
        border-radius: 22px;
    }

    .form-grid.two {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 26px;
    }

    .sample-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .sample-panel-header h3 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 900;
        color: #0f172a;
    }

    .sample-textarea {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px 18px;
        font-size: 0.90rem;
        line-height: 1.6;
        color: #1e293b;
        font-weight: 550;
        resize: vertical;
        width: 100%;
        min-height: 440px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    .sample-textarea:focus {
        outline: none;
        background-color: #ffffff;
        border-color: #10b981;
    }

    label {
        display: block;
        color: #475569;
        text-transform: uppercase;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }
    label span {
        color: #dc2626;
    }

    .form-control, .form-select {
        min-height: 46px;
        border-radius: 12px;
        border: 1px solid #dbe7e1;
        font-weight: 600;
        font-size: 0.92rem;
        color: #0f172a;
        transition: all 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }

    textarea.form-control {
        font-family: inherit;
        white-space: pre-wrap;
        line-height: 1.6;
    }

    /* Action Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 12px;
        min-height: 44px;
        padding: 0.6rem 1.25rem;
        font-weight: 800;
        font-size: 0.88rem;
        border: 0;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #0f744c 100%);
        color: #ffffff !important;
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
    }
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 22px rgba(16, 185, 129, 0.45);
        color: #ffffff !important;
    }

    .btn-word {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff !important;
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
    }
    .btn-word:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.45);
        color: #ffffff !important;
    }

    .btn-light, .btn-secondary {
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.22);
    }
    .btn-light:hover, .btn-secondary:hover {
        background: #e1f5ec;
        color: #0a5c3a;
    }

    /* Letterhead Customization Box */
    .lh-custom-box {
        background: #f8fafc;
        border: 1px solid #d1fae5;
        border-radius: 16px;
        padding: 18px;
        margin-top: 16px;
        margin-bottom: 16px;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.05);
    }

    .upload-slot-card {
        background: #ffffff;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 14px;
        transition: all 0.2s ease;
    }
    .upload-slot-card:hover {
        border-color: #10b981;
        background: #fcfdfd;
    }

    /* Preview Sheet Mini Modal */
    .lh-preview-card-a4 {
        width: 100%;
        max-width: 480px;
        min-height: 680px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 16px 40px rgba(0,0,0,0.12);
        border-radius: 6px;
        position: relative;
        padding: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background-size: 100% 100%;
        background-repeat: no-repeat;
        background-position: center;
    }

    .modal-preview-header-img {
        width: 100%;
        height: 65px;
        object-fit: fill;
        display: block;
        flex-shrink: 0;
    }
    .modal-preview-footer-img {
        width: 100%;
        height: 55px;
        object-fit: fill;
        display: block;
        margin-top: auto;
        flex-shrink: 0;
    }
    .modal-preview-body {
        padding: 15px 25px;
        flex-grow: 1;
        z-index: 2;
    }

    @media (max-width: 992px) {
        .leave-form-page { padding: 16px; }
        .leave-form-hero, .form-grid.two { grid-template-columns: 1fr; flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="leave-form-page">
    <!-- BREADCRUMB -->
    <div class="leave-breadcrumb">
        <i class="fas fa-envelope-open-text"></i>
        <a href="{{ route('dashboard') }}">Dashboard</a> / 
        <a href="{{ route('letterhead.index') }}">Letter Head Management</a> / 
        <span id="heroBreadcrumb">Send {{ $currentTemplate['name'] }}</span>
    </div>

    <!-- HERO HEADER -->
    <section class="leave-form-hero">
        <div>
            <h1 id="heroTitle">Send {{ $currentTemplate['name'] }}</h1>
            <p id="heroSubtitle">{{ $currentTemplate['description'] ?? 'Use the professional sample, edit your message, and download as PDF/Word or send to HR for review.' }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('letterhead.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to Letters
            </a>
        </div>
    </section>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm" role="alert" style="background: #dcfce7; color: #166534;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle fs-5"></i>
                <strong>{{ session('success') }}</strong>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-4 border-0 shadow-sm">
            <strong>Please resolve the following:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- MAIN TWO-COLUMN WORKSPACE -->
    <section class="form-card">
        <div class="form-grid two">

            <!-- ============================================================= -->
            <!-- LEFT COLUMN: PROFESSIONAL SAMPLE                              -->
            <!-- ============================================================= -->
            <div>
                <div class="sample-panel-header">
                    <h3>Professional Sample</h3>
                    <span class="badge rounded-pill" id="tplBadge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.76rem;">
                        {{ $currentTemplate['category_name'] ?? 'IT Industry Template' }}
                    </span>
                </div>

                <textarea id="sampleLetter" class="sample-textarea" rows="18" readonly>{{ $sampleText }}</textarea>

                <!-- 3 ACTION BUTTONS MATCHING PICTURE 1 -->
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-secondary" id="copySampleBtn">
                        <i class="fas fa-copy"></i> <span>Copy Sample</span>
                    </button>
                    
                    <button type="button" class="btn btn-light" id="useSampleBtn">
                        <i class="fas fa-pen"></i> <span>Use Sample</span>
                    </button>
                    
                    <!-- Download Demo Dropdown -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="downloadDemoBtn">
                            <i class="fas fa-download"></i> <span>Download Demo</span>
                        </button>
                        <ul class="dropdown-menu shadow-lg border-0 rounded-3 p-2">
                            <li>
                                <a class="dropdown-item rounded-2 py-2 fw-semibold" href="javascript:void(0)" onclick="triggerDemoDownload('pdf')">
                                    <i class="fas fa-file-pdf text-danger me-2"></i> Download as PDF
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-2 py-2 fw-semibold" href="javascript:void(0)" onclick="triggerDemoDownload('word')">
                                    <i class="fas fa-file-word text-primary me-2"></i> Download as Word (.docx)
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Template Category Quick Selector -->
                <div class="mt-4 p-3 rounded-4 bg-light border">
                    <label class="mb-2"><i class="fas fa-magic text-success me-1"></i> Quick Switch Template Category</label>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($categories as $catKey => $cat)
                            <button type="button" class="btn btn-sm btn-light py-1 px-2 text-dark border-0 rounded-pill {{ $catKey === ($currentTemplate['category_key'] ?? 'apology') ? 'bg-success text-white' : '' }}" 
                                    onclick="filterCategory('{{ $catKey }}')">
                                <i class="bx {{ $cat['icon'] }}"></i> {{ $cat['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- RIGHT COLUMN: LETTER GENERATOR FORM                           -->
            <!-- ============================================================= -->
            <div>
                <form id="letterMainForm" method="POST" action="{{ route('letterhead.send-letter') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ref_no" id="formRefNo" value="REF/{{ date('Y') }}/IT-{{ strtoupper(Str::random(4)) }}">
                    <input type="hidden" name="date" id="formDate" value="{{ now()->format('F d, Y') }}">

                    <!-- 1. LETTER TYPE DROPDOWN (ALL IT INDUSTRY LETTERS) -->
                    <div class="mb-3">
                        <label for="template_select">Letter Type / Category <span class="text-danger">*</span></label>
                        <select id="template_select" name="template_key" class="form-select" onchange="handleTemplateChange(this.value)">
                            @foreach($categories as $catKey => $cat)
                                <optgroup label="{{ $cat['name'] }}">
                                    @foreach($cat['templates'] as $tKey => $t)
                                        <option value="{{ $tKey }}" 
                                                data-subject="{{ $t['subject'] }}"
                                                data-category="{{ $cat['name'] }}"
                                                data-catkey="{{ $catKey }}"
                                                {{ $tKey === $templateKey ? 'selected' : '' }}>
                                            {{ $t['name'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. RELATED LEAVE (Shown when apology category is selected) -->
                    <div class="mb-3" id="relatedLeaveWrap">
                        <label>Related Leave</label>
                        <select name="leave_id" id="leave_id" class="form-select" onchange="handleLeaveSelect(this)">
                            <option value="">No specific leave</option>
                            @foreach($leaves as $item)
                                <option value="{{ $item->id }}" 
                                        data-type="{{ $item->type_label }}"
                                        data-start="{{ optional($item->start_date)->format('F d, Y') }}"
                                        data-end="{{ optional($item->end_date)->format('F d, Y') }}"
                                        {{ (string) old('leave_id', $selectedLeave?->id) === (string) $item->id ? 'selected' : '' }}>
                                    {{ $item->type_label }} - {{ optional($item->start_date)->format('d M Y') }} to {{ optional($item->end_date)->format('d M Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. HR / RECIPIENT EMAIL -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label id="lblRecipientName">Recipient / Employee Name</label>
                            <input type="text" name="recipient_name" id="recipient_name" class="form-control" 
                                   value="{{ old('recipient_name', 'Alexander Wright') }}" placeholder="e.g. John Doe / HR Department" oninput="syncPlaceholderData()">
                        </div>
                        <div class="col-md-6">
                            <label id="lblRecipientEmail">Recipient / HR Email</label>
                            <input type="email" name="recipient_email" id="recipient_email" class="form-control" 
                                   value="{{ old('recipient_email', 'hr@bengalithub.com') }}" placeholder="hr@example.com">
                        </div>
                    </div>

                    <!-- 4. SUBJECT LINE -->
                    <div class="mb-3">
                        <label>Subject <span>*</span></label>
                        <input type="text" name="subject" id="subject" class="form-control" required 
                               value="{{ old('subject', $currentTemplate['subject']) }}">
                    </div>

                    <!-- 5. LETTER CONTENT TEXTAREA -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="mb-0" id="lblLetterContent">Letter Content (Body) <span>*</span></label>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none text-success p-0 fw-bold" onclick="revertToSample()">
                                <i class="fas fa-undo-alt"></i> Reset to Sample
                            </button>
                        </div>
                        <textarea name="body" id="body" class="form-control" rows="11" required placeholder="Type or paste your customized letter content here...">{{ old('body', $sampleText) }}</textarea>
                    </div>

                    <!-- 6. HEADER & FOOTER IMAGE / PDF UPLOAD SECTION -->
                    <div class="lh-custom-box">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-image text-success fs-5"></i>
                                <span class="fw-bold text-dark">Header & Footer Image / PDF Placement</span>
                            </div>
                            <span class="badge bg-success text-white rounded-pill px-2">Image Placement</span>
                        </div>
                        <p class="text-muted small mb-3">
                            Upload separate <strong>Header & Footer Images</strong> or PDFs for official letterhead placement.
                        </p>

                        <input type="hidden" name="layout_mode" id="layout_mode" value="custom_header_footer">

                        <!-- Header & Footer Upload Slots -->
                        <div class="row g-2" id="headerFooterSlots">
                            <!-- Header Image Slot -->
                            <div class="col-md-6">
                                <div class="upload-slot-card">
                                    <label class="d-flex justify-content-between align-items-center text-success mb-1">
                                        <span><i class="fas fa-arrow-up me-1"></i> Header Image / PDF</span>
                                        <small class="badge bg-success text-white rounded-pill px-2" style="font-size: 0.68rem;">Fixed: 1240 × 160 px</small>
                                    </label>
                                    <input type="file" name="header_image" id="header_image" class="form-control form-control-sm" accept="image/*,.pdf" onchange="previewHeaderImage(this)">
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                        Default: Bengal IT Hub Header Ribbon • Uploaded photo auto-fits fixed 1240×160 header
                                    </small>
                                </div>
                            </div>

                            <!-- Footer Image Slot -->
                            <div class="col-md-6">
                                <div class="upload-slot-card">
                                    <label class="d-flex justify-content-between align-items-center text-success mb-1">
                                        <span><i class="fas fa-arrow-down me-1"></i> Footer Image / PDF</span>
                                        <small class="badge bg-success text-white rounded-pill px-2" style="font-size: 0.68rem;">Fixed: 1240 × 140 px</small>
                                    </label>
                                    <input type="file" name="footer_image" id="footer_image" class="form-control form-control-sm" accept="image/*,.pdf" onchange="previewFooterImage(this)">
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                        Default: Bengal IT Hub Footer Address & Ribbons • Uploaded photo auto-fits fixed 1240×140 footer
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Signatory Configuration -->
                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <label>Signatory Name</label>
                                <input type="text" name="signatory_name" id="signatory_name" class="form-control form-control-sm" value="{{ auth()->user()?->name ?: 'Arthur Pendelton' }}" placeholder="Authorized Signatory">
                            </div>
                            <div class="col-md-6">
                                <label>Signatory Title</label>
                                <input type="text" name="signatory_title" id="signatory_title" class="form-control form-control-sm" value="Authorized Signatory / HR Director" placeholder="Title">
                            </div>
                        </div>
                    </div>

                    <!-- 7. BOTTOM ACTION BUTTONS -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2">
                        <div class="d-flex gap-2">
                            <a href="{{ route('letterhead.index') }}" class="btn btn-light">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="button" class="btn btn-secondary" onclick="openPrintPreview()">
                                <i class="fas fa-eye"></i> Live Preview
                            </button>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <!-- Download PDF -->
                            <button type="button" class="btn btn-primary" onclick="submitExport('pdf')">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </button>

                            <!-- Download Word -->
                            <button type="button" class="btn btn-word" onclick="submitExport('word')">
                                <i class="fas fa-file-word"></i> Download Word (.docx)
                            </button>

                            <!-- Send to HR -->
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send to HR
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </section>
</div>

<!-- ========================================================================= -->
<!-- LIVE PREVIEW MODAL WITH HEADER & FOOTER IMAGE PLACEMENT                   -->
<!-- ========================================================================= -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom px-4 pt-3 pb-3 bg-white">
                <h5 class="modal-title fw-bold text-dark mb-0">
                    <i class="fas fa-file-invoice text-success me-2"></i> Document Print Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light d-flex justify-content-center">
                <div class="lh-preview-card-a4 bg-white" id="modalA4Preview">
                    
                    <!-- 1. PLACED HEADER IMAGE (Used in custom_header_footer mode) -->
                    <img id="prevHeaderImgElement" src="{{ asset('assets/letterhead/presets/bengal_header.svg') }}" class="modal-preview-header-img" alt="Header Image">

                    <!-- 2. CENTER CONTENT SECTION -->
                    <div class="modal-preview-body" id="modalPreviewBody">
                        <div style="display: flex; justify-content: space-between; font-size: 7.5pt; font-weight: bold; color: #475569; margin-bottom: 10px;">
                            <span id="prevRefNo">REF/2026/IT-0842</span>
                            <span id="prevDate">{{ now()->format('F d, Y') }}</span>
                        </div>

                        <div style="font-size: 8.5pt; font-weight: bold; color: #0f744c; text-decoration: underline; margin-bottom: 10px;" id="prevSubject">
                            Subject: Apology Letter Regarding Leave
                        </div>

                        <div style="font-size: 8pt; line-height: 1.55; color: #334155; white-space: pre-wrap;" id="prevBody">
                        </div>

                        <!-- Signatory Block -->
                        <div style="margin-top: 18px; font-size: 7.5pt;">
                            <div style="font-weight: bold; color: #1e293b;" id="prevSignatory">Arthur Pendelton</div>
                            <div style="color: #64748b;" id="prevSignatoryTitle">Authorized Signatory</div>
                        </div>
                    </div>

                    <!-- 3. PLACED FOOTER IMAGE (Used in custom_header_footer mode) -->
                    <img id="prevFooterImgElement" src="{{ asset('assets/letterhead/presets/bengal_footer.svg') }}" class="modal-preview-footer-img" alt="Footer Image">

                </div>
            </div>
            <div class="modal-footer border-top px-4 py-2 bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" onclick="submitExport('pdf')">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </button>
                    <button type="button" class="btn btn-word btn-sm" onclick="submitExport('word')">
                        <i class="fas fa-file-word"></i> Download Word
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const templateData = @json($allTemplates);
    const sampleArea = document.getElementById('sampleLetter');
    const bodyArea = document.getElementById('body');
    const subjectInput = document.getElementById('subject');
    const templateSelect = document.getElementById('template_select');

    let currentHeaderImgData = '{{ asset('assets/letterhead/presets/bengal_header.svg') }}';
    let currentFooterImgData = '{{ asset('assets/letterhead/presets/bengal_footer.svg') }}';

    // 1. COPY SAMPLE BUTTON
    document.getElementById('copySampleBtn')?.addEventListener('click', async function () {
        try {
            await navigator.clipboard.writeText(sampleArea.value);
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check text-success"></i> <span>Copied!</span>';
            setTimeout(() => this.innerHTML = originalHTML, 1600);
        } catch (err) {
            sampleArea.select();
            document.execCommand('copy');
            alert('Sample text copied to clipboard.');
        }
    });

    // 2. USE SAMPLE BUTTON
    document.getElementById('useSampleBtn')?.addEventListener('click', function () {
        bodyArea.value = sampleArea.value;
        bodyArea.focus();
        bodyArea.style.transition = 'background-color 0.3s ease';
        bodyArea.style.backgroundColor = '#ecfdf5';
        setTimeout(() => bodyArea.style.backgroundColor = '', 600);
    });

    // Template change handler
    window.handleTemplateChange = function (tplKey) {
        const tpl = templateData[tplKey];
        if (!tpl) return;

        document.getElementById('heroTitle').textContent = 'Send ' + tpl.name;
        document.getElementById('heroSubtitle').textContent = tpl.description || 'Use the professional sample, edit your message, and download as PDF/Word or send to HR for review.';
        document.getElementById('heroBreadcrumb').textContent = 'Send ' + tpl.name;
        document.getElementById('tplBadge').textContent = tpl.category_name || 'IT Industry Template';

        subjectInput.value = tpl.subject || tpl.name;
        const rendered = renderPlaceholders(tpl.content);
        sampleArea.value = rendered;
        bodyArea.value = rendered;

        const isApology = tpl.category_key === 'apology' || tplKey.startsWith('apology_');
        document.getElementById('relatedLeaveWrap').classList.toggle('d-none', !isApology);
        document.getElementById('lblRecipientName').textContent = isApology ? 'Reporting Manager / HR' : 'Recipient / Candidate / Employee Name';
        document.getElementById('lblRecipientEmail').textContent = isApology ? 'HR Email' : 'Recipient / Official Email';
    };

    window.filterCategory = function (catKey) {
        for (let i = 0; i < templateSelect.options.length; i++) {
            const opt = templateSelect.options[i];
            if (opt.dataset.catkey === catKey) {
                templateSelect.selectedIndex = i;
                handleTemplateChange(opt.value);
                break;
            }
        }
    };

    window.handleLeaveSelect = function (select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const leaveType = opt.dataset.type || 'Leave';
            const startDate = opt.dataset.start || '[Start Date]';
            const endDate = opt.dataset.end || '[End Date]';

            subjectInput.value = 'Apology Letter Regarding ' + leaveType + ' Leave';
            let content = bodyArea.value;
            content = content.replace(/\[Leave Type\]/g, leaveType);
            content = content.replace(/\[Start Date\]/g, startDate);
            content = content.replace(/\[End Date\]/g, endDate);
            bodyArea.value = content;
        }
    };

    window.revertToSample = function () {
        bodyArea.value = sampleArea.value;
    };

    function renderPlaceholders(text) {
        const recipient = document.getElementById('recipient_name')?.value || 'Alexander Wright';
        const now = new Date();
        const dateStr = now.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

        return text
            .replace(/\[Date\]/g, dateStr)
            .replace(/\[Year\]/g, now.getFullYear())
            .replace(/\[Fiscal Year\]/g, now.getFullYear() + '-' + (now.getFullYear() + 1))
            .replace(/\[Company Name\]/g, 'Bengal IT Hub Private Limited')
            .replace(/\[Employee Name\]/g, recipient)
            .replace(/\[Candidate Name\]/g, recipient)
            .replace(/\[Intern Name\]/g, recipient)
            .replace(/\[Designation\]/g, 'Senior Software Engineer')
            .replace(/\[Employee ID\]/g, 'EMP-2026-0842')
            .replace(/\[Start Date\]/g, 'August 25, 2026')
            .replace(/\[End Date\]/g, 'August 28, 2026')
            .replace(/\[HR Email\]/g, 'hr@bengalithub.com')
            .replace(/\[HR Phone\]/g, '+91 92306 53975');
    }

    window.syncPlaceholderData = function () {};

    function applyModalLayoutPreview() {
        const modalA4 = document.getElementById('modalA4Preview');
        const headerImgEl = document.getElementById('prevHeaderImgElement');
        const footerImgEl = document.getElementById('prevFooterImgElement');
        const bodyEl = document.getElementById('modalPreviewBody');

        if (modalA4 && headerImgEl && footerImgEl && bodyEl) {
            modalA4.style.backgroundImage = 'none';
            headerImgEl.style.display = 'block';
            footerImgEl.style.display = 'block';
            headerImgEl.src = currentHeaderImgData;
            footerImgEl.src = currentFooterImgData;
            bodyEl.style.paddingTop = '15px';
            bodyEl.style.paddingBottom = '15px';
            bodyEl.style.paddingLeft = '25px';
            bodyEl.style.paddingRight = '25px';
        }
    }

    // Live Header Preview Handler
    window.previewHeaderImage = function (input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                currentHeaderImgData = e.target.result;
                document.getElementById('prevHeaderImgElement').src = currentHeaderImgData;
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    // Live Footer Preview Handler
    window.previewFooterImage = function (input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                currentFooterImgData = e.target.result;
                document.getElementById('prevFooterImgElement').src = currentFooterImgData;
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    // Export Submit Handler (PDF or Word)
    window.submitExport = function (format) {
        const form = document.getElementById('letterMainForm');
        const originalAction = form.action;
        const originalTarget = form.target;

        if (format === 'pdf') {
            form.action = '{{ route('letterhead.export-pdf') }}';
            form.target = '_blank';
            form.submit();
        } else if (format === 'word') {
            form.action = '{{ route('letterhead.export-word') }}';
            form.target = '_blank';
            form.submit();
        }

        setTimeout(() => {
            form.action = originalAction;
            form.target = originalTarget;
        }, 500);
    };

    // Live Print Preview Modal
    window.openPrintPreview = function () {
        document.getElementById('prevSubject').textContent = 'Subject: ' + document.getElementById('subject').value;
        document.getElementById('prevBody').textContent = document.getElementById('body').value;
        document.getElementById('prevSignatory').textContent = document.getElementById('signatory_name').value;
        document.getElementById('prevSignatoryTitle').textContent = document.getElementById('signatory_title').value;
        document.getElementById('prevRefNo').textContent = document.getElementById('formRefNo').value;
        document.getElementById('prevDate').textContent = document.getElementById('formDate').value;

        applyModalLayoutPreview();

        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    };

    // Trigger Demo Download
    window.triggerDemoDownload = function (format) {
        const tplKey = templateSelect.value;
        window.location.href = `/letterhead/demo-download?template_key=${tplKey}&format=${format}`;
    };
});
</script>
@endsection
