@extends('admin.layout.app')
@section('title', 'Edit Holiday')

@section('content')

<div class="holiday-edit-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-edit"></i> Dashboard / Holidays / Edit
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-edit"></i>
            </div>
            <div>
                <h1>Edit Holiday</h1>
                <p>Update holiday details, filters, and occassion information</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('holidays.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
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

    <!-- Form Card -->
    <div class="form-card">
        <form method="POST" action="{{ route('holidays.update', $holiday->id) }}" class="form-content">
            @csrf
            @method('PUT')

            <!-- Holiday Items -->
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-list"></i>
                    <h6>Holiday Dates & Occasions</h6>
                    <span class="section-badge">{{ $holiday->group->holidays->count() }} holiday(s)</span>
                </div>

                <div id="holidayItems">
                    @foreach ($holiday->group->holidays as $h)
                        <div class="holiday-item">
                            <input type="hidden" name="holiday_id[]" value="{{ $h->id }}">

                            <div class="holiday-field">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date[]" class="form-control"
                                       value="{{ old('date.' . $loop->index, $h->date) }}" required>
                            </div>

                            <div class="holiday-field">
                                <label class="form-label">Occasion <span class="text-danger">*</span></label>
                                <input type="text" name="occassion[]" class="form-control"
                                       value="{{ old('occassion.' . $loop->index, $h->title) }}"
                                       placeholder="e.g., Republic Day" required>
                            </div>

                            <button type="button" class="btn btn-outline-danger remove-item {{ $loop->first ? 'd-none' : '' }}"
                                    aria-label="Remove holiday">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-holiday-item" class="btn btn-outline-primary add-btn">
                    <i class="fas fa-plus-circle"></i> Add More Holiday
                </button>
            </div>

            <!-- Filters Section -->
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-filter"></i>
                    <h6>Department, Designation & Employment Filters</h6>
                    <span class="section-badge">Filters apply to all holidays above</span>
                </div>

                <div class="filters-grid">
                    <div class="filter-group">
                        <label class="form-label">Department</label>
                        <select class="form-control multiple-users" multiple name="department_id_json[]"
                                id="selectdepartment" data-live-search="true" data-size="8">
                            @foreach ($department as $team)
                                <option value="{{ $team->id }}"
                                    {{ in_array($team->id, old('department_id_json', json_decode($holiday->department_id_json, true) ?? [])) ? 'selected' : '' }}>
                                    {{ $team->dpt_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="form-label">Designation</label>
                        <select class="form-control multiple-users" multiple name="designation_id_json[]"
                                id="selectdesignation" data-live-search="true" data-size="8">
                            @foreach ($designations as $designation)
                                <option value="{{ $designation->id }}"
                                    {{ in_array($designation->id, old('designation_id_json', json_decode($holiday->designation_id_json, true) ?? [])) ? 'selected' : '' }}>
                                    {{ $designation->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="form-label">Employment Type</label>
                        <select class="form-control select2" name="employment_type_json[]" multiple>
                            @php
                                $selectedEmployment = old('employment_type_json', json_decode($holiday->employment_type_json, true) ?? []);
                            @endphp
                            <option value="full_time" {{ in_array('full_time', $selectedEmployment) ? 'selected' : '' }}>Full Time</option>
                            <option value="part_time" {{ in_array('part_time', $selectedEmployment) ? 'selected' : '' }}>Part Time</option>
                            <option value="on_contract" {{ in_array('on_contract', $selectedEmployment) ? 'selected' : '' }}>On Contract</option>
                            <option value="internship" {{ in_array('internship', $selectedEmployment) ? 'selected' : '' }}>Internship</option>
                            <option value="trainee" {{ in_array('trainee', $selectedEmployment) ? 'selected' : '' }}>Trainee</option>
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
                    <i class="fas fa-save"></i> Update Holiday
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* ===== PREMIUM HOLIDAY EDIT PAGE STYLES ===== */
    .holiday-edit-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f0f9f4 0%, #e6f3ec 50%, #f4fbf7 100%);
        color: #0a2e1f;
        position: relative;
    }

    .holiday-edit-page::before {
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
        animation: fadeDown 0.4s ease;
    }

    .breadcrumb i {
        margin-right: 8px;
        color: #34d399;
    }

    @keyframes fadeDown {
        from { opacity: 0; transform: translateY(-16px); }
        to { opacity: 1; transform: translateY(0); }
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
        animation: fadeDown 0.5s ease;
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
        background: linear-gradient(145deg, #fbbf24, #f59e0b);
        color: white;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 12px 24px -8px rgba(245, 158, 11, 0.3);
        transition: all 0.3s ease;
    }

    .header-card:hover .header-icon {
        transform: scale(1.02);
    }

    .header-card h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 6px;
        background: linear-gradient(135deg, #0a2e1f, #d97706);
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

    .btn-primary {
        background: linear-gradient(145deg, #fbbf24, #f59e0b);
        color: white;
        box-shadow: 0 8px 20px -6px rgba(245, 158, 11, 0.35);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(245, 158, 11, 0.45);
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
        color: #d97706;
        border: 1.5px solid #fbbf24;
    }

    .btn-outline-primary:hover {
        background: #fffbeb;
        border-color: #d97706;
        color: #92400e;
        transform: translateY(-2px);
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

    .alert-success {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        color: #065f46;
        border-left: 4px solid #10b981;
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
        animation: fadeUp 0.6s ease;
    }

    .form-card:hover {
        box-shadow: 0 12px 40px rgba(16, 185, 129, 0.08);
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Form Content */
    .form-content {
        padding: 28px;
    }

    /* Form Sections */
    .form-section {
        margin-bottom: 28px;
        padding-bottom: 24px;
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
    }

    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .section-header i {
        color: #34d399;
        font-size: 1.1rem;
        width: 24px;
    }

    .section-header h6 {
        margin: 0;
        font-weight: 700;
        color: #0a2e1f;
        font-size: 1rem;
    }

    .section-badge {
        padding: 4px 14px;
        background: #f0f9f4;
        color: #059669;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: auto;
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
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.08);
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
        padding: 10px 20px;
        font-size: 0.85rem;
        border-radius: 12px;
        border: 1.5px dashed #fbbf24;
        transition: all 0.3s ease;
        background: transparent;
        color: #d97706;
    }

    .add-btn:hover {
        background: #fffbeb;
        border-color: #d97706;
        transform: translateY(-2px);
    }

    .add-btn i {
        font-size: 0.9rem;
    }

    /* Filters Grid */
    .filters-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-group .form-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6b7280;
        margin-bottom: 0;
    }

    .filter-group .form-control {
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

    .filter-group .form-control:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.08);
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
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.08) !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: #fffbeb !important;
        border: 1px solid #fde68a !important;
        border-radius: 8px !important;
        color: #d97706 !important;
        font-weight: 600 !important;
        font-size: 0.8rem !important;
        padding: 2px 10px !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #d97706 !important;
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
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.08) !important;
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
        background: #fffbeb !important;
        color: #d97706 !important;
    }

    .bootstrap-select .dropdown-menu .dropdown-item.active {
        background: linear-gradient(145deg, #fbbf24, #f59e0b) !important;
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
    @media (max-width: 1200px) {
        .filters-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 992px) {
        .holiday-edit-page {
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

        .filters-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .holiday-edit-page {
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

        .filters-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .section-header {
            gap: 8px;
        }

        .section-badge {
            margin-left: 0;
            font-size: 0.7rem;
        }
    }

    @media (max-width: 576px) {
        .holiday-edit-page {
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

        .filters-grid {
            gap: 12px;
        }
    }
</style>

<style>
    /* Larger typography for better readability */
    .holiday-edit-page {
        font-size: 16px;
        line-height: 1.55;
    }

    .holiday-edit-page .breadcrumb {
        font-size: 1rem;
    }

    .holiday-edit-page .header-card h1 {
        font-size: 36px;
        line-height: 1.2;
    }

    .holiday-edit-page .header-card p {
        font-size: 17px;
        line-height: 1.55;
    }

    .holiday-edit-page .btn {
        font-size: 1rem;
        padding: 13px 24px;
    }

    .holiday-edit-page .section-header h6 {
        font-size: 1.05rem;
    }

    .holiday-edit-page .holiday-field .form-label {
        font-size: 0.8rem;
    }

    .holiday-edit-page .holiday-field .form-control {
        font-size: 0.95rem;
    }

    .holiday-edit-page .filter-group .form-label {
        font-size: 0.8rem;
    }

    .holiday-edit-page .filter-group .form-control {
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .holiday-edit-page {
            font-size: 15px;
        }

        .holiday-edit-page .header-card h1 {
            font-size: 28px;
        }

        .holiday-edit-page .header-card p {
            font-size: 15px;
        }
    }
</style>

<style>
    /* Dark mode support */
    html[data-pms-theme="dark"] .holiday-edit-page {
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
        background: linear-gradient(135deg, #d9f1e4, #fbbf24);
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

    html[data-pms-theme="dark"] .form-section {
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .section-header h6 {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .section-badge {
        background: #183026;
        color: #34d399;
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
        border-color: #fbbf24;
        box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.08);
    }

    html[data-pms-theme="dark"] .holiday-field .form-control::placeholder {
        color: #6b7280;
    }

    html[data-pms-theme="dark"] .holiday-field .form-label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .filter-group .form-control {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .filter-group .form-control:focus {
        border-color: #fbbf24;
        box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.08);
    }

    html[data-pms-theme="dark"] .filter-group .form-label {
        color: #8ba198;
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
        color: #fbbf24;
        border-color: #fbbf24;
    }

    html[data-pms-theme="dark"] .btn-outline-primary:hover {
        background: #183026;
        color: #fde68a;
        border-color: #fde68a;
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
        border-color: #fbbf24 !important;
        color: #fbbf24 !important;
    }

    html[data-pms-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fbbf24 !important;
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
        color: #fbbf24 !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu .dropdown-item.active {
        background: linear-gradient(145deg, #fbbf24, #f59e0b) !important;
        color: white !important;
    }

    html[data-pms-theme="dark"] .alert-danger {
        background: #1a0d0d;
        color: #fca5a5;
        border-left-color: #ef4444;
    }

    html[data-pms-theme="dark"] .alert-success {
        background: #0d1b14;
        color: #34d399;
        border-left-color: #34d399;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addBtn = document.getElementById('add-holiday-item');
        const holidayItems = document.getElementById('holidayItems');

        // Add new holiday item
        if (addBtn) {
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
                        <input type="text" name="occassion[]" class="form-control" placeholder="e.g., Republic Day" required>
                    </div>
                    <button type="button" class="btn btn-outline-danger remove-item" aria-label="Remove holiday">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                holidayItems.appendChild(item);
            });
        }

        // Remove holiday item
        document.addEventListener('click', function (e) {
            if (e.target.closest('.remove-item')) {
                const item = e.target.closest('.holiday-item');
                if (item) {
                    item.style.animation = 'itemFadeOut 0.3s ease';
                    setTimeout(() => {
                        item.remove();
                    }, 300);
                }
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
