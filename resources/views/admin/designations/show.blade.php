@extends('admin.layout.app')

@section('title', 'View Designation')

@section('content')

<div class="designation-view-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-user-tie"></i> Dashboard / Designations / View
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div>
                <h1>View Designation</h1>
                <p>Review designation details and hierarchy information</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('designations.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('designations.edit', $designation->id) }}" class="btn btn-primary">
                <i class="fas fa-pen"></i> Edit Designation
            </a>
        </div>
    </div>

    <!-- Main View Card -->
    <div class="view-card">
        <!-- Status Badge -->
        <div class="view-status-bar">
            <div class="status-info">
                <i class="fas fa-info-circle"></i>
                <span>Designation Details</span>
            </div>
            <span class="status-badge">
                <i class="fas fa-check-circle"></i>
                Active
            </span>
        </div>

        <!-- View Fields -->
        <div class="view-fields">
            <!-- Unique Code -->
            <div class="view-field">
                <div class="field-icon">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div class="field-content">
                    <label>Unique Code</label>
                    <div class="field-value">{{ $designation->unique_code ?? 'N/A' }}</div>
                    <span class="field-hint">Unique identifier for this designation</span>
                </div>
            </div>

            <!-- Designation Name -->
            <div class="view-field">
                <div class="field-icon">
                    <i class="fas fa-user-tag"></i>
                </div>
                <div class="field-content">
                    <label>Designation Name</label>
                    <div class="field-value">{{ $designation->name }}</div>
                    <span class="field-hint">Official designation title</span>
                </div>
            </div>

            <!-- Parent Designation -->
            <div class="view-field">
                <div class="field-icon">
                    <i class="fas fa-sitemap"></i>
                </div>
                <div class="field-content">
                    <label>Parent Designation</label>
                    <div class="field-value">
                        @if($designation->parent)
                            <span class="parent-link">
                                <i class="fas fa-link"></i>
                                {{ $designation->parent->name }}
                            </span>
                        @else
                            <span class="no-parent">
                                <i class="fas fa-crown"></i>
                                Top-level designation
                            </span>
                        @endif
                    </div>
                    <span class="field-hint">Reporting hierarchy level</span>
                </div>
            </div>

            <!-- Level -->
            <div class="view-field">
                <div class="field-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="field-content">
                    <label>Level</label>
                    <div class="field-value">
                        @if($designation->level !== null)
                            <span class="level-badge level-{{ $designation->level }}">
                                <i class="fas fa-chevron-up"></i>
                                Level {{ $designation->level }}
                            </span>
                        @else
                            <span class="level-badge level-default">
                                <i class="fas fa-minus"></i>
                                Not Set
                            </span>
                        @endif
                    </div>
                    <span class="field-hint">Organizational level</span>
                </div>
            </div>

            <!-- Added By -->
            <div class="view-field">
                <div class="field-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="field-content">
                    <label>Added By</label>
                    <div class="field-value">
                        <div class="user-info">
                            <div class="avatar-circle">
                                <span>{{ substr($designation->addedBy?->name ?? 'S', 0, 1) }}</span>
                            </div>
                            <div>
                                <div class="user-name">{{ $designation->addedBy?->name ?? 'System' }}</div>
                                <small class="text-muted">{{ $designation->created_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                    </div>
                    <span class="field-hint">Creator of this designation</span>
                </div>
            </div>

            <!-- Updated By -->
            <div class="view-field">
                <div class="field-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div class="field-content">
                    <label>Last Updated By</label>
                    <div class="field-value">
                        <div class="user-info">
                            <div class="avatar-circle">
                                <span>{{ substr($designation->updatedBy?->name ?? 'S', 0, 1) }}</span>
                            </div>
                            <div>
                                <div class="user-name">{{ $designation->updatedBy?->name ?? 'System' }}</div>
                                <small class="text-muted">{{ $designation->updated_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                    </div>
                    <span class="field-hint">Last person to modify this designation</span>
                </div>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="view-footer">
            <div class="footer-info">
                <i class="fas fa-clock"></i>
                <span>Created: {{ $designation->created_at->format('d M Y') }}</span>
                <span class="separator">|</span>
                <i class="fas fa-sync"></i>
                <span>Updated: {{ $designation->updated_at->format('d M Y') }}</span>
            </div>
            <div class="footer-actions">
                <a href="{{ route('designations.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="{{ route('designations.edit', $designation->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Designation
                </a>
            </div>
        </div>
    </div>

    <!-- Related Information Card -->
    <div class="related-card">
        <div class="related-header">
            <i class="fas fa-project-diagram"></i>
            <h5>Hierarchy Information</h5>
        </div>
        <div class="related-content">
            <div class="related-item">
                <span class="related-label">Level:</span>
                <span class="related-value">
                    @if($designation->level !== null)
                        <span class="level-badge level-{{ $designation->level }}">
                            L{{ $designation->level }}
                        </span>
                    @else
                        <span class="level-badge level-default">Not Set</span>
                    @endif
                </span>
            </div>
            <div class="related-item">
                <span class="related-label">Parent:</span>
                <span class="related-value">
                    @if($designation->parent)
                        <a href="{{ route('designations.show', $designation->parent->id) }}" class="parent-link">
                            <i class="fas fa-link"></i> {{ $designation->parent->name }}
                        </a>
                    @else
                        <span class="no-parent"><i class="fas fa-crown"></i> Top-level</span>
                    @endif
                </span>
            </div>
            <div class="related-item">
                <span class="related-label">Children:</span>
                <span class="related-value">
                    {{ $designation->children->count() ?? 0 }} sub-designation(s)
                </span>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== PREMIUM VIEW PAGE STYLES ===== */
    .designation-view-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f0f9f4 0%, #e6f3ec 50%, #f4fbf7 100%);
        color: #0a2e1f;
        position: relative;
    }

    .designation-view-page::before {
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
    }

    .header-card:hover {
        box-shadow: 0 24px 48px -16px rgba(16, 185, 129, 0.18);
        border-color: rgba(16, 185, 129, 0.2);
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

    /* View Card */
    .view-card {
        background: white;
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .view-card:hover {
        box-shadow: 0 12px 40px rgba(16, 185, 129, 0.08);
    }

    .view-status-bar {
        padding: 18px 28px;
        background: linear-gradient(135deg, #fafefb, #f0f9f4);
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .status-info {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #0a2e1f;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .status-info i {
        color: #34d399;
        font-size: 1.1rem;
    }

    .status-badge {
        padding: 8px 20px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        border-radius: 40px;
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .status-badge i {
        font-size: 0.9rem;
    }

    /* View Fields */
    .view-fields {
        padding: 28px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .view-field {
        padding: 22px;
        border: 1px solid rgba(16, 185, 129, 0.08);
        border-radius: 20px;
        display: flex;
        gap: 18px;
        align-items: flex-start;
        transition: all 0.3s ease;
        background: #fafefb;
    }

    .view-field:hover {
        border-color: rgba(16, 185, 129, 0.2);
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.06);
        transform: translateY(-2px);
    }

    .view-field:nth-child(1) .field-icon { background: linear-gradient(145deg, #d1fae5, #a7f3d0); color: #059669; }
    .view-field:nth-child(2) .field-icon { background: linear-gradient(145deg, #dbeafe, #bfdbfe); color: #2563eb; }
    .view-field:nth-child(3) .field-icon { background: linear-gradient(145deg, #fef3c7, #fde68a); color: #d97706; }
    .view-field:nth-child(4) .field-icon { background: linear-gradient(145deg, #e0e7ff, #c7d2fe); color: #4f46e5; }
    .view-field:nth-child(5) .field-icon { background: linear-gradient(145deg, #fce7f3, #fbcfe8); color: #db2777; }
    .view-field:nth-child(6) .field-icon { background: linear-gradient(145deg, #cffafe, #a5f3fc); color: #0891b2; }

    .field-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .view-field:hover .field-icon {
        transform: scale(1.05);
    }

    .field-content {
        flex: 1;
        min-width: 0;
    }

    .field-content label {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #8ba198;
        margin-bottom: 6px;
    }

    .field-value {
        font-size: 1.05rem;
        font-weight: 600;
        color: #0a2e1f;
        word-break: break-word;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .field-hint {
        display: block;
        font-size: 0.7rem;
        color: #9ca3af;
        margin-top: 4px;
    }

    .parent-link {
        color: #059669;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .parent-link:hover {
        color: #0f744c;
        text-decoration: underline;
    }

    .no-parent {
        color: #f59e0b;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }

    .level-badge {
        padding: 6px 16px;
        border-radius: 40px;
        color: white;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .level-0 { background: linear-gradient(145deg, #1f2937, #111827); }
    .level-1 { background: linear-gradient(145deg, #0f744c, #0a5a3a); }
    .level-2 { background: linear-gradient(145deg, #10b981, #059669); }
    .level-3 { background: linear-gradient(145deg, #3b82f6, #2563eb); }
    .level-4 { background: linear-gradient(145deg, #f59e0b, #d97706); }
    .level-5 { background: linear-gradient(145deg, #f97316, #ea580c); }
    .level-6 { background: linear-gradient(145deg, #ef4444, #dc2626); }
    .level-default { background: linear-gradient(145deg, #e5e7eb, #d1d5db); color: #6b7280; }

    .level-badge i {
        font-size: 0.7rem;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #059669;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .user-name {
        font-weight: 600;
        color: #0a2e1f;
    }

    .text-muted {
        color: #8ba198;
        font-size: 0.8rem;
    }

    /* View Footer */
    .view-footer {
        padding: 20px 28px;
        background: #fafefb;
        border-top: 1px solid rgba(16, 185, 129, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .footer-info {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #6b7280;
        font-size: 0.85rem;
        font-weight: 500;
        flex-wrap: wrap;
    }

    .footer-info i {
        color: #34d399;
    }

    .separator {
        color: #e5e7eb;
    }

    .footer-actions {
        display: flex;
        gap: 12px;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
        padding: 10px 20px;
        min-height: 44px;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .btn-primary {
        padding: 10px 24px;
        min-height: 44px;
    }

    /* Related Card */
    .related-card {
        margin-top: 28px;
        background: white;
        border: 1px solid rgba(16, 185, 129, 0.1);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .related-card:hover {
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.08);
    }

    .related-header {
        padding: 16px 28px;
        background: linear-gradient(135deg, #fafefb, #f0f9f4);
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .related-header i {
        color: #34d399;
        font-size: 1.1rem;
    }

    .related-header h5 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #0a2e1f;
    }

    .related-content {
        padding: 20px 28px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .related-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .related-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #8ba198;
    }

    .related-value {
        font-size: 1rem;
        font-weight: 600;
        color: #0a2e1f;
    }

    .related-value .parent-link {
        font-size: 1rem;
    }

    .related-value .no-parent {
        font-size: 1rem;
    }

    .related-value .level-badge {
        font-size: 0.85rem;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .view-fields {
            grid-template-columns: 1fr 1fr;
        }

        .related-content {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 992px) {
        .designation-view-page {
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

        .view-fields {
            grid-template-columns: 1fr;
        }

        .related-content {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .designation-view-page {
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

        .view-fields {
            padding: 16px;
        }

        .view-field {
            padding: 16px;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .field-icon {
            width: 44px;
            height: 44px;
            font-size: 1rem;
        }

        .view-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .footer-info {
            justify-content: center;
            font-size: 0.8rem;
        }

        .footer-actions {
            flex-direction: column;
        }

        .footer-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .related-content {
            grid-template-columns: 1fr;
            padding: 16px;
        }

        .related-header {
            padding: 14px 20px;
        }

        .view-status-bar {
            flex-direction: column;
            align-items: flex-start;
            padding: 14px 20px;
        }

        .view-fields {
            gap: 12px;
        }
    }

    @media (max-width: 576px) {
        .designation-view-page {
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

        .view-card {
            border-radius: 20px;
        }

        .view-field {
            padding: 14px;
        }

        .field-value {
            font-size: 0.95rem;
        }

        .btn {
            font-size: 0.85rem;
            padding: 10px 16px;
            min-height: 40px;
        }

        .view-status-bar {
            padding: 12px 16px;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 6px 14px;
        }
    }
</style>

<style>
    /* Larger typography for better readability */
    .designation-view-page {
        font-size: 16px;
        line-height: 1.55;
    }

    .designation-view-page .breadcrumb {
        font-size: 1rem;
    }

    .designation-view-page .header-card h1 {
        font-size: 36px;
        line-height: 1.2;
    }

    .designation-view-page .header-card p {
        font-size: 17px;
        line-height: 1.55;
    }

    .designation-view-page .btn {
        font-size: 1rem;
        padding: 13px 24px;
    }

    .designation-view-page .field-content label {
        font-size: 0.75rem;
    }

    .designation-view-page .field-value {
        font-size: 1.1rem;
    }

    .designation-view-page .field-hint {
        font-size: 0.75rem;
    }

    .designation-view-page .footer-info {
        font-size: 0.9rem;
    }

    .designation-view-page .related-label {
        font-size: 0.75rem;
    }

    .designation-view-page .related-value {
        font-size: 1.05rem;
    }

    @media (max-width: 768px) {
        .designation-view-page {
            font-size: 15px;
        }

        .designation-view-page .header-card h1 {
            font-size: 28px;
        }

        .designation-view-page .header-card p {
            font-size: 15px;
        }
    }
</style>

<style>
    /* Dark mode support */
    html[data-pms-theme="dark"] .designation-view-page {
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

    html[data-pms-theme="dark"] .view-card {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .view-status-bar {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .status-info {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .status-badge {
        background: #183026;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .view-field {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .view-field:hover {
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .field-content label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .field-value {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .field-hint {
        color: #6b7280;
    }

    html[data-pms-theme="dark"] .user-name {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .text-muted {
        color: #6b7280;
    }

    html[data-pms-theme="dark"] .view-footer {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .footer-info {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .btn-secondary {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-secondary:hover {
        background: #1f3d30;
    }

    html[data-pms-theme="dark"] .related-card {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .related-header {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .related-header h5 {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .related-label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .related-value {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .parent-link {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .parent-link:hover {
        color: #6ee7b7;
    }

    html[data-pms-theme="dark"] .no-parent {
        color: #f59e0b;
    }

    html[data-pms-theme="dark"] .separator {
        color: #374151;
    }

    html[data-pms-theme="dark"] .avatar-circle {
        background: #183026;
        color: #34d399;
    }
</style>

@endsection
