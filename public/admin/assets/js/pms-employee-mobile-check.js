(function () {
    'use strict';
    if (window.PMSEmployeeMobileCheck) return;
    window.PMSEmployeeMobileCheck = {
        init: function (options) {
            var input = document.getElementById('mobile_only_digits');
            var country = document.getElementById('mobile_country_code');
            var error = document.getElementById('mobile-error');
            var hidden = document.getElementById('mobile_with_code');
            if (!input || !country || input.dataset.mobileCheckReady) return;
            input.dataset.mobileCheckReady = '1';
            var generation = 0, pending = null;
            var key = function () { return (country.value || '+91') + '|' + input.value.trim(); };
            var originalKey = key();
            function clearServerErrors() {
                document.querySelectorAll('[data-validation-field="mobile"],[data-validation-field="mobile_country_code"]').forEach(function (message) { message.remove(); });
                document.querySelectorAll('[data-validation-summary]').forEach(function (summary) {
                    if (!summary.querySelector('[data-validation-field]')) summary.hidden = true;
                });
            }
            function clearFeedback() {
                input.classList.remove('is-invalid');
                error.textContent = ''; error.classList.add('d-none'); error.classList.remove('d-block');
            }
            function cancel() { generation++; if (pending) pending.abort(); pending = null; }
            function sync() { if (hidden) hidden.value = input.value.trim() ? (country.value || '+91') + input.value.trim() : ''; }
            function changed() {
                cancel(); clearFeedback(); sync();
                if (key() !== originalKey) clearServerErrors();
            }
            input.addEventListener('input', changed);
            // Select2 emits jQuery change events as well as native select changes.
            if (window.jQuery) window.jQuery(country).on('change', changed);
            else country.addEventListener('change', changed);
            async function check() {
                cancel();
                if (!options.validate() || !input.value.trim()) return;
                sync();
                var snapshot = key(), requestGeneration = generation;
                var controller = new AbortController(); pending = controller;
                var employee = document.getElementById('employee_id_val');
                try {
                    var response = await fetch(options.url, {
                        method: 'POST', signal: controller.signal,
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': options.csrf },
                        body: JSON.stringify({ mobile: input.value.trim(), mobile_country_code: country.value || '+91', employee_id: employee ? employee.value : null })
                    });
                    var data = await response.json();
                    if (requestGeneration !== generation || key() !== snapshot) return;
                    if (!response.ok) {
                        if (response.status === 422 && data.errors && data.errors.mobile) {
                            input.classList.add('is-invalid'); error.textContent = data.errors.mobile[0];
                            error.classList.remove('d-none'); error.classList.add('d-block');
                        }
                        return; // Server-side submission validation remains authoritative on request failures.
                    }
                    clearFeedback();
                    if (data.exists === true) {
                        input.classList.add('is-invalid');
                        error.textContent = 'This mobile number is already registered.';
                        error.classList.remove('d-none'); error.classList.add('d-block');
                    } else clearServerErrors();
                } catch (failure) {
                    // An aborted/stale or failed availability check cannot invalidate the edited number.
                } finally { if (pending === controller) pending = null; }
            }
            if (window.jQuery) window.jQuery(input).on('blur', check);
            else input.addEventListener('blur', check);
        }
    };
})();
