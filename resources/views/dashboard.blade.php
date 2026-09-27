<!-- //dashboard blade page -->


@extends('admin.layout.app')

@section('title', 'Admin Dashboard')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700;800&display=swap');

    /* ======================================================
       BITROXIA ADMIN WORKSPACE DASHBOARD — DESIGN SYSTEM
       Visual Reference: https://pms.thesmartservice.in/
       Core Palette:
         • Primary Accent:    #2F6BFF (Professional Blue)
         • Secondary Accent:  #06B6D4 (Cyan / Teal)
         • Supporting Accent: #8B5CF6 (Violet / Purple)
         • Success State:     #10B981 (Emerald)
         • Warning State:     #F59E0B (Amber)
         • Danger State:      #EF4444 (Red)
         • Headings:          #0F172A (Dark Slate / Navy)
         • Body Text:         #334155 (Medium Slate)
         • Muted / Meta:      #64748B (Cool Gray)
         • Surface:           #FFFFFF (Crisp White)
         • Background:        #F8FAFC (Low-Contrast Cool Neutral)
         • Borders:           #E2E8F0 (Subtle Cool-Gray)
       ====================================================== */

    :root,
    html[data-pms-theme="light"],
    html[data-theme="light"] {
        /* Semantic Color Tokens */
        --bx-blue: #2F6BFF;
        --bx-blue-dim: #1E4FCC;
        --bx-blue-soft: rgba(47, 107, 255, 0.08);
        --bx-blue-subtle: rgba(47, 107, 255, 0.04);

        --bx-cyan: #06B6D4;
        --bx-cyan-dim: #0891B2;
        --bx-cyan-soft: rgba(6, 182, 212, 0.08);

        --bx-violet: #8B5CF6;
        --bx-violet-dim: #7C3AED;
        --bx-violet-soft: rgba(139, 92, 246, 0.08);

        --bx-emerald: #10B981;
        --bx-emerald-dim: #059669;
        --bx-emerald-soft: rgba(16, 185, 129, 0.08);

        --bx-amber: #F59E0B;
        --bx-amber-soft: rgba(245, 158, 11, 0.08);

        --bx-red: #EF4444;
        --bx-red-soft: rgba(239, 68, 68, 0.08);

        /* Surfaces & Inks */
        --bx-bg: #F8FAFC;
        --bx-surface: #FFFFFF;
        --bx-surface-2: #F8FAFC;
        --bx-surface-3: #F1F5F9;

        --bx-ink: #0F172A;
        --bx-ink-body: #334155;
        --bx-ink-muted: #64748B;
        --bx-ink-faint: #94A3B8;

        --bx-border: #E2E8F0;
        --bx-border-strong: #CBD5E1;
        --bx-border-focus: rgba(47, 107, 255, 0.35);

        --bx-shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.03);
        --bx-shadow-md: 0 4px 12px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
        --bx-shadow-lg: 0 12px 24px -4px rgba(15, 23, 42, 0.08), 0 4px 8px -2px rgba(15, 23, 42, 0.03);

        --bx-gauge-track: #E2E8F0;
        --bx-gauge-inner: #FFFFFF;

        --font-display: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
        --font-body: 'Inter', 'Plus Jakarta Sans', system-ui, sans-serif;
    }

    /* Dark Mode Theme Tokens */
    html[data-pms-theme="dark"],
    html[data-theme="dark"] {
        --bx-bg: #070B1A;
        --bx-surface: #0F1530;
        --bx-surface-2: #141B3D;
        --bx-surface-3: #1A2247;

        --bx-ink: #EEF1FB;
        --bx-ink-body: #CBD5E1;
        --bx-ink-muted: #9AA3C7;
        --bx-ink-faint: #6B739A;

        --bx-border: rgba(238, 241, 251, 0.09);
        --bx-border-strong: rgba(238, 241, 251, 0.16);
        --bx-border-focus: rgba(47, 107, 255, 0.5);

        --bx-blue-soft: rgba(47, 107, 255, 0.18);
        --bx-cyan-soft: rgba(6, 182, 212, 0.18);
        --bx-violet-soft: rgba(139, 92, 246, 0.18);
        --bx-emerald-soft: rgba(16, 185, 129, 0.18);
        --bx-amber-soft: rgba(245, 158, 11, 0.18);
        --bx-red-soft: rgba(239, 68, 68, 0.18);

        --bx-shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.25);
        --bx-shadow-md: 0 8px 20px -4px rgba(0, 0, 0, 0.4);
        --bx-shadow-lg: 0 16px 36px -6px rgba(0, 0, 0, 0.5);

        --bx-gauge-track: #1A2247;
        --bx-gauge-inner: #0F1530;
    }

    * {
        box-sizing: border-box;
    }

    body {
        background: var(--bx-bg) !important;
        font-family: var(--font-body);
        color: var(--bx-ink-body);
        line-height: 1.55;
        -webkit-font-smoothing: antialiased;
        transition: background 0.25s ease, color 0.25s ease;
    }

    /* Subdued Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(14px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes progressFill {
        from {
            width: 0;
        }
    }

    /* Floating orbs are hidden for a clean, professional SaaS experience */
    .floating-elements {
        display: none !important;
    }

    /* Main Container */
    #main {
        min-height: 100vh;
        position: relative;
        background: var(--bx-bg);
    }

    .content-wrapper {
        padding: 1.5rem 2rem;
        max-width: 100%;
        position: relative;
        z-index: 1;
        animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Dashboard Shell */
    .industry-dashboard-shell {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    /* ======================================================
       DASHBOARD HEADER / HERO COMMAND CENTER
       Clean SaaS Hierarchy:
         • Eyebrow: "Admin Workspace"
         • Title: Strong, crisp typography
         • Contextual description
         • Clean action buttons
       ====================================================== */
    .industry-hero-card {
        background: var(--bx-surface);
        border: 1px solid var(--bx-border);
        border-radius: 16px;
        box-shadow: var(--bx-shadow-sm);
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(260px, 0.6fr);
        gap: 2rem;
        min-height: 220px;
        overflow: hidden;
        padding: 1.75rem 2.25rem;
        position: relative;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .industry-hero-card:hover {
        box-shadow: var(--bx-shadow-md);
        border-color: var(--bx-border-strong);
    }

    .industry-hero-copy {
        align-self: center;
        max-width: 720px;
    }

    .industry-eyebrow {
        align-items: center;
        background: var(--bx-blue-soft);
        border: 1px solid rgba(47, 107, 255, 0.2);
        border-radius: 999px;
        color: var(--bx-blue) !important;
        display: inline-flex;
        font-family: var(--font-display);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        margin-bottom: 0.85rem;
        padding: 0.35rem 0.85rem;
        text-transform: uppercase;
        gap: 6px;
    }

    .industry-eyebrow i {
        font-size: 0.95rem;
    }

    .industry-hero-copy h1 {
        font-family: var(--font-display);
        font-size: clamp(1.65rem, 2.5vw, 2.15rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin-bottom: 0.65rem;
        color: var(--bx-ink) !important;
    }

    .industry-hero-copy p {
        color: var(--bx-ink-muted);
        font-size: 0.95rem;
        font-weight: 500;
        max-width: 600px;
        line-height: 1.55;
        margin-bottom: 0;
    }

    .industry-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.4rem;
    }

    /* Buttons */
    .industry-btn {
        align-items: center;
        border-radius: 10px;
        display: inline-flex;
        font-family: var(--font-display);
        font-weight: 650;
        font-size: 0.875rem;
        gap: 0.5rem;
        justify-content: center;
        min-height: 40px;
        padding: 0.6rem 1.25rem;
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap;
        cursor: pointer;
    }

    .industry-btn-primary {
        background: var(--bx-blue);
        border: 1px solid var(--bx-blue);
        color: #FFFFFF !important;
        box-shadow: 0 1px 3px rgba(47, 107, 255, 0.25);
    }

    .industry-btn-primary:hover {
        background: var(--bx-blue-dim);
        border-color: var(--bx-blue-dim);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.3);
        color: #FFFFFF !important;
    }

    .industry-btn-light {
        background: var(--bx-surface);
        border: 1px solid var(--bx-border);
        color: var(--bx-ink-body) !important;
        box-shadow: var(--bx-shadow-sm);
    }

    .industry-btn-light:hover {
        background: var(--bx-surface-3);
        border-color: var(--bx-border-strong);
        color: var(--bx-ink) !important;
        transform: translateY(-1px);
    }

    .industry-btn-outline {
        background: transparent;
        border: 1px solid var(--bx-border-strong);
        color: var(--bx-blue) !important;
    }

    .industry-btn-outline:hover {
        background: var(--bx-blue-soft);
        border-color: var(--bx-blue);
        color: var(--bx-blue) !important;
        transform: translateY(-1px);
    }

    .industry-hero-visual {
        align-items: center;
        display: flex;
        justify-content: center;
        position: relative;
    }

    .industry-hero-visual img {
        max-height: 180px;
        object-fit: contain;
        width: auto;
        filter: drop-shadow(0 10px 24px rgba(15, 23, 42, 0.08));
    }

    /* ======================================================
       STATISTICS / KPI CARDS (OVERVIEW GRID)
       Semantic Colors:
         • Projects   → Blue (#2F6BFF)
         • Tasks      → Violet (#8B5CF6)
         • Tickets    → Cyan (#06B6D4)
         • Attendance → Emerald (#10B981)
       ====================================================== */
    .industry-overview-grid {
        display: grid;
        gap: 1.25rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .industry-metric-card {
        background: var(--bx-surface);
        border: 1px solid var(--bx-border);
        border-radius: 14px;
        color: var(--bx-ink) !important;
        min-height: 150px;
        overflow: hidden;
        padding: 1.25rem 1.35rem;
        position: relative;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        box-shadow: var(--bx-shadow-sm);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .industry-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--bx-shadow-md);
    }

    .kpi-card-projects:hover { border-color: var(--bx-blue); }
    .kpi-card-tasks:hover { border-color: var(--bx-violet); }
    .kpi-card-tickets:hover { border-color: var(--bx-cyan); }
    .kpi-card-attendance:hover { border-color: var(--bx-emerald); }

    .kpi-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .kpi-card-top span {
        color: var(--bx-ink-muted) !important;
        font-family: var(--font-display);
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .kpi-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .kpi-blue { background: var(--bx-blue-soft); color: var(--bx-blue); }
    .kpi-violet { background: var(--bx-violet-soft); color: var(--bx-violet); }
    .kpi-cyan { background: var(--bx-cyan-soft); color: var(--bx-cyan); }
    .kpi-emerald { background: var(--bx-emerald-soft); color: var(--bx-emerald); }

    .kpi-number {
        color: var(--bx-ink) !important;
        display: block;
        font-family: var(--font-display);
        font-size: clamp(2rem, 2.8vw, 2.4rem);
        font-weight: 800;
        line-height: 1.05;
        margin: 0.35rem 0;
        letter-spacing: -0.03em;
    }

    .kpi-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .kpi-card-footer small {
        color: var(--bx-ink-muted);
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .industry-arrow {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: var(--bx-surface-2);
        border: 1px solid var(--bx-border);
        color: var(--bx-ink-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .industry-metric-card:hover .industry-arrow {
        background: var(--bx-blue);
        border-color: var(--bx-blue);
        color: #FFFFFF !important;
        transform: translateX(2px);
    }

    /* ======================================================
       EXECUTIVE PANELS & BUSINESS MODEL
       ====================================================== */
    .saas-executive-grid {
        display: grid;
        gap: 1.25rem;
        grid-template-columns: 1.15fr 0.85fr;
    }

    .industry-panel {
        background: var(--bx-surface);
        border: 1px solid var(--bx-border);
        border-radius: 16px;
        padding: 1.5rem;
        position: relative;
        box-shadow: var(--bx-shadow-sm);
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .industry-panel:hover {
        border-color: var(--bx-border-strong);
        box-shadow: var(--bx-shadow-md);
    }

    .industry-panel-head {
        align-items: flex-start;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }

    .industry-panel-head h3 {
        color: var(--bx-ink);
        font-family: var(--font-display);
        font-size: 1.15rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        margin: 0 0 0.25rem;
    }

    .industry-panel-head p {
        color: var(--bx-ink-muted);
        font-size: 0.85rem;
        font-weight: 500;
        margin: 0;
        line-height: 1.45;
    }

    .industry-panel-head a {
        align-items: center;
        background: var(--bx-surface-2);
        border: 1px solid var(--bx-border);
        border-radius: 8px;
        color: var(--bx-blue) !important;
        display: inline-flex;
        flex: 0 0 auto;
        font-family: var(--font-display);
        font-size: 0.8rem;
        font-weight: 650;
        padding: 0.4rem 0.85rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .industry-panel-head a:hover {
        background: var(--bx-blue);
        border-color: var(--bx-blue);
        color: #ffffff !important;
    }

    /* Revenue Strip Cards */
    .saas-revenue-strip {
        display: grid;
        gap: 0.85rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .saas-money-card {
        background: var(--bx-surface-2);
        border: 1px solid var(--bx-border);
        border-radius: 12px;
        min-height: 125px;
        padding: 1rem;
        position: relative;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .saas-money-card:hover {
        border-color: var(--bx-blue);
        transform: translateY(-2px);
    }

    .saas-money-card span {
        color: var(--bx-ink-muted) !important;
        display: block;
        font-family: var(--font-display);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .saas-money-card strong {
        color: var(--bx-ink);
        display: block;
        font-family: var(--font-display);
        font-size: clamp(1.2rem, 1.7vw, 1.65rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.15;
        margin: 0.35rem 0 0.25rem;
    }

    .saas-money-card em {
        color: var(--bx-blue);
        font-size: 0.78rem;
        font-style: normal;
        font-weight: 650;
    }

    /* Prediction Grid */
    .saas-prediction-grid {
        display: grid;
        gap: 0.85rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .saas-insight-card {
        background: var(--bx-surface-2);
        border: 1px solid var(--bx-border);
        border-radius: 12px;
        min-height: 125px;
        padding: 1rem;
        position: relative;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .saas-insight-card:hover {
        border-color: var(--bx-border-strong);
        transform: translateY(-2px);
    }

    .saas-insight-card span {
        color: var(--bx-ink-muted) !important;
        display: block;
        font-family: var(--font-display);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .saas-insight-card strong {
        color: var(--bx-ink);
        display: block;
        font-family: var(--font-display);
        font-size: 1.05rem;
        font-weight: 800;
        margin: 0.3rem 0;
    }

    .saas-insight-card p {
        color: var(--bx-ink-body);
        font-size: 0.82rem;
        font-weight: 500;
        margin: 0;
        line-height: 1.4;
    }

    /* Risk Status Pills */
    .saas-risk-pill {
        align-items: center;
        border-radius: 8px !important;
        display: inline-flex !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        gap: 0.35rem !important;
        padding: 0.3rem 0.65rem !important;
        letter-spacing: 0.04em !important;
        text-transform: uppercase !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .saas-risk-low {
        background: #ECFDF5 !important;
        border: 1px solid #A7F3D0 !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .saas-risk-low i,
    .saas-risk-low .bx,
    .saas-risk-low * {
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
        background-color: #059669 !important;
    }

    .saas-risk-mid {
        background: #FFFBEB !important;
        border: 1px solid #FDE68A !important;
        color: #D97706 !important;
        -webkit-text-fill-color: #D97706 !important;
    }

    .saas-risk-mid i,
    .saas-risk-mid .bx,
    .saas-risk-mid * {
        color: #D97706 !important;
        -webkit-text-fill-color: #D97706 !important;
        background-color: #D97706 !important;
    }

    .saas-risk-high {
        background: #FEF2F2 !important;
        border: 1px solid #FECACA !important;
        color: #DC2626 !important;
        -webkit-text-fill-color: #DC2626 !important;
    }

    .saas-risk-high i,
    .saas-risk-high .bx,
    .saas-risk-high * {
        color: #DC2626 !important;
        -webkit-text-fill-color: #DC2626 !important;
        background-color: #DC2626 !important;
    }

    /* Dark Mode Risk Status Pills - High Contrast & Clearly Visible */
    html[data-pms-theme="dark"] .saas-risk-low,
    html[data-theme="dark"] .saas-risk-low {
        background: rgba(16, 185, 129, 0.18) !important;
        border: 1px solid rgba(16, 185, 129, 0.45) !important;
        color: #34D399 !important;
        -webkit-text-fill-color: #34D399 !important;
    }

    html[data-pms-theme="dark"] .saas-risk-low i,
    html[data-pms-theme="dark"] .saas-risk-low .bx,
    html[data-pms-theme="dark"] .saas-risk-low *,
    html[data-theme="dark"] .saas-risk-low i,
    html[data-theme="dark"] .saas-risk-low .bx,
    html[data-theme="dark"] .saas-risk-low * {
        color: #34D399 !important;
        -webkit-text-fill-color: #34D399 !important;
        background-color: #34D399 !important;
    }

    html[data-pms-theme="dark"] .saas-risk-mid,
    html[data-theme="dark"] .saas-risk-mid {
        background: rgba(245, 158, 11, 0.18) !important;
        border: 1px solid rgba(245, 158, 11, 0.45) !important;
        color: #FBBF24 !important;
        -webkit-text-fill-color: #FBBF24 !important;
    }

    html[data-pms-theme="dark"] .saas-risk-mid i,
    html[data-pms-theme="dark"] .saas-risk-mid .bx,
    html[data-pms-theme="dark"] .saas-risk-mid *,
    html[data-theme="dark"] .saas-risk-mid i,
    html[data-theme="dark"] .saas-risk-mid .bx,
    html[data-theme="dark"] .saas-risk-mid * {
        color: #FBBF24 !important;
        -webkit-text-fill-color: #FBBF24 !important;
        background-color: #FBBF24 !important;
    }

    html[data-pms-theme="dark"] .saas-risk-high,
    html[data-theme="dark"] .saas-risk-high {
        background: rgba(239, 68, 68, 0.2) !important;
        border: 1px solid rgba(239, 68, 68, 0.5) !important;
        color: #F87171 !important;
        -webkit-text-fill-color: #F87171 !important;
    }

    html[data-pms-theme="dark"] .saas-risk-high i,
    html[data-pms-theme="dark"] .saas-risk-high .bx,
    html[data-theme="dark"] .saas-risk-high *,
    html[data-theme="dark"] .saas-risk-high i,
    html[data-theme="dark"] .saas-risk-high .bx,
    html[data-theme="dark"] .saas-risk-high * {
        color: #F87171 !important;
        -webkit-text-fill-color: #F87171 !important;
        background-color: #F87171 !important;
    }

    /* ======================================================
       TREND BOARD & GAUGES
       ====================================================== */
    .saas-trend-board {
        display: grid;
        gap: 1.25rem;
        grid-template-columns: 1fr 1fr;
    }

    .saas-line-card {
        min-height: 220px;
    }

    .saas-sparkline {
        align-items: flex-end;
        display: flex;
        gap: 0.75rem;
        height: 120px;
        margin-top: 1rem;
        padding: 0.6rem;
        background: var(--bx-surface-2);
        border-radius: 12px;
        border: 1px solid var(--bx-border);
    }

    .saas-sparkline span {
        background: var(--bx-blue);
        border-radius: 6px 6px 3px 3px;
        flex: 1 1 0;
        min-width: 10px;
        height: var(--spark);
        transition: transform 0.2s ease, background 0.2s ease;
    }

    .saas-sparkline span:hover {
        transform: scaleY(1.06);
        background: var(--bx-violet);
    }

    /* Gauges */
    .industry-gauge {
        align-items: center;
        background: conic-gradient(var(--bx-cyan) 0deg, var(--bx-blue) var(--value-deg), var(--bx-gauge-track) var(--value-deg), var(--bx-gauge-track) 180deg, transparent 180deg);
        border-radius: 180px 180px 20px 20px;
        display: flex;
        height: 145px;
        justify-content: center;
        margin: 0.5rem auto 1rem;
        max-width: 260px;
        position: relative;
    }

    .industry-gauge::after {
        background: var(--bx-gauge-inner);
        border-radius: 155px 155px 16px 16px;
        content: "";
        inset: 20px 20px 0;
        position: absolute;
    }

    .industry-gauge div {
        margin-top: 25px;
        position: relative;
        text-align: center;
        z-index: 1;
    }

    .industry-gauge strong {
        color: var(--bx-ink);
        display: block;
        font-family: var(--font-display);
        font-size: 2.15rem;
        font-weight: 800;
        line-height: 1;
    }

    .industry-gauge span {
        color: var(--bx-ink-muted) !important;
        font-family: var(--font-display);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .industry-presence-row {
        display: grid;
        gap: 0.65rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .industry-presence-row span {
        background: var(--bx-surface-2);
        border: 1px solid var(--bx-border);
        border-radius: 10px;
        color: var(--bx-ink-body) !important;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.75rem 0.5rem;
        text-align: center;
    }

    .industry-presence-row b {
        color: var(--bx-ink);
        display: block;
        font-family: var(--font-display);
        font-size: 1.1rem;
        font-weight: 800;
    }

    /* ======================================================
       WORK ANALYTICS & PRESENCE
       ====================================================== */
    .industry-main-grid {
        display: grid;
        gap: 1.25rem;
        grid-template-columns: minmax(0, 1.4fr) minmax(300px, 0.6fr);
    }

    .industry-bars {
        display: grid;
        gap: 0.85rem;
    }

    .industry-bar {
        align-items: center;
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(0, 1fr) 100px;
    }

    .industry-bar span {
        background: var(--bx-surface-3);
        border-radius: 999px;
        display: block;
        height: 12px;
        overflow: hidden;
        position: relative;
    }

    .industry-bar span::after {
        background: var(--bx-blue);
        border-radius: inherit;
        content: "";
        inset: 0 auto 0 0;
        position: absolute;
        width: var(--bar);
    }

    .industry-bar.is-muted span::after {
        background: var(--bx-emerald);
    }

    .industry-bar label {
        color: var(--bx-ink);
        font-size: 0.85rem;
        font-weight: 650;
        margin: 0;
    }

    /* ======================================================
       AUTOMATIC MODULES & SHORTCUTS
       ====================================================== */
    .saas-module-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .saas-module-card {
        background: var(--bx-surface-2);
        border: 1px solid var(--bx-border);
        border-radius: 14px;
        color: var(--bx-ink) !important;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 185px;
        padding: 1rem;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: var(--bx-shadow-sm);
    }

    .saas-module-card:hover {
        border-color: var(--bx-blue);
        transform: translateY(-2px);
    }

    .saas-module-head {
        align-items: center;
        display: flex;
        gap: 0.65rem;
    }

    .saas-module-icon {
        align-items: center;
        background: var(--bx-blue-soft);
        border-radius: 10px;
        color: var(--bx-blue) !important;
        display: inline-flex;
        flex: 0 0 36px;
        font-size: 1.15rem;
        height: 36px;
        justify-content: center;
        width: 36px;
    }

    .saas-module-card h4 {
        color: var(--bx-ink);
        font-family: var(--font-display);
        font-size: 0.9rem;
        font-weight: 700;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .saas-module-card small {
        color: var(--bx-ink-muted);
        font-size: 0.75rem;
        font-weight: 550;
    }

    .saas-module-donut {
        --accent: var(--bx-blue);
        align-items: center;
        align-self: center;
        background: conic-gradient(var(--accent) calc(var(--percent) * 1%), var(--bx-gauge-track) 0);
        border-radius: 50%;
        display: flex;
        height: 80px;
        justify-content: center;
        position: relative;
        width: 80px;
    }

    .saas-module-donut::after {
        background: var(--bx-surface);
        border-radius: 50%;
        content: "";
        inset: 10px;
        position: absolute;
    }

    .saas-module-donut strong {
        color: var(--bx-ink);
        font-family: var(--font-display);
        font-size: 1.05rem;
        font-weight: 800;
        position: relative;
        z-index: 1;
    }

    .saas-module-meta {
        align-items: center;
        display: flex;
        justify-content: space-between;
        margin-top: auto;
    }

    .saas-module-meta em {
        background: var(--bx-blue-soft);
        border-radius: 6px;
        color: var(--bx-blue);
        font-size: 0.72rem;
        font-style: normal;
        font-weight: 700;
        padding: 0.25rem 0.55rem;
    }

    /* Module Intelligence Pie Charts Grid */
    .industry-chart-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .industry-chart-card {
        background: var(--bx-surface-2);
        border: 1px solid var(--bx-border);
        border-radius: 14px;
        min-height: 230px;
        padding: 1.15rem;
        transition: all 0.2s ease;
    }

    .industry-chart-card:hover {
        border-color: var(--bx-blue);
        transform: translateY(-2px);
    }

    .industry-chart-body {
        align-items: center;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        text-align: center;
    }

    .industry-donut {
        --accent: var(--bx-blue);
        align-items: center;
        background: conic-gradient(var(--accent) calc(var(--percent) * 1%), var(--bx-gauge-track) 0);
        border-radius: 50%;
        display: flex;
        height: 110px;
        justify-content: center;
        position: relative;
        width: 110px;
    }

    .industry-donut::after {
        background: var(--bx-surface);
        border-radius: 50%;
        content: "";
        inset: 14px;
        position: absolute;
    }

    .industry-donut strong {
        color: var(--bx-ink);
        font-family: var(--font-display);
        font-size: 1.35rem;
        font-weight: 800;
        position: relative;
        z-index: 1;
    }

    .industry-chart-meta h4 {
        color: var(--bx-ink);
        font-family: var(--font-display);
        font-size: 0.95rem;
        font-weight: 700;
        margin: 0 0 0.25rem;
    }

    .industry-chart-meta p {
        color: var(--bx-ink-muted);
        font-size: 0.8rem;
        font-weight: 500;
        margin: 0;
    }

    .industry-chart-link {
        color: var(--bx-blue) !important;
        font-family: var(--font-display);
        font-size: 0.82rem;
        font-weight: 650;
        text-decoration: none;
    }

    .industry-chart-link:hover {
        text-decoration: underline;
    }

    /* Feature Shortcuts Grid */
    .industry-feature-grid {
        display: grid;
        gap: 0.85rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .industry-feature-card {
        align-items: center;
        background: var(--bx-surface-2);
        border: 1px solid var(--bx-border);
        border-radius: 12px;
        color: var(--bx-ink) !important;
        display: grid;
        gap: 0.75rem;
        grid-template-columns: 40px minmax(0, 1fr) auto;
        min-height: 72px;
        padding: 0.85rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .industry-feature-card:hover {
        border-color: var(--bx-blue);
        transform: translateY(-2px);
    }

    .industry-feature-icon {
        align-items: center;
        background: var(--bx-blue-soft);
        border-radius: 10px;
        color: var(--bx-blue) !important;
        display: inline-flex;
        font-size: 1.2rem;
        height: 40px;
        justify-content: center;
        width: 40px;
    }

    .industry-feature-copy {
        min-width: 0;
    }

    .industry-feature-copy strong {
        color: var(--bx-ink);
        font-family: var(--font-display);
        font-size: 0.88rem;
        font-weight: 700;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .industry-feature-copy small {
        color: var(--bx-ink-muted);
        font-size: 0.75rem;
        font-weight: 500;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .industry-feature-card em {
        background: var(--bx-surface-3);
        border-radius: 6px;
        color: var(--bx-ink-body);
        font-size: 0.75rem;
        font-style: normal;
        font-weight: 700;
        min-width: 32px;
        padding: 0.25rem 0.5rem;
        text-align: center;
    }

    /* ======================================================
       SECONDARY SECTIONS: STATS, WELCOME, CONTENT
       ====================================================== */
    .welcome-section {
        margin-bottom: 1.75rem;
    }

    .welcome-card {
        background: var(--bx-surface);
        border: 1px solid var(--bx-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--bx-shadow-sm);
    }

    .welcome-content {
        padding: 2rem;
    }

    .welcome-title {
        font-family: var(--font-display);
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--bx-ink);
        margin-bottom: 0.65rem;
    }

    .welcome-text {
        color: var(--bx-ink-muted);
        font-size: 0.95rem;
        margin-bottom: 1.25rem;
        line-height: 1.55;
    }

    .welcome-badges {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .welcome-badge {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        background: var(--bx-blue-soft);
        border: 1px solid rgba(47, 107, 255, 0.2);
        color: var(--bx-blue);
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .welcome-illustration {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .welcome-illustration img {
        max-height: 180px;
        filter: drop-shadow(0 10px 20px rgba(15, 23, 42, 0.08));
    }

    /* Secondary Stats Grid */
    .stats-section {
        margin-bottom: 1.75rem;
    }

    .stats-grid {
        display: grid;
        gap: 1.25rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .stat-card {
        background: var(--bx-surface);
        border: 1px solid var(--bx-border);
        border-radius: 14px;
        padding: 1.25rem;
        position: relative;
        box-shadow: var(--bx-shadow-sm);
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .stat-card:hover {
        border-color: var(--bx-blue);
        transform: translateY(-2px);
    }

    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: var(--pms-primary, #0f744c) !important;
        color: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        box-shadow: 0 4px 12px rgba(15, 116, 76, 0.2);
        flex-shrink: 0;
    }

    .stat-icon i,
    .stat-icon .bx,
    .stat-icon [class*="bx"],
    .stat-icon svg {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        background-color: #ffffff !important;
        fill: #ffffff !important;
        opacity: 1 !important;
        font-size: 1.35rem !important;
        line-height: 1 !important;
        visibility: visible !important;
        display: inline-block !important;
    }

    .stat-card:first-of-type .stat-icon,
    .stat-card.is-featured .stat-icon {
        background: rgba(255, 255, 255, 0.22) !important;
        border: 1px solid rgba(255, 255, 255, 0.35) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
        color: #ffffff !important;
    }

    .stat-card:first-of-type .stat-icon i,
    .stat-card:first-of-type .stat-icon .bx,
    .stat-card:first-of-type .stat-icon [class*="bx"],
    .stat-card:first-of-type .stat-icon svg,
    .stat-card.is-featured .stat-icon i,
    .stat-card.is-featured .stat-icon .bx,
    .stat-card.is-featured .stat-icon [class*="bx"],
    .stat-card.is-featured .stat-icon svg {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        background-color: #ffffff !important;
        fill: #ffffff !important;
        opacity: 1 !important;
        visibility: visible !important;
        display: inline-block !important;
    }

    .stat-title {
        margin: 0 0 0.35rem;
        font-size: 0.85rem;
        font-weight: 650;
        color: var(--bx-ink-muted);
    }

    .stat-title a {
        color: var(--bx-ink-muted);
        text-decoration: none;
    }

    .stat-title a:hover {
        color: var(--bx-blue);
    }

    .stat-value {
        font-family: var(--font-display);
        font-size: 1.95rem;
        font-weight: 800;
        color: var(--bx-ink);
        line-height: 1.1;
        margin-bottom: 0.45rem;
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.78rem;
        font-weight: 650;
        color: var(--bx-emerald);
    }

    .stat-progress {
        margin-top: 0.75rem;
    }

    .progress-container {
        height: 6px;
        background: var(--bx-surface-3);
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: var(--bx-blue);
        border-radius: 999px;
    }

    /* ======================================================
       CONTENT CARDS & LISTS (TICKETS, TASKS, ACTIVITIES)
       Soft Status Badges & Clean Timeline
       ====================================================== */
    .content-section {
        margin-bottom: 1.75rem;
    }

    .content-grid {
        display: grid;
        gap: 1.25rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .content-card {
        background: var(--bx-surface);
        border: 1px solid var(--bx-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--bx-shadow-sm);
        display: flex;
        flex-direction: column;
        height: 480px;
        transition: all 0.2s ease;
    }

    .content-card:hover {
        border-color: var(--bx-border-strong);
        box-shadow: var(--bx-shadow-md);
    }

    .card-header {
        padding: 1.15rem 1.4rem;
        border-bottom: 1px solid var(--bx-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bx-surface);
    }

    .card-title {
        font-family: var(--font-display);
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--bx-ink);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-title i {
        color: var(--bx-blue);
        font-size: 1.15rem;
    }

    .card-action {
        color: var(--bx-blue);
        font-size: 0.8rem;
        font-weight: 650;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .card-action:hover {
        color: var(--bx-blue-dim);
    }

    .card-body {
        padding: 1.15rem 1.4rem;
        overflow-y: auto;
        flex: 1;
    }

    .list-item {
        padding: 0.85rem 1rem;
        border-radius: 10px;
        background: var(--bx-surface-2);
        border: 1px solid var(--bx-border);
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
    }

    .list-item:hover {
        background: var(--bx-surface-3);
        border-color: var(--bx-border-strong);
        transform: translateX(2px);
    }

    .list-item-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.65rem;
        margin-bottom: 0.35rem;
    }

    .list-item-title {
        font-family: var(--font-display);
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--bx-ink);
        margin: 0 0 0.25rem;
    }

    .list-item-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.78rem;
        color: var(--bx-ink-muted);
        font-weight: 500;
    }

    .list-item-meta span {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Soft Tinted Status Badges (Requirement 9) */
    .badge-low,
    .badge.bg-success {
        background: #ECFDF5 !important;
        color: #059669 !important;
        border: 1px solid #A7F3D0 !important;
        border-radius: 6px;
        font-weight: 650;
        font-size: 0.72rem;
        padding: 0.2rem 0.5rem;
    }

    .badge-medium,
    .badge.bg-warning {
        background: #FFFBEB !important;
        color: #D97706 !important;
        border: 1px solid #FDE68A !important;
        border-radius: 6px;
        font-weight: 650;
        font-size: 0.72rem;
        padding: 0.2rem 0.5rem;
    }

    .badge-high,
    .badge.bg-danger {
        background: #FEF2F2 !important;
        color: #DC2626 !important;
        border: 1px solid #FECACA !important;
        border-radius: 6px;
        font-weight: 650;
        font-size: 0.72rem;
        padding: 0.2rem 0.5rem;
    }

    .badge.bg-primary {
        background: #EFF6FF !important;
        color: #2563EB !important;
        border: 1px solid #BFDBFE !important;
        border-radius: 6px;
        font-weight: 650;
        font-size: 0.72rem;
        padding: 0.2rem 0.5rem;
    }

    .empty-state {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--bx-ink-muted);
    }

    .empty-state i {
        font-size: 2.5rem;
        color: var(--bx-blue);
        margin-bottom: 0.65rem;
        opacity: 0.85;
    }

    .empty-state p {
        font-size: 0.88rem;
        font-weight: 600;
        margin: 0;
    }

    /* Activities Timeline */
    .timeline {
        position: relative;
        padding-left: 1.15rem;
    }

    .timeline::before {
        content: "";
        position: absolute;
        left: 4px;
        top: 6px;
        bottom: 6px;
        width: 2px;
        background: var(--bx-border-strong);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1rem;
    }

    .timeline-item::after {
        content: "";
        position: absolute;
        left: -1.15rem;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--bx-surface);
        border: 2px solid var(--bx-blue);
    }

    .timeline-content {
        padding-left: 0.4rem;
    }

    .timeline-title {
        font-size: 0.85rem;
        font-weight: 650;
        color: var(--bx-ink);
        margin-bottom: 0.2rem;
    }

    .timeline-project {
        font-size: 0.78rem;
        color: var(--bx-blue);
        font-weight: 600;
        margin-bottom: 0.2rem;
    }

    .timeline-time {
        font-size: 0.72rem;
        color: var(--bx-ink-faint);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* ======================================================
       RESPONSIVE BREAKPOINTS
       ====================================================== */
    @media (max-width: 1199.98px) {
        .industry-overview-grid,
        .industry-chart-grid,
        .industry-feature-grid,
        .saas-revenue-strip,
        .saas-module-grid,
        .stats-grid,
        .content-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .saas-executive-grid,
        .saas-trend-board,
        .industry-main-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991.98px) {
        .industry-hero-card {
            grid-template-columns: 1fr;
        }

        .industry-hero-visual {
            display: none;
        }

        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .content-wrapper {
            padding: 1rem;
        }

        .industry-overview-grid,
        .saas-revenue-strip,
        .saas-prediction-grid,
        .saas-module-grid,
        .industry-chart-grid,
        .industry-feature-grid,
        .industry-presence-row,
        .stats-grid,
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Floating Background Elements -->
<div class="floating-elements">
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
</div>

<main id="main" class="main">

    <div class="content-wrapper">

        @php
            $dashboardTotalEmployees = $totalEmployees ?? 0;
            $dashboardPresentCount = $presentCount ?? 0;
            $dashboardTotalClient = $totalClient ?? 0;
            $dashboardTotalProject = $totalProject ?? 0;
            $dashboardPendingTask = $pendingTask ?? 0;
            $dashboardUnresolvedTicket = $unresolvedTicket ?? 0;
            $dashboardLateCount = $lateCount ?? 0;
            $dashboardAbsentCount = $absentCount ?? max($dashboardTotalEmployees - $dashboardPresentCount, 0);
            $dashboardAttendancePercent = $dashboardTotalEmployees > 0 ? round(($dashboardPresentCount / $dashboardTotalEmployees) * 100) : 0;
            $dashboardTaskScale = max($dashboardPendingTask, $dashboardUnresolvedTicket, $dashboardTotalProject, $dashboardTotalClient, 1);
            $dashboardPendingLeaves = optional($pendingLeaves ?? collect())->count();
            $dashboardFeatureScale = max(
                $dashboardTotalEmployees,
                $dashboardPresentCount,
                $dashboardPendingLeaves,
                $dashboardTotalProject,
                $dashboardPendingTask,
                $dashboardUnresolvedTicket,
                $dashboardTotalClient,
                1
            );
            $featureLinks = [
                ['label' => 'Employees', 'hint' => 'Team directory', 'icon' => 'bx-group', 'route' => 'employees.index', 'value' => $dashboardTotalEmployees],
                ['label' => 'Attendance', 'hint' => 'Today and reports', 'icon' => 'bx-calendar-check', 'route' => 'attendance.index', 'value' => $dashboardPresentCount],
                ['label' => 'Leaves', 'hint' => 'Requests and policy', 'icon' => 'bx-calendar-minus', 'route' => 'leaves.index', 'value' => $dashboardPendingLeaves],
                ['label' => 'Projects', 'hint' => 'Active work', 'icon' => 'bx-briefcase-alt-2', 'route' => 'projects.index', 'value' => $dashboardTotalProject],
                ['label' => 'Tasks', 'hint' => 'Pending actions', 'icon' => 'bx-task', 'route' => 'tasks.index', 'value' => $dashboardPendingTask],
                ['label' => 'Timesheet', 'hint' => 'Work logs', 'icon' => 'bx-time-five', 'route' => 'timelogs.index', 'value' => 'Log'],
                ['label' => 'Tickets', 'hint' => 'Support queue', 'icon' => 'bx-support', 'route' => 'tickets.index', 'value' => $dashboardUnresolvedTicket],
                ['label' => 'Clients', 'hint' => 'Client records', 'icon' => 'bx-user-circle', 'route' => 'clients.index', 'value' => $dashboardTotalClient],
                ['label' => 'Leads', 'hint' => 'Contacts pipeline', 'icon' => 'bx-target-lock', 'route' => 'leads.contacts.index', 'value' => 'CRM'],
                ['label' => 'Deals', 'hint' => 'Sales stages', 'icon' => 'bx-trending-up', 'route' => 'admin.deals.index', 'value' => 'Deal'],
                ['label' => 'Holidays', 'hint' => 'Calendar view', 'icon' => 'bx-calendar-star', 'route' => 'holidays.calendar', 'value' => 'Cal'],
                ['label' => 'Reports', 'hint' => 'Attendance report', 'icon' => 'bx-bar-chart-alt-2', 'route' => 'attendance.report', 'value' => 'View'],
                ['label' => 'Payroll', 'hint' => 'Salary operations', 'icon' => 'bx-wallet', 'route' => 'payroll.index', 'value' => 'Pay'],
                ['label' => 'Organization', 'hint' => 'Company directory', 'icon' => 'bx-sitemap', 'route' => 'organization.index', 'value' => 'Org'],
                ['label' => 'Awards', 'hint' => 'Recognition', 'icon' => 'bx-trophy', 'route' => 'awards.index', 'value' => 'HR'],
                ['label' => 'Departments', 'hint' => 'Team structure', 'icon' => 'bx-buildings', 'route' => 'departments.index', 'value' => 'Dept'],
                ['label' => 'Designations', 'hint' => 'Role hierarchy', 'icon' => 'bx-id-card', 'route' => 'designations.index', 'value' => 'Role'],
                ['label' => 'Modules', 'hint' => 'Feature controls', 'icon' => 'bx-grid-alt', 'route' => 'admin.modules.index', 'value' => 'Mod'],
                ['label' => 'Permissions', 'hint' => 'Access matrix', 'icon' => 'bx-lock-alt', 'route' => 'admin.role-permissions.index', 'value' => 'ACL'],
                ['label' => 'Settings', 'hint' => 'System setup', 'icon' => 'bx-cog', 'route' => 'admin.settings.app', 'value' => 'Set'],
            ];
            $featureLinks = collect($featureLinks)->filter(function ($link) use ($currentCompany) {
                $slug = match ($link['label']) {
                    'Employees' => 'employees',
                    'Attendance' => 'attendance',
                    'Leaves' => 'leaves',
                    'Projects' => 'projects',
                    'Tasks' => 'tasks',
                    'Timesheet' => 'timelogs',
                    'Tickets' => 'tickets',
                    'Clients' => 'clients',
                    'Leads' => 'leads',
                    'Deals' => 'deals',
                    'Holidays' => 'holidays',
                    'Reports' => 'reports',
                    'Payroll' => 'payroll',
                    'Organization' => 'organization',
                    'Awards' => 'awards',
                    'Departments' => 'departments',
                    'Designations' => 'designations',
                    default => strtolower($link['label']),
                };
                return $currentCompany ? $currentCompany->hasFeature($slug) : true;
            })->values()->all();

            $adminPieCharts = [
                ['label' => 'Attendance', 'slug' => 'attendance', 'hint' => "{$dashboardPresentCount} present / {$dashboardTotalEmployees} employees", 'route' => 'attendance.index', 'value' => $dashboardPresentCount, 'percent' => $dashboardAttendancePercent, 'color' => '#22D3EE'],
                ['label' => 'Projects', 'slug' => 'projects', 'hint' => "{$dashboardTotalProject} active projects", 'route' => 'projects.index', 'value' => $dashboardTotalProject, 'percent' => round(($dashboardTotalProject / $dashboardFeatureScale) * 100), 'color' => '#2F6BFF'],
                ['label' => 'Tasks', 'slug' => 'tasks', 'hint' => "{$dashboardPendingTask} pending tasks", 'route' => 'tasks.index', 'value' => $dashboardPendingTask, 'percent' => round(($dashboardPendingTask / $dashboardFeatureScale) * 100), 'color' => '#8B5CF6'],
                ['label' => 'Tickets', 'slug' => 'tickets', 'hint' => "{$dashboardUnresolvedTicket} unresolved tickets", 'route' => 'tickets.index', 'value' => $dashboardUnresolvedTicket, 'percent' => round(($dashboardUnresolvedTicket / $dashboardFeatureScale) * 100), 'color' => '#FB7185'],
                ['label' => 'Clients', 'slug' => 'clients', 'hint' => "{$dashboardTotalClient} client records", 'route' => 'clients.index', 'value' => $dashboardTotalClient, 'percent' => round(($dashboardTotalClient / $dashboardFeatureScale) * 100), 'color' => '#38BDF8'],
                ['label' => 'Leaves', 'slug' => 'leaves', 'hint' => "{$dashboardPendingLeaves} pending requests", 'route' => 'leaves.index', 'value' => $dashboardPendingLeaves, 'percent' => round(($dashboardPendingLeaves / $dashboardFeatureScale) * 100), 'color' => '#A78BFA'],
                ['label' => 'Employees', 'slug' => 'employees', 'hint' => "{$dashboardTotalEmployees} total employees", 'route' => 'employees.index', 'value' => $dashboardTotalEmployees, 'percent' => round(($dashboardTotalEmployees / $dashboardFeatureScale) * 100), 'color' => '#34D399'],
                ['label' => 'Reports', 'slug' => 'reports', 'hint' => 'Attendance and operations reporting', 'route' => 'attendance.report', 'value' => 'View', 'percent' => max(35, $dashboardAttendancePercent), 'color' => '#6366F1'],
            ];
            $adminPieCharts = collect($adminPieCharts)->filter(function ($chart) use ($currentCompany) {
                return $currentCompany ? $currentCompany->hasFeature($chart['slug']) : true;
            })->values()->all();

            $safeTableSum = function (string $table, string $column): float {
                try {
                    return \Illuminate\Support\Facades\Schema::hasTable($table) && \Illuminate\Support\Facades\Schema::hasColumn($table, $column)
                        ? (float) \Illuminate\Support\Facades\DB::table($table)->sum($column)
                        : 0;
                } catch (\Throwable $e) {
                    return 0;
                }
            };
            $safeTableCount = function (string $table): int {
                try {
                    return \Illuminate\Support\Facades\Schema::hasTable($table)
                        ? (int) \Illuminate\Support\Facades\DB::table($table)->count()
                        : 0;
                } catch (\Throwable $e) {
                    return 0;
                }
            };
            $projectBudgetTotal = $safeTableSum('projects', 'project_budget');
            $dealPipelineValue = $safeTableSum('deals', 'value');
            $contractRevenueValue = $safeTableSum('contracts', 'contract_value');
            $expenseInvestmentValue = $safeTableSum('expenses', 'price');
            $subscriptionRevenueValue = $safeTableSum('company_subscriptions', 'price');
            $invoiceRevenueValue = $safeTableSum('invoices', 'total');
            $paymentRevenueValue = $safeTableSum('payments', 'amount');
            $grossRevenue = $contractRevenueValue + $dealPipelineValue + $subscriptionRevenueValue + $invoiceRevenueValue + $paymentRevenueValue;
            $netOutlook = $grossRevenue - $expenseInvestmentValue;
            $budgetUtilization = $projectBudgetTotal > 0 ? round(($expenseInvestmentValue / $projectBudgetTotal) * 100) : 0;
            $currency = '₹';
            $formatMoney = fn ($value) => $currency . number_format((float) $value, 0);
            $financeCards = [
                ['label' => 'Total Revenue Outlook', 'value' => $formatMoney($grossRevenue), 'meta' => 'Deals, contracts, invoices, payments'],
                ['label' => 'Investment / Expenses', 'value' => $formatMoney($expenseInvestmentValue), 'meta' => $budgetUtilization . '% of project budget'],
                ['label' => 'Project Budget', 'value' => $formatMoney($projectBudgetTotal), 'meta' => 'Allocated delivery budget'],
                ['label' => 'Net Business Outlook', 'value' => $formatMoney($netOutlook), 'meta' => $netOutlook >= 0 ? 'Positive operating signal' : 'Needs revenue recovery'],
            ];
            $deliveryRisk = $dashboardTotalProject > 0 ? round(($dashboardUnresolvedTicket + $dashboardPendingTask) / max($dashboardTotalProject, 1)) : 0;
            $peopleRisk = $dashboardTotalEmployees > 0 ? round((($dashboardAbsentCount + $dashboardLateCount) / $dashboardTotalEmployees) * 100) : 0;
            $growthSignal = $dashboardTotalClient > 0 ? round(($dealPipelineValue / max($dashboardTotalClient, 1))) : 0;
            $predictionCards = [
                ['label' => 'Delivery Prediction', 'value' => $deliveryRisk > 8 ? 'High Load' : ($deliveryRisk > 3 ? 'Watch Queue' : 'Healthy'), 'hint' => "{$dashboardPendingTask} tasks and {$dashboardUnresolvedTicket} tickets against {$dashboardTotalProject} projects.", 'risk' => $deliveryRisk > 8 ? 'high' : ($deliveryRisk > 3 ? 'mid' : 'low')],
                ['label' => 'People Prediction', 'value' => $peopleRisk > 35 ? 'Attendance Risk' : ($peopleRisk > 15 ? 'Monitor' : 'Stable'), 'hint' => "{$dashboardPresentCount} present, {$dashboardLateCount} late, {$dashboardAbsentCount} absent today.", 'risk' => $peopleRisk > 35 ? 'high' : ($peopleRisk > 15 ? 'mid' : 'low')],
                ['label' => 'Revenue Prediction', 'value' => $netOutlook >= 0 ? 'Profitable Outlook' : 'Cost Pressure', 'hint' => 'Projected revenue minus tracked investments and expenses.', 'risk' => $netOutlook >= 0 ? 'low' : 'high'],
                ['label' => 'Pipeline Prediction', 'value' => $growthSignal > 0 ? $formatMoney($growthSignal) . ' / client' : 'Build Pipeline', 'hint' => 'Average pipeline value per active client.', 'risk' => $growthSignal > 0 ? 'low' : 'mid'],
            ];
            $moduleRouteFallbacks = [
                'dashboard' => 'dashboard',
                'employees' => 'employees.index',
                'attendance' => 'attendance.index',
                'leaves' => 'leaves.index',
                'holidays' => 'holidays.index',
                'awards' => 'awards.index',
                'reports' => 'attendance.report',
                'clients' => 'clients.index',
                'projects' => 'projects.index',
                'tasks' => 'tasks.index',
                'timelogs' => 'timelogs.index',
                'payroll' => 'payroll.index',
                'leads' => 'leads.contacts.index',
                'tickets' => 'tickets.index',
                'settings' => 'admin.settings.app',
                'organization' => 'organization.index',
                'departments' => 'departments.index',
                'designations' => 'designations.index',
                'collaborating-companies' => 'collaborating-companies.index',
            ];
            $moduleRouteParams = [
                'admin.role-accounts.index' => ['role' => 'hr'],
            ];
            $safeRouteUrl = function (?string $routeName) use ($moduleRouteParams): ?string {
                if (! $routeName || ! Route::has($routeName)) {
                    return null;
                }

                try {
                    $route = Route::getRoutes()->getByName($routeName);
                    $requiredParameters = $route ? $route->parameterNames() : [];
                    $params = $moduleRouteParams[$routeName] ?? [];

                    foreach ($requiredParameters as $parameter) {
                        if (! array_key_exists($parameter, $params)) {
                            return null;
                        }
                    }

                    return route($routeName, $params);
                } catch (\Throwable $e) {
                    return null;
                }
            };
            $moduleMetricMap = [
                'employees' => $dashboardTotalEmployees,
                'attendance' => $dashboardPresentCount,
                'leaves' => $dashboardPendingLeaves,
                'clients' => $dashboardTotalClient,
                'projects' => $dashboardTotalProject,
                'tasks' => $dashboardPendingTask,
                'tickets' => $dashboardUnresolvedTicket,
                'timelogs' => $safeTableCount('time_logs') ?: $safeTableCount('timelogs'),
                'payroll' => $safeTableCount('payrolls'),
                'leads' => $safeTableCount('lead_contacts'),
                'awards' => $safeTableCount('awards'),
                'holidays' => $safeTableCount('holidays'),
                'departments' => $safeTableCount('departments'),
                'designations' => $safeTableCount('designations'),
                'organization' => $dashboardTotalEmployees,
                'settings' => $safeTableCount('modules'),
            ];
            try {
                $activeModules = \App\Models\Module::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
            } catch (\Throwable $e) {
                $activeModules = collect();
            }
            if ($currentCompany && method_exists($currentCompany, 'hasFeature')) {
                $activeModules = $activeModules->filter(fn ($m) => $currentCompany->hasFeature($m->slug))->values();
            }
            $moduleScale = max(collect($moduleMetricMap)->filter(fn ($value) => is_numeric($value))->max() ?? 1, 1);
            $autoModuleCards = $activeModules->map(function ($module, $index) use ($moduleRouteFallbacks, $moduleMetricMap, $moduleScale, $safeRouteUrl) {
                $route = $module->route_name ?: ($moduleRouteFallbacks[$module->slug] ?? null);
                $url = $safeRouteUrl($route);
                $metric = $moduleMetricMap[$module->slug] ?? 1;
                return [
                    'name' => $module->name,
                    'slug' => $module->slug,
                    'icon' => $module->icon ?: 'bx-grid-alt',
                    'route' => $route,
                    'url' => $url,
                    'value' => $metric,
                    'percent' => is_numeric($metric) ? round(((float) $metric / $moduleScale) * 100) : 35,
                    'color' => ['#2F6BFF', '#8B5CF6', '#22D3EE', '#34D399', '#FBBF24', '#FB7185', '#38BDF8', '#6366F1'][$index % 8],
                ];
            })->filter(fn ($module) => $module['url'])->values();
            if ($autoModuleCards->isEmpty()) {
                $autoModuleCards = collect($moduleRouteFallbacks)->filter(function ($route, $slug) use ($currentCompany) {
                    return $currentCompany ? $currentCompany->hasFeature($slug) : true;
                })->map(function ($route, $slug) use ($moduleMetricMap, $moduleScale, $safeRouteUrl) {
                    $url = $safeRouteUrl($route);
                    $metric = $moduleMetricMap[$slug] ?? 1;

                    return [
                        'name' => \Illuminate\Support\Str::headline($slug),
                        'slug' => $slug,
                        'icon' => 'bx-grid-alt',
                        'route' => $route,
                        'url' => $url,
                        'value' => $metric,
                        'percent' => is_numeric($metric) ? round(((float) $metric / $moduleScale) * 100) : 35,
                        'color' => '#2563eb',
                    ];
                })->filter(fn ($module) => $module['url'])->take(8)->values();
            }
            $sparkValues = [
                max(10, min(100, $dashboardAttendancePercent)),
                max(10, min(100, round(($dashboardTotalProject / $dashboardFeatureScale) * 100))),
                max(10, min(100, round(($dashboardPendingTask / $dashboardFeatureScale) * 100))),
                max(10, min(100, round(($dashboardUnresolvedTicket / $dashboardFeatureScale) * 100))),
                max(10, min(100, $budgetUtilization)),
                max(10, min(100, $activeModules->count() * 4)),
            ];
        @endphp

        <section class="industry-dashboard-shell">
            <div class="industry-hero-card">
                <div class="industry-hero-copy">
                    <span class="industry-eyebrow"><i class="bx bx-check-shield"></i> Admin Workspace</span>
                    <h1>Admin Workspace Dashboard</h1>
                    <p>Real-time command center for project progression, employee presence, pending work queue, and enterprise operations.</p>
                    <div class="industry-actions">
                        @if(Route::has('projects.create'))
                            <a href="{{ route('projects.create') }}" class="industry-btn industry-btn-primary">
                                <i class="bx bx-plus"></i> Add Project
                            </a>
                        @endif
                        @if(Route::has('tasks.create'))
                            <a href="{{ route('tasks.create') }}" class="industry-btn industry-btn-light">
                                <i class="bx bx-task"></i> New Task
                            </a>
                        @endif
                        @if(Route::has('attendance.report'))
                            <a href="{{ route('attendance.report') }}" class="industry-btn industry-btn-outline">
                                <i class="bx bx-bar-chart-alt-2"></i> Attendance Report
                            </a>
                        @endif
                    </div>
                </div>
                <div class="industry-hero-visual">
                    <img src="{{ asset('admin/assets/img/illustrations/dashboard-ui-preview.png') }}" alt="Admin Dashboard overview">
                </div>
            </div>

            <div class="industry-overview-grid">
                <a href="{{ Route::has('projects.index') ? route('projects.index') : '#' }}" class="industry-metric-card kpi-card-projects">
                    <div class="kpi-card-top">
                        <span>Total Projects</span>
                        <div class="kpi-icon-box kpi-blue">
                            <i class="bx bx-briefcase-alt-2"></i>
                        </div>
                    </div>
                    <strong class="kpi-number">{{ $dashboardTotalProject }}</strong>
                    <div class="kpi-card-footer">
                        <small style="color: var(--bx-blue);"><i class="bx bx-up-arrow-alt"></i> Open project workspace</small>
                        <i class="bx bx-right-arrow-alt industry-arrow"></i>
                    </div>
                </a>
                <a href="{{ Route::has('tasks.index') ? route('tasks.index') : '#' }}" class="industry-metric-card kpi-card-tasks">
                    <div class="kpi-card-top">
                        <span>Pending Tasks</span>
                        <div class="kpi-icon-box kpi-violet">
                            <i class="bx bx-task"></i>
                        </div>
                    </div>
                    <strong class="kpi-number">{{ $dashboardPendingTask }}</strong>
                    <div class="kpi-card-footer">
                        <small><i class="bx bx-time"></i> Active in queue</small>
                        <i class="bx bx-right-arrow-alt industry-arrow"></i>
                    </div>
                </a>
                <a href="{{ Route::has('tickets.index') ? route('tickets.index') : '#' }}" class="industry-metric-card kpi-card-tickets">
                    <div class="kpi-card-top">
                        <span>Open Tickets</span>
                        <div class="kpi-icon-box kpi-cyan">
                            <i class="bx bx-support"></i>
                        </div>
                    </div>
                    <strong class="kpi-number">{{ $dashboardUnresolvedTicket }}</strong>
                    <div class="kpi-card-footer">
                        <small><i class="bx bx-error-circle"></i> Requires attention</small>
                        <i class="bx bx-right-arrow-alt industry-arrow"></i>
                    </div>
                </a>
                <a href="{{ Route::has('attendance.report') ? route('attendance.report') : '#' }}" class="industry-metric-card kpi-card-attendance">
                    <div class="kpi-card-top">
                        <span>Attendance</span>
                        <div class="kpi-icon-box kpi-emerald">
                            <i class="bx bx-user-check"></i>
                        </div>
                    </div>
                    <strong class="kpi-number">{{ $dashboardAttendancePercent }}%</strong>
                    <div class="kpi-card-footer">
                        <small style="color: var(--bx-emerald);"><i class="bx bx-check"></i> {{ $dashboardPresentCount }} present today</small>
                        <i class="bx bx-right-arrow-alt industry-arrow"></i>
                    </div>
                </a>
            </div>

            <div class="saas-executive-grid">
                <div class="industry-panel">
                    <div class="industry-panel-head">
                        <div>
                            <h3>Executive Business Model</h3>
                            <p>Revenue, investment, budget, and net outlook from available finance data.</p>
                        </div>
                        <a href="{{ Route::has('admin.deals.index') ? route('admin.deals.index') : '#' }}">Pipeline</a>
                    </div>
                    <div class="saas-revenue-strip">
                        @foreach($financeCards as $card)
                            <div class="saas-money-card">
                                <span>{{ $card['label'] }}</span>
                                <strong>{{ $card['value'] }}</strong>
                                <em>{{ $card['meta'] }}</em>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="industry-panel">
                    <div class="industry-panel-head">
                        <div>
                            <h3>Feature Predictions</h3>
                            <p>Simple operating signals generated from current dashboard data.</p>
                        </div>
                    </div>
                    <div class="saas-prediction-grid">
                        @foreach($predictionCards as $card)
                            <div class="saas-insight-card">
                                <span>{{ $card['label'] }}</span>
                                <strong>{{ $card['value'] }}</strong>
                                <p>{{ $card['hint'] }}</p>
                                <div class="mt-2">
                                    <span class="saas-risk-pill saas-risk-{{ $card['risk'] }}">
                                        <i class="bx bx-pulse"></i> {{ ucfirst($card['risk']) }} signal
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="saas-trend-board">
                <div class="industry-panel saas-line-card">
                    <div class="industry-panel-head">
                        <div>
                            <h3>Operating Trend Graph</h3>
                            <p>Compact trend generated from attendance, projects, tasks, tickets, investment, and modules.</p>
                        </div>
                    </div>
                    <div class="saas-sparkline" aria-label="Operating trend graph">
                        @foreach($sparkValues as $spark)
                            <span style="--spark: {{ $spark }}%"></span>
                        @endforeach
                    </div>
                </div>

                <div class="industry-panel">
                    <div class="industry-panel-head">
                        <div>
                            <h3>SaaS Health Score</h3>
                            <p>Blended score across team presence, workload, revenue outlook, and enabled modules.</p>
                        </div>
                    </div>
                    @php
                        $healthScore = max(0, min(100, round(
                            ($dashboardAttendancePercent * .35)
                            + (max(0, 100 - min(100, $deliveryRisk * 8)) * .25)
                            + (($netOutlook >= 0 ? 100 : 45) * .2)
                            + (min(100, $activeModules->count() * 5) * .2)
                        )));
                    @endphp
                    <div class="industry-gauge" style="--value-deg: {{ round($healthScore * 1.8) }}deg">
                        <div>
                            <strong>{{ $healthScore }}%</strong>
                            <span>Health</span>
                        </div>
                    </div>
                    <div class="industry-presence-row">
                        <span><b>{{ $activeModules->count() }}</b> Modules</span>
                        <span><b>{{ $dashboardPendingTask }}</b> Tasks</span>
                        <span><b>{{ $dashboardUnresolvedTicket }}</b> Tickets</span>
                    </div>
                </div>
            </div>

            <div class="industry-main-grid">
                <div class="industry-panel industry-analytics-card">
                    <div class="industry-panel-head">
                        <div>
                            <h3>Work Analytics</h3>
                            <p>Quick health snapshot across core modules.</p>
                        </div>
                        <a href="{{ Route::has('attendance.report') ? route('attendance.report') : '#' }}">View Report</a>
                    </div>
                    <div class="industry-bars" aria-label="Dashboard analytics chart">
                        <div class="industry-bar" style="--bar: {{ max(18, min(100, round(($dashboardTotalProject / $dashboardTaskScale) * 100))) }}%">
                            <span></span><label>Projects</label>
                        </div>
                        <div class="industry-bar" style="--bar: {{ max(18, min(100, round(($dashboardPendingTask / $dashboardTaskScale) * 100))) }}%">
                            <span></span><label>Tasks</label>
                        </div>
                        <div class="industry-bar" style="--bar: {{ max(18, min(100, round(($dashboardUnresolvedTicket / $dashboardTaskScale) * 100))) }}%">
                            <span></span><label>Tickets</label>
                        </div>
                        <div class="industry-bar" style="--bar: {{ max(18, min(100, round(($dashboardTotalClient / $dashboardTaskScale) * 100))) }}%">
                            <span></span><label>Clients</label>
                        </div>
                        <div class="industry-bar is-muted" style="--bar: {{ max(18, min(100, $dashboardAttendancePercent)) }}%">
                            <span></span><label>Attendance</label>
                        </div>
                    </div>
                </div>

                <div class="industry-panel industry-attendance-card">
                    <div class="industry-panel-head">
                        <div>
                            <h3>Team Presence</h3>
                            <p>Today attendance summary.</p>
                        </div>
                        <a href="{{ Route::has('attendance.index') ? route('attendance.index') : '#' }}">Open</a>
                    </div>
                    <div class="industry-gauge" style="--value-deg: {{ round($dashboardAttendancePercent * 1.8) }}deg">
                        <div>
                            <strong>{{ $dashboardAttendancePercent }}%</strong>
                            <span>Present</span>
                        </div>
                    </div>
                    <div class="industry-presence-row">
                        <span><b>{{ $dashboardPresentCount }}</b> Present</span>
                        <span><b>{{ $dashboardLateCount }}</b> Late</span>
                        <span><b>{{ $dashboardAbsentCount }}</b> Absent</span>
                    </div>
                </div>
            </div>

            @if($autoModuleCards->isNotEmpty())
                <div class="industry-panel">
                    <div class="industry-panel-head">
                        <div>
                            <h3>Automatic Feature Analytics</h3>
                            <p>Generated from active admin modules. New active modules appear here when their route is available.</p>
                        </div>
                        <a href="{{ Route::has('admin.modules.index') ? route('admin.modules.index') : '#' }}">Manage Modules</a>
                    </div>
                    <div class="saas-module-grid">
                        @foreach($autoModuleCards as $module)
                            <a href="{{ $module['url'] }}" class="saas-module-card">
                                <div class="saas-module-head">
                                    <span class="saas-module-icon"><i class="bx {{ $module['icon'] }}"></i></span>
                                    <div>
                                        <h4>{{ $module['name'] }}</h4>
                                        <small>{{ $module['slug'] }}</small>
                                    </div>
                                </div>
                                <div class="saas-module-donut" style="--percent: {{ max(3, min(100, $module['percent'])) }}; --accent: {{ $module['color'] }};">
                                    <strong>{{ is_numeric($module['value']) ? $module['value'] : $module['value'] }}</strong>
                                </div>
                                <div class="saas-module-meta">
                                    <small>{{ max(3, min(100, $module['percent'])) }}% signal</small>
                                    <em>Open</em>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="industry-panel">
                <div class="industry-panel-head">
                    <div>
                        <h3>Module Intelligence</h3>
                        <p>Pie-style module coverage across the admin workspace.</p>
                    </div>
                    <a href="{{ Route::has('attendance.report') ? route('attendance.report') : '#' }}">Analytics</a>
                </div>
                <div class="industry-chart-grid">
                    @foreach($adminPieCharts as $chart)
                        @if(Route::has($chart['route']))
                            <div class="industry-chart-card">
                                <div class="industry-chart-body">
                                    <div class="industry-donut" style="--percent: {{ max(3, min(100, $chart['percent'])) }}; --accent: {{ $chart['color'] }};">
                                        <strong>{{ is_numeric($chart['value']) ? $chart['value'] : $chart['value'] }}</strong>
                                    </div>
                                    <div class="industry-chart-meta">
                                        <h4>{{ $chart['label'] }}</h4>
                                        <p>{{ $chart['hint'] }}</p>
                                    </div>
                                    <a href="{{ route($chart['route']) }}" class="industry-chart-link">Open {{ $chart['label'] }}</a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="industry-panel industry-feature-panel">
                <div class="industry-panel-head">
                    <div>
                        <h3>Feature Shortcuts</h3>
                        <p>Every core module is one click away.</p>
                    </div>
                </div>
                <div class="industry-feature-grid">
                    @foreach($featureLinks as $feature)
                        @if(Route::has($feature['route']))
                            <a href="{{ route($feature['route']) }}" class="industry-feature-card">
                                <span class="industry-feature-icon"><i class="bx {{ $feature['icon'] }}"></i></span>
                                <span class="industry-feature-copy">
                                    <strong>{{ $feature['label'] }}</strong>
                                    <small>{{ $feature['hint'] }}</small>
                                </span>
                                <em>{{ $feature['value'] }}</em>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-card">
                <div class="row g-0">
                    <div class="col-lg-7">
                        <div class="welcome-content">
                            <h1 class="welcome-title">{{ $currentCompany?->greeting_message ?: 'Welcome to' }} {{ $currentCompany?->display_name ?? 'Bitroxia' }} Dashboard</h1>
                            <p class="welcome-text">Manage your projects, team, and clients efficiently with our comprehensive dashboard. Track progress, monitor performance, and make data-driven decisions.</p>
                            <div class="welcome-badges">
                                <div class="welcome-badge">
                                    <i class="bx bx-trending-up"></i>
                                    Real-time Analytics
                                </div>
                                <div class="welcome-badge">
                                    <i class="bx bx-shield-quarter"></i>
                                    Secure & Reliable
                                </div>
                                <div class="welcome-badge">
                                    <i class="bx bx-rocket"></i>
                                    Performance Boost
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="welcome-illustration">
                            <img src="{{ asset('admin/assets/img/illustrations/dashboard-ui-preview.png')}}" class="img-fluid" alt="Dashboard preview"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Section - DIVISION BY ZERO FIXED -->
        <div class="stats-section">
            <div class="stats-grid">
                @php
                    // Safe calculation for attendance percentage
                    $totalEmployees = $totalEmployees ?? 0;
                    $presentCount = $presentCount ?? 0;
                    $totalClient = $totalClient ?? 0;
                    $totalProject = $totalProject ?? 0;

                    // Calculate attendance percentage safely
                    $attendancePercentage = $totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100) : 0;
                    $attendanceWidth = $totalEmployees > 0 ? ($presentCount / $totalEmployees) * 100 : 0;
                @endphp

                <!-- Total Employees -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bx bx-group"></i>
                        </div>
                        <div class="stat-dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('employees.index') }}">
                                    <i class="bx bx-list-ul"></i> View All
                                </a>
                                <!-- <a class="dropdown-item" href="#">
                                    <i class="bx bx-download"></i> Export Report
                                </a> -->
                            </div>
                        </div>
                    </div>
                    <p class="stat-title"><a href="{{ route('employees.index') }}">Total Employees</a></p>
                    <div class="stat-value">{{ $totalEmployees }}</div>
                    <div class="stat-trend positive">
                        <i class="bx bx-up-arrow-alt"></i>
                        <span>All Active</span>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>

                <!-- Total Attendance - DIVISION BY ZERO FIXED -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bx bx-calendar-check"></i>
                        </div>
                        <div class="stat-dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('attendance.report') }}">
                                    <i class="bx bx-bar-chart"></i> View Report
                                </a>
                                <!-- <a class="dropdown-item" href="#">
                                    <i class="bx bx-time"></i> Daily Logs
                                </a> -->
                            </div>
                        </div>
                    </div>
                    <p class="stat-title"><a href="{{ route('attendance.report') }}">Today's Attendance</a></p>
                    <div class="stat-value">{{ $presentCount }}/{{ $totalEmployees }}</div>
                    <div class="stat-trend positive">
                        <i class="bx bx-up-arrow-alt"></i>
                        <span>{{ $attendancePercentage }}% Present</span>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-container">
                            <div class="progress-bar" style="width: {{ $attendanceWidth }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Total Clients -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bx bx-user-circle"></i>
                        </div>
                        <div class="stat-dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('clients.index') }}">
                                    <i class="bx bx-list-ul"></i> View All
                                </a>
                                <!-- <a class="dropdown-item" href="#">
                                    <i class="bx bx-plus-circle"></i> Add New
                                </a> -->
                            </div>
                        </div>
                    </div>
                    <p class="stat-title"><a href="{{ route('clients.index') }}">Active Clients</a></p>
                    <div class="stat-value">{{ $totalClient }}</div>
                    <div class="stat-trend positive">
                        <i class="bx bx-up-arrow-alt"></i>
                        <span>All Engaged</span>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>

                <!-- Total Projects -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bx bx-briefcase-alt"></i>
                        </div>
                        <div class="stat-dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('projects.index') }}">
                                    <i class="bx bx-list-ul"></i> View All
                                </a>
                                <!-- <a class="dropdown-item" href="#">
                                    <i class="bx bx-plus-circle"></i> Create New
                                </a> -->
                            </div>
                        </div>
                    </div>
                    <p class="stat-title"><a href="{{ route('projects.index') }}">Active Projects</a></p>
                    <div class="stat-value">{{ $totalProject }}</div>
                    <div class="stat-trend positive">
                        <i class="bx bx-up-arrow-alt"></i>
                        <span>All Running</span>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="content-section">
            <div class="content-grid">
                <!-- Open Tickets -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bx bx-message-square-dots"></i>
                            Open Tickets
                        </div>
                        <a href="{{ route('tickets.index', ['status' => 'open']) }}" class="card-action">
                            View All <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        @forelse($openTickets ?? [] as $ticket)
                            <div class="list-item">
                                <div class="list-item-header">
                                    <div>
                                        <h6 class="list-item-title">{{ $ticket->subject ?? 'No Subject' }}</h6>
                                        <div class="list-item-meta">
                                            <span><i class="bx bx-user"></i> {{ $ticket->requester_name ?? 'Unknown' }}</span>
                                            <span><i class="bx bx-folder"></i> {{ $ticket->project?->name ?? 'No Project' }}</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-{{ strtolower($ticket->priority ?? 'low') }}">
                                        {{ ucfirst($ticket->priority ?? 'Low') }}
                                    </span>
                                </div>
                                <div class="list-item-meta">
                                    <span><i class="bx bx-calendar"></i> {{ \Carbon\Carbon::parse($ticket->created_at ?? now())->format('d M, Y') }}</span>
                                    <span><i class="bx bx-time"></i> {{ \Carbon\Carbon::parse($ticket->created_at ?? now())->format('h:i A') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="bx bx-message-square-check"></i>
                                <p>All tickets are resolved! 🎉</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pending Tasks -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bx bx-list-check"></i>
                            Pending Tasks
                        </div>
                        <a href="{{ route('tasks.index', ['exclude_completed' => true]) }}" class="card-action">
                            View All <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        @forelse($pendingTasksTotal ?? [] as $task)
                            <div class="list-item">
                                <div class="list-item-header">
                                    <div>
                                        <h6 class="list-item-title">{{ $task->title ?? 'N/A' }}</h6>
                                        <div class="list-item-meta">
                                            <span><i class="bx bx-folder"></i> {{ $task->project->name ?? 'N/A' }}</span>
                                            <span><i class="bx bx-calendar"></i> {{ \Carbon\Carbon::parse($task->start_date ?? now())->format('d M') }}</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-low">
                                        {{ $task->status ?? 'Pending' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="bx bx-check-circle"></i>
                                <p>No pending tasks! Great work! 🚀</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Project Activities -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bx bx-pulse"></i>
                            Recent Activities
                        </div>
                        <a href="#" class="card-action">
                            View All <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @forelse($activities ?? [] as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-content">
                                        <div class="timeline-title">{{ $activity->activity ?? 'No activity' }}</div>
                                        <div class="timeline-project">{{ $activity->project_name ?? 'N/A' }}</div>
                                        <div class="timeline-time">
                                            <i class="bx bx-time"></i>
                                            {{ \Carbon\Carbon::parse($activity->created_at ?? now())->format('h:i A • d M') }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="bx bx-time"></i>
                                    <p>No recent activities</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</main>

<script>
    // Initialize animations and interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Animate numbers in stat cards
        const statValues = document.querySelectorAll('.stat-value');
        statValues.forEach(value => {
            const originalText = value.textContent;
            const isFraction = originalText.includes('/');

            if (isFraction) {
                const [numerator, denominator] = originalText.split('/');
                animateFraction(value, parseInt(numerator) || 0, parseInt(denominator) || 1);
            } else {
                animateNumber(value, parseInt(originalText.replace(/\D/g, '')) || 0);
            }
        });

        function animateNumber(element, target) {
            let current = 0;
            const increment = target / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current).toLocaleString();
                }
            }, 40);
        }

        function animateFraction(element, numerator, denominator) {
            let currentNum = 0;
            let currentDen = denominator;
            const incrementNum = numerator / 20;

            const timer = setInterval(() => {
                currentNum += incrementNum;

                if (currentNum >= numerator) {
                    element.textContent = `${numerator}/${denominator}`;
                    clearInterval(timer);
                } else {
                    element.textContent = `${Math.floor(currentNum)}/${denominator}`;
                }
            }, 50);
        }

        // Add hover effects to cards
        const cards = document.querySelectorAll('.stat-card, .content-card, .industry-metric-card, .industry-panel, .industry-feature-card, .industry-chart-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '10';
            });

            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '1';
            });
        });

        // Add click ripple effect to tabs
        const tabs = document.querySelectorAll('.nav-link');
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');

                // Create ripple effect
                const rect = this.getBoundingClientRect();
                const ripple = document.createElement('span');
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.6);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    width: ${size}px;
                    height: ${size}px;
                    top: ${y}px;
                    left: ${x}px;
                    pointer-events: none;
                    z-index: 0;
                `;

                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Add parallax effect to floating elements
        document.addEventListener('mousemove', function(e) {
            const x = (e.clientX / window.innerWidth - 0.5) * 20;
            const y = (e.clientY / window.innerHeight - 0.5) * 20;

            const elements = document.querySelectorAll('.floating-element');
            elements.forEach((element, index) => {
                const speed = 0.5 + (index * 0.2);
                element.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        });

        // Add intersection observer for scroll animations. Keep mobile panels visible so
        // dashboard sections do not disappear when mobile browsers delay observers.
        const animatedCards = document.querySelectorAll('.stat-card, .content-card, .industry-metric-card, .industry-panel, .industry-feature-card, .industry-chart-card');
        if (window.innerWidth > 575 && 'IntersectionObserver' in window) {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            animatedCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        } else {
            animatedCards.forEach(card => {
                card.style.opacity = '1';
                card.style.transform = 'none';
            });
        }

        // Update active tab based on current route
        function updateActiveTab() {
            const currentPath = window.location.pathname;
            tabs.forEach(tab => {
                const href = tab.getAttribute('href');
                if (href && currentPath.includes(href.split('?')[0])) {
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                }
            });
        }

        updateActiveTab();
    });

    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
</script>

@endsection
