const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/admin/assets/js/pms-table-tools.js'), 'utf8');
const binding = source.slice(source.indexOf('  function bindController('), source.indexOf('  function enhance('));
let change;
const header = {checked: false, getAttribute: name => name === 'data-pms-table' ? 'test' : 'all'};
header.closest = () => header;
const controller = {
  id: 'test', selectedRows: new Set(),
  table: {addEventListener: (name, fn) => {if (name === 'change') change = fn;}},
  toolbar: {addEventListener() {}},
};
const rows = Array.from({length: 4}, () => {
  const row = {classList: {toggle() {}}};
  row.input = {
    checked: false,
    getAttribute: name => name === 'data-pms-table' ? 'test' : 'row',
    closest: selector => selector === 'tr' ? row : row.input,
    dispatchEvent() {change({target: row.input});},
  };
  row.querySelector = () => row.input;
  return row;
});
const context = {
  window: {}, Event: class {}, MutationObserver: class {observe() {}},
  dataRows: () => rows,
  syncToolbar() {
    header.checked = controller.selectedRows.size === rows.length;
    header.indeterminate = controller.selectedRows.size > 0 && !header.checked;
  },
};
vm.runInNewContext(binding + '\nbindController', context)(controller);
header.checked = true;
change({target: header});
assert.equal(controller.selectedRows.size, 4);
assert.ok(rows.every(row => row.input.checked));
assert.equal(header.indeterminate, false);
rows[0].input.checked = false;
change({target: rows[0].input});
assert.equal(controller.selectedRows.size, 3);
assert.equal(header.indeterminate, true);
header.checked = true;
change({target: header});
assert.equal(controller.selectedRows.size, 4);
header.checked = false;
change({target: header});
assert.equal(controller.selectedRows.size, 0);
assert.ok(rows.every(row => !row.input.checked));
console.log('Select all, partial selection, and deselect all checks passed.');
