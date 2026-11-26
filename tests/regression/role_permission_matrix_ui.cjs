const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const view = fs.readFileSync('resources/views/admin/settings/role-permissions/index.blade.php', 'utf8');
const actions = ['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'];
const defaults = { 1: ['view'], 2: ['view', 'create'], 3: [], 4: ['view'] };
const script = view.slice(view.lastIndexOf('<script>') + 8, view.lastIndexOf('</script>')).replace('@json($defaultPermissions)', JSON.stringify(defaults));
function element(properties = {}) {
    return Object.assign({ checked: false, hidden: false, value: '', dataset: {}, listeners: {},
        classList: { toggle() {}, remove() {} },
        addEventListener(event, fn) { this.listeners[event] = fn; },
        getAttribute(name) { return this.dataset[name.replace('data-', '').replace(/-([a-z])/g, (_, c) => c.toUpperCase())]; },
        fire(event) { return this.listeners[event]?.call(this, { target: this, preventDefault() {} }); },
    }, properties);
}
const categories = ['HR & PEOPLE', 'FINANCE & PAYROLL'];
const rows = ['employees', 'payroll', 'payslips', 'salary-structures'].map((slug, i) => element({
    dataset: { moduleId: String(i + 1), moduleSlug: slug, category: categories[i ? 1 : 0], search: `${slug} ${categories[i ? 1 : 0]}`.toLowerCase(), payrollChild: i > 1 ? 'true' : 'false' },
}));
const boxes = rows.flatMap(row => actions.map(perm => element({ value: perm,
    dataset: {moduleId: row.dataset.moduleId, perm}, closest: () => row,
})));
const rowBoxes = rows.map(row => element({ dataset: {moduleId: row.dataset.moduleId} }));
const columns = actions.map(perm => element({dataset: {perm}}));
const headers = categories.map(category => element({dataset: {category}}));
const button = element({innerHTML: 'Save Permissions'});
let roleSubmits = 0, allowDiscard = false;
const roleSelect = element({value: 'hr', form: {submit() { roleSubmits++; }}});
const form = element({action: '/permissions', querySelector: selector => selector.includes('button') ? button : {value: selector.includes('_token') ? 'csrf' : 'hr'}});
const ids = {rolePermissionsForm: form, masterToggleAll: element(), permissionModuleSearch: element(), permissionCategoryFilter: element(), permissionSearchStatus: element(), permissionSearchEmpty: element(), permissionSaveStatus: element()};
for (const id of ['btnGrantAll', 'btnViewOnly', 'btnResetDefaults', 'btnRevokeAll']) ids[id] = element();
const table = {querySelectorAll(selector) {
    if (selector === '.col-checkbox') return columns;
    if (selector === '.row-checkbox') return rowBoxes;
    if (selector === '.permission-category') return headers;
    if (selector === 'tbody tr[data-search]') return rows;
    let selected = boxes;
    if (selector.includes('data-payroll-child')) selected = selected.filter(box => box.closest().dataset.payrollChild === 'true');
    const module = selector.match(/data-module-id="([^"]+)"/);
    const perm = selector.match(/data-perm="([^"]+)"/);
    return selected.filter(box => (!module || box.dataset.moduleId === module[1]) && (!perm || box.dataset.perm === perm[1]));
}};
const unload = {};
let finishRequest, payload;
vm.runInNewContext(script, {
    document: {getElementById: id => ids[id], querySelector: selector => selector === '.matrix-table' ? table : roleSelect,
        addEventListener: (event, fn) => { if (event === 'DOMContentLoaded') fn(); }},
    window: {addEventListener: (event, fn) => unload[event] = fn, confirm: () => allowDiscard},
    fetch: async (_, options) => { payload = JSON.parse(options.body); return new Promise(resolve => finishRequest = resolve); },
});
(async () => {
    ids.btnGrantAll.fire('click');
    assert(boxes.every(box => box.checked));
    assert(ids.masterToggleAll.checked);
    assert(ids.permissionSaveStatus.textContent.includes('Unsaved'));
    ids.permissionModuleSearch.value = ' salary '; ids.permissionModuleSearch.fire('input');
    assert.equal(rows.filter(row => !row.hidden).length, 1);
    assert(headers[0].hidden && !headers[1].hidden);
    ids.permissionCategoryFilter.value = categories[0]; ids.permissionCategoryFilter.fire('change');
    assert.equal(ids.permissionSearchEmpty.hidden, false);
    ids.btnViewOnly.fire('click');
    assert(boxes.every(box => box.checked === (box.value === 'view')), 'Filtered rows lost bulk changes');
    assert(ids.masterToggleAll.indeterminate);
    ids.permissionModuleSearch.value = ''; ids.permissionModuleSearch.fire('input');
    assert.equal(rows.filter(row => !row.hidden).length, 1);
    ids.permissionCategoryFilter.value = ''; ids.permissionCategoryFilter.fire('change');
    assert.equal(rows.filter(row => !row.hidden).length, 4);
    assert.equal(ids.permissionSearchEmpty.hidden, true);
    ids.btnRevokeAll.fire('click'); assert(boxes.every(box => !box.checked));
    ids.btnResetDefaults.fire('click');
    assert(boxes.every(box => box.checked === defaults[box.dataset.moduleId].includes(box.value)));
    columns.find(column => column.dataset.perm === 'delete').checked = true;
    columns.find(column => column.dataset.perm === 'delete').fire('change');
    assert(boxes.filter(box => box.value === 'delete').every(box => box.checked));
    rowBoxes[0].checked = true; rowBoxes[0].fire('change');
    assert(boxes.filter(box => box.dataset.moduleId === '1').every(box => box.checked));
    roleSelect.value = 'manager'; roleSelect.fire('change');
    assert.equal(roleSubmits, 0); assert.equal(roleSelect.value, 'hr');
    let prevented = false;
    unload.beforeunload({preventDefault() { prevented = true; }}); assert(prevented);
    const save = form.fire('submit');
    assert.equal(button.disabled, true);
    assert.equal(Object.keys(payload.permissions).length, 4);
    // Changes made while a request is in flight must remain marked unsaved.
    boxes[0].checked = false; boxes[0].fire('change');
    finishRequest({ok: true, json: async () => ({success: true, message: 'Saved'})});
    await save;
    assert.equal(button.disabled, false);
    assert(ids.permissionSaveStatus.textContent.includes('additional unsaved'));
    const retry = form.fire('submit');
    finishRequest({ok: false, json: async () => ({success: false, message: 'Save failed'})});
    await retry; assert.equal(ids.permissionSaveStatus.textContent, 'Save failed');
    const finalSave = form.fire('submit');
    finishRequest({ok: true, json: async () => ({success: true, message: 'Saved'})});
    await finalSave;
    prevented = false; unload.beforeunload({preventDefault() { prevented = true; }}); assert.equal(prevented, false);
    roleSelect.value = 'manager'; roleSelect.fire('change'); assert.equal(roleSubmits, 1);
    console.log('PASS: matrix row/column/master controls, defaults, category/search, filtered bulk actions, unsaved navigation, inline feedback without toast, failed saves and edits during saves.');
})().catch(error => { console.error(error); process.exitCode = 1; });
