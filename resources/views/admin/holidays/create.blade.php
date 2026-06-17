@extends('admin.layout.app')
@section('title', 'Add Holiday')

@section('content')

<div class="holiday-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-calendar-plus"></i> Dashboard / Holidays / Add
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-calendar-plus"></i>
            </div>
            <div>
                <h1>Add Organization Holiday</h1>
                <p>Create one holiday, add more rows, or override an existing date when the yearly calendar changes</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('holidays.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Validation Errors!</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Excel Bulk Upload Card -->
    <div class="bulk-upload-card">
        <div class="bulk-upload-left">
            <div class="bulk-icon">
                <i class="fas fa-file-excel"></i>
            </div>
            <div>
                <h5>Bulk Add Holidays with Excel</h5>
                <p>Download the sample file, fill holiday rows, then upload it here to add or override the yearly holiday list in bulk.</p>
                <div class="bulk-columns">
                    <span>date</span>
                    <span>occassion</span>
                    <span>type</span>
                    <span>department_ids</span>
                    <span>designation_ids</span>
                    <span>employment_types</span>
                    <span>override_existing</span>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('holidays.import.excel') }}" enctype="multipart/form-data" class="bulk-upload-form">
            @csrf
            <a href="{{ route('holidays.sample') }}" class="btn btn-light">
                <i class="fas fa-download"></i> Sample Excel
            </a>
            <label class="bulk-file">
                <i class="fas fa-cloud-upload-alt"></i>
                <span id="bulkFileLabel">Choose Excel / CSV file</span>
                <input type="file" name="holiday_file" id="holidayBulkFile" accept=".xlsx,.xls,.csv" required>
            </label>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-file-import"></i> Import Holidays
            </button>
        </form>
    </div>

    <!-- Form Card -->
    <div class="form-card">
        <form method="POST" action="{{ route('holidays.store') }}" id="save-holiday-data-form" class="form-content">
            @csrf

            <div class="form-header">
                <div>
                    <h5 class="mb-0">Holiday Details</h5>
                    <p>The same department, designation, and employment filters apply to all rows below.</p>
                </div>
                <label class="override-check">
                    <input type="checkbox" name="override_holidays" value="1">
                    <span>Override existing date</span>
                </label>
            </div>

            <!-- Holiday Items -->
            <div id="holidayItems" class="holiday-items">
                <div class="holiday-item">
                    <div class="holiday-field">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date[]" class="form-control" required>
                    </div>
                    <div class="holiday-field">
                        <label class="form-label">Occasion <span class="text-danger">*</span></label>
                        <input type="text" name="occassion[]" class="form-control" placeholder="e.g., Durga Puja Holiday" required>
                    </div>
                    <button type="button" class="btn btn-outline-danger remove-item d-none" aria-label="Remove holiday">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <button type="button" id="add-holiday-item" class="btn btn-outline-primary add-btn">
                <i class="fas fa-plus-circle"></i> Add More Holiday
            </button>

            <!-- Filters -->
            <div class="filters-section">
                <div class="row g-3 mt-2">
                    <div class="col-lg-6">
                        <label class="form-label">Department</label>
                        <select class="form-control multiple-users" multiple name="department_id_json[]" id="selectdepartment" data-live-search="true" data-size="8">
                            @foreach ($department as $team)
                                <option value="{{ $team->id }}">{{ $team->dpt_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">Designation</label>
                        <select class="form-control multiple-users" multiple name="designation_id_json[]" id="selectdesignation" data-live-search="true" data-size="8">
                            @foreach ($designations as $designation)
                                <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">Employment Type</label>
                        <select class="form-control select2" name="employment_type_json[]" multiple>
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="on_contract">On Contract</option>
                            <option value="internship">Internship</option>
                            <option value="trainee">Trainee</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('holidays.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check-circle"></i> Save Holiday
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* ===== PREMIUM HOLIDAY FORM PAGE STYLES ===== */
    .holiday-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f0f9f4 0%, #e6f3ec 50%, #f4fbf7 100%);
        color: #0a2e1f;
        position: relative;
    }

    .holiday-page::before {
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
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 14px 24px;
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        margin-bottom: 28px;
        color: #0f744c;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
        animation: slideDown 0.4s ease;
    }

    .breadcrumb i {
        margin-right: 8px;
        color: #34d399;
    }

    /* Header Card */
    .header-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        border-radius: 28px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        box-shadow: 0 20px 40px -12px rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.12);
        margin-bottom: 32px;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        animation: slideDown 0.5s ease;
    }

    .header-card:hover {
        box-shadow: 0 24px 48px -16px rgba(16, 185, 129, 0.18);
        border-color: rgba(16, 185, 129, 0.2);
        transform: translateY(-2px);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 22px;
    }

    .header-icon {
        width: 72px;
        height: 72px;
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 12px 24px -8px rgba(5, 150, 105, 0.3);
        transition: all 0.3s ease;
    }

    .header-card:hover .header-icon {
        transform: scale(1.02);
    }

    .header-card h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 6px;
        background: linear-gradient(135deg, #0a2e1f, #0f744c);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header-card p {
        color: #5a6e63;
        font-size: 15px;
        font-weight: 500;
    }

    /* Buttons */
    .btn-group {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        padding: 12px 24px;
        border-radius: 16px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        font-size: 0.9rem;
        min-height: 48px;
    }

    .btn i {
        font-size: 1rem;
    }

    .btn-light {
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .btn-light:hover {
        background: #e6f3ec;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(16, 185, 129, 0.25);
        border-color: #34d399;
    }

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        box-shadow: 0 8px 20px -6px rgba(5, 150, 105, 0.35);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(5, 150, 105, 0.45);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .btn-outline-primary {
        background: transparent;
        color: #059669;
        border: 1.5px solid #34d399;
    }

    .btn-outline-primary:hover {
        background: #ecfdf5;
        border-color: #059669;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15);
    }

    .btn-outline-danger {
        background: transparent;
        color: #dc2626;
        border: 1.5px solid #fca5a5;
    }

    .btn-outline-danger:hover {
        background: #fef2f2;
        border-color: #dc2626;
        transform: translateY(-2px);
    }

    /* Alerts */
    .alert {
        border-radius: 18px;
        border: none;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        animation: slideDown 0.4s ease;
        position: relative;
        z-index: 1;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .alert i {
        font-size: 1.25rem;
        margin-top: 2px;
    }

    .alert ul {
        margin-bottom: 0;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Excel Bulk Upload */
    .bulk-upload-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        border-radius: 28px;
        padding: 26px 28px;
        display: grid;
        grid-template-columns: 1fr minmax(320px, 430px);
        gap: 24px;
        align-items: center;
        box-shadow: 0 14px 34px -14px rgba(16, 185, 129, 0.16);
        border: 1px solid rgba(16, 185, 129, 0.12);
        margin-bottom: 32px;
        position: relative;
        z-index: 1;
        animation: slideUp 0.55s ease;
    }

    .bulk-upload-left {
        display: flex;
        align-items: flex-start;
        gap: 18px;
    }

    .bulk-icon {
        width: 62px;
        height: 62px;
        border-radius: 22px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #047857;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex: 0 0 auto;
        box-shadow: 0 12px 24px -12px rgba(5, 150, 105, 0.35);
    }

    .bulk-upload-card h5 {
        margin: 0 0 6px;
        color: #0a2e1f;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .bulk-upload-card p {
        margin: 0;
        color: #5a6e63;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .bulk-columns {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .bulk-columns span {
        padding: 6px 10px;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        font-size: 0.76rem;
        font-weight: 800;
    }

    .bulk-upload-form {
        display: grid;
        gap: 12px;
    }

    .bulk-file {
        min-height: 52px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 16px;
        border: 1.5px dashed #34d399;
        background: #f0fdf4;
        color: #0f744c;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .bulk-file:hover {
        background: #ecfdf5;
        border-color: #059669;
        transform: translateY(-2px);
    }

    .bulk-file input {
        display: none;
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        animation: slideUp 0.6s ease;
    }

    .form-card:hover {
        box-shadow: 0 12px 40px rgba(16, 185, 129, 0.08);
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Form Content */
    .form-content {
        padding: 28px;
    }

    /* Form Header */
    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        flex-wrap: wrap;
    }

    .form-header h5 {
        font-weight: 700;
        color: #0a2e1f;
        font-size: 1.1rem;
    }

    .form-header p {
        margin: 4px 0 0;
        color: #8ba198;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .override-check {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 18px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        font-size: 0.85rem;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .override-check:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .override-check input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #059669;
        cursor: pointer;
    }

    /* Holiday Items */
    .holiday-items {
        display: grid;
        gap: 12px;
        margin-bottom: 16px;
    }

    .holiday-item {
        display: grid;
        grid-template-columns: minmax(180px, 260px) 1fr auto;
        gap: 12px;
        align-items: end;
        padding: 16px 18px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid rgba(16, 185, 129, 0.08);
        transition: all 0.3s ease;
        animation: itemFade 0.3s ease;
    }

    .holiday-item:hover {
        background: #f0f9f4;
        border-color: rgba(16, 185, 129, 0.15);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.05);
    }

    @keyframes itemFade {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }

    .holiday-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .holiday-field .form-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6b7280;
        margin-bottom: 0;
    }

    .holiday-field .form-control {
        min-height: 44px;
        padding: 8px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #0a2e1f;
        background: #ffffff;
        transition: all 0.2s ease;
        width: 100%;
    }

    .holiday-field .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
        outline: none;
    }

    .holiday-field .form-control::placeholder {
        color: #9ca3af;
        font-weight: 400;
    }

    .holiday-item .btn-outline-danger {
        min-height: 44px;
        width: 44px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        flex-shrink: 0;
        margin-bottom: 0;
        align-self: center;
    }

    .holiday-item .btn-outline-danger i {
        font-size: 1.1rem;
    }

    /* Add Button */
    .add-btn {
        margin-bottom: 8px;
        padding: 10px 20px;
        font-size: 0.85rem;
        border-radius: 12px;
        border: 1.5px dashed #34d399;
        transition: all 0.3s ease;
    }

    .add-btn:hover {
        background: #ecfdf5;
        border-color: #059669;
        transform: translateY(-2px);
    }

    .add-btn i {
        font-size: 0.9rem;
    }

    /* Filters Section */
    .filters-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid rgba(16, 185, 129, 0.08);
    }

    .filters-section .form-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .filters-section .form-control {
        min-height: 44px;
        padding: 8px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #0a2e1f;
        background: #ffffff;
        transition: all 0.2s ease;
        width: 100%;
    }

    .filters-section .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
        outline: none;
    }

    /* Select2 Custom */
    .select2-container--default .select2-selection--multiple {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        min-height: 44px !important;
        padding: 4px 8px;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #34d399 !important;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08) !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: #ecfdf5 !important;
        border: 1px solid #a7f3d0 !important;
        border-radius: 8px !important;
        color: #059669 !important;
        font-weight: 600 !important;
        font-size: 0.8rem !important;
        padding: 2px 10px !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #059669 !important;
        font-weight: 700 !important;
        margin-right: 6px !important;
    }

    /* Bootstrap Select Custom */
    .bootstrap-select .dropdown-toggle {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        min-height: 44px !important;
        padding: 8px 14px !important;
        font-size: 0.9rem !important;
        font-weight: 500 !important;
        color: #0a2e1f !important;
        background: #ffffff !important;
    }

    .bootstrap-select .dropdown-toggle:focus {
        border-color: #34d399 !important;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08) !important;
    }

    .bootstrap-select .dropdown-menu {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06) !important;
        padding: 6px !important;
    }

    .bootstrap-select .dropdown-menu .dropdown-item {
        border-radius: 8px !important;
        padding: 8px 14px !important;
        font-weight: 500 !important;
    }

    .bootstrap-select .dropdown-menu .dropdown-item:hover {
        background: #ecfdf5 !important;
        color: #059669 !important;
    }

    .bootstrap-select .dropdown-menu .dropdown-item.active {
        background: linear-gradient(145deg, #34d399, #059669) !important;
        color: white !important;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 14px;
        padding-top: 24px;
        margin-top: 24px;
        border-top: 1px solid rgba(16, 185, 129, 0.08);
    }

    .form-actions .btn {
        min-height: 48px;
        padding: 12px 28px;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .holiday-page {
            padding: 20px 25px;
        }

        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-group {
            width: 100%;
            justify-content: flex-start;
        }

        .bulk-upload-card {
            grid-template-columns: 1fr;
        }

        .form-header {
            flex-direction: column;
            align-items: stretch;
        }

        .override-check {
            align-self: flex-start;
        }
    }

    @media (max-width: 768px) {
        .holiday-page {
            padding: 16px;
        }

        .header-card {
            padding: 20px;
        }

        .header-icon {
            width: 56px;
            height: 56px;
            font-size: 24px;
        }

        .header-card h1 {
            font-size: 24px;
        }

        .form-content {
            padding: 16px;
        }

        .bulk-upload-card {
            padding: 18px;
            border-radius: 20px;
        }

        .bulk-upload-left {
            flex-direction: column;
        }

        .holiday-item {
            grid-template-columns: 1fr;
            gap: 10px;
            padding: 14px;
        }

        .holiday-item .btn-outline-danger {
            width: 100%;
            align-self: stretch;
            min-height: 40px;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .form-header {
            gap: 12px;
        }
    }

    @media (max-width: 576px) {
        .holiday-page {
            padding: 12px;
        }

        .header-card {
            padding: 16px;
            border-radius: 20px;
        }

        .header-left {
            gap: 14px;
        }

        .header-icon {
            width: 48px;
            height: 48px;
            font-size: 20px;
            border-radius: 18px;
        }

        .header-card h1 {
            font-size: 20px;
        }

        .header-card p {
            font-size: 13px;
        }

        .form-card {
            border-radius: 20px;
        }

        .holiday-field .form-control {
            font-size: 0.85rem;
            min-height: 40px;
        }

        .btn {
            font-size: 0.85rem;
            padding: 10px 16px;
            min-height: 40px;
        }
    }
</style>

<style>
    /* Larger typography for better readability */
    .holiday-page {
        font-size: 17px;
        line-height: 1.65;
    }

    .holiday-page .breadcrumb {
        font-size: 1.08rem;
    }

    .holiday-page .header-card h1 {
        font-size: 40px;
        line-height: 1.2;
    }

    .holiday-page .header-card p {
        font-size: 18px;
        line-height: 1.65;
    }

    .holiday-page .btn {
        font-size: 1.05rem;
        padding: 14px 26px;
    }

    .holiday-page .form-header h5 {
        font-size: 1.35rem;
    }

    .holiday-page .form-header p {
        font-size: 1.02rem;
    }

    .holiday-page .holiday-field .form-label {
        font-size: 0.92rem;
    }

    .holiday-page .holiday-field .form-control {
        font-size: 1.06rem;
        min-height: 50px;
    }

    .holiday-page .filters-section .form-label {
        font-size: 0.92rem;
    }

    .holiday-page .filters-section .form-control {
        font-size: 1.06rem;
        min-height: 50px;
    }

    .holiday-page .bulk-upload-card h5 {
        font-size: 1.35rem;
    }

    .holiday-page .bulk-upload-card p {
        font-size: 1.05rem;
        line-height: 1.6;
    }

    .holiday-page .bulk-columns span,
    .holiday-page .override-check,
    .holiday-page .bulk-file {
        font-size: 0.98rem;
    }

    .holiday-page .select2-container--default .select2-selection--multiple,
    .holiday-page .bootstrap-select .dropdown-toggle {
        font-size: 1.04rem !important;
        min-height: 50px !important;
    }

    @media (max-width: 768px) {
        .holiday-page {
            font-size: 16px;
        }

        .holiday-page .header-card h1 {
            font-size: 30px;
        }

        .holiday-page .header-card p {
            font-size: 16px;
        }
    }
</style>

<style>
    /* Dark mode support */
    html[data-pms-theme="dark"] .holiday-page {
        background: linear-gradient(135deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .breadcrumb {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .breadcrumb i {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .header-card {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .header-card h1 {
        background: linear-gradient(135deg, #d9f1e4, #34d399);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    html[data-pms-theme="dark"] .header-card p {
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

    html[data-pms-theme="dark"] .form-card {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .bulk-upload-card {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .bulk-upload-card h5 {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .bulk-upload-card p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .bulk-columns span {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.2);
        color: #34d399;
    }

    html[data-pms-theme="dark"] .bulk-file {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.25);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .bulk-file:hover {
        background: #102119;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .form-header {
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .form-header h5 {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .form-header p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .holiday-item {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .holiday-item:hover {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .holiday-field .form-control {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .holiday-field .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
    }

    html[data-pms-theme="dark"] .holiday-field .form-control::placeholder {
        color: #6b7280;
    }

    html[data-pms-theme="dark"] .holiday-field .form-label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .override-check {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.12);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .override-check:hover {
        background: #102119;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .filters-section {
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .filters-section .form-label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .filters-section .form-control {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .filters-section .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
    }

    html[data-pms-theme="dark"] .form-actions {
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .btn-secondary {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-secondary:hover {
        background: #1f3d30;
    }

    html[data-pms-theme="dark"] .btn-outline-primary {
        color: #34d399;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .btn-outline-primary:hover {
        background: #183026;
        color: #6ee7b7;
        border-color: #6ee7b7;
    }

    html[data-pms-theme="dark"] .btn-outline-danger {
        color: #fca5a5;
        border-color: rgba(239, 68, 68, 0.3);
    }

    html[data-pms-theme="dark"] .btn-outline-danger:hover {
        background: #1a0d0d;
        border-color: #ef4444;
        color: #fca5a5;
    }

    html[data-pms-theme="dark"] .select2-container--default .select2-selection--multiple {
        background: #102119 !important;
        border-color: rgba(122, 240, 181, 0.15) !important;
    }

    html[data-pms-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: #183026 !important;
        border-color: #34d399 !important;
        color: #34d399 !important;
    }

    html[data-pms-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #34d399 !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-toggle {
        background: #102119 !important;
        border-color: rgba(122, 240, 181, 0.15) !important;
        color: #d9f1e4 !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu {
        background: #102119 !important;
        border-color: rgba(122, 240, 181, 0.15) !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu .dropdown-item {
        color: #d9f1e4 !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu .dropdown-item:hover {
        background: #183026 !important;
        color: #34d399 !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu .dropdown-item.active {
        background: linear-gradient(145deg, #34d399, #059669) !important;
        color: white !important;
    }

    html[data-pms-theme="dark"] .alert-danger {
        background: #1a0d0d;
        color: #fca5a5;
        border-left-color: #ef4444;
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addBtn = document.getElementById('add-holiday-item');
    const holidayItems = document.getElementById('holidayItems');
    const holidayBulkFile = document.getElementById('holidayBulkFile');
    const bulkFileLabel = document.getElementById('bulkFileLabel');

    if (holidayBulkFile && bulkFileLabel) {
        holidayBulkFile.addEventListener('change', function () {
            bulkFileLabel.textContent = this.files && this.files.length ? this.files[0].name : 'Choose Excel / CSV file';
        });
    }

    addBtn.addEventListener('click', function () {
        const item = document.createElement('div');
        item.className = 'holiday-item';
        item.style.animation = 'itemFade 0.3s ease';
        item.innerHTML = `
            <div class="holiday-field">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date[]" class="form-control" required>
            </div>
            <div class="holiday-field">
                <label class="form-label">Occasion <span class="text-danger">*</span></label>
                <input type="text" name="occassion[]" class="form-control" placeholder="Occasion" required>
            </div>
            <button type="button" class="btn btn-outline-danger remove-item" aria-label="Remove holiday">
                <i class="fas fa-times"></i>
            </button>
        `;
        holidayItems.appendChild(item);
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('.remove-item')) {
            const item = event.target.closest('.holiday-item');
            item.style.animation = 'itemFadeOut 0.3s ease';
            setTimeout(() => {
                item.remove();
            }, 300);
        }
    });

    // Add fade out animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes itemFadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.96); }
        }
    `;
    document.head.appendChild(style);

    // Initialize Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select employment types',
            allowClear: true
        });
    }

    // Initialize Bootstrap Select
    if (typeof $ !== 'undefined' && $.fn.selectpicker) {
        $('#selectdesignation, #selectdepartment').selectpicker({
            actionsBox: true,
            selectAllText: 'Select All',
            deselectAllText: 'Deselect All',
            selectedTextFormat: 'count > 4',
            liveSearch: true,
            size: 8
        });
    }
});
</script>
@endpush

@endsection
