@auth
@php
    $showNotice = Auth::user()->password_changed_notice;
    $changerRole = Auth::user()->password_changed_by_role ?? 'Admin';
@endphp

<div id="passwordChangedNoticeModal" class="pwd-notice-overlay" style="display: {{ $showNotice ? 'flex' : 'none' }};">
    <div class="pwd-notice-card">
        <div class="pwd-notice-header">
            <div class="pwd-notice-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3 class="pwd-notice-title">Security Alert: Password Changed</h3>
        </div>
        <div class="pwd-notice-body">
            <p class="pwd-notice-message">
                Your password has been changed by <strong id="pwdChangerRole">{{ $changerRole }}</strong>. Please contact them to get your updated password.
            </p>
            
            <div class="pwd-notice-timer-box">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-xs text-uppercase font-weight-bold text-muted">Automatic Logout Countdown</span>
                    <span class="pwd-timer-display" id="pwdCountdownText">01:00</span>
                </div>
                <div class="pwd-progress-bar-bg">
                    <div class="pwd-progress-bar-fill" id="pwdProgressBar"></div>
                </div>
            </div>
        </div>
        <div class="pwd-notice-footer">
            <form id="pwdChangedLogoutForm" method="POST" action="{{ route('password-changed-logout') }}">
                @csrf
                <button type="submit" class="pwd-notice-btn" id="pwdLogoutBtn">
                    <i class="fas fa-sign-out-alt me-1.5"></i> OK, Log Out Now
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .pwd-notice-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.88);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        z-index: 9999999 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif;
    }

    .pwd-notice-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 28px;
        max-width: 480px;
        width: 100%;
        padding: 2.25rem 2rem;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(239, 68, 68, 0.25);
        text-align: center;
        animation: pwdPopIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }

    @keyframes pwdPopIn {
        from { opacity: 0; transform: scale(0.85) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .pwd-notice-icon {
        width: 64px;
        height: 64px;
        border-radius: 22px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 1.25rem;
        box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.45);
    }

    .pwd-notice-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 0.5rem;
    }

    .pwd-notice-message {
        font-size: 0.98rem;
        color: #475569;
        line-height: 1.55;
        margin-bottom: 1.5rem;
    }

    .pwd-notice-timer-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.75rem;
    }

    .pwd-timer-display {
        font-family: 'JetBrains Mono', monospace, sans-serif;
        font-weight: 800;
        font-size: 1.1rem;
        color: #ef4444;
    }

    .pwd-progress-bar-bg {
        width: 100%;
        height: 8px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .pwd-progress-bar-fill {
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, #ef4444, #f59e0b);
        border-radius: inherit;
        transition: width 1s linear;
    }

    .pwd-notice-btn {
        width: 100%;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 40px;
        padding: 0.85rem 1.75rem;
        font-weight: 800;
        font-size: 1rem;
        box-shadow: 0 8px 25px -4px rgba(239, 68, 68, 0.4);
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .pwd-notice-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px -4px rgba(239, 68, 68, 0.5);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('passwordChangedNoticeModal');
    const countdownText = document.getElementById('pwdCountdownText');
    const progressBar = document.getElementById('pwdProgressBar');
    const logoutForm = document.getElementById('pwdChangedLogoutForm');
    const changerRoleText = document.getElementById('pwdChangerRole');

    let totalSeconds = 60;
    let remainingSeconds = totalSeconds;
    let timerInterval = null;

    function startNoticeTimer() {
        if (timerInterval) return;
        
        remainingSeconds = totalSeconds;
        updateTimerDisplay();

        timerInterval = setInterval(function () {
            remainingSeconds--;
            updateTimerDisplay();

            if (remainingSeconds <= 0) {
                clearInterval(timerInterval);
                // Auto logout after 1 minute
                logoutForm.submit();
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        const mins = Math.floor(remainingSeconds / 60);
        const secs = remainingSeconds % 60;
        const formatted = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        if (countdownText) countdownText.textContent = formatted;
        
        const pct = (remainingSeconds / totalSeconds) * 100;
        if (progressBar) progressBar.style.width = pct + '%';
    }

    // If modal is visible on page load, start timer immediately
    if (modal && modal.style.display !== 'none') {
        startNoticeTimer();
    }

    // Polling background check every 5 seconds for real-time notification even without page refresh
    setInterval(function () {
        fetch("{{ route('check-password-status') }}", {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.changed) {
                if (changerRoleText && data.by_role) {
                    changerRoleText.textContent = data.by_role;
                }
                if (modal && modal.style.display === 'none') {
                    modal.style.display = 'flex';
                    startNoticeTimer();
                }
            }
        })
        .catch(err => console.log('Password check error:', err));
    }, 5000);
});
</script>
@endauth
