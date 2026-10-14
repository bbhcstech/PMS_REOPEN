(() => {
    if (window.PmsPhoneFields) return;
    const countries = Object.entries(JSON.parse(document.getElementById('pms-phone-countries')?.textContent || '{}'))
        .map(([name, meta]) => ({ ...meta, name, iso2: meta.iso, dialCode: meta.dial_code.slice(1) }));
    const prefixes = [...new Set(countries.map(c => c.dial_code))].sort((a, b) => b.length - a.length);
    const adapters = new WeakMap();
    function split(value) {
        const raw = value.trim();
        const compact = raw.replace(/[\s().-]/g, '');
        const code = compact.startsWith('+') ? prefixes.find(prefix => compact.startsWith(prefix)) : null;
        return { code, number: code ? compact.slice(code.length) : raw };
    }
    function options(select) {
        for (const country of countries) {
            const option = document.createElement('option');
            option.value = country.dial_code;
            option.textContent = `${country.iso.toUpperCase()} (${country.dial_code}) — ${country.name}`;
            option.dataset.iso = country.iso;
            select.append(option);
        }
    }
    function attach(input, settings = {}) {
        if (adapters.has(input)) return adapters.get(input);
        const group = document.createElement('div'); group.className = 'pms-phone-group';
        input.before(group); group.append(input);
        const select = document.createElement('select'); select.className = 'pms-phone-country';
        select.setAttribute('aria-label', 'Phone country code'); options(select); group.prepend(select);
        input.style.paddingLeft = ''; select.disabled = input.disabled || input.readOnly;
        const initial = countries.find(c => c.iso === (settings.initialCountry || 'in'));
        if (initial) select.value = initial.dial_code;
        const country = () => countries.find(c => c.dial_code === select.value && c.iso === select.selectedOptions[0]?.dataset.iso) || countries.find(c => c.dial_code === select.value);
        const api = {
            getSelectedCountryData: () => country() || {},
            getNumber: () => input.value.trim() ? select.value + input.value.replace(/[\s().-]/g, '') : '',
            getValidationError: () => {
                const meta = country(), digits = input.value.replace(/[\s().-]/g, '');
                if (!meta || !/^\d+$/.test(digits)) return 4;
                if (digits.length < meta.min_digits) return 2;
                if (digits.length > meta.max_digits || digits.length + meta.dialCode.length > 15) return 3;
                return 0;
            },
            isValidNumber: () => api.getValidationError() === 0,
            setNumber: value => { const parsed = split(value); if (parsed.code) select.value = parsed.code; input.value = parsed.number; },
            setCountry: iso => { const index = countries.findIndex(c => c.iso === iso); if (index >= 0) select.selectedIndex = index; },
        };
        api.setNumber(input.value);
        select.addEventListener('change', () => input.dispatchEvent(new Event('countrychange', { bubbles: true })));
        input.addEventListener('input', () => { if (input.value.trim().startsWith('+')) { api.setNumber(input.value); input.dispatchEvent(new Event('countrychange', { bubbles: true })); } });
        adapters.set(input, api);
        return api;
    }
    function scan() {
        document.querySelectorAll('select[data-phone-field]').forEach(select => {
            const input = select.parentElement.querySelector('input:not([type="hidden"])');
            if (!input) return;
            select.disabled = input.disabled || input.readOnly;
            const parsed = split(input.value);
            if (parsed.code) { select.value = parsed.code; input.value = parsed.number; }
            if (select.dataset.phoneReady) return;
            select.dataset.phoneReady = '1';
            select.addEventListener('change', () => input.dispatchEvent(new Event('input', { bubbles: true })));
            // A pasted international number selects its database-backed prefix.
            input.addEventListener('input', () => {
                if (!input.value.trim().startsWith('+')) return;
                const parsed = split(input.value);
                if (parsed.code) { select.value = parsed.code; input.value = parsed.number; }
            });
        });
    }
    window.PmsPhoneFields = { attach, scan, countries, formatted: input => {
        const select = input.closest('.pms-phone-group')?.querySelector('select');
        return select && input.value.trim() ? select.value + ' ' + input.value : input.value;
    } };
    new MutationObserver(scan).observe(document.documentElement, { childList: true, subtree: true });
    document.addEventListener('submit', scan, true);
    scan();
})();
