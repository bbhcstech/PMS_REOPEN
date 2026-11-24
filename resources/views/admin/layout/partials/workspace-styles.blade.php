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

   .modal-backdrop {
       z-index: 10040 !important;
   }

   .modal {
       z-index: 10050 !important;
       padding-top: 0 !important;
   }

   .modal-dialog {
       z-index: 10051 !important;
       margin-top: 1.75rem !important;
       margin-bottom: 1.75rem !important;
   }

   .modal-dialog-centered {
       min-height: calc(100% - 3.5rem) !important;
       margin-top: 1.75rem !important;
       margin-bottom: 1.75rem !important;
   }

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
       position: absolute !important;
       top: 0.75rem !important;
       z-index: 1035 !important;
       display: flex;
       flex-wrap: nowrap !important;
       align-items: center;
       background: var(--bx-navbar-bg, rgba(255, 255, 255, 0.94)) !important;
       backdrop-filter: blur(16px);
       -webkit-backdrop-filter: blur(16px);
       box-shadow: var(--bx-shadow-sm) !important;
       border-radius: 16px;
       border: 1px solid var(--bx-border-subtle) !important;
       padding: 0.75rem 1.25rem;
       font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
       min-height: 64px;
       margin: 0 !important;
       transition: left 0.25s ease, width 0.25s ease, max-width 0.25s ease, background 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
   }

   @media (min-width: 1200px) {
       html.layout-menu-fixed:not(.layout-menu-collapsed) #layout-navbar,
       .layout-menu-fixed:not(.layout-menu-collapsed) #layout-navbar {
           left: calc(var(--bs-menu-width, 16.25rem) + 1.25rem) !important;
           right: 1.25rem !important;
           width: calc(100% - var(--bs-menu-width, 16.25rem) - 2.5rem) !important;
           max-width: calc(100% - var(--bs-menu-width, 16.25rem) - 2.5rem) !important;
       }

       html.layout-menu-fixed.layout-menu-collapsed #layout-navbar,
       .layout-menu-fixed.layout-menu-collapsed #layout-navbar {
           left: calc(var(--bs-menu-collapsed-width, 5.25rem) + 1.25rem) !important;
           right: 1.25rem !important;
           width: calc(100% - var(--bs-menu-collapsed-width, 5.25rem) - 2.5rem) !important;
           max-width: calc(100% - var(--bs-menu-collapsed-width, 5.25rem) - 2.5rem) !important;
       }

       html.layout-navbar-fixed .layout-wrapper:not(.layout-horizontal) .layout-page,
       .layout-page {
           padding-top: calc(72px + 1.75rem) !important;
       }
   }

   @media (max-width: 1199.98px) {
       #layout-navbar {
           left: 0.75rem !important;
           right: 0.75rem !important;
           width: calc(100% - 1.5rem) !important;
           max-width: calc(100% - 1.5rem) !important;
           top: 0.75rem !important;
       }

       html.layout-navbar-fixed .layout-wrapper:not(.layout-horizontal) .layout-page,
       .layout-page {
           padding-top: calc(68px + 1.5rem) !important;
       }
   }

   @media (max-width: 767.98px) {
       #layout-navbar {
           left: 0.5rem !important;
           right: 0.5rem !important;
           width: calc(100% - 1rem) !important;
           max-width: calc(100% - 1rem) !important;
           top: 0.5rem !important;
       }

       html.layout-navbar-fixed .layout-wrapper:not(.layout-horizontal) .layout-page,
       .layout-page {
           padding-top: calc(60px + 1.25rem) !important;
       }
   }

   .layout-navbar-fixed .layout-wrapper:not(.layout-horizontal) .layout-page::before,
   .layout-page::before {
       display: none !important;
       content: none !important;
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
   }

   /* ===================== SEARCH FIELD CONTROL ===================== */
   @media (max-width: 992px) {
       #layout-navbar input[type="text"] {
           display: none !important;
       }
   }

   /* ===================== MOBILE / TABLET FINAL FIX ===================== */
   @media (max-width: 992px) {

       #layout-navbar {
           padding: 0.5rem 0.75rem;
       }

       #layout-navbar .navbar-nav,
       #layout-navbar .navbar-nav-right {
           flex-direction: row !important;
           flex-wrap: nowrap !important;
           align-items: center;
       }

       #layout-navbar .navbar-nav > *,
       #layout-navbar .navbar-nav-right > * {
           flex: 0 0 auto;
       }

       /* hide username only, keep avatar */
       .dropdown-user .d-md-block {
           display: none !important;
       }
   }

   /* Cleaned legacy purple conflict block */

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
       cursor: default;
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
       flex: 0 0 auto;
       min-width: 22px;
       height: 22px;
       padding: 0 6px;
       border-radius: 999px;
       display: none;
       align-items: center;
       justify-content: center;
       font-size: 11px;
       font-weight: 800;
       line-height: 1;
       color: #fff !important;
       -webkit-text-fill-color: #fff !important;
       white-space: nowrap;
       font-variant-numeric: tabular-nums;
       background: var(--bx-blue-primary);
   }

   .sidebar-notification-badge.is-visible {
       display: inline-flex;
   }

   #layout-menu .menu-toggle .sidebar-notification-badge {
       margin-right: 24px;
   }

   .sidebar-notification-badge.type-new { background: var(--bx-blue-primary); }
   .sidebar-notification-badge.type-pending { background: var(--bx-amber); }
   .sidebar-notification-badge.type-issue { background: var(--bx-red); }
   .sidebar-notification-badge.type-warning { background: var(--bx-amber); }
   .sidebar-notification-badge.type-unread { background: var(--bx-blue-primary); }

   #layout-menu .sidebar-notification-badge.type-pending,
   #layout-menu .sidebar-notification-badge.type-warning {
       color: #1e293b !important;
       -webkit-text-fill-color: #1e293b !important;
   }

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

   /* Sticky Notes Dark Mode Styling */
   html[data-pms-theme="dark"] .sticky-note-card,
   html[data-theme="dark"] .sticky-note-card,
   [data-pms-theme="dark"] .sticky-note-card {
       color: #EEF1FB !important;
       box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35) !important;
   }

   html[data-pms-theme="dark"] .sticky-note-card p,
   html[data-theme="dark"] .sticky-note-card p,
   [data-pms-theme="dark"] .sticky-note-card p {
       color: #F8FAFC !important;
   }

   html[data-pms-theme="dark"] .sticky-note-card .sticky-note-meta,
   html[data-theme="dark"] .sticky-note-card .sticky-note-meta,
   [data-pms-theme="dark"] .sticky-note-card .sticky-note-meta {
       color: rgba(248, 250, 252, 0.75) !important;
   }

   html[data-pms-theme="dark"] .sticky-note-card.yellow,
   html[data-theme="dark"] .sticky-note-card.yellow,
   [data-pms-theme="dark"] .sticky-note-card.yellow {
       background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(180, 83, 9, 0.35)) !important;
       border: 1px solid rgba(245, 158, 11, 0.5) !important;
   }

   html[data-pms-theme="dark"] .sticky-note-card.blue,
   html[data-theme="dark"] .sticky-note-card.blue,
   [data-pms-theme="dark"] .sticky-note-card.blue {
       background: linear-gradient(135deg, rgba(59, 130, 246, 0.22), rgba(29, 78, 216, 0.35)) !important;
       border: 1px solid rgba(59, 130, 246, 0.5) !important;
   }

   html[data-pms-theme="dark"] .sticky-note-card.red,
   html[data-theme="dark"] .sticky-note-card.red,
   [data-pms-theme="dark"] .sticky-note-card.red {
       background: linear-gradient(135deg, rgba(239, 68, 68, 0.22), rgba(185, 28, 28, 0.35)) !important;
       border: 1px solid rgba(239, 68, 68, 0.5) !important;
   }

   html[data-pms-theme="dark"] .sticky-note-card.gray,
   html[data-theme="dark"] .sticky-note-card.gray,
   [data-pms-theme="dark"] .sticky-note-card.gray {
       background: linear-gradient(135deg, rgba(148, 163, 184, 0.2), rgba(71, 85, 105, 0.35)) !important;
       border: 1px solid rgba(148, 163, 184, 0.5) !important;
   }

   html[data-pms-theme="dark"] .sticky-note-card.purple,
   html[data-theme="dark"] .sticky-note-card.purple,
   [data-pms-theme="dark"] .sticky-note-card.purple {
       background: linear-gradient(135deg, rgba(168, 85, 247, 0.22), rgba(126, 34, 206, 0.35)) !important;
       border: 1px solid rgba(168, 85, 247, 0.5) !important;
   }

   html[data-pms-theme="dark"] .sticky-note-card.green,
   html[data-theme="dark"] .sticky-note-card.green,
   [data-pms-theme="dark"] .sticky-note-card.green {
       background: linear-gradient(135deg, rgba(47, 107, 255, 0.22), rgba(4, 120, 87, 0.35)) !important;
       border: 1px solid rgba(47, 107, 255, 0.5) !important;
   }

   html[data-pms-theme="dark"] .sticky-note-actions button,
   html[data-theme="dark"] .sticky-note-actions button,
   [data-pms-theme="dark"] .sticky-note-actions button {
       background: rgba(255, 255, 255, 0.15) !important;
       color: #F8FAFC !important;
       border: 1px solid rgba(255, 255, 255, 0.25) !important;
   }

   html[data-pms-theme="dark"] .sticky-note-actions button:hover,
   html[data-theme="dark"] .sticky-note-actions button:hover,
   [data-pms-theme="dark"] .sticky-note-actions button:hover {
       background: rgba(255, 255, 255, 0.35) !important;
       color: #FFFFFF !important;
   }

   html[data-pms-theme="dark"] #addNoteModal .form-control,
   html[data-theme="dark"] #addNoteModal .form-control,
   [data-pms-theme="dark"] #addNoteModal .form-control,
   html[data-pms-theme="dark"] #addNoteModal .form-select,
   html[data-theme="dark"] #addNoteModal .form-select,
   [data-pms-theme="dark"] #addNoteModal .form-select {
       background-color: #141B3D !important;
       color: #EEF1FB !important;
       border-color: rgba(238, 241, 251, 0.16) !important;
   }

   html[data-pms-theme="dark"] #addNoteModal .form-label,
   html[data-theme="dark"] #addNoteModal .form-label,
   [data-pms-theme="dark"] #addNoteModal .form-label,
   html[data-pms-theme="dark"] #addNoteModal h6,
   html[data-theme="dark"] #addNoteModal h6,
   [data-pms-theme="dark"] #addNoteModal h6 {
       color: #EEF1FB !important;
   }

   html[data-pms-theme="dark"] #addNoteModal .btn-close,
   html[data-theme="dark"] #addNoteModal .btn-close,
   [data-pms-theme="dark"] #addNoteModal .btn-close {
       filter: invert(1) grayscale(100%) brightness(200%) !important;
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
   html[data-theme="dark"],
   html[data-bs-theme="dark"],
   body[data-pms-theme="dark"],
   body.dark-mode,
   [data-pms-theme="dark"],
   [data-theme="dark"] {
       --bx-blue-primary: #2F6BFF;
       --bx-blue-hover: #4F83FF;
       --bx-blue-soft: rgba(47, 107, 255, 0.18);
       --bx-blue-subtle: rgba(47, 107, 255, 0.08);
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
       --bx-border-focus: rgba(47, 107, 255, 0.5);
       --bx-navbar-bg: rgba(15, 21, 48, 0.94);
   }

   /* Layout & Navbar */
   html[data-pms-theme="dark"] #layout-navbar,
   html[data-theme="dark"] #layout-navbar,
   html[data-bs-theme="dark"] #layout-navbar,
   [data-pms-theme="dark"] #layout-navbar,
   body.dark-mode #layout-navbar {
       background: rgba(15, 21, 48, 0.94) !important;
       border-color: rgba(238, 241, 251, 0.09) !important;
       color: #CBD5E1 !important;
   }

   html[data-pms-theme="dark"] #layout-navbar .fw-bold,
   html[data-theme="dark"] #layout-navbar .fw-bold,
   [data-pms-theme="dark"] #layout-navbar .fw-bold {
       color: #EEF1FB !important;
   }

   html[data-pms-theme="dark"] #layout-navbar .navbar-search-wrapper,
   [data-pms-theme="dark"] #layout-navbar .navbar-search-wrapper {
       color: #CBD5E1 !important;
   }

   html[data-pms-theme="dark"] #layout-navbar .navbar-search-wrapper input,
   [data-pms-theme="dark"] #layout-navbar .navbar-search-wrapper input {
       background: #141B3D !important;
       border: 1px solid rgba(238, 241, 251, 0.1) !important;
       color: #EEF1FB !important;
   }

   html[data-pms-theme="dark"] #layout-navbar .navbar-search-wrapper input::placeholder,
   [data-pms-theme="dark"] #layout-navbar .navbar-search-wrapper input::placeholder {
       color: #9AA3C7 !important;
   }

   /* Header Icons */
   html[data-pms-theme="dark"] .header-icon-box,
   html[data-theme="dark"] .header-icon-box,
   html[data-pms-theme="dark"] .theme-toggle-btn,
   html[data-theme="dark"] .theme-toggle-btn,
   html[data-pms-theme="dark"] .notification-bell,
   html[data-theme="dark"] .notification-bell,
   [data-pms-theme="dark"] .header-icon-box,
   [data-pms-theme="dark"] .theme-toggle-btn,
   [data-pms-theme="dark"] .notification-bell,
   body.dark-mode .header-icon-box,
   body.dark-mode .theme-toggle-btn,
   body.dark-mode .notification-bell {
       background: #141B3D !important;
       border-color: rgba(238, 241, 251, 0.1) !important;
       color: #CBD5E1 !important;
   }

   html[data-pms-theme="dark"] .header-icon-box:hover,
   html[data-theme="dark"] .header-icon-box:hover,
   html[data-pms-theme="dark"] .theme-toggle-btn:hover,
   html[data-theme="dark"] .theme-toggle-btn:hover,
   html[data-pms-theme="dark"] .notification-bell:hover,
   html[data-theme="dark"] .notification-bell:hover,
   [data-pms-theme="dark"] .header-icon-box:hover,
   [data-pms-theme="dark"] .theme-toggle-btn:hover,
   [data-pms-theme="dark"] .notification-bell:hover {
       background: #1A2247 !important;
       border-color: rgba(47, 107, 255, 0.35) !important;
       color: #2F6BFF !important;
   }

   html[data-pms-theme="dark"] .header-icon-box i,
   html[data-pms-theme="dark"] .header-icon-box i.text-dark,
   html[data-pms-theme="dark"] .header-icon-box i.text-dark-grey,
   [data-pms-theme="dark"] .header-icon-box i,
   [data-pms-theme="dark"] .header-icon-box i.text-dark,
   [data-pms-theme="dark"] .header-icon-box i.text-dark-grey,
   body.dark-mode .header-icon-box i,
   body.dark-mode .header-icon-box i.text-dark,
   body.dark-mode .header-icon-box i.text-dark-grey {
       color: #CBD5E1 !important;
   }

   html[data-pms-theme="dark"] .header-icon-box:hover i,
   html[data-pms-theme="dark"] .header-icon-box:hover i.text-dark,
   [data-pms-theme="dark"] .header-icon-box:hover i {
       color: #2F6BFF !important;
   }

   /* Sidebar Menu */
   html[data-pms-theme="dark"] .layout-menu,
   html[data-theme="dark"] .layout-menu,
   html[data-bs-theme="dark"] .layout-menu,
   [data-pms-theme="dark"] .layout-menu,
   body.dark-mode .layout-menu {
       background: #0B1026 !important;
       border-color: rgba(238, 241, 251, 0.09) !important;
   }

   html[data-pms-theme="dark"] .bg-menu-theme,
   html[data-theme="dark"] .bg-menu-theme,
   [data-pms-theme="dark"] .bg-menu-theme,
   body.dark-mode .bg-menu-theme {
       background: #0B1026 !important;
   }

   html[data-pms-theme="dark"] .app-brand,
   html[data-theme="dark"] .app-brand,
   [data-pms-theme="dark"] .app-brand,
   body.dark-mode .app-brand {
       background: #0B1026 !important;
       border-color: rgba(238, 241, 251, 0.09) !important;
   }

   html[data-pms-theme="dark"] .app-brand-text.demo,
   html[data-theme="dark"] .app-brand-text.demo,
   [data-pms-theme="dark"] .app-brand-text.demo {
       color: #EEF1FB !important;
   }

   html[data-pms-theme="dark"] .menu-inner .menu-item .menu-link,
   html[data-theme="dark"] .menu-inner .menu-item .menu-link,
   [data-pms-theme="dark"] .menu-inner .menu-item .menu-link,
   body.dark-mode .menu-inner .menu-item .menu-link {
       color: #CBD5E1 !important;
   }

   html[data-pms-theme="dark"] .menu-inner .menu-item .menu-link i,
   html[data-pms-theme="dark"] .menu-inner .menu-item .menu-link .menu-icon,
   [data-pms-theme="dark"] .menu-inner .menu-item .menu-link i,
   [data-pms-theme="dark"] .menu-inner .menu-item .menu-link .menu-icon {
       color: #9AA3C7 !important;
   }

   html[data-pms-theme="dark"] .menu-inner .menu-item:hover > .menu-link,
   [data-pms-theme="dark"] .menu-inner .menu-item:hover > .menu-link {
       background-color: #141B3D !important;
       color: #EEF1FB !important;
   }

   html[data-pms-theme="dark"] .menu-inner .menu-item:hover > .menu-link i,
   html[data-pms-theme="dark"] .menu-inner .menu-item:hover > .menu-link .menu-icon,
   [data-pms-theme="dark"] .menu-inner .menu-item:hover > .menu-link i {
       color: #2F6BFF !important;
   }

   html[data-pms-theme="dark"] .menu-inner .menu-item.active > .menu-link,
   [data-pms-theme="dark"] .menu-inner .menu-item.active > .menu-link {
       background: rgba(47, 107, 255, 0.18) !important;
       color: #60A5FA !important;
       font-weight: 700 !important;
       box-shadow: inset 3px 0 0 #2F6BFF !important;
   }

   html[data-pms-theme="dark"] .menu-inner .menu-item.active > .menu-link i,
   html[data-pms-theme="dark"] .menu-inner .menu-item.active > .menu-link .menu-icon,
   [data-pms-theme="dark"] .menu-inner .menu-item.active > .menu-link i {
       color: #2F6BFF !important;
   }

   html[data-pms-theme="dark"] .menu-inner .menu-item.open > .menu-link,
   [data-pms-theme="dark"] .menu-inner .menu-item.open > .menu-link {
       background-color: #141B3D !important;
       color: #EEF1FB !important;
   }

   html[data-pms-theme="dark"] .menu-inner .menu-item.open > .menu-link i,
   [data-pms-theme="dark"] .menu-inner .menu-item.open > .menu-link i {
       color: #2F6BFF !important;
   }

   html[data-pms-theme="dark"] .menu-sub,
   [data-pms-theme="dark"] .menu-sub {
       background: rgba(7, 11, 26, 0.5) !important;
       border-radius: 8px;
   }

   html[data-pms-theme="dark"] .menu-sub .menu-item .menu-link,
   [data-pms-theme="dark"] .menu-sub .menu-item .menu-link {
       color: #9AA3C7 !important;
   }

   html[data-pms-theme="dark"] .menu-sub .menu-item:hover .menu-link,
   [data-pms-theme="dark"] .menu-sub .menu-item:hover .menu-link {
       color: #EEF1FB !important;
       background-color: #141B3D !important;
   }

   html[data-pms-theme="dark"] .menu-sub .menu-item.active .menu-link,
   [data-pms-theme="dark"] .menu-sub .menu-item.active .menu-link {
       color: #2F6BFF !important;
       background: rgba(47, 107, 255, 0.16) !important;
       box-shadow: inset 2px 0 0 #2F6BFF !important;
   }

   html[data-pms-theme="dark"] .menu-header-text,
   [data-pms-theme="dark"] .menu-header-text {
       color: #6B739A !important;
   }

   /* Dropdowns in Dark Mode */
   html[data-pms-theme="dark"] .dropdown-menu,
   html[data-theme="dark"] .dropdown-menu,
   html[data-bs-theme="dark"] .dropdown-menu,
   [data-pms-theme="dark"] .dropdown-menu,
   body.dark-mode .dropdown-menu {
       background: #0F1530 !important;
       border-color: rgba(238, 241, 251, 0.12) !important;
       box-shadow: 0 16px 36px rgba(0, 0, 0, 0.6) !important;
       color: #CBD5E1 !important;
   }

   html[data-pms-theme="dark"] .dropdown-item,
   html[data-pms-theme="dark"] .dropdown-item.text-dark,
   [data-pms-theme="dark"] .dropdown-item,
   [data-pms-theme="dark"] .dropdown-item.text-dark {
       color: #CBD5E1 !important;
   }

   html[data-pms-theme="dark"] .dropdown-item:hover,
   [data-pms-theme="dark"] .dropdown-item:hover {
       background-color: #141B3D !important;
       color: #2F6BFF !important;
   }

   html[data-pms-theme="dark"] .dropdown-item:hover i,
   [data-pms-theme="dark"] .dropdown-item:hover i {
       color: #2F6BFF !important;
   }

   html[data-pms-theme="dark"] .dropdown-header,
   [data-pms-theme="dark"] .dropdown-header {
       color: #9AA3C7 !important;
   }

   html[data-pms-theme="dark"] .dropdown-divider,
   [data-pms-theme="dark"] .dropdown-divider {
       border-color: rgba(238, 241, 251, 0.09) !important;
   }

   html[data-pms-theme="dark"] .dropdown-menu .bg-white,
   html[data-pms-theme="dark"] .dropdown-menu li.bg-white,
   [data-pms-theme="dark"] .dropdown-menu .bg-white,
   [data-pms-theme="dark"] .dropdown-menu li.bg-white {
       background-color: #141B3D !important;
       color: #EEF1FB !important;
   }

   html[data-pms-theme="dark"] .dropdown-menu .text-dark,
   [data-pms-theme="dark"] .dropdown-menu .text-dark {
       color: #EEF1FB !important;
   }

   /* Modals in Dark Mode */
   html[data-pms-theme="dark"] .modal-content,
   html[data-theme="dark"] .modal-content,
   html[data-bs-theme="dark"] .modal-content,
   [data-pms-theme="dark"] .modal-content,
   body.dark-mode .modal-content {
       background-color: #0F1530 !important;
       color: #CBD5E1 !important;
       border-color: rgba(238, 241, 251, 0.12) !important;
       box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.7) !important;
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
    /* Global Fix: Ensure all inline Add buttons (in input groups & flex containers) have ample size, minimum width, and never squish */
    .d-flex > button,
    .input-group > button,
    .d-flex > .btn,
    .input-group > .btn {
        flex-shrink: 0 !important;
        white-space: nowrap !important;
        min-width: 76px;
        padding-left: 0.9rem !important;
        padding-right: 0.9rem !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* ===================== SUBSCRIPTION & SUSPENSION BANNERS ===================== */
    .pms-suspended-banner {
        background: linear-gradient(135deg, #fef2f2 0%, #ffe4e6 100%) !important;
        border: 1px solid #fecaca !important;
        border-left: 5px solid #ef4444 !important;
        border-radius: 14px !important;
        color: #991b1b !important;
        padding: 16px 20px !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08) !important;
    }
    .pms-suspended-banner .pms-banner-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #fee2e2 !important;
        color: #ef4444 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .pms-suspended-banner .pms-banner-title {
        color: #991b1b !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        letter-spacing: -0.01em;
    }
    .pms-suspended-banner .pms-banner-text {
        color: #7f1d1d !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        opacity: 0.95 !important;
        margin-top: 2px;
    }
    .pms-suspended-banner .pms-banner-btn {
        font-size: 13px !important;
        background: #ef4444 !important;
        border-color: #ef4444 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25) !important;
    }
    .pms-suspended-banner .pms-banner-btn:hover {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35) !important;
    }

    /* Expiring Banner - Light Mode */
    .pms-expiring-banner {
        border-radius: 14px !important;
        padding: 16px 20px !important;
    }
    .pms-expiring-banner.is-urgent {
        background: linear-gradient(135deg, #fff5f5 0%, #fef2f2 100%) !important;
        border: 1px solid #fecaca !important;
        border-left: 5px solid #ef4444 !important;
        color: #991b1b !important;
    }
    .pms-expiring-banner.is-urgent .pms-banner-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #fee2e2 !important;
        color: #ef4444 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .pms-expiring-banner.is-urgent .pms-banner-title {
        color: #991b1b !important;
        font-size: 15px !important;
        font-weight: 700 !important;
    }
    .pms-expiring-banner.is-urgent .pms-banner-text {
        color: #7f1d1d !important;
        font-size: 13px !important;
        opacity: 0.95 !important;
    }

    .pms-expiring-banner.is-warning {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%) !important;
        border: 1px solid #fde68a !important;
        border-left: 5px solid #f59e0b !important;
        color: #92400e !important;
    }
    .pms-expiring-banner.is-warning .pms-banner-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #fde68a !important;
        color: #d97706 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .pms-expiring-banner.is-warning .pms-banner-title {
        color: #92400e !important;
        font-size: 15px !important;
        font-weight: 700 !important;
    }
    .pms-expiring-banner.is-warning .pms-banner-text {
        color: #78350f !important;
        font-size: 13px !important;
        opacity: 0.95 !important;
    }
    .pms-expiring-banner .pms-banner-btn-secondary {
        font-size: 12.5px !important;
        background: transparent !important;
        border: 1px solid rgba(0, 0, 0, 0.2) !important;
        color: #1e293b !important;
    }

    /* ===================== SUSPENDED & EXPIRING BANNERS — DARK MODE ===================== */
    html[data-pms-theme="dark"] .pms-suspended-banner,
    html[data-theme="dark"] .pms-suspended-banner,
    html[data-bs-theme="dark"] .pms-suspended-banner,
    [data-pms-theme="dark"] .pms-suspended-banner,
    [data-theme="dark"] .pms-suspended-banner,
    body.dark-mode .pms-suspended-banner {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.16) 0%, rgba(185, 28, 28, 0.28) 100%) !important;
        border: 1px solid rgba(239, 68, 68, 0.38) !important;
        border-left: 5px solid #ef4444 !important;
        color: #fca5a5 !important;
        box-shadow: 0 8px 24px rgba(239, 68, 68, 0.18) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
    }
    html[data-pms-theme="dark"] .pms-suspended-banner .pms-banner-icon,
    html[data-theme="dark"] .pms-suspended-banner .pms-banner-icon,
    html[data-bs-theme="dark"] .pms-suspended-banner .pms-banner-icon,
    [data-pms-theme="dark"] .pms-suspended-banner .pms-banner-icon,
    [data-theme="dark"] .pms-suspended-banner .pms-banner-icon,
    body.dark-mode .pms-suspended-banner .pms-banner-icon {
        background: rgba(239, 68, 68, 0.25) !important;
        color: #f87171 !important;
        border: 1px solid rgba(239, 68, 68, 0.45) !important;
    }
    html[data-pms-theme="dark"] .pms-suspended-banner .pms-banner-title,
    html[data-theme="dark"] .pms-suspended-banner .pms-banner-title,
    html[data-bs-theme="dark"] .pms-suspended-banner .pms-banner-title,
    [data-pms-theme="dark"] .pms-suspended-banner .pms-banner-title,
    [data-theme="dark"] .pms-suspended-banner .pms-banner-title,
    body.dark-mode .pms-suspended-banner .pms-banner-title {
        color: #ffffff !important;
        font-weight: 750 !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
    }
    html[data-pms-theme="dark"] .pms-suspended-banner .pms-banner-text,
    html[data-theme="dark"] .pms-suspended-banner .pms-banner-text,
    html[data-bs-theme="dark"] .pms-suspended-banner .pms-banner-text,
    [data-pms-theme="dark"] .pms-suspended-banner .pms-banner-text,
    [data-theme="dark"] .pms-suspended-banner .pms-banner-text,
    body.dark-mode .pms-suspended-banner .pms-banner-text {
        color: #fecaca !important;
        opacity: 1 !important;
        font-weight: 500 !important;
    }
    html[data-pms-theme="dark"] .pms-suspended-banner .pms-banner-btn,
    html[data-theme="dark"] .pms-suspended-banner .pms-banner-btn,
    html[data-bs-theme="dark"] .pms-suspended-banner .pms-banner-btn,
    [data-pms-theme="dark"] .pms-suspended-banner .pms-banner-btn,
    [data-theme="dark"] .pms-suspended-banner .pms-banner-btn,
    body.dark-mode .pms-suspended-banner .pms-banner-btn {
        background: linear-gradient(135deg, #ef4444, #dc2626) !important;
        border-color: #ef4444 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 16px rgba(239, 68, 68, 0.45) !important;
    }

    /* Dark Mode Expiring Banners */
    html[data-pms-theme="dark"] .pms-expiring-banner.is-urgent,
    html[data-theme="dark"] .pms-expiring-banner.is-urgent,
    html[data-bs-theme="dark"] .pms-expiring-banner.is-urgent,
    [data-pms-theme="dark"] .pms-expiring-banner.is-urgent,
    [data-theme="dark"] .pms-expiring-banner.is-urgent,
    body.dark-mode .pms-expiring-banner.is-urgent {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.16) 0%, rgba(185, 28, 28, 0.28) 100%) !important;
        border: 1px solid rgba(239, 68, 68, 0.38) !important;
        border-left: 5px solid #ef4444 !important;
        color: #fca5a5 !important;
        box-shadow: 0 8px 24px rgba(239, 68, 68, 0.18) !important;
    }
    html[data-pms-theme="dark"] .pms-expiring-banner.is-urgent .pms-banner-icon,
    html[data-theme="dark"] .pms-expiring-banner.is-urgent .pms-banner-icon,
    [data-pms-theme="dark"] .pms-expiring-banner.is-urgent .pms-banner-icon {
        background: rgba(239, 68, 68, 0.25) !important;
        color: #f87171 !important;
        border: 1px solid rgba(239, 68, 68, 0.45) !important;
    }
    html[data-pms-theme="dark"] .pms-expiring-banner.is-urgent .pms-banner-title,
    html[data-theme="dark"] .pms-expiring-banner.is-urgent .pms-banner-title,
    [data-pms-theme="dark"] .pms-expiring-banner.is-urgent .pms-banner-title {
        color: #ffffff !important;
        font-weight: 750 !important;
    }
    html[data-pms-theme="dark"] .pms-expiring-banner.is-urgent .pms-banner-text,
    html[data-theme="dark"] .pms-expiring-banner.is-urgent .pms-banner-text,
    [data-pms-theme="dark"] .pms-expiring-banner.is-urgent .pms-banner-text {
        color: #fecaca !important;
        opacity: 1 !important;
        font-weight: 500 !important;
    }

    html[data-pms-theme="dark"] .pms-expiring-banner.is-warning,
    html[data-theme="dark"] .pms-expiring-banner.is-warning,
    html[data-bs-theme="dark"] .pms-expiring-banner.is-warning,
    [data-pms-theme="dark"] .pms-expiring-banner.is-warning,
    [data-theme="dark"] .pms-expiring-banner.is-warning,
    body.dark-mode .pms-expiring-banner.is-warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.16) 0%, rgba(180, 83, 9, 0.28) 100%) !important;
        border: 1px solid rgba(245, 158, 11, 0.38) !important;
        border-left: 5px solid #f59e0b !important;
        color: #fde68a !important;
        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.18) !important;
    }
    html[data-pms-theme="dark"] .pms-expiring-banner.is-warning .pms-banner-icon,
    html[data-theme="dark"] .pms-expiring-banner.is-warning .pms-banner-icon,
    [data-pms-theme="dark"] .pms-expiring-banner.is-warning .pms-banner-icon {
        background: rgba(245, 158, 11, 0.25) !important;
        color: #fbbf24 !important;
        border: 1px solid rgba(245, 158, 11, 0.45) !important;
    }
    html[data-pms-theme="dark"] .pms-expiring-banner.is-warning .pms-banner-title,
    html[data-theme="dark"] .pms-expiring-banner.is-warning .pms-banner-title,
    [data-pms-theme="dark"] .pms-expiring-banner.is-warning .pms-banner-title {
        color: #ffffff !important;
        font-weight: 750 !important;
    }
    html[data-pms-theme="dark"] .pms-expiring-banner.is-warning .pms-banner-text,
    html[data-theme="dark"] .pms-expiring-banner.is-warning .pms-banner-text,
    [data-pms-theme="dark"] .pms-expiring-banner.is-warning .pms-banner-text {
        color: #fef3c7 !important;
        opacity: 1 !important;
        font-weight: 500 !important;
    }
    html[data-pms-theme="dark"] .pms-expiring-banner .pms-banner-btn-secondary,
    html[data-theme="dark"] .pms-expiring-banner .pms-banner-btn-secondary,
    [data-pms-theme="dark"] .pms-expiring-banner .pms-banner-btn-secondary {
        border-color: rgba(238, 241, 251, 0.25) !important;
        color: #e2e8f0 !important;
        background: rgba(255, 255, 255, 0.08) !important;
    }

   /* An active Department parent must not give inactive children white text. */
   html:not([data-pms-theme="dark"]):not([data-theme="dark"]):not([data-bs-theme="dark"]):not(.dark) #layout-menu .department-sidebar-menu > .menu-sub > .menu-item:not(.active) > .menu-link {
       background: transparent !important;
       color: #334155 !important;
       -webkit-text-fill-color: #334155 !important;
       box-shadow: none !important;
   }
   html:not([data-pms-theme="dark"]):not([data-theme="dark"]):not([data-bs-theme="dark"]):not(.dark) #layout-menu .department-sidebar-menu > .menu-sub > .menu-item:not(.active) > .menu-link > div {
       color: #334155 !important;
       -webkit-text-fill-color: #334155 !important;
   }
   html:not([data-pms-theme="dark"]):not([data-theme="dark"]):not([data-bs-theme="dark"]):not(.dark) #layout-menu .department-sidebar-menu > .menu-sub > .menu-item:not(.active) > .menu-link:hover {
       background: #eff6ff !important;
   }
   html:not([data-pms-theme="dark"]):not([data-theme="dark"]):not([data-bs-theme="dark"]):not(.dark) #layout-menu .department-sidebar-menu > .menu-sub > .menu-item.active > .menu-link {
       background: #2f6bff !important;
       color: #fff !important;
       -webkit-text-fill-color: #fff !important;
   }
   html:not([data-pms-theme="dark"]):not([data-theme="dark"]):not([data-bs-theme="dark"]):not(.dark) #layout-menu .department-sidebar-menu > .menu-sub > .menu-item.active > .menu-link > div {
       color: #fff !important;
       -webkit-text-fill-color: #fff !important;
   }
</style>
