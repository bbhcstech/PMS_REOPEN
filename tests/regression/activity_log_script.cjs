const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../resources/views/superadmin/tenant_audit/index.blade.php'), 'utf8');
const script = source.match(/<script>([\s\S]*?)<\/script>/)[1]
    .replace(/^\s*const url = @json.*$/m, 'const url = "/super-admin/tenant-audit/event/__EVENT__";')
    .replace(/^\s*const today = .*@json.*$/m, 'const today = new Date("2026-10-09T00:00:00+05:30");');
new vm.Script(script);
function element() {
    const classes = new Set();
    return {value: '', style: {}, attributes: {}, handlers: {}, children: [],
        classList: {add: name => classes.add(name), remove: name => classes.delete(name), contains: name => classes.has(name)},
        addEventListener(type, handler) { this.handlers[type] = handler; },
        getAttribute(name) { return this.attributes[name] ?? null; },
        setAttribute(name, value) { this.attributes[name] = value; },
        querySelector() { return element(); }, appendChild() {},
    };
}
const elements = Object.fromEntries([...source.matchAll(/id="([^"]+)"/g)].map(match => [match[1], element()]));
const button = element(); button.setAttribute('data-id', 'EVT-SA-00001');
let ready, navigated, requested;
const context = {
    URL, URLSearchParams, Date, Set, JSON, Object, Array, Math, parseInt,
    setInterval() {}, setTimeout, clearTimeout,
    document: {addEventListener(type, handler) { ready = handler; }, getElementById: id => elements[id],
        querySelectorAll: selector => selector === '.open-drawer-btn' ? [button] : [], createElement: element},
    window: {location: {href: 'https://example.test/super-admin/activity-logs', search: '', assign: url => { navigated = url; }}, alert: message => { throw new Error(message); }},
    fetch: async url => { requested = url; return {ok: true, json: async () => ({event: {
        company_name: 'Alpha', company_code: 'ALPHA', user_name: 'Platform', user_email: 'platform@example.test',
        role: 'Super Admin', date_str: '09 Oct 2026', module: 'Companies', action: 'Company updated',
        resource: 'Company', resource_id: '1', status: 'success', ip_address: '10.0.0.5', new_values: {name: 'Alpha'},
    }})}; },
};
context.location = context.window.location;
context.alert = context.window.alert;
context.window = context;
vm.createContext(context);
vm.runInContext(script, context);
ready();
(async () => {
    await button.handlers.click.call(button);
    if (!requested.endsWith('EVT-SA-00001') || !elements.auditDetailsDrawer.classList.contains('open') || elements.drawerCompany.textContent !== 'Alpha') throw new Error('View button did not open selected server event.');
    elements.companyFilter.value = '3';
    elements.companyFilter.handlers.change();
    if (new URL(navigated).searchParams.get('company') !== '3') throw new Error('Company filter did not request server results.');
    console.log('PASS: script initialization, View fetch/drawer and server filter navigation.');
})().catch(error => { console.error(error); process.exitCode = 1; });
