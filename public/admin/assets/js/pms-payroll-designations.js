(() => {
    function syncLevel(designation) {
        const level = designation.closest('form')?.querySelector('[data-salary-level]');
        if (!level) return;
        const value = designation.selectedOptions[0]?.dataset.designationLevel;
        level.replaceChildren(new Option(value || 'Select a designation first', value || ''));
    }
    function initialize() {
        document.querySelectorAll('[data-salary-designation]').forEach(syncLevel);
    }
    document.addEventListener('change', event => {
        if (event.target.matches('[data-salary-designation]')) syncLevel(event.target);
    });
    document.addEventListener('shown.bs.modal', initialize);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
