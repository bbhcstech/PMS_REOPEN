
@extends('admin.layout.app')

@section('title', 'Employee Details')

@section('content')
    {{-- ENTERPRISE SAAS DESIGN SYSTEM — EMPLOYEE DETAILS --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        /* =============================================
               BRAND DESIGN SYSTEM & VARIABLES
               ============================================= */
        :root {
            /* Core Palette - Matching Listing Page */
            --primary: #0f744c;
            --secondary: #188b5e;
            --accent: #22c55e;
            --mint: #d1fae5;
            --text-main: #07130d;
            --text-muted: #52645a;
            --bg-base: #f7fbf9;
            --surface: #ffffff;

            /* Gradients */
            --grad-cta: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 40%, var(--accent) 80%, var(--mint) 100%);
            --grad-bg: linear-gradient(135deg, rgba(15, 116, 76, 0.12) 0%, rgba(24, 139, 94, 0.12) 40%, rgba(34, 197, 94, 0.12) 80%, rgba(209, 250, 229, 0.2) 100%);
            --grad-bg-hover: linear-gradient(135deg, rgba(15, 116, 76, 0.2) 0%, rgba(24, 139, 94, 0.2) 40%, rgba(34, 197, 94, 0.2) 80%, rgba(209, 250, 229, 0.3) 100%);

            /* Glassmorphism */
            --glass-bg: rgba(255, 255, 255, 0.92);
            --glass-border: rgba(255, 255, 255, 1);
            --shadow-soft: 0 10px 30px -10px rgba(15, 116, 76, 0.08);
            --shadow-hover: 0 20px 40px -12px rgba(15, 116, 76, 0.2);

            /* State Colors */
            --state-active-bg: rgba(34, 197, 94, 0.1);
            --state-active-text: #16a34a;
            --state-inactive-bg: rgba(100, 116, 139, 0.1);
            --state-inactive-text: #64748b;

            /* Motion */
            --spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            --smooth: cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        /* Ambient Animated Background */
        .ambient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -2;
            overflow: hidden;
            pointer-events: none;
            background: var(--bg-base);
        }

        .ambient-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100vh;
            background: var(--grad-bg);
            filter: blur(60px);
            opacity: 0.6;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            will-change: transform;
            animation: floatOrb 20s infinite alternate var(--smooth);
        }

        .orb-1 {
            width: 50vw;
            height: 50vw;
            background: var(--secondary);
            top: -20%;
            right: -10%;
        }

        .orb-2 {
            width: 40vw;
            height: 40vw;
            background: var(--accent);
            bottom: -10%;
            left: -10%;
            animation-delay: -5s;
        }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-50px, 30px) scale(1.1); }
        }

        .show-container {
            position: relative;
            z-index: 5;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* BACK BUTTON */
        .btn-header-enterprise {
            background: var(--surface);
            border: 1px solid rgba(15, 116, 76, 0.2);
            color: var(--primary);
            padding: 0.8rem 1.8rem;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.4s var(--spring);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
        }

        .btn-header-enterprise:hover {
            transform: translateX(-5px);
            background: var(--grad-bg);
            color: var(--secondary);
            border-color: var(--secondary);
            box-shadow: var(--shadow-hover);
        }

        /* CARD STYLES */
        .card-premium {
            background: var(--surface);
            border: 1px solid rgba(15, 116, 76, 0.12);
            border-radius: 24px;
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: all 0.4s var(--spring);
            margin-bottom: 2rem;
            opacity: 0;
            animation: slideUp 0.8s var(--spring) forwards;
        }

        .card-premium:hover {
            box-shadow: var(--shadow-hover);
            border-color: rgba(15, 116, 76, 0.2);
        }

        .card-premium:nth-child(1) { animation-delay: 0.1s; }
        .card-premium:nth-child(2) { animation-delay: 0.2s; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header-premium {
            background: var(--grad-bg);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header-cta {
            background: var(--grad-cta);
            background-size: 200% 200%;
            color: white;
            animation: gradientFlow 10s ease infinite;
        }

        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .card-header-cta h5 {
            color: white !important;
        }

        .card-header-premium h5 {
            margin: 0;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .card-body-premium {
            padding: 2.5rem;
        }

        /* AVATAR */
        .avatar-large {
            width: 140px;
            height: 140px;
            border-radius: 28px;
            background: var(--grad-cta);
            background-size: 200% 200%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border: 4px solid white;
            box-shadow: 0 15px 30px rgba(15, 116, 76, 0.2);
            overflow: hidden;
            animation: floatAvatar 6s ease-in-out infinite;
        }

        @keyframes floatAvatar {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-large i {
            font-size: 4rem;
            color: white;
        }

        /* BADGES */
        .status-badge {
            padding: 0.4rem 1.2rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: var(--state-active-bg);
            color: var(--state-active-text);
            position: relative;
        }

        .status-active::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 30px;
            border: 1px solid var(--accent);
            animation: pulseBorder 2s infinite;
            opacity: 0;
        }

        .status-inactive {
            background: var(--state-inactive-bg);
            color: var(--state-inactive-text);
            border: 1px solid rgba(100, 116, 139, 0.3);
        }

        @keyframes pulseBorder {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.1); opacity: 0; }
        }

        .designation-badge-large {
            padding: 0.6rem 1.8rem;
            border-radius: 40px;
            font-weight: 800;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(15, 116, 76, 0.1);
            color: var(--primary);
            border: 1px solid rgba(15, 116, 76, 0.2);
        }

        /* INFO GRID */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .info-item {
            padding: 1.5rem;
            background: var(--bg-base);
            border-radius: 20px;
            border: 1px solid rgba(15, 116, 76, 0.1);
            transition: all 0.3s var(--spring);
        }

        .info-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.03);
            border-color: var(--secondary);
            background: white;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label i {
            color: var(--secondary);
            font-size: 1.1rem;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary);
            word-break: break-word;
        }

        /* SMALL BADGES */
        .info-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .gender-badge {
            background: rgba(34, 197, 94, 0.1);
            color: var(--accent);
        }

        .mobile-badge {
            background: rgba(15, 116, 76, 0.1);
            color: var(--primary);
        }

        .employment-badge {
            background: rgba(24, 139, 94, 0.1);
            color: var(--secondary);
        }

        .skill-tag {
            background: rgba(15, 116, 76, 0.08);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin: 2px 4px 2px 0;
        }

        .toggle-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .toggle-status.active {
            color: #16a34a;
        }

        .toggle-status.inactive {
            color: #dc2626;
        }

        /* SECTION STYLES */
        .resident-info-section {
            margin-top: 2rem;
            padding: 1.5rem 2rem;
            border-radius: 20px;
            background: var(--grad-bg);
            border: 1px solid rgba(15, 116, 76, 0.2);
        }

        .section-label {
            font-size: 0.9rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
        }

        /* AUDIT GRID */
        .audit-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .audit-item {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            border: 1px solid rgba(100, 116, 139, 0.15);
            transition: all 0.3s;
        }

        .audit-item:hover {
            border-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        }

        .audit-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 0.4rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .audit-value {
            font-size: 1rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.2rem;
        }

        /* ACTION BUTTONS */
        .action-btn {
            width: 100%;
            padding: 1rem;
            border-radius: 16px;
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s var(--spring);
            border: none;
            margin-bottom: 1rem;
            text-decoration: none;
            cursor: pointer;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0));
            transition: left 0.6s ease;
            z-index: 1;
        }

        .action-btn:hover::before {
            left: 200%;
        }

        .action-btn:hover {
            transform: translateY(-4px);
            color: white;
        }

        .btn-edit {
            background: var(--grad-cta);
            background-size: 200% 200%;
            box-shadow: 0 10px 20px rgba(15, 116, 76, 0.2);
        }

        .btn-edit:hover {
            box-shadow: 0 15px 30px rgba(15, 116, 76, 0.3);
            background-position: 100% 50%;
        }

        .btn-print {
            background: var(--bg-base);
            color: var(--primary);
            border: 2px dashed rgba(15, 116, 76, 0.3);
        }

        .btn-print:hover {
            border-style: solid;
            border-color: var(--secondary);
            color: var(--secondary);
            background: white;
            transform: translateY(-2px);
        }

        .btn-print::before {
            display: none;
        }

        /* QUICK INFO LIST */
        .quick-info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .quick-info-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.05);
        }

        .quick-info-list li:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .quick-info-label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .quick-info-label i {
            color: var(--secondary);
            font-size: 1.1rem;
        }

        .quick-info-value {
            font-weight: 800;
            color: var(--primary);
        }

        @media (max-width: 992px) {
            .info-grid,
            .audit-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .show-container {
                padding: 1rem;
            }
            .card-body-premium {
                padding: 1.5rem;
            }
            .card-header-premium {
                padding: 1rem 1.5rem;
            }
            .info-item {
                padding: 1rem;
            }
            .avatar-large {
                width: 100px;
                height: 100px;
            }
            .avatar-large i {
                font-size: 2.5rem;
            }
        }

        /* DARK MODE — Enterprise Employee View */
        html[data-pms-theme="dark"] {
            --primary: #7af0b5;
            --secondary: #40d48c;
            --accent: #22c55e;
            --mint: #153f2d;
            --text-main: #f8fffb;
            --text-muted: #b9d8c7;
            --bg-base: #07110d;
            --surface: rgba(16, 33, 25, 0.96);
            --grad-cta: linear-gradient(135deg, #0f744c 0%, #188b5e 48%, #35c985 100%);
            --grad-bg: linear-gradient(135deg, rgba(122, 240, 181, 0.14) 0%, rgba(64, 212, 140, 0.1) 50%, rgba(255, 255, 255, 0.04) 100%);
            --grad-bg-hover: linear-gradient(135deg, rgba(122, 240, 181, 0.22) 0%, rgba(64, 212, 140, 0.16) 50%, rgba(255, 255, 255, 0.08) 100%);
            --glass-bg: rgba(16, 33, 25, 0.86);
            --glass-border: rgba(122, 240, 181, 0.18);
            --shadow-soft: 0 18px 38px rgba(0, 0, 0, 0.28);
            --shadow-hover: 0 26px 54px rgba(0, 0, 0, 0.38);
            --state-active-bg: rgba(122, 240, 181, 0.14);
            --state-active-text: #dfffee;
            --state-inactive-bg: rgba(255, 123, 123, 0.12);
            --state-inactive-text: #ffd5d5;
        }

        html[data-pms-theme="dark"] .ambient-bg {
            background: #07110d !important;
        }

        html[data-pms-theme="dark"] .ambient-bg::before {
            background:
                radial-gradient(circle at top right, rgba(122, 240, 181, 0.14), transparent 34rem),
                radial-gradient(circle at bottom left, rgba(64, 212, 140, 0.1), transparent 28rem),
                #07110d !important;
            opacity: 1;
            filter: blur(42px);
        }

        html[data-pms-theme="dark"] .orb {
            opacity: 0.16;
        }

        html[data-pms-theme="dark"] .show-container {
            color: var(--text-main) !important;
        }

        html[data-pms-theme="dark"] .card-premium,
        html[data-pms-theme="dark"] .info-item,
        html[data-pms-theme="dark"] .audit-item,
        html[data-pms-theme="dark"] .resident-info-section {
            background: rgba(16, 33, 25, 0.94) !important;
            border-color: rgba(122, 240, 181, 0.18) !important;
            color: var(--text-main) !important;
            box-shadow: var(--shadow-soft) !important;
        }

        html[data-pms-theme="dark"] .card-premium:hover,
        html[data-pms-theme="dark"] .info-item:hover,
        html[data-pms-theme="dark"] .audit-item:hover {
            background: rgba(20, 49, 36, 0.96) !important;
            border-color: rgba(122, 240, 181, 0.34) !important;
            box-shadow: var(--shadow-hover) !important;
        }

        html[data-pms-theme="dark"] .card-header-premium {
            background: linear-gradient(135deg, rgba(122, 240, 181, 0.14), rgba(255, 255, 255, 0.04)) !important;
            border-bottom-color: rgba(122, 240, 181, 0.16) !important;
        }

        html[data-pms-theme="dark"] .card-header-cta {
            background: var(--grad-cta) !important;
        }

        html[data-pms-theme="dark"] .card-header-premium h5,
        html[data-pms-theme="dark"] .section-label,
        html[data-pms-theme="dark"] .info-value,
        html[data-pms-theme="dark"] .audit-value,
        html[data-pms-theme="dark"] .quick-info-value {
            color: var(--text-main) !important;
            -webkit-text-fill-color: var(--text-main) !important;
        }

        html[data-pms-theme="dark"] .info-label,
        html[data-pms-theme="dark"] .audit-label,
        html[data-pms-theme="dark"] .quick-info-label,
        html[data-pms-theme="dark"] .text-muted,
        html[data-pms-theme="dark"] small {
            color: var(--text-muted) !important;
            -webkit-text-fill-color: var(--text-muted) !important;
        }

        html[data-pms-theme="dark"] .show-container i,
        html[data-pms-theme="dark"] .info-label i,
        html[data-pms-theme="dark"] .section-label i,
        html[data-pms-theme="dark"] .quick-info-label i,
        html[data-pms-theme="dark"] .audit-label i {
            color: var(--primary) !important;
            -webkit-text-fill-color: var(--primary) !important;
        }

        html[data-pms-theme="dark"] .card-header-cta,
        html[data-pms-theme="dark"] .card-header-cta h5,
        html[data-pms-theme="dark"] .card-header-cta i,
        html[data-pms-theme="dark"] .btn-edit,
        html[data-pms-theme="dark"] .btn-edit i {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        html[data-pms-theme="dark"] .avatar-large {
            border-color: rgba(255, 255, 255, 0.78) !important;
            box-shadow: 0 18px 36px rgba(64, 212, 140, 0.22) !important;
        }

        html[data-pms-theme="dark"] .avatar-large i {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        html[data-pms-theme="dark"] .designation-badge-large,
        html[data-pms-theme="dark"] .status-badge,
        html[data-pms-theme="dark"] .info-badge,
        html[data-pms-theme="dark"] .skill-tag,
        html[data-pms-theme="dark"] .toggle-status {
            background: rgba(122, 240, 181, 0.13) !important;
            border: 1px solid rgba(122, 240, 181, 0.26) !important;
            color: #dfffee !important;
            -webkit-text-fill-color: #dfffee !important;
        }

        html[data-pms-theme="dark"] .status-inactive,
        html[data-pms-theme="dark"] .toggle-status.inactive {
            background: rgba(255, 123, 123, 0.12) !important;
            border-color: rgba(255, 123, 123, 0.26) !important;
            color: #ffd5d5 !important;
            -webkit-text-fill-color: #ffd5d5 !important;
        }

        html[data-pms-theme="dark"] .designation-badge-large i,
        html[data-pms-theme="dark"] .status-badge i,
        html[data-pms-theme="dark"] .info-badge i,
        html[data-pms-theme="dark"] .toggle-status i {
            color: inherit !important;
            -webkit-text-fill-color: inherit !important;
        }

        html[data-pms-theme="dark"] .btn-header-enterprise,
        html[data-pms-theme="dark"] .btn-print {
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: rgba(122, 240, 181, 0.24) !important;
            color: var(--text-main) !important;
            -webkit-text-fill-color: var(--text-main) !important;
            box-shadow: var(--shadow-soft) !important;
        }

        html[data-pms-theme="dark"] .btn-header-enterprise:hover,
        html[data-pms-theme="dark"] .btn-print:hover {
            background: var(--grad-bg-hover) !important;
            border-color: rgba(122, 240, 181, 0.42) !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        html[data-pms-theme="dark"] .btn-header-enterprise i,
        html[data-pms-theme="dark"] .btn-print i {
            color: inherit !important;
            -webkit-text-fill-color: inherit !important;
        }

        html[data-pms-theme="dark"] .quick-info-list li {
            border-bottom-color: rgba(122, 240, 181, 0.16) !important;
        }

        /* PRINT STYLES */
        @media print {
            .btn-header-enterprise,
            .card-premium:last-child,
            .action-btn,
            footer,
            nav,
            .ambient-bg,
            .orb {
                display: none !important;
            }
            .show-container {
                padding: 0 !important;
                max-width: 100% !important;
            }
            .card-premium {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                break-inside: avoid;
            }
            .col-lg-8, .col-lg-4 {
                width: 100% !important;
                max-width: 100% !important;
            }
            .row {
                display: block !important;
            }
            .info-grid {
                break-inside: avoid;
            }
            .avatar-large {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>

    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <div class="show-container">
        <a href="{{ route('employees.index') }}" class="btn-header-enterprise">
            <i class="fas fa-arrow-left"></i>
            BACK TO EMPLOYEE LIST
        </a>

        @php
            $detail = $employee->employeeDetail;
            $status = $detail?->status ?? 'N/A';
            $statusLower = strtolower($status);
            $imagePath = null;
            if (!empty($employee->profile_image)) {
                if (file_exists(public_path($employee->profile_image))) {
                    $imagePath = asset($employee->profile_image);
                }
            }
        @endphp

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card-premium">
                    <div class="card-header-premium card-header-cta">
                        <h5><i class="fas fa-user-circle"></i> Employee Profile</h5>
                        <span class="status-badge {{ $statusLower == 'active' ? 'status-active' : 'status-inactive' }}">
                            <i class="fas fa-{{ $statusLower == 'active' ? 'check-circle' : 'times-circle' }}"></i>
                            {{ $status }}
                        </span>
                    </div>
                    <div class="card-body-premium">
                        <div class="text-center">
                            <div class="avatar-large">
                                @if ($imagePath)
                                    <img src="{{ $imagePath }}" alt="{{ $employee->name }}">
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                            </div>
                            <h3 style="font-weight: 800; color: var(--primary); margin-bottom: 0.8rem; font-size: 2rem; letter-spacing: -0.5px;">
                                {{ $employee->name }}
                            </h3>
                            <div class="d-flex justify-content-center align-items-center flex-wrap gap-3">
                                <span class="designation-badge-large">
                                    <i class="fas fa-briefcase"></i> {{ $detail?->designation?->name ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-id-card"></i> Employee ID</div>
                                <div class="info-value">{{ $detail?->employee_id ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-envelope"></i> Email Address</div>
                                <div class="info-value">{{ $employee->email ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-calendar-alt"></i> Date of Birth</div>
                                <div class="info-value">{{ $detail?->dob ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-venus-mars"></i> Gender</div>
                                <div class="info-value">
                                    @if ($detail?->gender)
                                        <span class="info-badge gender-badge">
                                            <i class="fas fa-{{ $detail->gender == 'Male' ? 'mars' : ($detail->gender == 'Female' ? 'venus' : 'genderless') }}"></i>
                                            {{ $detail->gender }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-heart"></i> Marital Status</div>
                                <div class="info-value">{{ ucfirst($detail?->marital_status ?? 'N/A') }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-mobile-alt"></i> Mobile Number</div>
                                <div class="info-value">
                                    @if ($detail?->mobile)
                                        <span class="info-badge mobile-badge">
                                            <i class="fas fa-phone-alt"></i> {{ $detail->mobile }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-language"></i> Language</div>
                                <div class="info-value">{{ $detail?->language ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-building"></i> Department</div>
                                <div class="info-value">{{ $detail?->department->dpt_name ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-user-tag"></i> Designation</div>
                                <div class="info-value">{{ $detail?->designation->name ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-clock"></i> Employment Type</div>
                                <div class="info-value">
                                    <span class="info-badge employment-badge">
                                        {{ ucfirst(str_replace('_', ' ', $detail?->employment_type ?? 'N/A')) }}
                                    </span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-user-friends"></i> Reporting To</div>
                                <div class="info-value">{{ $detail?->reportingTo->name ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-dollar-sign"></i> Hourly Rate</div>
                                <div class="info-value">{{ $detail?->hourly_rate ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="resident-info-section">
                            <div class="section-label"><i class="fas fa-map-marker-alt"></i> Location Information</div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-4">
                                    <div class="info-label mb-1" style="font-size: 0.65rem;">Country</div>
                                    <div class="info-value" style="font-size: 0.95rem;">{{ $detail?->country ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-label mb-1" style="font-size: 0.65rem;">Address</div>
                                    <div class="info-value" style="font-size: 0.95rem;">{{ $detail?->address ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-label mb-1" style="font-size: 0.65rem;">Business Address</div>
                                    <div class="info-value" style="font-size: 0.95rem;">{{ $detail?->business_address ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>

                        @if($detail?->skills)
                        <div class="resident-info-section" style="margin-top: 1.5rem;">
                            <div class="section-label"><i class="fas fa-code"></i> Skills & Expertise</div>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach(explode(',', $detail->skills) as $skill)
                                    <span class="skill-tag">{{ trim($skill) }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="resident-info-section" style="margin-top: 0;">
                                    <div class="section-label"><i class="fas fa-lock"></i> Login Allowed</div>
                                    <div class="info-value">
                                        <span class="toggle-status {{ ($detail?->login_allowed ?? false) ? 'active' : 'inactive' }}">
                                            <i class="fas fa-{{ ($detail?->login_allowed ?? false) ? 'check-circle' : 'times-circle' }}"></i>
                                            {{ ($detail?->login_allowed ?? false) ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="resident-info-section" style="margin-top: 0;">
                                    <div class="section-label"><i class="fas fa-bell"></i> Email Notifications</div>
                                    <div class="info-value">
                                        <span class="toggle-status {{ ($detail?->email_notifications ?? false) ? 'active' : 'inactive' }}">
                                            <i class="fas fa-{{ ($detail?->email_notifications ?? false) ? 'check-circle' : 'times-circle' }}"></i>
                                            {{ ($detail?->email_notifications ?? false) ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-premium">
                    <div class="card-header-premium">
                        <h5><i class="fas fa-history"></i> System Log & Audit Trail</h5>
                    </div>
                    <div class="card-body-premium">
                        <div class="audit-grid">
                            <div class="audit-item">
                                <div class="audit-label"><i class="fas fa-user-plus me-1 text-secondary"></i> Created By</div>
                                <div class="audit-value">{{ $employee->creator?->name ?? 'System' }}</div>
                                <small class="text-muted fw-medium">{{ $employee->created_at ? $employee->created_at->format('d M Y, h:i A') : '-' }}</small>
                            </div>
                            <div class="audit-item">
                                <div class="audit-label"><i class="fas fa-user-edit me-1 text-secondary"></i> Last Updated By</div>
                                <div class="audit-value">{{ $employee->updater?->name ?? 'System' }}</div>
                                <small class="text-muted fw-medium">{{ $employee->updated_at ? $employee->updated_at->format('d M Y, h:i A') : '-' }}</small>
                            </div>
                            <div class="audit-item">
                                <div class="audit-label"><i class="fas fa-calendar-check me-1 text-secondary"></i> Joined Date</div>
                                <div class="audit-value">{{ $detail?->joining_date ? \Carbon\Carbon::parse($detail->joining_date)->format('d M, Y') : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-premium">
                    <div class="card-header-premium">
                        <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body-premium" style="padding: 2rem;">
                        @php
                            $role = strtolower(auth()->user()->roleMaster->role_name ?? (auth()->user()->role ?? ''));
                        @endphp

                        <a href="{{ route('employees.edit', $employee->id) }}" class="action-btn btn-edit">
                            <i class="fas fa-pen-nib"></i> Edit Employee Profile
                        </a>

                        <button onclick="window.print()" class="action-btn btn-print">
                            <i class="fas fa-print"></i> Print Profile
                        </button>
                    </div>
                </div>

                <div class="card-premium">
                    <div class="card-header-premium" style="background: var(--bg-base); border-bottom-color: rgba(15, 116, 76, 0.15);">
                        <h5 style="color: var(--secondary);"><i class="fas fa-info-circle"></i> Quick Overview</h5>
                    </div>
                    <div class="card-body-premium" style="padding: 1.5rem 2rem;">
                        <ul class="quick-info-list">
                            <li>
                                <div class="quick-info-label"><i class="fas fa-id-card"></i> Employee ID</div>
                                <div class="quick-info-value">#{{ $detail?->employee_id ?? 'N/A' }}</div>
                            </li>
                            <li>
                                <div class="quick-info-label"><i class="fas fa-calendar-alt"></i> Joined Date</div>
                                <div class="quick-info-value">{{ $detail?->joining_date ? \Carbon\Carbon::parse($detail->joining_date)->format('d M, Y') : 'N/A' }}</div>
                            </li>
                            <li>
                                <div class="quick-info-label"><i class="fas fa-mobile-alt"></i> Mobile Verified</div>
                                <div class="quick-info-value">
                                    @if($detail?->mobile)
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i>
                                    @endif
                                </div>
                            </li>
                            <li>
                                <div class="quick-info-label"><i class="fas fa-envelope"></i> Email Verified</div>
                                <div class="quick-info-value">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                            </li>
                            <li>
                                <div class="quick-info-label"><i class="fas fa-briefcase"></i> Employment Type</div>
                                <div class="quick-info-value">{{ ucfirst(str_replace('_', ' ', $detail?->employment_type ?? 'N/A')) }}</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush
