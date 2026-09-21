<!-- //layout main dashboard menu ... -->

<!-- //layout/manu.blade.php -->

@php $userId = Auth::id(); @endphp

<style>
   /* ======================================================
      BITROXIA ADMIN WORKSPACE — SIDEBAR & NAVBAR SYSTEM
      Derived from https://pms.thesmartservice.in/
      Color Direction:
        • Primary:    #2F6BFF (Professional Blue)
        • Secondary:  #06B6D4 (Cyan / Teal)
        • Supporting: #8B5CF6 (Violet / Purple)
        • Success:    #10B981 (Emerald)
        • Text:       #0F172A (Headings) / #334155 (Body) / #64748B (Muted)
        • Background: #F8FAFC / Surface: #FFFFFF
        • Border:     #E2E8F0 (Subtle Cool Gray)
      ====================================================== */

   :root {
       --bx-blue-primary: #2F6BFF;
       --bx-blue-hover: #1E4FCC;
       --bx-blue-soft: rgba(47, 107, 255, 0.08);
       --bx-blue-subtle: rgba(47, 107, 255, 0.04);
       --bx-cyan: #06B6D4;
       --bx-violet: #8B5CF6;
       --bx-emerald: #10B981;
       --bx-amber: #F59E0B;
       --bx-red: #EF4444;

       --bx-surface: #FFFFFF;
       --bx-surface-soft: #F8FAFC;
       --bx-surface-subtle: #F1F5F9;

       --bx-text-heading: #0F172A;
       --bx-text-body: #1E293B;
       --bx-text-secondary: #475569;
       --bx-text-muted: #64748B;
       --bx-text-faint: #94A3B8;

       --bx-border-subtle: #E2E8F0;
       --bx-border-soft: #CBD5E1;
       --bx-border-focus: rgba(47, 107, 255, 0.35);

       --bx-shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.03);
       --bx-shadow-md: 0 4px 12px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
       --bx-shadow-lg: 0 12px 24px -4px rgba(15, 23, 42, 0.08), 0 4px 8px -2px rgba(15, 23, 42, 0.03);
   }

   /* ===================== GLOBAL MODALS ===================== */
   .modal-backdrop.show {
       opacity: 0.45 !important;
   }

   body.modal-open {
       opacity: 1 !important;
   }

   .modal { z-index: 1050; }
   .modal-backdrop { z-index: 1040; }

   .modal-content {
       background-color: var(--bx-surface) !important;
       box-shadow: 0 16px 36px -4px rgba(15, 23, 42, 0.14) !important;
       border-radius: 16px !important;
       border: 1px solid var(--bx-border-subtle) !important;
       color: var(--bx-text-body);
   }

   .modal-header {
       border-bottom: 1px solid var(--bx-border-subtle) !important;
       color: var(--bx-text-heading);
       padding: 1.25rem 1.5rem;
   }

   .modal-title {
       color: var(--bx-text-heading) !important;
       font-weight: 700;
       font-size: 1.125rem;
   }

   .btn-close:hover {
       background-color: var(--bx-surface-subtle);
       border-radius: 8px;
   }

   .modal-footer {
       border-top: 1px solid var(--bx-border-subtle) !important;
       padding: 1rem 1.5rem;
   }

   /* ===================== TOP NAVBAR ===================== */
   #layout-navbar {
       display: flex;
       flex-wrap: nowrap !important;
       align-items: center;
       width: 100%;
       background: var(--bx-navbar-bg, rgba(255, 255, 255, 0.94)) !important;
       backdrop-filter: blur(16px);
       -webkit-backdrop-filter: blur(16px);
       box-shadow: var(--bx-shadow-sm) !important;
       border-radius: 16px;
       border: 1px solid var(--bx-border-subtle) !important;
       padding: 0.75rem 1.25rem;
       font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
       min-height: 64px;
       transition: background 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
   }

   #layout-navbar .nav-item,
   #layout-navbar .fw-bold,
   #layout-navbar .navbar-brand {
       font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
       font-size: 1rem !important;
       font-weight: 700 !important;
       letter-spacing: -0.01em;
       color: var(--bx-text-heading) !important;
   }

   #layout-navbar .navbar-nav {
       display: flex;
       flex-direction: row;
       flex-wrap: nowrap !important;
       align-items: center;
   }

   #layout-navbar .navbar-nav > * {
       flex: 0 0 auto;
       white-space: nowrap;
   }

   .navbar-nav > div {
       display: contents;
   }

   .navbar-nav-right,
   #layout-navbar .navbar-nav-right {
       display: flex;
       flex-direction: row !important;
       flex-wrap: nowrap !important;
       align-items: center;
       gap: 10px;
   }

   /* Search Box */
   #layout-navbar .nav-item input[type="text"] {
       background: var(--bx-surface-soft) !important;
       border: 1px solid var(--bx-border-subtle) !important;
       border-radius: 10px !important;
       color: var(--bx-text-body) !important;
       font-size: 0.875rem !important;
       font-weight: 500;
       transition: all 0.2s ease;
   }

   #layout-navbar .nav-item input[type="text"]::placeholder {
       color: var(--bx-text-muted) !important;
       opacity: 0.85;
   }

   #layout-navbar .nav-item input[type="text"]:focus,
   #layout-navbar .nav-item:hover input[type="text"] {
       border-color: var(--bx-blue-primary) !important;
       background: var(--bx-surface) !important;
       box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.12) !important;
   }

   /* Header Action Buttons / Icons */
   .header-icon-box {
       display: flex;
       align-items: center;
       justify-content: center;
       min-width: 38px;
       height: 38px;
       border-radius: 10px;
       background: var(--bx-surface-soft);
       border: 1px solid var(--bx-border-subtle);
       color: var(--bx-text-secondary);
       transition: all 0.2s ease;
       text-decoration: none;
   }

   .header-icon-box:hover {
       color: var(--bx-blue-primary);
       background: var(--bx-blue-soft);
       border-color: rgba(47, 107, 255, 0.2);
       transform: translateY(-1px);
       box-shadow: 0 2px 6px rgba(47, 107, 255, 0.08);
   }

   .header-icon-box i {
       color: inherit;
       font-size: 1.15rem;
       transition: color 0.2s ease;
   }

   .theme-toggle-btn {
       width: 38px;
       height: 38px;
       border-radius: 10px;
       border: 1px solid var(--bx-border-subtle);
       background: var(--bx-surface-soft);
       color: var(--bx-text-secondary);
       display: inline-flex;
       align-items: center;
       justify-content: center;
       cursor: pointer;
       transition: all 0.2s ease;
   }

   .theme-toggle-btn:hover {
       color: var(--bx-blue-primary);
       background: var(--bx-blue-soft);
       border-color: rgba(47, 107, 255, 0.2);
       transform: translateY(-1px);
   }

   /* ===================== SIDEBAR ===================== */
   .layout-menu {
       transition: transform 0.3s ease, width 0.3s ease;
       background: var(--bx-surface) !important;
       border-right: 1px solid var(--bx-border-subtle) !important;
       box-shadow: none !important;
   }

   .bg-menu-theme {
       background: var(--bx-surface) !important;
   }

   .app-brand {
       background: var(--bx-surface) !important;
       border-bottom: 1px solid var(--bx-border-subtle);
       padding: 1.125rem 1.25rem !important;
       min-height: 68px;
       display: flex;
       align-items: center;
   }

   .app-brand-logo.demo {
       width: 38px;
       height: 38px;
       border-radius: 10px;
       overflow: hidden;
       display: inline-flex;
       align-items: center;
       justify-content: center;
       flex: 0 0 auto;
       background: transparent;
   }

   .app-brand-logo.demo img {
       width: 100%;
       height: 100%;
       object-fit: contain;
       display: block;
   }

   .app-brand-text.demo {
       font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif !important;
       font-size: 1.125rem !important;
       font-weight: 800 !important;
       letter-spacing: -0.02em !important;
       color: var(--bx-text-heading) !important;
       -webkit-text-fill-color: initial !important;
   }

   .menu-divider {
       border-color: var(--bx-border-subtle) !important;
       margin: 0 !important;
   }

   /* Menu Item Styling */
   .menu-inner {
       padding: 0.75rem 0.5rem !important;
   }

   .menu-inner .menu-item {
       margin-bottom: 2px;
   }

   .menu-inner .menu-item .menu-link {
       color: var(--bx-text-body) !important;
       font-size: 0.875rem !important;
       font-weight: 600 !important;
       padding: 0.6rem 0.875rem !important;
       border-radius: 10px !important;
       transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
       position: relative;
   }

   .menu-inner .menu-item .menu-link i,
   .menu-inner .menu-item .menu-link .menu-icon {
       color: var(--bx-text-muted) !important;
       font-size: 1.18rem !important;
       margin-right: 0.75rem !important;
       transition: color 0.18s ease, transform 0.18s ease;
   }

   /* Hover State */
   .menu-inner .menu-item:hover > .menu-link {
       background-color: var(--bx-blue-soft) !important;
       color: var(--bx-blue-primary) !important;
   }

   .menu-inner .menu-item:hover > .menu-link i,
   .menu-inner .menu-item:hover > .menu-link .menu-icon {
       color: var(--bx-blue-primary) !important;
       transform: translateX(1px);
   }

   /* Active State — Modern SaaS Accent */
   .menu-inner .menu-item.active > .menu-link {
       background: var(--bx-blue-soft) !important;
       color: var(--bx-blue-primary) !important;
       font-weight: 700 !important;
       box-shadow: inset 3px 0 0 var(--bx-blue-primary) !important;
   }

   .menu-inner .menu-item.active > .menu-link i,
   .menu-inner .menu-item.active > .menu-link .menu-icon {
       color: var(--bx-blue-primary) !important;
   }

   /* Open state for parent menus */
   .menu-inner .menu-item.open > .menu-link {
       background-color: var(--bx-surface-soft) !important;
       color: var(--bx-text-heading) !important;
       font-weight: 650 !important;
   }

   .menu-inner .menu-item.open > .menu-link i {
       color: var(--bx-blue-primary) !important;
   }

   /* Submenus */
   .menu-sub {
       background: transparent !important;
       padding-left: 0.5rem !important;
   }

   .menu-sub .menu-item .menu-link {
       color: var(--bx-text-secondary) !important;
       font-weight: 550 !important;
       font-size: 0.84rem !important;
       padding: 0.48rem 0.875rem !important;
       border-radius: 8px !important;
   }

   .menu-sub .menu-item:hover .menu-link {
       color: var(--bx-blue-primary) !important;
       background-color: var(--bx-blue-soft) !important;
   }

   .menu-sub .menu-item.active .menu-link {
       color: var(--bx-blue-primary) !important;
       font-weight: 700 !important;
       background: var(--bx-blue-soft) !important;
       box-shadow: inset 2px 0 0 var(--bx-blue-primary) !important;
   }

   /* Section Headers */
   .menu-header {
       padding: 1.25rem 1rem 0.4rem !important;
   }

   .menu-header-text {
       font-size: 0.7rem !important;
       font-weight: 750 !important;
       text-transform: uppercase !important;
       letter-spacing: 0.06em !important;
       color: var(--bx-text-faint) !important;
   }

   /* ===================== DROPDOWNS ===================== */
   .dropdown-menu {
       background: var(--bx-surface) !important;
       border: 1px solid var(--bx-border-subtle) !important;
       border-radius: 14px !important;
       box-shadow: var(--bx-shadow-lg) !important;
       padding: 0.5rem !important;
   }

   .dropdown-item {
       color: var(--bx-text-body) !important;
       font-weight: 550;
       border-radius: 8px;
       padding: 0.55rem 0.85rem;
       transition: all 0.15s ease;
   }

   .dropdown-item:hover {
       background-color: var(--bx-blue-soft) !important;
       color: var(--bx-blue-primary) !important;
   }

   .dropdown-item i {
       color: var(--bx-text-muted);
       transition: color 0.15s ease;
   }

   .dropdown-item:hover i {
       color: var(--bx-blue-primary);
   }

   .dropdown-divider {
       border-color: var(--bx-border-subtle) !important;
       margin: 0.4rem 0 !important;
   }

   /* ===================== NOTIFICATIONS ===================== */
   .notification-bell {
       position: relative;
       width: 38px;
       height: 38px;
       border-radius: 10px;
       display: inline-flex;
       align-items: center;
       justify-content: center;
       background: var(--bx-surface-soft);
       border: 1px solid var(--bx-border-subtle);
       color: var(--bx-text-secondary);
       transition: all 0.2s ease;
       text-decoration: none;
   }

   .notification-bell:hover {
       background: var(--bx-blue-soft);
       border-color: rgba(47, 107, 255, 0.2);
       color: var(--bx-blue-primary);
       transform: translateY(-1px);
   }

   .notification-bell.has-unread {
       color: var(--bx-blue-primary);
   }

   .notification-bell.has-unread i {
       animation: pmsBellShake 1.25s ease-in-out infinite;
       transform-origin: top center;
   }

   .notification-bell .badge {
       position: absolute;
       top: -5px;
       right: -5px;
       min-width: 18px;
       height: 18px;
       border-radius: 999px;
       background: var(--bx-red) !important;
       color: #FFFFFF !important;
       font-size: 10px;
       font-weight: 800;
       display: inline-flex;
       align-items: center;
       justify-content: center;
       border: 2px solid var(--bx-surface);
       padding: 0 4px;
   }

   .notification-dropdown {
       width: min(420px, calc(100vw - 24px)) !important;
       max-height: 540px;
       overflow: hidden;
       border-radius: 16px;
       border: 1px solid var(--bx-border-subtle) !important;
       box-shadow: var(--bx-shadow-lg) !important;
       padding: 0 !important;
   }

   .notification-dropdown-head {
       padding: 14px 18px;
       background: var(--bx-surface-soft);
       border-bottom: 1px solid var(--bx-border-subtle);
   }

   .notification-dropdown-body {
       max-height: 380px;
       overflow-y: auto;
       padding: 6px;
   }

   .notification-card-link {
       display: grid;
       grid-template-columns: 40px 1fr auto;
       gap: 12px;
       align-items: start;
       padding: 10px 12px;
       border-radius: 10px;
       text-decoration: none;
       color: var(--bx-text-body);
       border: 1px solid transparent;
       transition: all 0.15s ease;
   }

   .notification-card-link:hover {
       background: var(--bx-surface-soft);
       border-color: var(--bx-border-subtle);
   }

   .notification-card-link.is-unread {
       background: var(--bx-blue-soft);
       border-color: rgba(47, 107, 255, 0.12);
   }

   .notification-avatar-icon {
       width: 40px;
       height: 40px;
       border-radius: 10px;
       display: inline-flex;
       align-items: center;
       justify-content: center;
       color: #fff;
       font-size: 16px;
       background: var(--bx-blue-primary);
   }

   .notification-avatar-icon.color-warning { background: var(--bx-amber); }
   .notification-avatar-icon.color-success { background: var(--bx-emerald); }
   .notification-avatar-icon.color-danger { background: var(--bx-red); }
   .notification-avatar-icon.color-info { background: var(--bx-cyan); }

   .notification-title {
       display: block;
       font-size: 13.5px;
       font-weight: 700;
       color: var(--bx-text-heading);
       line-height: 1.25;
       margin-bottom: 3px;
   }

   .notification-message {
       display: block;
       font-size: 12px;
       color: var(--bx-text-muted);
       line-height: 1.35;
       margin-bottom: 4px;
   }

   .notification-time {
       display: inline-flex;
       align-items: center;
       gap: 4px;
       font-size: 11px;
       color: var(--bx-text-faint);
       font-weight: 600;
   }

   .notification-unread-dot {
       width: 8px;
       height: 8px;
       margin-top: 14px;
       border-radius: 50%;
       background: var(--bx-blue-primary);
   }

   .notification-dropdown-foot {
       display: flex;
       justify-content: space-between;
       gap: 10px;
       padding: 10px 16px;
       border-top: 1px solid var(--bx-border-subtle);
       background: var(--bx-surface);
   }

   @keyframes pmsBellShake {
       0%, 100% { transform: rotate(0); }
       15% { transform: rotate(10deg); }
       30% { transform: rotate(-8deg); }
       45% { transform: rotate(6deg); }
       60% { transform: rotate(-4deg); }
       75% { transform: rotate(2deg); }
   }

   /* ===================== BADGES & BUTTONS ===================== */
   .btn-primary {
       background: var(--bx-blue-primary) !important;
       border-color: var(--bx-blue-primary) !important;
       color: #FFFFFF !important;
       font-weight: 600 !important;
       border-radius: 10px !important;
       box-shadow: 0 1px 3px rgba(47, 107, 255, 0.25) !important;
       transition: all 0.2s ease !important;
   }

   .btn-primary:hover {
       background: var(--bx-blue-hover) !important;
       border-color: var(--bx-blue-hover) !important;
       box-shadow: 0 4px 12px rgba(47, 107, 255, 0.3) !important;
       transform: translateY(-1px);
   }

   .btn-outline-primary {
       border-color: var(--bx-blue-primary) !important;
       color: var(--bx-blue-primary) !important;
       font-weight: 600 !important;
       border-radius: 10px !important;
       background: transparent !important;
       transition: all 0.2s ease !important;
   }

   .btn-outline-primary:hover {
       background: var(--bx-blue-primary) !important;
       color: #FFFFFF !important;
       box-shadow: 0 2px 8px rgba(47, 107, 255, 0.25) !important;
   }

   .badge.bg-primary {
       background: var(--bx-blue-primary) !important;
       color: #FFFFFF !important;
   }

   .badge.bg-success {
       background: var(--bx-emerald) !important;
       color: #FFFFFF !important;
   }

   .badge.bg-warning {
       background: var(--bx-amber) !important;
       color: #FFFFFF !important;
   }

   .badge.bg-danger {
       background: var(--bx-red) !important;
       color: #FFFFFF !important;
   }

   /* ===================== USER AVATAR ===================== */
   .avatar-online img,
   .navbar-profile-avatar {
       width: 38px !important;
       height: 38px !important;
       border-radius: 50% !important;
       border: 2px solid var(--bx-blue-primary) !important;
       object-fit: cover;
   }

   /* Sticky Note Trigger */
   .sticky-note-trigger {
       position: relative;
   }

   .sticky-note-count {
       position: absolute;
       top: -6px;
       right: -6px;
       min-width: 18px;
       height: 18px;
       padding: 0 5px;
       border-radius: 999px;
       background: var(--bx-red);
       color: #fff;
       font-size: 10px;
       font-weight: 800;
       display: inline-flex;
       align-items: center;
       justify-content: center;
       border: 2px solid var(--bx-surface);
   }

   .sidebar-notification-badge {
       margin-left: auto;
       min-width: 18px;
       height: 18px;
       padding: 0 5px;
       border-radius: 999px;
       display: none;
       align-items: center;
       justify-content: center;
       font-size: 10px;
       font-weight: 800;
       line-height: 1;
       color: #fff;
       background: var(--bx-blue-primary);
   }

   .sidebar-notification-badge.is-visible {
       display: inline-flex;
   }

   .sidebar-notification-badge.type-new { background: var(--bx-blue-primary); }
   .sidebar-notification-badge.type-pending { background: var(--bx-amber); }
   .sidebar-notification-badge.type-issue { background: var(--bx-red); }
   .sidebar-notification-badge.type-warning { background: var(--bx-amber); }
   .sidebar-notification-badge.type-unread { background: var(--bx-blue-primary); }

   /* Sticky Notes System Styling */
   .sticky-note-dock {
       position: fixed;
       right: 20px;
       bottom: 20px;
       z-index: 1030;
   }

   .sticky-note-card {
       position: relative;
       padding: 16px 14px 12px;
       border-radius: 12px;
       box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
       color: #1e293b;
       overflow: hidden;
       transition: transform 0.2s ease, box-shadow 0.2s ease;
   }

   .sticky-note-card:hover {
       transform: translateY(-2px) rotate(0deg) !important;
       box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
   }

   .sticky-note-card.yellow { background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1px solid #fde68a; }
   .sticky-note-card.blue { background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #bfdbfe; }
   .sticky-note-card.red { background: linear-gradient(135deg, #fef2f2, #fee2e2); border: 1px solid #fecaca; }
   .sticky-note-card.gray { background: linear-gradient(135deg, #f8fafc, #f1f5f9); border: 1px solid #e2e8f0; }
   .sticky-note-card.purple { background: linear-gradient(135deg, #faf5ff, #f3e8ff); border: 1px solid #e9d5ff; }
   .sticky-note-card.green { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #bbf7d0; }

   .sticky-note-card p {
       margin: 0 0 8px;
       font-size: 0.9rem;
       font-weight: 600;
       line-height: 1.4;
       white-space: pre-line;
   }

   .sticky-note-meta {
       font-size: 0.72rem;
       font-weight: 650;
       color: #64748b;
   }

   .sticky-note-actions {
       position: absolute;
       top: 8px;
       right: 8px;
       display: flex;
       gap: 4px;
   }

   .sticky-note-actions button {
       width: 24px;
       height: 24px;
       border: 0;
       border-radius: 6px;
       background: rgba(255, 255, 255, 0.75);
       color: #1e293b;
       display: inline-flex;
       align-items: center;
       justify-content: center;
       cursor: pointer;
   }

   .sticky-notes-grid {
       display: grid;
       grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
       gap: 14px;
   }

   /* ===================== DARK MODE SUPPORT ===================== */
   html[data-pms-theme="dark"],
   html[data-theme="dark"] {
       --bx-surface: #0F1530;
       --bx-surface-soft: #141B3D;
       --bx-surface-subtle: #1A2247;
       --bx-text-heading: #EEF1FB;
       --bx-text-body: #CBD5E1;
       --bx-text-secondary: #9AA3C7;
       --bx-text-muted: #6B739A;
       --bx-text-faint: #4B5578;
       --bx-border-subtle: rgba(238, 241, 251, 0.09);
       --bx-border-soft: rgba(238, 241, 251, 0.16);
       --bx-navbar-bg: rgba(15, 21, 48, 0.92);
   }

   html[data-pms-theme="dark"] #layout-navbar,
   html[data-theme="dark"] #layout-navbar {
       background: rgba(15, 21, 48, 0.92) !important;
       border-color: rgba(238, 241, 251, 0.09) !important;
   }

   html[data-pms-theme="dark"] .layout-menu,
   html[data-theme="dark"] .layout-menu {
       background: #0B1026 !important;
       border-color: rgba(238, 241, 251, 0.09) !important;
   }

   html[data-pms-theme="dark"] .app-brand,
   html[data-theme="dark"] .app-brand {
       background: #0B1026 !important;
       border-color: rgba(238, 241, 251, 0.09) !important;
   }

   html[data-pms-theme="dark"] .app-brand-text.demo,
   html[data-theme="dark"] .app-brand-text.demo {
       color: #EEF1FB !important;
   }

   html[data-pms-theme="dark"] .header-icon-box,
   html[data-theme="dark"] .header-icon-box,
   html[data-pms-theme="dark"] .theme-toggle-btn,
   html[data-theme="dark"] .theme-toggle-btn {
       background: #141B3D;
       border-color: rgba(238, 241, 251, 0.09);
       color: #CBD5E1;
   }

   html[data-pms-theme="dark"] .dropdown-menu,
   html[data-theme="dark"] .dropdown-menu {
       background: #0F1530 !important;
       border-color: rgba(238, 241, 251, 0.12) !important;
   }

   html[data-pms-theme="dark"] .modal-content,
   html[data-theme="dark"] .modal-content {
       background-color: #0F1530 !important;
       color: #CBD5E1 !important;
       border-color: rgba(238, 241, 251, 0.12) !important;
   }

   html[data-pms-theme="dark"] .modal-header,
   html[data-theme="dark"] .modal-header,
   html[data-pms-theme="dark"] .modal-footer,
   html[data-theme="dark"] .modal-footer {
       border-color: rgba(238, 241, 251, 0.09) !important;
   }

   html[data-pms-theme="dark"] .modal-title,
   html[data-theme="dark"] .modal-title {
       color: #EEF1FB !important;
   }

   html[data-pms-theme="dark"] .notification-dropdown-head,
   html[data-theme="dark"] .notification-dropdown-head {
       background: #141B3D !important;
       border-color: rgba(238, 241, 251, 0.09) !important;
   }

   html[data-pms-theme="dark"] .notification-dropdown-foot,
   html[data-theme="dark"] .notification-dropdown-foot {
       background: #0F1530 !important;
       border-color: rgba(238, 241, 251, 0.09) !important;
   }

   html[data-pms-theme="dark"] .notification-title,
   html[data-theme="dark"] .notification-title {
       color: #EEF1FB !important;
   }

   html[data-pms-theme="dark"] .notification-message,
   html[data-theme="dark"] .notification-message {
       color: #9AA3C7 !important;
   }

   html[data-pms-theme="dark"] .notification-card-link:hover,
   html[data-theme="dark"] .notification-card-link:hover {
       background: #141B3D !important;
   }

   @media (max-width: 992px) {
       #layout-navbar {
           padding: 0.5rem 0.75rem;
       }
       #layout-navbar input[type="text"] {
           display: none !important;
       }
   }
</style>
@php
    $adminRefreshVersion = file_exists(public_path('admin/assets/css/pms-refresh.css')) ? filemtime(public_path('admin/assets/css/pms-refresh.css')) : time();
    $logoVersion = file_exists(public_path('logo.png')) ? filemtime(public_path('logo.png')) : time();
    $companySetting = \App\Models\CompanySetting::first();
    $brandLogo = $currentCompany?->logo ? asset($currentCompany->logo) : ($companySetting?->company_logo ? asset($companySetting->company_logo) : asset('logo.png') . '?v=' . $logoVersion);
    $brandName = $currentCompany?->brand_name ?? ($companySetting?->company_name ?? 'Bitroxia');
@endphp
<link rel="stylesheet" href="{{ asset('admin/assets/css/pms-refresh.css') }}?v={{ $adminRefreshVersion }}">

<body>
    @php
    use App\Models\Project;
    use App\Models\Task;
    use App\Models\TaskTimer;
    use App\Models\Ticket;
    use App\Services\SidebarNotificationService;

    $projects = Project::all();
    $tasks = Task::all();

    $activeTimer = TaskTimer::where('user_id', auth()->id())
        ->whereNull('end_time')
        ->latest()
        ->with('task.project')
        ->first();

    $stickyNotes = \App\Models\StickyNote::where('user_id', auth()->id())
        ->whereNull('completed_at')
        ->latest()
        ->get();

    $canCreateWorkItems = in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true);
    $canSeeModule = fn (string $slug) => auth()->user()?->canViewModule($slug) ?? false;
    $canAnyModule = fn (array $slugs) => collect($slugs)->contains(fn ($slug) => $canSeeModule($slug));
    $isEmployeeUser = strtolower((string) auth()->user()?->role) === 'employee';
    $userId = auth()->id();
    $navbarNotifications = auth()->user()->notifications()->latest()->take(8)->get();
    $navbarUnreadCount = auth()->user()->unreadNotifications()->count();
    $sidebarNotificationItems = SidebarNotificationService::forUser(auth()->user());
    $assignedWorkProjects = collect();
    $assignedWorkTasks = collect();
    $assignedWorkTickets = collect();
    $timerProjects = $projects;
    $timerTasks = $tasks;

    if ($isEmployeeUser) {
        $assignedWorkProjects = Project::withCount(['tasks' => function ($query) use ($userId) {
                $query->where(function ($taskQuery) use ($userId) {
                    $taskQuery->whereHas('assignees', function ($assignees) use ($userId) {
                        $assignees->where('users.id', $userId);
                    })->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$userId]);
                });
            }])
            ->where(function ($query) use ($userId) {
                $query->whereHas('users', function ($members) use ($userId) {
                    $members->where('users.id', $userId);
                })->orWhereHas('tasks', function ($taskQuery) use ($userId) {
                    $taskQuery->whereHas('assignees', function ($assignees) use ($userId) {
                        $assignees->where('users.id', $userId);
                    })->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$userId]);
                });
            })
            ->latest()
            ->take(5)
            ->get();

        $assignedWorkTasks = Task::with('project')
            ->where(function ($taskQuery) use ($userId) {
                $taskQuery->whereHas('assignees', function ($assignees) use ($userId) {
                    $assignees->where('users.id', $userId);
                })->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$userId]);
            })
            ->latest()
            ->take(5)
            ->get();

        $assignedWorkTickets = Ticket::with('project')
            ->where(function ($ticketQuery) use ($userId) {
                $ticketQuery->where('agent_id', $userId)
                    ->orWhere('requester_id', $userId);
            })
            ->whereNotIn('status', ['resolved', 'closed'])
            ->latest()
            ->take(5)
            ->get();

        $timerProjects = Project::where(function ($query) use ($userId) {
                $query->whereHas('users', function ($members) use ($userId) {
                    $members->where('users.id', $userId);
                })->orWhereHas('tasks', function ($taskQuery) use ($userId) {
                    $taskQuery->whereHas('assignees', function ($assignees) use ($userId) {
                        $assignees->where('users.id', $userId);
                    })->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$userId]);
                });
            })
            ->orderBy('name')
            ->get();

        $timerTasks = Task::with('project')
            ->where(function ($taskQuery) use ($userId) {
                $taskQuery->whereHas('assignees', function ($assignees) use ($userId) {
                    $assignees->where('users.id', $userId);
                })->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$userId]);
            })
            ->latest()
            ->get();
    }
    @endphp

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="{{ route('dashboard') }}" class="app-brand-link">
              <span class="app-brand-logo demo">
                <img src="{{ $brandLogo }}" alt="{{ $brandName }} logo">
              </span>
              <span class="app-brand-text demo menu-text fw-bold ms-2">{{ $brandName }}</span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
              <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
            </a>
          </div>

          <div class="menu-divider mt-0"></div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">
            @php
                $sidebarCompanyObj = app(\App\Services\CompanyContext::class)->current();
                if (! $sidebarCompanyObj && auth()->check() && auth()->user()?->company_id) {
                    $sidebarCompanyObj = \App\Models\Company::find(auth()->user()->company_id);
                }
                $isSidebarSuspended = $sidebarCompanyObj ? $sidebarCompanyObj->isSuspended() : false;
            @endphp

            @if($isSidebarSuspended)
                <div class="px-3 py-3 mx-2 my-2 text-center" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 12px; color: #ef4444;">
                    <i class="bx bx-lock-alt fs-3 d-block mb-1"></i>
                    <div class="fw-bold fs-7">ACCOUNT SUSPENDED</div>
                    <small class="d-block mt-1 text-muted" style="font-size: 11px;">Subscription expired. Upgrade or renew to unlock full PMS features.</small>
                </div>

                <li class="menu-item {{ request()->routeIs('subscription.suspended') ? 'active' : '' }}">
                    <a href="{{ route('subscription.suspended') }}" class="menu-link text-danger fw-bold">
                        <i class="menu-icon tf-icons bx bx-zap text-danger"></i>
                        <div class="text-truncate">Reactivate Plan</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('notifications.*') || request()->routeIs('admin.company-notifications.*') ? 'active' : '' }}">
                    <a href="{{ route('notifications.all') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-bell"></i>
                        <div class="text-truncate">Notifications Center</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.company-complaints.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.company-complaints.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-support"></i>
                        <div class="text-truncate">Platform Support</div>
                    </a>
                </li>
                <li class="menu-item">
                    <form method="POST" action="{{ route('logout') }}" id="sidebarSuspendedLogoutForm">
                        @csrf
                        <a href="javascript:void(0);" onclick="document.getElementById('sidebarSuspendedLogoutForm').submit();" class="menu-link text-muted">
                            <i class="menu-icon tf-icons bx bx-log-out"></i>
                            <div class="text-truncate">Log Out</div>
                        </a>
                    </form>
                </li>
            @else

            @if($canSeeModule('dashboard'))
            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
              <a href="{{ route('dashboard') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-home-smile"></i>
                  <div class="text-truncate" data-i18n="Dashboard">Dashboard</div>
              </a>
            </li>
            @endif

            @if($canSeeModule('notifications'))
            <li class="menu-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
              <a href="{{ route('notifications.all') }}" class="menu-link" data-sidebar-key="notifications">
                  <i class="menu-icon tf-icons bx bx-bell"></i>
                  <div class="text-truncate" data-i18n="Notifications">Notifications</div>
              </a>
            </li>
            @endif

            <!-- My Documents -->
            <li class="menu-item {{ request()->routeIs('my-documents.*') ? 'active' : '' }}">
              <a href="{{ route('my-documents.index') }}" class="menu-link" data-sidebar-key="my-documents">
                  <i class="menu-icon tf-icons bx bx-file"></i>
                  <div class="text-truncate" data-i18n="My Documents">My Documents</div>
              </a>
            </li>

            <!-- My Projects (for Employee) -->
            @if($isEmployeeUser || $canSeeModule('projects'))
            <li class="menu-item {{ (request()->routeIs('projects.*') && !request()->routeIs('projects.tasks.*') && !request()->routeIs('projects.timelogs.*')) ? 'active' : '' }}">
              <a href="{{ route('projects.index') }}" class="menu-link" data-sidebar-key="my-projects">
                  <i class="menu-icon tf-icons bx bx-briefcase-alt-2"></i>
                  <div class="text-truncate" data-i18n="My Projects">My Projects</div>
              </a>
            </li>
            @endif

            @if($canSeeModule('organization'))
            <li class="menu-item {{ request()->routeIs('organization.*') ? 'active' : '' }}">
              <a href="{{ route('organization.index') }}" class="menu-link" data-sidebar-key="organization">
                  <i class="menu-icon tf-icons bx bx-sitemap"></i>
                  <div class="text-truncate">Organization</div>
              </a>
            </li>
            @endif

            <!-- Events -->
            @if($canSeeModule('events') || (auth()->check() && auth()->user()->normalizedRole() !== 'superadmin'))
            <li class="menu-item {{ request()->routeIs('events.*') ? 'active' : '' }}">
              <a href="{{ route('events.index') }}" class="menu-link" data-sidebar-key="events">
                  <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                  <div class="text-truncate" data-i18n="Events">Events</div>
              </a>
            </li>
            @endif

            <!-- Community Message -->
            @if(auth()->check() && auth()->user()->normalizedRole() !== 'superadmin')
            <li class="menu-item {{ request()->routeIs('community.*') ? 'active' : '' }}">
              <a href="{{ route('community.index') }}" class="menu-link" data-sidebar-key="community">
                  <i class="menu-icon tf-icons bx bx-chat"></i>
                  <div class="text-truncate" data-i18n="Community">Community</div>
              </a>
            </li>
            @endif



            <!-- Layouts -->
            @if($canAnyModule(['employees', 'designations', 'departments', 'attendance', 'leaves', 'holidays', 'awards', 'recruitment', 'appraisal']))
            <li class="menu-item {{ request()->routeIs('employees.*') ||
                      request()->routeIs('designations.*') ||
                      (request()->routeIs('attendance.*') && !request()->routeIs('attendance.report')) ||
                      request()->routeIs('leaves.*') ||
                      request()->routeIs('holidays.*') ||
                      request()->routeIs('awards.*') ||
                      request()->routeIs('employee.awards') ||
                      request()->routeIs('recruitment.*') ||
                      request()->routeIs('appraisal.*')
                      ? 'active open' : '' }}">

              <a href="javascript:void(0);" class="menu-link menu-toggle" data-sidebar-key="hr">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div class="text-truncate" data-i18n="Layouts">HR</div>
              </a>



              <ul class="menu-sub">
                    @if($canSeeModule('employees'))
                        <li class="menu-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <a href="{{ route('employees.index') }}" class="menu-link" data-sidebar-key="employees">
                                <div class="text-truncate" data-i18n="Without menu">Employee</div>
                            </a>
                        </li>
                    @endif

                    @if($canSeeModule('recruitment'))
                        <li class="menu-item {{ request()->routeIs('recruitment.*') ? 'active' : '' }}">
                            <a href="{{ route('recruitment.index') }}" class="menu-link" data-sidebar-key="recruitment">
                                <div class="text-truncate" data-i18n="Recruitment">Recruitment</div>
                            </a>
                        </li>
                    @endif

                    @if($canSeeModule('appraisal'))
                        <li class="menu-item {{ request()->routeIs('appraisal.*') ? 'active' : '' }}">
                            <a href="{{ route('appraisal.index') }}" class="menu-link" data-sidebar-key="appraisal">
                                <div class="text-truncate" data-i18n="Appraisal">Appraisal</div>
                            </a>
                        </li>
                    @endif

                    @if($canSeeModule('designations'))
                        <li class="menu-item {{ request()->routeIs('designations.*') ? 'active' : '' }}">
                            <a href="{{ route('designations.index') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Without menu">Designation</div>
                            </a>
                        </li>

 <!-- Department with Submenu -->
                  @if($canSeeModule('departments'))
                  <li class="menu-item {{ request()->routeIs('parent-departments.*') || request()->routeIs('departments.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                      <div class="text-truncate">Department</div>
                    </a>
                    <ul class="menu-sub">
                      <li class="menu-item {{ request()->routeIs('parent-departments.*') ? 'active' : '' }}">
                        <a href="{{ route('parent-departments.index') }}" class="menu-link">
                          <div class="text-truncate">Parent Department</div>
                        </a>
                      </li>
                      <li class="menu-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                        <a href="{{ route('departments.index') }}" class="menu-link">
                          <div class="text-truncate">Sub Department</div>
                        </a>
                      </li>
                    </ul>
                  </li>
                  @endif
                @endif


                     @if($canSeeModule('attendance'))
                    <li class="menu-item {{ (request()->routeIs('attendance.*') && !request()->routeIs('attendance.report')) ? 'active' : '' }}">
                          <a href="{{ route('attendance.index') }}" class="menu-link" data-sidebar-key="attendance">
                            <div class="text-truncate" data-i18n="Without menu">
                              {{ auth()->user()->role == 'admin' ? 'Attendance' : 'My Attendance' }}
                            </div>
                          </a>
                    </li>
                    @endif


<!--
                @if(auth()->user()->role === 'admin')
                <li class="menu-item {{ request()->routeIs('attendance.report') ? 'active open' : '' }}">
                  <a href="{{ route('attendance.report') }}" class="menu-link">
                    <div class="text-truncate" data-i18n="Without menu">Attendance Report</div>
                  </a>
                </li>
                 @endif -->



                @if($canSeeModule('leaves'))
                <li class="menu-item {{ request()->routeIs('leaves.*') ? 'active' : '' }}">
                <a href="{{ route('leaves.index') }}" class="menu-link" data-sidebar-key="leaves">
                    <div class="text-truncate" data-i18n="Without navbar">My Leaves</div>
                </a>
                </li>
                @endif


                <!-- @if(auth()->user()->role === 'admin')
                <li class="menu-item {{ request()->routeIs('admin.leave.report') ? 'active open' : '' }}">
                <a href="{{ route('admin.leave.report') }}" class="menu-link">
                    <div class="text-truncate" data-i18n="Without navbar">Leaves Report</div>
                </a>
                </li>
                @endif -->

                {{-- Employee Holiday View --}}
                @if($canSeeModule('holidays'))
                <li class="menu-item {{ request()->routeIs('holidays.*') ? 'active' : '' }}">
                <a href="{{ route('holidays.calendar') }}" class="menu-link" data-sidebar-key="holidays">
                    <div class="text-truncate">Holiday List</div>
                </a>
                </li>
                @endif


                    @if($canSeeModule('awards') && auth()->user()->role === 'admin')
                    <!-- Admin sees Appreciation menu -->
                    <li class="menu-item {{ request()->routeIs('awards.*') ? 'active' : '' }}">
                        <a href="{{ route('awards.index') }}" class="menu-link">
                            <div class="text-truncate" data-i18n="Container">Recognition</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('letterhead.*') || request()->routeIs('admin.letterhead.*') ? 'active' : '' }}">
                        <a href="{{ route('letterhead.index') }}" class="menu-link">
                            <div class="text-truncate" data-i18n="Letter Head">Letter Head</div>
                        </a>
                    </li>
                    @elseif($canSeeModule('awards') && auth()->user()->role === 'employee')
                    <!-- Employee also sees Recognition menu but goes to filtered view -->
                    <li class="menu-item {{ request()->routeIs('awards.*') || request()->routeIs('employee.awards') ? 'active' : '' }}">
                        <a href="{{ route('awards.index') }}" class="menu-link">
                            <div class="text-truncate" data-i18n="Container">My Awards</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('letterhead.*') || request()->routeIs('admin.letterhead.*') ? 'active' : '' }}">
                        <a href="{{ route('letterhead.index') }}" class="menu-link">
                            <div class="text-truncate" data-i18n="Letter Head">Letter Head</div>
                        </a>
                    </li>
                @endif

              </ul>
            </li>
            @endif



             <!-- Reports Section -->
            @if($canSeeModule('reports'))
            <li class="menu-item {{ request()->routeIs('reports.*') || request()->routeIs('attendance.report') || request()->routeIs('admin.leave.report') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle" data-sidebar-key="reports">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                <div class="text-truncate">Reports</div>
              </a>

              <ul class="menu-sub">
                  <li class="menu-item {{ request()->routeIs('reports.task') ? 'active' : '' }}">
                    <a href="{{ route('reports.task') }}" class="menu-link">
                      <div class="text-truncate">Task Report</div>
                    </a>
                  </li>

                  <li class="menu-item {{ request()->routeIs('reports.timelog') ? 'active' : '' }}">
                    <a href="{{ route('reports.timelog') }}" class="menu-link">
                      <div class="text-truncate">Time Log Report</div>
                    </a>
                  </li>

                  <li class="menu-item {{ request()->routeIs('reports.finance') ? 'active' : '' }}">
                    <a href="{{ route('reports.finance') }}" class="menu-link">
                      <div class="text-truncate">Finance Report</div>
                    </a>
                  </li>

                  <li class="menu-item {{ request()->routeIs('reports.income-vs-expense') ? 'active' : '' }}">
                    <a href="{{ route('reports.income-vs-expense') }}" class="menu-link">
                      <div class="text-truncate">Income Vs Expense</div>
                    </a>
                  </li>

                  <li class="menu-item {{ request()->routeIs('admin.leave.report') ? 'active' : '' }}">
                    <a href="{{ route('admin.leave.report') }}" class="menu-link">
                      <div class="text-truncate">Leave Report</div>
                    </a>
                  </li>

                  <li class="menu-item {{ request()->routeIs('attendance.report') ? 'active' : '' }}">
                    <a href="{{ route('attendance.report') }}" class="menu-link">
                      <div class="text-truncate">Attendance Report</div>
                    </a>
                  </li>

                  <li class="menu-item {{ request()->routeIs('reports.expense') ? 'active' : '' }}">
                    <a href="{{ route('reports.expense') }}" class="menu-link">
                      <div class="text-truncate">Expense Report</div>
                    </a>
                  </li>

                  <li class="menu-item {{ request()->routeIs('reports.deal') ? 'active' : '' }}">
                    <a href="{{ route('reports.deal') }}" class="menu-link">
                      <div class="text-truncate">Deal Report</div>
                    </a>
                  </li>

                  <li class="menu-item {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                    <a href="{{ route('reports.sales') }}" class="menu-link">
                      <div class="text-truncate">Sales Report</div>
                    </a>
                  </li>
              </ul>
            </li>
            @endif



            <!-- Work Section -->
            @if($canSeeModule('collaborating-companies'))
            <li class="menu-item {{ request()->routeIs('collaborating-companies.*') ? 'active' : '' }}">
                <a href="{{ route('collaborating-companies.index') }}" class="menu-link" data-sidebar-key="collaborating-companies">
                    <i class="menu-icon tf-icons bx bx-buildings"></i>
                    <div class="text-truncate">Collaborating Companies</div>
                </a>
            </li>
            @endif

            @if($canSeeModule('clients'))
            <li class="menu-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                <a href="{{ route('clients.index') }}" class="menu-link" data-sidebar-key="clients">
                    <i class="menu-icon tf-icons bx bx-user-voice"></i>
                    <div class="text-truncate">Client</div>
                </a>
            </li>
            @endif

            @if(($canSeeModule('work') || in_array(strtolower((string) auth()->user()?->role), ['admin', 'manager', 'hr'], true)) && ($canAnyModule(['projects', 'tasks', 'timelogs', 'timesheets']) || in_array(strtolower((string) auth()->user()?->role), ['admin', 'manager', 'hr'], true)))
            <li class="menu-item {{ request()->routeIs('projects.*') ||
                request()->routeIs('tasks.*') || request()->routeIs('users.tasks.*') ||
                request()->routeIs('timelogs.*') || request()->routeIs('task-timer.*') ||
                request()->routeIs('admin.contracts.*') || request()->routeIs('admin.contract-templates.*') ? 'active open' : '' }}">

                <a href="javascript:void(0);" class="menu-link menu-toggle" data-sidebar-key="work">
                    <i class="menu-icon tf-icons bx bx-store"></i>
                    <div class="text-truncate" data-i18n="Front Pages">Work</div>
                </a>

                <ul class="menu-sub">
                    @if($canSeeModule('projects') || in_array(strtolower((string) auth()->user()?->role), ['admin', 'manager', 'hr'], true))
                        <li class="menu-item {{ (request()->routeIs('projects.*') && !request()->routeIs('projects.tasks.*') && !request()->routeIs('projects.timelogs.*')) ? 'active' : '' }}">
                            <a href="{{ route('projects.index') }}" class="menu-link" data-sidebar-key="projects">
                                <div class="text-truncate" data-i18n="Landing">Projects</div>
                            </a>
                        </li>
                    @endif

                    @if($canSeeModule('tasks') || in_array(strtolower((string) auth()->user()?->role), ['admin', 'manager', 'hr'], true))
                    <li class="menu-item {{ request()->routeIs('tasks.*') || request()->routeIs('projects.tasks.*') || request()->routeIs('users.tasks.*') || request()->routeIs('task-timer.*') ? 'active' : '' }}">
                        <a href="{{ route('tasks.index') }}" class="menu-link" data-sidebar-key="tasks">
                            <div class="text-truncate" data-i18n="Pricing">Tasks</div>
                        </a>
                    </li>
                    @endif

                    @if($canSeeModule('timelogs') || $canSeeModule('timesheets') || in_array(strtolower((string) auth()->user()?->role), ['admin', 'manager', 'hr'], true))
                        <li class="menu-item {{ request()->routeIs('timelogs.*') || request()->routeIs('projects.timelogs.*') ? 'active' : '' }}">
                            <a href="{{ route('timelogs.index') }}" class="menu-link" data-sidebar-key="timelogs">
                                <div class="text-truncate" data-i18n="Payment">Timesheet</div>
                            </a>
                        </li>
                    @endif

                    <!-- Contracts Section - Admin Only -->

                    <!-- @if(auth()->user()->role === 'admin')
                         <li class="menu-item {{ request()->routeIs('admin.contracts.*') ? 'active open' : '' }}">
                            <a href="{{ route('admin.contracts.index') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Contracts">Contracts</div>
                            </a>
                        </li>

                        <li class="menu-item {{ request()->routeIs('admin.contract-templates.*') ? 'active open' : '' }}">
                            <a href="{{ route('admin.contract-templates.index') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Contract Templates">Contract Templates</div>
                            </a>
                        </li> -->
                    @endif
                </ul>
            </li>
            @endif

            @if($canSeeModule('payroll') && $canAnyModule(['payroll', 'payroll-architectures', 'payslips', 'salary-structures', 'payroll-policies', 'payroll-cycles', 'tax-rules', 'bonus-rules', 'deduction-rules', 'overtime-rules', 'payroll-reports', 'payroll-audit-logs', 'payroll-settings', 'payroll-import-export', 'payroll-archive', 'formula-builder']))
            <li class="menu-item {{ request()->routeIs('payroll.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle" data-sidebar-key="payroll">
                    <i class="menu-icon tf-icons bx bx-wallet"></i>
                    <div class="text-truncate">Payroll</div>
                </a>

                <ul class="menu-sub">
                    @if($canSeeModule('payroll'))
                        <li class="menu-item {{ request()->routeIs('payroll.index') ? 'active' : '' }}">
                            <a href="{{ route('payroll.index') }}" class="menu-link"><div>Dashboard</div></a>
                        </li>
                    @endif
                    @if($canSeeModule('payroll'))
                        <li class="menu-item {{ request()->routeIs('payroll.processing') ? 'active' : '' }}">
                            <a href="{{ route('payroll.processing') }}" class="menu-link"><div>Processing</div></a>
                        </li>
                    @endif
                    @if($canSeeModule('payroll-architectures'))
                        <li class="menu-item {{ request()->routeIs('payroll.architectures.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.architectures.index') }}" class="menu-link"><div>Architectures</div></a>
                        </li>
                    @endif
                    @if($canSeeModule('salary-structures'))
                        <li class="menu-item {{ request()->routeIs('payroll.salary-structures.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.salary-structures.index') }}" class="menu-link"><div>Salary Structures</div></a>
                        </li>
                    @endif
                    @if($canSeeModule('payroll-cycles'))
                        <li class="menu-item {{ request()->routeIs('payroll.cycles.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.cycles.index') }}" class="menu-link"><div>Payroll Cycles</div></a>
                        </li>
                    @endif
                    @if($canSeeModule('payslips'))
                        <li class="menu-item {{ request()->routeIs('payroll.payslips.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.payslips.index') }}" class="menu-link"><div>Payslips</div></a>
                        </li>
                    @endif

                    {{-- ── Payroll Policies Nested Group ────────────────────── --}}
                    @if($canAnyModule(['payroll-policies', 'deduction-rules', 'bonus-rules', 'tax-rules', 'overtime-rules', 'formula-builder', 'payroll-reports', 'payroll-import-export', 'payroll-archive', 'payroll-audit-logs']))
                        <li class="menu-item {{ request()->routeIs('payroll.policies.*') || request()->routeIs('payroll.deduction-rules.*') || request()->routeIs('payroll.bonus-rules.*') || request()->routeIs('payroll.tax-rules.*') || request()->routeIs('payroll.overtime-rules.*') || request()->routeIs('payroll.formula-builder.*') || request()->routeIs('payroll.reports.*') || request()->routeIs('payroll.import-export.*') || request()->routeIs('payroll.archive.*') || request()->routeIs('payroll.audit-logs.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon tf-icons bx bx-shield-quarter" style="margin-left:4px;font-size:13px;"></i>
                                <div class="text-truncate">Payroll Policies</div>
                            </a>
                            <ul class="menu-sub">
                                @if($canSeeModule('payroll-policies'))
                                    <li class="menu-item {{ request()->routeIs('payroll.policies.*') ? 'active' : '' }}">
                                        <a href="{{ route('payroll.policies.index') }}" class="menu-link"><div>Policy Engine</div></a>
                                    </li>
                                @endif
                                @if($canSeeModule('deduction-rules'))
                                    <li class="menu-item {{ request()->routeIs('payroll.deduction-rules.*') ? 'active' : '' }}">
                                        <a href="{{ route('payroll.deduction-rules.index') }}" class="menu-link"><div>Deduction Rules</div></a>
                                    </li>
                                @endif
                                @if($canSeeModule('bonus-rules'))
                                    <li class="menu-item {{ request()->routeIs('payroll.bonus-rules.*') ? 'active' : '' }}">
                                        <a href="{{ route('payroll.bonus-rules.index') }}" class="menu-link"><div>Bonus Rules</div></a>
                                    </li>
                                @endif
                                @if($canSeeModule('tax-rules'))
                                    <li class="menu-item {{ request()->routeIs('payroll.tax-rules.*') ? 'active' : '' }}">
                                        <a href="{{ route('payroll.tax-rules.index') }}" class="menu-link"><div>Tax Rules</div></a>
                                    </li>
                                @endif
                                @if($canSeeModule('overtime-rules'))
                                    <li class="menu-item {{ request()->routeIs('payroll.overtime-rules.*') ? 'active' : '' }}">
                                        <a href="{{ route('payroll.overtime-rules.index') }}" class="menu-link"><div>Overtime Rules</div></a>
                                    </li>
                                @endif
                                @if($canSeeModule('formula-builder'))
                                    <li class="menu-item {{ request()->routeIs('payroll.formula-builder.*') ? 'active' : '' }}">
                                        <a href="{{ route('payroll.formula-builder.index') }}" class="menu-link"><div>Formula Builder</div></a>
                                    </li>
                                @endif
                                @if($canSeeModule('payroll-reports'))
                                    <li class="menu-item {{ request()->routeIs('payroll.reports.*') ? 'active' : '' }}">
                                        <a href="{{ route('payroll.reports.index') }}" class="menu-link"><div>Reports</div></a>
                                    </li>
                                @endif
                                @if($canSeeModule('payroll-import-export'))
                                    <li class="menu-item {{ request()->routeIs('payroll.import-export.*') ? 'active' : '' }}">
                                        <a href="{{ route('payroll.import-export.index') }}" class="menu-link"><div>Import / Export</div></a>
                                    </li>
                                @endif
                                @if($canSeeModule('payroll-archive'))
                                    <li class="menu-item {{ request()->routeIs('payroll.archive.*') ? 'active' : '' }}">
                                        <a href="{{ route('payroll.archive.index') }}" class="menu-link"><div>Archive</div></a>
                                    </li>
                                @endif
                                @if($canSeeModule('payroll-audit-logs'))
                                    <li class="menu-item {{ request()->routeIs('payroll.audit-logs.*') ? 'active' : '' }}">
                                        <a href="{{ route('payroll.audit-logs.index') }}" class="menu-link"><div>Audit Logs</div></a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if($canSeeModule('payroll-settings'))
                        <li class="menu-item {{ request()->routeIs('payroll.settings.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.settings.index') }}" class="menu-link"><div>Settings</div></a>
                        </li>
                    @endif
                </ul>
            </li>
            @endif

<!--    //leads section -->

                   @if($canSeeModule('leads') && $canAnyModule(['leads', 'crm-deals', 'leads-contacts', 'deals', 'crm']))
                    <li class="menu-item has-sub {{ request()->routeIs('leads.*') || request()->routeIs('admin.deals.*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-target-lock"></i>
                            <div class="text-truncate" data-i18n="Front Pages">Leads</div>
                        </a>

                        <ul class="menu-sub">
                            {{-- Lead Contact --}}
                            <li class="menu-item {{ request()->routeIs('leads.contacts.*') ? 'active' : '' }}">
                                <a href="{{ route('leads.contacts.index') }}" class="menu-link">
                                    <div class="text-truncate" data-i18n="Landing">Lead Contact</div>
                                </a>
                            </li>

                            {{-- Deals --}}
                            <li class="menu-item {{ request()->routeIs('admin.deals.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.deals.index') }}" class="menu-link">
                                    <div class="text-truncate">Deals</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Products -->
                <li class="menu-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <a href="{{ route('products.index') }}" class="menu-link" data-sidebar-key="products">
                        <i class="menu-icon tf-icons bx bx-cube"></i>
                        <div class="text-truncate" data-i18n="Products">Products</div>
                    </a>
                </li>

                <!-- Orders -->
                <li class="menu-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                    <a href="{{ route('orders.index') }}" class="menu-link" data-sidebar-key="orders">
                        <i class="menu-icon tf-icons bx bx-cart"></i>
                        <div class="text-truncate" data-i18n="Orders">Orders</div>
                    </a>
                </li>

                <!-- //ticket section . -->


            @if($canSeeModule('tickets'))
            <li class="menu-item {{ request()->routeIs('tickets.*') || request()->routeIs('ticket-groups.*') ? 'active' : '' }}">
                  <a href="{{ route('tickets.index') }}" class="menu-link" data-sidebar-key="tickets">
                       <i class="menu-icon tf-icons bx bx-receipt"></i>
                      <div class="text-truncate" data-i18n="Dashboard">Ticket</div>
                  </a>
              </li>
            @endif

            <!-- Platform Support & Complaints -->
            <li class="menu-item {{ request()->routeIs('admin.company-complaints.*') ? 'active' : '' }}">
                <a href="{{ route('admin.company-complaints.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-support"></i>
                    <div class="text-truncate">Platform Support &amp; Complaints</div>
                </a>
            </li>

            @if($canSeeModule('settings'))
                <li class="menu-item {{ request()->routeIs('settings.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.modules.*') || request()->routeIs('admin.role-permissions.*') || request()->routeIs('admin.role-accounts.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-cog"></i>
                        <div class="text-truncate" data-i18n="Settings">Settings</div>
                    </a>
                    <ul class="menu-sub">
                        @if($canSeeModule('settings-dashboard'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}"><a href="{{ route('admin.settings.index') }}" class="menu-link"><div>Settings Dashboard</div></a></li>
                        @endif
                        @if($canSeeModule('company-profile-settings'))
                        <li class="menu-item {{ request()->routeIs('settings.company') ? 'active' : '' }}"><a href="{{ route('settings.company') }}" class="menu-link"><div>Company Profile</div></a></li>
                        @endif
                        @if($canSeeModule('organization-details-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.organization-details*') ? 'active' : '' }}"><a href="{{ route('admin.settings.organization-details') }}" class="menu-link"><div>Organization Details</div></a></li>
                        @endif
                        @if($canSeeModule('business-address-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.business-address*') ? 'active' : '' }}"><a href="{{ route('admin.settings.business-address.index') }}" class="menu-link"><div>Branches / Locations</div></a></li>
                        @endif
                        @if($canSeeModule('departments'))
                        <li class="menu-item {{ request()->routeIs('parent-departments.*') || request()->routeIs('departments.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div>Department</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item {{ request()->routeIs('parent-departments.*') ? 'active' : '' }}">
                                    <a href="{{ route('parent-departments.index') }}" class="menu-link">
                                        <div>Parent Department</div>
                                    </a>
                                </li>
                                <li class="menu-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                                    <a href="{{ route('departments.index') }}" class="menu-link">
                                        <div>Sub Department</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif
                        @if($canSeeModule('designations'))
                        <li class="menu-item {{ request()->routeIs('designations.*') ? 'active' : '' }}"><a href="{{ route('designations.index') }}" class="menu-link"><div>Designations</div></a></li>
                        @endif
                        @if($canSeeModule('work-schedule-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.work-schedule*') ? 'active' : '' }}"><a href="{{ route('admin.settings.work-schedule') }}" class="menu-link"><div>Work Schedule</div></a></li>
                        @endif
                        @if($canSeeModule('leave-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.leave*') ? 'active' : '' }}"><a href="{{ route('admin.settings.leave') }}" class="menu-link"><div>Leave Settings</div></a></li>
                        @endif
                        @if($canSeeModule('holiday-settings'))
                        <li class="menu-item {{ request()->routeIs('holidays.*') ? 'active' : '' }}"><a href="{{ route('holidays.index') }}" class="menu-link"><div>Holiday Settings</div></a></li>
                        @endif
                        @if($canSeeModule('attendance-settings'))
                        <li class="menu-item {{ request()->routeIs('attendance.settings*') ? 'active' : '' }}"><a href="{{ route('attendance.settings') }}" class="menu-link"><div>Attendance Settings</div></a></li>
                        @endif
                        @if($canSeeModule('payroll-settings'))
                        <li class="menu-item {{ request()->routeIs('payroll.settings*') ? 'active' : '' }}"><a href="{{ route('payroll.settings.index') }}" class="menu-link"><div>Payroll Settings</div></a></li>
                        @endif
                        @if($canSeeModule('recruitment-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.recruitment*') ? 'active' : '' }}"><a href="{{ route('admin.settings.recruitment') }}" class="menu-link"><div>Recruitment Settings</div></a></li>
                        @endif
                        @if($canSeeModule('performance-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.performance*') ? 'active' : '' }}"><a href="{{ route('admin.settings.performance') }}" class="menu-link"><div>Performance Settings</div></a></li>
                        @endif
                        @if($canSeeModule('notification-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.notification*') ? 'active' : '' }}"><a href="{{ route('admin.settings.notification') }}" class="menu-link"><div>Notification Settings</div></a></li>
                        @endif
                        @if($canSeeModule('email-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.email*') ? 'active' : '' }}"><a href="{{ route('admin.settings.email') }}" class="menu-link"><div>Email Settings</div></a></li>
                        @endif
                        @if($canSeeModule('document-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.document*') ? 'active' : '' }}"><a href="{{ route('admin.settings.document') }}" class="menu-link"><div>Document Settings</div></a></li>
                        @endif
                        @if($canSeeModule('security-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.security*') ? 'active' : '' }}"><a href="{{ route('admin.settings.security') }}" class="menu-link"><div>Security Settings</div></a></li>
                        @endif
                        @if($canSeeModule('change-password-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.change-password*') ? 'active' : '' }}"><a href="{{ route('admin.settings.change-password') }}" class="menu-link"><div>Change Password</div></a></li>
                        @endif
                        @if($canSeeModule('role-permissions-settings') || $canSeeModule('role-management'))
                        <li class="menu-item {{ request()->routeIs('admin.role-permissions.*') ? 'active' : '' }}"><a href="{{ route('admin.role-permissions.index') }}" class="menu-link"><div>Role & Permission</div></a></li>
                        @endif
                        @if($canSeeModule('localization-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.localization*') ? 'active' : '' }}"><a href="{{ route('admin.settings.localization') }}" class="menu-link"><div>Localization</div></a></li>
                        @endif
                        @if($canSeeModule('terms-policy-settings'))
                        <li class="menu-item {{ request()->routeIs('admin.settings.terms-policy*') ? 'active' : '' }}"><a href="{{ route('admin.settings.terms-policy') }}" class="menu-link"><div>Terms &amp; Policy</div></a></li>
                        @endif
                        @if($canSeeModule('module-management'))
                        <li class="menu-item {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}"><a href="{{ route('admin.modules.index') }}" class="menu-link"><div>Module Management</div></a></li>
                        @endif
                    </ul>
                </li>
            @endif
            @endif
          </ul>



                   <!-- ================== SETTINGS SECTION ==================
                <li class="menu-item {{ request()->routeIs('settings.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-cog"></i>
                        <div class="text-truncate" data-i18n="Settings">Settings</div>
                    </a>

                    <ul class="menu-sub">
                        @if(auth()->user()->role === 'admin')
                            @if(Route::has('settings.company'))
                                <li class="menu-item">
                                    <a href="{{ route('settings.company') }}" class="menu-link">
                                        <div>Company Settings</div>
                                    </a>
                                </li>
                            @endif


                           @if(Route::has('admin.settings.business-address.index'))
                            <li class="menu-item">
                                <a href="{{ route('admin.settings.business-address.index') }}" class="menu-link">
                                    <div>Business Address</div>
                                </a>
                            </li>
                        @endif


                   @if(Route::has('admin.settings.app'))
                    <li class="menu-item">
                        <a href="{{ route('admin.settings.app', ['page' => 'app']) }}" class="menu-link">
                            <div>App Settings</div>
                        </a>
                    </li>
                @endif


                        @if(Route::has('admin.settings.profile'))
                        <li class="menu-item">
                            <a href="{{ route('admin.settings.profile') }}" class="menu-link">
                                <div>Profile Settings</div>
                            </a>
                        </li>
                    @endif

                        @if(Route::has('admin.government-id-verifications.index'))
                        <li class="menu-item">
                            <a href="{{ route('admin.government-id-verifications.index') }}" class="menu-link">
                                <div>ID Verifications</div>
                            </a>
                        </li>
                    @endif






                    @endif
                    </ul>
                </li>
            </ul> -->

        </aside>
        <!-- / Menu -->

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            let sidebarNotificationItems = @json($sidebarNotificationItems ?? []);
            const sectionReadUrlTemplate = @json(route('notifications.section.read', ['section' => '__SECTION__']));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const seenStorageKey = 'pms_sidebar_seen_{{ auth()->id() }}';
            let sidebarSeenCounts = {};

            try {
                sidebarSeenCounts = JSON.parse(localStorage.getItem(seenStorageKey) || '{}') || {};
            } catch (error) {
                sidebarSeenCounts = {};
            }

            function shortCount(count) {
                count = Number(count || 0);
                return count > 99 ? '99+' : String(count);
            }

            function saveSidebarSeenCounts() {
                localStorage.setItem(seenStorageKey, JSON.stringify(sidebarSeenCounts));
            }

            function currentSidebarKey() {
                const activeLink = document.querySelector('.menu-item.active > [data-sidebar-key], .menu-item.active [data-sidebar-key]');
                return activeLink ? activeLink.getAttribute('data-sidebar-key') : null;
            }

            function visibleCountForKey(key, item) {
                const count = Number(item.count || 0);
                const seenCount = Number(sidebarSeenCounts[key] || 0);
                return Math.max(0, count - seenCount);
            }

            function ensureBadge(link) {
                let badge = link.querySelector('.sidebar-notification-badge');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'sidebar-notification-badge';
                    link.appendChild(badge);
                }
                return badge;
            }

            function renderSidebarNotifications(items) {
                document.querySelectorAll('[data-sidebar-key]').forEach(function (link) {
                    const key = link.getAttribute('data-sidebar-key');
                    const item = items[key] || { count: 0, type: 'new', important: false };
                    const count = visibleCountForKey(key, item);
                    const badge = ensureBadge(link);
                    const menuItem = link.closest('.menu-item');

                    badge.className = 'sidebar-notification-badge type-' + (item.type || 'new');
                    link.classList.toggle('sidebar-has-important', Boolean(item.important) && count > 0);
                    if (menuItem) {
                        menuItem.classList.toggle('sidebar-has-important-item', Boolean(item.important) && count > 0);
                    }

                    if (count > 0) {
                        badge.textContent = shortCount(count);
                        badge.title = count + ' ' + (item.type || 'update') + ' update' + (count === 1 ? '' : 's');
                        badge.classList.add('is-visible');
                        link.setAttribute('data-sidebar-count', String(count));
                    } else {
                        badge.textContent = '';
                        badge.classList.remove('is-visible');
                        link.removeAttribute('data-sidebar-count');
                    }
                });
            }

            function markSidebarKeySeen(key, items) {
                if (!key || !items[key]) {
                    return;
                }

                sidebarSeenCounts[key] = Number(items[key].count || 0);
                saveSidebarSeenCounts();
                renderSidebarNotifications(items);
            }

            function markCurrentSectionRead() {
                const key = currentSidebarKey();
                if (!key) {
                    return;
                }

                markSidebarKeySeen(key, sidebarNotificationItems);

                fetch(sectionReadUrlTemplate.replace('__SECTION__', encodeURIComponent(key)), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.ok ? response.json() : Promise.reject(response))
                .then(data => {
                    sidebarNotificationItems = data.items || sidebarNotificationItems;
                    markSidebarKeySeen(key, sidebarNotificationItems);
                    if (window.pmsUpdateNotificationBell) {
                        window.pmsUpdateNotificationBell(Number(data.count || 0));
                    }
                })
                .catch(() => {});
            }

            function fetchSidebarNotifications() {
                fetch('{{ route('notifications.sidebar') }}', {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.ok ? response.json() : Promise.reject(response))
                .then(data => {
                    sidebarNotificationItems = data.items || {};
                    renderSidebarNotifications(sidebarNotificationItems);
                })
                .catch(() => {});
            }

            renderSidebarNotifications(sidebarNotificationItems);
            document.querySelectorAll('[data-sidebar-key]').forEach(function (link) {
                link.addEventListener('click', function () {
                    markSidebarKeySeen(link.getAttribute('data-sidebar-key'), sidebarNotificationItems);
                });
            });
            setTimeout(markCurrentSectionRead, 600);
            setTimeout(fetchSidebarNotifications, 1200);
            setInterval(fetchSidebarNotifications, 45000);
        });
        </script>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.documentElement;
            const body = document.body;

            function closeMobileMenu(event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                }

                root.classList.remove('layout-menu-expanded');
                body.classList.remove('layout-menu-expanded');
            }

            function toggleMobileMenu(event) {
                if (window.innerWidth >= 1200) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
                root.classList.toggle('layout-menu-expanded');
                body.classList.toggle('layout-menu-expanded');
            }

            document.querySelectorAll('.layout-page .layout-menu-toggle').forEach(function (toggle) {
                toggle.addEventListener('click', toggleMobileMenu);
            });

            document.querySelectorAll('#layout-menu > .app-brand .layout-menu-toggle, .layout-overlay').forEach(function (closeControl) {
                closeControl.addEventListener('click', closeMobileMenu);
            });

            document.querySelectorAll('#layout-menu .menu-link[href]:not([href="javascript:void(0);"])').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 1200) {
                        closeMobileMenu();
                    }
                });
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1200) {
                    closeMobileMenu();
                }
            });
        });
        </script>

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <nav
            class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
            id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                <i class="icon-base bx bx-menu icon-md"></i>
              </a>
            </div>

            <a href="{{ route('dashboard') }}" class="mobile-navbar-brand d-xl-none" aria-label="{{ $brandName }} dashboard">
              <span class="mobile-navbar-logo">
                <img src="{{ $brandLogo }}" alt="">
              </span>
            </a>

            <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">

              <!-- LEFT: Breadcrumbs -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item">
                  <span class="fw-bold">Dashboard</span> • Home
                </div>
              </div>

              <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                <!-- Place this tag where you want the button to render. -->

                <!-- RIGHT: Search + User -->
                <div class="navbar-nav align-items-center ms-auto d-flex">

                  <!-- Small search -->
                  <div class="nav-item d-flex align-items-center me-3" style="width: 200px;" title="Saerch">
                    <i class="bx bx-search icon-md me-2" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#searchModal"></i>
                    <input
                      type="text"
                      class="form-control border-0 shadow-none ps-1 ps-sm-2"
                      placeholder="Search..."
                      data-bs-toggle="modal"
                      data-bs-target="#searchModal"
                      readonly
                      style="cursor: pointer; height: 32px; font-size: 14px;" />
                  </div>

                  <!-- Sticky Note Icon -->
                  <div class="nav-item me-3">
                      <a href="javascript:void(0);" class="d-block header-icon-box sticky-note-trigger" data-bs-toggle="modal" data-bs-target="#addNoteModal" title="Sticky Notes">
                          <i class="bx bx-note icon-md text-dark"></i>
                          @if($stickyNotes->count())
                            <span class="sticky-note-count">{{ $stickyNotes->count() }}</span>
                          @endif
                      </a>
                  </div>

                  {{-- Header Timer Icon --}}
                  <div class="nav-item">
                      @if($activeTimer ?? false)
                          <!-- Active Timer (red icon) -->
                          <div class="nav-item me-3">
                          <a href="javascript:void(0);" class="d-block header-icon-box" data-bs-toggle="modal" data-bs-target="#activeTimerModal" title="Active Timer">
                              <i class="bx bx-time-five icon-md text-danger"></i>
                          </a>
                          </div>
                      @else
                          <!-- Start Timer -->
                          <div class="nav-item me-3">
                          <a href="javascript:void(0);" class="d-block header-icon-box" data-bs-toggle="modal" data-bs-target="#startTimerModal" title="Start Timer">
                              <i class="bx bx-time-five icon-md text-dark"></i>
                          </a>
                          </div>
                      @endif
                  </div>

                  @if($canCreateWorkItems)
                  <div class="nav-item me-3">
                      <li class="nav-item dropdown" data-bs-toggle="tooltip" data-bs-placement="top" title="Create new">
                        <a class="d-block header-icon-box" href="#" id="createNewDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-plus-circle icon-md text-dark"></i>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="createNewDropdown">
                            <li title="Add Task">
                                <a class="dropdown-item f-14 text-dark openRightModal" href="{{ route('tasks.create') }}">
                                    <i class="bx bx-plus me-2"></i> Add Task
                                </a>
                            </li>
                            <li title="Create Ticket">
                                <a class="dropdown-item f-14 text-dark openRightModal" href="{{ route('tickets.create') }}">
                                    <i class="bx bx-plus me-2"></i> Create Ticket
                                </a>
                            </li>
                        </ul>
                    </li>
                  </div>
                  @elseif($isEmployeeUser)
                  <div class="nav-item me-3">
                      <li class="nav-item dropdown" data-bs-toggle="tooltip" data-bs-placement="top" title="Assigned work">
                        <a class="d-block header-icon-box sticky-note-trigger" href="#" id="assignedWorkDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-plus-circle icon-md text-dark"></i>
                            @php $assignedWorkCount = $assignedWorkProjects->count() + $assignedWorkTasks->count() + $assignedWorkTickets->count(); @endphp
                            @if($assignedWorkCount)
                              <span class="sticky-note-count">{{ $assignedWorkCount }}</span>
                            @endif
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="assignedWorkDropdown" style="width: 380px; max-height: 520px; overflow-y: auto;">
                            <li class="px-3 py-2 border-bottom bg-white">
                                <p class="mb-0 fw-bold">Assigned Work</p>
                                <small class="text-muted">Projects, tasks, and tickets assigned to you</small>
                            </li>

                            <li class="dropdown-header text-uppercase fw-bold">Projects</li>
                            @forelse($assignedWorkProjects as $projectItem)
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('projects.show', $projectItem->id) }}">
                                        <div class="fw-semibold text-truncate">{{ $projectItem->name }}</div>
                                        <small class="text-muted">{{ ucfirst($projectItem->status ?? 'active') }} · {{ $projectItem->tasks_count }} assigned task{{ $projectItem->tasks_count === 1 ? '' : 's' }}</small>
                                    </a>
                                </li>
                            @empty
                                <li><span class="dropdown-item-text text-muted small">No assigned projects.</span></li>
                            @endforelse

                            <li><hr class="dropdown-divider my-1"></li>
                            <li class="dropdown-header text-uppercase fw-bold">Tasks</li>
                            @forelse($assignedWorkTasks as $taskItem)
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('tasks.show', $taskItem->id) }}">
                                        <div class="fw-semibold text-truncate">{{ $taskItem->title }}</div>
                                        <small class="text-muted">{{ $taskItem->project?->name ?? 'No project' }} · {{ $taskItem->status ?? 'To Do' }}</small>
                                    </a>
                                </li>
                            @empty
                                <li><span class="dropdown-item-text text-muted small">No assigned tasks.</span></li>
                            @endforelse

                            <li><hr class="dropdown-divider my-1"></li>
                            <li class="dropdown-header text-uppercase fw-bold">Tickets</li>
                            @forelse($assignedWorkTickets as $ticketItem)
                                <li class="px-3 py-2">
                                    <div class="d-flex justify-content-between gap-2">
                                        <a class="text-dark text-decoration-none flex-grow-1" href="{{ route('tickets.show', $ticketItem->id) }}">
                                            <div class="fw-semibold text-truncate">#{{ $ticketItem->id }} {{ $ticketItem->subject }}</div>
                                            <small class="text-muted">{{ $ticketItem->project?->name ?? 'No project' }} · {{ ucfirst($ticketItem->status) }}</small>
                                        </a>
                                    </div>
                                    <div class="d-flex gap-1 mt-2">
                                        <form method="POST" action="{{ route('tickets.change-status') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="ticketId" value="{{ $ticketItem->id }}">
                                            <input type="hidden" name="status" value="pending">
                                            <button type="submit" class="btn btn-sm btn-outline-primary py-1 px-2">Start Progress</button>
                                        </form>
                                        <form method="POST" action="{{ route('tickets.change-status') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="ticketId" value="{{ $ticketItem->id }}">
                                            <input type="hidden" name="status" value="resolved">
                                            <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2">End</button>
                                        </form>
                                        <a href="{{ route('tickets.show', $ticketItem->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">Details</a>
                                    </div>
                                </li>
                            @empty
                                <li><span class="dropdown-item-text text-muted small">No active tickets.</span></li>
                            @endforelse
                        </ul>
                    </li>
                  </div>
                  @endif

                  <div class="nav-item me-3">
                      <button type="button" class="theme-toggle-btn pms-theme-toggle" aria-label="Toggle dark mode" title="Toggle theme">
                          <i class="bx bx-moon theme-toggle-icon"></i>
                      </button>
                  </div>

                  @php
                      $headerCentralNotifications = collect();
                      $headerCentralUnreadCount = 0;
                      try {
                          $hdrCompany = app(\App\Services\CompanyContext::class)->current();
                          $hdrCompanyId = $hdrCompany?->id ?? auth()->user()?->company_id;
                          if ($hdrCompanyId && class_exists(\App\Models\Central\CentralNotification::class)) {
                               try {
                                   app(\App\Services\SubscriptionNotificationEngine::class)->scanAndGenerateAlerts($hdrCompanyId);
                               } catch (\Throwable $e) {}

                               $headerCentralNotifications = \App\Models\Central\CentralNotification::on('central')
                                  ->where('company_id', $hdrCompanyId)
                                  ->whereIn('target_audience', ['company_admin', 'all'])
                                  ->orderBy('created_at', 'desc')
                                  ->take(5)
                                  ->get();
                              $headerCentralUnreadCount = \App\Models\Central\CentralNotification::on('central')
                                  ->where('company_id', $hdrCompanyId)
                                  ->whereIn('target_audience', ['company_admin', 'all'])
                                  ->where('is_read', false)
                                  ->count();
                          }
                      } catch (\Throwable $e) {}

                      $totalHeaderUnread = ($navbarUnreadCount ?? 0) + $headerCentralUnreadCount;
                      $navbarNotifications = $navbarNotifications ?? collect();
                  @endphp

                  <div class="nav-item me-3">
                       <li class="nav-item dropdown" title="New notifications">
                        <a class="nav-link header-icon-box notification-bell {{ $totalHeaderUnread > 0 ? 'has-unread' : '' }}" href="#" id="navbarDropdown"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bell f-16 text-dark-grey"></i>
                            @if($totalHeaderUnread > 0)
                                <span class="badge bg-danger" id="navbarNotificationCount">{{ $totalHeaderUnread }}</span>
                            @endif
                        </a>

                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown border-0 shadow-lg py-0"
                                aria-labelledby="navbarDropdown">

                                <li class="notification-dropdown-head d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-0 fw-bold">Notifications</p>
                                        <small class="text-muted" id="navbarNotificationUnreadText">{{ $totalHeaderUnread }} unread</small>
                                    </div>
                                    <form method="POST" action="{{ route('notifications.readAll') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">Mark all read</button>
                                    </form>
                                </li>

                                <li class="notification-dropdown-body">
                           @foreach($headerCentralNotifications as $cntf)
                             <a href="{{ $cntf->action_url ?: route('notifications.all') }}"
                                class="notification-card-link {{ !$cntf->is_read ? 'is-unread' : '' }}" style="border-left: 3px solid {{ $cntf->severity === 'CRITICAL' ? '#ef4444' : ($cntf->severity === 'WARNING' ? '#f59e0b' : '#3b82f6') }};">
                                 <span class="notification-avatar-icon color-{{ $cntf->severity === 'CRITICAL' ? 'danger' : ($cntf->severity === 'WARNING' ? 'warning' : 'info') }}">
                                     <i class="fas {{ $cntf->severity === 'CRITICAL' ? 'fa-exclamation-triangle' : ($cntf->severity === 'WARNING' ? 'fa-bell-circle-exclamation' : 'fa-bell') }}"></i>
                                 </span>
                                 <span class="flex-grow-1">
                                     <span class="notification-title d-flex align-items-center justify-content-between">
                                         <span>{{ $cntf->title }}</span>
                                         <span class="badge bg-{{ $cntf->severity === 'CRITICAL' ? 'danger' : ($cntf->severity === 'WARNING' ? 'warning' : 'primary') }} text-white rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                             {{ $cntf->severity }}
                                         </span>
                                     </span>
                                     @if($cntf->message)
                                         <span class="notification-message">{{ \Illuminate\Support\Str::limit($cntf->message, 85) }}</span>
                                     @endif
                                     <span class="notification-time"><i class="fas fa-clock me-1"></i>{{ $cntf->created_at?->diffForHumans() }}</span>
                                 </span>
                                 @if(!$cntf->is_read)
                                     <span class="notification-unread-dot" style="background: #ef4444;"></span>
                                 @endif
                             </a>
                           @endforeach
                           @forelse($navbarNotifications as $notification)
                            @php
                                $data = $notification->data ?? [];
                                $type = class_basename($notification->type); // e.g. TaskAssignedNotification
                                $isUnread = is_null($notification->read_at);
                                $icon = data_get($data, 'icon', 'fa-bell');
                                $color = data_get($data, 'color', 'info');
                                $title = data_get($data, 'title', $type);
                                $message = data_get($data, 'message', '');

                                if ($taskId = data_get($data, 'task_id')) {
                                    $link = route('tasks.show', $taskId);
                                } elseif ($ticketId = data_get($data, 'ticket_id')) {
                                    $link = route('tickets.show', $ticketId);
                                } elseif ($projectId = data_get($data, 'project_id')) {
                                    $link = route('projects.show', $projectId);
                                } elseif ($employeeId = data_get($data, 'employee_id')) {
                                    $link = route('employees.show', $employeeId);
                                } else {
                                    $link = data_get($data, 'url', '#');
                                }
                                $isClickable = data_get($data, 'clickable', true) !== false && data_get($data, 'type') !== 'own_password_changed';
                            @endphp

    @if($isClickable)
        <a href="{{ route('notifications.open', $notification->id) }}"
           class="notification-card-link {{ $isUnread ? 'is-unread' : '' }}">
    @else
        <div class="notification-card-link {{ $isUnread ? 'is-unread' : '' }}" style="cursor: default; opacity: 0.95;">
    @endif
        <span class="notification-avatar-icon color-{{ $color }}">
            <i class="fas {{ $icon }}"></i>
        </span>
        <span class="flex-grow-1">
            <span class="notification-title d-flex align-items-center justify-content-between">
                <span>{{ $title }}</span>
                @if(!$isClickable)
                    <span class="badge bg-secondary text-white rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">View Only</span>
                @endif
            </span>
            @if($message)
                <span class="notification-message">{{ \Illuminate\Support\Str::limit($message, 92) }}</span>
            @endif
            <span class="notification-time"><i class="fas fa-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}</span>
        </span>
        @if($isUnread)
            <span class="notification-unread-dot"></span>
        @endif
    @if($isClickable)
        </a>
    @else
        </div>
    @endif
@empty
    <div class="px-3 py-5 text-center text-muted">
        <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
        <span>No notifications yet</span>
    </div>
@endforelse
                                </li>


                                <li class="notification-dropdown-foot">
                                    <a href="{{ route('notifications.all') }}" class="btn btn-sm btn-primary rounded-pill flex-grow-1">View all</a>
                                </li>
                            </ul>
                        </li>

                  </div>

                </div>

                @php
                  use App\Models\User;
                  $user = auth()->user();
                  $employeeDesignation = $user?->employeeDetail?->designation?->name ?? $user?->designation ?? 'Employee';
                  $employeeDepartment = $user?->employeeDetail?->department?->dpt_name ?? null;
                @endphp

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a  class="nav-link dropdown-toggle hide-arrow p-0"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown">

                    <!-- Image + Name + Role -->
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-online me-2">
                        <img src="{{ $user && $user->profile_image ? asset($user->profile_image) : asset('admin/assets/img/avatars/1.png') }}"
                             alt="Profile" class="navbar-profile-avatar rounded-circle" />
                      </div>

                      <div class="d-none d-md-block text-start">
                        <h6 class="mb-0 text-truncate" style="font-size: 14px;">{{ $user->name }}</h6>
                        <small class="text-muted d-block text-truncate" style="max-width: 180px;">
                          {{ $employeeDesignation }}
                        </small>
                        @if($user?->role === 'employee' && $employeeDepartment)
                          <small class="text-muted d-block text-truncate" style="max-width: 180px;">
                            {{ $employeeDepartment }}
                          </small>
                        @endif
                      </div>
                    </div>
                  </a>

                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                             <img src="{{ $user && $user->profile_image ? asset($user->profile_image) : asset('admin/assets/img/avatars/1.png') }}" alt="Profile" class="navbar-profile-avatar rounded-circle" />

                            </div>
                          </div>

                          <div class="flex-grow-1">
                              <h6 class="mb-0">{{ $user->name }}</h6>
                              <small class="text-body-secondary d-block">{{ $employeeDesignation }} ({{ ucfirst($user->role) }})</small>
                              @if($user?->role === 'employee' && $employeeDepartment)
                                <small class="text-body-secondary d-block">Department: {{ $employeeDepartment }}</small>
                              @endif
                          </div>

                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="icon-base bx bx-user icon-md me-3"></i><span>My Profile</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('admin.settings.change-password') }}">
                        <i class="icon-base bx bx-key icon-md me-3"></i><span>Change Password</span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>

                      <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                               <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
                            </x-dropdown-link>
                        </form>

                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>

          <script>
          document.addEventListener('DOMContentLoaded', function () {
              const bell = document.getElementById('navbarDropdown');
              const unreadText = document.getElementById('navbarNotificationUnreadText');
              const readAllUrl = @json(route('notifications.readAll'));
              const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
              let soundReady = false;
              let pendingNotificationSound = false;
              let previousUnread = Number(localStorage.getItem('pms_unread_notifications') || '{{ $navbarUnreadCount }}');
              const notificationSoundUrl = @json(asset('sound/notification sound.mp3'));
              let notificationAudio = new Audio(notificationSoundUrl);
              notificationAudio.preload = 'auto';
              notificationAudio.volume = 0.75;

              function armNotificationSound() {
                  soundReady = true;
                  notificationAudio.play()
                      .then(() => {
                          notificationAudio.pause();
                          notificationAudio.currentTime = 0;
                          if (pendingNotificationSound) {
                              pendingNotificationSound = false;
                              setTimeout(playNotificationSound, 80);
                          }
                      })
                      .catch(() => {
                          if (pendingNotificationSound) {
                              pendingNotificationSound = false;
                              setTimeout(playGeneratedNotificationTone, 80);
                          }
                      });
                  document.removeEventListener('click', armNotificationSound);
                  document.removeEventListener('keydown', armNotificationSound);
                  document.removeEventListener('touchstart', armNotificationSound);
                  document.removeEventListener('pointerdown', armNotificationSound);
              }

              document.addEventListener('click', armNotificationSound, { once: true });
              document.addEventListener('keydown', armNotificationSound, { once: true });
              document.addEventListener('touchstart', armNotificationSound, { once: true, passive: true });
              document.addEventListener('pointerdown', armNotificationSound, { once: true });

              function playNotificationSound() {
                  if (!soundReady) {
                      pendingNotificationSound = true;
                      return;
                  }

                  if (notificationAudio) {
                      notificationAudio.currentTime = 0;
                      notificationAudio.play()
                          .catch(() => playGeneratedNotificationTone());
                      return;
                  }

                  playGeneratedNotificationTone();
              }

              function playGeneratedNotificationTone() {
                  try {
                      const AudioContext = window.AudioContext || window.webkitAudioContext;
                      if (!AudioContext) return;

                      const ctx = new AudioContext();
                      const gain = ctx.createGain();
                      gain.gain.value = 0.06;
                      gain.connect(ctx.destination);

                      [880, 1174].forEach(function (frequency, index) {
                          const osc = ctx.createOscillator();
                          osc.type = 'sine';
                          osc.frequency.value = frequency;
                          osc.connect(gain);
                          osc.start(ctx.currentTime + index * 0.12);
                          osc.stop(ctx.currentTime + index * 0.12 + 0.11);
                      });

                      setTimeout(function () { ctx.close(); }, 500);
                  } catch (error) {
                      console.debug('Notification sound skipped', error);
                  }
              }

              function updateBell(count) {
                  if (!bell) return;

                  let badge = document.getElementById('navbarNotificationCount');
                  if (count > 0) {
                      bell.classList.add('has-unread');
                      if (!badge) {
                          badge = document.createElement('span');
                          badge.id = 'navbarNotificationCount';
                          badge.className = 'badge bg-danger';
                          bell.appendChild(badge);
                      }
                      badge.textContent = count;
                  } else {
                      bell.classList.remove('has-unread');
                      if (badge) badge.remove();
                  }

                  if (unreadText) {
                      unreadText.textContent = count + ' unread';
                  }

                  previousUnread = count;
                  localStorage.setItem('pms_unread_notifications', String(count));
              }

              window.pmsUpdateNotificationBell = updateBell;

              function silentlyMarkBellNotificationsRead() {
                  fetch(readAllUrl, {
                      method: 'POST',
                      credentials: 'same-origin',
                      headers: {
                          'Accept': 'application/json',
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': csrfToken,
                          'X-Requested-With': 'XMLHttpRequest'
                      },
                      body: JSON.stringify({})
                  })
                  .then(response => response.ok ? response.json() : Promise.reject(response))
                  .then(() => {
                      document.querySelectorAll('.notification-card-link.is-unread').forEach(function (link) {
                          link.classList.remove('is-unread');
                      });
                      document.querySelectorAll('.notification-unread-dot').forEach(function (dot) {
                          dot.remove();
                      });
                      updateBell(0);
                  })
                  .catch(() => {});
              }

              if (bell) {
                  bell.addEventListener('shown.bs.dropdown', silentlyMarkBellNotificationsRead);
              }

              function checkNotifications() {
                  fetch('{{ route('notifications.unreadCount') }}', {
                      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                  })
                  .then(response => response.json())
                  .then(data => {
                      const count = Number(data.count || 0);
                      if (count > previousUnread) {
                          playNotificationSound();
                          if (bell) {
                              bell.classList.remove('has-unread');
                              void bell.offsetWidth;
                              bell.classList.add('has-unread');
                          }
                      }
                      updateBell(count);
                  })
                  .catch(() => {});
              }

              updateBell(previousUnread);
              setTimeout(checkNotifications, 1500);
              setInterval(checkNotifications, 15000);
          });
          </script>

          <!-- Search Modal -->
          <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="searchModalLabel">Search</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form action="{{ route('dashboard.search') }}" method="GET">
                      <div class="mb-3">
                          <label for="type">Search For:</label>
                          <select name="type" id="type" class="form-select">
                              <option value="ticket">Ticket</option>
                              <option value="task">Task</option>
                              <option value="project">Project</option>
                              <option value="employee">Employee</option>
                              <option value="client">Client</option>
                          </select>
                      </div>
                      <div class="mb-3">
                          <input type="text" name="query" class="form-control" placeholder="Enter keyword to search">
                      </div>
                      <button type="submit" class="btn btn-primary">Search</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Add Sticky Note Modal -->
          <div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="addNoteModalLabel">Today Sticky Notes</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                  <!-- Add Note Form -->
                  <form action="{{ route('sticky_notes.store') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <label for="note_text" class="form-label">What do you want to do today?</label>
                        <textarea name="note_text" id="note_text" class="form-control" rows="3" maxlength="1000" placeholder="Write a quick reminder for your own screen..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="colour" class="form-label">Note Color</label>
                        <select name="colour" id="colour" class="form-select" required>
                            <option value="yellow">Yellow</option>
                            <option value="blue">Blue</option>
                            <option value="red">Red</option>
                            <option value="gray">Gray</option>
                            <option value="purple">Purple</option>
                            <option value="green">Green</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end">
                      <button type="submit" class="btn btn-primary me-2">Save</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                  </form>

                  <hr>

                  <!-- Existing Notes -->
                  <h6>Your Active Notes</h6>
                  <div class="sticky-notes-grid">
                    @forelse($stickyNotes as $note)
                      <div class="sticky-note-card {{ $note->colour }}">
                        <div class="sticky-note-actions">
                          <form method="POST" action="{{ route('sticky_notes.complete', $note) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="Mark done"><i class="bx bx-check"></i></button>
                          </form>
                          <form method="POST" action="{{ route('sticky_notes.destroy', $note) }}" onsubmit="return confirm('Delete this sticky note?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Delete"><i class="bx bx-x"></i></button>
                          </form>
                        </div>
                        <p>{{ $note->note_text }}</p>
                        <span class="sticky-note-meta">{{ $note->created_at->format('d M Y, h:i A') }}</span>
                      </div>
                    @empty
                      <p class="text-center text-muted">- No record found -</p>
                    @endforelse
                  </div>

                </div>
              </div>
            </div>
          </div>

          @if($stickyNotes->isNotEmpty())
            <div class="sticky-note-dock" aria-label="Your sticky notes">
              @foreach($stickyNotes->take(4) as $note)
                <div class="sticky-note-card {{ $note->colour }}">
                  <div class="sticky-note-actions">
                    <form method="POST" action="{{ route('sticky_notes.complete', $note) }}">
                      @csrf
                      @method('PATCH')
                      <button type="submit" title="Mark done"><i class="bx bx-check"></i></button>
                    </form>
                  </div>
                  <p>{{ $note->note_text }}</p>
                  <span class="sticky-note-meta">{{ $note->created_at->format('h:i A') }}</span>
                </div>
              @endforeach
            </div>
          @endif

          {{-- ================= START TIMER MODAL ================= --}}
          <div class="modal fade" id="startTimerModal" tabindex="-1">
            <div class="modal-dialog modal-md modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Start Timer</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('dashboard-timers.store') }}" method="POST">
                  @csrf
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Project <sup class="text-danger">*</sup></label>
                      <select name="project_id" id="timer_project_id" class="form-select" required>
                        <option value="">Select Project</option>
                        @foreach($timerProjects as $project)
                          <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Task <sup class="text-danger">*</sup></label>
                      <select name="task_id" id="task_id" class="form-select">
                        <option value="">Select Task</option>
                        @foreach($timerTasks as $task)
                          <option value="{{ $task->id }}" data-project-id="{{ $task->project_id }}">{{ $task->title }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Project Update</label>
                      <select name="project_status" class="form-select">
                        <option value="">No Change</option>
                        <option value="not started">Start</option>
                        <option value="in progress">In Process</option>
                        <option value="on hold">End Up</option>
                        <option value="completed">End</option>
                      </select>
                    </div>

                    @if($canCreateWorkItems)
                    <div class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" id="create_task" name="create_task" value="1">
                      <label class="form-check-label" for="create_task">Create New Task</label>
                    </div>

                    <div class="mb-3" id="newTaskDiv" style="display:none;">
                      <label class="form-label">New Task Name</label>
                      <input type="text" name="new_task_name" class="form-control">
                    </div>
                    @endif

                    <div class="mb-3">
                      <label class="form-label">Memo <sup class="text-danger">*</sup></label>
                      <textarea name="memo" class="form-control" rows="2" required></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Start</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          {{-- ================= ACTIVE TIMER MODAL ================= --}}
          @if($activeTimer ?? false)
          <div class="modal fade" id="activeTimerModal" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                  <h5 class="modal-title">Active Timer</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <p><strong>Project:</strong> {{ $activeTimer->project->name ?? '' }}</p>
                  <p><strong>Task:</strong> {{ $activeTimer->task->title ?? '' }}</p>
                  <p><strong>Start:</strong> {{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i A') }}</p>
                  <p class="text-danger fw-bold">Running...</p>
                </div>
                <div class="modal-footer">
                  <form method="POST" action="{{ route('task-timer.pause', $activeTimer->task->id) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="timer_id" value="{{ $activeTimer->id }}">
                    <button type="submit" class="btn btn-warning">Pause</button>
                  </form>

                  <form method="POST" action="{{ route('task-timer.resume', $activeTimer->task->id) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="timer_id" value="{{ $activeTimer->id }}">
                    <button type="submit" class="btn btn-success">Resume</button>
                  </form>

                  <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#stopTimerModal-{{ $activeTimer->id }}">
                    Stop
                  </button>
                </div>
              </div>
            </div>
          </div>
          @endif

          {{-- ================= STOP TIMER MODAL ================= --}}
          @if($activeTimer ?? false)
          <div class="modal fade" id="stopTimerModal-{{ $activeTimer->id }}" tabindex="-1">
            <div class="modal-dialog">
              <form method="POST" action="{{ route('task-timer.stop', $activeTimer->task->id) }}">
                @csrf
                <input type="hidden" name="timer_id" value="{{ $activeTimer->id }}">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Stop Timer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p><strong>Start:</strong> {{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i A') }}</p>
                    <p><strong>End:</strong> {{ now()->format('h:i A') }}</p>
                    <p><strong>Total Time:</strong>
                      {{ \Carbon\Carbon::parse($activeTimer->start_time)->diffForHumans(now(), true) }}
                    </p>
                    <div class="mb-3">
                      <label class="form-label">Memo *</label>
                      <textarea name="memo" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Project Update</label>
                      <select name="project_status" class="form-select">
                        <option value="">No Change</option>
                        <option value="not started">Start</option>
                        <option value="in progress">In Process</option>
                        <option value="on hold">End Up</option>
                        <option value="completed">End</option>
                      </select>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          @endif

          <script>
          const createTaskCheckbox = document.getElementById('create_task');
          if (createTaskCheckbox) {
              createTaskCheckbox.addEventListener('change', function() {
                  let newTaskDiv = document.getElementById('newTaskDiv');
                  let taskSelect = document.getElementById('task_id');
                  if (this.checked) {
                      newTaskDiv.style.display = 'block';   // Show new task input
                      taskSelect.disabled = true;            // Disable existing task dropdown
                      taskSelect.required = false;           // Remove required
                      newTaskDiv.querySelector('input').required = true; // Make new task input required
                  } else {
                      newTaskDiv.style.display = 'none';
                      taskSelect.disabled = false;
                      taskSelect.required = true;
                      newTaskDiv.querySelector('input').required = false;
                  }
              });
          }

          const timerProjectSelect = document.getElementById('timer_project_id');
          const timerTaskSelect = document.getElementById('task_id');
          if (timerProjectSelect && timerTaskSelect) {
              const taskOptions = Array.from(timerTaskSelect.querySelectorAll('option[data-project-id]'));
              const filterTimerTasks = function () {
                  const selectedProjectId = timerProjectSelect.value;
                  timerTaskSelect.value = '';
                  taskOptions.forEach(function(option) {
                      option.hidden = selectedProjectId !== '' && option.dataset.projectId !== selectedProjectId;
                  });
              };

              timerProjectSelect.addEventListener('change', filterTimerTasks);
              filterTimerTasks();
          }

          document.addEventListener("DOMContentLoaded", function () {
              const elapsedSpan = document.getElementById("activeTimerElapsed");
              @if($activeTimer)
                  const startTime = new Date("{{ $activeTimer->start_time }}");
                  setInterval(() => {
                      const now = new Date();
                      const diff = Math.floor((now - startTime) / 1000);
                      const h = Math.floor(diff / 3600);
                      const m = Math.floor((diff % 3600) / 60);
                      const s = diff % 60;
                      if (elapsedSpan) elapsedSpan.innerText = `${h}h ${m}m ${s}s`;
                  }, 1000);
              @endif
          });

          // Stop modal population
          document.addEventListener("DOMContentLoaded", function () {
              const stopModal = document.getElementById("stopTimerModal-{{ $activeTimer->id ?? '0' }}");
              if (stopModal) {
                  stopModal.addEventListener("show.bs.modal", function () {
                      const endTimeEl = document.getElementById("stopEndTime");
                      const totalTimeEl = document.getElementById("stopTotalTime");

                      const startTime = new Date("{{ $activeTimer->start_time ?? '' }}");
                      const now = new Date();

                      // End time
                      if (endTimeEl) endTimeEl.innerText = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                      // Total diff
                      const diff = Math.floor((now - startTime) / 1000);
                      const h = Math.floor(diff / 3600);
                      const m = Math.floor((diff % 3600) / 60);
                      const s = diff % 60;
                      if (totalTimeEl) totalTimeEl.innerText = `${h}h ${m}m ${s}s`;
                  });
              }
          });

          </script>

    <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <div class="container-xxl flex-grow-1 container-p-y">

              @php
                $bannerComp = app(\App\Services\CompanyContext::class)->current();
                $bannerSub = $bannerComp?->subscriptions?->where('status', 'active')?->first();
                $bannerExpiry = $bannerSub?->ends_at ?? $bannerComp?->trial_ends_at;
                $bannerDaysLeft = $bannerExpiry ? (int) \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($bannerExpiry)->startOfDay(), false) : null;
                $bannerIsSuspended = $bannerComp ? $bannerComp->isSuspended() : false;
                $currentPath = strtolower(request()->path());
                $showExpiringBanner = request()->routeIs('dashboard', 'dashboard.*', 'admin.dashboard', 'superadmin.dashboard', 'developer.dashboard', 'notifications.*', 'admin.company-notifications.*', 'developer.notifications')
                    || str_contains($currentPath, 'dashboard')
                    || str_contains($currentPath, 'notification')
                    || $currentPath === '/'
                    || $currentPath === 'home';
              @endphp

              @if($bannerIsSuspended)
                <div class="alert border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: linear-gradient(135deg, #fef2f2 0%, #ffe4e6 100%); border-left: 5px solid #ef4444 !important; border-radius: 14px; color: #991b1b; padding: 16px 20px;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 20px;" class="flex-shrink-0">
                            <i class="bx bx-lock-alt"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 15px;">Account Suspended – Immediate Action Required</div>
                            <div style="font-size: 13px; opacity: 0.9;">Your organization's subscription has expired and account access is restricted. Renew or upgrade your plan to restore full access.</div>
                        </div>
                    </div>
                    <a href="{{ route('subscription.suspended') }}" class="btn btn-danger fw-bold rounded-pill px-4" style="font-size: 13px;">
                        <i class="bx bx-zap me-1"></i> Reactivate Plan
                    </a>
                </div>
              @elseif($showExpiringBanner && $bannerDaysLeft !== null && $bannerDaysLeft <= 7 && $bannerDaysLeft >= 0)
                @php
                    $bBg = $bannerDaysLeft <= 3 ? 'linear-gradient(135deg, #fff5f5 0%, #fef2f2 100%)' : 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)';
                    $bBorder = $bannerDaysLeft <= 3 ? '#ef4444' : '#f59e0b';
                    $bColor = $bannerDaysLeft <= 3 ? '#991b1b' : '#92400e';
                    $bIconBg = $bannerDaysLeft <= 3 ? '#fee2e2' : '#fde68a';
                    $bIconColor = $bannerDaysLeft <= 3 ? '#ef4444' : '#d97706';
                    $bPlanName = strtoupper($bannerSub?->plan?->name ?? ($bannerComp?->isOnTrial() ? 'FREE TRIAL' : 'SUBSCRIPTION'));
                @endphp
                <div class="alert border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: {{ $bBg }}; border-left: 5px solid {{ $bBorder }} !important; border-radius: 14px; color: {{ $bColor }}; padding: 16px 20px;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: {{ $bIconBg }}; color: {{ $bIconColor }}; display: flex; align-items: center; justify-content: center; font-size: 20px;" class="flex-shrink-0">
                            <i class="bx {{ $bannerDaysLeft <= 3 ? 'bx-error-alt' : 'bx-time-five' }}"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 15px;">
                                Subscription Warning: Expiring in {{ $bannerDaysLeft == 1 ? '1 Day (Tomorrow)' : $bannerDaysLeft . ' Days' }} – {{ $bPlanName }}
                            </div>
                            <div style="font-size: 13px; opacity: 0.9;">
                                Your {{ $bPlanName }} subscription is scheduled to expire on {{ \Carbon\Carbon::parse($bannerExpiry)->format('d M Y') }}. Renew or upgrade now to avoid suspension.
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('notifications.all') }}" class="btn btn-outline-dark fw-bold rounded-pill px-3" style="font-size: 12.5px;">
                            <i class="bx bx-bell me-1"></i> Notifications
                        </a>
                        <a href="{{ route('subscription.suspended') }}" class="btn btn-primary fw-bold rounded-pill px-3" style="font-size: 12.5px; background: #2563eb; border-color: #2563eb;">
                            <i class="bx bx-credit-card me-1"></i> Renew / Upgrade
                        </a>
                    </div>
                </div>
              @endif
