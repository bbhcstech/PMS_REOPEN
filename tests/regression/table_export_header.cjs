const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/admin/assets/js/pms-table-tools.js'), 'utf8');
const code = source.slice(source.indexOf('  function placeToolbar('), source.indexOf('  function createToolbar('));
function node() {
  return {children: [], classList: {add() {}}, setAttribute() {},
    appendChild(child) { this.children.push(child); child.parentNode = this; },
    insertBefore(child, anchor) { this.children.splice(this.children.indexOf(anchor), 0, child); child.parentNode = this; },
    querySelectorAll() { return []; }, querySelector() { return null; }, matches() { return false; }, closest() { return null; }};
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
console.log('PASS: table header placement, headerless tables, multiple tables and explicit hosts.');
