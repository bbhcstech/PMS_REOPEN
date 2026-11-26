const fs = require('fs');
const vm = require('vm');
const assert = require('node:assert/strict');
const nodes = {}, inputs = [], urls = new Map();
let nextUrl = 0;
for (const [id, height] of [['header', 240], ['footer', 160]]) {
    const input = {
        id, dataset: { width: '1800', height: String(height), maxBytes: '2097152' }, files: [], events: {},
        addEventListener(name, fn) { this.events[name] = fn; },
        setCustomValidity(message) { this.error = message; }, setAttribute() {}, classList: { toggle() {} },
        form: { addEventListener(name, fn) { this[name] = fn; } }
    };
    nodes[id + 'Feedback'] = {};
    nodes[id + 'Preview'] = { hidden: true, removeAttribute() { delete this.src; } };
    inputs.push(input);
}
const context = {
    document: { querySelectorAll: () => inputs, getElementById: id => nodes[id] },
    URL: { createObjectURL(file) { const url = String(++nextUrl); urls.set(url, file); return url; }, revokeObjectURL(url) { urls.delete(url); } },
    Image: class {
        set src(url) {
            const file = urls.get(url);
            if (file.corrupt) this.onerror();
            else { this.naturalWidth = file.width; this.naturalHeight = file.height; this.onload(); }
        }
    }
};
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-recruitment-branding.js', 'utf8'), context);
function choose(input, overrides = {}) {
    input.files = [{ name: 'banner.png', type: 'image/png', size: 1000, width: 1800, height: Number(input.dataset.height), ...overrides }];
    input.events.change();
}
for (const input of inputs) {
    choose(input);
    assert.equal(input.error, '');
    assert.equal(nodes[input.id + 'Preview'].hidden, false);
    choose(input, { width: 1799 });
    assert.match(input.error, /exactly 1800/);
    assert.equal(nodes[input.id + 'Preview'].hidden, true);
    choose(input, { name: 'banner.pdf', type: 'application/pdf' });
    assert.match(input.error, /PDF files are not accepted/);
    choose(input, { size: 2097153 });
    assert.match(input.error, /2 MB/);
    choose(input, { corrupt: true });
    assert.match(input.error, /cannot be read/);
    choose(input);
    assert.equal(input.error, '', 'Valid replacement must clear errors');
    input.form.reset();
    assert.equal(nodes[input.id + 'Preview'].hidden, true);
}
assert.equal(urls.size, 0, 'Preview URLs must be released');
console.log('Recruitment branding UI: file type, dimensions, size, corrupt images, previews, replacement and reset passed.');
