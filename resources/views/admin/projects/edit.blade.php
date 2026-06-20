@extends('admin.layout.app')

@section('title', 'Edit Project - ' . $project->name)

@section('content')
<div class="edit-project-page">
    <div class="container-fluid px-4">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <i class="fas fa-edit"></i>
            <span>Dashboard / Projects / <a href="{{ route('projects.show', $project->id) }}">{{ $project->name }}</a> / <strong>Edit</strong></span>
        </div>

        <!-- Header Card -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div>
                    <h1>Edit Project</h1>
                    <p>Update project details for <strong>{{ $project->name }}</strong></p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('projects.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-outline">
                    <i class="fas fa-eye"></i> View Project
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
                        <span class="muted">Update the project information below</span>
                    </div>
                </div>
                <div class="form-status">
                    <span class="status-badge">
                        <i class="fas fa-circle"></i> Editing
                    </span>
                </div>
            </div>

            <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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
                            <input type="text" name="project_code" class="form-control"
                                   value="{{ old('project_code', $project->project_code) }}"
                                   placeholder="Project unique short code" required>
                            <small class="form-hint">A unique identifier for this project</small>
                        </div>

                        <!-- Project Name -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-tag"></i> Project Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $project->name) }}" required>
                        </div>

                        <!-- Start Date -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-plus"></i> Start Date
                            </label>
                            <input type="date" name="start_date" class="form-control"
                                   value="{{ old('start_date', $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '') }}">
                        </div>

                        <!-- Deadline -->
                        @php
                            $withoutDeadlineChecked = old('without_deadline', $project->without_deadline);
                        @endphp
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-times"></i> Deadline
                            </label>
                            <input type="date" name="deadline" id="deadline_input" class="form-control"
                                   value="{{ old('deadline', $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('Y-m-d') : '') }}"
                                   {{ $withoutDeadlineChecked ? 'disabled' : '' }}>
                        </div>

                        <!-- No Deadline Checkbox -->
                        <div class="form-group checkbox-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="without_deadline" id="without_deadline"
                                       {{ $withoutDeadlineChecked ? 'checked' : '' }} value="1">
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
                                    <option value="{{ $value }}" {{ old('priority', $project->priority ?? 'medium') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Progress -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-chart-simple"></i> Progress Percentage
                            </label>
                            <input type="number" name="completion_percent" class="form-control" min="0" max="100" value="{{ old('completion_percent', $project->completion_percent ?? 0) }}">
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
                                        <option value="{{ $cat->id }}" {{ old('category_id', $project->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#catModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Department -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-building"></i> Department <span class="text-danger">*</span>
                            </label>
                            <select name="department_id" class="form-control" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $project->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->dpt_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Client -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-tie"></i> Client
                            </label>
                            <div class="input-group">
                                <select name="client_id" id="client_id" class="form-control">
                                    <option value="">Select Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id', $project->client_id) == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
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
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $project->description) }}</textarea>
                        </div>

                        <!-- Notes -->
                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-sticky-note"></i> Notes
                            </label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $project->notes) }}</textarea>
                        </div>

                        <!-- Remarks -->
                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-comment-dots"></i> Remarks
                            </label>
                            <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $project->remarks) }}</textarea>
                        </div>
                    </div>

                    <!-- Settings Section -->
                    <div class="section-title mt-4">
                        <i class="fas fa-cog"></i>
                        <span>Project Settings</span>
                    </div>

                    <div class="settings-grid">
                        <div class="form-group checkbox-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="public_gantt_chart" id="public_gantt_chart"
                                       value="1" {{ old('public_gantt_chart', $project->public_gantt_chart) ? 'checked' : '' }}>
                                <label class="form-check-label" for="public_gantt_chart">
                                    <i class="fas fa-chart-bar"></i> Public Gantt Chart
                                </label>
                            </div>
                        </div>

                        <div class="form-group checkbox-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="public_taskboard" id="public_taskboard"
                                       value="1" {{ old('public_taskboard', $project->public_taskboard) ? 'checked' : '' }}>
                                <label class="form-check-label" for="public_taskboard">
                                    <i class="fas fa-columns"></i> Public Task Board
                                </label>
                            </div>
                        </div>

                        <div class="form-group checkbox-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="need_approval_by_admin" id="need_approval_by_admin"
                                       value="1" {{ old('need_approval_by_admin', $project->need_approval_by_admin) ? 'checked' : '' }}>
                                <label class="form-check-label" for="need_approval_by_admin">
                                    <i class="fas fa-check-double"></i> Task approval required
                                </label>
                            </div>
                        </div>

                        <div class="form-group checkbox-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="public" id="is_public" value="1"
                                       {{ old('public', $project->public) ? 'checked' : '' }}>
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
                            @php
                                $selectedMemberIds = old('employee_ids', $project->users->pluck('id')->toArray());
                            @endphp
                            <select name="employee_ids[]" id="employee_ids" class="form-control" multiple>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" data-fullname="{{ $u->name }}"
                                        {{ in_array($u->id, $selectedMemberIds) ? 'selected' : '' }}>
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
                                    @if(!empty($project->project_file))
                                        <div class="current-file">
                                            <i class="fas fa-file"></i>
                                            <span>Current File:</span>
                                            <a href="{{ asset($project->project_file) }}" target="_blank" class="file-link">
                                                View File <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </div>
                                    @endif
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
                                                <option value="{{ $c->id }}" {{ old('currency_id', $project->currency_id) == $c->id ? 'selected' : '' }}>
                                                    {{ $c->currency_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Project Budget -->
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-wallet"></i> Project Budget
                                        </label>
                                        <input type="number" class="form-control" id="project_budget" name="project_budget"
                                               placeholder="e.g. 10000" value="{{ old('project_budget', $project->project_budget) }}">
                                    </div>

                                    <!-- Hours Estimate -->
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-clock"></i> Hours Estimate
                                        </label>
                                        <input type="number" class="form-control" id="hours_allocated" name="hours_allocated"
                                               placeholder="e.g. 50" value="{{ old('hours_allocated', $project->hours_allocated) }}">
                                    </div>
                                </div>

                                <!-- Additional Checkboxes -->
                                <div class="checkbox-grid">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="manual_timelog" id="manual_timelog" value="1"
                                               {{ old('manual_timelog', $project->manual_timelog) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="manual_timelog">
                                            <i class="fas fa-stopwatch"></i> Allow manual time logs
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="enable_miroboard" id="miroboard_checkbox" value="1"
                                               {{ old('enable_miroboard', $project->enable_miroboard) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="miroboard_checkbox">
                                            <i class="fas fa-chalkboard"></i> Enable Miroboard
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allow_client_notification" id="client_task_notification" value="1"
                                               {{ old('allow_client_notification', $project->allow_client_notification) ? 'checked' : '' }}>
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
                            <i class="fas fa-save"></i> Update Project
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

<!-- Include all modals here -->
@include('admin.projects.partials.edit-modals')

<style>
    /* ===== PREMIUM EDIT PROJECT PAGE - GREEN/TEAL THEME ===== */
    .edit-project-page {
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

    .breadcrumb a {
        color: #0f744c;
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumb a:hover {
        color: #10b981;
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

    .header-card p strong {
        color: #0f744c;
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
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
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

    .form-status {
        display: flex;
        align-items: center;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: #fef3c7;
        color: #92400e;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .status-badge i {
        font-size: 0.6rem;
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

    .form-control[disabled] {
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

    .form-hint {
        font-size: 0.85rem;
        color: #8ba198;
        margin-top: 4px;
    }

    /* Settings Grid */
    .settings-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 20px;
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

    .current-file {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        padding: 10px 14px;
        background: #f0f9f4;
        border-radius: 10px;
        font-size: 0.9rem;
    }

    .current-file i {
        color: #0f744c;
    }

    .file-link {
        color: #0f744c;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .file-link:hover {
        color: #059669;
        text-decoration: underline;
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

    .form-check-label {
        font-size: 0.95rem;
        cursor: pointer;
        color: #07130d;
        font-weight: 500;
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
        .edit-project-page {
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
        .form-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    /* Dark Mode Support */
    html[data-pms-theme="dark"] .edit-project-page {
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

    html[data-pms-theme="dark"] .form-control[disabled] {
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

    html[data-pms-theme="dark"] .current-file {
        background: #142a20;
    }

    html[data-pms-theme="dark"] .status-badge {
        background: rgba(245, 158, 11, .2);
        color: #fbbf24;
    }

    html[data-pms-theme="dark"] .file-link {
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .file-link:hover {
        color: #34d399;
    }
</style>

@push('js')
<script>
    $(document).ready(function () {
        // Employee multi-select
        $('#employee_ids').select2({
            placeholder: "Select Employees",
            allowClear: true,
            width: '100%'
        });

        // Ensure preselected values applied after select2 init
        const preselected = @json(old('employee_ids', $project->users->pluck('id')->toArray()));
        if (preselected && preselected.length) {
            $('#employee_ids').val(preselected).trigger('change');
        }

        // Client single-select
        $('#client_id').select2({
            placeholder: "Select Client",
            allowClear: true,
            width: '100%',
            tags: false
        });

        // Category and currency single selects
        $('#project_category_id, #currency').select2({
            placeholder: "Select",
            allowClear: true,
            width: '100%',
            tags: false
        });

        // Deadline toggle
        $('#without_deadline').on('change', function () {
            if ($(this).is(':checked')) {
                $('#deadline_input').prop('disabled', true).val('');
            } else {
                $('#deadline_input').prop('disabled', false);
            }
        });
    });

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
                    ).trigger('change');

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

    // Modal z-index stacking helper
    document.addEventListener("show.bs.modal", function (event) {
        const zIndex = 1050 + 10 * document.querySelectorAll('.modal.show').length;
        event.target.style.zIndex = zIndex;
        setTimeout(() => {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops[backdrops.length - 1].style.zIndex = zIndex - 1;
        });
    });
</script>
@endpush
@endsection
