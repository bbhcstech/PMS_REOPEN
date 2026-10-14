(function () {
    'use strict';
    function validate(form) {
        const start = form.querySelector('[name="start_date"]');
        const deadline = form.querySelector('[name="due_date"]');
        if (!start || !deadline) return true;
        const today = new Date();
        deadline.min = start.value || `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        const valid = !deadline.value || deadline.value >= deadline.min;
        deadline.setCustomValidity(valid ? '' : 'Deadline must be on or after the Start Date.');
        return valid;
    }
    for (const type of ['input', 'change']) document.addEventListener(type, event => {
        if (!['start_date', 'due_date'].includes(event.target.name)) return;
        const form = event.target.closest('#assignWorkForm');
        if (form) validate(form);
    });
    document.addEventListener('submit', event => {
        if (event.target.id !== 'assignWorkForm') return;
        if (!validate(event.target)) {
            event.preventDefault(); event.stopImmediatePropagation(); event.target.reportValidity();
        }
    }, true);
})();
