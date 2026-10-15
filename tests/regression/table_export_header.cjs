const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/admin/assets/js/pms-table-tools.js'), 'utf8');
const attendanceView = fs.readFileSync(require('node:path').join(__dirname, '../../resources/views/admin/attendance/create.blade.php'), 'utf8');
const adminHeader = fs.readFileSync(require('node:path').join(__dirname, '../../resources/views/admin/layout/header.blade.php'), 'utf8');
assert.match(adminHeader, /@stack\('styles'\)/);
assert.match(attendanceView, /@push\('styles'\)[\s\S]*?daterangepicker\.css[\s\S]*?@endpush/,
  'Attendance date-picker CSS must be sent to the stack rendered by the admin layout');
const eligible = vm.runInNewContext(source.slice(source.indexOf('  function isEligible('), source.indexOf('  function dataRows(')) + '\nisEligible');
const recordTable = {
  tagName: 'TABLE', matches: () => false, closest: () => null,
  tHead: {querySelector: () => ({})}, tBodies: [{}],
};
assert.equal(eligible(recordTable), true, 'Record tables must retain export controls');
assert.equal(eligible({...recordTable, closest: selector => selector === '.fc' ? {} : null}), false,
  'Calendar layout tables must not receive selection columns, export controls or scroll hosts');
assert.equal(eligible({...recordTable, closest: selector => selector === '.daterangepicker' ? {} : null}), false,
  'Date-range picker tables must not receive selection columns, export controls or scroll hosts');
assert.equal(eligible({...recordTable, closest: selector => selector === '[data-pms-export="off"]' ? {} : null}), false,
  'Explicitly excluded containers must remain untouched');
const code = source.slice(source.indexOf('  function placeToolbar('), source.indexOf('  function createToolbar('));
function node() {
  return {children: [], classList: {add() {}}, setAttribute() {}, compareDocumentPosition() { return 4; },
    contains(child) { return this.children.includes(child); },
    appendChild(child) {
      if (child.parentNode) child.parentNode.children.splice(child.parentNode.children.indexOf(child), 1);
      this.children.push(child); child.parentNode = this;
    },
    insertBefore(child, anchor) { this.children.splice(this.children.indexOf(anchor), 0, child); child.parentNode = this; },
    querySelectorAll() { return []; }, querySelector(selector) { return selector === '.pms-table-header-tools' ? this.children.find(child => child.className === 'pms-table-header-tools') || null : null; }, matches() { return false; }, closest() { return null; }};
}
const document = {body: node(), createElement: node, getElementById: () => null};
const place = vm.runInNewContext(code + '\nplaceToolbar', {document});
function fixture(count = 1, hasHeader = true) {
  const card = node(), scroll = node(), table = node(), header = node(), toolbar = node();
  table.getAttribute = () => null;
  table.closest = () => null;
  table.parentElement = scroll;
  scroll.parentElement = card;
  card.parentElement = document.body;
  scroll.querySelectorAll = () => [table];
  card.querySelectorAll = () => Array(count).fill(table);
  card.querySelector = () => hasHeader ? header : null;
  header.compareDocumentPosition = () => 4;
  card.appendChild(scroll);
  place({table, scrollHost: scroll}, toolbar);
  return {card, scroll, table, header, toolbar};
}
let f = fixture();
assert.equal(f.header.children[0].children[0], f.toolbar, 'Toolbar must be inside the table header');
assert.equal(f.card.children.length, 1, 'Toolbar must not escape below or outside the card');
const headerHost = f.header.children[0];
for (let refresh = 0; refresh < 5; refresh++) {
  f.card.appendChild(f.toolbar); // Simulate live refresh preserving it at the bottom.
  place({table: f.table, scrollHost: f.scroll}, f.toolbar);
  assert.equal(f.toolbar.parentNode, headerHost, 'Refresh must restore the toolbar to its header');
  assert.equal(f.header.children.length, 1, 'Repair must reuse the header host');
  place({table: f.table, scrollHost: f.scroll}, f.toolbar);
  assert.equal(headerHost.children.length, 1, 'Repeated scans must not duplicate the toolbar');
}
f = fixture(1, false);
assert.equal(f.card.children[0], f.toolbar, 'Headerless table must have controls above the table');
assert.equal(f.card.children[1], f.scroll);
f = fixture(2);
assert.equal(f.header.children.length, 0, 'A shared header must not receive another table controls');
assert.equal(f.card.children[0], f.toolbar);
const host = node(); document.getElementById = () => host;
f = fixture(); f.table.getAttribute = () => 'custom-host';
const toolbar = node(); place({table: f.table}, toolbar);
assert.equal(host.children[0], toolbar, 'Explicit toolbar hosts must remain supported');
host.compareDocumentPosition = () => 2; // Preserved custom host moved below its table.
place({table: f.table, scrollHost: f.scroll}, toolbar);
assert.equal(host.parentNode, f.header, 'A custom host moved to the footer must be restored to the header');
console.log('PASS: table header placement, headerless tables, multiple tables and explicit hosts.');
