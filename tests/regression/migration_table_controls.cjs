const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const handlers = {}, controls = {
    migrationSearchInput: { value: '' }, filterStatus: { value: 'all' },
    filterTenant: { value: 'all' }, filterVersion: { value: 'all' },
};
function row(id, name, pending) {
    return { dataset: { id, name, pending: String(pending), status: pending ? 'pending' : 'up_to_date', code: id, db: 'tenant_' + id }, style: {},
        cells: ['', name, id, 'tenant_' + id, 'v10', 'v12', pending ? '2 Pending' : 'Up to Date', '10 Oct 2026', '2.54s', 'View'].map(innerText => ({ innerText })) };
}
const rows = [row('1', 'Instagram', 0), row('2', 'Amazon, "India"', 2), row('3', '=unsafe', 0)];
let blob, anchor, clicked = 0, removed = 0, revoked = 0;
const document = {
    getElementById: id => controls[id], querySelectorAll: () => rows,
    addEventListener: (type, fn) => { handlers[type] = fn; },
    createElement: () => anchor = { click: () => clicked++, remove: () => removed++ },
    body: { appendChild() {} },
};
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/admin/assets/js/pms-migration-table.js'), 'utf8'), {
    document, Blob, URL: { createObjectURL: value => { blob = value; return 'blob:csv'; }, revokeObjectURL: () => revoked++ }, setTimeout: fn => fn(),
});
const click = id => handlers.click({ preventDefault() {}, target: { closest: selector => selector === '#' + id } });
(async () => {
    controls.migrationSearchInput.value = 'amazon'; handlers.input({ target: { id: 'migrationSearchInput' } });
    assert.deepEqual(rows.map(row => row.style.display), ['none', '', 'none']);
    click('exportMigrationsBtn');
    const csv = await blob.text();
    assert.ok(csv.includes('"Amazon, ""India"""'), 'CSV must escape commas and quotes');
    assert.ok(!csv.includes('Instagram'), 'Export must respect the search filter');
    assert.ok(csv.includes('"v10","v12","2 Pending","10 Oct 2026","2.54s"'), 'Export columns must match the table');
    assert.equal(anchor.download, 'tenant-migrations.csv');
    assert.equal(clicked, 1); assert.equal(removed, 1); assert.equal(revoked, 1);
    controls.filterTenant.value = '2'; controls.filterStatus.value = 'pending'; controls.filterVersion.value = 'outdated';
    click('resetFiltersBtn');
    assert.deepEqual(Object.values(controls).map(input => input.value), ['', 'all', 'all', 'all']);
    assert.ok(rows.every(row => row.style.display === ''), 'Reset must show every row');
    controls.filterVersion.value = 'outdated'; handlers.change({ target: { id: 'filterVersion' } });
    assert.deepEqual(rows.map(row => row.style.display), ['none', '', 'none']);
    rows.push(row('4', 'New Tenant', 0)); handlers['pms:records-updated']();
    assert.equal(rows[3].style.display, 'none', 'Refresh must reapply filters to new rows');
    // Replaced controls must still be read rather than retaining old DOM references.
    controls.migrationSearchInput = { value: 'instagram' }; controls.filterVersion = { value: 'all' };
    handlers.input({ target: { id: 'migrationSearchInput' } });
    assert.equal(rows[0].style.display, ''); assert.equal(rows[1].style.display, 'none');
    click('resetFiltersBtn'); click('exportMigrationsBtn');
    assert.ok((await blob.text()).includes("\"'=unsafe\""), 'Spreadsheet formula-like values must be escaped');
    console.log('PASS: Reset, filtered CSV export, quoting, safe spreadsheet values, version filters and refresh/replaced controls.');
})().catch(error => { console.error(error); process.exitCode = 1; });
