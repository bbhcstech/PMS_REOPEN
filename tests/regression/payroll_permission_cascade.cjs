const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('resources/views/admin/settings/role-permissions/index.blade.php', 'utf8');
const start = source.indexOf('    function syncPayrollChildren(');
const end = source.indexOf('    rowCheckboxes.forEach', start);
assert(start > 0 && end > start);
const children = [{checked:false}, {checked:false}];
let queries = 0;
const context = {table: {querySelectorAll(selector) {
    queries++;
    assert.equal(selector, 'tr[data-payroll-child="true"] .action-checkbox[data-perm="view"]');
    return children;
}}};
vm.createContext(context);
vm.runInContext(source.slice(start, end), context);
const payroll = {checked:true, dataset:{perm:'view'}, closest: () => ({dataset:{moduleSlug:'payroll'}})};
context.syncPayrollChildren(payroll);
assert(children.every(child => child.checked));
// A separately adjusted page remains independent until Payroll is changed again.
children[0].checked = false;
context.syncPayrollChildren({checked:false, dataset:{perm:'view'}, closest: () => ({dataset:{moduleSlug:'payslips'}})});
assert.equal(queries, 1);
assert.equal(children[1].checked, true);
payroll.checked = false;
context.syncPayrollChildren(payroll);
assert(children.every(child => !child.checked));
console.log('PASS: Payroll view grants/revokes reach pages; child overrides remain independent.');
