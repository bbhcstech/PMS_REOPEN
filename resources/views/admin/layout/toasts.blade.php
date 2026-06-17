@php
    $toastMessages = [];

    foreach (['success', 'error', 'danger', 'warning', 'info', 'status', 'message'] as $toastType) {
        if (session()->has($toastType)) {
            $toastMessages[] = [
                'type' => in_array($toastType, ['status', 'message'], true) ? 'info' : ($toastType === 'danger' ? 'error' : $toastType),
                'message' => session($toastType),
            ];
        }
    }

    if ($errors->any()) {
        foreach ($errors->all() as $errorMessage) {
            $toastMessages[] = [
                'type' => 'error',
                'message' => $errorMessage,
            ];
        }
    }

    $toastMeta = [
        'success' => ['title' => 'Success', 'icon' => 'fa-check', 'class' => 'pms-toast-success'],
        'error' => ['title' => 'Error', 'icon' => 'fa-triangle-exclamation', 'class' => 'pms-toast-error'],
        'warning' => ['title' => 'Warning', 'icon' => 'fa-exclamation', 'class' => 'pms-toast-warning'],
        'info' => ['title' => 'Info', 'icon' => 'fa-circle-info', 'class' => 'pms-toast-info'],
    ];
@endphp

<style>
    .pms-toast-stack {
        position: fixed;
        top: 1.25rem;
        right: 1.25rem;
        z-index: 2147483640;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        width: min(420px, calc(100vw - 2rem));
        pointer-events: none;
    }

    .pms-toast {
        pointer-events: auto;
        display: grid;
        grid-template-columns: 42px 1fr auto;
        gap: 0.85rem;
        align-items: start;
        padding: 1rem 1rem 0.95rem;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 22px 60px rgba(15, 23, 42, 0.18);
        overflow: hidden;
        position: relative;
        animation: pmsToastIn 0.45s cubic-bezier(0.2, 0.9, 0.2, 1) both;
        backdrop-filter: blur(12px);
    }

    .pms-toast::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        background: var(--pms-toast-accent);
    }

    .pms-toast-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        color: #fff;
        background: var(--pms-toast-accent);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
    }

    .pms-toast-title {
        color: #111827;
        font-weight: 800;
        font-size: 0.95rem;
        line-height: 1.15;
        margin-bottom: 0.25rem;
    }

    .pms-toast-message {
        color: #4b5563;
        font-size: 0.86rem;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .pms-toast-close {
        border: 0;
        background: transparent;
        color: #64748b;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        transition: all 0.2s ease;
    }

    .pms-toast-close:hover {
        background: rgba(15, 23, 42, 0.06);
        color: #0f172a;
    }

    .pms-toast-progress {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        background: color-mix(in srgb, var(--pms-toast-accent) 70%, transparent);
        transform-origin: left center;
        animation: pmsToastProgress var(--pms-toast-duration, 5200ms) linear forwards;
    }

    .pms-toast-success { --pms-toast-accent: #10b981; }
    .pms-toast-error { --pms-toast-accent: #ef4444; }
    .pms-toast-warning { --pms-toast-accent: #f59e0b; }
    .pms-toast-info { --pms-toast-accent: #3b82f6; }

    .pms-toast.is-hiding {
        animation: pmsToastOut 0.28s ease both;
    }

    @keyframes pmsToastIn {
        from {
            opacity: 0;
            transform: translate3d(26px, -12px, 0) scale(0.96);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }
    }

    @keyframes pmsToastOut {
        to {
            opacity: 0;
            transform: translate3d(24px, -8px, 0) scale(0.96);
        }
    }

    @keyframes pmsToastProgress {
        from { transform: scaleX(1); }
        to { transform: scaleX(0); }
    }

    html[data-pms-theme="dark"] .pms-toast {
        background: rgba(17, 24, 39, 0.96);
        border-color: rgba(255, 255, 255, 0.08);
    }

    html[data-pms-theme="dark"] .pms-toast-title {
        color: #f9fafb;
    }

    html[data-pms-theme="dark"] .pms-toast-message {
        color: #d1d5db;
    }

    @media (max-width: 576px) {
        .pms-toast-stack {
            top: 0.85rem;
            right: 0.75rem;
            left: 0.75rem;
            width: auto;
        }

        .pms-toast {
            grid-template-columns: 38px 1fr auto;
            padding: 0.9rem;
        }
    }
</style>

<div class="pms-toast-stack" id="pmsToastStack" aria-live="polite" aria-atomic="true"></div>

<script>
    window.PMSToast = window.PMSToast || {
        show: function (message, type, title) {
            var stack = document.getElementById('pmsToastStack');
            if (!stack || !message) return;

            var meta = {
                success: { title: 'Success', icon: 'fa-check', className: 'pms-toast-success' },
                error: { title: 'Error', icon: 'fa-triangle-exclamation', className: 'pms-toast-error' },
                warning: { title: 'Warning', icon: 'fa-exclamation', className: 'pms-toast-warning' },
                info: { title: 'Info', icon: 'fa-circle-info', className: 'pms-toast-info' }
            }[type || 'info'];

            var toast = document.createElement('div');
            toast.className = 'pms-toast ' + meta.className;
            toast.style.setProperty('--pms-toast-duration', '5200ms');

            toast.innerHTML =
                '<div class="pms-toast-icon"><i class="fa-solid ' + meta.icon + '"></i></div>' +
                '<div><div class="pms-toast-title"></div><div class="pms-toast-message"></div></div>' +
                '<button type="button" class="pms-toast-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>' +
                '<div class="pms-toast-progress"></div>';

            toast.querySelector('.pms-toast-title').textContent = title || meta.title;
            toast.querySelector('.pms-toast-message').textContent = message;
            stack.appendChild(toast);

            function closeToast() {
                if (toast.classList.contains('is-hiding')) return;
                toast.classList.add('is-hiding');
                setTimeout(function () {
                    if (toast.parentNode) toast.parentNode.removeChild(toast);
                }, 300);
            }

            toast.querySelector('.pms-toast-close').addEventListener('click', closeToast);
            setTimeout(closeToast, 5200);
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        var messages = @json($toastMessages);
        messages.forEach(function (item, index) {
            setTimeout(function () {
                window.PMSToast.show(item.message, item.type);
            }, index * 180);
        });
    });
</script>
