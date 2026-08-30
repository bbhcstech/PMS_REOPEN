<!-- //dashboard blade page -->


@extends('admin.layout.app')

@section('title', 'Admin Dashboard')

@section('content')

<style>
    /* ===== LUXURY DASHBOARD THEME VARIABLES & CORE ===== */
    :root {
        --emerald-primary: #0f744c;
        --emerald-dark: #073a26;
        --emerald-deep: #05291b;
        --emerald-light: #10b981;
        --emerald-soft: #e4f3eb;
        --emerald-glow: rgba(16, 185, 129, 0.25);
        --purple-accent: #7c3aed;
        --blue-accent: #2563eb;
        --amber-accent: #f59e0b;
        --rose-accent: #ef4444;

        --slate-dark: #0f172a;
        --slate-body: #334155;
        --slate-muted: #64748b;
        --slate-light: #f8fafc;

        --glass-surface: linear-gradient(145deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 249, 0.94) 100%);
        --glass-border: 1px solid rgba(255, 255, 255, 0.85);
        --card-shadow-sm: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 4px 10px rgba(0, 0, 0, 0.02);
        --card-shadow-md: 0 20px 45px -10px rgba(15, 116, 76, 0.08), 0 6px 18px rgba(0, 0, 0, 0.03);
        --card-shadow-lg: 0 30px 70px -15px rgba(15, 116, 76, 0.16), 0 12px 30px rgba(0, 0, 0, 0.05);

        --font-family-main: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
    }

    * {
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #f1f5f3 0%, #e6eee8 50%, #f7faf8 100%) !important;
        font-family: var(--font-family-main);
        color: var(--slate-dark);
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
    }

    /* ===== ANIMATIONS & SHIMMER EFFECTS ===== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(24px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes floatHero {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        50% {
            transform: translateY(-12px) rotate(1.5deg);
        }
    }

    @keyframes shimmerSweep {
        0% {
            transform: translateX(-150%) skewX(-20deg);
            opacity: 0;
        }
        50% {
            opacity: 1;
        }
        100% {
            transform: translateX(250%) skewX(-20deg);
            opacity: 0;
        }
    }

    @keyframes gradientShift {
        0%, 100% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
    }

    @keyframes progressFill {
        from {
            width: 0;
        }
    }

    @keyframes bounceIn {
        0% {
            opacity: 0;
            transform: scale(0.7) translateY(10px);
        }
        70% {
            transform: scale(1.04);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* ===== MAIN CONTAINER ===== */
    #main {
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    .content-wrapper {
        padding: 1.75rem 2.25rem;
        max-width: 100%;
        position: relative;
        z-index: 1;
        animation: fadeInUp 0.75s ease-out;
    }

    /* ===== FLOATING AMBIENT GLOW ===== */
    .floating-elements {
        position: fixed;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .floating-element {
        position: absolute;
        border-radius: 50%;
        opacity: 0.22;
        filter: blur(60px);
        animation: floatHero 18s infinite ease-in-out;
    }

    .floating-element:nth-child(1) {
        width: 450px;
        height: 450px;
        background: var(--emerald-primary);
        top: -5%;
        left: -5%;
    }

    .floating-element:nth-child(2) {
        width: 380px;
        height: 380px;
        background: var(--purple-accent);
        bottom: 10%;
        right: -5%;
        animation-delay: 4s;
    }

    .floating-element:nth-child(3) {
        width: 280px;
        height: 280px;
        background: var(--blue-accent);
        top: 45%;
        left: 40%;
        animation-delay: 8s;
    }

    /* ===== INDUSTRY DASHBOARD SHELL & CONTAINERS ===== */
    .industry-dashboard-shell {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
        margin-bottom: 2rem;
    }

    /* ===== HERO COMMAND CENTER CARD ===== */
    .industry-hero-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(246, 250, 247, 0.94) 100%);
        border: 1px solid rgba(255, 255, 255, 0.95);
        border-radius: 30px;
        box-shadow: 0 25px 65px -15px rgba(15, 116, 76, 0.12), 0 8px 24px rgba(0, 0, 0, 0.03);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(290px, 0.6fr);
        gap: 2rem;
        min-height: 320px;
        overflow: hidden;
        padding: clamp(1.8rem, 3.5vw, 2.75rem);
        position: relative;
        isolation: isolate;
        transition: transform 0.35s ease, box-shadow 0.35s ease;
    }

    .industry-hero-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 32px 80px -15px rgba(15, 116, 76, 0.18), 0 12px 30px rgba(0, 0, 0, 0.05);
    }

    .industry-hero-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #094c32, #0f744c, #10b981, #2563eb, #7c3aed);
        background-size: 300% 300%;
        animation: gradientShift 6s ease infinite;
    }

    .industry-hero-copy {
        align-self: center;
        max-width: 780px;
    }

    .industry-eyebrow {
        align-items: center;
        background: linear-gradient(135deg, #073a26 0%, #0f744c 100%);
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-radius: 999px;
        color: #ffffff !important;
        display: inline-flex;
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        margin-bottom: 1.15rem;
        padding: 0.5rem 1rem;
        text-transform: uppercase;
        box-shadow: 0 4px 12px rgba(15, 116, 76, 0.15);
    }

    .industry-eyebrow * {
        color: #ffffff !important;
    }

    .industry-hero-copy h1 {
        color: var(--slate-dark);
        font-size: clamp(2.4rem, 4.5vw, 3.8rem);
        font-weight: 900;
        letter-spacing: -0.03em;
        line-height: 1.1;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #073a26 0%, #0f744c 45%, #0f172a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .industry-hero-copy p {
        color: #475569;
        font-size: clamp(1.05rem, 1.8vw, 1.2rem);
        font-weight: 600;
        max-width: 660px;
        line-height: 1.65;
        margin-bottom: 0;
    }

    .industry-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1.65rem;
    }

    .industry-btn {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-weight: 800;
        font-size: 1.05rem;
        gap: 0.6rem;
        justify-content: center;
        min-height: 48px;
        padding: 0.85rem 1.6rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
    }

    .industry-btn-primary {
        background: linear-gradient(135deg, #073a26 0%, #0f744c 60%, #10b981 100%);
        box-shadow: 0 14px 30px rgba(15, 116, 76, 0.32);
        color: #ffffff !important;
        border: none;
    }

    .industry-btn-primary:hover {
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 20px 40px rgba(15, 116, 76, 0.45);
        color: #ffffff !important;
    }

    .industry-btn-light {
        background: #ffffff;
        border: 2px solid rgba(15, 116, 76, 0.2);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
        color: var(--slate-dark) !important;
    }

    .industry-btn-light:hover {
        background: rgba(15, 116, 76, 0.06);
        border-color: var(--emerald-primary);
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 12px 28px rgba(15, 116, 76, 0.15);
        color: var(--emerald-primary) !important;
    }

    .industry-hero-visual {
        align-items: center;
        display: flex;
        justify-content: center;
        min-height: 240px;
        position: relative;
    }

    .industry-hero-visual img {
        filter: drop-shadow(0 30px 45px rgba(15, 116, 76, 0.22));
        max-height: 290px;
        object-fit: contain;
        transform: rotate(-1.5deg);
        width: min(100%, 440px);
        animation: floatHero 7s ease-in-out infinite;
        transition: transform 0.4s ease;
    }

    /* ===== STAT CARDS ROW (OVERVIEW GRID) ===== */
    .industry-overview-grid {
        display: grid;
        gap: 1.35rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .industry-metric-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 249, 0.94) 100%);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 24px;
        color: var(--slate-dark) !important;
        min-height: 165px;
        overflow: hidden;
        padding: 1.4rem;
        position: relative;
        text-decoration: none;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--card-shadow-sm);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .industry-metric-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--emerald-primary), var(--emerald-light));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .industry-metric-card:hover {
        border-color: rgba(15, 116, 76, 0.35);
        box-shadow: var(--card-shadow-lg);
        transform: translateY(-8px) scale(1.025);
    }

    .industry-metric-card:hover::before {
        opacity: 1;
    }

    .industry-metric-card span {
        color: var(--slate-muted) !important;
        display: block;
        font-size: 0.9rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .industry-metric-card strong {
        color: var(--slate-dark);
        display: block;
        font-size: clamp(2.4rem, 3.5vw, 3.2rem);
        font-weight: 900;
        line-height: 1.05;
        margin: 0.5rem 0;
        letter-spacing: -0.03em;
    }

    .industry-metric-card small {
        color: var(--slate-muted) !important;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.86rem;
        font-weight: 700;
    }

    .industry-metric-card.is-primary {
        background: linear-gradient(135deg, #073a26 0%, #0f744c 55%, #10b981 100%) !important;
        border: 1px solid rgba(16, 185, 129, 0.4) !important;
        box-shadow: 0 20px 45px -10px rgba(15, 116, 76, 0.35) !important;
    }

    .industry-metric-card.is-primary,
    .industry-metric-card.is-primary *,
    .industry-metric-card.is-primary span,
    .industry-metric-card.is-primary strong,
    .industry-metric-card.is-primary small,
    .industry-metric-card.is-primary i,
    .industry-metric-card.is-primary a {
        color: #ffffff !important;
    }

    .industry-arrow {
        bottom: 1.25rem;
        right: 1.25rem;
        position: absolute;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(15, 116, 76, 0.08);
        color: var(--emerald-primary) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .industry-metric-card.is-primary .industry-arrow {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff !important;
    }

    .industry-metric-card:hover .industry-arrow {
        background: var(--emerald-primary);
        color: #ffffff !important;
        transform: translate(3px, -3px) rotate(45deg);
        box-shadow: 0 6px 16px rgba(15, 116, 76, 0.3);
    }

    /* ===== EXECUTIVE BUSINESS MODEL & PREDICTIONS GRID ===== */
    .saas-executive-grid {
        display: grid;
        gap: 1.35rem;
        grid-template-columns: 1.15fr 0.85fr;
    }

    .industry-panel {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 249, 0.94) 100%);
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 26px;
        padding: 1.6rem;
        overflow: hidden;
        position: relative;
        box-shadow: var(--card-shadow-md);
        backdrop-filter: blur(16px);
        transition: all 0.35s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .industry-panel:hover {
        border-color: rgba(15, 116, 76, 0.25);
        box-shadow: var(--card-shadow-lg);
    }

    .industry-panel-head {
        align-items: flex-start;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1.4rem;
    }

    .industry-panel-head h3 {
        color: var(--slate-dark);
        font-size: 1.3rem;
        font-weight: 900;
        letter-spacing: -0.02em;
        margin: 0 0 0.3rem;
    }

    .industry-panel-head p {
        color: var(--slate-muted);
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0;
        line-height: 1.5;
    }

    .industry-panel-head a {
        align-items: center;
        background: rgba(15, 116, 76, 0.1);
        border: 1px solid rgba(15, 116, 76, 0.2);
        border-radius: 999px;
        color: var(--emerald-primary) !important;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: 0.85rem;
        font-weight: 800;
        padding: 0.55rem 1rem;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .industry-panel-head a:hover {
        background: var(--emerald-primary);
        color: #ffffff !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15, 116, 76, 0.25);
    }

    /* Revenue Strip Cards */
    .saas-revenue-strip {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .saas-money-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(241, 248, 244, 0.85));
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        min-height: 145px;
        overflow: hidden;
        padding: 1.25rem;
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .saas-money-card:hover {
        border-color: rgba(15, 116, 76, 0.35);
        box-shadow: 0 16px 36px -8px rgba(15, 116, 76, 0.15);
        transform: translateY(-5px);
    }

    .saas-money-card span {
        color: var(--slate-muted) !important;
        display: block;
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .saas-money-card strong {
        color: var(--slate-dark);
        display: block;
        font-size: clamp(1.4rem, 2vw, 2.1rem);
        font-weight: 950;
        letter-spacing: -0.02em;
        line-height: 1.1;
        margin: 0.5rem 0 0.3rem;
    }

    .saas-money-card em {
        color: var(--emerald-primary);
        font-size: 0.82rem;
        font-style: normal;
        font-weight: 800;
    }

    /* Prediction Grid */
    .saas-prediction-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .saas-insight-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(248, 250, 249, 0.92));
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        min-height: 145px;
        padding: 1.25rem;
        position: relative;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .saas-insight-card:hover {
        border-color: rgba(15, 116, 76, 0.3);
        box-shadow: 0 16px 36px -8px rgba(15, 116, 76, 0.14);
        transform: translateY(-5px);
    }

    .saas-insight-card span {
        color: var(--slate-muted) !important;
        display: block;
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .saas-insight-card strong {
        color: var(--slate-dark);
        display: block;
        font-size: 1.2rem;
        font-weight: 900;
        margin: 0.4rem 0;
    }

    .saas-insight-card p {
        color: var(--slate-body);
        font-size: 0.86rem;
        font-weight: 600;
        margin: 0;
        line-height: 1.45;
    }

    .saas-risk-pill {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-size: 0.78rem;
        font-weight: 900;
        gap: 0.4rem;
        padding: 0.4rem 0.75rem;
    }

    .saas-risk-low {
        background: rgba(16, 185, 129, 0.14);
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #047857;
    }

    .saas-risk-mid {
        background: rgba(245, 158, 11, 0.14);
        border: 1px solid rgba(245, 158, 11, 0.25);
        color: #92400e;
    }

    .saas-risk-high {
        background: rgba(239, 68, 68, 0.14);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #991b1b;
    }

    /* ===== TREND BOARD & GAUGES ===== */
    .saas-trend-board {
        display: grid;
        gap: 1.35rem;
        grid-template-columns: 1fr 1fr;
    }

    .saas-line-card {
        min-height: 230px;
    }

    .saas-sparkline {
        align-items: flex-end;
        display: flex;
        gap: 0.75rem;
        height: 130px;
        margin-top: 1.25rem;
        padding: 0.5rem;
        background: rgba(15, 116, 76, 0.03);
        border-radius: 16px;
        border: 1px dashed rgba(15, 116, 76, 0.15);
    }

    .saas-sparkline span {
        background: linear-gradient(180deg, var(--emerald-primary), var(--emerald-light));
        border-radius: 999px 999px 6px 6px;
        flex: 1 1 0;
        min-width: 12px;
        height: var(--spark);
        animation: progressFill 1.2s cubic-bezier(0.4, 0, 0.2, 1) both;
        transition: transform 0.25s ease;
    }

    .saas-sparkline span:hover {
        transform: scaleY(1.08);
        background: linear-gradient(180deg, var(--purple-accent), var(--blue-accent));
    }

    .industry-gauge {
        align-items: center;
        background: conic-gradient(var(--emerald-light) 0deg, var(--emerald-primary) var(--value-deg), #e2e8f0 var(--value-deg), #e2e8f0 180deg, transparent 180deg);
        border-radius: 190px 190px 24px 24px;
        display: flex;
        height: 155px;
        justify-content: center;
        margin: 0.75rem auto 1.25rem;
        max-width: 280px;
        position: relative;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    .industry-gauge::after {
        background: #ffffff;
        border-radius: 160px 160px 20px 20px;
        content: "";
        inset: 22px 22px 0;
        position: absolute;
    }

    .industry-gauge div {
        margin-top: 30px;
        position: relative;
        text-align: center;
        z-index: 1;
    }

    .industry-gauge strong {
        color: var(--slate-dark);
        display: block;
        font-size: 2.35rem;
        font-weight: 900;
        line-height: 1;
    }

    .industry-gauge span {
        color: var(--slate-muted) !important;
        font-size: 0.88rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .industry-presence-row {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .industry-presence-row span {
        background: rgba(15, 116, 76, 0.05);
        border: 1px solid rgba(15, 116, 76, 0.12);
        border-radius: 16px;
        color: var(--slate-body) !important;
        font-size: 0.85rem;
        font-weight: 800;
        padding: 0.85rem 0.65rem;
        text-align: center;
    }

    .industry-presence-row b {
        color: var(--slate-dark);
        display: block;
        font-size: 1.15rem;
        font-weight: 900;
    }

    /* ===== WORK ANALYTICS & TEAM PRESENCE GRID ===== */
    .industry-main-grid {
        display: grid;
        gap: 1.35rem;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, 0.6fr);
    }

    .industry-bars {
        display: grid;
        gap: 1rem;
    }

    .industry-bar {
        align-items: center;
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(0, 1fr) 110px;
    }

    .industry-bar span {
        background: #f1f5f9;
        border-radius: 999px;
        display: block;
        height: 16px;
        overflow: hidden;
        position: relative;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04);
    }

    .industry-bar span::after {
        animation: progressFill 1.3s ease both;
        background: linear-gradient(90deg, #073a26, #0f744c, #10b981);
        border-radius: inherit;
        content: "";
        inset: 0 auto 0 0;
        position: absolute;
        width: var(--bar);
    }

    .industry-bar.is-muted span::after {
        background: linear-gradient(90deg, #f59e0b, #ef4444);
    }

    .industry-bar label {
        color: var(--slate-dark);
        font-size: 0.92rem;
        font-weight: 900;
        margin: 0;
    }

    /* ===== AUTOMATIC FEATURE MODULE CARDS GRID ===== */
    .saas-module-grid {
        display: grid;
        gap: 1.1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .saas-module-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 249, 0.92));
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 22px;
        color: var(--slate-dark) !important;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        min-height: 205px;
        padding: 1.1rem;
        text-decoration: none;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--card-shadow-sm);
    }

    .saas-module-card:hover {
        border-color: rgba(15, 116, 76, 0.35);
        box-shadow: var(--card-shadow-lg);
        transform: translateY(-6px) scale(1.02);
    }

    .saas-module-head {
        align-items: center;
        display: flex;
        gap: 0.75rem;
    }

    .saas-module-icon {
        align-items: center;
        background: linear-gradient(135deg, rgba(15, 116, 76, 0.14), rgba(37, 99, 235, 0.14));
        border-radius: 14px;
        color: var(--emerald-primary) !important;
        display: inline-flex;
        flex: 0 0 44px;
        font-size: 1.35rem;
        height: 44px;
        justify-content: center;
        width: 44px;
    }

    .saas-module-card h4 {
        color: var(--slate-dark);
        font-size: 0.98rem;
        font-weight: 900;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .saas-module-card small {
        color: var(--slate-muted);
        font-size: 0.8rem;
        font-weight: 700;
    }

    .saas-module-donut {
        --accent: var(--emerald-primary);
        align-items: center;
        align-self: center;
        background: conic-gradient(var(--accent) calc(var(--percent) * 1%), #e2e8f0 0);
        border-radius: 50%;
        display: flex;
        height: 96px;
        justify-content: center;
        position: relative;
        width: 96px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
    }

    .saas-module-donut::after {
        background: #ffffff;
        border-radius: 50%;
        content: "";
        inset: 12px;
        position: absolute;
    }

    .saas-module-donut strong {
        color: var(--slate-dark);
        font-size: 1.15rem;
        font-weight: 950;
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
        background: rgba(15, 116, 76, 0.1);
        border-radius: 999px;
        color: var(--emerald-primary);
        font-size: 0.78rem;
        font-style: normal;
        font-weight: 900;
        padding: 0.35rem 0.65rem;
    }

    /* ===== MODULE INTELLIGENCE PIE CHARTS GRID ===== */
    .industry-chart-grid {
        display: grid;
        gap: 1.1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .industry-chart-card {
        background: linear-gradient(145deg, #ffffff, rgba(248, 250, 249, 0.92));
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 22px;
        min-height: 255px;
        padding: 1.25rem;
        transition: all 0.35s ease;
        box-shadow: var(--card-shadow-sm);
    }

    .industry-chart-card:hover {
        border-color: rgba(15, 116, 76, 0.3);
        box-shadow: var(--card-shadow-lg);
        transform: translateY(-5px);
    }

    .industry-chart-body {
        align-items: center;
        display: flex;
        flex-direction: column;
        gap: 0.95rem;
        text-align: center;
    }

    .industry-donut {
        --accent: var(--emerald-primary);
        align-items: center;
        animation: bounceIn 0.65s ease both;
        background: conic-gradient(var(--accent) calc(var(--percent) * 1%), #e2e8f0 0);
        border-radius: 50%;
        display: flex;
        height: 135px;
        justify-content: center;
        position: relative;
        width: 135px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .industry-donut::after {
        background: #ffffff;
        border-radius: 50%;
        content: "";
        inset: 16px;
        position: absolute;
    }

    .industry-donut strong {
        color: var(--slate-dark);
        font-size: 1.45rem;
        font-weight: 900;
        position: relative;
        z-index: 1;
    }

    .industry-chart-meta h4 {
        color: var(--slate-dark);
        font-size: 1.05rem;
        font-weight: 900;
        margin: 0 0 0.3rem;
    }

    .industry-chart-meta p {
        color: var(--slate-muted);
        font-size: 0.85rem;
        font-weight: 700;
        margin: 0;
    }

    .industry-chart-link {
        color: var(--emerald-primary) !important;
        font-size: 0.88rem;
        font-weight: 900;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .industry-chart-link:hover {
        color: var(--emerald-dark) !important;
        text-decoration: underline;
    }

    /* ===== FEATURE SHORTCUTS GRID ===== */
    .industry-feature-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .industry-feature-card {
        align-items: center;
        background: linear-gradient(145deg, #ffffff, rgba(248, 250, 249, 0.92));
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        color: var(--slate-dark) !important;
        display: grid;
        gap: 0.9rem;
        grid-template-columns: 48px minmax(0, 1fr) auto;
        min-height: 90px;
        padding: 1rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--card-shadow-sm);
    }

    .industry-feature-card:hover {
        border-color: rgba(15, 116, 76, 0.35);
        box-shadow: var(--card-shadow-md);
        transform: translateY(-4px) translateX(2px);
    }

    .industry-feature-icon {
        align-items: center;
        background: linear-gradient(135deg, rgba(15, 116, 76, 0.12), rgba(37, 99, 235, 0.12));
        border-radius: 16px;
        color: var(--emerald-primary) !important;
        display: inline-flex;
        font-size: 1.4rem;
        height: 48px;
        justify-content: center;
        width: 48px;
        transition: transform 0.3s ease;
    }

    .industry-feature-card:hover .industry-feature-icon {
        transform: scale(1.15) rotate(5deg);
        background: var(--emerald-primary);
        color: #ffffff !important;
    }

    .industry-feature-copy {
        min-width: 0;
    }

    .industry-feature-copy strong,
    .industry-feature-copy small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .industry-feature-copy strong {
        color: var(--slate-dark);
        font-size: 0.96rem;
        font-weight: 900;
    }

    .industry-feature-copy small {
        color: var(--slate-muted);
        font-size: 0.8rem;
        font-weight: 700;
    }

    .industry-feature-card em {
        background: rgba(15, 116, 76, 0.08);
        border-radius: 999px;
        color: var(--emerald-primary);
        font-size: 0.8rem;
        font-style: normal;
        font-weight: 900;
        min-width: 44px;
        padding: 0.4rem 0.65rem;
        text-align: center;
    }

    /* ===== WELCOME SECTION & STATS CARDS ===== */
    .welcome-section {
        margin-bottom: 2rem;
    }

    .welcome-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(246, 250, 247, 0.94));
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 26px;
        overflow: hidden;
        box-shadow: var(--card-shadow-md);
    }

    .welcome-content {
        padding: 2.25rem;
    }

    .welcome-title {
        font-size: 1.85rem;
        font-weight: 900;
        color: var(--slate-dark);
        margin-bottom: 0.75rem;
    }

    .welcome-text {
        color: var(--slate-muted);
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }

    .welcome-badges {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .welcome-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(15, 116, 76, 0.08);
        color: var(--emerald-primary);
        padding: 0.5rem 1rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 800;
    }

    .welcome-illustration {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .stats-section {
        margin-bottom: 2rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.35rem;
    }

    .stat-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 22px;
        padding: 1.4rem;
        box-shadow: var(--card-shadow-sm);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--card-shadow-md);
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 900;
        color: var(--slate-dark);
    }

    .stat-label {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--slate-muted);
    }

    /* ===== SECONDARY STATS CARDS GRID ===== */
    .stats-section {
        margin-bottom: 2rem;
    }

    .stats-grid {
        display: grid;
        gap: 1.35rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .stat-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 249, 0.94) 100%);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 22px;
        padding: 1.4rem;
        position: relative;
        box-shadow: var(--card-shadow-sm);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .stat-card:hover {
        border-color: rgba(15, 116, 76, 0.3);
        box-shadow: var(--card-shadow-md);
        transform: translateY(-4px);
    }

    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.85rem;
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(15, 116, 76, 0.12), rgba(16, 185, 129, 0.12));
        color: var(--emerald-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .stat-title {
        margin: 0 0 0.4rem;
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--slate-muted);
    }

    .stat-title a {
        color: var(--slate-muted);
        text-decoration: none;
        transition: color 0.2s;
    }

    .stat-title a:hover {
        color: var(--emerald-primary);
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 900;
        color: var(--slate-dark);
        line-height: 1.1;
        margin-bottom: 0.5rem;
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.82rem;
        font-weight: 800;
        color: var(--emerald-primary);
    }

    .stat-progress {
        margin-top: 0.85rem;
    }

    .progress-container {
        height: 8px;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--emerald-primary), var(--emerald-light));
        border-radius: 999px;
        transition: width 1s ease;
    }

    /* ===== CONTENT CARDS GRID ===== */
    .content-section {
        margin-bottom: 2rem;
    }

    .content-grid {
        display: grid;
        gap: 1.35rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .content-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 249, 0.94));
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--card-shadow-sm);
        display: flex;
        flex-direction: column;
        height: 480px;
        transition: all 0.3s ease;
    }

    .content-card:hover {
        border-color: rgba(15, 116, 76, 0.25);
        box-shadow: var(--card-shadow-md);
    }

    .card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(255, 255, 255, 0.6);
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: 900;
        color: var(--slate-dark);
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .card-title i {
        color: var(--emerald-primary);
        font-size: 1.3rem;
    }

    .card-action {
        color: var(--emerald-primary);
        font-size: 0.85rem;
        font-weight: 800;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        transition: all 0.2s;
    }

    .card-action:hover {
        color: var(--emerald-dark);
        transform: translateX(2px);
    }

    .card-body {
        padding: 1.25rem 1.5rem;
        overflow-y: auto;
        flex: 1;
    }

    .list-item {
        padding: 0.9rem;
        border-radius: 16px;
        background: rgba(248, 250, 249, 0.8);
        border: 1px solid rgba(226, 232, 240, 0.7);
        margin-bottom: 0.85rem;
        transition: all 0.25s ease;
    }

    .list-item:hover {
        background: #ffffff;
        border-color: rgba(15, 116, 76, 0.25);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.04);
    }

    .list-item-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.4rem;
    }

    .list-item-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--slate-dark);
        margin: 0 0 0.3rem;
    }

    .list-item-meta {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        font-size: 0.8rem;
        color: var(--slate-muted);
        font-weight: 600;
    }

    .list-item-meta span {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .badge-low {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .badge-medium {
        background: rgba(245, 158, 11, 0.12);
        color: #92400e;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .badge-high {
        background: rgba(239, 68, 68, 0.12);
        color: #991b1b;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .empty-state {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--slate-muted);
    }

    .empty-state i {
        font-size: 3rem;
        color: var(--emerald-primary);
        margin-bottom: 0.75rem;
        opacity: 0.8;
    }

    .empty-state p {
        font-size: 0.95rem;
        font-weight: 700;
        margin: 0;
    }

    .timeline {
        position: relative;
        padding-left: 1.25rem;
    }

    .timeline::before {
        content: "";
        position: absolute;
        left: 4px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: rgba(15, 116, 76, 0.15);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.1rem;
    }

    .timeline-point {
        position: absolute;
        left: -1.25rem;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--emerald-primary);
        box-shadow: 0 0 0 3px rgba(15, 116, 76, 0.15);
    }

    .timeline-event {
        font-size: 0.88rem;
        color: var(--slate-body);
        font-weight: 600;
    }

    /* ===== RESPONSIVE MEDIA QUERIES ===== */
    @media (max-width: 1399.98px) {
        .saas-module-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

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
            min-height: 180px;
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
                ['label' => 'Attendance', 'slug' => 'attendance', 'hint' => "{$dashboardPresentCount} present / {$dashboardTotalEmployees} employees", 'route' => 'attendance.index', 'value' => $dashboardPresentCount, 'percent' => $dashboardAttendancePercent, 'color' => '#10b981'],
                ['label' => 'Projects', 'slug' => 'projects', 'hint' => "{$dashboardTotalProject} active projects", 'route' => 'projects.index', 'value' => $dashboardTotalProject, 'percent' => round(($dashboardTotalProject / $dashboardFeatureScale) * 100), 'color' => '#2563eb'],
                ['label' => 'Tasks', 'slug' => 'tasks', 'hint' => "{$dashboardPendingTask} pending tasks", 'route' => 'tasks.index', 'value' => $dashboardPendingTask, 'percent' => round(($dashboardPendingTask / $dashboardFeatureScale) * 100), 'color' => '#f59e0b'],
                ['label' => 'Tickets', 'slug' => 'tickets', 'hint' => "{$dashboardUnresolvedTicket} unresolved tickets", 'route' => 'tickets.index', 'value' => $dashboardUnresolvedTicket, 'percent' => round(($dashboardUnresolvedTicket / $dashboardFeatureScale) * 100), 'color' => '#ef4444'],
                ['label' => 'Clients', 'slug' => 'clients', 'hint' => "{$dashboardTotalClient} client records", 'route' => 'clients.index', 'value' => $dashboardTotalClient, 'percent' => round(($dashboardTotalClient / $dashboardFeatureScale) * 100), 'color' => '#7c3aed'],
                ['label' => 'Leaves', 'slug' => 'leaves', 'hint' => "{$dashboardPendingLeaves} pending requests", 'route' => 'leaves.index', 'value' => $dashboardPendingLeaves, 'percent' => round(($dashboardPendingLeaves / $dashboardFeatureScale) * 100), 'color' => '#06b6d4'],
                ['label' => 'Employees', 'slug' => 'employees', 'hint' => "{$dashboardTotalEmployees} total employees", 'route' => 'employees.index', 'value' => $dashboardTotalEmployees, 'percent' => round(($dashboardTotalEmployees / $dashboardFeatureScale) * 100), 'color' => '#14b8a6'],
                ['label' => 'Reports', 'slug' => 'reports', 'hint' => 'Attendance and operations reporting', 'route' => 'attendance.report', 'value' => 'View', 'percent' => max(35, $dashboardAttendancePercent), 'color' => '#64748b'],
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
                    'color' => ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#7c3aed', '#06b6d4', '#14b8a6', '#64748b'][$index % 8],
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
                    <span class="industry-eyebrow">Workspace overview</span>
                    <h1>Dashboard</h1>
                    <p>Plan work, track teams, review support, and jump into every PMS feature from one clean command center.</p>
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
                    </div>
                </div>
                <div class="industry-hero-visual">
                    <img src="{{ asset('admin/assets/img/illustrations/dashboard-ui-preview.png') }}" alt="Dashboard overview">
                </div>
            </div>

            <div class="industry-overview-grid">
                <a href="{{ Route::has('projects.index') ? route('projects.index') : '#' }}" class="industry-metric-card is-primary">
                    <span>Total Projects</span>
                    <strong>{{ $dashboardTotalProject }}</strong>
                    <small><i class="bx bx-up-arrow-alt"></i> Open project workspace</small>
                    <i class="bx bx-right-arrow-alt industry-arrow"></i>
                </a>
                <a href="{{ Route::has('tasks.index') ? route('tasks.index') : '#' }}" class="industry-metric-card">
                    <span>Pending Tasks</span>
                    <strong>{{ $dashboardPendingTask }}</strong>
                    <small>Task queue</small>
                    <i class="bx bx-right-arrow-alt industry-arrow"></i>
                </a>
                <a href="{{ Route::has('tickets.index') ? route('tickets.index') : '#' }}" class="industry-metric-card">
                    <span>Open Tickets</span>
                    <strong>{{ $dashboardUnresolvedTicket }}</strong>
                    <small>Support needs attention</small>
                    <i class="bx bx-right-arrow-alt industry-arrow"></i>
                </a>
                <a href="{{ Route::has('attendance.report') ? route('attendance.report') : '#' }}" class="industry-metric-card">
                    <span>Attendance</span>
                    <strong>{{ $dashboardAttendancePercent }}%</strong>
                    <small>{{ $dashboardPresentCount }} present today</small>
                    <i class="bx bx-right-arrow-alt industry-arrow"></i>
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
