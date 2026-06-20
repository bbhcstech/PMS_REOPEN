<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ $loginTitle ?? 'Login' }} - Bitroxia</title>
    <meta name="description" content="Bitroxia PMS login" />

    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="admin/assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" />
    <link rel="stylesheet" href="admin/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="admin/assets/css/demo.css" />
    <link rel="stylesheet" href="admin/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="admin/assets/vendor/css/pages/page-auth.css" />

    <script src="admin/assets/vendor/js/helpers.js"></script>
    <script src="admin/assets/js/config.js"></script>

    <style>
        :root {
            --brand-blue: #0569ff;
            --brand-cyan: #13d5e7;
            --brand-purple: #8f25ff;
            --brand-rose: #ff4da6;
            --ink: #101427;
            --muted: #697083;
            --line: rgba(16, 20, 39, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 15% 16%, rgba(19, 213, 231, 0.28), transparent 31%),
                radial-gradient(circle at 88% 12%, rgba(143, 37, 255, 0.22), transparent 28%),
                radial-gradient(circle at 70% 90%, rgba(255, 77, 166, 0.14), transparent 30%),
                linear-gradient(135deg, #eef5ff 0%, #c9d8ee 48%, #aeb8cc 100%);
            color: var(--ink);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 34px;
            overflow-x: hidden;
        }

        @keyframes pageFadeIn {
            from { opacity: 0; transform: translateY(14px) scale(0.985); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes imageDrift {
            0%, 100% { transform: scale(1.025) translate3d(0, 0, 0); }
            50% { transform: scale(1.055) translate3d(-8px, -6px, 0); }
        }

        @keyframes cardFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes softPulse {
            0%, 100% { box-shadow: 0 18px 32px rgba(5, 105, 255, 0.24); }
            50% { box-shadow: 0 20px 42px rgba(19, 213, 231, 0.32); }
        }

        @keyframes haloSweep {
            0% { transform: translate3d(-18%, -12%, 0) rotate(0deg); opacity: 0.5; }
            50% { transform: translate3d(8%, 5%, 0) rotate(12deg); opacity: 0.8; }
            100% { transform: translate3d(-18%, -12%, 0) rotate(0deg); opacity: 0.5; }
        }

        .auth-shell {
            width: min(1120px, 100%);
            min-height: 720px;
            display: grid;
            grid-template-columns: minmax(360px, 0.92fr) minmax(440px, 1.38fr);
            gap: 30px;
            padding: 18px;
            border-radius: 34px;
            background:
                linear-gradient(160deg, rgba(255, 255, 255, 0.96) 0%, rgba(246, 250, 255, 0.93) 54%, rgba(230, 248, 255, 0.9) 100%);
            box-shadow: 0 34px 80px rgba(8, 16, 35, 0.22);
            position: relative;
            overflow: hidden;
            animation: pageFadeIn 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .auth-shell::before {
            content: "";
            position: absolute;
            inset: 18px auto auto 18px;
            width: 38%;
            height: 58%;
            border-radius: 30px;
            background: linear-gradient(145deg, rgba(19, 213, 231, 0.08), rgba(143, 37, 255, 0.05));
            pointer-events: none;
        }

        .auth-shell::after {
            content: "";
            position: absolute;
            width: 420px;
            height: 420px;
            right: 26%;
            bottom: -220px;
            border-radius: 50%;
            background: conic-gradient(from 90deg, rgba(5,105,255,.18), rgba(19,213,231,.18), rgba(143,37,255,.16), rgba(255,77,166,.12), rgba(5,105,255,.18));
            filter: blur(26px);
            animation: haloSweep 10s ease-in-out infinite;
            pointer-events: none;
        }

        .auth-form-panel {
            min-width: 0;
            display: flex;
            flex-direction: column;
            padding: 18px 18px 12px;
        }

        .brand-pill {
            width: fit-content;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 18px 8px 10px;
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--ink);
            text-decoration: none;
            font-size: 0.94rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.58);
            position: relative;
            z-index: 2;
            transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
        }

        .brand-pill:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.76);
            border-color: rgba(5, 105, 255, 0.18);
        }

        .brand-pill img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }

        .form-wrap {
            width: min(100%, 320px);
            margin: auto;
            padding: 28px 0;
            position: relative;
            z-index: 2;
        }

        .auth-title {
            font-size: 1.72rem;
            line-height: 1.15;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0;
            margin-bottom: 8px;
        }

        .auth-subtitle {
            color: var(--muted);
            text-align: center;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 26px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            color: #747b8e;
            font-size: 0.78rem;
            font-weight: 600;
            margin: 0 0 7px 18px;
            text-transform: none;
            letter-spacing: 0;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #9aa3b5;
            font-size: 1.08rem;
            pointer-events: none;
            transition: color 0.2s ease;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            min-height: 46px;
            border: 1px solid rgba(16, 20, 39, 0.04);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.84);
            color: var(--ink);
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0 48px 0 66px;
            outline: none;
            box-shadow: 0 12px 30px rgba(44, 55, 86, 0.06);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .input-wrapper > .form-control {
            box-sizing: border-box;
            flex: 1 1 auto;
            min-width: 0;
            padding-left: 66px !important;
            padding-right: 56px !important;
        }

        .form-control:focus {
            border-color: rgba(5, 105, 255, 0.42);
            background: #fff;
            box-shadow: 0 0 0 5px rgba(19, 213, 231, 0.16), 0 16px 34px rgba(5, 105, 255, 0.1);
        }

        .input-wrapper:focus-within .input-icon {
            color: var(--brand-blue);
        }

        .form-control::placeholder {
            color: #9ba3b2;
        }

        #email.form-control {
            padding-left: 72px !important;
        }

        #password.form-control {
            padding-left: 66px !important;
            padding-right: 62px !important;
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #555f72;
            font-size: 1.12rem;
            cursor: pointer;
            z-index: 3;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--brand-purple);
            transform: translateY(-50%) scale(1.08);
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin: 10px 0 22px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 22px;
            margin: 0;
        }

        .form-check-input {
            width: 17px;
            height: 17px;
            margin: 0;
            border: 1px solid rgba(16, 20, 39, 0.2);
            border-radius: 5px;
            appearance: none;
            background: rgba(255, 255, 255, 0.82);
            cursor: pointer;
            position: relative;
            flex: 0 0 auto;
        }

        .form-check-input:checked {
            background: linear-gradient(135deg, var(--brand-blue), var(--brand-cyan));
            border-color: transparent;
        }

        .form-check-input:checked::after {
            content: "\2713";
            position: absolute;
            left: 3px;
            top: -3px;
            color: #fff;
            font-size: 0.88rem;
            font-weight: 800;
        }

        .form-check-label,
        .forgot-link {
            color: #5f687a;
            font-size: 0.78rem;
            font-weight: 600;
            text-decoration: none;
        }

        .forgot-link {
            color: var(--brand-blue);
            white-space: nowrap;
        }

        .terms-check {
            align-items: flex-start;
            margin: -6px 0 20px;
        }

        .terms-check .form-check-input {
            margin-top: 2px;
        }

        .terms-check .form-check-label {
            line-height: 1.45;
        }

        .terms-link {
            color: var(--brand-blue);
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .btn-pms {
            width: 100%;
            min-height: 50px;
            border: 0;
            border-radius: 999px !important;
            background: linear-gradient(135deg, var(--brand-cyan), var(--brand-blue) 58%, var(--brand-purple));
            color: #fff;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 18px 32px rgba(5, 105, 255, 0.24);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            animation: softPulse 5s ease-in-out infinite;
        }

        .btn-pms::after {
            content: "";
            position: absolute;
            inset: -120% auto auto -40%;
            width: 40%;
            height: 300%;
            background: rgba(255, 255, 255, 0.28);
            transform: rotate(24deg);
            transition: left 0.55s ease;
        }

        .btn-pms:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 38px rgba(5, 105, 255, 0.3);
            color: #fff;
        }

        .btn-pms:hover::after {
            left: 120%;
        }

        .auth-footer {
            margin-top: auto;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            color: #6b7280;
            font-size: 0.86rem;
        }

        .auth-footer a {
            color: var(--ink);
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .access-note {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #5f687a;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .alert {
            border: 0;
            border-radius: 18px;
            padding: 0.9rem 1rem;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 14px 30px rgba(44, 55, 86, 0.08);
            font-size: 0.82rem;
            margin-bottom: 18px;
        }

        .alert-danger {
            border-left: 4px solid #ef5d70;
        }

        .alert-success {
            border-left: 4px solid #13b981;
        }

        .error-badge {
            background: rgba(239, 93, 112, 0.08);
            border-radius: 12px;
            padding: 0.55rem 0.7rem;
            margin-bottom: 0.5rem;
            color: #4a5568;
        }

        .field-error {
            margin: 7px 0 0 18px;
            color: #bc3b4c;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .visual-panel {
            position: relative;
            min-height: 684px;
            border-radius: 28px;
            overflow: hidden;
            background: #101427;
            isolation: isolate;
        }

        .visual-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(4, 8, 28, 0.02), rgba(4, 8, 28, 0.24)),
                radial-gradient(circle at 72% 20%, rgba(19, 213, 231, 0.24), transparent 26%);
            z-index: 1;
            pointer-events: none;
        }

        .visual-copy {
            position: absolute;
            left: 34px;
            right: 34px;
            bottom: 34px;
            z-index: 4;
            color: #fff;
            text-shadow: 0 10px 28px rgba(4, 8, 28, 0.36);
        }

        .visual-copy h2 {
            margin: 0 0 8px;
            font-size: clamp(1.65rem, 3vw, 2.55rem);
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: 0;
        }

        .visual-copy p {
            max-width: 420px;
            margin: 0;
            color: rgba(255,255,255,.84);
            font-size: .95rem;
            font-weight: 600;
        }

        .visual-panel img {
            width: 100%;
            height: 100%;
            min-height: 684px;
            object-fit: cover;
            display: block;
            filter: saturate(0.82) hue-rotate(10deg) brightness(1.02) contrast(0.96);
            transform: scale(1.025);
            animation: imageDrift 14s ease-in-out infinite;
        }

        .close-link {
            position: absolute;
            right: 20px;
            top: 18px;
            z-index: 5;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.86);
            color: #111827;
            text-decoration: none;
            font-size: 1.2rem;
            box-shadow: 0 18px 30px rgba(12, 16, 33, 0.12);
        }

        .floating-card {
            position: absolute;
            z-index: 4;
            border-radius: 18px;
            backdrop-filter: blur(16px);
            box-shadow: 0 18px 44px rgba(3, 8, 26, 0.22);
            animation: cardFloat 6s ease-in-out infinite;
        }

        .task-card {
            top: 26px;
            left: 62px;
            width: 184px;
            padding: 14px 16px;
            background: linear-gradient(135deg, #dff9ff, #8eeaff);
            color: #123048;
            font-size: 0.78rem;
            font-weight: 700;
            animation-delay: 0.4s;
        }

        .task-card span,
        .meeting-card span {
            display: block;
            margin-top: 4px;
            font-size: 0.72rem;
            font-weight: 500;
            color: rgba(18, 48, 72, 0.74);
        }

        .week-card {
            left: 118px;
            bottom: 206px;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 13px;
            padding: 16px 18px 18px;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            min-width: 310px;
            animation-delay: 1.1s;
        }

        .week-card div {
            text-align: center;
            font-size: 0.76rem;
            font-weight: 500;
        }

        .week-card strong {
            display: block;
            margin-top: 6px;
            font-size: 1.12rem;
            font-weight: 700;
        }

        .meeting-card {
            left: 78px;
            bottom: 134px;
            width: 196px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.94);
            color: #252b3d;
            font-size: 0.78rem;
            font-weight: 700;
            animation-delay: 1.7s;
        }

        .avatar-stack {
            display: flex;
            margin-top: 12px;
        }

        .avatar-stack img {
            width: 24px;
            height: 24px;
            min-height: 24px;
            border-radius: 50%;
            border: 2px solid #fff;
            margin-right: -7px;
            object-fit: cover;
            filter: none;
        }

        .mini-people {
            right: 96px;
            top: 252px;
            display: flex;
            align-items: center;
            padding: 0;
            background: transparent;
            box-shadow: none;
            animation-delay: 0.9s;
        }

        .mini-people img {
            width: 58px;
            height: 58px;
            min-height: 58px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.86);
            object-fit: cover;
            margin-left: -16px;
            box-shadow: 0 16px 26px rgba(3, 8, 26, 0.22);
            filter: none;
        }

        @media (max-width: 980px) {
            body {
                padding: 18px;
            }

            .auth-shell {
                grid-template-columns: 1fr;
                min-height: 0;
            }

            .visual-panel {
                min-height: 360px;
                order: -1;
            }

            .visual-panel img {
                min-height: 360px;
            }
        }

        @media (max-width: 620px) {
            body {
                align-items: stretch;
                padding: 0;
            }

            .auth-shell {
                border-radius: 0;
                padding: 12px;
                gap: 14px;
            }

            .auth-form-panel {
                padding: 6px 6px 10px;
            }

            .form-wrap {
                width: 100%;
                padding: 28px 6px;
            }

            .visual-panel,
            .visual-panel img {
                min-height: 260px;
            }

            .floating-card,
            .mini-people {
                display: none;
            }

            .auth-footer {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        body .authentication-wrapper,
        body .container-xxl {
            all: unset;
            display: block;
        }

        .btn-check:focus + .btn,
        .btn:focus {
            box-shadow: none;
        }

        .btn-close {
            border-radius: 50%;
        }
    </style>
</head>
<body>
    @php
        $logoVersion = file_exists(public_path('logo.png')) ? filemtime(public_path('logo.png')) : time();
    @endphp
    <main class="auth-shell">
        <section class="auth-form-panel">
            <a href="{{ route('login') }}" class="brand-pill" aria-label="Bitroxia">
                <img src="{{ asset('logo.png') }}?v={{ $logoVersion }}" alt="Bitroxia logo" />
                <span>Bitroxia</span>
            </a>

            <div class="form-wrap">
                <h1 class="auth-title">{{ $loginTitle ?? 'Welcome back' }}</h1>
                <p class="auth-subtitle">Sign in to continue your workspace</p>

                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-start" role="alert">
                        <i class="fas fa-check-circle me-3 fs-5" style="color: #1e8a5e;"></i>
                        <div class="fw-semibold">{{ session('success') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger p-3" role="alert">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-triangle me-2 fs-5" style="color: #b13e4a;"></i>
                            <strong class="fs-6 fw-bold">Authentication requires attention</strong>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <div>
                            @foreach ($errors->all() as $error)
                                @if (str_contains($error, 'active but login is blocked'))
                                    <div class="error-badge d-flex align-items-start">
                                        <i class="fas fa-user-lock me-2 mt-1" style="color: #b45309;"></i>
                                        <div><strong class="fw-bold">{{ $error }}</strong><br /><small class="text-muted">Admin has disabled login access.</small></div>
                                    </div>
                                @elseif (str_contains($error, 'account is inactive') && !str_contains($error, 'and login is blocked'))
                                    <div class="error-badge d-flex align-items-start">
                                        <i class="fas fa-user-slash me-2 mt-1" style="color: #306cbe;"></i>
                                        <div><strong class="fw-bold">{{ $error }}</strong><br /><small class="text-muted">Employment status inactive.</small></div>
                                    </div>
                                @elseif (str_contains($error, 'inactive and login is blocked'))
                                    <div class="error-badge d-flex align-items-start">
                                        <i class="fas fa-ban me-2 mt-1" style="color: #5b5281;"></i>
                                        <div><strong class="fw-bold">{{ $error }}</strong><br /><small class="text-muted">Access blocked & inactive.</small></div>
                                    </div>
                                @elseif (str_contains($error, 'credentials do not match') || str_contains($error, 'auth.failed'))
                                    <div class="error-badge d-flex align-items-start">
                                        <i class="fas fa-key me-2 mt-1" style="color: #cc4b5a;"></i>
                                        <div><strong class="fw-bold">Invalid email or password</strong><br /><small class="text-muted">Check credentials and try again.</small></div>
                                    </div>
                                @else
                                    <div class="error-badge d-flex align-items-start">
                                        <i class="fas fa-exclamation-circle me-2 mt-1" style="color: #0569ff;"></i>
                                        <div><strong class="fw-bold">{{ $error }}</strong></div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-wrapper">
                            <i class="bx bx-envelope input-icon"></i>
                            <input type="text" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="you@company.com" autocomplete="email">
                        </div>
                        @if ($errors->has('email'))
                            <div class="field-error">{{ $errors->first('email') }}</div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrapper">
                            <i class="bx bx-lock-alt input-icon"></i>
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Enter password" autocomplete="current-password">
                            <span class="password-toggle" id="toggle-password">
                                <i class="bx bx-hide" id="toggle-icon"></i>
                            </span>
                        </div>
                        @if ($errors->has('password'))
                            <div class="field-error">{{ $errors->first('password') }}</div>
                        @endif
                    </div>

                    <div class="form-row">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                            <label class="form-check-label" for="remember_me">Keep me signed in</label>
                        </div>
                        <a href="auth-forgot-password-basic.html" class="forgot-link">Forgot?</a>
                    </div>

                    <div class="form-check terms-check">
                        <input class="form-check-input" type="checkbox" id="terms_accepted" name="terms_accepted" value="1" required @checked(old('terms_accepted'))>
                        <label class="form-check-label" for="terms_accepted">
                            I agree to the
                            <a href="{{ route('company.terms') }}" class="terms-link" target="_blank" rel="noopener">Terms &amp; Conditions</a>
                            of the organization.
                        </label>
                    </div>
                    @if ($errors->has('terms_accepted'))
                        <div class="field-error">{{ $errors->first('terms_accepted') }}</div>
                    @endif

                    <button type="submit" class="btn-pms">Submit</button>
                </form>
            </div>

            <div class="auth-footer">
                <span class="access-note"><i class="bx bx-shield-quarter"></i> Accounts are created by Admin or HR</span>
                <a href="{{ route('company.terms') }}" target="_blank" rel="noopener">Terms &amp; Conditions</a>
            </div>
        </section>

        <section class="visual-panel">
            <a href="{{ route('home') }}" class="close-link" aria-label="Back to home">
                <i class="bx bx-home-alt"></i>
            </a>
            <img src="{{ asset('register_login.jpeg') }}" alt="" />

            <div class="floating-card task-card">
                Task Review With Team
                <span>09:30am-10:00am</span>
            </div>

            <div class="floating-card mini-people">
                <img src="{{ asset('frontend/img/team-1.jpg') }}" alt="" />
                <img src="{{ asset('frontend/img/team-2.jpg') }}" alt="" />
                <img src="{{ asset('frontend/img/team-3.jpg') }}" alt="" />
            </div>

            <div class="floating-card week-card">
                <div>Sun<strong>22</strong></div>
                <div>Mon<strong>23</strong></div>
                <div>Tue<strong>24</strong></div>
                <div>Wed<strong>25</strong></div>
                <div>Thu<strong>26</strong></div>
                <div>Fri<strong>27</strong></div>
                <div>Sat<strong>28</strong></div>
            </div>

            <div class="floating-card meeting-card">
                Daily Meeting
                <span>12:00pm-01:00pm</span>
                <div class="avatar-stack">
                    <img src="{{ asset('frontend/img/testimonial-1.jpg') }}" alt="" />
                    <img src="{{ asset('frontend/img/testimonial-2.jpg') }}" alt="" />
                    <img src="{{ asset('frontend/img/testimonial-3.jpg') }}" alt="" />
                    <img src="{{ asset('frontend/img/testimonial-4.jpg') }}" alt="" />
                </div>
            </div>

            <!-- <div class="visual-copy">
                <h2>Run projects, people, and payroll from one calm workspace.</h2>
                <p>Secure access for Admin, HR, Managers, and Employees with every module right where the team expects it.</p>
            </div> -->
        </section>
    </main>

    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        (function() {
            'use strict';
            const togglePassword = document.getElementById('toggle-password');
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggle-icon');
            if (togglePassword && passwordField && toggleIcon) {
                togglePassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    toggleIcon.classList.toggle('bx-show');
                    toggleIcon.classList.toggle('bx-hide');
                });
            }
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-light)');
                alerts.forEach(function(alertEl) {
                    if (alertEl && typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                        const bsAlert = new bootstrap.Alert(alertEl);
                        bsAlert.close();
                    } else {
                        alertEl.style.transition = 'opacity 0.3s';
                        alertEl.style.opacity = '0';
                        setTimeout(() => alertEl.style.display = 'none', 350);
                    }
                });
            }, 7000);
        })();
    </script>
</body>
</html>
