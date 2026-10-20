<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin Login - {{ config('app.name', 'BBHPMS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Glowing background accent circles -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-500 to-indigo-600 shadow-xl shadow-cyan-500/20 mb-4 text-white">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Super Admin Portal</h1>
            <p class="text-sm text-slate-400 mt-1">Platform Central Management</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-xl">
            @if(session('success'))
                <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-xs font-semibold text-emerald-400">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs font-semibold text-rose-400">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('super-admin.login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Super Admin Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition text-sm"
                           placeholder="superadmin@bbhpms.com">
                    <div id="loginEmail_error" style="display:none; color: #ef4444; font-size: 12px; margin-top: 4px; font-weight: 500;"></div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required minlength="8" maxlength="128"
                               class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition text-sm pr-11"
                               placeholder="•••••••• (min 8 chars)">
                        <button type="button" id="toggleLoginPasswordBtn" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition" title="Show/Hide Password">
                            <i id="toggleLoginPasswordIcon" class="bx bx-show" style="font-size: 20px;"></i>
                        </button>
                    </div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Must be between 8 and 128 characters.</div>
                    <div id="loginPassword_error" style="display:none; color: #ef4444; font-size: 12px; margin-top: 4px; font-weight: 500;"></div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-800 bg-slate-950 text-cyan-500 focus:ring-cyan-500/20">
                        <span class="text-xs font-medium text-slate-400">Remember session</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-cyan-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 text-white font-bold rounded-xl shadow-lg shadow-cyan-500/25 transition duration-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                    Sign In to Central Panel
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-slate-600 mt-8">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const errorDiv = document.getElementById('loginEmail_error');
            const form = document.querySelector('form');

            function validateEmail() {
                const val = (emailInput.value || '').trim();
                if (!val) {
                    showError('Email is required.');
                    return false;
                }
                if (/\s/.test(val)) {
                    showError('Email address cannot contain spaces.');
                    return false;
                }
                const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!regex.test(val)) {
                    showError('Please enter a valid email address.');
                    return false;
                }
                clearError();
                return true;
            }

            function showError(msg) {
                if (errorDiv) {
                    errorDiv.textContent = msg;
                    errorDiv.style.display = 'block';
                }
                emailInput.style.borderColor = '#ef4444';
            }

            function clearError() {
                if (errorDiv) {
                    errorDiv.textContent = '';
                    errorDiv.style.display = 'none';
                }
                emailInput.style.borderColor = '';
            }

            const pwdInput = document.getElementById('password');
            const pwdErrorDiv = document.getElementById('loginPassword_error');
            const togglePwdBtn = document.getElementById('toggleLoginPasswordBtn');
            const togglePwdIcon = document.getElementById('toggleLoginPasswordIcon');

            if (togglePwdBtn && pwdInput) {
                togglePwdBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (pwdInput.type === 'password') {
                        pwdInput.type = 'text';
                        if (togglePwdIcon) { togglePwdIcon.className = 'bx bx-hide'; }
                    } else {
                        pwdInput.type = 'password';
                        if (togglePwdIcon) { togglePwdIcon.className = 'bx bx-show'; }
                    }
                });
            }

            function validatePassword() {
                if (!pwdInput) return true;
                const val = pwdInput.value;
                if (!val) {
                    showPwdError('Password is required.');
                    return false;
                }
                if (val.length < 8) {
                    showPwdError('Password must be at least 8 characters long.');
                    return false;
                }
                if (val.length > 128) {
                    showPwdError('Password cannot exceed 128 characters.');
                    return false;
                }
                clearPwdError();
                return true;
            }

            function showPwdError(msg) {
                if (pwdErrorDiv) {
                    pwdErrorDiv.textContent = msg;
                    pwdErrorDiv.style.display = 'block';
                }
                if (pwdInput) pwdInput.style.borderColor = '#ef4444';
            }

            function clearPwdError() {
                if (pwdErrorDiv) {
                    pwdErrorDiv.textContent = '';
                    pwdErrorDiv.style.display = 'none';
                }
                if (pwdInput) pwdInput.style.borderColor = '';
            }

            if (pwdInput) {
                pwdInput.addEventListener('input', function() {
                    if (pwdErrorDiv && pwdErrorDiv.style.display === 'block') {
                        validatePassword();
                    }
                });
                pwdInput.addEventListener('blur', validatePassword);
            }

            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    if (/\s/.test(this.value)) {
                        this.value = this.value.replace(/\s+/g, '');
                    }
                    if (errorDiv.style.display === 'block') {
                        validateEmail();
                    }
                });
                emailInput.addEventListener('blur', validateEmail);
            }

            if (form) {
                form.addEventListener('submit', function(e) {
                    const isEmailValid = validateEmail();
                    const isPwdValid = validatePassword();
                    if (!isEmailValid || !isPwdValid) {
                        e.preventDefault();
                        if (!isEmailValid) {
                            emailInput.focus();
                        } else if (!isPwdValid) {
                            pwdInput.focus();
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
