const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const listeners = {};
let cachedWrites = 0;
const context = vm.createContext({
    URL, Promise, console,
    self: { location: { origin: 'https://pms.test' }, addEventListener: (name, fn) => { listeners[name] = fn; } },
    caches: { match: async () => null, open: async () => ({ put: () => { cachedWrites++; } }) },
    fetch: async () => ({ status: 200, headers: { get: () => 'private, no-store' }, clone: () => ({}) }),
});
vm.runInContext(fs.readFileSync('public/sw.js', 'utf8'), context);
for (const pathname of ['/uploads/community/1/private.png', '/uploads/community/2/private.pdf', '/storage/private.jpg', '/community/messages/1/attachment', '/notifications/latest']) {
    let intercepted = false;
    listeners.fetch({ request: { method: 'GET', mode: 'cors', url: 'https://pms.test' + pathname }, respondWith: () => { intercepted = true; } });
    assert.equal(intercepted, false, 'Private data was intercepted by the shared asset cache: ' + pathname);
}
(async () => {
    assert.equal(vm.runInContext("isStaticAsset('/admin/assets/css/pms-refresh.css')", context), true);
    await vm.runInContext("cacheFirstAsset({url: 'https://pms.test/admin/assets/image.png'})", context);
    assert.equal(cachedWrites, 0, 'A private/no-store response was cached.');
    console.log('PASS: shared assets still work; uploads, downloads, records and private responses never enter the shared PWA cache.');
})().catch(error => { console.error(error); process.exitCode = 1; });
