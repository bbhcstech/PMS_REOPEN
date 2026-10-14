<style>
    :is(.create-project-page, .edit-project-page) .members-section {
        --member-chip-bg: #eff6ff;
        --member-chip-text: #1e40af;
        --member-chip-border: #93c5fd;
    }

    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) :is(.create-project-page, .edit-project-page) .members-section {
        --member-chip-bg: #1e3a5f;
        --member-chip-text: #eff6ff;
        --member-chip-border: #60a5fa;
    }

    :is(.create-project-page, .edit-project-page) .members-section .select2-container .select2-selection--multiple {
        min-height: 48px;
        padding: 6px 10px;
        height: auto !important;
    }

    :is(.create-project-page, .edit-project-page) .members-section .select2-container .select2-selection__choice {
        background: var(--member-chip-bg) !important;
        border: 1px solid var(--member-chip-border) !important;
        color: var(--member-chip-text) !important;
        -webkit-text-fill-color: var(--member-chip-text) !important;
        border-radius: 8px;
        margin: 4px 6px 4px 0;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    :is(.create-project-page, .edit-project-page) .members-section .select2-container :is(.select2-selection__choice__display, .select2-selection__choice__remove, .select2-selection__choice__remove span) {
        color: var(--member-chip-text) !important;
        -webkit-text-fill-color: var(--member-chip-text) !important;
        background: transparent !important;
    }

    :is(.create-project-page, .edit-project-page) .members-section .select2-container .select2-search__field {
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        min-height: 24px;
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }
</style>
