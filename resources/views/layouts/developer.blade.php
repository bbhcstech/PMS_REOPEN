<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Developer Portal') - Bitroxia Developer Hub</title>

    <!-- Google Fonts & Boxicons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-base: #f4f6f9;
            --bg-surface: #ffffff;
            --bg-sidebar: #0f172a;
            --sidebar-border: #1e293b;
            --sidebar-text: #cbd5e1; /* Lighter high-contrast sidebar text */
            --sidebar-label: #94a3b8; /* Lighter clear label text */
            --sidebar-active-bg: rgba(16, 185, 129, 0.16);
            --sidebar-active-border: rgba(16, 185, 129, 0.35);
            --sidebar-active-text: #34d399; /* Bright green active link */
            --primary: #059669;
            --primary-vibrant: #10b981;
            --primary-hover: #047857;
            --primary-light: #ecfdf5;
            --primary-border: #a7f3d0;
            --slate-dark: #0f172a;
            --slate-heading: #1e293b;
            --slate-body: #334155;
            --slate-muted: #64748b;
            --slate-light: #f8fafc;
            --border-color: #cbd5e1;
            --border-subtle: #e2e8f0;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --shadow-xs: 0 1px 3px rgba(15, 23, 42, 0.04);
            --shadow-sm: 0 3px 8px rgba(15, 23, 42, 0.06);
            --shadow-md: 0 8px 20px -2px rgba(15, 23, 42, 0.09);
            --shadow-lg: 0 14px 32px -4px rgba(15, 23, 42, 0.14);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-base);
            color: var(--slate-body);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        /* SIDEBAR STYLES */
        .dev-sidebar {
            width: 270px;
            background: var(--bg-sidebar);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid var(--sidebar-border);
        }

        .dev-sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .dev-brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
            flex-shrink: 0;
        }

        .dev-brand-title {
            font-size: 16.5px;
            font-weight: 800;
            letter-spacing: -0.4px;
            color: #ffffff;
            line-height: 1.2;
        }

        .dev-brand-sub {
            font-size: 10.5px;
            color: #94a3b8; /* Lighter clear subtitle */
            font-weight: 700;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            margin-top: 2px;
        }

        .dev-nav-menu {
            padding: 20px 14px;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .dev-nav-label {
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.1px;
            color: var(--sidebar-label); /* Lighter category label */
            padding: 16px 12px 6px;
        }

        .dev-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            border-radius: var(--radius-md);
            color: var(--sidebar-text); /* Lighter text color */
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .dev-nav-item:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.09);
        }

        .dev-nav-item.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            font-weight: 700;
            border-color: var(--sidebar-active-border);
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.2);
        }

        .dev-nav-item i {
            font-size: 20px;
            transition: transform 0.2s ease;
        }

        .dev-nav-item:hover i {
            transform: translateX(3px);
        }

        .dev-sidebar-footer {
            padding: 18px 14px;
            border-top: 1px solid var(--sidebar-border);
        }

        /* MAIN CONTENT AREA */
        .dev-main-wrapper {
            margin-left: 270px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: margin-left 0.3s ease;
        }

        /* TOPBAR STYLES */
        .dev-topbar {
            height: 72px;
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: var(--shadow-xs);
        }

        .dev-topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .toggle-sidebar-btn {
            display: none;
            background: none;
            border: none;
            font-size: 26px;
            color: var(--slate-dark);
            cursor: pointer;
        }

        .page-header-title {
            font-size: 19.5px;
            font-weight: 800;
            color: var(--slate-heading);
            letter-spacing: -0.4px;
        }

        .dev-topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* USER PROFILE DROPDOWN */
        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px 10px 4px 6px;
            border-radius: 30px;
            transition: background 0.2s;
            position: relative;
        }

        .user-profile-btn:hover {
            background: #f1f5f9;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #059669, #047857);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 15px;
            border: 2px solid #ffffff;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            flex-shrink: 0;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-meta {
            text-align: left;
        }

        .user-name {
            font-size: 14px;
            font-weight: 800;
            color: var(--slate-heading);
            display: block;
            line-height: 1.2;
        }

        .user-role-tag {
            font-size: 11.5px;
            color: var(--primary);
            font-weight: 700;
        }

        .dropdown-menu-box {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            width: 220px;
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            display: none;
            flex-direction: column;
            padding: 8px;
            z-index: 200;
        }

        .dropdown-menu-box.show {
            display: flex;
        }

        .dropdown-item-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--slate-body);
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: all 0.15s ease;
        }

        .dropdown-item-link:hover {
            background: #f1f5f9;
            color: var(--slate-dark);
        }

        .dropdown-item-link i {
            font-size: 18px;
            color: var(--slate-muted);
        }

        /* PAGE BODY CONTENT */
        .dev-body-content {
            padding: 32px;
            flex: 1;
        }

        /* CARD CONTAINERS */
        .dev-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xs);
            padding: 24px;
            margin-bottom: 24px;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }

        /* FORM CONTROLS PREMIUM STYLING */
        input[type="text"], input[type="email"], input[type="password"], select, textarea {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, select:focus, textarea:focus {
            border-color: var(--primary-vibrant) !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
            outline: none !important;
        }

        /* BADGES */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-in_progress { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .status-assigned { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .status-completed { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .status-on_hold { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
        .status-overdue { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        /* ALERTS */
        .alert-box {
            padding: 14px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-xs);
        }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        /* RESPONSIVE DESIGN */
        @media (max-width: 992px) {
            .dev-sidebar { transform: translateX(-100%); }
            .dev-sidebar.open { transform: translateX(0); }
            .dev-main-wrapper { margin-left: 0; }
            .toggle-sidebar-btn { display: block; }
            .dev-topbar { padding: 0 16px; }
            .dev-body-content { padding: 20px 16px; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- SIDEBAR NAVIGATION -->
    <aside class="dev-sidebar" id="devSidebar">
        <div class="dev-sidebar-brand">
            <div class="dev-brand-icon" style="background: transparent; box-shadow: none; width: 40px; height: 40px; border-radius: 50%; overflow: hidden;">
                <img src="{{ asset('logos/bitroxia_logo.png') }}" alt="Bitroxia" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div>
                <div class="dev-brand-title" style="font-size: 16px; font-weight: 800; color: #ffffff;">Bitroxia Developer Hub</div>
                <span class="dev-brand-sub">DEVELOPER WORKSPACE</span>
            </div>
        </div>

        <nav class="dev-nav-menu">
            <div class="dev-nav-label">MAIN WORKSPACE</div>

            <a href="{{ route('developer.dashboard') }}" class="dev-nav-item {{ request()->routeIs('developer.dashboard') ? 'active' : '' }}">
                <i class="bx bx-grid-alt"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('developer.my-work') }}" class="dev-nav-item {{ request()->routeIs('developer.my-work') ? 'active' : '' }}">
                <i class="bx bx-check-square"></i>
                <span>My Work</span>
            </a>

            <a href="{{ route('developer.my-contributions') }}" class="dev-nav-item {{ request()->routeIs('developer.my-contributions') ? 'active' : '' }}">
                <i class="bx bx-medal"></i>
                <span>My Contributions</span>
            </a>

            <a href="{{ route('developer.deadlines') }}" class="dev-nav-item {{ request()->routeIs('developer.deadlines') ? 'active' : '' }}">
                <i class="bx bx-time-five"></i>
                <span>Deadlines</span>
            </a>

            <div class="dev-nav-label">ACCOUNT & SYSTEM</div>

            <a href="{{ route('developer.notifications') }}" class="dev-nav-item {{ request()->routeIs('developer.notifications') ? 'active' : '' }}">
                <i class="bx bx-bell"></i>
                <span>Notifications</span>
            </a>

            <a href="{{ route('developer.profile') }}" class="dev-nav-item {{ request()->routeIs('developer.profile') ? 'active' : '' }}">
                <i class="bx bx-user-circle"></i>
                <span>Profile</span>
            </a>

            <a href="{{ route('developer.settings') }}" class="dev-nav-item {{ request()->routeIs('developer.settings') ? 'active' : '' }}">
                <i class="bx bx-cog"></i>
                <span>Settings</span>
            </a>
        </nav>

        <div class="dev-sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dev-nav-item" style="width: 100%; border: none; background: none; cursor: pointer; color: #f87171;">
                    <i class="bx bx-log-out" style="color: #f87171;"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="dev-main-wrapper">
        @if(session('superadmin_preview_active'))
        <!-- SUPER ADMIN PREVIEW MODE BANNER -->
        <div style="background: linear-gradient(90deg, #1e1b4b 0%, #312e81 100%); color: #ffffff; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.2); flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 13.5px;">
                <span style="background: #e0e7ff; color: #3730a3; padding: 3px 10px; border-radius: 12px; font-size: 11.5px; text-transform: uppercase;">🔐 SUPER ADMIN PREVIEW MODE</span>
                <span>You are viewing this developer workspace as Super Admin for <strong>{{ \App\Models\User::find(session('superadmin_preview_dev_id'))?->name ?? 'Developer' }}</strong></span>
            </div>
            <form method="POST" action="{{ route('super-admin.developers.exit-workspace') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="padding: 6px 14px; border-radius: 6px; background: #ef4444; color: #ffffff; border: none; font-weight: 700; font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i class="bx bx-log-out" style="font-size: 16px;"></i> Exit Workspace
                </button>
            </form>
        </div>
        @endif

        <!-- TOPBAR -->
        <header class="dev-topbar">
            <div class="dev-topbar-left">
                <button class="toggle-sidebar-btn" onclick="document.getElementById('devSidebar').classList.toggle('open')">
                    <i class="bx bx-menu"></i>
                </button>
                <div class="page-header-title">@yield('page_title', 'Developer Workspace')</div>
            </div>

            <div class="dev-topbar-right">
                <!-- USER DROPDOWN -->
                <div style="position: relative;">
                    <button class="user-profile-btn" onclick="toggleUserDropdown(event)">
                        <div class="user-avatar">
                            @if(!empty(Auth::user()->profile_image) && file_exists(public_path(Auth::user()->profile_image)))
                                <img src="{{ asset(Auth::user()->profile_image) }}" alt="{{ Auth::user()->name }}">
                            @else
                                {{ strtoupper(substr(Auth::user()->name ?? 'Dev', 0, 2)) }}
                            @endif
                        </div>
                        <div class="user-meta">
                            <span class="user-name">{{ Auth::user()->name }}</span>
                            <span class="user-role-tag">{{ ucfirst(Auth::user()->designation ?? 'Developer') }}</span>
                        </div>
                        <i class="bx bx-chevron-down" style="color: var(--slate-muted); font-size: 18px;"></i>
                    </button>

                    <div class="dropdown-menu-box" id="userDropdownMenu">
                        <a href="{{ route('developer.profile') }}" class="dropdown-item-link">
                            <i class="bx bx-user"></i> My Profile
                        </a>
                        <a href="{{ route('developer.settings') }}" class="dropdown-item-link">
                            <i class="bx bx-cog"></i> Settings
                        </a>
                        <div style="height: 1px; background: var(--border-color); margin: 4px 0;"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item-link" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer; color: #dc2626;">
                                <i class="bx bx-log-out" style="color: #dc2626;"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- CONTENT BODY -->
        <main class="dev-body-content">
            @if(session('success'))
                <div class="alert-box alert-success">
                    <i class="bx bx-check-circle" style="font-size: 22px;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class="alert-box alert-danger">
                    <i class="bx bx-error-circle" style="font-size: 22px;"></i>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleUserDropdown(e) {
            e.stopPropagation();
            const menu = document.getElementById('userDropdownMenu');
            menu.classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('userDropdownMenu');
            if (menu && menu.classList.contains('show')) {
                menu.classList.remove('show');
            }
        });
    </script>
    @yield('scripts')
</body>
</html>

