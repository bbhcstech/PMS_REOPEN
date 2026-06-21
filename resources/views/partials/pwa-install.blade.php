<div class="pwa-install-card" data-pwa-install>
    <div class="pwa-install-card__icon" aria-hidden="true">
        <img src="{{ asset('pwa/icons/icon-192x192.png') }}" alt="">
    </div>
    <div class="pwa-install-card__copy">
        <strong>Install Bitroxia</strong>
        <span data-pwa-install-text>Open PMS faster from your device.</span>
    </div>
    <button class="pwa-install-card__button" type="button" data-pwa-install-button>
        <i class="fas fa-download" aria-hidden="true"></i>
        <span>Install</span>
    </button>
    <button class="pwa-install-card__close" type="button" data-pwa-install-close aria-label="Hide install option">
        <i class="fas fa-times" aria-hidden="true"></i>
    </button>
</div>

<style>
    .pwa-install-card[hidden] {
        display: none !important;
    }

    .pwa-install-card {
        align-items: center;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(18, 104, 255, 0.16);
        border-radius: 18px;
        box-shadow: 0 22px 60px rgba(16, 20, 39, 0.18);
        display: grid;
        gap: 12px;
        grid-template-columns: 44px minmax(0, 1fr) auto 34px;
        left: 18px;
        max-width: min(430px, calc(100vw - 36px));
        padding: 12px;
        position: fixed;
        right: auto;
        bottom: 18px;
        z-index: 1100;
        animation: pwa-install-card-pop 0.55s ease-out both;
    }

    .pwa-install-card__icon {
        align-items: center;
        background: linear-gradient(135deg, #1268ff, #8f25ff);
        border-radius: 14px;
        display: grid;
        height: 44px;
        justify-items: center;
        overflow: hidden;
        width: 44px;
    }

    .pwa-install-card__icon img {
        height: 36px;
        object-fit: contain;
        width: 36px;
    }

    .pwa-install-card__copy {
        display: grid;
        gap: 2px;
        min-width: 0;
    }

    .pwa-install-card__copy strong {
        color: #101427;
        font-size: 0.95rem;
        font-weight: 900;
        line-height: 1.15;
    }

    .pwa-install-card__copy span {
        color: #697083;
        font-size: 0.78rem;
        line-height: 1.25;
    }

    .pwa-install-card__button,
    .pwa-install-card__close {
        align-items: center;
        border: 0;
        display: inline-flex;
        justify-content: center;
    }

    .pwa-install-card__button {
        background: linear-gradient(135deg, #ff7a18, #ff2f6d);
        border-radius: 999px;
        box-shadow: 0 16px 30px rgba(255, 78, 69, 0.35);
        color: #fff;
        font-size: 0.82rem;
        font-weight: 900;
        gap: 7px;
        min-height: 38px;
        padding: 0 14px;
        position: relative;
        white-space: nowrap;
        animation: pwa-install-button-pulse 1.8s ease-in-out infinite;
        isolation: isolate;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .pwa-install-card__button::after {
        border: 2px solid rgba(255, 122, 24, 0.42);
        border-radius: inherit;
        content: "";
        inset: -6px;
        opacity: 0;
        position: absolute;
        z-index: -1;
        animation: pwa-install-button-ring 1.8s ease-out infinite;
    }

    .pwa-install-card__button:hover {
        box-shadow: 0 18px 34px rgba(255, 78, 69, 0.44);
        transform: translateY(-1px);
    }

    .pwa-install-card__button:focus-visible {
        outline: 3px solid rgba(255, 190, 70, 0.72);
        outline-offset: 3px;
    }

    .pwa-install-card__close {
        background: rgba(16, 20, 39, 0.06);
        border-radius: 12px;
        color: #697083;
        height: 34px;
        width: 34px;
    }

    @media (max-width: 575.98px) {
        .pwa-install-card {
            bottom: 14px;
            grid-template-columns: 40px minmax(0, 1fr) 34px;
        }

        .pwa-install-card__button {
            grid-column: 1 / -1;
            width: 100%;
        }

        .pwa-install-card__close {
            grid-column: 3;
            grid-row: 1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .pwa-install-card,
        .pwa-install-card__button,
        .pwa-install-card__button::after {
            animation: none;
        }

        .pwa-install-card__button:hover {
            transform: none;
        }
    }

    @keyframes pwa-install-card-pop {
        0% {
            opacity: 0;
            transform: translateY(14px) scale(0.98);
        }

        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes pwa-install-button-pulse {
        0%,
        100% {
            transform: translateY(0) scale(1);
        }

        50% {
            transform: translateY(-1px) scale(1.035);
        }
    }

    @keyframes pwa-install-button-ring {
        0% {
            opacity: 0.72;
            transform: scale(0.94);
        }

        70%,
        100% {
            opacity: 0;
            transform: scale(1.18);
        }
    }
</style>

<script>
    (function () {
        'use strict';

        var installPromptEvent = null;
        var installCard = document.querySelector('[data-pwa-install]');
        var installButton = document.querySelector('[data-pwa-install-button]');
        var closeButton = document.querySelector('[data-pwa-install-close]');
        var installText = document.querySelector('[data-pwa-install-text]');
        function isStandalone() {
            return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        }

        function hasInstallCompleted() {
            return isStandalone();
        }

        function hideInstallOption() {
            if (installCard) {
                installCard.hidden = true;
            }
        }

        function showInstallOption() {
            if (installCard && !hasInstallCompleted()) {
                installCard.hidden = false;
            }
        }

        if (!installCard || hasInstallCompleted()) {
            hideInstallOption();
            return;
        }

        showInstallOption();

        window.addEventListener('beforeinstallprompt', function (event) {
            event.preventDefault();
            installPromptEvent = event;
            if (installText) {
                installText.textContent = 'Open PMS faster from your device.';
            }
            showInstallOption();
        });

        window.addEventListener('appinstalled', function () {
            installPromptEvent = null;
            hideInstallOption();
        });

        if (installButton) {
            installButton.addEventListener('click', function () {
                if (!installPromptEvent) {
                    if (installText) {
                        installText.textContent = 'Use your browser menu and choose Install app or Add to home screen.';
                    }
                    return;
                }

                installPromptEvent.prompt();
                installPromptEvent.userChoice.then(function (choice) {
                    installPromptEvent = null;
                    if (choice.outcome === 'accepted') {
                        hideInstallOption();
                    } else {
                        showInstallOption();
                    }
                });
            });
        }

        if (closeButton) {
            closeButton.addEventListener('click', hideInstallOption);
        }
    })();
</script>
