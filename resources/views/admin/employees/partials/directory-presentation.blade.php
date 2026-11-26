<style>
    #employeeDirectoryPage {
        --dir-surface: #fff;
        --dir-subtle: #f5f8fd;
        --dir-border: #dce5f1;
        --dir-control-border: #cad6e7;
        --dir-ink: #14223a;
        --dir-muted: #52637b;
        --dir-accent: #2457d6;
        --dir-accent-soft: #eaf0ff;
        --dir-primary: #2457d6;
        --dir-primary-hover: #1945b7;
        --dir-green: #137253;
        --dir-green-soft: #e8f7f0;
        --dir-red: #a43749;
        --dir-red-soft: #feeff1;
        --dir-amber: #8c550a;
        --dir-amber-soft: #fff4de;
        --dir-selected: #edf3ff;
        --dir-shadow: 0 2px 4px rgb(20 34 58 / 3%), 0 8px 24px rgb(20 34 58 / 5%);
        --dir-gap: clamp(14px, 1.4vw, 20px);
        --dir-padding: clamp(14px, 1.7vw, 24px);
        --emp-designation-color: var(--dir-muted);
        --emp-role-color: var(--dir-ink);
        --emp-reports-color: var(--dir-muted);
        --emp-footer-text: var(--dir-muted);
        --emp-footer-select-bg: var(--dir-surface);
        --emp-footer-select-color: var(--dir-ink);
        --emp-footer-select-border: var(--dir-control-border);
        border: 0 !important;
        color: var(--dir-ink) !important;
    }

    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) #employeeDirectoryPage {
        --dir-surface: #121e32;
        --dir-subtle: #17253d;
        --dir-border: #2a3a56;
        --dir-control-border: #405375;
        --dir-ink: #edf3ff;
        --dir-muted: #a8b8cf;
        --dir-accent: #a8c1ff;
        --dir-accent-soft: #21355b;
        --dir-primary: #2f61dc;
        --dir-primary-hover: #2754c5;
        --dir-green: #8de2be;
        --dir-green-soft: #163a36;
        --dir-red: #ffafba;
        --dir-red-soft: #3d2639;
        --dir-amber: #ffd18a;
        --dir-amber-soft: #3b302a;
        --dir-selected: #203351;
        --dir-shadow: 0 8px 24px rgb(0 0 0 / 16%);
    }

    #employeeDirectoryPage::before {
        display: none;
    }

    #employeeDirectoryPage .breadcrumb {
        align-items: center;
        margin: 0;
    }

    #employeeDirectoryPage .breadcrumb :is(a, .breadcrumb-item) {
        color: var(--dir-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .8125rem;
    }

    #employeeDirectoryPage .breadcrumb a {
        gap: 7px;
    }

    #employeeDirectoryPage .breadcrumb a:hover,
    #employeeDirectoryPage .breadcrumb .breadcrumb-item.active {
        color: var(--dir-ink) !important;
    }

    #employeeDirectoryPage .breadcrumb-item + .breadcrumb-item::before {
        content: "\203A";
        margin: 0 8px 0 0;
        padding: 0;
        color: var(--dir-muted) !important;
    }

    #employeeDirectoryPage .directory-hero {
        --dir-hero-muted: #d0ddf5;
        position: relative;
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px 28px;
        margin-bottom: var(--dir-gap);
        padding: clamp(18px, 2vw, 30px);
        border: 1px solid #355688;
        border-radius: 20px;
        background:
            radial-gradient(circle at 92% 110%, transparent 0 118px, rgb(165 193 255 / 17%) 119px 120px, transparent 121px 152px, rgb(165 193 255 / 11%) 153px 154px, transparent 155px),
            linear-gradient(112deg, #122544 20%, #162f59 61%, #234d8a);
        box-shadow: 0 8px 24px rgb(18 37 68 / 16%);
    }

    #employeeDirectoryPage .directory-hero-intro,
    #employeeDirectoryPage .directory-panel-title {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    #employeeDirectoryPage .directory-hero-icon,
    #employeeDirectoryPage .directory-metric-icon,
    #employeeDirectoryPage .directory-panel-icon {
        display: grid;
        flex: 0 0 auto;
        place-items: center;
        line-height: 1;
    }

    #employeeDirectoryPage .directory-hero-icon {
        width: clamp(44px, 5vw, 62px);
        aspect-ratio: 1;
        border: 1px solid rgb(209 225 255 / 27%);
        border-radius: 16px;
        background: rgb(124 163 236 / 19%);
        color: #fff;
        font-size: clamp(18px, 2.1vw, 25px);
    }

    #employeeDirectoryPage .directory-hero h1 {
        margin: 0;
        color: #fff !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: clamp(1.35rem, 2.65vw, 2.05rem);
        font-weight: 750 !important;
        letter-spacing: -.035em;
        line-height: 1.12;
    }

    #employeeDirectoryPage .directory-hero p {
        margin: 6px 0 0;
        color: var(--dir-hero-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: clamp(.8125rem, 1.1vw, .9375rem);
        font-weight: 450 !important;
    }

    #employeeDirectoryPage :is(.directory-hero-actions, .directory-export, .directory-filter-actions, .directory-export-formats) {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    #employeeDirectoryPage .directory-button {
        --dir-button-bg: var(--dir-surface);
        --dir-button-text: var(--dir-ink);
        --dir-button-border: var(--dir-control-border);
        --dir-button-hover: var(--dir-subtle);
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px !important;
        padding: 8px 13px !important;
        border: 1px solid var(--dir-button-border) !important;
        border-radius: 10px !important;
        background: var(--dir-button-bg) !important;
        color: var(--dir-button-text) !important;
        -webkit-text-fill-color: currentColor !important;
        box-shadow: none !important;
        font-size: .8125rem !important;
        font-weight: 650 !important;
        line-height: 1.2 !important;
        white-space: nowrap;
        text-decoration: none;
        transition: background-color .16s ease, border-color .16s ease;
    }

    #employeeDirectoryPage .directory-button:hover,
    #employeeDirectoryPage .directory-button.show {
        background: var(--dir-button-hover) !important;
        transform: none !important;
    }

    #employeeDirectoryPage :is(.directory-button, .directory-quick-menu a, .breadcrumb a, .company-switch-card, .form-select, .select2-selection):focus-visible {
        outline: 2px solid var(--dir-accent) !important;
        outline-offset: 2px;
    }

    #employeeDirectoryPage .directory-hero .directory-button {
        --dir-button-bg: rgb(234 242 255 / 8%);
        --dir-button-text: #f4f7ff;
        --dir-button-border: rgb(209 225 255 / 32%);
        --dir-button-hover: rgb(234 242 255 / 18%);
    }

    #employeeDirectoryPage .directory-hero .directory-button:focus-visible {
        outline-color: #bcd1ff !important;
    }

    #employeeDirectoryPage .directory-button.directory-button-primary {
        --dir-button-bg: var(--dir-primary);
        --dir-button-text: #fff;
        --dir-button-border: var(--dir-primary);
        --dir-button-hover: var(--dir-primary-hover);
    }

    #employeeDirectoryPage .directory-hero .directory-button-primary {
        --dir-button-bg: #f2f6ff;
        --dir-button-text: #193b78;
        --dir-button-border: #f2f6ff;
        --dir-button-hover: #dce8ff;
    }

    #employeeDirectoryPage .directory-hero-actions .dropdown {
        min-width: 0;
    }

    #employeeDirectoryPage .directory-hero .dropdown-toggle::after {
        color: inherit !important;
        border-top-color: currentColor !important;
        margin-left: 0;
    }

    #employeeDirectoryPage .directory-quick-menu {
        top: calc(100% + 6px) !important;
        right: auto !important;
        bottom: auto !important;
        left: 0 !important;
        margin: 0 !important;
        min-width: 210px;
        padding: 5px !important;
        border: 1px solid var(--dir-border) !important;
        border-radius: 12px !important;
        background: var(--dir-surface) !important;
        box-shadow: var(--dir-shadow) !important;
        transform: none !important;
    }

    #employeeDirectoryPage .directory-quick-menu .dropdown-item {
        padding: 9px 10px;
        border-radius: 7px;
        color: var(--dir-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .8125rem;
        font-weight: 650;
    }

    #employeeDirectoryPage .directory-quick-menu .dropdown-item:is(:hover, :focus-visible) {
        background: var(--dir-accent-soft) !important;
    }

    #employeeDirectoryPage .directory-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: var(--dir-gap);
        margin: 0 0 var(--dir-gap);
    }

    #employeeDirectoryPage .directory-metric {
        --dir-metric-ink: var(--dir-accent);
        --dir-metric-bg: var(--dir-accent-soft);
        position: relative;
        display: grid;
        align-content: center;
        gap: 6px;
        min-width: 0;
        padding: clamp(12px, 1.5vw, 22px);
        border: 1px solid var(--dir-border);
        border-radius: 16px;
        background: var(--dir-surface);
        box-shadow: var(--dir-shadow);
    }

    #employeeDirectoryPage .directory-metric-active { --dir-metric-ink: var(--dir-green); --dir-metric-bg: var(--dir-green-soft); }
    #employeeDirectoryPage .directory-metric-inactive { --dir-metric-ink: var(--dir-red); --dir-metric-bg: var(--dir-red-soft); }
    #employeeDirectoryPage .directory-metric-notice { --dir-metric-ink: var(--dir-amber); --dir-metric-bg: var(--dir-amber-soft); }

    #employeeDirectoryPage .directory-metric dt {
        color: var(--dir-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .8125rem;
        font-weight: 650;
        line-height: 1.25;
    }

    #employeeDirectoryPage .directory-metric dd {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        margin: 0;
        color: var(--dir-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: clamp(1.6rem, 3.2vw, 2.25rem);
        font-weight: 750;
        font-variant-numeric: tabular-nums;
        letter-spacing: -.045em;
        line-height: 1;
        overflow-wrap: anywhere;
    }

    #employeeDirectoryPage .directory-metric-icon {
        width: clamp(32px, 3.6vw, 46px);
        aspect-ratio: 1;
        border-radius: 12px;
        background: var(--dir-metric-bg);
        color: var(--dir-metric-ink) !important;
        font-size: clamp(14px, 1.5vw, 18px);
    }

    #employeeDirectoryPage :is(.directory-hero-icon, .directory-button, .directory-metric-icon, .directory-panel-icon) > i,
    #employeeDirectoryPage :is(.directory-hero-icon, .directory-button, .directory-metric-icon, .directory-panel-icon) > i::before {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        filter: none !important;
        font-size: inherit;
    }

    #employeeDirectoryPage .company-switch-card {
        border-radius: 14px !important;
        border: 1px solid var(--dir-border) !important;
        background: var(--dir-surface) !important;
        box-shadow: var(--dir-shadow) !important;
    }

    #employeeDirectoryPage .company-switch-card:is(.active, :hover) {
        border-color: var(--dir-accent) !important;
        background: var(--dir-subtle) !important;
        transform: none;
    }

    #employeeDirectoryPage .company-switch-card strong {
        color: var(--dir-ink) !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #employeeDirectoryPage .company-switch-card small {
        color: var(--dir-muted) !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #employeeDirectoryPage :is(.filter-export-card, .table-card) {
        margin: 0 0 var(--dir-gap) !important;
        border: 1px solid var(--dir-border) !important;
        border-radius: 18px !important;
        background: var(--dir-surface) !important;
        box-shadow: var(--dir-shadow) !important;
        backdrop-filter: none;
        transform: none !important;
    }

    #employeeDirectoryPage .filter-export-card {
        overflow: visible !important;
    }

    #employeeDirectoryPage .directory-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px 16px;
        padding: 15px var(--dir-padding);
        border-bottom: 1px solid var(--dir-border);
    }

    #employeeDirectoryPage .directory-panel-title {
        flex-wrap: wrap;
        gap: 10px;
    }

    #employeeDirectoryPage .directory-panel-title h2 {
        margin: 0;
        color: var(--dir-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: 1.0625rem !important;
        font-weight: 750 !important;
        letter-spacing: -.015em;
    }

    #employeeDirectoryPage .directory-panel-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--dir-accent-soft);
        color: var(--dir-accent) !important;
        font-size: 15px;
    }

    #employeeDirectoryPage .directory-count,
    #employeeDirectoryPage #export-selected-count {
        display: inline-flex;
        align-items: center;
        min-height: 25px;
        padding: 3px 9px;
        border: 1px solid var(--dir-border) !important;
        border-radius: 999px;
        background: var(--dir-subtle) !important;
        color: var(--dir-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .75rem;
        font-weight: 650 !important;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    #employeeDirectoryPage .directory-export-formats {
        gap: 2px;
        padding-left: 8px;
        border-left: 1px solid var(--dir-border);
    }

    #employeeDirectoryPage .directory-export-formats .directory-button {
        --dir-button-text: var(--dir-accent);
        --dir-button-border: transparent;
        width: 38px !important;
        min-width: 38px !important;
        min-height: 38px !important;
        padding: 0 !important;
    }

    #employeeDirectoryPage .directory-export-formats .directory-button:disabled {
        --dir-button-text: var(--dir-muted);
        opacity: .55 !important;
    }

    #employeeDirectoryPage .directory-filter-body {
        padding: 16px var(--dir-padding) 18px;
    }

    #employeeDirectoryPage .filter-form {
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        overflow: visible !important;
    }

    #employeeDirectoryPage .directory-filter-fields {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        align-items: end;
        gap: 13px;
    }

    #employeeDirectoryPage .directory-filter-field {
        min-width: 0;
    }

    #employeeDirectoryPage .directory-filter-field label {
        display: flex !important;
        align-items: center;
        gap: 7px;
        margin-bottom: 6px;
        color: var(--dir-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .8125rem;
        font-weight: 650 !important;
    }

    #employeeDirectoryPage :is(.breadcrumb, .directory-filter-field, .directory-quick-menu) i {
        color: var(--dir-accent) !important;
        -webkit-text-fill-color: currentColor !important;
        filter: none !important;
    }

    #employeeDirectoryPage .directory-quick-menu .text-danger {
        color: var(--dir-red) !important;
    }

    #employeeDirectoryPage .directory-filter-field :is(.form-select, .select2-selection--single) {
        width: 100% !important;
        height: 42px !important;
        min-height: 42px !important;
        border: 1px solid var(--dir-control-border) !important;
        border-radius: 10px !important;
        background: var(--dir-subtle) !important;
        color: var(--dir-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        box-shadow: none !important;
        font-size: .875rem !important;
        font-weight: 500 !important;
    }

    #employeeDirectoryPage .directory-filter-field .form-select {
        appearance: none;
        padding: 0 33px 0 11px !important;
        background-image: linear-gradient(45deg, transparent 50%, var(--dir-muted) 50%), linear-gradient(135deg, var(--dir-muted) 50%, transparent 50%) !important;
        background-size: 5px 5px !important;
        background-position: right 16px center, right 11px center !important;
        background-repeat: no-repeat !important;
    }

    #employeeDirectoryPage .directory-filter-field .select2-container {
        width: 100% !important;
        max-width: 100%;
    }

    #employeeDirectoryPage .directory-filter-field .select2-selection__rendered {
        padding: 0 32px 0 11px !important;
        color: var(--dir-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .875rem !important;
        font-weight: 500 !important;
        line-height: 40px !important;
    }

    #employeeDirectoryPage .directory-filter-field .select2-selection__arrow {
        height: 40px !important;
        right: 6px !important;
    }

    #employeeDirectoryPage .directory-filter-field .select2-container--open .select2-selection {
        border-color: var(--dir-accent) !important;
    }

    #employeeDirectoryPage #btn-bulk-delete {
        --dir-button-bg: var(--dir-red-soft);
        --dir-button-text: var(--dir-red);
        --dir-button-border: var(--dir-red);
        --dir-button-hover: var(--dir-surface);
        background: var(--dir-button-bg) !important;
        border-color: var(--dir-button-border) !important;
        border-radius: 10px !important;
        color: var(--dir-button-text) !important;
        -webkit-text-fill-color: currentColor !important;
        box-shadow: none !important;
        min-height: 42px !important;
        font-weight: 650 !important;
    }

    #employeeDirectoryPage #btn-bulk-delete:hover {
        background: var(--dir-button-hover) !important;
    }

    #employeeDirectoryPage #btn-bulk-delete.is-awaiting-selection {
        --dir-button-bg: var(--dir-subtle);
        --dir-button-text: var(--dir-muted);
        --dir-button-border: var(--dir-border);
        opacity: 1 !important;
    }

    #employeeDirectoryPage .table-card .table-responsive {
        padding: 0 !important;
        border-radius: 0 !important;
        background: var(--dir-surface) !important;
    }

    #employeeDirectoryPage #employeeTable.employee-list-table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        --bs-table-bg: transparent;
        --bs-table-accent-bg: transparent;
    }

    #employeeDirectoryPage #employeeTable thead th {
        padding: 13px 14px !important;
        border: 0 !important;
        border-bottom: 1px solid var(--dir-control-border) !important;
        background: var(--dir-subtle) !important;
        color: var(--dir-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .72rem !important;
        font-weight: 700 !important;
        letter-spacing: .04em !important;
    }

    #employeeDirectoryPage #employeeTable tbody tr {
        --dir-row-base: var(--dir-surface);
        --dir-row-bg: var(--dir-row-base);
        background: var(--dir-row-bg) !important;
    }

    #employeeDirectoryPage #employeeTable tbody tr:nth-child(even) {
        --dir-row-base: color-mix(in srgb, var(--dir-subtle) 48%, var(--dir-surface));
    }

    #employeeDirectoryPage #employeeTable tbody tr.table-warning {
        --dir-row-base: color-mix(in srgb, var(--dir-amber-soft) 50%, var(--dir-surface));
    }

    #employeeDirectoryPage #employeeTable tbody tr:is(:hover, :focus-within) {
        --dir-row-bg: color-mix(in srgb, var(--dir-accent-soft) 45%, var(--dir-row-base));
    }

    #employeeDirectoryPage #employeeTable tbody tr:is(.selected, .pms-row-selected, :has(.employee-checkbox:checked)) {
        --dir-row-bg: var(--dir-selected);
    }

    #employeeDirectoryPage #employeeTable tbody td {
        background: var(--dir-row-bg) !important;
        color: var(--dir-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        box-shadow: none !important;
        font-weight: 500 !important;
    }

    #employeeDirectoryPage #employeeTable .employee-name {
        color: var(--dir-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .9375rem !important;
        font-weight: 750 !important;
        letter-spacing: -.01em !important;
    }

    #employeeDirectoryPage #employeeTable :is(.employee-name, .employee-company-name, .email-link, .employee-designation, .role-name, .reports-to) {
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        line-height: 1.35 !important;
    }

    #employeeDirectoryPage #employeeTable :is(.employee-company-chip, .employee-details, .email-link) {
        min-width: 0 !important;
        max-width: 100% !important;
    }

    #employeeDirectoryPage #employeeTable :is(.employee-company-logo, .email-icon, .reports-to > i) {
        flex: 0 0 auto;
    }

    #employeeDirectoryPage #employeeTable :is(.employee-designation, .reports-to) {
        font-weight: 500 !important;
    }

    #employeeDirectoryPage #employeeTable :is(.employee-id-badge, .profile-placeholder, .btn-action-dots) {
        border: 1px solid var(--dir-border) !important;
        border-radius: 9px !important;
        background: var(--dir-subtle) !important;
        color: var(--dir-accent) !important;
        -webkit-text-fill-color: currentColor !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage #employeeTable .employee-id-badge {
        padding: 5px 7px !important;
        font-size: .75rem !important;
        font-variant-numeric: tabular-nums;
    }

    #employeeDirectoryPage #employeeTable .employee-badges .badge {
        padding: 4px 7px !important;
        box-shadow: none !important;
        font-size: .6875rem !important;
        letter-spacing: 0 !important;
    }

    #employeeDirectoryPage #employeeTable .status-badge {
        padding-inline: 8px !important;
        box-shadow: none !important;
        white-space: nowrap !important;
    }

    #employeeDirectoryPage #employeeTable .status-badge .status-text {
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        font-size: inherit !important;
        font-weight: inherit !important;
    }

    #employeeDirectoryPage #employeeTable .form-check {
        padding: 0 !important;
    }

    #employeeDirectoryPage #employeeTable :is(.profile-initials, .email-link),
    #employeeDirectoryPage #employeeTable .btn-action-dots > i,
    #employeeDirectoryPage #employeeTable .btn-action-dots > i::before {
        color: var(--dir-accent) !important;
        -webkit-text-fill-color: currentColor !important;
        filter: none !important;
    }

    #employeeDirectoryPage #employeeTable .email-link {
        font-weight: 550 !important;
    }

    #employeeDirectoryPage #employeeTable .btn-action-dots:is(:hover, [aria-expanded="true"]) {
        border-color: var(--dir-control-border) !important;
        background: var(--dir-accent-soft) !important;
        transform: none !important;
    }

    #employeeDirectoryPage #employeeTable :is(.form-check-input, .email-link, .btn-action-dots):focus-visible {
        outline: 2px solid var(--dir-accent) !important;
        outline-offset: 2px;
    }

    #employeeDirectoryPage .empty-state {
        display: grid;
        justify-items: center;
        gap: 8px;
        padding: clamp(24px, 5vw, 60px) 12px;
    }

    #employeeDirectoryPage .empty-state .directory-panel-icon {
        width: 48px;
        height: 48px;
        font-size: 19px;
    }

    #employeeDirectoryPage .empty-state :is(h3, p) {
        margin: 0;
        -webkit-text-fill-color: currentColor !important;
    }

    #employeeDirectoryPage .empty-state h3 {
        color: var(--dir-ink) !important;
        font-size: 1rem;
        font-weight: 750 !important;
    }

    #employeeDirectoryPage .empty-state p {
        margin-bottom: 6px;
        color: var(--dir-muted) !important;
        font-size: .875rem;
        font-weight: 500 !important;
    }

    #employeeDirectoryPage .table-footer {
        border-top: 1px solid var(--dir-border) !important;
        background: var(--dir-subtle) !important;
    }

    #employeeDirectoryPage .table-footer .page-btn {
        border-color: var(--dir-control-border) !important;
        background: var(--dir-surface) !important;
        color: var(--dir-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .table-footer .page-btn:is(.active, :not(.disabled):hover) {
        border-color: var(--dir-accent) !important;
        background: var(--dir-accent-soft) !important;
        color: var(--dir-accent) !important;
        transform: none;
    }

    @media (min-width: 768px) {
        #employeeDirectoryPage #employeeTable.employee-list-table {
            min-width: 1080px !important;
            table-layout: fixed !important;
        }

        #employeeDirectoryPage #employeeTable :is(th, td) {
            min-width: 0 !important;
            max-width: none !important;
        }

        #employeeDirectoryPage #employeeTable thead th:first-child { width: 48px !important; }
        #employeeDirectoryPage #employeeTable thead th:nth-child(2) { width: 10% !important; }
        #employeeDirectoryPage #employeeTable thead th:nth-child(4) { width: 14% !important; }
        #employeeDirectoryPage #employeeTable thead th:nth-child(5) { width: 17% !important; }
        #employeeDirectoryPage #employeeTable thead th:nth-child(6) { width: 19% !important; }
        #employeeDirectoryPage #employeeTable thead th:nth-child(7) { width: 9% !important; }
        #employeeDirectoryPage #employeeTable thead th:last-child { width: 76px !important; }

        #employeeDirectoryPage #employeeTable tbody td {
            height: auto !important;
            padding: 12px 14px !important;
            border: 0 !important;
            border-radius: 0 !important;
            border-bottom: 1px solid var(--dir-border) !important;
        }

        #employeeDirectoryPage #employeeTable :is(thead th, tbody td):not(:last-child) {
            border-inline-end: 1px solid var(--dir-border) !important;
        }

        #employeeDirectoryPage #employeeTable :is(thead th, tbody td):is(:first-child, :nth-child(7), :last-child) {
            padding-inline: 8px !important;
            text-align: center !important;
        }

        #employeeDirectoryPage #employeeTable tbody tr:last-child td {
            border-bottom: 0 !important;
        }
    }

    @media (max-width: 767.98px) {
        #employeeDirectoryPage .table-card .table-responsive {
            padding: 12px !important;
        }

        #employeeDirectoryPage #employeeTable tbody tr {
            border: 1px solid var(--dir-border) !important;
            border-radius: 12px !important;
        }

        #employeeDirectoryPage #employeeTable tbody tr:last-child {
            margin-bottom: 0 !important;
        }

        #employeeDirectoryPage #employeeTable tbody td {
            background: transparent !important;
        }
    }

    @media (min-width: 560px) {
        #employeeDirectoryPage .directory-filter-fields {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1080px) {
        #employeeDirectoryPage .directory-metrics {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        #employeeDirectoryPage .directory-metric {
            min-height: 108px;
            padding-right: 72px;
        }

        #employeeDirectoryPage .directory-metric-icon {
            position: absolute;
            inset: 50% clamp(12px, 1.5vw, 22px) auto auto;
            transform: translateY(-50%);
        }

        #employeeDirectoryPage .directory-filter-fields {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1.15fr) auto;
        }
    }

    @media (max-width: 559px) {
        #employeeDirectoryPage .directory-hero-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }

        #employeeDirectoryPage .directory-hero-actions .directory-button {
            width: 100% !important;
        }

        #employeeDirectoryPage .directory-export-formats {
            flex: 1 1 100%;
            justify-content: space-between;
            padding-left: 0;
            border-left: 0;
        }

        #employeeDirectoryPage #export-all {
            width: auto !important;
        }

        #employeeDirectoryPage .directory-filter-actions .directory-button-primary {
            flex: 1 1 auto;
        }
    }

    @media (max-width: 375px) {
        #employeeDirectoryPage .directory-hero-actions {
            grid-template-columns: minmax(0, 1fr);
        }

        #employeeDirectoryPage .eot-switch-tab {
            gap: 6px;
            padding-inline: 6px !important;
            font-size: .75rem !important;
            white-space: nowrap;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #employeeDirectoryPage .directory-button {
            transition: none;
        }
    }
</style>
