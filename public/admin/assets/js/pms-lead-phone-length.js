document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('leadCreateForm') || document.getElementById('leadEditForm');
    if (!form) return;
    const countries = window.PmsPhoneFields?.countries || [];
    for (const field of ['phone', 'mobile', 'alternate_phone', 'whatsapp']) {
        const input = form.querySelector(`[name="${field}"]`);
        const select = form.querySelector(`[name="${field}_country_code"]`) || form.querySelector(`[data-phone-field="${field}"]`);
        if (!input || !select) continue;
        input.inputMode = 'numeric';
        const update = () => {
            const option = select.selectedOptions[0];
            const meta = countries.find(country => country.dial_code === select.value && (!option?.dataset.iso || option.dataset.iso === country.iso)) || countries.find(country => country.dial_code === select.value);
            const min = Number(option?.dataset.minDigits || meta?.min_digits);
            const max = Math.min(Number(option?.dataset.maxDigits || meta?.max_digits), 15 - select.value.replace(/\D/g, '').length);
            if (!min || !max) return;
            // Keep invalid existing values visible so corrections never silently change a contact number.
            input.maxLength = max;
            input.minLength = min;
            input.pattern = `[0-9]{${min},${max}}`;
            const range = min === max ? `exactly ${min}` : `${min} to ${max}`;
            input.title = `Enter ${range} digits for ${select.value}, excluding the country code.`;
            input.placeholder = `${min === max ? min : min + '–' + max} digits`;
            const number = input.value.replace(/[\s().-]/g, '');
            input.setCustomValidity(number && (!/^\d+$/.test(number) || number.length < min || number.length > max) ? input.title : '');
        };
        input.addEventListener('input', () => {
            if (!input.value.trim().startsWith('+')) input.value = input.value.replace(/\D/g, '');
            update();
        });
        select.addEventListener('change', update);
        update();
        form.addEventListener('submit', event => {
            update();
            if (!input.checkValidity()) { event.preventDefault(); input.reportValidity(); }
        });
    }
});
