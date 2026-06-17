<style>
    .department-view-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f0f9f4 0%, #e6f3ec 50%, #f4fbf7 100%);
        color: #0a2e1f;
        position: relative;
        font-size: 16px;
        line-height: 1.55;
    }

    .department-view-page::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.03) 0%, transparent 50%),
                    radial-gradient(circle at 100% 100%, rgba(52, 211, 153, 0.03) 0%, transparent 50%);
        pointer-events: none;
    }

    .department-view-page .breadcrumb,
    .department-view-page .header-card,
    .department-view-page .view-card,
    .department-view-page .related-card {
        position: relative;
        z-index: 1;
    }

    .department-view-page .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 14px 24px;
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        margin-bottom: 28px;
        color: #0f744c;
        font-weight: 600;
        font-size: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .department-view-page .breadcrumb i {
        margin-right: 8px;
        color: #34d399;
    }

    .department-view-page .header-card {
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
    }

    .department-view-page .header-card:hover {
        box-shadow: 0 24px 48px -16px rgba(16, 185, 129, 0.18);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .department-view-page .header-left {
        display: flex;
        align-items: center;
        gap: 22px;
    }

    .department-view-page .header-icon {
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
        flex-shrink: 0;
    }

    .department-view-page .header-card:hover .header-icon {
        transform: scale(1.02);
    }

    .department-view-page .header-card h1 {
        font-size: 36px;
        line-height: 1.2;
        font-weight: 700;
        margin-bottom: 6px;
        background: linear-gradient(135deg, #0a2e1f, #0f744c);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .department-view-page .header-card p {
        color: #5a6e63;
        font-size: 17px;
        line-height: 1.55;
        font-weight: 500;
        margin: 0;
    }

    .department-view-page .btn-group {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

    .department-view-page .btn {
        border: none;
        padding: 13px 24px;
        border-radius: 16px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        font-size: 1rem;
        min-height: 48px;
    }

    .department-view-page .btn-light {
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .department-view-page .btn-light:hover {
        color: #0f744c;
        background: #e6f3ec;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(16, 185, 129, 0.25);
        border-color: #34d399;
    }

    .department-view-page .btn-primary {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        box-shadow: 0 8px 20px -6px rgba(5, 150, 105, 0.35);
    }

    .department-view-page .btn-primary:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(5, 150, 105, 0.45);
    }

    .department-view-page .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
        padding: 10px 20px;
        min-height: 44px;
    }

    .department-view-page .btn-secondary:hover {
        color: #374151;
        background: #e5e7eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .department-view-page .view-card {
        background: white;
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    .department-view-page .view-card:hover {
        box-shadow: 0 12px 40px rgba(16, 185, 129, 0.08);
    }

    .department-view-page .view-status-bar {
        padding: 18px 28px;
        background: linear-gradient(135deg, #fafefb, #f0f9f4);
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .department-view-page .status-info {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #0a2e1f;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .department-view-page .status-info i {
        color: #34d399;
        font-size: 1.1rem;
    }

    .department-view-page .status-badge {
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

    .department-view-page .status-badge.archived {
        background: linear-gradient(145deg, #fef3c7, #fde68a);
        color: #b45309;
    }

    .department-view-page .view-fields {
        padding: 28px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .department-view-page .view-field {
        padding: 22px;
        border: 1px solid rgba(16, 185, 129, 0.08);
        border-radius: 20px;
        display: flex;
        gap: 18px;
        align-items: flex-start;
        transition: all 0.3s ease;
        background: #fafefb;
    }

    .department-view-page .view-field:hover {
        border-color: rgba(16, 185, 129, 0.2);
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.06);
        transform: translateY(-2px);
    }

    .department-view-page .view-field:nth-child(1) .field-icon { background: linear-gradient(145deg, #d1fae5, #a7f3d0); color: #059669; }
    .department-view-page .view-field:nth-child(2) .field-icon { background: linear-gradient(145deg, #dbeafe, #bfdbfe); color: #2563eb; }
    .department-view-page .view-field:nth-child(3) .field-icon { background: linear-gradient(145deg, #fef3c7, #fde68a); color: #d97706; }
    .department-view-page .view-field:nth-child(4) .field-icon { background: linear-gradient(145deg, #e0e7ff, #c7d2fe); color: #4f46e5; }
    .department-view-page .view-field:nth-child(5) .field-icon { background: linear-gradient(145deg, #fce7f3, #fbcfe8); color: #db2777; }
    .department-view-page .view-field:nth-child(6) .field-icon { background: linear-gradient(145deg, #cffafe, #a5f3fc); color: #0891b2; }

    .department-view-page .field-icon {
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

    .department-view-page .view-field:hover .field-icon {
        transform: scale(1.05);
    }

    .department-view-page .field-content {
        flex: 1;
        min-width: 0;
    }

    .department-view-page .field-content label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #8ba198;
        margin-bottom: 6px;
    }

    .department-view-page .field-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #0a2e1f;
        word-break: break-word;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .department-view-page .field-hint {
        display: block;
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 4px;
    }

    .department-view-page .parent-link {
        color: #059669;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .department-view-page .parent-link:hover {
        color: #0f744c;
        text-decoration: underline;
    }

    .department-view-page .no-parent {
        color: #f59e0b;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }

    .department-view-page .count-badge {
        padding: 7px 16px;
        border-radius: 40px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        font-weight: 800;
    }

    .department-view-page .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .department-view-page .avatar-circle {
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

    .department-view-page .user-name {
        font-weight: 600;
        color: #0a2e1f;
    }

    .department-view-page .text-muted {
        color: #8ba198;
        font-size: 0.8rem;
    }

    .department-view-page .view-footer {
        padding: 20px 28px;
        background: #fafefb;
        border-top: 1px solid rgba(16, 185, 129, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .department-view-page .footer-info {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 500;
        flex-wrap: wrap;
    }

    .department-view-page .footer-info i {
        color: #34d399;
    }

    .department-view-page .separator {
        color: #e5e7eb;
    }

    .department-view-page .footer-actions {
        display: flex;
        gap: 12px;
    }

    .department-view-page .related-card {
        margin-top: 28px;
        background: white;
        border: 1px solid rgba(16, 185, 129, 0.1);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
    }

    .department-view-page .related-card:hover {
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.08);
    }

    .department-view-page .related-header {
        padding: 16px 28px;
        background: linear-gradient(135deg, #fafefb, #f0f9f4);
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .department-view-page .related-header i {
        color: #34d399;
        font-size: 1.1rem;
    }

    .department-view-page .related-header h5 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #0a2e1f;
    }

    .department-view-page .related-content {
        padding: 20px 28px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .department-view-page .related-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .department-view-page .related-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #8ba198;
    }

    .department-view-page .related-value {
        font-size: 1.05rem;
        font-weight: 600;
        color: #0a2e1f;
    }

    @media (max-width: 992px) {
        .department-view-page {
            padding: 20px 25px;
        }

        .department-view-page .header-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .department-view-page .btn-group {
            width: 100%;
            justify-content: flex-start;
        }

        .department-view-page .view-fields {
            grid-template-columns: 1fr;
        }

        .department-view-page .related-content {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .department-view-page {
            padding: 16px;
            font-size: 15px;
        }

        .department-view-page .header-card {
            padding: 20px;
        }

        .department-view-page .header-icon {
            width: 56px;
            height: 56px;
            font-size: 24px;
        }

        .department-view-page .header-card h1 {
            font-size: 28px;
        }

        .department-view-page .header-card p {
            font-size: 15px;
        }

        .department-view-page .view-fields {
            padding: 16px;
            gap: 12px;
        }

        .department-view-page .view-field {
            padding: 16px;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .department-view-page .field-icon {
            width: 44px;
            height: 44px;
            font-size: 1rem;
        }

        .department-view-page .view-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .department-view-page .footer-info {
            justify-content: center;
            font-size: 0.8rem;
        }

        .department-view-page .footer-actions {
            flex-direction: column;
        }

        .department-view-page .footer-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .department-view-page .related-content {
            grid-template-columns: 1fr;
            padding: 16px;
        }

        .department-view-page .related-header {
            padding: 14px 20px;
        }

        .department-view-page .view-status-bar {
            flex-direction: column;
            align-items: flex-start;
            padding: 14px 20px;
        }
    }

    @media (max-width: 576px) {
        .department-view-page {
            padding: 12px;
        }

        .department-view-page .header-card {
            padding: 16px;
            border-radius: 20px;
        }

        .department-view-page .header-left {
            gap: 14px;
        }

        .department-view-page .header-icon {
            width: 48px;
            height: 48px;
            font-size: 20px;
            border-radius: 18px;
        }

        .department-view-page .header-card h1 {
            font-size: 20px;
        }

        .department-view-page .header-card p {
            font-size: 13px;
        }

        .department-view-page .view-card {
            border-radius: 20px;
        }

        .department-view-page .view-field {
            padding: 14px;
        }

        .department-view-page .field-value {
            font-size: 0.95rem;
        }

        .department-view-page .btn {
            font-size: 0.85rem;
            padding: 10px 16px;
            min-height: 40px;
        }

        .department-view-page .view-status-bar {
            padding: 12px 16px;
        }

        .department-view-page .status-badge {
            font-size: 0.75rem;
            padding: 6px 14px;
        }
    }
</style>
