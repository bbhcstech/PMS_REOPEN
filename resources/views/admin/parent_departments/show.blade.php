@extends('admin.layout.app')

@section('title', 'View Parent Department')

@section('content')
<div class="department-view-page">
    <div class="breadcrumb">
        <i class="fas fa-building"></i> Dashboard / Parent Departments / View
    </div>

    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div>
                <h1>View Parent Department</h1>
                <p>Review parent department details and linked structure information</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('parent-departments.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('parent-departments.edit', $parentDepartment->id) }}" class="btn btn-primary">
                <i class="fas fa-pen"></i> Edit Department
            </a>
        </div>
    </div>

    <div class="view-card">
        <div class="view-status-bar">
            <div class="status-info">
                <i class="fas fa-info-circle"></i>
                <span>Parent Department Details</span>
            </div>
            @if($parentDepartment->archived_at)
                <span class="status-badge archived"><i class="fas fa-box-archive"></i> Archived</span>
            @else
                <span class="status-badge"><i class="fas fa-check-circle"></i> Active</span>
            @endif
        </div>

        <div class="view-fields">
            <div class="view-field">
                <div class="field-icon"><i class="fas fa-qrcode"></i></div>
                <div class="field-content">
                    <label>Department Code</label>
                    <div class="field-value">{{ $parentDepartment->dpt_code ?? 'N/A' }}</div>
                    <span class="field-hint">Unique identifier for this parent department</span>
                </div>
            </div>

            <div class="view-field">
                <div class="field-icon"><i class="fas fa-building"></i></div>
                <div class="field-content">
                    <label>Department Name</label>
                    <div class="field-value">{{ $parentDepartment->dpt_name }}</div>
                    <span class="field-hint">Official parent department name</span>
                </div>
            </div>

            <div class="view-field">
                <div class="field-icon"><i class="fas fa-sitemap"></i></div>
                <div class="field-content">
                    <label>Sub Departments</label>
                    <div class="field-value">
                        <span class="count-badge"><i class="fas fa-layer-group"></i> {{ $parentDepartment->departments_count ?? 0 }}</span>
                    </div>
                    <span class="field-hint">Sub departments linked to this parent</span>
                </div>
            </div>

            <div class="view-field">
                <div class="field-icon"><i class="fas fa-users"></i></div>
                <div class="field-content">
                    <label>Employees</label>
                    <div class="field-value">
                        <span class="count-badge"><i class="fas fa-user-friends"></i> {{ $parentDepartment->employees_count ?? 0 }}</span>
                    </div>
                    <span class="field-hint">Employees tagged under this parent department</span>
                </div>
            </div>

            <div class="view-field">
                <div class="field-icon"><i class="fas fa-user-plus"></i></div>
                <div class="field-content">
                    <label>Added By</label>
                    <div class="field-value">
                        <div class="user-info">
                            <div class="avatar-circle">{{ substr($parentDepartment->addedBy?->name ?? 'S', 0, 1) }}</div>
                            <div>
                                <div class="user-name">{{ $parentDepartment->addedBy?->name ?? 'System' }}</div>
                                <small class="text-muted">{{ $parentDepartment->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                    <span class="field-hint">Creator of this parent department</span>
                </div>
            </div>

            <div class="view-field">
                <div class="field-icon"><i class="fas fa-user-edit"></i></div>
                <div class="field-content">
                    <label>Last Updated By</label>
                    <div class="field-value">
                        <div class="user-info">
                            <div class="avatar-circle">{{ substr($parentDepartment->updatedBy?->name ?? 'S', 0, 1) }}</div>
                            <div>
                                <div class="user-name">{{ $parentDepartment->updatedBy?->name ?? 'System' }}</div>
                                <small class="text-muted">{{ $parentDepartment->updated_at?->format('d M Y, h:i A') ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                    <span class="field-hint">Last person to modify this parent department</span>
                </div>
            </div>
        </div>

        <div class="view-footer">
            <div class="footer-info">
                <i class="fas fa-clock"></i>
                <span>Created: {{ $parentDepartment->created_at?->format('d M Y') ?? 'N/A' }}</span>
                <span class="separator">|</span>
                <i class="fas fa-sync"></i>
                <span>Updated: {{ $parentDepartment->updated_at?->format('d M Y') ?? 'N/A' }}</span>
            </div>
            <div class="footer-actions">
                <a href="{{ route('parent-departments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="{{ route('parent-departments.edit', $parentDepartment->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Department
                </a>
            </div>
        </div>
    </div>

    <div class="related-card">
        <div class="related-header">
            <i class="fas fa-project-diagram"></i>
            <h5>Structure Information</h5>
        </div>
        <div class="related-content">
            <div class="related-item">
                <span class="related-label">Code</span>
                <span class="related-value">{{ $parentDepartment->dpt_code ?? 'N/A' }}</span>
            </div>
            <div class="related-item">
                <span class="related-label">Sub Departments</span>
                <span class="related-value">{{ $parentDepartment->departments_count ?? 0 }} linked</span>
            </div>
            <div class="related-item">
                <span class="related-label">Employees</span>
                <span class="related-value">{{ $parentDepartment->employees_count ?? 0 }} assigned</span>
            </div>
        </div>
    </div>
</div>

@include('admin.parent_departments.partials.show-styles')
@endsection
