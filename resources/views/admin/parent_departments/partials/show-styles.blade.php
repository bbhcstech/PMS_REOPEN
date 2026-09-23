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

    /* ==========================================
       DARK MODE SUPPORT FOR VIEW / DETAILS PAGE
       ========================================== */
    html[data-pms-theme="dark"] .department-view-page,
    html[data-bs-theme="dark"] .department-view-page,
    html[data-theme="dark"] .department-view-page,
    html.dark .department-view-page,
    body[data-pms-theme="dark"] .department-view-page,
    body[data-bs-theme="dark"] .department-view-page,
    body[data-theme="dark"] .department-view-page,
    body.dark .department-view-page,
    body.dark-mode .department-view-page {
        background: linear-gradient(135deg, #07130d 0%, #102119 50%, #07130d 100%);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .department-view-page .breadcrumb,
    html[data-bs-theme="dark"] .department-view-page .breadcrumb,
    html[data-theme="dark"] .department-view-page .breadcrumb,
    html.dark .department-view-page .breadcrumb,
    body[data-pms-theme="dark"] .department-view-page .breadcrumb,
    body[data-bs-theme="dark"] .department-view-page .breadcrumb,
    body[data-theme="dark"] .department-view-page .breadcrumb,
    body.dark .department-view-page .breadcrumb,
    body.dark-mode .department-view-page .breadcrumb {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .department-view-page .breadcrumb i,
    html[data-bs-theme="dark"] .department-view-page .breadcrumb i,
    html[data-theme="dark"] .department-view-page .breadcrumb i,
    html.dark .department-view-page .breadcrumb i,
    body[data-pms-theme="dark"] .department-view-page .breadcrumb i,
    body[data-bs-theme="dark"] .department-view-page .breadcrumb i,
    body[data-theme="dark"] .department-view-page .breadcrumb i,
    body.dark .department-view-page .breadcrumb i,
    body.dark-mode .department-view-page .breadcrumb i {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .department-view-page .header-card,
    html[data-bs-theme="dark"] .department-view-page .header-card,
    html[data-theme="dark"] .department-view-page .header-card,
    html.dark .department-view-page .header-card,
    body[data-pms-theme="dark"] .department-view-page .header-card,
    body[data-bs-theme="dark"] .department-view-page .header-card,
    body[data-theme="dark"] .department-view-page .header-card,
    body.dark .department-view-page .header-card,
    body.dark-mode .department-view-page .header-card {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .department-view-page .header-card h1,
    html[data-bs-theme="dark"] .department-view-page .header-card h1,
    html[data-theme="dark"] .department-view-page .header-card h1,
    html.dark .department-view-page .header-card h1,
    body[data-pms-theme="dark"] .department-view-page .header-card h1,
    body[data-bs-theme="dark"] .department-view-page .header-card h1,
    body[data-theme="dark"] .department-view-page .header-card h1,
    body.dark .department-view-page .header-card h1,
    body.dark-mode .department-view-page .header-card h1 {
        background: linear-gradient(135deg, #d9f1e4, #34d399);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    html[data-pms-theme="dark"] .department-view-page .header-card p,
    html[data-bs-theme="dark"] .department-view-page .header-card p,
    html[data-theme="dark"] .department-view-page .header-card p,
    html.dark .department-view-page .header-card p,
    body[data-pms-theme="dark"] .department-view-page .header-card p,
    body[data-bs-theme="dark"] .department-view-page .header-card p,
    body[data-theme="dark"] .department-view-page .header-card p,
    body.dark .department-view-page .header-card p,
    body.dark-mode .department-view-page .header-card p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .department-view-page .btn-light,
    html[data-bs-theme="dark"] .department-view-page .btn-light,
    html[data-theme="dark"] .department-view-page .btn-light,
    html.dark .department-view-page .btn-light,
    body[data-pms-theme="dark"] .department-view-page .btn-light,
    body[data-bs-theme="dark"] .department-view-page .btn-light,
    body[data-theme="dark"] .department-view-page .btn-light,
    body.dark .department-view-page .btn-light,
    body.dark-mode .department-view-page .btn-light {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .department-view-page .btn-light:hover,
    html[data-bs-theme="dark"] .department-view-page .btn-light:hover,
    html[data-theme="dark"] .department-view-page .btn-light:hover,
    html.dark .department-view-page .btn-light:hover,
    body[data-pms-theme="dark"] .department-view-page .btn-light:hover,
    body[data-bs-theme="dark"] .department-view-page .btn-light:hover,
    body[data-theme="dark"] .department-view-page .btn-light:hover,
    body.dark .department-view-page .btn-light:hover,
    body.dark-mode .department-view-page .btn-light:hover {
        background: #1f3d30;
        border-color: #34d399;
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .department-view-page .btn-secondary,
    html[data-bs-theme="dark"] .department-view-page .btn-secondary,
    html[data-theme="dark"] .department-view-page .btn-secondary,
    html.dark .department-view-page .btn-secondary,
    body[data-pms-theme="dark"] .department-view-page .btn-secondary,
    body[data-bs-theme="dark"] .department-view-page .btn-secondary,
    body[data-theme="dark"] .department-view-page .btn-secondary,
    body.dark .department-view-page .btn-secondary,
    body.dark-mode .department-view-page .btn-secondary {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .department-view-page .btn-secondary:hover,
    html[data-bs-theme="dark"] .department-view-page .btn-secondary:hover,
    html[data-theme="dark"] .department-view-page .btn-secondary:hover,
    html.dark .department-view-page .btn-secondary:hover,
    body[data-pms-theme="dark"] .department-view-page .btn-secondary:hover,
    body[data-bs-theme="dark"] .department-view-page .btn-secondary:hover,
    body[data-theme="dark"] .department-view-page .btn-secondary:hover,
    body.dark .department-view-page .btn-secondary:hover,
    body.dark-mode .department-view-page .btn-secondary:hover {
        background: #1f3d30;
        color: #d9f1e4;
    }

    /* Main View Card & Fields in Dark Mode */
    html[data-pms-theme="dark"] .department-view-page .view-card,
    html[data-bs-theme="dark"] .department-view-page .view-card,
    html[data-theme="dark"] .department-view-page .view-card,
    html.dark .department-view-page .view-card,
    body[data-pms-theme="dark"] .department-view-page .view-card,
    body[data-bs-theme="dark"] .department-view-page .view-card,
    body[data-theme="dark"] .department-view-page .view-card,
    body.dark .department-view-page .view-card,
    body.dark-mode .department-view-page .view-card {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.1);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
    }

    html[data-pms-theme="dark"] .department-view-page .view-status-bar,
    html[data-bs-theme="dark"] .department-view-page .view-status-bar,
    html[data-theme="dark"] .department-view-page .view-status-bar,
    html.dark .department-view-page .view-status-bar,
    body[data-pms-theme="dark"] .department-view-page .view-status-bar,
    body[data-bs-theme="dark"] .department-view-page .view-status-bar,
    body[data-theme="dark"] .department-view-page .view-status-bar,
    body.dark .department-view-page .view-status-bar,
    body.dark-mode .department-view-page .view-status-bar {
        background: linear-gradient(135deg, #0d1b14, #12281e);
        border-bottom-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .department-view-page .status-info,
    html[data-bs-theme="dark"] .department-view-page .status-info,
    html[data-theme="dark"] .department-view-page .status-info,
    html.dark .department-view-page .status-info,
    body[data-pms-theme="dark"] .department-view-page .status-info,
    body[data-bs-theme="dark"] .department-view-page .status-info,
    body[data-theme="dark"] .department-view-page .status-info,
    body.dark .department-view-page .status-info,
    body.dark-mode .department-view-page .status-info {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .department-view-page .status-badge,
    html[data-bs-theme="dark"] .department-view-page .status-badge,
    html[data-theme="dark"] .department-view-page .status-badge,
    html.dark .department-view-page .status-badge,
    body[data-pms-theme="dark"] .department-view-page .status-badge,
    body[data-bs-theme="dark"] .department-view-page .status-badge,
    body[data-theme="dark"] .department-view-page .status-badge,
    body.dark .department-view-page .status-badge,
    body.dark-mode .department-view-page .status-badge {
        background: linear-gradient(145deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.4));
        color: #34d399;
        border: 1px solid rgba(52, 211, 153, 0.3);
    }

    html[data-pms-theme="dark"] .department-view-page .status-badge.archived,
    html[data-bs-theme="dark"] .department-view-page .status-badge.archived,
    html[data-theme="dark"] .department-view-page .status-badge.archived,
    html.dark .department-view-page .status-badge.archived,
    body[data-pms-theme="dark"] .department-view-page .status-badge.archived,
    body[data-bs-theme="dark"] .department-view-page .status-badge.archived,
    body[data-theme="dark"] .department-view-page .status-badge.archived,
    body.dark .department-view-page .status-badge.archived,
    body.dark-mode .department-view-page .status-badge.archived {
        background: linear-gradient(145deg, rgba(245, 158, 11, 0.25), rgba(217, 119, 6, 0.4));
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.3);
    }

    html[data-pms-theme="dark"] .department-view-page .view-field,
    html[data-bs-theme="dark"] .department-view-page .view-field,
    html[data-theme="dark"] .department-view-page .view-field,
    html.dark .department-view-page .view-field,
    body[data-pms-theme="dark"] .department-view-page .view-field,
    body[data-bs-theme="dark"] .department-view-page .view-field,
    body[data-theme="dark"] .department-view-page .view-field,
    body.dark .department-view-page .view-field,
    body.dark-mode .department-view-page .view-field {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .department-view-page .view-field:hover,
    html[data-bs-theme="dark"] .department-view-page .view-field:hover,
    html[data-theme="dark"] .department-view-page .view-field:hover,
    html.dark .department-view-page .view-field:hover,
    body[data-pms-theme="dark"] .department-view-page .view-field:hover,
    body[data-bs-theme="dark"] .department-view-page .view-field:hover,
    body[data-theme="dark"] .department-view-page .view-field:hover,
    body.dark .department-view-page .view-field:hover,
    body.dark-mode .department-view-page .view-field:hover {
        border-color: rgba(122, 240, 181, 0.2);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }

    html[data-pms-theme="dark"] .department-view-page .field-content label,
    html[data-bs-theme="dark"] .department-view-page .field-content label,
    html[data-theme="dark"] .department-view-page .field-content label,
    html.dark .department-view-page .field-content label,
    body[data-pms-theme="dark"] .department-view-page .field-content label,
    body[data-bs-theme="dark"] .department-view-page .field-content label,
    body[data-theme="dark"] .department-view-page .field-content label,
    body.dark .department-view-page .field-content label,
    body.dark-mode .department-view-page .field-content label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .department-view-page .field-value,
    html[data-bs-theme="dark"] .department-view-page .field-value,
    html[data-theme="dark"] .department-view-page .field-value,
    html.dark .department-view-page .field-value,
    body[data-pms-theme="dark"] .department-view-page .field-value,
    body[data-bs-theme="dark"] .department-view-page .field-value,
    body[data-theme="dark"] .department-view-page .field-value,
    body.dark .department-view-page .field-value,
    body.dark-mode .department-view-page .field-value {
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .department-view-page .user-name,
    html[data-bs-theme="dark"] .department-view-page .user-name,
    html[data-theme="dark"] .department-view-page .user-name,
    html.dark .department-view-page .user-name,
    body[data-pms-theme="dark"] .department-view-page .user-name,
    body[data-bs-theme="dark"] .department-view-page .user-name,
    body[data-theme="dark"] .department-view-page .user-name,
    body.dark .department-view-page .user-name,
    body.dark-mode .department-view-page .user-name {
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .department-view-page .text-muted,
    html[data-bs-theme="dark"] .department-view-page .text-muted,
    html[data-theme="dark"] .department-view-page .text-muted,
    html.dark .department-view-page .text-muted,
    body[data-pms-theme="dark"] .department-view-page .text-muted,
    body[data-bs-theme="dark"] .department-view-page .text-muted,
    body[data-theme="dark"] .department-view-page .text-muted,
    body.dark .department-view-page .text-muted,
    body.dark-mode .department-view-page .text-muted {
        color: #8ba198 !important;
    }

    html[data-pms-theme="dark"] .department-view-page .field-hint,
    html[data-bs-theme="dark"] .department-view-page .field-hint,
    html[data-theme="dark"] .department-view-page .field-hint,
    html.dark .department-view-page .field-hint,
    body[data-pms-theme="dark"] .department-view-page .field-hint,
    body[data-bs-theme="dark"] .department-view-page .field-hint,
    body[data-theme="dark"] .department-view-page .field-hint,
    body.dark .department-view-page .field-hint,
    body.dark-mode .department-view-page .field-hint {
        color: #6b7280;
    }

    html[data-pms-theme="dark"] .department-view-page .count-badge,
    html[data-bs-theme="dark"] .department-view-page .count-badge,
    html[data-theme="dark"] .department-view-page .count-badge,
    html.dark .department-view-page .count-badge,
    body[data-pms-theme="dark"] .department-view-page .count-badge,
    body[data-bs-theme="dark"] .department-view-page .count-badge,
    body[data-theme="dark"] .department-view-page .count-badge,
    body.dark .department-view-page .count-badge,
    body.dark-mode .department-view-page .count-badge {
        background: linear-gradient(145deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.4));
        color: #34d399;
        border: 1px solid rgba(52, 211, 153, 0.3);
    }

    html[data-pms-theme="dark"] .department-view-page .avatar-circle,
    html[data-bs-theme="dark"] .department-view-page .avatar-circle,
    html[data-theme="dark"] .department-view-page .avatar-circle,
    html.dark .department-view-page .avatar-circle,
    body[data-pms-theme="dark"] .department-view-page .avatar-circle,
    body[data-bs-theme="dark"] .department-view-page .avatar-circle,
    body[data-theme="dark"] .department-view-page .avatar-circle,
    body.dark .department-view-page .avatar-circle,
    body.dark-mode .department-view-page .avatar-circle {
        background: linear-gradient(145deg, rgba(16, 185, 129, 0.3), rgba(5, 150, 105, 0.5));
        color: #34d399;
        border: 1px solid rgba(52, 211, 153, 0.4);
    }

    html[data-pms-theme="dark"] .department-view-page .parent-link,
    html[data-bs-theme="dark"] .department-view-page .parent-link,
    html[data-theme="dark"] .department-view-page .parent-link,
    html.dark .department-view-page .parent-link,
    body[data-pms-theme="dark"] .department-view-page .parent-link,
    body[data-bs-theme="dark"] .department-view-page .parent-link,
    body[data-theme="dark"] .department-view-page .parent-link,
    body.dark .department-view-page .parent-link,
    body.dark-mode .department-view-page .parent-link {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .department-view-page .no-parent,
    html[data-bs-theme="dark"] .department-view-page .no-parent,
    html[data-theme="dark"] .department-view-page .no-parent,
    html.dark .department-view-page .no-parent,
    body[data-pms-theme="dark"] .department-view-page .no-parent,
    body[data-bs-theme="dark"] .department-view-page .no-parent,
    body[data-theme="dark"] .department-view-page .no-parent,
    body.dark .department-view-page .no-parent,
    body.dark-mode .department-view-page .no-parent {
        color: #fbbf24;
    }

    /* Field Icon Styling in Dark Mode */
    html[data-pms-theme="dark"] .department-view-page .view-field:nth-child(1) .field-icon,
    html[data-bs-theme="dark"] .department-view-page .view-field:nth-child(1) .field-icon,
    html[data-theme="dark"] .department-view-page .view-field:nth-child(1) .field-icon,
    html.dark .department-view-page .view-field:nth-child(1) .field-icon,
    body[data-pms-theme="dark"] .department-view-page .view-field:nth-child(1) .field-icon,
    body[data-bs-theme="dark"] .department-view-page .view-field:nth-child(1) .field-icon,
    body[data-theme="dark"] .department-view-page .view-field:nth-child(1) .field-icon,
    body.dark .department-view-page .view-field:nth-child(1) .field-icon,
    body.dark-mode .department-view-page .view-field:nth-child(1) .field-icon {
        background: linear-gradient(145deg, rgba(16, 185, 129, 0.3), rgba(5, 150, 105, 0.5)) !important;
        color: #34d399 !important;
        border: 1px solid rgba(52, 211, 153, 0.4) !important;
    }

    html[data-pms-theme="dark"] .department-view-page .view-field:nth-child(2) .field-icon,
    html[data-bs-theme="dark"] .department-view-page .view-field:nth-child(2) .field-icon,
    html[data-theme="dark"] .department-view-page .view-field:nth-child(2) .field-icon,
    html.dark .department-view-page .view-field:nth-child(2) .field-icon,
    body[data-pms-theme="dark"] .department-view-page .view-field:nth-child(2) .field-icon,
    body[data-bs-theme="dark"] .department-view-page .view-field:nth-child(2) .field-icon,
    body[data-theme="dark"] .department-view-page .view-field:nth-child(2) .field-icon,
    body.dark .department-view-page .view-field:nth-child(2) .field-icon,
    body.dark-mode .department-view-page .view-field:nth-child(2) .field-icon {
        background: linear-gradient(145deg, rgba(56, 189, 248, 0.3), rgba(2, 132, 199, 0.5)) !important;
        color: #38bdf8 !important;
        border: 1px solid rgba(56, 189, 248, 0.4) !important;
    }

    html[data-pms-theme="dark"] .department-view-page .view-field:nth-child(3) .field-icon,
    html[data-bs-theme="dark"] .department-view-page .view-field:nth-child(3) .field-icon,
    html[data-theme="dark"] .department-view-page .view-field:nth-child(3) .field-icon,
    html.dark .department-view-page .view-field:nth-child(3) .field-icon,
    body[data-pms-theme="dark"] .department-view-page .view-field:nth-child(3) .field-icon,
    body[data-bs-theme="dark"] .department-view-page .view-field:nth-child(3) .field-icon,
    body[data-theme="dark"] .department-view-page .view-field:nth-child(3) .field-icon,
    body.dark .department-view-page .view-field:nth-child(3) .field-icon,
    body.dark-mode .department-view-page .view-field:nth-child(3) .field-icon {
        background: linear-gradient(145deg, rgba(245, 158, 11, 0.3), rgba(217, 119, 6, 0.5)) !important;
        color: #fbbf24 !important;
        border: 1px solid rgba(251, 191, 36, 0.4) !important;
    }

    html[data-pms-theme="dark"] .department-view-page .view-field:nth-child(4) .field-icon,
    html[data-bs-theme="dark"] .department-view-page .view-field:nth-child(4) .field-icon,
    html[data-theme="dark"] .department-view-page .view-field:nth-child(4) .field-icon,
    html.dark .department-view-page .view-field:nth-child(4) .field-icon,
    body[data-pms-theme="dark"] .department-view-page .view-field:nth-child(4) .field-icon,
    body[data-bs-theme="dark"] .department-view-page .view-field:nth-child(4) .field-icon,
    body[data-theme="dark"] .department-view-page .view-field:nth-child(4) .field-icon,
    body.dark .department-view-page .view-field:nth-child(4) .field-icon,
    body.dark-mode .department-view-page .view-field:nth-child(4) .field-icon {
        background: linear-gradient(145deg, rgba(99, 102, 241, 0.3), rgba(79, 70, 229, 0.5)) !important;
        color: #818cf8 !important;
        border: 1px solid rgba(129, 140, 248, 0.4) !important;
    }

    html[data-pms-theme="dark"] .department-view-page .view-field:nth-child(5) .field-icon,
    html[data-bs-theme="dark"] .department-view-page .view-field:nth-child(5) .field-icon,
    html[data-theme="dark"] .department-view-page .view-field:nth-child(5) .field-icon,
    html.dark .department-view-page .view-field:nth-child(5) .field-icon,
    body[data-pms-theme="dark"] .department-view-page .view-field:nth-child(5) .field-icon,
    body[data-bs-theme="dark"] .department-view-page .view-field:nth-child(5) .field-icon,
    body[data-theme="dark"] .department-view-page .view-field:nth-child(5) .field-icon,
    body.dark .department-view-page .view-field:nth-child(5) .field-icon,
    body.dark-mode .department-view-page .view-field:nth-child(5) .field-icon {
        background: linear-gradient(145deg, rgba(236, 72, 153, 0.3), rgba(219, 39, 119, 0.5)) !important;
        color: #f472b6 !important;
        border: 1px solid rgba(244, 114, 182, 0.4) !important;
    }

    html[data-pms-theme="dark"] .department-view-page .view-field:nth-child(6) .field-icon,
    html[data-bs-theme="dark"] .department-view-page .view-field:nth-child(6) .field-icon,
    html[data-theme="dark"] .department-view-page .view-field:nth-child(6) .field-icon,
    html.dark .department-view-page .view-field:nth-child(6) .field-icon,
    body[data-pms-theme="dark"] .department-view-page .view-field:nth-child(6) .field-icon,
    body[data-bs-theme="dark"] .department-view-page .view-field:nth-child(6) .field-icon,
    body[data-theme="dark"] .department-view-page .view-field:nth-child(6) .field-icon,
    body.dark .department-view-page .view-field:nth-child(6) .field-icon,
    body.dark-mode .department-view-page .view-field:nth-child(6) .field-icon {
        background: linear-gradient(145deg, rgba(6, 182, 212, 0.3), rgba(8, 145, 178, 0.5)) !important;
        color: #22d3ee !important;
        border: 1px solid rgba(34, 211, 238, 0.4) !important;
    }

    /* Footer & Structure Information Card in Dark Mode */
    html[data-pms-theme="dark"] .department-view-page .view-footer,
    html[data-bs-theme="dark"] .department-view-page .view-footer,
    html[data-theme="dark"] .department-view-page .view-footer,
    html.dark .department-view-page .view-footer,
    body[data-pms-theme="dark"] .department-view-page .view-footer,
    body[data-bs-theme="dark"] .department-view-page .view-footer,
    body[data-theme="dark"] .department-view-page .view-footer,
    body.dark .department-view-page .view-footer,
    body.dark-mode .department-view-page .view-footer {
        background: #0d1b14;
        border-top-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .department-view-page .footer-info,
    html[data-bs-theme="dark"] .department-view-page .footer-info,
    html[data-theme="dark"] .department-view-page .footer-info,
    html.dark .department-view-page .footer-info,
    body[data-pms-theme="dark"] .department-view-page .footer-info,
    body[data-bs-theme="dark"] .department-view-page .footer-info,
    body[data-theme="dark"] .department-view-page .footer-info,
    body.dark .department-view-page .footer-info,
    body.dark-mode .department-view-page .footer-info {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .department-view-page .separator,
    html[data-bs-theme="dark"] .department-view-page .separator,
    html[data-theme="dark"] .department-view-page .separator,
    html.dark .department-view-page .separator,
    body[data-pms-theme="dark"] .department-view-page .separator,
    body[data-bs-theme="dark"] .department-view-page .separator,
    body[data-theme="dark"] .department-view-page .separator,
    body.dark .department-view-page .separator,
    body.dark-mode .department-view-page .separator {
        color: rgba(122, 240, 181, 0.2);
    }

    html[data-pms-theme="dark"] .department-view-page .related-card,
    html[data-bs-theme="dark"] .department-view-page .related-card,
    html[data-theme="dark"] .department-view-page .related-card,
    html.dark .department-view-page .related-card,
    body[data-pms-theme="dark"] .department-view-page .related-card,
    body[data-bs-theme="dark"] .department-view-page .related-card,
    body[data-theme="dark"] .department-view-page .related-card,
    body.dark .department-view-page .related-card,
    body.dark-mode .department-view-page .related-card {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.1);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    html[data-pms-theme="dark"] .department-view-page .related-header,
    html[data-bs-theme="dark"] .department-view-page .related-header,
    html[data-theme="dark"] .department-view-page .related-header,
    html.dark .department-view-page .related-header,
    body[data-pms-theme="dark"] .department-view-page .related-header,
    body[data-bs-theme="dark"] .department-view-page .related-header,
    body[data-theme="dark"] .department-view-page .related-header,
    body.dark .department-view-page .related-header,
    body.dark-mode .department-view-page .related-header {
        background: linear-gradient(135deg, #0d1b14, #12281e);
        border-bottom-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .department-view-page .related-header h5,
    html[data-bs-theme="dark"] .department-view-page .related-header h5,
    html[data-theme="dark"] .department-view-page .related-header h5,
    html.dark .department-view-page .related-header h5,
    body[data-pms-theme="dark"] .department-view-page .related-header h5,
    body[data-bs-theme="dark"] .department-view-page .related-header h5,
    body[data-theme="dark"] .department-view-page .related-header h5,
    body.dark .department-view-page .related-header h5,
    body.dark-mode .department-view-page .related-header h5 {
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .department-view-page .related-label,
    html[data-bs-theme="dark"] .department-view-page .related-label,
    html[data-theme="dark"] .department-view-page .related-label,
    html.dark .department-view-page .related-label,
    body[data-pms-theme="dark"] .department-view-page .related-label,
    body[data-bs-theme="dark"] .department-view-page .related-label,
    body[data-theme="dark"] .department-view-page .related-label,
    body.dark .department-view-page .related-label,
    body.dark-mode .department-view-page .related-label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .department-view-page .related-value,
    html[data-bs-theme="dark"] .department-view-page .related-value,
    html[data-theme="dark"] .department-view-page .related-value,
    html.dark .department-view-page .related-value,
    body[data-pms-theme="dark"] .department-view-page .related-value,
    body[data-bs-theme="dark"] .department-view-page .related-value,
    body[data-theme="dark"] .department-view-page .related-value,
    body.dark .department-view-page .related-value,
    body.dark-mode .department-view-page .related-value {
        color: #ffffff !important;
    }
</style>
