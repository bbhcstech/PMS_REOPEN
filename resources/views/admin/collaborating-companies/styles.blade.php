<style>
    #partner-workspace.partner-page {
        min-height: 0;
        padding: 0 0 2rem;
        max-width: 1180px;
        margin: 0 auto;
        background: transparent !important;
    }

    #partner-workspace .partner-hero,
    #partner-workspace .partner-filter,
    #partner-workspace .partner-card,
    #partner-workspace .partner-detail-card,
    #partner-workspace .partner-form-card,
    #partner-workspace .partner-stats > div,
    #partner-workspace .partner-empty {
        border: 1px solid var(--bx-border, #E2E8F0) !important;
        background: var(--bx-surface, #FFFFFF) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
        border-radius: 16px !important;
    }

    #partner-workspace .partner-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        padding: 1.35rem 1.5rem;
        margin-bottom: 1rem;
    }

    #partner-workspace .partner-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #2563EB !important;
        -webkit-text-fill-color: #2563EB !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.06em;
    }

    #partner-workspace .partner-eyebrow i {
        color: #2563EB !important;
        -webkit-text-fill-color: #2563EB !important;
        -webkit-text-stroke: 0 !important;
        font-size: 0.9rem !important;
    }

    #partner-workspace .partner-hero h1 {
        margin: 0.35rem 0 0.25rem;
        font-size: 1.35rem !important;
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
        color: var(--bx-ink, #0F172A) !important;
        -webkit-text-fill-color: var(--bx-ink, #0F172A) !important;
    }

    #partner-workspace .partner-hero p,
    #partner-workspace .partner-card p,
    #partner-workspace .partner-muted {
        margin: 0;
        font-size: 0.84rem !important;
        color: var(--bx-ink-muted, #64748B) !important;
        -webkit-text-fill-color: var(--bx-ink-muted, #64748B) !important;
        font-weight: 500 !important;
    }

    #partner-workspace .partner-actions,
    #partner-workspace .partner-card-actions,
    #partner-workspace .partner-form-actions {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    /* 3-Column Stats */
    #partner-workspace .partner-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    #partner-workspace .partner-stats > div {
        padding: 1.15rem 1.35rem;
        border-radius: 14px !important;
    }

    #partner-workspace .partner-stats span {
        color: var(--bx-ink-muted, #64748B) !important;
        -webkit-text-fill-color: var(--bx-ink-muted, #64748B) !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        display: block;
    }

    #partner-workspace .partner-stats strong {
        display: block;
        margin-top: 0.35rem;
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: #2563EB !important;
        -webkit-text-fill-color: #2563EB !important;
        letter-spacing: -0.02em;
    }

    /* Filter Toolbar */
    #partner-workspace .partner-filter {
        padding: 0.85rem 1rem;
        margin-bottom: 1rem;
        border-radius: 14px !important;
    }

    #partner-workspace .partner-filter form {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) 170px auto auto;
        gap: 0.65rem;
        align-items: center;
    }

    #partner-workspace .partner-search-box {
        position: relative;
        display: flex;
        align-items: center;
    }

    #partner-workspace .partner-search-box i {
        position: absolute;
        left: 0.85rem;
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
        -webkit-text-stroke: 0 !important;
        font-size: 1rem !important;
        pointer-events: none;
    }

    #partner-workspace .partner-search-box .form-control {
        padding-left: 2.35rem !important;
        background: var(--bx-surface-2, #F8FAFC) !important;
    }

    /* Form Controls & Buttons */
    #partner-workspace .form-control,
    #partner-workspace .form-select {
        min-height: 40px !important;
        border-radius: 10px !important;
        border: 1px solid var(--bx-border, #E2E8F0) !important;
        background: var(--bx-surface, #FFFFFF) !important;
        color: var(--bx-ink, #0F172A) !important;
        font-size: 0.82rem !important;
        font-weight: 500 !important;
        box-shadow: none !important;
    }

    #partner-workspace .form-control:focus,
    #partner-workspace .form-select:focus {
        border-color: #2563EB !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
    }

    #partner-workspace .form-control::placeholder {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    #partner-workspace textarea.form-control {
        min-height: 110px !important;
    }

    #partner-workspace .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        border-radius: 10px !important;
        min-height: 40px !important;
        padding: 0.48rem 1rem !important;
        font-size: 0.82rem !important;
        font-weight: 650 !important;
        box-shadow: none !important;
    }

    #partner-workspace .btn i {
        -webkit-text-stroke: 0 !important;
        font-size: 0.95rem !important;
    }

    #partner-workspace .btn-primary {
        background: #2563EB !important;
        border: 1px solid #2563EB !important;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.24) !important;
    }

    #partner-workspace .btn-primary:hover {
        background: #1D4ED8 !important;
        border-color: #1D4ED8 !important;
    }

    #partner-workspace .btn-light {
        background: var(--bx-surface-2, #F8FAFC) !important;
        border: 1px solid var(--bx-border, #E2E8F0) !important;
        color: var(--bx-ink-body, #334155) !important;
        -webkit-text-fill-color: var(--bx-ink-body, #334155) !important;
    }

    #partner-workspace .btn-light i {
        color: var(--bx-ink-muted, #64748B) !important;
        -webkit-text-fill-color: var(--bx-ink-muted, #64748B) !important;
    }

    #partner-workspace .btn-light:hover {
        background: #F1F5F9 !important;
        border-color: #CBD5E1 !important;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
    }

    #partner-workspace .btn-danger {
        background: #EF4444 !important;
        border: 1px solid #EF4444 !important;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
    }

    /* Empty State */
    #partner-workspace .partner-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    #partner-workspace .partner-empty {
        grid-column: 1 / -1;
        padding: 3.75rem 1.5rem;
        text-align: center;
    }

    #partner-workspace .partner-empty-icon-wrap {
        position: relative;
        width: 64px;
        height: 64px;
        margin: 0 auto 1.15rem;
        border-radius: 16px;
        background: #EFF6FF;
        border: 1px solid rgba(37, 99, 235, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #partner-workspace .partner-empty-icon-wrap > i {
        font-size: 1.85rem !important;
        color: #2563EB !important;
        -webkit-text-fill-color: #2563EB !important;
        -webkit-text-stroke: 0 !important;
    }

    #partner-workspace .partner-empty-badge {
        position: absolute;
        right: -5px;
        bottom: -5px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #2563EB;
        border: 2px solid var(--bx-surface, #FFFFFF);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #partner-workspace .partner-empty-badge i {
        font-size: 0.7rem !important;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        -webkit-text-stroke: 0 !important;
    }

    #partner-workspace .partner-empty h2 {
        margin: 0 0 0.35rem;
        font-size: 1.08rem !important;
        font-weight: 800 !important;
        color: var(--bx-ink, #0F172A) !important;
        -webkit-text-fill-color: var(--bx-ink, #0F172A) !important;
    }

    #partner-workspace .partner-empty p {
        margin: 0 auto 1.25rem;
        max-width: 380px;
        font-size: 0.84rem !important;
        color: var(--bx-ink-muted, #64748B) !important;
        -webkit-text-fill-color: var(--bx-ink-muted, #64748B) !important;
    }

    #partner-workspace .partner-empty-cta {
        padding: 0.55rem 1.25rem !important;
    }

    /* Partner Cards */
    #partner-workspace .partner-card {
        padding: 1.15rem;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        min-width: 0;
        border-radius: 14px !important;
    }

    #partner-workspace .partner-card-image {
        display: grid;
        place-items: center;
        overflow: hidden;
        width: 100%;
        aspect-ratio: 16 / 9;
        border-radius: 12px;
        background: linear-gradient(135deg, #2563EB, #4F83FF);
        color: #FFFFFF;
        text-decoration: none;
    }

    #partner-workspace .partner-card-image img,
    #partner-workspace .partner-show-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    #partner-workspace .partner-card-image span {
        font-size: 2.5rem;
        font-weight: 800;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
    }

    #partner-workspace .partner-card-head {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.75rem;
        align-items: start;
        min-width: 0;
    }

    #partner-workspace .partner-card h2 {
        margin: 0 0 2px;
        font-size: 1.05rem !important;
        font-weight: 750 !important;
        color: var(--bx-ink, #0F172A) !important;
        -webkit-text-fill-color: var(--bx-ink, #0F172A) !important;
        overflow-wrap: anywhere;
    }

    #partner-workspace .partner-status {
        display: inline-flex;
        padding: 4px 10px;
        border-radius: 999px;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        font-size: 0.7rem;
        font-weight: 700;
        white-space: nowrap;
    }

    #partner-workspace .partner-status.active { background: #10B981; }
    #partner-workspace .partner-status.inactive { background: #64748B; }

    #partner-workspace .partner-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    #partner-workspace .partner-meta span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 999px;
        background: #EFF6FF;
        color: #2563EB !important;
        -webkit-text-fill-color: #2563EB !important;
        font-size: 0.74rem;
        font-weight: 650;
    }

    #partner-workspace .partner-contact-links {
        display: grid;
        gap: 5px;
    }

    #partner-workspace .partner-contact-links a,
    #partner-workspace .partner-text-link {
        color: #2563EB !important;
        -webkit-text-fill-color: #2563EB !important;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        overflow-wrap: anywhere;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    #partner-workspace .partner-description {
        min-height: 54px;
    }

    #partner-workspace .partner-socials,
    #partner-workspace .partner-social-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    #partner-workspace .partner-socials a,
    #partner-workspace .partner-social-list a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 34px;
        min-height: 34px;
        padding: 0 8px;
        justify-content: center;
        border-radius: 8px;
        background: #EFF6FF;
        color: #2563EB !important;
        -webkit-text-fill-color: #2563EB !important;
        text-decoration: none;
        font-weight: 650;
    }

    #partner-workspace .partner-card-actions {
        margin-top: auto;
    }

    #partner-workspace .partner-card-actions form {
        display: inline-flex;
    }

    #partner-workspace .partner-card-actions .btn {
        min-height: 34px !important;
        padding: 0.35rem 0.75rem !important;
        font-size: 0.78rem !important;
    }

    #partner-workspace .partner-pagination {
        margin-top: 1.25rem;
    }

    /* Form & Detail Cards */
    #partner-workspace .partner-form-card,
    #partner-workspace .partner-detail-card {
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    #partner-workspace .partner-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    #partner-workspace .partner-form-grid .full {
        grid-column: 1 / -1;
    }

    #partner-workspace .partner-form-label {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        margin-bottom: 0.4rem !important;
        color: var(--bx-ink, #0F172A) !important;
        -webkit-text-fill-color: var(--bx-ink, #0F172A) !important;
        font-size: 0.76rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.03em !important;
    }

    #partner-workspace .partner-badge-optional {
        display: inline-flex;
        align-items: center;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 2px 8px;
        border-radius: 6px;
        background: #F1F5F9;
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        border: 1px solid #E2E8F0;
        line-height: 1.2;
    }

    #partner-workspace .partner-badge-required {
        display: inline-flex;
        align-items: center;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 2px 8px;
        border-radius: 6px;
        background: #FEF2F2;
        color: #DC2626 !important;
        -webkit-text-fill-color: #DC2626 !important;
        border: 1px solid #FECACA;
        line-height: 1.2;
    }

    #partner-workspace .partner-form-actions {
        margin-top: 1.25rem;
    }

    #partner-workspace .partner-form-preview {
        width: min(280px, 100%);
        aspect-ratio: 16 / 9;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid var(--bx-border, #E2E8F0);
    }

    #partner-workspace .partner-detail-grid {
        display: grid;
        grid-template-columns: 1.25fr 0.75fr;
        gap: 1rem;
    }

    #partner-workspace .partner-show-image {
        overflow: hidden;
        border-radius: 16px;
        height: clamp(190px, 32vw, 380px);
        margin-bottom: 1rem;
        border: 1px solid var(--bx-border, #E2E8F0);
    }

    #partner-workspace .partner-detail-card h2 {
        font-size: 1.05rem !important;
        font-weight: 750 !important;
        margin-bottom: 0.85rem;
    }

    #partner-workspace .partner-detail-card p {
        white-space: pre-wrap;
        color: var(--bx-ink-body, #334155);
        font-weight: 500;
    }

    #partner-workspace .partner-detail-card dl {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 0.65rem;
        margin: 0;
    }

    #partner-workspace .partner-detail-card dt {
        color: var(--bx-ink-muted, #64748B);
        font-weight: 700;
    }

    #partner-workspace .partner-detail-card dd {
        margin: 0;
        font-weight: 600;
    }

    @media (max-width: 1199px) {
        #partner-workspace .partner-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        #partner-workspace.partner-page {
            padding: 0 0 1.5rem;
        }
        #partner-workspace .partner-hero,
        #partner-workspace .partner-filter form,
        #partner-workspace .partner-detail-grid,
        #partner-workspace .partner-form-grid,
        #partner-workspace .partner-stats,
        #partner-workspace .partner-grid {
            grid-template-columns: 1fr;
            flex-direction: column;
            align-items: stretch;
        }
        #partner-workspace .partner-actions .btn,
        #partner-workspace .partner-filter .btn,
        #partner-workspace .partner-card-actions .btn,
        #partner-workspace .partner-card-actions form,
        #partner-workspace .partner-form-actions .btn {
            width: 100%;
        }
        #partner-workspace .partner-card-head {
            grid-template-columns: 1fr auto;
        }
        #partner-workspace .partner-detail-card dl {
            grid-template-columns: 1fr;
        }
    }

    /* Dark Theme Support */
    html[data-pms-theme="dark"] #partner-workspace .partner-hero,
    html[data-pms-theme="dark"] #partner-workspace .partner-filter,
    html[data-pms-theme="dark"] #partner-workspace .partner-card,
    html[data-pms-theme="dark"] #partner-workspace .partner-detail-card,
    html[data-pms-theme="dark"] #partner-workspace .partner-form-card,
    html[data-pms-theme="dark"] #partner-workspace .partner-stats > div,
    html[data-pms-theme="dark"] #partner-workspace .partner-empty {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.1) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.38) !important;
    }

    html[data-pms-theme="dark"] #partner-workspace .partner-eyebrow,
    html[data-pms-theme="dark"] #partner-workspace .partner-eyebrow i,
    html[data-pms-theme="dark"] #partner-workspace .partner-stats strong {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] #partner-workspace .partner-empty-icon-wrap {
        background: rgba(47, 107, 255, 0.16);
        border-color: rgba(96, 165, 250, 0.25);
    }

    html[data-pms-theme="dark"] #partner-workspace .partner-empty-icon-wrap > i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] #partner-workspace .partner-empty-badge {
        border-color: #0F1530;
    }

    html[data-pms-theme="dark"] #partner-workspace .form-control,
    html[data-pms-theme="dark"] #partner-workspace .form-select,
    html[data-pms-theme="dark"] #partner-workspace .partner-search-box .form-control,
    html[data-pms-theme="dark"] #partner-workspace .btn-light {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] #partner-workspace .btn-light:hover {
        background: #1C2652 !important;
        border-color: rgba(96, 165, 250, 0.4) !important;
    }

    html[data-pms-theme="dark"] #partner-workspace .partner-meta span,
    html[data-pms-theme="dark"] #partner-workspace .partner-socials a,
    html[data-pms-theme="dark"] #partner-workspace .partner-social-list a {
        background: #141B3D !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        border: 1px solid rgba(238, 241, 251, 0.08) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #partner-workspace .partner-badge-required {
        background: rgba(239, 68, 68, 0.16) !important;
        border-color: rgba(248, 113, 113, 0.35) !important;
        color: #FCA5A5 !important;
        -webkit-text-fill-color: #FCA5A5 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #partner-workspace .partner-badge-optional {
        background: rgba(148, 163, 184, 0.16) !important;
        border-color: rgba(148, 163, 184, 0.3) !important;
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #partner-workspace .partner-form-label {
        color: #E2E8F0 !important;
        -webkit-text-fill-color: #E2E8F0 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #partner-workspace .partner-form-card small.text-muted,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #partner-workspace .partner-detail-card p,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #partner-workspace .partner-detail-card dt {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #partner-workspace .partner-detail-card h2,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #partner-workspace .partner-detail-card dd {
        color: #F8FAFC !important;
        -webkit-text-fill-color: #F8FAFC !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #partner-workspace input[type="file"]::file-selector-button {
        background: #1E293B !important;
        color: #E2E8F0 !important;
        border: 1px solid rgba(148, 163, 184, 0.3) !important;
        border-radius: 6px !important;
    }
</style>
