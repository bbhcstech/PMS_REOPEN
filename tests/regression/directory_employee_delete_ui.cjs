const fs = require('fs');
const vm = require('vm');
const assert = require('node:assert/strict');
const view = fs.readFileSync('resources/views/admin/organization-directory/index.blade.php', 'utf8');
const start = view.indexOf('    function toggleSelectAll(master)');
const end = view.indexOf('    // Export Directory', start);
const script = view.slice(start, end).replace(/@json\(route\('organization\.employees\.delete'\)\)/g, '"/organization/employees"').replace(/@json\(csrf_token\(\)\)/g, '"token"');
const boxes = [{ checked: false, value: '1' }, { checked: false, value: '2' }];
const elements = { selectAllCheckbox: {}, directorySelectionCount: {}, directoryBulkDelete: {}, directoryDeleteFeedback: {} };
let confirmed = true, calls = [], reloads = 0, fail = false;
const context = {
    document: {
        getElementById: id => elements[id],
        querySelectorAll: selector => selector.endsWith(':checked') ? boxes.filter(b => b.checked) : boxes
    },
    window: { confirm: () => confirmed, location: { reload: () => reloads++ } },
    fetch: async (url, options) => {
        calls.push({ url, options });
        return { ok: !fail, json: async () => fail ? { errors: { employee_ids: ['Reassign reporting employees.'] } } : { message: 'Deleted' } };
    }
};
vm.createContext(context);
vm.runInContext(script, context);
(async () => {
    context.updateDirectorySelection();
    assert.equal(elements.directoryBulkDelete.disabled, true);
    boxes[0].checked = true;
    context.updateDirectorySelection();
    assert.equal(elements.selectAllCheckbox.indeterminate, true);
    assert.equal(elements.directoryBulkDelete.disabled, false);
    confirmed = false;
    await context.deleteDirectoryEmployees();
    assert.equal(calls.length, 0, 'Cancel must not send deletion');
    confirmed = true;
    context.toggleSelectAll({ checked: true });
    await context.deleteDirectoryEmployees();
    assert.deepEqual(JSON.parse(calls[0].options.body).employee_ids, [1, 2]);
    assert.equal(calls[0].options.method, 'DELETE');
    assert.equal(calls[0].options.headers['X-CSRF-TOKEN'], 'token');
    assert.equal(reloads, 1);
    // Fresh page after successful deletion.
    vm.runInContext('directoryDeletePending = false', context);
    fail = true;
    await context.deleteDirectoryEmployees(2);
    assert.deepEqual(JSON.parse(calls[1].options.body).employee_ids, [2]);
    assert.equal(elements.directoryDeleteFeedback.hidden, false);
    assert.equal(elements.directoryDeleteFeedback.textContent, 'Reassign reporting employees.');
    assert.equal(reloads, 1, 'Rejected deletion must not reload as success');
    assert.equal(elements.directoryBulkDelete.disabled, false, 'Failure must allow retry');
    console.log('Directory delete UI: selection, confirmation, single/bulk requests, CSRF, errors and retry passed.');
})().catch(error => { console.error(error); process.exitCode = 1; });
