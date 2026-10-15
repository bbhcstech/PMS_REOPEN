(function () {
    'use strict';
    function preview(form) {
        var inputs = Array.from(form.querySelectorAll('[data-behaviour-rating]'));
        var output = form.querySelector('[data-behaviour-preview]');
        var valid = inputs.length === 3 && inputs.every(function (input) {
            return input.value !== '' && input.validity.valid && Number(input.value) >= 1 && Number(input.value) <= 10;
        });
        var target = Number(form.dataset && form.dataset.behaviourTarget) || 10;
        output.textContent = valid ? Math.min(100, inputs.reduce(function (sum, input) { return sum + Number(input.value); }, 0) / 3 / target * 100).toFixed(2) + ' / 100' : 'Enter all three ratings (1–10)';
    }
    document.querySelectorAll('[data-behaviour-rating-form]').forEach(preview);
    document.addEventListener('input', function (event) {
        if (event.target.matches('[data-behaviour-rating]')) preview(event.target.closest('form'));
    });
    var section = document.querySelector('[data-behaviour-section]');
    if (section && section.dataset.behaviourOpen === '1' && window.bootstrap) {
        var tab = document.querySelector('[data-bs-target="#behaviour-table-pane"]');
        if (tab) window.bootstrap.Tab.getOrCreateInstance(tab).show();
        var modal = document.getElementById('behaviourRating-' + section.dataset.behaviourEmployee);
        if (modal) window.bootstrap.Modal.getOrCreateInstance(modal).show();
    }
})();
