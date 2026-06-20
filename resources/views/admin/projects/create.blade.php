@extends('admin.layout.app')

@section('title', 'Create Project')

@section('content')
<div class="create-project-page">
    <div class="container-fluid px-4">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <i class="fas fa-plus-circle"></i>
            <span>Dashboard / Projects / <strong>Create Project</strong></span>
        </div>

        <!-- Header Card -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div>
                    <h1>Create New Project</h1>
                    <p>Fill in the details to create a new project</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('projects.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
            </div>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
        <div class="alert alert-danger">
            <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Main Form Card -->
        <div class="content-card">
            <div class="form-header">
                <div class="form-title">
                    <div class="form-title-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h4>Project Details</h4>
                        <span class="muted">Fill in all required fields marked with <span class="text-danger">*</span></span>
                    </div>
                </div>
            </div>

            <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" id="projectForm">
                @csrf

                <div class="form-body">
                    <!-- Basic Information Section -->
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i>
                        <span>Basic Information</span>
                    </div>

                    <div class="form-grid">
                        <!-- Short Code -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-code"></i> Short Code <span class="text-danger">*</span>
                            </label>
                            <div class="shortcode-options">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="shortcode_option_radio" id="shortcode_auto" value="auto" {{ old('shortcode_option', 'auto') === 'auto' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shortcode_auto">Auto-generate</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="shortcode_option_radio" id="shortcode_manual_opt" value="manual" {{ old('shortcode_option') === 'manual' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shortcode_manual_opt">Custom</label>
                                </div>
                            </div>
                            <input type="hidden" name="shortcode_option" id="shortcode_option" value="{{ old('shortcode_option', 'auto') }}">
                            <input type="text" id="shortcode_display" class="form-control shortcode-display" value="{{ $nextProjectCode ?? 'Will be generated automatically' }}" readonly>
                            <input type="text" name="shortcode_manual" id="shortcode_manual" class="form-control d-none" placeholder="Enter shortcode e.g. bit25-26/0001" value="{{ old('shortcode_manual') }}">
                            <small class="form-hint">Use auto for the next bit code, or custom to enter your own project code.</small>
                            @error('shortcode_manual')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Project Name -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-tag"></i> Project Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="Enter project name">
                        </div>

                        <!-- Start Date -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-plus"></i> Start Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="start_date" class="form-control" required value="{{ old('start_date') }}">
                        </div>

                        <!-- Deadline -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-times"></i> Deadline <span class="text-danger" id="deadline_required">*</span>
                            </label>
                            <input type="date" name="deadline" class="form-control" id="deadline_input" value="{{ old('deadline') }}">
                        </div>

                        <!-- No Deadline Checkbox -->
                        <div class="form-group checkbox-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="without_deadline" id="without_deadline" {{ old('without_deadline') ? 'checked' : '' }}>
                                <label class="form-check-label" for="without_deadline">
                                    <i class="fas fa-infinity"></i> No deadline for this project
                                </label>
                            </div>
                        </div>

                        <!-- Priority -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-bolt"></i> Priority <span class="text-danger">*</span>
                            </label>
                            <select name="priority" class="form-control" required>
                                @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('priority', 'medium') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Progress -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-chart-simple"></i> Progress Percentage
                            </label>
                            <input type="number" name="completion_percent" class="form-control" min="0" max="100" value="{{ old('completion_percent', 0) }}" placeholder="0">
                        </div>

                        <!-- Project Category -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-folder"></i> Project Category <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <select name="category_id" id="project_category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#catModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Project Departments -->
                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-building"></i> Project Department <span class="text-danger">*</span>
                            </label>
                            @php
                                $selectedDepartmentIds = collect(old('department_ids', []))->map(fn($id) => (string) $id)->all();
                            @endphp
                            <div class="department-picker" id="projectDepartmentPicker">
                                <button type="button" class="department-picker-toggle" id="departmentPickerToggle" aria-expanded="false">
                                    <span id="departmentPickerText">Select project departments</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>

                                <div class="department-picker-menu d-none" id="departmentPickerMenu">
                                    <input type="text" class="department-search" id="departmentSearch" placeholder="Search departments...">

                                    <div class="department-picker-actions">
                                        <button type="button" id="selectAllDepartments">Select All</button>
                                        <button type="button" id="deselectAllDepartments">Deselect All</button>
                                    </div>

                                    <div class="department-options" id="departmentOptions">
                                        @foreach($departments as $department)
                                            @php
                                                $departmentLabel = trim(($department->dpt_code ? $department->dpt_code . ' - ' : '') . $department->dpt_name);
                                            @endphp
                                            <label class="department-option" data-label="{{ strtolower($departmentLabel . ' ' . optional($department->parent)->dpt_name) }}">
                                                <input
                                                    type="checkbox"
                                                    class="department-checkbox"
                                                    value="{{ $department->id }}"
                                                    data-label="{{ $departmentLabel }}"
                                                    {{ in_array((string) $department->id, $selectedDepartmentIds, true) ? 'checked' : '' }}
                                                >
                                                <span>{{ $departmentLabel }}</span>
                                                @if(optional($department->parent)->dpt_name)
                                                    <small>{{ $department->parent->dpt_name }}</small>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="department-quick-add">
                                        <strong>Add Department</strong>
                                        <div class="department-quick-grid">
                                            <select id="quick_parent_dpt_id" class="form-control">
                                                <option value="">Parent Department</option>
                                                @foreach($prtdepartments as $prt)
                                                    <option value="{{ $prt->id }}">{{ $prt->dpt_name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" id="quick_department_name" class="form-control" placeholder="Department name">
                                            <button type="button" class="btn btn-primary" id="quickAddDepartment">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </div>
                                        <div id="quickDepartmentError" class="quick-add-error d-none"></div>
                                    </div>
                                </div>
                            </div>

                            <select name="department_ids[]" id="project_department_ids" class="department-native-select" multiple>
                                @foreach($departments as $department)
                                    @php
                                        $departmentLabel = trim(($department->dpt_code ? $department->dpt_code . ' - ' : '') . $department->dpt_name);
                                    @endphp
                                    <option value="{{ $department->id }}" {{ in_array((string) $department->id, $selectedDepartmentIds, true) ? 'selected' : '' }}>
                                        {{ $departmentLabel }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-hint">Select one or more departments. New departments are saved to the department database.</small>
                        </div>

                        <!-- Client -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-tie"></i> Client <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <select name="client_id" id="client_id" class="form-control" required>
                                    <option value="">Select Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#clientModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Project Summary -->
                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> Project Summary
                            </label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter project summary">{{ old('description') }}</textarea>
                        </div>

                        <!-- Notes -->
                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-sticky-note"></i> Notes
                            </label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Add any additional notes">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Remarks -->
                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-comment-dots"></i> Remarks
                            </label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Initial project remarks">{{ old('remarks') }}</textarea>
                        </div>
                    </div>

                    <!-- Settings Section -->
                    <div class="section-title mt-4">
                        <i class="fas fa-cog"></i>
                        <span>Project Settings</span>
                    </div>

                    <div class="settings-grid">
                        <!-- Public Gantt Chart -->
                        <div class="form-group">
                            <label class="form-label">Public Gantt Chart</label>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input type="radio" value="enable" class="form-check-input" id="public_gantt_chart-yes" name="public_gantt_chart" {{ old('public_gantt_chart', 'enable') == 'enable' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="public_gantt_chart-yes">Enable</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" value="disable" class="form-check-input" id="public_gantt_chart-no" name="public_gantt_chart" {{ old('public_gantt_chart') == 'disable' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="public_gantt_chart-no">Disable</label>
                                </div>
                            </div>
                        </div>

                        <!-- Public Task Board -->
                        <div class="form-group">
                            <label class="form-label">Public Task Board</label>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input type="radio" value="enable" class="form-check-input" id="public_taskboard-yes" name="public_taskboard" {{ old('public_taskboard', 'enable') == 'enable' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="public_taskboard-yes">Enable</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" value="disable" class="form-check-input" id="public_taskboard-no" name="public_taskboard" {{ old('public_taskboard') == 'disable' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="public_taskboard-no">Disable</label>
                                </div>
                            </div>
                        </div>

                        <!-- Task Approval -->
                        <div class="form-group">
                            <label class="form-label">Task Approval Required</label>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input type="radio" value="1" class="form-check-input" id="need_approval_by_admin-yes" name="need_approval_by_admin" {{ old('need_approval_by_admin') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="need_approval_by_admin-yes">Enable</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" value="0" class="form-check-input" id="need_approval_by_admin-no" name="need_approval_by_admin" {{ old('need_approval_by_admin', '0') == '0' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="need_approval_by_admin-no">Disable</label>
                                </div>
                            </div>
                        </div>

                        <!-- Public Project -->
                        <div class="form-group checkbox-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="public" id="is_public" {{ old('public') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_public">
                                    <i class="fas fa-globe"></i> Create Public Project
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Project Members -->
                    <div class="section-title mt-4">
                        <i class="fas fa-users"></i>
                        <span>Project Members <span class="text-danger">*</span></span>
                    </div>

                    <div class="members-section">
                        <div class="form-group">
                            <label class="form-label">Select Members</label>
                            <select name="employee_ids[]" id="projectMembers" class="form-control" multiple>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" data-fullname="{{ $u->name }}">
                                        {{ $u->name }} ({{ $u->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-hint">Hold Ctrl/Cmd to select multiple members</small>
                        </div>
                        <div class="add-member-btn">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#employeeModal">
                                <i class="fas fa-user-plus"></i> Add Employee
                            </button>
                        </div>
                    </div>

                    <!-- Other Details (Collapsible) -->
                    <div class="other-details-section mt-4">
                        <div class="other-details-toggle" data-bs-toggle="collapse" data-bs-target="#otherDetails" aria-expanded="false">
                            <i class="fas fa-chevron-down"></i>
                            <span>Other Details</span>
                            <small class="text-muted">(Optional settings)</small>
                        </div>

                        <div class="collapse" id="otherDetails">
                            <div class="other-details-body">
                                <!-- File Upload -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-file-upload"></i> Add File
                                    </label>
                                    <input type="file" class="form-control" id="project_file" name="project_file">
                                </div>

                                <div class="form-grid">
                                    <!-- Currency -->
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-dollar-sign"></i> Currency
                                        </label>
                                        <select id="currency" name="currency_id" class="form-control">
                                            <option value="">Select Currency</option>
                                            @foreach($currency as $c)
                                                <option value="{{ $c->id }}" {{ old('currency_id') == $c->id ? 'selected' : '' }}>{{ $c->currency_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Project Budget -->
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-wallet"></i> Project Budget
                                        </label>
                                        <input type="number" class="form-control" id="project_budget" name="project_budget" placeholder="e.g. 10000" value="{{ old('project_budget') }}">
                                    </div>

                                    <!-- Hours Estimate -->
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-clock"></i> Hours Estimate
                                        </label>
                                        <input type="number" class="form-control" id="hours_allocated" name="hours_allocated" placeholder="e.g. 50" value="{{ old('hours_allocated') }}">
                                    </div>
                                </div>

                                <!-- Additional Checkboxes -->
                                <div class="checkbox-grid">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="manual_timelog" id="manual_timelog" {{ old('manual_timelog') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="manual_timelog">
                                            <i class="fas fa-stopwatch"></i> Allow manual time logs
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="enable_miroboard" id="miroboard_checkbox" {{ old('enable_miroboard') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="miroboard_checkbox">
                                            <i class="fas fa-chalkboard"></i> Enable Miroboard
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allow_client_notification" id="client_task_notification" {{ old('allow_client_notification') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="client_task_notification">
                                            <i class="fas fa-bell"></i> Send task notification to client
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Project
                        </button>
                        <a href="{{ route('projects.index') }}" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include all modals here (Category, Client, Employee, Parent Department, Department) -->
<!-- [All modal code remains exactly as provided - keeping functionality intact] -->

@include('admin.projects.partials.modals')

<style>
    /* ===== PREMIUM CREATE PROJECT PAGE - GREEN/TEAL THEME ===== */
    .create-project-page {
        padding: 30px 0;
        min-height: 100vh;
        background: linear-gradient(145deg, #f7fbf9, #eef7f2);
        color: #07130d;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 16px 26px;
        border-radius: 18px;
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 28px;
        color: #0f744c;
        font-weight: 600;
        font-size: 1.05rem;
    }

    .breadcrumb i {
        margin-right: 12px;
        color: #34d399;
        font-size: 1.1rem;
    }

    .breadcrumb strong {
        color: #07130d;
    }

    /* Header Card */
    .header-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 30px 36px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        box-shadow: 0 18px 45px rgba(15, 116, 76, .09);
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 28px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .header-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 10px 25px rgba(16, 185, 129, .2);
    }

    .header-card h1 {
        font-size: 34px;
        font-weight: 700;
        margin-bottom: 6px;
        color: #07130d;
    }

    .header-card p {
        color: #52645a;
        font-size: 17px;
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        padding: 12px 24px;
        border-radius: 14px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s ease;
        text-decoration: none;
        min-height: 48px;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid rgba(15, 116, 76, .2);
        color: #0f744c;
    }

    .btn-outline:hover {
        background: #edf8f2;
        border-color: #34d399;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 116, 76, .1);
    }

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        box-shadow: 0 8px 20px rgba(16, 185, 129, .25);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(16, 185, 129, .35);
    }

    /* Alert */
    .alert {
        border-radius: 16px;
        padding: 18px 24px;
        margin-bottom: 22px;
        border: none;
        display: flex;
        gap: 16px;
        align-items: flex-start;
        font-size: 1rem;
    }

    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .alert-icon {
        font-size: 1.4rem;
        color: #ef4444;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .alert ul {
        padding-left: 20px;
        color: #991b1b;
    }

    /* Content Card */
    .content-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 18px 45px rgba(15, 116, 76, .08);
        overflow: hidden;
    }

    /* Form Header */
    .form-header {
        padding: 22px 28px;
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .1);
    }

    .form-title {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .form-title-icon {
        width: 48px;
        height: 48px;
        background: #e7f5ee;
        color: #0f744c;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .form-title h4 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        color: #07130d;
    }

    .form-title .muted {
        font-size: 0.95rem;
        color: #8ba198;
        display: block;
        margin-top: 2px;
    }

    .text-danger {
        color: #dc2626;
    }

    /* Form Body */
    .form-body {
        padding: 28px;
    }

    /* Section Title */
    .section-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        color: #07130d;
        padding-bottom: 12px;
        border-bottom: 2px solid rgba(15, 116, 76, .08);
        margin-bottom: 20px;
    }

    .section-title i {
        color: #0f744c;
        font-size: 1.2rem;
    }

    /* Form Grid */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group.checkbox-group {
        justify-content: flex-end;
        padding-bottom: 4px;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.95rem;
        color: #07130d;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label i {
        color: #0f744c;
        font-size: 0.9rem;
    }

    .form-control {
        border-radius: 12px;
        border: 1px solid rgba(15, 116, 76, .18);
        padding: 12px 16px;
        font-weight: 500;
        font-size: 1rem;
        min-height: 48px;
        transition: all 0.2s ease;
        background: #fafefb;
        width: 100%;
    }

    .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
    }

    .form-control[readonly] {
        background: #f0f9f4;
        cursor: not-allowed;
    }

    textarea.form-control {
        min-height: 80px;
        resize: vertical;
    }

    .input-group {
        display: flex;
        gap: 0;
    }

    .input-group .form-control {
        border-radius: 12px 0 0 12px;
    }

    .input-group .btn {
        border-radius: 0 12px 12px 0;
        min-height: 48px;
        padding: 0 16px;
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(15, 116, 76, .18);
        border-left: none;
    }

    .input-group .btn:hover {
        background: #d1fae5;
    }

    .department-picker {
        position: relative;
    }

    .department-picker-toggle {
        width: 100%;
        min-height: 52px;
        border: 1px solid rgba(15, 116, 76, .18);
        border-radius: 12px;
        background: #fafefb;
        color: #07130d;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        font-weight: 600;
        text-align: left;
    }

    .department-picker-toggle span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .department-picker-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        z-index: 1080;
        background: #ffffff;
        border: 1px solid rgba(15, 116, 76, .16);
        border-radius: 14px;
        box-shadow: 0 18px 45px rgba(15, 116, 76, .16);
        padding: 14px;
    }

    .department-search {
        width: 100%;
        border: 1px solid rgba(15, 116, 76, .18);
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 12px;
        background: #fafefb;
    }

    .department-picker-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 12px;
    }

    .department-picker-actions button {
        border: none;
        background: #f0f9f4;
        color: #0f744c;
        border-radius: 10px;
        padding: 10px;
        font-weight: 700;
    }

    .department-options {
        max-height: 330px;
        overflow-y: auto;
        padding-right: 4px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .department-option {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(15, 116, 76, .10);
        border-radius: 999px;
        padding: 10px 14px;
        background: #ffffff;
        cursor: pointer;
        font-weight: 700;
        color: #07130d;
    }

    .department-option:hover {
        background: #edf8f2;
    }

    .department-option input {
        width: 18px;
        height: 18px;
        accent-color: #0f744c;
    }

    .department-option small {
        color: #6b7d73;
        font-weight: 500;
        margin-left: auto;
    }

    .department-quick-add {
        border-top: 1px solid rgba(15, 116, 76, .10);
        margin-top: 14px;
        padding-top: 14px;
    }

    .department-quick-grid {
        display: grid;
        grid-template-columns: minmax(170px, .8fr) 1fr auto;
        gap: 10px;
        margin-top: 10px;
    }

    .quick-add-error {
        margin-top: 8px;
        color: #dc2626;
        font-size: .9rem;
        font-weight: 600;
    }

    .department-native-select {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .form-hint {
        font-size: 0.85rem;
        color: #8ba198;
        margin-top: 4px;
    }

    /* Shortcode */
    .shortcode-options {
        display: flex;
        gap: 20px;
        margin-bottom: 8px;
    }

    .shortcode-display {
        font-weight: 700;
        color: #0f744c;
    }

    /* Settings Grid */
    .settings-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 20px;
    }

    .radio-group {
        display: flex;
        gap: 20px;
        margin-top: 4px;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 2px solid #a7f3d0;
        cursor: pointer;
        transition: all 0.2s;
    }

    .form-check-input:checked {
        background-color: #0f744c;
        border-color: #0f744c;
    }

    .form-check-input[type="radio"] {
        border-radius: 50%;
    }

    .form-check-label {
        font-size: 0.95rem;
        cursor: pointer;
        color: #07130d;
        font-weight: 500;
    }

    /* Members Section */
    .members-section {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 16px;
        align-items: end;
    }

    .add-member-btn {
        padding-bottom: 4px;
    }

    /* Other Details */
    .other-details-toggle {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        background: #f0f9f4;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 700;
        font-size: 1rem;
        color: #07130d;
    }

    .other-details-toggle:hover {
        background: #d1fae5;
    }

    .other-details-toggle i:first-child {
        color: #0f744c;
        transition: transform 0.3s;
    }

    .other-details-toggle[aria-expanded="true"] i:first-child {
        transform: rotate(180deg);
    }

    .other-details-toggle small {
        font-weight: 400;
    }

    .other-details-body {
        padding: 20px;
        background: #fafefb;
        border-radius: 0 0 12px 12px;
        border: 1px solid rgba(15, 116, 76, .08);
        border-top: none;
    }

    .checkbox-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 16px;
        margin-top: 16px;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 16px;
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid rgba(15, 116, 76, .08);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .settings-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 992px) {
        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }
        .header-actions {
            width: 100%;
        }
        .form-grid {
            grid-template-columns: 1fr;
        }
        .members-section {
            grid-template-columns: 1fr;
        }
        .checkbox-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .create-project-page {
            padding: 16px 0;
        }
        .header-card {
            padding: 20px 24px;
        }
        .header-card h1 {
            font-size: 26px;
        }
        .header-card p {
            font-size: 15px;
        }
        .form-body {
            padding: 20px;
        }
        .settings-grid {
            grid-template-columns: 1fr;
        }
        .form-actions {
            flex-direction: column;
        }
        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }
        .shortcode-options {
            flex-wrap: wrap;
        }
        .department-picker-menu {
            position: static;
            margin-top: 8px;
        }
        .department-quick-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Dark Mode Support */
    html[data-pms-theme="dark"] .create-project-page {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .breadcrumb,
    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .content-card {
        background: #102119;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .header-card h1,
    html[data-pms-theme="dark"] .form-title h4,
    html[data-pms-theme="dark"] .section-title {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .form-control {
        background: #183026;
        color: #ffffff;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .form-control[readonly] {
        background: #142a20;
    }

    html[data-pms-theme="dark"] .form-header {
        background: #142a20;
        border-color: rgba(122, 240, 181, .16);
    }

    html[data-pms-theme="dark"] .form-title-icon {
        background: rgba(122, 240, 181, .15);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .other-details-toggle {
        background: #142a20;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .other-details-toggle:hover {
        background: #183026;
    }

    html[data-pms-theme="dark"] .other-details-body {
        background: #142a20;
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .form-label {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .form-check-label {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .input-group .btn {
        background: #183026;
        color: #7af0b5;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .input-group .btn:hover {
        background: #142a20;
    }

    html[data-pms-theme="dark"] .department-picker-toggle,
    html[data-pms-theme="dark"] .department-picker-menu,
    html[data-pms-theme="dark"] .department-search,
    html[data-pms-theme="dark"] .department-option {
        background: #183026;
        color: #ffffff;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .department-picker-actions button {
        background: #142a20;
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .department-option:hover {
        background: #142a20;
    }

    html[data-pms-theme="dark"] .department-option small {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .section-title {
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .form-actions {
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .alert-danger {
        background: rgba(239, 68, 68, .15);
        color: #f87171;
    }

    html[data-pms-theme="dark"] .alert ul {
        color: #f87171;
    }
</style>

@push('js')
<script>
    // Shortcode toggle + hidden fallback sync
    (function () {
        function setShortcodeOption(val) {
            const hidden = document.getElementById('shortcode_option');
            if (hidden) hidden.value = val;
        }

        function toggleShortcodeInputs() {
            const manualInput = document.getElementById('shortcode_manual');
            const display = document.getElementById('shortcode_display');
            const manualOpt = document.getElementById('shortcode_manual_opt');

            if (manualOpt && manualOpt.checked) {
                manualInput.classList.remove('d-none');
                display.classList.add('d-none');
                manualInput.focus();
            } else {
                manualInput.classList.add('d-none');
                display.classList.remove('d-none');
            }

            const selected = document.querySelector('input[name="shortcode_option_radio"]:checked');
            if (selected) setShortcodeOption(selected.value);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // init select2
            $('.select2').select2({
                placeholder: "Select Employees",
                allowClear: true,
                width: '100%'
            });

            $('#currency').select2({
                placeholder: "Select Currency",
                allowClear: true,
                width: '100%'
            });

            // bind radios
            const radios = document.querySelectorAll('input[name="shortcode_option_radio"]');
            radios.forEach(r => r.addEventListener('change', toggleShortcodeInputs));

            // init toggles
            toggleShortcodeInputs();

            // disable/enable deadline field + hide/show star
            $('#without_deadline').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#deadline_input').val('').prop('disabled', true);
                    $('#deadline_required').addClass('d-none');
                } else {
                    $('#deadline_input').prop('disabled', false);
                    $('#deadline_required').removeClass('d-none');
                }
            }).trigger('change');

            initProjectDepartmentPicker();

            // Toggle password visibility
            $(document).on('click', '.toggle-password', function() {
                const input = $(this).closest('.input-group').find('input[type="password"], input[type="text"]');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Generate random password
            $(document).on('click', '.generate-password', function() {
                function randPass(len = 10) {
                    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
                    let out = '';
                    for (let i = 0; i < len; i++) {
                        out += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    return out;
                }
                $(this).closest('.input-group').find('input[name="password"]').val(randPass(10));
            });

            // Modal z-index fix for nested modals
            document.addEventListener("show.bs.modal", function (event) {
                const zIndex = 1050 + 10 * document.querySelectorAll('.modal.show').length;
                event.target.style.zIndex = zIndex;
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops[backdrops.length - 1].style.zIndex = zIndex - 1;
                });
            });
        });
    })();

    function initProjectDepartmentPicker() {
        const picker = document.getElementById('projectDepartmentPicker');
        const toggle = document.getElementById('departmentPickerToggle');
        const menu = document.getElementById('departmentPickerMenu');
        const text = document.getElementById('departmentPickerText');
        const nativeSelect = document.getElementById('project_department_ids');
        const search = document.getElementById('departmentSearch');
        const selectAll = document.getElementById('selectAllDepartments');
        const deselectAll = document.getElementById('deselectAllDepartments');
        const quickAdd = document.getElementById('quickAddDepartment');
        const quickName = document.getElementById('quick_department_name');
        const quickParent = document.getElementById('quick_parent_dpt_id');
        const quickError = document.getElementById('quickDepartmentError');

        if (!picker || !toggle || !menu || !nativeSelect) return;

        const getCheckboxes = () => Array.from(picker.querySelectorAll('.department-checkbox'));

        function syncNativeSelect() {
            const checkedValues = getCheckboxes()
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);

            Array.from(nativeSelect.options).forEach(option => {
                option.selected = checkedValues.includes(option.value);
            });

            const checkedLabels = getCheckboxes()
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.dataset.label);

            if (!checkedLabels.length) {
                text.textContent = 'Select project departments';
            } else if (checkedLabels.length <= 2) {
                text.textContent = checkedLabels.join(', ');
            } else {
                text.textContent = `${checkedLabels.length} departments selected`;
            }
        }

        function addDepartmentOption(department, parentLabel = '') {
            const label = `${department.dpt_code ? department.dpt_code + ' - ' : ''}${department.dpt_name}`;
            const optionWrapper = document.createElement('label');
            optionWrapper.className = 'department-option';
            optionWrapper.dataset.label = `${label} ${parentLabel}`.toLowerCase();

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'department-checkbox';
            checkbox.value = department.id;
            checkbox.dataset.label = label;
            checkbox.checked = true;

            const labelText = document.createElement('span');
            labelText.textContent = label;

            optionWrapper.appendChild(checkbox);
            optionWrapper.appendChild(labelText);

            if (parentLabel) {
                const parentText = document.createElement('small');
                parentText.textContent = parentLabel;
                optionWrapper.appendChild(parentText);
            }

            document.getElementById('departmentOptions').appendChild(optionWrapper);

            const nativeOption = new Option(label, department.id, true, true);
            nativeSelect.add(nativeOption);
            syncNativeSelect();
        }

        toggle.addEventListener('click', function () {
            const willOpen = menu.classList.contains('d-none');
            menu.classList.toggle('d-none');
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
            if (!picker.contains(event.target)) {
                menu.classList.add('d-none');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        picker.addEventListener('change', function (event) {
            if (event.target.classList.contains('department-checkbox')) {
                syncNativeSelect();
            }
        });

        search.addEventListener('input', function () {
            const query = search.value.trim().toLowerCase();
            picker.querySelectorAll('.department-option').forEach(option => {
                option.classList.toggle('d-none', query && !option.dataset.label.includes(query));
            });
        });

        selectAll.addEventListener('click', function () {
            getCheckboxes().forEach(checkbox => checkbox.checked = true);
            syncNativeSelect();
        });

        deselectAll.addEventListener('click', function () {
            getCheckboxes().forEach(checkbox => checkbox.checked = false);
            syncNativeSelect();
        });

        quickAdd.addEventListener('click', function () {
            quickError.classList.add('d-none');
            quickError.textContent = '';

            if (!quickParent.value || !quickName.value.trim()) {
                quickError.textContent = 'Choose a parent department and enter a department name.';
                quickError.classList.remove('d-none');
                return;
            }

            quickAdd.disabled = true;

            $.ajax({
                url: '{{ route('departments.store.ajax') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    parent_dpt_id: quickParent.value,
                    dpt_name: quickName.value.trim(),
                    status: 'Active'
                },
                success: function (res) {
                    if (res.status === 'success' && res.department) {
                        const parentLabel = quickParent.options[quickParent.selectedIndex]?.text || '';
                        addDepartmentOption(res.department, parentLabel);
                        quickName.value = '';
                    }
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    quickError.textContent = errors.dpt_name?.[0] || errors.parent_dpt_id?.[0] || xhr.responseJSON?.message || 'Could not add department.';
                    quickError.classList.remove('d-none');
                },
                complete: function () {
                    quickAdd.disabled = false;
                }
            });
        });

        const form = document.getElementById('projectForm');
        if (form) {
            form.addEventListener('submit', function (event) {
                syncNativeSelect();
                if (!Array.from(nativeSelect.selectedOptions).length) {
                    event.preventDefault();
                    menu.classList.remove('d-none');
                    toggle.setAttribute('aria-expanded', 'true');
                    quickError.textContent = 'Select at least one project department.';
                    quickError.classList.remove('d-none');
                }
            });
        }

        syncNativeSelect();
    }

    // Add Project Category via AJAX
    $('#addCatForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route('project-categories.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status === 'success') {
                    $('#project_category_id').append(
                        `<option value="${res.cat.id}" selected>${res.cat.category_name}</option>`
                    ).trigger('change');

                    $('#addCatForm')[0].reset();

                    const modalEl = document.getElementById('catModal');
                    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modalInstance.hide();

                    Swal.fire({
                        title: 'Success!',
                        text: 'Project Category added successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                $('#cat-error').removeClass('d-none').text(xhr.responseJSON.message || 'Error occurred.');
            }
        });
    });

    // Delete Project Category
    $(document).on('click', '.delete-cat', function () {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this category?')) {
            $.ajax({
                url: `{{ url('project-categories') }}/${id}`,
                method: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {
                    if (res.status === 'success') {
                        $(`#cat-row-${id}`).remove();
                        $(`#project_category_id option[value="${id}"]`).remove();
                    }
                }
            });
        }
    });

    // Add Client via AJAX
    $('#addClientForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route('project.clientstore') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status === 'success') {
                    $('#client_id').append(
                        `<option value="${res.client.id}" selected>${res.client.name}</option>`
                    );

                    $('#addClientForm')[0].reset();

                    const modalEl = document.getElementById('clientModal');
                    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modalInstance.hide();

                    Swal.fire({
                        title: 'Success!',
                        text: 'Client added successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                $('#client-error').removeClass('d-none').text(xhr.responseJSON.message || 'Error occurred.');
            }
        });
    });

    // Department cascade
    $('#parent_dpt_id').on('change', function () {
        let parentId = $(this).val();

        $('#department_id').html('<option>Loading...</option>');

        $.ajax({
            url: "{{ route('get.subdepartments', '') }}/" + parentId,
            type: 'GET',
            success: function (res) {
                let html = '<option value="">Select</option>';
                res.forEach(function (dpt) {
                    html += `<option value="${dpt.id}">${dpt.dpt_name}</option>`;
                });
                $('#department_id').html(html);
            }
        });
    });

    // Select2 for project members
    $(document).ready(function() {
        $('#projectMembers').select2({
            placeholder: "Select Employees",
            allowClear: true,
            width: '100%'
        });
    });

    // Employee names hidden inputs for form submission
    (function(){
        const form = document.querySelector('form[action="{{ route('projects.store') }}"]');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            document.querySelectorAll('input[name="employee_names[]"]').forEach(i => i.remove());

            const sel = document.getElementById('projectMembers');
            if (!sel) return;

            let selected = [];
            if (typeof $(sel).select2 === 'function') {
                const vals = $(sel).val() || [];
                vals.forEach(v => {
                    const opt = sel.querySelector(`option[value="${v}"]`);
                    if (opt) selected.push(opt);
                });
            } else {
                selected = Array.from(sel.selectedOptions || []);
            }

            selected.forEach(opt => {
                const fullName = opt.dataset.fullname ?? opt.textContent.trim();
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'employee_names[]';
                input.value = fullName;
                form.appendChild(input);
            });
        });
    })();
</script>
@endpush
@endsection
