@extends('admin.layout.app')

@section('title', 'View Sub Department')

@section('content')
<div class="department-view-page">
    <div class="breadcrumb">
        <i class="fas fa-sitemap"></i> Dashboard / Sub Departments / View
    </div>

    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div>
                <h1>View Sub Department</h1>
                <p>Review sub department details and parent department information</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('departments.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-primary">
                <i class="fas fa-pen"></i> Edit Sub Department
            </a>
        </div>
    </div>

    <div class="view-card">
        <div class="view-status-bar">
            <div class="status-info">
                <i class="fas fa-info-circle"></i>
                <span>Sub Department Details</span>
            </div>
            <span class="status-badge"><i class="fas fa-check-circle"></i> Active</span>
        </div>

        <div class="view-fields">
            <div class="view-field">
                <div class="field-icon"><i class="fas fa-qrcode"></i></div>
                <div class="field-content">
                    <label>Sub Department Code</label>
                    <div class="field-value">{{ $department->dpt_code ?? 'N/A' }}</div>
                    <span class="field-hint">Unique identifier for this sub department</span>
                </div>
            </div>

            <div class="view-field">
                <div class="field-icon"><i class="fas fa-users"></i></div>
                <div class="field-content">
                    <label>Sub Department Name</label>
                    <div class="field-value">{{ $department->dpt_name }}</div>
                    <span class="field-hint">Official sub department name</span>
                </div>
            </div>

            <div class="view-field">
                <div class="field-icon"><i class="fas fa-building"></i></div>
                <div class="field-content">
                    <label>Parent Department</label>
                    <div class="field-value">
                        @if($department->parent)
                            <a href="{{ route('parent-departments.show', $department->parent->id) }}" class="parent-link">
                                <i class="fas fa-link"></i> {{ $department->parent->dpt_name }}
                            </a>
                        @else
                            <span class="no-parent"><i class="fas fa-exclamation-circle"></i> No parent assigned</span>
                        @endif
                    </div>
                    <span class="field-hint">Parent department for this sub department</span>
                </div>
            </div>

            <div class="view-field">
                <div class="field-icon"><i class="fas fa-user-friends"></i></div>
                <div class="field-content">
                    <label>Employees</label>
                    <div class="field-value">
                        <span class="count-badge"><i class="fas fa-users"></i> {{ $department->employee_details_count ?? 0 }}</span>
                    </div>
                    <span class="field-hint">Employees tagged under this sub department</span>
                </div>
            </div>

            <div class="view-field">
                <div class="field-icon"><i class="fas fa-user-plus"></i></div>
                <div class="field-content">
                    <label>Added By</label>
                    <div class="field-value">
                        <div class="user-info">
                            <div class="avatar-circle">{{ substr($department->addedBy?->name ?? 'S', 0, 1) }}</div>
                            <div>
                                <div class="user-name">{{ $department->addedBy?->name ?? 'System' }}</div>
                                <small class="text-muted">{{ $department->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                    <span class="field-hint">Creator of this sub department</span>
                </div>
            </div>

            <div class="view-field">
                <div class="field-icon"><i class="fas fa-user-edit"></i></div>
                <div class="field-content">
                    <label>Last Updated By</label>
                    <div class="field-value">
                        <div class="user-info">
                            <div class="avatar-circle">{{ substr($department->updatedBy?->name ?? 'S', 0, 1) }}</div>
                            <div>
                                <div class="user-name">{{ $department->updatedBy?->name ?? 'System' }}</div>
                                <small class="text-muted">{{ $department->updated_at?->format('d M Y, h:i A') ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                    <span class="field-hint">Last person to modify this sub department</span>
                </div>
            </div>
        </div>

        <div class="view-footer">
            <div class="footer-info">
                <i class="fas fa-clock"></i>
                <span>Created: {{ $department->created_at?->format('d M Y') ?? 'N/A' }}</span>
                <span class="separator">|</span>
                <i class="fas fa-sync"></i>
                <span>Updated: {{ $department->updated_at?->format('d M Y') ?? 'N/A' }}</span>
            </div>
            <div class="footer-actions">
                <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Sub Department
                </a>
            </div>
        </div>
    </div>

    <div class="related-card">
        <div class="related-header">
            <i class="fas fa-project-diagram"></i>
            <h5>Hierarchy Information</h5>
        </div>
        <div class="related-content">
            <div class="related-item">
                <span class="related-label">Parent</span>
                <span class="related-value">{{ $department->parent?->dpt_name ?? 'N/A' }}</span>
            </div>
            <div class="related-item">
                <span class="related-label">Parent Code</span>
                <span class="related-value">{{ $department->parent?->dpt_code ?? 'N/A' }}</span>
            </div>
            <div class="related-item">
                <span class="related-label">Employees</span>
                <span class="related-value">{{ $department->employee_details_count ?? 0 }} assigned</span>
            </div>
        </div>
    </div>
</div>

@include('admin.parent_departments.partials.show-styles')
@endsection
