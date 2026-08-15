<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'Super Admin · Command Center') - {{ config('app.name', 'BBHPMS') }}</title>

  <!-- Google Fonts: Plus Jakarta Sans & Inter & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />

  <!-- Boxicons -->
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

  <style>
    /* ===== LUXURY DASHBOARD THEME — EMERALD · SLATE · PURPLE ===== */
    :root {
      --emerald-primary: #0f744c;
      --emerald-dark: #073a26;
      --emerald-deep: #05291b;
      --emerald-light: #10b981;
      --emerald-soft: #e4f3eb;
      --emerald-glow: rgba(15, 116, 76, 0.25);
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
      --font-mono: 'JetBrains Mono', monospace;
      --sidebar-width: 264px;
      --header-height: 68px;

      /* Legacy compatibility mappings */
      --font-main: var(--font-family-main);
      --bg-app: linear-gradient(135deg, #f1f5f3 0%, #e6eee8 50%, #f7faf8 100%);
      --bg-surface: rgba(255, 255, 255, 0.92);
      --bg-sidebar: var(--slate-dark);
      --primary: var(--emerald-primary);
      --primary-hover: var(--emerald-dark);
      --primary-gradient: linear-gradient(135deg, var(--emerald-dark), var(--emerald-primary), var(--emerald-light));
      --text-main: var(--slate-dark);
      --text-muted: var(--slate-muted);
      --text-light: #94a3b8;
      --border-subtle: rgba(226, 232, 240, 0.85);
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-xl: 20px;
      --shadow-sm: var(--card-shadow-sm);
      --shadow-md: var(--card-shadow-md);
      --shadow-lg: var(--card-shadow-lg);
    }

    /* ===== RESET & BASE ===== */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html {
      font-size: 15px;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    body {
      font-family: var(--font-family-main);
      background: linear-gradient(135deg, #f1f5f3 0%, #e6eee8 50%, #f7faf8 100%) !important;
      color: var(--slate-dark);
      display: flex;
      min-height: 100vh;
      line-height: 1.6;
      overflow-x: hidden;
    }

    a {
      text-decoration: none;
      color: inherit;
    }
    button {
      font-family: inherit;
      cursor: pointer;
      border: none;
      background: none;
      font-size: inherit;
    }
    input,
    select {
      font-family: inherit;
      font-size: inherit;
    }

    ::selection {
      background: var(--emerald-glow);
      color: var(--slate-dark);
    }

    *:focus-visible {
      outline: 2px solid var(--emerald-primary);
      outline-offset: 2px;
    }

    /* ===== SCROLLBAR ===== */
    ::-webkit-scrollbar {
      width: 5px;
      height: 5px;
    }
    ::-webkit-scrollbar-track {
      background: transparent;
    }
    ::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 8px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    /* ===== ANIMATIONS ===== */
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

    @keyframes pulse {
      0%, 100% {
        opacity: 1;
        transform: scale(1);
      }
      50% {
        opacity: 0.5;
        transform: scale(0.85);
      }
    }

    @keyframes fadeSlide {
      from {
        opacity: 0;
        transform: translateY(-4px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* ===== FLOATING AMBIENT GLOW ===== */
    .floating-elements {
      position: fixed;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 0;
      top: 0;
      left: 0;
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

    /* ============================================================
       SIDEBAR
       ============================================================ */
    .sidebar {
      width: var(--sidebar-width);
      min-height: 100vh;
      background: var(--slate-dark);
      color: rgba(255, 255, 255, 0.75);
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
      padding: 0 16px 20px;
      border-right: 1px solid rgba(255, 255, 255, 0.06);
      transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 100;
    }

    .sidebar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 22px 8px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      margin-bottom: 18px;
    }

    .sidebar-brand .logo {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, var(--emerald-primary), var(--emerald-light));
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 18px;
      color: #fff;
      flex-shrink: 0;
      box-shadow: 0 4px 12px rgba(15, 116, 76, 0.35);
    }

    .sidebar-brand .brand-group {
      flex: 1;
    }
    .sidebar-brand .brand-name {
      font-size: 16px;
      font-weight: 800;
      color: #fff;
      letter-spacing: -0.2px;
      line-height: 1.2;
    }
    .sidebar-brand .brand-sub {
      font-size: 10px;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.7);
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }
    .sidebar-brand .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--emerald-light);
      display: inline-block;
      margin-left: 4px;
      box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
    }

    .sidebar-nav {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 1px;
    }

    .sidebar-nav .nav-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      color: #94a3b8;
      padding: 18px 8px 6px;
    }

    .sidebar-nav a,
    .sidebar-nav .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 9px 12px;
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.88);
      transition: all 0.2s ease;
      position: relative;
    }

    .sidebar-nav a:hover,
    .sidebar-nav .nav-item:hover {
      background: rgba(255, 255, 255, 0.1);
      color: #fff;
    }

    .sidebar-nav a.active,
    .sidebar-nav .nav-item.active {
      background: rgba(16, 185, 129, 0.22);
      border: 1px solid rgba(16, 185, 129, 0.35);
      color: #fff;
      font-weight: 700;
    }

    .sidebar-nav a.active::before,
    .sidebar-nav .nav-item.active::before {
      content: '';
      position: absolute;
      left: -16px;
      top: 50%;
      transform: translateY(-50%);
      width: 3px;
      height: 24px;
      background: var(--emerald-light);
      border-radius: 0 4px 4px 0;
    }

    .sidebar-nav a .icon,
    .sidebar-nav .nav-item i {
      font-size: 18px;
      width: 24px;
      text-align: center;
      flex-shrink: 0;
      opacity: 0.85;
      color: #cbd5e1;
    }

    .sidebar-nav a:hover .icon,
    .sidebar-nav .nav-item:hover i {
      opacity: 1;
      color: #fff;
    }

    .sidebar-nav a.active .icon,
    .sidebar-nav .nav-item.active i {
      opacity: 1;
      color: var(--emerald-light);
    }

    .sidebar-nav a .badge,
    .sidebar-nav .nav-item .badge {
      margin-left: auto;
      background: rgba(255, 255, 255, 0.12);
      padding: 2px 8px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: 700;
      color: rgba(255, 255, 255, 0.8);
    }

    .sidebar-nav a.active .badge,
    .sidebar-nav .nav-item.active .badge {
      background: rgba(15, 116, 76, 0.4);
      color: #6ee7b7;
    }

    .sidebar-footer {
      margin-top: auto;
      padding-top: 18px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      flex-direction: column;
      gap: 6px;
      padding: 16px 8px 0;
    }

    .sidebar-footer .status-row {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.75);
    }

    .sidebar-footer .status-row .dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--emerald-light);
      display: inline-block;
      box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }

    .sidebar-footer .version {
      font-size: 11px;
      color: rgba(255, 255, 255, 0.45);
      font-weight: 500;
    }

    /* ============================================================
       MAIN CONTENT
       ============================================================ */
    .main {
      flex: 1;
      min-width: 0;
      padding: 0 32px 40px;
      max-width: calc(100vw - var(--sidebar-width));
      display: flex;
      flex-direction: column;
      position: relative;
      z-index: 1;
      animation: fadeInUp 0.75s ease-out;
    }

    /* ============================================================
       TOP HEADER
       ============================================================ */
    .top-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 0 20px;
      border-bottom: none;
      flex-wrap: wrap;
      gap: 12px;
      position: sticky;
      top: 0;
      background: rgba(241, 245, 243, 0.85);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      z-index: 50;
    }

    .top-header .left {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .top-header .left .title-wrap {
      display: flex;
      flex-direction: column;
      gap: 1px;
      margin-left: 9px;
    }

    .top-header .left .page-title {
      font-size: 30px;
      font-weight: 900;
      letter-spacing: -0.6px;
      line-height: 1.15;
      color: var(--slate-dark);
      background: linear-gradient(135deg, #073a26 0%, var(--emerald-primary) 55%, var(--emerald-light) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-left: 9px;
    }

    .top-header .left .page-sub {
      font-size: 13.5px;
      color: var(--slate-muted);
      font-weight: 700;
      margin-top: -2px;
      margin-left: 9px;
      letter-spacing: -0.1px;
    }

    .top-header .center {
      flex: 1;
      max-width: 440px;
      min-width: 160px;
      margin: 0 20px;
      position: relative;
    }

    .top-header .center .search-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 12px;
      padding: 0 14px;
      border: 1px solid rgba(226, 232, 240, 0.8);
      transition: all 0.2s ease;
      box-shadow: var(--card-shadow-sm);
    }

    .top-header .center .search-wrap:focus-within {
      border-color: var(--emerald-primary);
      box-shadow: 0 0 0 3px var(--emerald-glow);
    }

    .top-header .center .search-wrap .search-icon {
      color: var(--slate-muted);
      font-size: 16px;
    }

    .top-header .center .search-wrap input {
      border: none;
      background: transparent;
      padding: 8px 0;
      width: 100%;
      outline: none;
      font-size: 13px;
      color: var(--slate-dark);
      font-weight: 500;
    }

    .top-header .center .search-wrap input::placeholder {
      color: var(--slate-muted);
      font-weight: 500;
    }

    .top-header .center .search-wrap .kbd {
      font-size: 10px;
      background: var(--slate-light);
      padding: 1px 8px;
      border-radius: 4px;
      color: var(--slate-muted);
      font-weight: 700;
      border: 1px solid rgba(226, 232, 240, 0.8);
      letter-spacing: 0.2px;
    }

    .top-header .right {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .top-header .right .btn-notif {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid rgba(226, 232, 240, 0.8);
      font-size: 18px;
      transition: all 0.2s ease;
      position: relative;
      color: var(--slate-body);
    }

    .top-header .right .btn-notif:hover {
      border-color: var(--emerald-primary);
      background: var(--emerald-soft);
      color: var(--emerald-primary);
    }

    .top-header .right .btn-notif .badge-dot {
      position: absolute;
      top: 6px;
      right: 6px;
      width: 8px;
      height: 8px;
      background: var(--rose-accent);
      border-radius: 50%;
      border: 2px solid #fff;
      animation: pulse 2s infinite;
    }

    .top-header .right .profile-wrapper {
      position: relative;
    }

    .top-header .right .profile {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 4px 12px 4px 4px;
      border-radius: 12px;
      border: 1px solid rgba(226, 232, 240, 0.8);
      background: rgba(255, 255, 255, 0.9);
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .top-header .right .profile:hover {
      border-color: var(--emerald-primary);
      background: var(--emerald-soft);
    }

    .top-header .right .profile .avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--emerald-primary), var(--emerald-light));
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 13px;
      color: #fff;
      flex-shrink: 0;
    }

    .top-header .right .profile .info .name {
      font-size: 13px;
      font-weight: 700;
      color: var(--slate-dark);
      line-height: 1.2;
    }

    .top-header .right .profile .info .role {
      font-size: 10px;
      color: var(--slate-muted);
      font-weight: 600;
    }

    .top-header .right .profile .arrow {
      color: var(--slate-muted);
      font-size: 12px;
      margin-left: 2px;
      transition: transform 0.2s ease;
    }

    .profile-wrapper.open .profile .arrow {
      transform: rotate(180deg);
    }

    /* Profile dropdown */
    .profile-dropdown {
      position: absolute;
      right: 0;
      top: calc(100% + 6px);
      background: rgba(255, 255, 255, 0.98);
      border-radius: 12px;
      border: 1px solid rgba(226, 232, 240, 0.8);
      box-shadow: var(--card-shadow-lg);
      min-width: 210px;
      padding: 6px 0;
      display: none;
      z-index: 60;
      animation: fadeSlide 0.18s ease;
      backdrop-filter: blur(16px);
    }

    .profile-wrapper.open .profile-dropdown {
      display: block;
    }

    .profile-dropdown-header {
      padding: 10px 16px;
      border-bottom: 1px solid rgba(226, 232, 240, 0.8);
      margin-bottom: 4px;
    }

    .profile-dropdown-header .user-name {
      font-size: 13px;
      font-weight: 700;
      color: var(--slate-dark);
    }

    .profile-dropdown-header .user-email {
      font-size: 11px;
      color: var(--slate-muted);
      font-weight: 500;
    }

    .profile-dropdown a,
    .profile-dropdown button.dropdown-item {
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      text-align: left;
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 600;
      color: var(--slate-body);
      transition: all 0.15s ease;
      background: none;
      border: none;
      cursor: pointer;
    }

    .profile-dropdown a:hover,
    .profile-dropdown button.dropdown-item:hover {
      background: var(--emerald-soft);
      color: var(--emerald-primary);
    }

    .profile-dropdown .divider {
      height: 1px;
      background: rgba(226, 232, 240, 0.8);
      margin: 4px 12px;
    }

    .profile-dropdown .danger {
      color: var(--rose-accent) !important;
    }

    .profile-dropdown .danger:hover {
      background: rgba(239, 68, 68, 0.08) !important;
      color: var(--rose-accent) !important;
    }

    /* ===== HAMBURGER (mobile) ===== */
    .hamburger {
      display: none;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      border-radius: 12px;
      border: 1px solid rgba(226, 232, 240, 0.8);
      background: rgba(255, 255, 255, 0.9);
      font-size: 20px;
      color: var(--slate-body);
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .hamburger:hover {
      background: var(--emerald-soft);
      border-color: var(--emerald-primary);
      color: var(--emerald-primary);
    }

    .sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      z-index: 90;
      backdrop-filter: blur(2px);
    }

    .sidebar-overlay.open {
      display: block;
    }

    /* ============================================================
       CONTENT CONTAINER
       ============================================================ */
    .content {
      padding: 8px 0 32px;
      flex: 1;
    }

    /* Flash Alerts */
    .flash-alert {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 18px;
      border-radius: 14px;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 20px;
      box-shadow: var(--card-shadow-sm);
    }

    .flash-alert.success {
      background: var(--emerald-soft);
      color: var(--emerald-primary);
      border: 1px solid rgba(15, 116, 76, 0.2);
    }

    .flash-alert.error {
      background: rgba(239, 68, 68, 0.1);
      color: var(--rose-accent);
      border: 1px solid rgba(239, 68, 68, 0.2);
    }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 992px) {
      :root {
        --sidebar-width: 0px;
      }

      .sidebar {
        position: fixed;
        transform: translateX(-100%);
        width: 280px;
        height: 100vh;
        top: 0;
        left: 0;
        z-index: 100;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-right: 1px solid rgba(255, 255, 255, 0.06);
        background: var(--slate-dark);
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .main {
        max-width: 100vw;
        padding: 0 16px 24px;
      }

      .hamburger {
        display: flex;
      }
    }
  </style>
  @stack('styles')
</head>
<body>

  <!-- FLOATING AMBIENT GLOW -->
  <div class="floating-elements">
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
  </div>

  <!-- SIDEBAR (LUXURY DARK THEME) -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="logo">
        <i class="bx bx-cube-alt"></i>
      </div>
      <div class="brand-group">
        <div class="brand-name">Super Admin</div>
        <div class="brand-sub">Command Center <span class="status-dot"></span></div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <!-- COMMAND CENTER -->
      <div class="nav-label">Command Center</div>
      <a href="{{ Route::has('superadmin.dashboard') ? route('superadmin.dashboard') : route('super-admin.companies.index') }}" 
         class="{{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
        <i class="bx bx-grid-alt icon"></i> Dashboard
      </a>

      <!-- TENANT MANAGEMENT -->
      <div class="nav-label">Tenant Management</div>
      <a href="{{ Route::has('super-admin.companies.index') ? route('super-admin.companies.index') : (Route::has('superadmin.companies.index') ? route('superadmin.companies.index') : url('/super-admin/companies')) }}" 
         class="{{ request()->routeIs('super-admin.companies.index') || (request()->routeIs('super-admin.companies.*') && !request()->routeIs('super-admin.companies.metrics')) ? 'active' : '' }}">
        <i class="bx bx-building-house icon"></i> Companies
      </a>
      <a href="{{ Route::has('super-admin.companies.metrics') ? route('super-admin.companies.metrics') : url('/super-admin/companies/metrics') }}" 
         class="{{ request()->routeIs('super-admin.companies.metrics') ? 'active' : '' }}">
        <i class="bx bx-line-chart icon"></i> Company Metrics
      </a>

      <!-- SUBSCRIPTIONS -->
      <div class="nav-label">Subscriptions</div>
      <a href="{{ Route::has('super-admin.plans.index') ? route('super-admin.plans.index') : url('/super-admin/plans') }}" 
         class="{{ request()->routeIs('super-admin.plans.*') ? 'active' : '' }}">
        <i class="bx bx-layer icon"></i> Plans Catalog
      </a>
      <a href="{{ Route::has('super-admin.subscriptions.index') ? route('super-admin.subscriptions.index') : url('/super-admin/subscriptions') }}" 
         class="{{ request()->routeIs('super-admin.subscriptions.*') ? 'active' : '' }}">
        <i class="bx bx-receipt icon"></i> Subscriptions
      </a>

      <!-- OPERATIONS -->
      <div class="nav-label">Operations</div>
      <a href="{{ Route::has('super-admin.migrations.index') ? route('super-admin.migrations.index') : (Route::has('superadmin.migrations.index') ? route('superadmin.migrations.index') : url('/super-admin/migrations')) }}"
         class="{{ request()->routeIs('super-admin.migrations.*') || request()->routeIs('superadmin.migrations.*') ? 'active' : '' }}">
        <i class="bx bx-git-repo-forked icon"></i> Migrations
      </a>
      <a href="{{ Route::has('super-admin.backups.index') ? route('super-admin.backups.index') : (Route::has('superadmin.backups.index') ? route('superadmin.backups.index') : url('/super-admin/backups')) }}"
         class="{{ request()->routeIs('super-admin.backups.*') || request()->routeIs('superadmin.backups.*') ? 'active' : '' }}">
        <i class="bx bx-data icon"></i> Backups
      </a>
      <a href="{{ Route::has('super-admin.tenant-audit.index') ? route('super-admin.tenant-audit.index') : (Route::has('superadmin.tenant-audit.index') ? route('superadmin.tenant-audit.index') : url('/super-admin/tenant-audit')) }}"
         class="{{ request()->routeIs('super-admin.tenant-audit.*') || request()->routeIs('superadmin.tenant-audit.*') ? 'active' : '' }}">
        <i class="bx bx-shield-quarter icon"></i> Tenant Audit
      </a>

      <!-- MONITORING -->
      <div class="nav-label">Monitoring</div>
      <a href="{{ Route::has('super-admin.system-health.index') ? route('super-admin.system-health.index') : (Route::has('superadmin.system-health.index') ? route('superadmin.system-health.index') : url('/super-admin/system-health')) }}"
         class="{{ request()->routeIs('super-admin.system-health.*') || request()->routeIs('superadmin.system-health.*') ? 'active' : '' }}">
        <i class="bx bx-pulse icon"></i> System Health
      </a>
      <a href="{{ Route::has('super-admin.alerts.index') ? route('super-admin.alerts.index') : (Route::has('superadmin.alerts.index') ? route('superadmin.alerts.index') : url('/super-admin/alerts')) }}"
         class="{{ request()->routeIs('super-admin.alerts.*') || request()->routeIs('superadmin.alerts.*') ? 'active' : '' }}">
        <i class="bx bx-bell icon"></i> Alerts <span class="badge">Live</span>
      </a>

      <!-- SECURITY & AUDIT -->
      <div class="nav-label">Security &amp; Audit</div>
      <a href="{{ Route::has('super-admin.tenant-audit.index') ? route('super-admin.tenant-audit.index') : (Route::has('super-admin.activity-logs.index') ? route('super-admin.activity-logs.index') : url('/super-admin/tenant-audit')) }}"
         class="{{ request()->routeIs('super-admin.tenant-audit.*') || request()->routeIs('super-admin.activity-logs.*') || request()->routeIs('tenant-audit.*') ? 'active' : '' }}">
        <i class="bx bx-history icon"></i> Activity Logs
      </a>
      <a href="{{ Route::has('superadmin.admins.index') ? route('superadmin.admins.index') : '#' }}" 
         class="{{ request()->routeIs('superadmin.admins.*') ? 'active' : '' }}">
        <i class="bx bx-user-voice icon"></i> Company Admins
      </a>
      <a href="{{ Route::has('super-admin.developers.index') ? route('super-admin.developers.index') : url('/super-admin/developers') }}" 
         class="{{ request()->routeIs('super-admin.developers.*') ? 'active' : '' }}">
        <i class="bx bx-code-alt icon"></i> Developer Management
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="status-row">
        <span class="dot"></span> Platform Live
      </div>
      <div class="version">v3.4.0 · Production</div>
    </div>
  </aside>

  <!-- MOBILE OVERLAY -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- MAIN CONTAINER -->
  <div class="main">
    <!-- TOP HEADER -->
    <header class="top-header">
      <div class="left">
        <button class="hamburger" id="hamburgerBtn" title="Toggle Sidebar" aria-label="Toggle Navigation">
          <i class="bx bx-menu"></i>
        </button>
        <div class="title-wrap">
          <h1 class="page-title">@yield('page_title', 'Dashboard')</h1>
          <div class="page-sub">@yield('page_subtitle', 'Central Command Center')</div>
        </div>
      </div>

      <!-- Header Search -->
      <div class="center">
        <div class="search-wrap">
          <i class="bx bx-search search-icon"></i>
          <input type="text" placeholder="Search platform..." id="globalHeaderSearch" aria-label="Global Search" />
          <span class="kbd">⌘K</span>
        </div>
      </div>

      <div class="right">
        <button class="btn-notif" id="btnNotif" title="System Alerts" aria-label="System Alerts">
          <i class="bx bx-bell"></i>
          <span class="badge-dot"></span>
        </button>

        <!-- Executive Profile Menu -->
        <div class="profile-wrapper" id="profileWrapper">
          <div class="profile" id="profileMenuToggle">
            <div class="avatar">
              {{ strtoupper(substr(auth('super_admin')->user()?->name ?? auth()->user()?->name ?? 'SA', 0, 2)) }}
            </div>
            <div class="info">
              <div class="name">{{ auth('super_admin')->user()?->name ?? auth()->user()?->name ?? 'Super Admin' }}</div>
              <div class="role">Platform Administrator</div>
            </div>
            <i class="bx bx-chevron-down arrow"></i>
          </div>

          <!-- Executive Profile Dropdown -->
          <div class="profile-dropdown" id="profileDropdown">
            <div class="profile-dropdown-header">
              <div class="user-name">{{ auth('super_admin')->user()?->name ?? auth()->user()?->name ?? 'Super Admin' }}</div>
              <div class="user-email">{{ auth('super_admin')->user()?->email ?? auth()->user()?->email ?? 'root@platform.io' }}</div>
            </div>
            <a href="#platform-health"><i class="bx bx-slider-alt" style="color:#2563eb;"></i> System Health</a>
            <a href="{{ Route::has('super-admin.tenant-audit.index') ? route('super-admin.tenant-audit.index') : url('/super-admin/tenant-audit') }}"><i class="bx bx-shield-alt-2" style="color:#7c3aed;"></i> Audit Activity</a>
            <div class="divider"></div>
            <button type="button" class="dropdown-item danger" onclick="document.getElementById('logoutForm').submit();">
              <i class="bx bx-log-out"></i> Sign Out
            </button>
          </div>
        </div>

        <form method="POST" action="{{ Route::has('super-admin.logout') ? route('super-admin.logout') : (Route::has('superadmin.logout') ? route('superadmin.logout') : route('logout')) }}" id="logoutForm" style="display:none;">
          @csrf
        </form>
      </div>
    </header>

    <!-- CONTENT BODY -->
    <div class="content">
      @if(session('success'))
        <div class="flash-alert success">
          <i class="bx bx-check-circle" style="font-size:18px;"></i> {{ session('success') }}
        </div>
      @endif

      @if($errors->any())
        <div class="flash-alert error">
          <i class="bx bx-error-circle" style="font-size:18px;"></i> {{ $errors->first() }}
        </div>
      @endif

      @yield('content')
    </div>
  </div>

  @yield('modals')

  <script>
    // Mobile Sidebar Toggle
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (hamburgerBtn && sidebar) {
      hamburgerBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        if (sidebarOverlay) sidebarOverlay.classList.toggle('open');
      });
    }
    if (sidebarOverlay) {
      sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('open');
      });
    }

    // Profile Dropdown Toggle
    const profileWrapper = document.getElementById('profileWrapper');
    const profileToggle = document.getElementById('profileMenuToggle');
    if (profileToggle && profileWrapper) {
      profileToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        profileWrapper.classList.toggle('open');
      });
      document.addEventListener('click', () => {
        profileWrapper.classList.remove('open');
      });
    }

    // Dropdown Toggles inside content
    document.querySelectorAll('.dropdown-toggle').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const menu = btn.nextElementSibling;
        document.querySelectorAll('.dropdown-menu').forEach(m => {
          if (m !== menu) m.classList.remove('show');
        });
        if (menu) menu.classList.toggle('show');
      });
    });
    document.addEventListener('click', () => {
      document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
    });

    // Close Modal Overlay on click outside
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.classList.remove('active');
      });
    });

    // Global Header Quick Table Search Filter
    const globalSearchInput = document.getElementById('globalHeaderSearch');
    if (globalSearchInput) {
      globalSearchInput.addEventListener('keyup', function() {
        const query = this.value.toLowerCase().trim();
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
          const text = row.textContent.toLowerCase();
          if (query === '' || text.includes(query)) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      });

      // Shortcut Cmd/Ctrl + K focus
      document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
          e.preventDefault();
          globalSearchInput.focus();
        }
      });
    }

    // Counter Animation for Numbers with data-counter-target
    document.addEventListener('DOMContentLoaded', () => {
      const counters = document.querySelectorAll('.value[data-counter-target]');
      counters.forEach(counter => {
        const target = parseFloat(counter.getAttribute('data-counter-target'));
        const prefix = counter.getAttribute('data-counter-prefix') || '';
        const suffix = counter.getAttribute('data-counter-suffix') || '';
        const isDecimal = counter.getAttribute('data-counter-decimal') === 'true';

        if (isNaN(target)) return;

        let start = 0;
        const duration = 1200;
        const startTime = performance.now();

        function updateCounter(currentTime) {
          const elapsed = currentTime - startTime;
          const progress = Math.min(elapsed / duration, 1);
          const easedProgress = progress * (2 - progress);
          const currentVal = start + (target - start) * easedProgress;

          let formattedVal = isDecimal 
            ? currentVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : Math.floor(currentVal).toLocaleString('en-US');

          counter.innerText = prefix + formattedVal + suffix;

          if (progress < 1) {
            requestAnimationFrame(updateCounter);
          }
        }
        requestAnimationFrame(updateCounter);
      });
    });
  </script>

  @stack('scripts')
</body>
</html>
