{{-- "Create New Recruitment Requirement" modal: Bitroxia look, light and dark themes. Styles only; no behaviour. --}}
<style>
    #createRequirementModal {
        --rcm-surface: #ffffff;
        --rcm-soft: #f6f7fc;
        --rcm-field: #fbfcff;
        --rcm-ink: #10142c;
        --rcm-muted: #545d82;
        --rcm-faint: #848cb0;
        --rcm-border: rgba(16, 20, 44, .12);
        --rcm-border-soft: rgba(16, 20, 44, .07);
        --rcm-primary: #2f6bff;
        --rcm-violet: #8b5cf6;
        --rcm-primary-soft: rgba(47, 107, 255, .09);
        --rcm-focus: rgba(47, 107, 255, .18);
        --rcm-gradient: linear-gradient(135deg, #2f6bff 0%, #6d5dfc 55%, #8b5cf6 100%);
        --rcm-shadow: 0 24px 60px -12px rgba(15, 23, 42, .28);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark, body.dark-mode) #createRequirementModal {
        --rcm-surface: #0f1530;
        --rcm-soft: #141b3d;
        --rcm-field: #111735;
        --rcm-ink: #eef1fb;
        --rcm-muted: #9aa3c7;
        --rcm-faint: #6b739a;
        --rcm-border: rgba(238, 241, 251, .14);
        --rcm-border-soft: rgba(238, 241, 251, .08);
        --rcm-primary-soft: rgba(47, 107, 255, .16);
        --rcm-focus: rgba(47, 107, 255, .35);
        --rcm-shadow: 0 24px 60px -12px rgba(0, 0, 0, .6);
    }

    /* Shell: header and footer stay visible, only the body scrolls. */
    #createRequirementModal .modal-dialog { max-width: 860px; }
    #createRequirementModal .modal-content { max-height: calc(100vh - 3rem); border-radius: 22px !important; overflow: hidden; background: var(--rcm-surface) !important; box-shadow: var(--rcm-shadow) !important; }
    #createRequirementModal .modal-content > form { display: flex; flex-direction: column; min-height: 0; max-height: calc(100vh - 3rem); }
    #createRequirementModal .modal-body { flex: 1 1 auto; min-height: 0; overflow-y: auto; padding: 22px 26px 8px !important; background: var(--rcm-surface) !important; scrollbar-width: thin; }

    /* Header */
    #createRequirementModal .rcm-header { position: relative; display: flex; align-items: center; gap: 14px; padding: 20px 24px !important; border: 0 !important; background: var(--rcm-gradient) !important; overflow: hidden; }
    #createRequirementModal .rcm-header::after { content: ''; position: absolute; right: -60px; top: -80px; width: 240px; height: 240px; border-radius: 50%; background: radial-gradient(circle, rgba(255, 255, 255, .22), transparent 65%); pointer-events: none; }
    #createRequirementModal .rcm-header-icon { position: relative; z-index: 1; width: 46px; height: 46px; flex-shrink: 0; border-radius: 14px; display: grid; place-items: center; background: rgba(255, 255, 255, .18); border: 1px solid rgba(255, 255, 255, .28); color: #fff; }
    #createRequirementModal .rcm-header-icon svg { width: 22px; height: 22px; color: #fff !important; stroke: #fff; }
    #createRequirementModal .rcm-header-text { position: relative; z-index: 1; flex: 1; min-width: 0; }
    #createRequirementModal .rcm-header .modal-title { margin: 0; font-size: 1.12rem; font-weight: 800; letter-spacing: -.01em; color: #fff !important; -webkit-text-fill-color: #fff !important; }
    #createRequirementModal .rcm-header-text p { margin: 3px 0 0; font-size: .84rem; color: rgba(255, 255, 255, .85) !important; -webkit-text-fill-color: rgba(255, 255, 255, .85) !important; }
    #createRequirementModal .rcm-header .btn-close { position: relative; z-index: 1; width: 34px; height: 34px; padding: 0 !important; margin: 0 !important; border-radius: 10px; background-color: rgba(255, 255, 255, .16); background-size: 12px; opacity: 1; transition: background-color .15s; }
    #createRequirementModal .rcm-header .btn-close:hover { background-color: rgba(255, 255, 255, .28); }

    /* Broadcast notice */
    #createRequirementModal .rec-notification-alert { display: flex; gap: 12px; align-items: flex-start !important; padding: 12px 14px !important; margin-bottom: 18px !important; border: 1px solid rgba(47, 107, 255, .2) !important; border-radius: 14px !important; background: var(--rcm-primary-soft) !important; font-size: .84rem; line-height: 1.5; color: var(--rcm-muted) !important; -webkit-text-fill-color: var(--rcm-muted) !important; }
    #createRequirementModal .rec-notification-alert div { color: inherit !important; -webkit-text-fill-color: currentColor !important; }
    #createRequirementModal .rec-notification-alert :is(i, .rec-notification-alert-icon) { margin: 0 !important; font-size: 1.35rem !important; line-height: 1; color: var(--rcm-primary) !important; -webkit-text-fill-color: var(--rcm-primary) !important; }
    #createRequirementModal .rec-notification-alert :is(strong, .rec-alert-heading) { color: var(--rcm-ink) !important; -webkit-text-fill-color: var(--rcm-ink) !important; }

    /* Numbered sections */
    #createRequirementModal .rcm-section { display: flex; align-items: center; gap: 12px; margin-top: 10px; padding-bottom: 10px; border-bottom: 1px dashed var(--rcm-border); }
    #createRequirementModal .row > .rcm-section:first-child { margin-top: 0; }
    #createRequirementModal .rcm-step { width: 28px; height: 28px; flex-shrink: 0; border-radius: 9px; display: grid; place-items: center; background: var(--rcm-gradient); color: #fff !important; -webkit-text-fill-color: #fff !important; font-size: .8rem; font-weight: 800; box-shadow: 0 6px 14px rgba(47, 107, 255, .25); }
    #createRequirementModal .rcm-section h6 { margin: 0; font-size: .92rem; font-weight: 800; color: var(--rcm-ink) !important; -webkit-text-fill-color: var(--rcm-ink) !important; }
    #createRequirementModal .rcm-section p { margin: 1px 0 0; font-size: .78rem; color: var(--rcm-faint) !important; -webkit-text-fill-color: var(--rcm-faint) !important; }

    /* Fields */
    #createRequirementModal .modal-body > .row > [class*="col-"] > .form-label,
    #createRequirementModal .modal-body > .row > [class*="col-"] > label.form-label { margin-bottom: 6px; font-size: .8rem; font-weight: 700 !important; letter-spacing: .01em; color: var(--rcm-muted) !important; -webkit-text-fill-color: var(--rcm-muted) !important; }
    #createRequirementModal .modal-body > .row sup.text-danger { color: #ef4444 !important; -webkit-text-fill-color: #ef4444 !important; }
    #createRequirementModal .modal-body > .row > [class*="col-"] :is(.form-control, .form-select):not(.panel-input) {
        min-height: 44px; border: 1px solid var(--rcm-border) !important; border-radius: 12px !important; background-color: var(--rcm-field) !important;
        color: var(--rcm-ink) !important; -webkit-text-fill-color: var(--rcm-ink) !important; font-size: .9rem; box-shadow: none !important;
        transition: border-color .15s, box-shadow .15s, background-color .15s;
    }
    #createRequirementModal .modal-body > .row > [class*="col-"] textarea.form-control { min-height: 96px; padding: 11px 14px; line-height: 1.55; resize: vertical; }
    #createRequirementModal .modal-body > .row > [class*="col-"] :is(.form-control, .form-select):not(.panel-input):hover { border-color: rgba(47, 107, 255, .35) !important; }
    #createRequirementModal .modal-body > .row > [class*="col-"] :is(.form-control, .form-select):not(.panel-input):focus { border-color: var(--rcm-primary) !important; background-color: var(--rcm-surface) !important; box-shadow: 0 0 0 4px var(--rcm-focus) !important; }
    #createRequirementModal .modal-body > .row > [class*="col-"] :is(.form-control, .form-select).is-invalid { border-color: #ef4444 !important; }
    #createRequirementModal .modal-body :is(.form-control, .form-select)::placeholder { color: var(--rcm-faint) !important; -webkit-text-fill-color: var(--rcm-faint) !important; opacity: 1; }
    #createRequirementModal .modal-body > .row small.text-muted { font-size: .75rem; color: var(--rcm-faint) !important; -webkit-text-fill-color: var(--rcm-faint) !important; }

    /* Joined controls: department / location with a "+" button, and the CTC range */
    #createRequirementModal .modal-body > .row .input-group > :is(.form-control, .form-select):not(:last-child) { border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important; }
    #createRequirementModal .modal-body > .row .input-group > :is(.form-control, .form-select):not(:first-child) { border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important; }
    #createRequirementModal .modal-body > .row .input-group > .btn-outline-success { min-width: 46px; justify-content: center; border: 1px solid var(--rcm-border) !important; border-left: 0 !important; border-radius: 0 12px 12px 0 !important; background: var(--rcm-primary-soft) !important; color: var(--rcm-primary) !important; -webkit-text-fill-color: var(--rcm-primary) !important; transition: background .15s, color .15s; }
    #createRequirementModal .modal-body > .row .input-group > .btn-outline-success i { color: inherit !important; -webkit-text-fill-color: currentColor !important; }
    #createRequirementModal .modal-body > .row .input-group > .btn-outline-success:hover { background: var(--rcm-primary) !important; color: #fff !important; -webkit-text-fill-color: #fff !important; }
    #createRequirementModal .recruitment-salary-ctc .input-group-text { min-height: 44px; border: 1px solid var(--rcm-border) !important; background: var(--rcm-soft) !important; color: var(--rcm-muted) !important; -webkit-text-fill-color: var(--rcm-muted) !important; font-size: .82rem; }
    #createRequirementModal .recruitment-salary-ctc .input-group-text:last-child { border-radius: 0 12px 12px 0 !important; background: var(--rcm-primary-soft) !important; color: var(--rcm-primary) !important; -webkit-text-fill-color: var(--rcm-primary) !important; font-weight: 800; }
    #createRequirementModal .recruitment-salary-ctc .form-control { border-radius: 0 !important; }
    #createRequirementModal .recruitment-salary-ctc .form-control:first-child { border-radius: 12px 0 0 12px !important; }

    /* Quick-add panels keep their own colours; just soften their frame */
    #createRequirementModal .quick-add-dept-panel { border-radius: 14px !important; }

    /* Footer */
    #createRequirementModal .rcm-footer { display: flex; align-items: center; gap: 10px; padding: 14px 26px !important; border-top: 1px solid var(--rcm-border-soft) !important; background: var(--rcm-soft) !important; }
    #createRequirementModal .rcm-footer-note { margin-right: auto; font-size: .76rem; color: var(--rcm-faint) !important; -webkit-text-fill-color: var(--rcm-faint) !important; }
    #createRequirementModal .rcm-cancel { height: 44px; padding: 0 20px !important; border: 1px solid var(--rcm-border) !important; border-radius: 12px !important; background: var(--rcm-surface) !important; color: var(--rcm-ink) !important; -webkit-text-fill-color: var(--rcm-ink) !important; font-weight: 600; box-shadow: none !important; }
    #createRequirementModal .rcm-cancel:hover { border-color: var(--rcm-primary) !important; color: var(--rcm-primary) !important; -webkit-text-fill-color: var(--rcm-primary) !important; }
    #createRequirementModal .rcm-submit { display: inline-flex !important; align-items: center; gap: 6px; height: 44px; border: 0 !important; border-radius: 12px !important; background: var(--rcm-gradient) !important; color: #fff !important; -webkit-text-fill-color: #fff !important; font-weight: 700; box-shadow: 0 10px 22px -6px rgba(47, 107, 255, .55) !important; transition: transform .15s, filter .15s; }
    #createRequirementModal .rcm-submit i { margin: 0 !important; color: #fff !important; -webkit-text-fill-color: #fff !important; }
    #createRequirementModal .rcm-submit:hover { transform: translateY(-1px); filter: brightness(1.06); }
    #createRequirementModal :is(.rcm-submit, .rcm-cancel, .btn-close):focus-visible { outline: 3px solid var(--rcm-focus); outline-offset: 2px; box-shadow: none !important; }

    @media (max-width: 767.98px) {
        #createRequirementModal .modal-dialog { margin: .75rem; }
        #createRequirementModal .rcm-header { padding: 16px 18px !important; }
        #createRequirementModal .rcm-header-text p { display: none; }
        #createRequirementModal .modal-body { padding: 18px 18px 6px !important; }
        #createRequirementModal .rcm-footer { flex-wrap: wrap; padding: 12px 18px !important; }
        #createRequirementModal .rcm-footer-note { width: 100%; }
        #createRequirementModal :is(.rcm-cancel, .rcm-submit) { flex: 1; justify-content: center; }
    }
    @media (prefers-reduced-motion: reduce) {
        #createRequirementModal .rcm-submit:hover { transform: none; }
    }
</style>
