const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const root = path.join(__dirname, '../..');
const view = fs.readFileSync(path.join(root, 'resources/views/superadmin/dashboard.blade.php'), 'utf8');
const source = fs.readFileSync(path.join(root, 'public/admin/assets/js/pms-live-records.js'), 'utf8');
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

const sections = [...view.matchAll(/^  <(div|section)[^>]*data-live-key="(dashboard-[^"]+)"/gm)].map(match => ({ tag:match[1].toUpperCase(), key:match[2], className:match[0].match(/class="([^"]+)"/)?.[1] || 'catalog-grid' }));
assert.equal(sections.length,19);
assert.ok(view.includes('data-live-key="dashboard-success"'));
assert.ok(view.includes('data-live-key="dashboard-errors"'));
function dashboard(alerts=[]) {
  return new Element('dashboard','root',[
    ...alerts.map(key=>new Element('alert',key)),
    ...sections.map(section=>{
      const node=new Element(section.className,section.key);
      node.tagName=section.tag;
      if(section.key==='dashboard-charts')node.insertBefore(new Element('chart-card','chart-fixture',[new Element('canvas-host','canvas-fixture')]),null);
      if(section.key==='dashboard-health-grid')for(let i=0;i<6;i++)node.insertBefore(new Element('health-item','health-'+i),null);
      return node;
    })
  ]);
}
const broken=dashboard(['dashboard-success']), fresh=dashboard();
for(const node of [...broken.childNodes,...fresh.childNodes])node.attrs={};
context.sync(broken,fresh);
assert.ok(broken.childNodes.some((node,i)=>node.className!==fresh.childNodes[i]?.className),'Original unkeyed fixture must reproduce the layout corruption');
const current=dashboard(['dashboard-success']);
const original=new Map(current.childNodes.filter(n=>n.attrs['data-live-key']!=='dashboard-success').map(n=>[n.attrs['data-live-key'],n]));
const chart=original.get('dashboard-charts').firstChild.firstChild;
const health=[...original.get('dashboard-health-grid').childNodes];
for(const alerts of [[],['dashboard-success'],[],['dashboard-errors'],[],['dashboard-success','dashboard-errors'],[]]){
  context.sync(current,dashboard(alerts));
  assert.deepEqual(current.childNodes.map(n=>n.attrs['data-live-key']),[...alerts,...sections.map(s=>s.key)]);
  for(const [key,node]of original)assert.equal(current.childNodes.find(n=>n.attrs['data-live-key']===key),node,key+' must retain its container');
  assert.equal(original.get('dashboard-charts').firstChild.firstChild,chart,'Chart subtree must stay inside chart container');
  assert.deepEqual(original.get('dashboard-health-grid').childNodes,health,'Health cards must stay in grid');
}
console.log('PASS: Dashboard success/error transitions preserve all 19 sections, chart subtree, health grid and section order across repeated refreshes.');
