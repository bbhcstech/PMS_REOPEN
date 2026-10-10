(() => {
    function initialize() {
        const form = document.getElementById('projectForm');
        if (!form) return;
        const feedback = document.getElementById('project-form-feedback');
        const deadline = form.querySelector('[name="deadline"]');
        const noDeadline = form.querySelector('[name="without_deadline"]');
        const manualCode = form.querySelector('[name="shortcode_manual"]');
        const members = form.querySelector('[name="employee_ids[]"]');
        const departments = form.querySelector('[name="department_ids[]"]');
        function synchronizeRules() {
            if (deadline) {
                deadline.required = !noDeadline?.checked;
                deadline.disabled = Boolean(noDeadline?.checked);
                deadline.setCustomValidity('');
                const start = form.querySelector('[name="start_date"]')?.value;
                if (!deadline.disabled && deadline.value && start && deadline.value < start)
                    deadline.setCustomValidity('Deadline cannot be before the start date.');
            }
            if (manualCode) manualCode.required = form.querySelector('#shortcode_manual_opt')?.checked || false;
            if (members) members.required = true;
            if (departments) departments.setCustomValidity(departments.selectedOptions.length ? '' : 'Select at least one project department.');
        }
        function focusField(field) {
            const collapse = field.closest('.collapse');
            if (collapse && window.bootstrap) bootstrap.Collapse.getOrCreateInstance(collapse, { toggle: false }).show();
            const group = field.closest('.form-group');
            group?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (field === departments) {
                document.getElementById('departmentPickerToggle')?.focus();
            } else if (field.classList.contains('select2-hidden-accessible')) {
                group?.querySelector('.select2-selection')?.focus();
                if (window.jQuery?.fn.select2) jQuery(field).select2('open');
            } else field.focus();
        }
        form.addEventListener('submit', event => {
            synchronizeRules();
            const invalid = Array.from(form.elements).filter(field => field.willValidate && !field.checkValidity());
            feedback.replaceChildren();
            feedback.classList.toggle('d-none', !invalid.length);
            if (!invalid.length) return;
            event.preventDefault();
            const title = document.createElement('strong'); title.textContent = 'Please complete these fields before creating the project:';
            const list = document.createElement('ul'); list.className = 'mb-0 mt-2';
            invalid.forEach(field => {
                field.classList.add('is-invalid'); field.setAttribute('aria-invalid', 'true');
                const label = field.closest('.form-group')?.querySelector('.form-label')?.textContent.replace(/\s+/g, ' ').replace('*', '').trim()
                    || (field === members ? 'Project members' : field === departments ? 'Project departments' : field.name);
                const item = document.createElement('li'); item.textContent = label + ': ' + field.validationMessage; list.append(item);
            });
            feedback.append(title, list);
            focusField(invalid[0]);
        });
        function clearField(event) {
            const field = event.target;
            synchronizeRules();
            if (field.checkValidity?.()) { field.classList.remove('is-invalid'); field.removeAttribute('aria-invalid'); }
        }
        form.addEventListener('input', clearField);
        form.addEventListener('change', clearField);
        if (window.jQuery) jQuery(form).on('change', 'select', event => clearField(event));
        synchronizeRules();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
