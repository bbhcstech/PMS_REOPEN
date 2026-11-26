const fs = require('fs');
const vm = require('vm');
const assert = require('node:assert/strict');
const cards = [], urls = new Map(), pendingImages = [];
let nextUrl = 0;
const classList = { toggle() {}, remove() {} };
for (const [kind, height] of [['header', 160], ['footer', 140]]) {
    const input = {
        dataset: { width: '1240', height: String(height), maxBytes: '2097152' }, files: [], events: {},
        addEventListener(name, fn) { this.events[name] = fn; },
        setCustomValidity(message) { this.error = message; }, setAttribute() {}, classList,
        form: { addEventListener(name, fn) { this[name] = fn; } }
    };
    const source = { value: '', selectedOptions: [], classList, addEventListener(name, fn) { this[name] = fn; } };
    const preview = { hidden: true, removeAttribute() { delete this.src; } };
    const elements = { source, upload: {}, file: input, name: {}, feedback: { classList }, preview };
    cards.push({ kind, input, ...elements, querySelector: selector => elements[selector.slice('[data-branding-'.length, -1)] });
}
const context = {
    document: { querySelectorAll: () => cards },
    setTimeout: callback => callback(),
    URL: { createObjectURL(file) { const url = String(++nextUrl); urls.set(url, file); return url; }, revokeObjectURL(url) { urls.delete(url); } },
    Image: class {
        set src(url) {
            const file = urls.get(url);
            const decode = () => {
                if (file.corrupt) this.onerror();
                else { this.naturalWidth = file.width; this.naturalHeight = file.height; this.onload(); }
            };
            if (file.pending) pendingImages.push(decode);
            else decode();
        }
    }
};
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-recruitment-branding.js', 'utf8'), context);
function choose(input, overrides = {}) {
    input.files = [{ name: 'banner.png', type: 'image/png', size: 1000, width: 1240, height: Number(input.dataset.height), ...overrides }];
    input.events.change();
}
for (const { kind, input, preview, upload, source, name } of cards) {
    choose(input);
    assert.equal(input.error, '');
    assert.equal(preview.hidden, false);
    choose(input, { width: 1239 });
    assert.match(input.error, /exactly 1240/);
    assert.equal(preview.hidden, true);
    choose(input, { name: 'banner.pdf', type: 'application/pdf' });
    assert.match(input.error, /PDF files are not accepted/);
    choose(input, { size: 2097153 });
    assert.match(input.error, /2 MB/);
    choose(input, { corrupt: true });
    assert.match(input.error, /cannot be read/);
    choose(input);
    assert.equal(input.error, '', 'Valid replacement must clear errors');
    choose(input, { pending: true });
    source.value = '21';
    source.selectedOptions = [{ dataset: { preview: '/preview/' + kind } }];
    source.change();
    pendingImages.shift()();
    assert.equal(preview.src, '/preview/' + kind, 'Saved preview wins over stale upload validation');
    assert.equal(upload.hidden, true);
    assert.equal(input.required, false);
    assert.equal(input.disabled && name.disabled, true);
    source.value = '';
    input.files = [];
    input.form.reset();
    assert.equal(input.required, true);
    assert.equal(input.disabled || name.disabled || upload.hidden, false);
    assert.equal(preview.hidden, true);
}
assert.equal(urls.size, 0, 'Preview URLs must be released');
console.log('Recruitment branding UI: exact dimensions, upload validation, saved previews, stale checks, replacement and reset passed.');
