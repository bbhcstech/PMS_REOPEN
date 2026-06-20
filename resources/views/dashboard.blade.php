<!-- //dashboard blade page -->


@extends('admin.layout.app')

@section('title', 'Admin Dashboard')

@section('content')

<style>
    /* ===== CSS Variables & Base Styles ===== */
    :root {
        --primary: #7C3AED;
        --primary-light: #8B5CF6;
        --primary-dark: #6D28D9;
        --secondary: #F59E0B;
        --success: #10B981;
        --danger: #EF4444;
        --warning: #F59E0B;
        --info: #3B82F6;
        --dark: #1F2937;
        --light: #F9FAFB;
        --gray: #6B7280;
        --gray-light: #E5E7EB;

        --gradient-primary: linear-gradient(135deg, #7C3AED 0%, #8B5CF6 100%);
        --gradient-success: linear-gradient(135deg, #10B981 0%, #34D399 100%);
        --gradient-warning: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
        --gradient-danger: linear-gradient(135deg, #EF4444 0%, #F87171 100%);
        --gradient-info: linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%);

        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.07);
        --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.12);
        --shadow-xl: 0 20px 60px rgba(0, 0, 0, 0.15);

        --radius-sm: 10px;
        --radius-md: 16px;
        --radius-lg: 24px;

        --transition-fast: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-base: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: 0.5s cubic-bezier(0.4, 0, 0.2, 1);

        /* Font Sizes - Enhanced Readability */
        --font-xs: 0.75rem;    /* 12px */
        --font-sm: 0.875rem;   /* 14px */
        --font-base: 1rem;     /* 16px */
        --font-lg: 1.125rem;   /* 18px */
        --font-xl: 1.25rem;    /* 20px */
        --font-2xl: 1.5rem;    /* 24px */
        --font-3xl: 1.875rem;  /* 30px */
        --font-4xl: 2.25rem;   /* 36px */
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 15%, #f7fafc 100%);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-attachment: fixed;
        font-size: var(--font-base);
        line-height: 1.6;
    }

    /* ===== Animations ===== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        33% {
            transform: translateY(-10px) rotate(2deg);
        }
        66% {
            transform: translateY(5px) rotate(-2deg);
        }
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -200% center;
        }
        100% {
            background-position: 200% center;
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
            transform: scale(0.3) translateY(20px);
        }
        50% {
            opacity: 0.9;
            transform: scale(1.05);
        }
        80% {
            opacity: 1;
            transform: scale(0.95);
        }
        100% {
            opacity: 1;
            transform: scale(1);
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

    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }

    @keyframes shine {
        0% {
            left: -100%;
        }
        20% {
            left: 100%;
        }
        100% {
            left: 100%;
        }
    }

    /* ===== Main Container ===== */
    #main {
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    .content-wrapper {
        padding: 2rem 2.5rem;
        max-width: 100%;
        position: relative;
        z-index: 1;
        animation: fadeInUp 0.8s ease-out;
    }

    /* ===== Floating Background Elements ===== */
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
        opacity: 0.3;
        filter: blur(40px);
        animation: float 20s infinite ease-in-out;
    }

    .floating-element:nth-child(1) {
        width: 400px;
        height: 400px;
        background: var(--primary);
        top: 10%;
        left: 5%;
        animation-delay: 0s;
    }

    .floating-element:nth-child(2) {
        width: 300px;
        height: 300px;
        background: var(--success);
        bottom: 20%;
        right: 10%;
        animation-delay: 5s;
    }

    .floating-element:nth-child(3) {
        width: 200px;
        height: 200px;
        background: var(--warning);
        top: 40%;
        right: 20%;
        animation-delay: 10s;
    }

    /* ===== Dashboard Tabs ===== */
    #dashboardTabs {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-lg);
        padding: 0.5rem;
        margin-bottom: 2.5rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
        animation: slideInLeft 0.6s ease-out;
    }

    #dashboardTabs::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--secondary), var(--success), var(--info));
        background-size: 400% 400%;
        animation: gradientShift 8s ease infinite;
    }

    .nav-pills .nav-link {
        padding: 1.125rem 1.5rem;
        border-radius: var(--radius-sm);
        color: var(--dark);
        font-weight: 700;
        font-size: var(--font-base);
        transition: all var(--transition-base);
        position: relative;
        overflow: hidden;
        z-index: 1;
        border: 2px solid transparent;
        margin: 0 0.25rem;
        letter-spacing: 0.3px;
    }

    .nav-pills .nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.7s ease;
        z-index: -1;
    }

    .nav-pills .nav-link:hover::before {
        left: 100%;
    }

    .nav-pills .nav-link:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        background: rgba(124, 58, 237, 0.05);
        border-color: var(--primary-light);
    }

    .nav-pills .nav-link.active {
        background: var(--gradient-primary) !important;
        color: white !important;
        box-shadow: 0 10px 30px rgba(124, 58, 237, 0.3);
        transform: translateY(-2px);
        animation: pulse 3s infinite;
        border: 2px solid white;
    }

    .nav-pills .nav-link i {
        font-size: 1.35rem;
        margin-right: 0.75rem;
        transition: transform var(--transition-base);
    }

    .nav-pills .nav-link:hover i {
        transform: scale(1.2) rotate(10deg);
    }

    /* ===== Welcome Card ===== */
    .welcome-section {
        margin-bottom: 2.5rem;
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    .welcome-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--radius-lg);
        overflow: hidden;
        position: relative;
        box-shadow: var(--shadow-xl);
        transition: all var(--transition-base);
        min-height: 280px;
    }

    .welcome-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-xl), 0 30px 60px rgba(124, 58, 237, 0.1);
    }

    .welcome-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: var(--gradient-primary);
        background-size: 400% 400%;
        animation: gradientShift 6s ease infinite;
    }

    .welcome-content {
        padding: 2.5rem;
    }

    .welcome-title {
        font-size: var(--font-4xl);
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1.25rem;
        line-height: 1.2;
        letter-spacing: -0.5px;
    }

    .welcome-text {
        font-size: var(--font-lg);
        color: var(--dark);
        line-height: 1.7;
        max-width: 600px;
        margin-bottom: 1.75rem;
        font-weight: 450;
    }

    .welcome-badges {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .welcome-badge {
        padding: 0.6rem 1.35rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: var(--font-sm);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        transition: all var(--transition-fast);
    }

    .welcome-badge:nth-child(1) {
        background: rgba(124, 58, 237, 0.12);
        color: var(--primary);
        border: 2px solid rgba(124, 58, 237, 0.25);
    }

    .welcome-badge:nth-child(2) {
        background: rgba(16, 185, 129, 0.12);
        color: var(--success);
        border: 2px solid rgba(16, 185, 129, 0.25);
    }

    .welcome-badge:nth-child(3) {
        background: rgba(245, 158, 11, 0.12);
        color: var(--warning);
        border: 2px solid rgba(245, 158, 11, 0.25);
    }

    .welcome-badge:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .welcome-badge i {
        font-size: var(--font-lg);
    }

    .welcome-illustration {
        padding: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        height: 100%;
    }

    .welcome-illustration::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(124, 58, 237, 0.1) 0%, rgba(124, 58, 237, 0) 70%);
        border-radius: 50%;
        animation: pulse 4s infinite;
    }

    .welcome-illustration img {
        animation: float 6s infinite ease-in-out;
        filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.15));
        max-height: 200px;
        position: relative;
        z-index: 1;
    }

    /* ===== Statistics Cards ===== */
    .stats-section {
        margin-bottom: 2.5rem;
        animation: fadeInUp 0.8s ease-out 0.3s both;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }

    .stat-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
        transition: all var(--transition-base);
        box-shadow: var(--shadow-md);
        min-height: 220px;
        display: flex;
        flex-direction: column;
    }

    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: var(--shadow-xl);
        border-color: rgba(124, 58, 237, 0.3);
    }

    .stat-card:nth-child(1):hover { border-color: rgba(124, 58, 237, 0.3); }
    .stat-card:nth-child(2):hover { border-color: rgba(16, 185, 129, 0.3); }
    .stat-card:nth-child(3):hover { border-color: rgba(245, 158, 11, 0.3); }
    .stat-card:nth-child(4):hover { border-color: rgba(239, 68, 68, 0.3); }

    .stat-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
        opacity: 0;
        transition: opacity var(--transition-base);
    }

    .stat-card:hover::after {
        opacity: 1;
    }

    .stat-card:nth-child(1) .stat-icon { background: var(--gradient-primary); }
    .stat-card:nth-child(2) .stat-icon { background: var(--gradient-success); }
    .stat-card:nth-child(3) .stat-icon { background: var(--gradient-warning); }
    .stat-card:nth-child(4) .stat-icon { background: var(--gradient-danger); }

    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }

    .stat-icon {
        width: 65px;
        height: 65px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        font-size: 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .stat-icon::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transform: rotate(45deg);
        transition: all var(--transition-base);
    }

    .stat-card:hover .stat-icon::before {
        left: 100%;
    }

    .stat-dropdown .btn {
        color: var(--gray);
        transition: all var(--transition-fast);
        font-size: var(--font-lg);
    }

    .stat-dropdown .btn:hover {
        color: var(--primary);
        transform: rotate(90deg);
    }

    .stat-title {
        font-size: var(--font-sm);
        color: var(--gray);
        text-transform: uppercase;
        letter-spacing: 1.2px;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stat-title a {
        color: inherit;
        text-decoration: none;
        transition: color var(--transition-fast);
    }

    .stat-title a:hover {
        color: var(--primary);
    }

    .stat-value {
        font-size: var(--font-3xl);
        font-weight: 800;
        color: var(--dark);
        line-height: 1.1;
        margin-bottom: 0.75rem;
        background: linear-gradient(135deg, var(--dark) 0%, #4B5563 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.5px;
    }

    .stat-progress {
        margin-top: auto;
        padding-top: 0.75rem;
    }

    .progress-container {
        height: 8px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .progress-bar {
        height: 100%;
        border-radius: 4px;
        animation: progressFill 1.5s ease-out;
        position: relative;
        overflow: hidden;
    }

    .stat-card:nth-child(1) .progress-bar { background: var(--gradient-primary); }
    .stat-card:nth-child(2) .progress-bar { background: var(--gradient-success); }
    .stat-card:nth-child(3) .progress-bar { background: var(--gradient-warning); }
    .stat-card:nth-child(4) .progress-bar { background: var(--gradient-danger); }

    .progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        animation: shimmer 2s infinite;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: var(--font-sm);
        font-weight: 600;
        color: var(--dark);
    }

    .stat-trend.positive {
        color: var(--success);
    }

    .stat-trend i {
        font-size: var(--font-base);
    }

    /* ===== Content Cards Section ===== */
    .content-section {
        animation: fadeInUp 0.8s ease-out 0.4s both;
    }

    .content-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }

    .content-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: all var(--transition-base);
        box-shadow: var(--shadow-md);
        display: flex;
        flex-direction: column;
        height: 500px;
    }

    .content-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-xl);
        border-color: rgba(124, 58, 237, 0.2);
    }

    .content-card:nth-child(1) { animation: slideInRight 0.6s ease-out 0.1s both; }
    .content-card:nth-child(2) { animation: slideInRight 0.6s ease-out 0.2s both; }
    .content-card:nth-child(3) { animation: slideInRight 0.6s ease-out 0.3s both; }

    .card-header {
        padding: 1.5rem 1.75rem;
        background: rgba(124, 58, 237, 0.05);
        border-bottom: 1px solid rgba(124, 58, 237, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .card-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: var(--gradient-primary);
    }

    .card-title {
        font-size: var(--font-xl);
        font-weight: 700;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        letter-spacing: -0.3px;
    }

    .card-title i {
        color: var(--primary);
        font-size: 1.65rem;
    }

    .card-action {
        padding: 0.6rem 1.35rem;
        background: var(--gradient-primary);
        color: white;
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-weight: 700;
        font-size: var(--font-sm);
        transition: all var(--transition-base);
        box-shadow: 0 4px 15px rgba(124, 58, 237, 0.2);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        letter-spacing: 0.3px;
    }

    .card-action:hover {
        transform: translateX(5px) scale(1.05);
        box-shadow: 0 6px 25px rgba(124, 58, 237, 0.3);
        color: white;
    }

    .card-body {
        padding: 1.5rem;
        flex: 1;
        overflow-y: auto;
    }

    .card-body::-webkit-scrollbar {
        width: 6px;
    }

    .card-body::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 3px;
    }

    .card-body::-webkit-scrollbar-thumb {
        background: var(--gradient-primary);
        border-radius: 3px;
    }

    /* ===== List Items ===== */
    .list-item {
        padding: 1.25rem;
        border-bottom: 1px solid rgba(124, 58, 237, 0.1);
        transition: all var(--transition-base);
        border-radius: var(--radius-sm);
        margin-bottom: 0.75rem;
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.5);
    }

    .list-item:hover {
        background: rgba(124, 58, 237, 0.05);
        transform: translateX(8px);
        box-shadow: var(--shadow-sm);
    }

    .list-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--gradient-primary);
        opacity: 0;
        transition: opacity var(--transition-base);
    }

    .list-item:hover::before {
        opacity: 1;
    }

    .list-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .list-item-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .list-item-title {
        font-weight: 700;
        color: var(--dark);
        font-size: var(--font-base);
        margin-bottom: 0.5rem;
        transition: color var(--transition-fast);
        line-height: 1.5;
    }

    .list-item:hover .list-item-title {
        color: var(--primary);
    }

    .list-item-meta {
        font-size: var(--font-sm);
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        font-weight: 500;
    }

    .list-item-meta span {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .list-item-meta i {
        font-size: var(--font-base);
        color: var(--primary);
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: var(--font-xs);
        letter-spacing: 0.8px;
        text-transform: uppercase;
        white-space: nowrap;
        transition: all var(--transition-fast);
        border: 2px solid transparent;
    }

    .badge-high {
        background: rgba(239, 68, 68, 0.12);
        color: var(--danger);
        border: 2px solid rgba(239, 68, 68, 0.25);
    }

    .badge-medium {
        background: rgba(245, 158, 11, 0.12);
        color: var(--warning);
        border: 2px solid rgba(245, 158, 11, 0.25);
    }

    .badge-low {
        background: rgba(59, 130, 246, 0.12);
        color: var(--info);
        border: 2px solid rgba(59, 130, 246, 0.25);
    }

    .badge:hover {
        transform: scale(1.05);
    }

    /* ===== Timeline ===== */
    .timeline {
        position: relative;
        padding-left: 1.75rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(to bottom, var(--primary), var(--secondary), var(--success));
        border-radius: 1.5px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
        padding-left: 2rem;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.75rem;
        top: 0.375rem;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--primary);
        border: 3px solid white;
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.2);
        animation: pulse 2s infinite;
    }

    .timeline-item:nth-child(2)::before { background: var(--secondary); box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2); }
    .timeline-item:nth-child(3)::before { background: var(--success); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2); }

    .timeline-content {
        background: rgba(255, 255, 255, 0.7);
        padding: 1.1rem 1.35rem;
        border-radius: var(--radius-sm);
        border: 1px solid rgba(124, 58, 237, 0.15);
        transition: all var(--transition-fast);
    }

    .timeline-content:hover {
        transform: translateX(5px);
        background: white;
        box-shadow: var(--shadow-sm);
    }

    .timeline-title {
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.5rem;
        font-size: var(--font-base);
        line-height: 1.5;
    }

    .timeline-project {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 0.25rem;
        font-size: var(--font-sm);
    }

    .timeline-time {
        font-size: var(--font-sm);
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        font-weight: 500;
    }

    .timeline-time i {
        color: var(--primary);
        font-size: var(--font-base);
    }

    /* ===== Empty States ===== */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--dark);
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.5);
        border-radius: var(--radius-md);
    }

    .empty-state::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(124, 58, 237, 0.05) 0%, rgba(124, 58, 237, 0) 70%);
        border-radius: 50%;
    }

    .empty-state i {
        font-size: 3.5rem;
        margin-bottom: 1.25rem;
        opacity: 0.4;
        position: relative;
        z-index: 1;
        animation: float 4s infinite ease-in-out;
        color: var(--primary);
    }

    .empty-state p {
        font-size: var(--font-base);
        margin: 0;
        position: relative;
        z-index: 1;
        font-weight: 500;
    }

    /* ===== Responsive Design ===== */
    @media (max-width: 1200px) {
        .content-wrapper {
            padding: 1.5rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .content-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .welcome-section .row {
            flex-direction: column;
        }

        .welcome-illustration {
            padding-top: 0;
        }

        .welcome-illustration img {
            max-height: 160px;
        }

        .nav-pills .nav-link {
            padding: 1rem;
            font-size: var(--font-sm);
        }

        .nav-pills .nav-link i {
            font-size: 1.25rem;
            margin-right: 0.5rem;
        }

        .welcome-title {
            font-size: var(--font-3xl);
        }
    }

    @media (max-width: 768px) {
        .content-wrapper {
            padding: 1.25rem;
        }

        #dashboardTabs {
            display: none !important;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .content-grid {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .welcome-title {
            font-size: var(--font-2xl);
        }

        .welcome-content {
            padding: 1.75rem;
        }

        .stat-card {
            padding: 1.5rem;
            min-height: 200px;
        }

        .stat-value {
            font-size: var(--font-2xl);
        }

        .content-card {
            height: 450px;
        }
    }

    @media (max-width: 576px) {
        .content-wrapper {
            padding: 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .nav-pills .nav-link {
            padding: 0.875rem 0.5rem;
            font-size: var(--font-xs);
        }

        .nav-pills .nav-link i {
            font-size: 1rem;
            margin-right: 0.375rem;
        }

        .welcome-badges {
            flex-direction: column;
            gap: 0.75rem;
        }

        .card-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        .card-action {
            align-self: flex-start;
        }

        .welcome-title {
            font-size: var(--font-xl);
        }

        .welcome-text {
            font-size: var(--font-base);
        }

        .stat-value {
            font-size: var(--font-xl);
        }
    }

    /* ===== Loading Animation ===== */
    .loading-shimmer {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 400% 100%;
        animation: shimmer 1.5s infinite linear;
        border-radius: var(--radius-sm);
    }

    /* ===== Additional Readability Enhancements ===== */
    .dropdown-menu {
        font-size: var(--font-sm);
        border: 1px solid rgba(124, 58, 237, 0.1);
        box-shadow: var(--shadow-lg);
    }

    .dropdown-item {
        font-weight: 500;
        padding: 0.6rem 1.25rem;
    }

    .dropdown-item:hover {
        background: rgba(124, 58, 237, 0.05);
        color: var(--primary);
    }

    .dropdown-item i {
        margin-right: 0.5rem;
        color: var(--primary);
    }

    /* Fix for text contrast */
    .text-muted {
        color: #4B5563 !important;
    }

    .text-primary {
        color: var(--primary) !important;
    }

    /* Ensure all text meets WCAG contrast standards */
    p, span, div, a {
        color: var(--dark);
    }

    .bg-white {
        background: rgba(255, 255, 255, 0.95) !important;
    }

    /* ===== Admin Command Center ===== */
    .industry-dashboard-shell {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .industry-hero-card,
    .industry-panel,
    .industry-metric-card {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(255, 255, 255, 0.72);
        box-shadow: 0 24px 60px rgba(31, 41, 55, 0.12);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
    }

    .industry-hero-card {
        border-radius: 28px;
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr);
        gap: 1.5rem;
        min-height: 310px;
        overflow: hidden;
        padding: clamp(1.35rem, 3vw, 2.5rem);
        position: relative;
        isolation: isolate;
        animation: fadeInUp .7s ease both;
    }

    .industry-hero-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 18% 24%, rgba(59, 130, 246, .20), transparent 30%),
            radial-gradient(circle at 74% 12%, rgba(16, 185, 129, .20), transparent 28%),
            linear-gradient(135deg, rgba(124, 58, 237, .12), rgba(255, 255, 255, 0));
        z-index: -1;
    }

    .industry-hero-copy {
        align-self: center;
        max-width: 760px;
    }

    .industry-eyebrow {
        align-items: center;
        background: rgba(16, 185, 129, .12);
        border: 1px solid rgba(16, 185, 129, .18);
        border-radius: 999px;
        color: #047857 !important;
        display: inline-flex;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .08em;
        margin-bottom: 1rem;
        padding: .42rem .75rem;
        text-transform: uppercase;
    }

    .industry-hero-copy h1 {
        color: #111827;
        font-size: clamp(2rem, 4vw, 4rem);
        font-weight: 900;
        letter-spacing: 0;
        line-height: 1;
        margin-bottom: .85rem;
    }

    .industry-hero-copy p {
        color: #4b5563;
        font-size: clamp(.98rem, 1.6vw, 1.1rem);
        max-width: 640px;
    }

    .industry-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .8rem;
        margin-top: 1.35rem;
    }

    .industry-btn {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-weight: 800;
        gap: .5rem;
        justify-content: center;
        min-height: 44px;
        padding: .78rem 1.1rem;
        text-decoration: none;
        transition: transform .22s ease, box-shadow .22s ease, background .22s ease;
        white-space: nowrap;
    }

    .industry-btn-primary {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        box-shadow: 0 16px 34px rgba(37, 99, 235, .26);
        color: #fff !important;
    }

    .industry-btn-light {
        background: #fff;
        border: 1px solid rgba(37, 99, 235, .16);
        color: #1f2937 !important;
    }

    .industry-btn:hover {
        transform: translateY(-2px);
    }

    .industry-hero-visual {
        align-items: center;
        display: flex;
        justify-content: center;
        min-height: 230px;
    }

    .industry-hero-visual img {
        filter: drop-shadow(0 28px 38px rgba(31, 41, 55, .18));
        max-height: 280px;
        object-fit: contain;
        transform: rotate(-2deg);
        width: min(100%, 420px);
        animation: float 7s ease-in-out infinite;
    }

    .industry-overview-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .industry-metric-card {
        border-radius: 20px;
        color: #111827 !important;
        min-height: 155px;
        overflow: hidden;
        padding: 1.1rem;
        position: relative;
        text-decoration: none;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .industry-metric-card:hover {
        border-color: rgba(37, 99, 235, .28);
        box-shadow: 0 28px 70px rgba(37, 99, 235, .16);
        transform: translateY(-5px);
    }

    .industry-metric-card span,
    .industry-metric-card small {
        color: #667085 !important;
        display: block;
        font-weight: 800;
    }

    .industry-metric-card strong {
        color: #111827;
        display: block;
        font-size: clamp(2rem, 3vw, 3rem);
        font-weight: 900;
        line-height: 1.05;
        margin: .65rem 0;
    }

    .industry-metric-card.is-primary {
        background: linear-gradient(135deg, rgba(37, 99, 235, .95), rgba(124, 58, 237, .94));
    }

    .industry-metric-card.is-primary span,
    .industry-metric-card.is-primary strong,
    .industry-metric-card.is-primary small,
    .industry-metric-card.is-primary i {
        color: #fff !important;
    }

    .industry-arrow {
        bottom: 1rem;
        color: #2563eb !important;
        font-size: 1.35rem;
        position: absolute;
        right: 1rem;
    }

    .industry-main-grid,
    .industry-chart-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, .6fr);
    }

    .industry-panel {
        border-radius: 24px;
        padding: 1.2rem;
        overflow: hidden;
        position: relative;
    }

    .industry-panel-head {
        align-items: flex-start;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .industry-panel-head h3 {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 900;
        margin: 0 0 .25rem;
    }

    .industry-panel-head p {
        color: #667085;
        font-size: .86rem;
        font-weight: 700;
        margin: 0;
    }

    .industry-panel-head a {
        align-items: center;
        background: rgba(37, 99, 235, .08);
        border-radius: 999px;
        color: #2563eb !important;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: .78rem;
        font-weight: 900;
        padding: .48rem .75rem;
        text-decoration: none;
    }

    .industry-bars {
        display: grid;
        gap: .8rem;
    }

    .industry-bar {
        align-items: center;
        display: grid;
        gap: .8rem;
        grid-template-columns: minmax(0, 1fr) 94px;
    }

    .industry-bar span {
        background: #eef2ff;
        border-radius: 999px;
        display: block;
        height: 13px;
        overflow: hidden;
        position: relative;
    }

    .industry-bar span::after {
        animation: progressFill 1.2s ease both;
        background: linear-gradient(90deg, #2563eb, #10b981);
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
        color: #4b5563;
        font-size: .82rem;
        font-weight: 900;
        margin: 0;
    }

    .industry-gauge {
        align-items: center;
        background: conic-gradient(#10b981 0deg, #2563eb var(--value-deg), #e5e7eb var(--value-deg), #e5e7eb 180deg, transparent 180deg);
        border-radius: 180px 180px 20px 20px;
        display: flex;
        height: 148px;
        justify-content: center;
        margin: .5rem auto 1rem;
        max-width: 270px;
        position: relative;
    }

    .industry-gauge::after {
        background: #fff;
        border-radius: 150px 150px 18px 18px;
        content: "";
        inset: 20px 20px 0;
        position: absolute;
    }

    .industry-gauge div {
        margin-top: 28px;
        position: relative;
        text-align: center;
        z-index: 1;
    }

    .industry-gauge strong {
        color: #111827;
        display: block;
        font-size: 2.15rem;
        font-weight: 900;
        line-height: 1;
    }

    .industry-gauge span {
        color: #667085 !important;
        font-size: .82rem;
        font-weight: 900;
    }

    .industry-presence-row {
        display: grid;
        gap: .65rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .industry-presence-row span {
        background: rgba(15, 23, 42, .04);
        border-radius: 14px;
        color: #4b5563 !important;
        font-size: .78rem;
        font-weight: 800;
        padding: .75rem .6rem;
        text-align: center;
    }

    .industry-presence-row b {
        color: #111827;
        display: block;
        font-size: 1.05rem;
    }

    .industry-chart-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .industry-chart-card {
        background: linear-gradient(180deg, #fff, rgba(248, 250, 252, .9));
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 20px;
        min-height: 250px;
        padding: 1rem;
    }

    .industry-chart-body {
        align-items: center;
        display: flex;
        flex-direction: column;
        gap: .85rem;
        text-align: center;
    }

    .industry-donut {
        --accent: #2563eb;
        align-items: center;
        animation: bounceIn .65s ease both;
        background: conic-gradient(var(--accent) calc(var(--percent) * 1%), #e5e7eb 0);
        border-radius: 50%;
        display: flex;
        height: 132px;
        justify-content: center;
        position: relative;
        width: 132px;
    }

    .industry-donut::after {
        background: #fff;
        border-radius: 50%;
        content: "";
        inset: 16px;
        position: absolute;
    }

    .industry-donut strong {
        color: #111827;
        font-size: 1.35rem;
        font-weight: 900;
        position: relative;
        z-index: 1;
    }

    .industry-chart-meta h4 {
        color: #111827;
        font-size: .98rem;
        font-weight: 900;
        margin: 0 0 .25rem;
    }

    .industry-chart-meta p {
        color: #667085;
        font-size: .8rem;
        font-weight: 800;
        margin: 0;
    }

    .industry-chart-link {
        color: #2563eb !important;
        font-size: .82rem;
        font-weight: 900;
        text-decoration: none;
    }

    .industry-feature-grid {
        display: grid;
        gap: .85rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .industry-feature-card {
        align-items: center;
        background: linear-gradient(180deg, #fff, rgba(248, 250, 252, .88));
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 18px;
        color: #111827 !important;
        display: grid;
        gap: .8rem;
        grid-template-columns: 44px minmax(0, 1fr) auto;
        min-height: 86px;
        padding: .9rem;
        text-decoration: none;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    .industry-feature-card:hover {
        border-color: rgba(37, 99, 235, .25);
        box-shadow: 0 18px 42px rgba(37, 99, 235, .12);
        transform: translateY(-3px);
    }

    .industry-feature-icon {
        align-items: center;
        background: linear-gradient(135deg, rgba(37, 99, 235, .12), rgba(16, 185, 129, .12));
        border-radius: 14px;
        color: #2563eb !important;
        display: inline-flex;
        font-size: 1.35rem;
        height: 44px;
        justify-content: center;
        width: 44px;
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
        color: #111827;
        font-size: .9rem;
        font-weight: 900;
    }

    .industry-feature-copy small {
        color: #667085;
        font-size: .76rem;
        font-weight: 800;
    }

    .industry-feature-card em {
        background: rgba(37, 99, 235, .08);
        border-radius: 999px;
        color: #2563eb;
        font-size: .78rem;
        font-style: normal;
        font-weight: 900;
        min-width: 42px;
        padding: .38rem .55rem;
        text-align: center;
    }

    @media (max-width: 1199.98px) {
        .industry-overview-grid,
        .industry-chart-grid,
        .industry-feature-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .industry-hero-card,
        .industry-main-grid {
            grid-template-columns: 1fr;
        }

        .industry-hero-visual {
            min-height: 170px;
        }
    }

    @media (max-width: 575.98px) {
        .content-wrapper {
            padding: 1rem;
        }

        .industry-hero-card,
        .industry-panel,
        .industry-metric-card {
            border-radius: 18px;
        }

        .industry-overview-grid,
        .industry-chart-grid,
        .industry-feature-grid,
        .industry-presence-row {
            grid-template-columns: 1fr;
        }

        .industry-panel-head {
            flex-direction: column;
        }

        .industry-bar {
            grid-template-columns: 1fr;
            gap: .35rem;
        }
    }

    .saas-executive-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: 1.1fr .9fr;
    }

    .saas-revenue-strip {
        display: grid;
        gap: .85rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .saas-money-card {
        background:
            linear-gradient(180deg, rgba(255,255,255,.95), rgba(248,250,252,.92)),
            radial-gradient(circle at 20% 10%, rgba(37,99,235,.14), transparent 30%);
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 18px;
        min-height: 132px;
        overflow: hidden;
        padding: 1rem;
        position: relative;
    }

    .saas-money-card::after {
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.7), transparent);
        content: "";
        height: 100%;
        left: -120%;
        position: absolute;
        top: 0;
        transform: skewX(-20deg);
        width: 55%;
        animation: shine 5s ease-in-out infinite;
    }

    .saas-money-card span,
    .saas-insight-card span,
    .saas-module-card small {
        color: #667085 !important;
        display: block;
        font-size: .76rem;
        font-weight: 900;
    }

    .saas-money-card strong {
        color: #111827;
        display: block;
        font-size: clamp(1.35rem, 2vw, 2.15rem);
        font-weight: 950;
        letter-spacing: 0;
        line-height: 1.1;
        margin: .55rem 0 .3rem;
    }

    .saas-money-card em {
        color: #2563eb;
        font-size: .76rem;
        font-style: normal;
        font-weight: 900;
    }

    .saas-prediction-grid {
        display: grid;
        gap: .85rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .saas-insight-card {
        background: rgba(255,255,255,.94);
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 18px;
        min-height: 132px;
        padding: 1rem;
        position: relative;
    }

    .saas-insight-card strong {
        color: #111827;
        display: block;
        font-size: 1.05rem;
        font-weight: 950;
        margin: .4rem 0;
    }

    .saas-insight-card p {
        color: #667085;
        font-size: .8rem;
        font-weight: 750;
        margin: 0;
    }

    .saas-risk-pill {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-size: .72rem;
        font-weight: 950;
        gap: .35rem;
        padding: .32rem .56rem;
    }

    .saas-risk-low {
        background: rgba(16,185,129,.12);
        color: #047857;
    }

    .saas-risk-mid {
        background: rgba(245,158,11,.14);
        color: #92400e;
    }

    .saas-risk-high {
        background: rgba(239,68,68,.12);
        color: #991b1b;
    }

    .saas-module-grid {
        display: grid;
        gap: .85rem;
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .saas-module-card {
        background: linear-gradient(180deg, #fff, rgba(248,250,252,.9));
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 18px;
        color: #111827 !important;
        display: flex;
        flex-direction: column;
        gap: .75rem;
        min-height: 196px;
        padding: .95rem;
        text-decoration: none;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    .saas-module-card:hover {
        border-color: rgba(37,99,235,.24);
        box-shadow: 0 24px 54px rgba(37,99,235,.13);
        transform: translateY(-4px);
    }

    .saas-module-head {
        align-items: center;
        display: flex;
        gap: .65rem;
    }

    .saas-module-icon {
        align-items: center;
        background: linear-gradient(135deg, rgba(37,99,235,.12), rgba(16,185,129,.12));
        border-radius: 14px;
        color: #2563eb !important;
        display: inline-flex;
        flex: 0 0 42px;
        font-size: 1.25rem;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .saas-module-card h4 {
        color: #111827;
        font-size: .9rem;
        font-weight: 950;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .saas-module-donut {
        --accent: #2563eb;
        align-items: center;
        align-self: center;
        background: conic-gradient(var(--accent) calc(var(--percent) * 1%), #e5e7eb 0);
        border-radius: 50%;
        display: flex;
        height: 94px;
        justify-content: center;
        position: relative;
        width: 94px;
    }

    .saas-module-donut::after {
        background: #fff;
        border-radius: 50%;
        content: "";
        inset: 12px;
        position: absolute;
    }

    .saas-module-donut strong {
        color: #111827;
        font-size: 1rem;
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
        background: rgba(37,99,235,.08);
        border-radius: 999px;
        color: #2563eb;
        font-size: .72rem;
        font-style: normal;
        font-weight: 950;
        padding: .3rem .48rem;
    }

    .saas-trend-board {
        display: grid;
        gap: .9rem;
        grid-template-columns: 1fr 1fr;
    }

    .saas-line-card {
        min-height: 220px;
    }

    .saas-sparkline {
        align-items: end;
        display: flex;
        gap: .5rem;
        height: 120px;
        margin-top: 1rem;
    }

    .saas-sparkline span {
        background: linear-gradient(180deg, #2563eb, #10b981);
        border-radius: 999px 999px 4px 4px;
        flex: 1 1 0;
        min-width: 10px;
        height: var(--spark);
        animation: progressFill 1.1s ease both;
    }

    @media (max-width: 1399.98px) {
        .saas-module-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 1199.98px) {
        .saas-executive-grid,
        .saas-trend-board {
            grid-template-columns: 1fr;
        }

        .saas-revenue-strip,
        .saas-module-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .saas-revenue-strip,
        .saas-prediction-grid,
        .saas-module-grid {
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
            $adminPieCharts = [
                ['label' => 'Attendance', 'hint' => "{$dashboardPresentCount} present / {$dashboardTotalEmployees} employees", 'route' => 'attendance.index', 'value' => $dashboardPresentCount, 'percent' => $dashboardAttendancePercent, 'color' => '#10b981'],
                ['label' => 'Projects', 'hint' => "{$dashboardTotalProject} active projects", 'route' => 'projects.index', 'value' => $dashboardTotalProject, 'percent' => round(($dashboardTotalProject / $dashboardFeatureScale) * 100), 'color' => '#2563eb'],
                ['label' => 'Tasks', 'hint' => "{$dashboardPendingTask} pending tasks", 'route' => 'tasks.index', 'value' => $dashboardPendingTask, 'percent' => round(($dashboardPendingTask / $dashboardFeatureScale) * 100), 'color' => '#f59e0b'],
                ['label' => 'Tickets', 'hint' => "{$dashboardUnresolvedTicket} unresolved tickets", 'route' => 'tickets.index', 'value' => $dashboardUnresolvedTicket, 'percent' => round(($dashboardUnresolvedTicket / $dashboardFeatureScale) * 100), 'color' => '#ef4444'],
                ['label' => 'Clients', 'hint' => "{$dashboardTotalClient} client records", 'route' => 'clients.index', 'value' => $dashboardTotalClient, 'percent' => round(($dashboardTotalClient / $dashboardFeatureScale) * 100), 'color' => '#7c3aed'],
                ['label' => 'Leaves', 'hint' => "{$dashboardPendingLeaves} pending requests", 'route' => 'leaves.index', 'value' => $dashboardPendingLeaves, 'percent' => round(($dashboardPendingLeaves / $dashboardFeatureScale) * 100), 'color' => '#06b6d4'],
                ['label' => 'Employees', 'hint' => "{$dashboardTotalEmployees} total employees", 'route' => 'employees.index', 'value' => $dashboardTotalEmployees, 'percent' => round(($dashboardTotalEmployees / $dashboardFeatureScale) * 100), 'color' => '#14b8a6'],
                ['label' => 'Reports', 'hint' => 'Attendance and operations reporting', 'route' => 'attendance.report', 'value' => 'View', 'percent' => max(35, $dashboardAttendancePercent), 'color' => '#64748b'],
            ];
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

        // Add intersection observer for scroll animations
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

        // Observe all animated elements
        document.querySelectorAll('.stat-card, .content-card, .industry-metric-card, .industry-panel, .industry-feature-card, .industry-chart-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

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
