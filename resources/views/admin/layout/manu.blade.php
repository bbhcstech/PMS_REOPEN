<!-- //layout main dashboard menu ... -->

<!-- //layout/manu.blade.php -->

@php $userId = Auth::id(); @endphp

<style>
   /* ===================== PURPLE & WHITE COLOR THEME ===================== */

   /* Global Purple Theme Variables */
   :root {
       --purple-primary: #7C3AED;
       --purple-light: #8B5CF6;
       --purple-dark: #6D28D9;
       --purple-soft: #EDE9FE;
       --purple-gradient: linear-gradient(135deg, #7C3AED 0%, #8B5CF6 100%);
       --purple-hover: rgba(124, 58, 237, 0.08);
       --white-pure: #FFFFFF;
       --white-soft: #F9FAFB;
       --text-dark: #1F2937;
       --text-soft: #4B5563;
       --shadow-purple: 0 4px 20px rgba(124, 58, 237, 0.12);
   }

   /* ===================== GLOBAL MODAL FIX ===================== */
   .modal-backdrop.show {
       opacity: 0.4 !important;
   }

   body.modal-open {
       opacity: 1 !important;
   }

   .modal { z-index: 1050; }
   .modal-backdrop { z-index: 1040; }

   .modal-content {
       background-color: var(--white-pure) !important;
       box-shadow: 0 0.75rem 1.5rem rgba(124, 58, 237, 0.15);
       border-radius: 10px;
       border: 1px solid rgba(124, 58, 237, 0.1);
   }

   .modal-header {
       border-bottom: 1px solid rgba(124, 58, 237, 0.1);
       color: var(--purple-dark);
   }

   .modal-title {
       color: var(--purple-primary);
       font-weight: 700;
   }

   .btn-close:hover {
       background-color: var(--purple-soft);
       border-radius: 4px;
   }

   /* ===================== NAVBAR CORE FIX ===================== */
   #layout-navbar {
       display: flex;
       flex-wrap: nowrap !important;
       align-items: center;
       width: 100%;
       background: var(--white-pure) !important;
       box-shadow: 0 2px 10px rgba(124, 58, 237, 0.08);
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

   /* ===================== RIGHT ICONS FIX ===================== */
   .navbar-nav-right,
   #layout-navbar .navbar-nav-right {
       display: flex;
       flex-direction: row !important;
       flex-wrap: nowrap !important;
       align-items: center;
       gap: 8px;
   }

   .header-icon-box {
       display: flex;
       align-items: center;
       justify-content: center;
       min-width: 36px;
       color: var(--purple-primary);
       transition: all 0.2s ease;
   }

   .header-icon-box:hover {
       color: var(--purple-dark);
       transform: scale(1.1);
   }

   .header-icon-box i {
       color: var(--purple-primary);
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

   /* ===================== EXTRA SAFETY ===================== */
   .layout-menu {
       transition: transform 0.3s ease;
       background: linear-gradient(135deg, var(--white-pure) 0%, var(--white-soft) 100%);
       border-right: 1px solid rgba(124, 58, 237, 0.1);
   }

   .layout-menu-active .layout-menu {
       transform: translateX(0);
   }

   /* ===================== MENU THEME - PURPLE & WHITE ===================== */
   .bg-menu-theme {
       background: linear-gradient(135deg, var(--white-pure) 0%, var(--white-soft) 100%) !important;
   }

   .app-brand {
       background: var(--white-pure);
       border-bottom: 1px solid rgba(124, 58, 237, 0.1);
   }

   .app-brand-text {
       color: var(--purple-primary) !important;
       font-weight: 800 !important;
   }

   .app-brand-logo.demo {
       width: 42px;
       height: 42px;
       border-radius: 14px;
       overflow: hidden;
       display: inline-flex;
       align-items: center;
       justify-content: center;
       flex: 0 0 auto;
       box-shadow: 0 10px 22px rgba(5, 105, 255, 0.18);
   }

   .app-brand-logo.demo img {
       width: 100%;
       height: 100%;
       object-fit: cover;
       display: block;
   }

   .app-brand-text.demo {
       max-width: 132px;
       overflow: hidden;
       text-overflow: ellipsis;
       white-space: nowrap;
       font-size: 1.08rem !important;
       line-height: 1.1;
       letter-spacing: 0 !important;
       background: linear-gradient(135deg, #0569ff, #13d5e7 50%, #8f25ff);
       -webkit-background-clip: text;
       background-clip: text;
       color: transparent !important;
   }

   .menu-inner .menu-item .menu-link {
       color: var(--text-dark);
       transition: all 0.2s ease;
   }

   .menu-inner .menu-item .menu-link i {
       color: var(--purple-primary);
   }

   .menu-inner .menu-item:hover > .menu-link {
       background-color: var(--purple-hover);
       color: var(--purple-primary);
   }

   .menu-inner .menu-item.active > .menu-link {
       background: var(--purple-gradient);
       color: var(--white-pure);
       box-shadow: var(--shadow-purple);
   }

   .menu-inner .menu-item.active > .menu-link i {
       color: var(--white-pure);
   }

   .menu-inner .menu-item.active:not(.open) > .menu-link {
       position: relative;
       overflow: hidden;
       animation: sidebarActiveGlow 1.8s ease-in-out infinite;
   }

   .menu-inner .menu-item.active:not(.open) > .menu-link::after {
       content: "";
       position: absolute;
       right: 12px;
       top: 50%;
       width: 8px;
       height: 8px;
       border-radius: 999px;
       background: #ffffff;
       box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.9);
       transform: translateY(-50%);
       animation: sidebarActiveDot 1.25s ease-in-out infinite;
   }

   @keyframes sidebarActiveGlow {
       0%, 100% {
           box-shadow: 0 4px 18px rgba(124, 58, 237, 0.18);
       }
       50% {
           box-shadow: 0 8px 26px rgba(124, 58, 237, 0.38);
       }
   }

   @keyframes sidebarActiveDot {
       0% {
           box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.9);
       }
       70% {
           box-shadow: 0 0 0 8px rgba(255, 255, 255, 0);
       }
       100% {
           box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
       }
   }

   .menu-inner .menu-item.open > .menu-link {
       background-color: var(--purple-hover);
       color: var(--purple-primary);
   }

   .menu-sub {
       background: rgba(124, 58, 237, 0.02);
   }

   .menu-sub .menu-item .menu-link {
       color: var(--text-soft);
   }

   .menu-sub .menu-item:hover .menu-link {
       color: var(--purple-primary);
       background-color: var(--purple-hover);
   }

   .menu-sub .menu-item.active .menu-link {
       color: var(--purple-primary);
       font-weight: 700;
       background: linear-gradient(90deg, var(--purple-soft) 0%, rgba(124, 58, 237, 0.05) 100%);
       border-left: 3px solid var(--purple-primary);
   }

   .menu-divider {
       border-color: rgba(124, 58, 237, 0.1) !important;
   }

   /* Dropdown Menu Purple Theme */
   .dropdown-menu {
       border: 1px solid rgba(124, 58, 237, 0.1);
       box-shadow: var(--shadow-purple);
   }

   .dropdown-item:hover {
       background-color: var(--purple-hover);
       color: var(--purple-primary);
   }

   .dropdown-item i {
       color: var(--purple-primary);
   }

   /* Badge Purple Theme */
   .badge.bg-danger {
       background: var(--purple-gradient) !important;
       color: var(--white-pure);
   }

   /* Form Controls Purple Theme */
   .form-control:focus,
   .form-select:focus {
       border-color: var(--purple-light);
       box-shadow: 0 0 0 0.25rem rgba(124, 58, 237, 0.1);
   }

   .btn-primary {
       background: var(--purple-gradient) !important;
       border: none !important;
       color: var(--white-pure) !important;
   }

   .btn-primary:hover {
       background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-primary) 100%) !important;
       box-shadow: var(--shadow-purple);
   }

   .btn-outline-primary {
       border-color: var(--purple-primary) !important;
       color: var(--purple-primary) !important;
   }

   .btn-outline-primary:hover {
       background: var(--purple-gradient) !important;
       color: var(--white-pure) !important;
   }

   /* Text Colors */
   .text-primary {
       color: var(--purple-primary) !important;
   }

   .text-dark {
       color: var(--text-dark) !important;
   }

   .text-muted {
       color: var(--text-soft) !important;
   }

   /* Links */
   a {
       color: var(--purple-primary);
       transition: color 0.2s ease;
   }

   a:hover {
       color: var(--purple-dark);
   }

   /* Notification Dropdown */
   .notification-dropdown {
       border-top: 3px solid var(--purple-primary);
   }

   .notification-dropdown li:hover {
       background-color: var(--purple-hover);
   }

   .notification-bell.has-unread i {
       color: #7C3AED !important;
       animation: pmsBellShake 1.15s ease-in-out infinite;
       transform-origin: top center;
   }

   .notification-item-unread {
       background: linear-gradient(90deg, rgba(124, 58, 237, 0.12), rgba(255, 255, 255, 0.98));
       border-left: 4px solid #7C3AED;
   }

   .notification-item-read {
       background: #fff;
       opacity: 0.72;
   }

   .notification-unread-dot {
       width: 8px;
       height: 8px;
       border-radius: 50%;
       background: #7C3AED;
       box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12);
       flex: 0 0 8px;
       margin-top: 6px;
   }

   @keyframes pmsBellShake {
       0%, 100% { transform: rotate(0); }
       15% { transform: rotate(12deg); }
       30% { transform: rotate(-10deg); }
       45% { transform: rotate(7deg); }
       60% { transform: rotate(-5deg); }
       75% { transform: rotate(2deg); }
   }

   /* Avatar border */
   .avatar-online img {
       width: 40px;
       height: 40px;
       min-width: 40px;
       min-height: 40px;
       max-width: 40px;
       max-height: 40px;
       border: 2px solid var(--purple-primary);
       border-radius: 50% !important;
       object-fit: cover;
       object-position: center;
       display: block;
   }

   .navbar-profile-avatar {
       width: 40px !important;
       height: 40px !important;
       aspect-ratio: 1 / 1;
       border-radius: 50% !important;
       object-fit: cover;
       object-position: center;
   }

   /* Timer Icon */
   .text-danger {
       color: var(--purple-primary) !important;
   }

   /* Modal Footer */
   .modal-footer {
       border-top: 1px solid rgba(124, 58, 237, 0.1);
   }

   /* Active Timer Modal Header */
   .modal-header.bg-danger {
       background: var(--purple-gradient) !important;
   }

   /* List Group Items */
   .list-group-item {
       border-left: 5px solid var(--purple-primary) !important;
       border-color: rgba(124, 58, 237, 0.1);
   }

   /* ===================== END PURPLE THEME ===================== */

   /* ===================== SIDEBAR FONT BOLD FIX ===================== */
/* Make all sidebar menu text bold for better visibility */
.menu-inner .menu-item .menu-link,
.menu-inner .menu-item .menu-link div,
.menu-inner .menu-item .menu-link .text-truncate,
.menu-sub .menu-item .menu-link,
.menu-sub .menu-item .menu-link div,
.menu-sub .menu-item .menu-link .text-truncate {
    font-weight: 600 !important;
    color: #1F2937 !important; /* Dark gray for better contrast */
}

/* Make active menu items even bolder */
.menu-inner .menu-item.active > .menu-link,
.menu-inner .menu-item.active > .menu-link div,
.menu-inner .menu-item.active > .menu-link .text-truncate {
    font-weight: 700 !important;
    color: #FFFFFF !important; /* White text on purple gradient */
}

/* Active submenu items */
.menu-sub .menu-item.active .menu-link,
.menu-sub .menu-item.active .menu-link div,
.menu-sub .menu-item.active .menu-link .text-truncate {
    font-weight: 700 !important;
    color: #7C3AED !important; /* Purple for active submenu items */
}

/* Hover states */
.menu-inner .menu-item:hover > .menu-link,
.menu-inner .menu-item:hover > .menu-link div,
.menu-inner .menu-item:hover > .menu-link .text-truncate,
.menu-sub .menu-item:hover .menu-link,
.menu-sub .menu-item:hover .menu-link div,
.menu-sub .menu-item:hover .menu-link .text-truncate {
    font-weight: 600 !important;
    color: #7C3AED !important; /* Purple on hover */
}

/* Section headers/ parent menu items with toggle */
.menu-item.has-sub > .menu-link,
.menu-item .menu-toggle {
    font-weight: 650 !important;
}

/* Ensure submenu items are also bold */
.menu-sub .menu-link {
    font-weight: 600 !important;
}

/* Department submenu items fix */
.menu-sub .menu-sub .menu-link,
.menu-sub .menu-sub .menu-link div {
    font-weight: 600 !important;
}

/* Override any existing font weights */
.app-brand-text {
    font-weight: 800 !important; /* Keep brand text extra bold */
}

/* Ensure all sidebar text is visible */
.menu-text,
[data-i18n] {
    font-weight: 600 !important;
}

/* Fix for the department toggle text */
.menu-item .menu-toggle .text-truncate,
.menu-item.has-sub .menu-link .text-truncate {
    font-weight: 650 !important;
}

.sticky-note-trigger {
    position: relative;
}

.sticky-note-count {
    position: absolute;
    top: -8px;
    right: -10px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #ef4444;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 14px rgba(239, 68, 68, 0.28);
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
    background: #7C3AED;
    box-shadow: 0 6px 14px rgba(124, 58, 237, 0.18);
}

.sidebar-notification-badge.is-visible {
    display: inline-flex;
}

.sidebar-notification-badge.type-new { background: #2563eb; }
.sidebar-notification-badge.type-pending { background: #f59e0b; }
.sidebar-notification-badge.type-issue { background: #ef4444; }
.sidebar-notification-badge.type-warning { background: #d97706; }
.sidebar-notification-badge.type-unread { background: #7C3AED; }

.notification-bell {
    position: relative;
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    border: 1px solid rgba(15, 23, 42, 0.08);
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}

.notification-bell:hover {
    background: #eef2ff;
    box-shadow: 0 12px 30px rgba(79, 70, 229, 0.16);
    transform: translateY(-1px);
}

.notification-bell.has-unread {
    color: #4f46e5;
    animation: notificationBellPulse 1.8s ease-in-out infinite;
}

.notification-bell .badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 20px;
    height: 20px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 900;
    border: 2px solid #fff;
}

.notification-dropdown {
    width: min(430px, calc(100vw - 24px)) !important;
    max-height: 560px;
    overflow: hidden;
    border-radius: 18px;
}

.notification-dropdown-head {
    padding: 16px 18px;
    background: linear-gradient(135deg, #f8fafc, #eef2ff);
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
}

.notification-dropdown-body {
    max-height: 410px;
    overflow-y: auto;
    padding: 8px;
}

.notification-card-link {
    display: grid;
    grid-template-columns: 44px 1fr auto;
    gap: 12px;
    align-items: start;
    padding: 12px;
    border-radius: 14px;
    text-decoration: none;
    color: inherit;
    border: 1px solid transparent;
    transition: background .16s ease, border-color .16s ease, transform .16s ease;
}

.notification-card-link:hover {
    background: #f8fafc;
    border-color: rgba(79, 70, 229, 0.14);
    transform: translateY(-1px);
    color: inherit;
}

.notification-card-link.is-unread {
    background: #eef2ff;
    border-color: rgba(79, 70, 229, 0.18);
}

.notification-avatar-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 17px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    box-shadow: 0 10px 22px rgba(99, 102, 241, 0.24);
}

.notification-avatar-icon.color-warning { background: linear-gradient(135deg, #f59e0b, #f97316); }
.notification-avatar-icon.color-success { background: linear-gradient(135deg, #10b981, #059669); }
.notification-avatar-icon.color-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
.notification-avatar-icon.color-info { background: linear-gradient(135deg, #06b6d4, #2563eb); }

.notification-title {
    display: block;
    font-size: 14px;
    font-weight: 900;
    line-height: 1.25;
    margin-bottom: 4px;
}

.notification-message {
    display: block;
    font-size: 12px;
    color: #64748b;
    line-height: 1.35;
    margin-bottom: 6px;
}

.notification-time {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    color: #94a3b8;
    font-weight: 800;
}

.notification-unread-dot {
    width: 9px;
    height: 9px;
    margin-top: 17px;
    border-radius: 50%;
    background: #4f46e5;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
}

.notification-dropdown-foot {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 16px;
    border-top: 1px solid rgba(15, 23, 42, 0.08);
    background: #fff;
}

@keyframes notificationBellPulse {
    0%, 100% { box-shadow: 0 0 0 rgba(79, 70, 229, 0); }
    50% { box-shadow: 0 0 0 8px rgba(79, 70, 229, 0.08); }
}

.menu-link.sidebar-has-important {
    position: relative;
}

.menu-link.sidebar-has-important::before {
    content: "";
    position: absolute;
    inset: 7px 8px 7px auto;
    width: 3px;
    border-radius: 999px;
    background: currentColor;
    opacity: 0.55;
    animation: sidebarBadgeGlow 1.65s ease-in-out infinite;
}

.menu-item.sidebar-has-important-item > .menu-link {
    background: rgba(245, 158, 11, 0.08);
}

@keyframes sidebarBadgeGlow {
    0%, 100% { opacity: 0.35; box-shadow: 0 0 0 rgba(124, 58, 237, 0); }
    50% { opacity: 0.9; box-shadow: 0 0 12px rgba(124, 58, 237, 0.45); }
}

.sticky-note-dock {
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 1025;
    width: min(340px, calc(100vw - 32px));
    display: grid;
    gap: 12px;
    pointer-events: none;
}

.sticky-note-card {
    pointer-events: auto;
    position: relative;
    padding: 16px 16px 14px;
    border-radius: 8px 8px 18px 8px;
    color: #243029;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
    transform-origin: top left;
    animation: stickyFloatIn 0.55s ease both, stickySway 4.5s ease-in-out infinite;
    overflow: hidden;
}

.sticky-note-card::before {
    content: "";
    position: absolute;
    inset: 0 0 auto;
    height: 10px;
    background: rgba(255, 255, 255, 0.38);
}

.sticky-note-card:nth-child(2n) {
    animation-delay: 0.08s, 0.2s;
    transform: rotate(1deg);
}

.sticky-note-card:nth-child(3n) {
    animation-delay: 0.14s, 0.35s;
    transform: rotate(-1deg);
}

.sticky-note-card.yellow { background: linear-gradient(135deg, #fff7ad, #fde68a); }
.sticky-note-card.blue { background: linear-gradient(135deg, #bfdbfe, #93c5fd); }
.sticky-note-card.red { background: linear-gradient(135deg, #fecaca, #fca5a5); }
.sticky-note-card.gray { background: linear-gradient(135deg, #f1f5f9, #cbd5e1); }
.sticky-note-card.purple { background: linear-gradient(135deg, #ddd6fe, #c4b5fd); }
.sticky-note-card.green { background: linear-gradient(135deg, #bbf7d0, #86efac); }

.sticky-note-card p {
    margin: 0 0 10px;
    font-size: 0.94rem;
    font-weight: 650;
    line-height: 1.35;
    white-space: pre-line;
}

.sticky-note-meta {
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(31, 41, 55, 0.62);
}

.sticky-note-actions {
    position: absolute;
    top: 8px;
    right: 8px;
    display: flex;
    gap: 4px;
}

.sticky-note-actions button {
    width: 26px;
    height: 26px;
    border: 0;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.58);
    color: #1f2937;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.sticky-notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 14px;
}

@keyframes stickyFloatIn {
    from { opacity: 0; transform: translateY(18px) rotate(-2deg) scale(0.96); }
    to { opacity: 1; transform: translateY(0) rotate(var(--note-tilt, 0deg)) scale(1); }
}

@keyframes stickySway {
    0%, 100% { translate: 0 0; }
    50% { translate: 0 -4px; }
}

@media (max-width: 768px) {
    .sticky-note-dock {
        left: 12px;
        right: 12px;
        bottom: 12px;
        width: auto;
    }
}

</style>
@php
    $adminRefreshVersion = file_exists(public_path('admin/assets/css/pms-refresh.css')) ? filemtime(public_path('admin/assets/css/pms-refresh.css')) : time();
    $logoVersion = file_exists(public_path('logo.png')) ? filemtime(public_path('logo.png')) : time();
    $brandLogo = $currentCompany?->logo ? asset($currentCompany->logo) : asset('logo.png') . '?v=' . $logoVersion;
    $brandName = $currentCompany?->brand_name ?? 'Bitroxia';
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

            @if($canSeeModule('organization'))
            <li class="menu-item {{ request()->routeIs('organization.*') ? 'active' : '' }}">
              <a href="{{ route('organization.index') }}" class="menu-link" data-sidebar-key="organization">
                  <i class="menu-icon tf-icons bx bx-sitemap"></i>
                  <div class="text-truncate">Organization</div>
              </a>
            </li>
            @endif



            <!-- Layouts -->
            @if($canAnyModule(['employees', 'designations', 'departments', 'attendance', 'leaves', 'holidays', 'awards']))
            <li class="menu-item {{ request()->routeIs('employees.*') ||
                      request()->routeIs('designations.*') ||
                      (request()->routeIs('attendance.*') && !request()->routeIs('attendance.report')) ||
                      request()->routeIs('leaves.*') ||
                      request()->routeIs('holidays.*') ||
                      request()->routeIs('awards.*') ||
                      request()->routeIs('employee.awards')
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
                          <div class="text-truncate"> Department</div>
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
                    @elseif($canSeeModule('awards') && auth()->user()->role === 'employee')
                    <!-- Employee also sees Recognition menu but goes to filtered view -->
                    <li class="menu-item {{ request()->routeIs('awards.*') || request()->routeIs('employee.awards') ? 'active' : '' }}">
                        <a href="{{ route('awards.index') }}" class="menu-link">
                            <div class="text-truncate" data-i18n="Container">My Awards</div>
                        </a>
                    </li>
                @endif

              </ul>
            </li>
            @endif



             <!-- Reports Section -->
            @if($canSeeModule('reports'))
            <li class="menu-item {{ request()->routeIs('attendance.report') || request()->routeIs('admin.leave.report') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle" data-sidebar-key="reports">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt"></i>
                <div class="text-truncate" data-i18n="Layouts">Reports</div>
              </a>

              <ul class="menu-sub">
                  <li class="menu-item {{ request()->routeIs('attendance.report') ? 'active' : '' }}">
                    <a href="{{ route('attendance.report') }}" class="menu-link">
                      <div class="text-truncate">Attendance Report</div>
                    </a>
                  </li>

                  <li class="menu-item {{ request()->routeIs('admin.leave.report') ? 'active' : '' }}">
                    <a href="{{ route('admin.leave.report') }}" class="menu-link">
                      <div class="text-truncate">Leaves Report</div>
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

            @if($canAnyModule(['projects', 'tasks', 'timelogs']))
            <li class="menu-item {{ request()->routeIs('projects.*') ||
                request()->routeIs('tasks.*') || request()->routeIs('users.tasks.*') ||
                request()->routeIs('timelogs.*') || request()->routeIs('task-timer.*') ||
                request()->routeIs('admin.contracts.*') || request()->routeIs('admin.contract-templates.*') ? 'active open' : '' }}">

                <a href="javascript:void(0);" class="menu-link menu-toggle" data-sidebar-key="work">
                    <i class="menu-icon tf-icons bx bx-store"></i>
                    <div class="text-truncate" data-i18n="Front Pages">Work</div>
                </a>

                <ul class="menu-sub">
                    @if($canSeeModule('projects'))
                        <li class="menu-item {{ (request()->routeIs('projects.*') && !request()->routeIs('projects.tasks.*') && !request()->routeIs('projects.timelogs.*')) ? 'active' : '' }}">
                            <a href="{{ route('projects.index') }}" class="menu-link" data-sidebar-key="projects">
                                <div class="text-truncate" data-i18n="Landing">Projects</div>
                            </a>
                        </li>
                    @endif

                    @if($canSeeModule('tasks'))
                    <li class="menu-item {{ request()->routeIs('tasks.*') || request()->routeIs('projects.tasks.*') || request()->routeIs('users.tasks.*') || request()->routeIs('task-timer.*') ? 'active' : '' }}">
                        <a href="{{ route('tasks.index') }}" class="menu-link" data-sidebar-key="tasks">
                            <div class="text-truncate" data-i18n="Pricing">Tasks</div>
                        </a>
                    </li>
                    @endif

                    @if($canSeeModule('timelogs'))
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

            @if($canAnyModule(['payroll', 'payroll-architectures', 'payslips', 'salary-structures', 'payroll-policies', 'payroll-cycles', 'tax-rules', 'bonus-rules', 'deduction-rules', 'overtime-rules', 'payroll-reports', 'payroll-audit-logs', 'payroll-settings', 'payroll-import-export', 'payroll-archive', 'formula-builder']))
            <li class="menu-item {{ request()->routeIs('payroll.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle" data-sidebar-key="payroll">
                    <i class="menu-icon tf-icons bx bx-wallet"></i>
                    <div class="text-truncate">Payroll</div>
                </a>

                <ul class="menu-sub">
                    @if($canSeeModule('payroll'))
                        <li class="menu-item {{ request()->routeIs('payroll.index') ? 'active' : '' }}">
                            <a href="{{ route('payroll.index') }}" class="menu-link"><div>Payroll</div></a>
                        </li>
                    @endif
                    @if($canSeeModule('payroll-architectures'))
                        <li class="menu-item {{ request()->routeIs('payroll.architectures.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.architectures.index') }}" class="menu-link"><div>Payroll Architectures</div></a>
                        </li>
                    @endif
                    @if($canSeeModule('payslips'))
                        <li class="menu-item {{ request()->routeIs('payroll.payslips.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.payslips.index') }}" class="menu-link"><div>Payslips</div></a>
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
                    @if($canSeeModule('payroll-policies'))
                        <li class="menu-item {{ request()->routeIs('payroll.policies.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.policies.index') }}" class="menu-link"><div>Payroll Policies</div></a>
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
                            <a href="{{ route('payroll.reports.index') }}" class="menu-link"><div>Payroll Reports</div></a>
                        </li>
                    @endif
                    @if($canSeeModule('payroll-import-export'))
                        <li class="menu-item {{ request()->routeIs('payroll.import-export.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.import-export.index') }}" class="menu-link"><div>Import Export</div></a>
                        </li>
                    @endif
                    @if($canSeeModule('payroll-archive'))
                        <li class="menu-item {{ request()->routeIs('payroll.archive.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.archive.index') }}" class="menu-link"><div>Payroll Archive</div></a>
                        </li>
                    @endif
                    @if($canSeeModule('payroll-audit-logs'))
                        <li class="menu-item {{ request()->routeIs('payroll.audit-logs.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.audit-logs.index') }}" class="menu-link"><div>Audit Logs</div></a>
                        </li>
                    @endif
                    @if($canSeeModule('payroll-settings'))
                        <li class="menu-item {{ request()->routeIs('payroll.settings.*') ? 'active' : '' }}">
                            <a href="{{ route('payroll.settings.index') }}" class="menu-link"><div>Payroll Settings</div></a>
                        </li>
                    @endif
                </ul>
            </li>
            @endif

<!--    //leads section -->

                   @if($canSeeModule('leads'))
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

                <!-- //ticket section . -->


            @if($canSeeModule('tickets'))
            <li class="menu-item {{ request()->routeIs('tickets.*') || request()->routeIs('ticket-groups.*') ? 'active' : '' }}">
                  <a href="{{ route('tickets.index') }}" class="menu-link" data-sidebar-key="tickets">
                       <i class="menu-icon tf-icons bx bx-receipt"></i>
                      <div class="text-truncate" data-i18n="Dashboard">Ticket</div>
                  </a>
              </li>
            @endif

            @if($canSeeModule('settings'))
                <li class="menu-item {{ request()->routeIs('settings.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.modules.*') || request()->routeIs('admin.role-permissions.*') || request()->routeIs('admin.role-accounts.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-cog"></i>
                        <div class="text-truncate" data-i18n="Settings">Settings</div>
                    </a>
                    <ul class="menu-sub">
                        @if(Route::has('admin.companies.index') && auth()->user()->role === 'admin')
                            <li class="menu-item {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}"><a href="{{ route('admin.companies.index') }}" class="menu-link"><div>Company Management</div></a></li>
                        @endif
                        @if(Route::has('settings.company'))
                            <li class="menu-item {{ request()->routeIs('settings.company') ? 'active' : '' }}"><a href="{{ route('settings.company') }}" class="menu-link"><div>Company Settings</div></a></li>
                        @endif
                        @if(Route::has('admin.settings.app'))
                            <li class="menu-item"><a href="{{ route('admin.settings.app', ['page' => 'app']) }}" class="menu-link"><div>App Settings</div></a></li>
                        @endif
                        @if(Route::has('admin.settings.terms-policy'))
                            <li class="menu-item {{ request()->routeIs('admin.settings.terms-policy*') ? 'active' : '' }}"><a href="{{ route('admin.settings.terms-policy') }}" class="menu-link"><div>Terms &amp; Policy</div></a></li>
                        @endif
                        <li class="menu-item {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}"><a href="{{ route('admin.modules.index') }}" class="menu-link"><div>Module Management</div></a></li>
                        <li class="menu-item {{ request()->routeIs('admin.role-permissions.*') ? 'active' : '' }}"><a href="{{ route('admin.role-permissions.index') }}" class="menu-link"><div>Role & Permission</div></a></li>
                        <li class="menu-item {{ request()->routeIs('admin.role-accounts.*') && request()->route('role') === 'hr' ? 'active' : '' }}"><a href="{{ route('admin.role-accounts.index', 'hr') }}" class="menu-link"><div>HR Management</div></a></li>
                        <li class="menu-item {{ request()->routeIs('admin.role-accounts.*') && request()->route('role') === 'manager' ? 'active' : '' }}"><a href="{{ route('admin.role-accounts.index', 'manager') }}" class="menu-link"><div>Manager Management</div></a></li>
                    </ul>
                </li>
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

                  <div class="nav-item me-3">
                       <li class="nav-item dropdown" title="New notifications">
                        <a class="nav-link header-icon-box notification-bell {{ $navbarUnreadCount > 0 ? 'has-unread' : '' }}" href="#" id="navbarDropdown"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bell f-16 text-dark-grey"></i>
                            @if($navbarUnreadCount > 0)
                                <span class="badge bg-danger" id="navbarNotificationCount">{{ $navbarUnreadCount }}</span>
                            @endif
                        </a>

                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown border-0 shadow-lg py-0"
                                aria-labelledby="navbarDropdown">

                                <li class="notification-dropdown-head d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-0 fw-bold">Notifications</p>
                                        <small class="text-muted" id="navbarNotificationUnreadText">{{ $navbarUnreadCount }} unread</small>
                                    </div>
                                    <form method="POST" action="{{ route('notifications.readAll') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">Mark all read</button>
                                    </form>
                                </li>

                                <li class="notification-dropdown-body">
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
                            @endphp

    <a href="{{ route('notifications.open', $notification->id) }}"
       class="notification-card-link {{ $isUnread ? 'is-unread' : '' }}">
        <span class="notification-avatar-icon color-{{ $color }}">
            <i class="fas {{ $icon }}"></i>
        </span>
        <span>
            <span class="notification-title">{{ $title }}</span>
            @if($message)
                <span class="notification-message">{{ \Illuminate\Support\Str::limit($message, 92) }}</span>
            @endif
            <span class="notification-time"><i class="fas fa-clock"></i>{{ $notification->created_at->diffForHumans() }}</span>
        </span>
        @if($isUnread)
            <span class="notification-unread-dot"></span>
        @endif
    </a>
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
