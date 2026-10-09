(() => {
    const input = document.getElementById('company_phone_edit_display');
    const select = document.getElementById('company_edit_country_code');
    const hidden = document.getElementById('company_phone_edit');
    const form = document.getElementById('companyEditForm');
    const hint = document.getElementById('company_phone_edit_hint');
    if (!input || !select || !hidden || !form) return;

    function update() {
        const option = select.selectedOptions[0];
        const min = Number(option?.dataset.minDigits) || 1;
        const max = Math.min(Number(option?.dataset.maxDigits) || 15, 15 - select.value.replace(/\D/g, '').length);
        const position = input.selectionStart;
        const original = input.value;
        input.value = original.replace(/[^0-9]/g, '').slice(0, max);
        if (document.activeElement === input && position !== null && input.value !== original) {
            const cursor = Math.min(original.slice(0, position).replace(/[^0-9]/g, '').length, input.value.length);
            input.setSelectionRange(cursor, cursor);
        }
        input.minLength = min;
        input.maxLength = max;
        input.pattern = `[0-9]{${min},${max}}`;
        const range = min === max ? `${max}` : `${min} to ${max}`;
        if (hint) hint.textContent = `Digits only: ${range} digits for ${select.value}.`;
        input.setCustomValidity(input.value && input.value.length < min ? `Enter ${range} digits for ${select.value}.` : '');
        hidden.value = input.value ? `${select.value} ${input.value}` : '';
    }
    input.addEventListener('beforeinput', event => {
        if (event.data && /[a-z]/i.test(event.data)) event.preventDefault();
    });
    input.addEventListener('input', update);
    select.addEventListener('change', update);
    form.addEventListener('submit', event => {
        update();
        if (!input.checkValidity()) {
            event.preventDefault();
            input.reportValidity();
        }
    });
    update();
})();
