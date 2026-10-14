const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const root = path.join(__dirname, '../..');
const view = fs.readFileSync(path.join(root, 'resources/views/admin/designations/index.blade.php'), 'utf8');
const source = fs.readFileSync(path.join(root, 'public/admin/assets/js/pms-live-records.js'), 'utf8');

// Exercise the real refresh reconciler with the Designation page's section keys.
class Element {
    constructor(className, key, children = []) {
        this.nodeType = 1;
        this.tagName = 'DIV';
        this.className = className;
        this.attrs = key ? { 'data-live-key': key } : {};
        this.childNodes = [];
        children.forEach(child => this.insertBefore(child, null));
    }
    get attributes() { return Object.entries(this.attrs).map(([name, value]) => ({ name, value })); }
    get firstChild() { return this.childNodes[0] || null; }
    get nextSibling() { return this.parentNode?.childNodes[this.parentNode.childNodes.indexOf(this) + 1] || null; }
    getAttribute(name) { return this.attrs[name] ?? null; }
    hasAttribute(name) { return name in this.attrs; }
    setAttribute(name, value) { this.attrs[name] = value; }
    matches(selectors) { return selectors.split(',').some(s => s.trim() === 'script' && this.tagName === 'SCRIPT'); }
    querySelector() { return null; }
    querySelectorAll() { return []; }
    contains(node) { return this === node || this.childNodes.some(child => child.contains(node)); }
    insertBefore(node, anchor) {
        node.remove();
        const index = anchor ? this.childNodes.indexOf(anchor) : this.childNodes.length;
        this.childNodes.splice(index, 0, node);
        node.parentNode = this;
    }
    remove() {
        if (this.parentNode) this.parentNode.childNodes.splice(this.parentNode.childNodes.indexOf(this), 1);
        this.parentNode = null;
    }
    cloneNode() { return new Element(this.className, this.attrs['data-live-key'], this.childNodes.map(child => child.cloneNode())); }
}
const context = { document: { activeElement: null }, protect: 'script' };
vm.runInNewContext(source.slice(source.indexOf('    function key('), source.indexOf('    function syncDataTables(')) + '\nthis.sync = sync;', context);
function section(className) {
    const match = view.match(new RegExp('<div class="' + className + '"[^>]*data-live-key="([^"]+)"'));
    assert.ok(match, className + ' must have a stable refresh identity');
    return new Element(className, match[1]);
}
const successClass = 'alert alert-success alert-dismissible fade show';
const errorClass = 'alert alert-danger alert-dismissible fade show';
function page(alertClasses = []) {
    return new Element('designation-page', null, [
        section('breadcrumb'), section('header-card'), section('stats'),
        ...alertClasses.map(section), section('table-card'), section('legend-card'),
    ]);
}
const broken = page([successClass]);
const originalTable = broken.childNodes.find(node => node.className === 'table-card');
const withoutAlert = page();
for (const node of [...broken.childNodes, ...withoutAlert.childNodes]) node.attrs = {};
context.sync(broken, withoutAlert);
assert.notEqual(broken.childNodes[3], originalTable, 'Fixture must reproduce the original layout corruption without section keys');
assert.equal(broken.childNodes[3].className, successClass, 'Without keys the refreshed list is matched to the alert container');

for (const initialAlerts of [[successClass], [errorClass], [successClass, errorClass], []]) {
    const current = page(initialAlerts);
    const table = current.childNodes.find(node => node.className === 'table-card');
    const legend = current.childNodes.find(node => node.className === 'legend-card');
    for (const nextAlerts of [[], [successClass], [], [errorClass], []]) {
        context.sync(current, page(nextAlerts));
        assert.equal(current.childNodes.find(node => node.className === 'table-card'), table, 'Refresh replaced or swallowed the table card');
        assert.equal(current.childNodes.find(node => node.className === 'legend-card'), legend, 'Refresh replaced or swallowed the legend');
        assert.equal(current.childNodes.filter(node => node.className.startsWith('alert ')).length, nextAlerts.length);
        assert.equal(current.childNodes.length, 5 + nextAlerts.length);
    }
}
console.log('PASS: Designation table and legend stay intact when success/error alerts appear or disappear across repeated refreshes.');
