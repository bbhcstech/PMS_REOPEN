// Real Chromium browser against an isolated HTTP fixture; no application data or dependencies.
const fs = require('node:fs'), path = require('node:path'), os = require('node:os'), http = require('node:http');
const { spawn } = require('node:child_process'), assert = require('node:assert/strict');
const root = path.resolve(__dirname, '../..');
const temp = fs.mkdtempSync(path.join(os.tmpdir(), 'pms-loading-test-'));
const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
let server, browser, socket, origin, posts = 0, submitted = '', checks = 0, clockPosts = 0, clockBody = '';
let employeeClockScript = fs.readFileSync(path.join(root, 'resources/views/employee-dashboard.blade.php'), 'utf8');
employeeClockScript = employeeClockScript.slice(employeeClockScript.indexOf('const officeLocation ='), employeeClockScript.indexOf('const istClock ='))
    .replace(/@json\(\$officeLatitude\)/g, '22.49682').replace(/@json\(\$officeLongitude\)/g, '88.39462')
    .replace(/@json\(\$officeRadiusMeters\)/g, '10').replace(/@json\(\$officeAddress\)/g, '"Office"');
const employeeFixture = `<!doctype html><meta name="viewport" content="width=device-width,initial-scale=1">
<script src="/admin/assets/js/pms-operation-loading.js"></script><script src="/admin/assets/js/pms-clock-capture.js"></script>
<form id="employeeClockInForm" method="post" action="/clock-save">
<input name="_token" value="fixture-token"><input id="clockInLatitude" name="clock_in_latitude"><input id="clockInLongitude" name="clock_in_longitude">
<input id="clockInAccuracy" name="clock_in_accuracy"><input id="clockInAddress" name="clock_in_address">
<input id="clockInSelfie" name="clock_in_selfie"><input id="clockInTimezone" name="clock_in_timezone">
<button type="submit" id="employeeClockInButton">Clock In</button></form><div id="clockRequirementStatus"></div>
<div id="clockCameraModal"><div id="clockCameraPreview"><video id="clockCameraVideo" autoplay playsinline muted></video><canvas id="clockCameraCanvas"></canvas><img id="clockCameraPhoto"></div>
<button type="button" id="clockCameraCapture">Capture</button><button type="button" id="clockCameraClose">Close</button><button type="button" id="clockCameraFlip">Flip</button>
<button type="button" id="clockCameraRetake">Retake</button><button type="button" id="clockCameraUse">Confirm</button></div>
<script>window.PMSTime={timeZone:'Asia/Kolkata'};
PMSClockCapture.location=async()=>({coords:{latitude:22.49682,longitude:88.39462,accuracy:3}});PMSClockCapture.json=async()=>({address:{city:'Kolkata'}});
document.addEventListener('DOMContentLoaded',()=>{${employeeClockScript}});</script>`;
const fixture = `<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/admin/assets/css/pms-operation-loading.css"><script src="/admin/assets/js/pms-operation-loading.js"></script>
<script src="/admin/assets/js/pms-clock-capture.js"></script>
<style>button,a{display:inline-block;padding:12px 24px;background:#2463eb;color:white;border:0;border-radius:8px;font:14px Arial;margin:6px}body{margin:10px}</style></head><body>
<button id="fetch">Save employee</button><button id="fail">Failed request</button><button id="http">HTTP error</button>
<button id="xhr">Save company</button><button id="parallel">Load dashboard</button><button id="instant">Toggle</button>
<button id="abort">Abort</button><button id="custom">Custom operation</button>
<button id="chain">Load then save</button><button id="redirect">Redirect</button><button id="xhrFail">XHR failure</button>
<button id="existing"><span class="spinner-border">↻</span> Existing spinner</button>
<form id="ajax"><input name="name" value="Deal"><button id="deal">Save deal</button></form>
<form id="cancel"><button id="cancelButton">Cancel validation</button></form>
<form id="invalid"><input required name="required"><button id="invalidButton">Required</button></form>
<form id="native" method="post" action="/saved"><button id="nativeButton" name="action" value="approve">Approve</button></form>
<form id="program" method="post" action="/saved"><button id="programButton">Confirm and submit</button></form>
<form id="clock" method="post" action="/clock-save"><input name="clock_in_selfie" value="data:image/png;base64,captured"><input name="_token" value="fixture-token"><button type="submit" id="clockSave">Clock In</button></form>
<a id="navigate" href="/destination">Another dashboard</a><a id="hash" href="#section">Section</a>
<a id="download" href="/file" download>Download</a><div id="section"></div>
<script>
window.calls=0;window.errors=0;window.done=0;
document.getElementById('fetch').onclick=()=>{calls++;fetch('/api',{method:'POST'}).then(r=>r.json()).then(()=>done++);};
document.getElementById('fail').onclick=()=>fetch('/disconnect').catch(()=>errors++);
document.getElementById('http').onclick=()=>fetch('/error').then(r=>{if(!r.ok)throw Error('existing error');}).catch(()=>errors++);
document.getElementById('abort').onclick=()=>{const c=new AbortController();fetch('/api',{signal:c.signal}).then(r=>r.json()).catch(()=>errors++);setTimeout(()=>c.abort(),50);};
document.getElementById('xhr').onclick=()=>{const x=new XMLHttpRequest();x.open('POST','/api');x.send('company');};
document.getElementById('xhrFail').onclick=()=>{const x=new XMLHttpRequest();x.open('GET','/disconnect');x.send();};
document.getElementById('chain').onclick=async()=>{await fetch('/api').then(r=>r.json());await fetch('/api?slow=1').then(r=>r.json());};
document.getElementById('redirect').onclick=()=>location.assign('/destination');
document.getElementById('parallel').onclick=()=>Promise.all([fetch('/api').then(r=>r.json()),fetch('/api?slow=1').then(r=>r.json())]);
document.getElementById('instant').onclick=()=>document.body.classList.toggle('open');
document.getElementById('custom').onclick=()=>PMSLoading.run(document.getElementById('custom'),()=>new Promise(r=>setTimeout(r,300)));
document.getElementById('existing').onclick=()=>fetch('/api').then(r=>r.json());
document.getElementById('ajax').onsubmit=e=>{e.preventDefault();calls++;fetch('/api',{method:'POST',body:new FormData(e.target)}).then(r=>r.json());};
document.getElementById('cancel').onsubmit=e=>e.preventDefault();
document.getElementById('program').onsubmit=e=>{e.preventDefault();HTMLFormElement.prototype.submit.call(e.target);};
document.getElementById('clock').onsubmit=e=>{e.preventDefault();PMSClockCapture.submit(e.target);};
const originalTimeout=window.setTimeout;window.setTimeout=(fn,ms,...args)=>originalTimeout(fn,window.clockFastTimeout&&ms===45000?100:ms,...args);
</script></body></html>`;
async function run() {
    server = http.createServer((req, res) => {
        const url = new URL(req.url, 'http://localhost'); res.setHeader('Cache-Control', 'no-store');
        if (url.pathname.startsWith('/admin/assets/')) {
            const file = path.join(root, 'public', url.pathname);
            res.setHeader('Content-Type', file.endsWith('.js') ? 'application/javascript' : 'text/css'); res.end(fs.readFileSync(file)); return;
        }
        if (url.pathname === '/clock-save') {
            clockPosts++; let body = '';
            req.on('data', chunk => body += chunk);
            req.on('end', () => {
                clockBody = body;
                setTimeout(() => {
                    const mode = url.searchParams.get('mode');
                    res.setHeader('Content-Type', mode === 'html' ? 'text/html' : 'application/json');
                    res.statusCode = mode === 'validation' ? 422 : mode === 'expired' ? 419 : mode === 'html' ? 500 : 200;
                    res.end(mode === 'html' ? '<h1>Internal server error</h1>' : JSON.stringify(mode === 'validation'
                        ? { success: false, errors: { clock_in_selfie: ['Photo could not be saved.'] } }
                        : { success: true, redirect_url: '/destination' }));
                }, 300);
            }); return;
        }
        if (url.pathname === '/employee-clock-fixture') { res.setHeader('Content-Type', 'text/html'); res.end(employeeFixture); return; }
        if (url.pathname === '/api') {
            if (req.method === 'POST') posts++;
            res.setHeader('Content-Type', 'application/json'); res.writeHead(200); res.flushHeaders();
            setTimeout(() => res.end('{"ok":true}'), url.search ? 600 : 300); return;
        }
        if (url.pathname === '/disconnect') { setTimeout(() => req.socket.destroy(), 180); return; }
        if (url.pathname === '/error') { setTimeout(() => { res.statusCode = 500; res.end('failure'); }, 180); return; }
        if (url.pathname === '/saved') {
            let body = ''; req.on('data', chunk => body += chunk);
            req.on('end', () => { submitted = body; posts++; setTimeout(() => { res.setHeader('Content-Type', 'text/html'); res.end('<html><body id="destination">Saved</body></html>'); }, 500); }); return;
        }
        if (url.pathname === '/destination') { setTimeout(() => { res.setHeader('Content-Type', 'text/html'); res.end('<html><body id="destination">Next dashboard</body></html>'); }, 500); return; }
        if (url.pathname === '/file') { res.setHeader('Content-Disposition', 'attachment; filename="test.txt"'); res.end('download'); return; }
        res.setHeader('Content-Type', 'text/html'); res.end(fixture);
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    origin = 'http://127.0.0.1:' + server.address().port;
    browser = spawn('C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', ['--headless', '--disable-gpu', '--no-first-run', '--disable-background-networking', '--use-fake-device-for-media-stream', '--use-fake-ui-for-media-stream', '--remote-debugging-port=0', '--user-data-dir=' + temp, 'about:blank'], { stdio: 'ignore', windowsHide: true });
    const portFile = path.join(temp, 'DevToolsActivePort');
    for (let i = 0; i < 100 && !fs.existsSync(portFile); i++) await delay(100);
    const port = fs.readFileSync(portFile, 'utf8').split('\n')[0];
    const targets = await fetch('http://127.0.0.1:' + port + '/json/list').then(r => r.json());
    socket = new WebSocket(targets.find(target => target.type === 'page').webSocketDebuggerUrl);
    await new Promise(resolve => socket.addEventListener('open', resolve, { once: true }));
    let id = 0; const pending = new Map();
    socket.addEventListener('message', event => {
        const message = JSON.parse(event.data), item = pending.get(message.id);
        if (!item) return; pending.delete(message.id);
        message.error ? item.reject(Error(message.error.message)) : item.resolve(message.result);
    });
    const send = (method, params = {}) => new Promise((resolve, reject) => { const key = ++id; pending.set(key, { resolve, reject }); socket.send(JSON.stringify({ id: key, method, params })); });
    const evaluate = async expression => {
        const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
        if (result.exceptionDetails) throw Error(result.exceptionDetails.text + ': ' + result.exceptionDetails.exception?.description);
        return result.result.value;
    };
    const check = (condition, message) => { assert.ok(condition, message); checks++; };
    const waitFor = async expression => { for (let i = 0; i < 100; i++) { try { if (await evaluate(expression)) return; } catch (_) {} await delay(25); } throw Error('Timeout: ' + expression); };
    const load = async () => { await send('Page.navigate', { url: origin + '/fixture' }); await waitFor('!!document.getElementById("fetch") && !!window.PMSLoading'); };
    const click = id => evaluate(`document.getElementById(${JSON.stringify(id)}).click()`);
    const busy = id => evaluate(`document.getElementById(${JSON.stringify(id)}).getAttribute('aria-busy')==='true'`);
    for (const [width, height, mobile] of [[390, 844, true], [820, 1180, true], [1440, 900, false]]) {
        await send('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile });
        await send('Emulation.setTouchEmulationEnabled', { enabled: mobile }); await load();
        const before = await evaluate(`(()=>{const e=document.getElementById('fetch'),r=e.getBoundingClientRect();return [r.width,r.height,e.textContent,getComputedStyle(e).backgroundColor]})()`);
        if (mobile) {
            const point = await evaluate(`(()=>{const r=document.getElementById('fetch').getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})()`);
            await send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [point] });
            await send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] });
        } else await click('fetch');
        await delay(150);
        check(await busy('fetch'), width + ': spinner remains during response body download');
        check(await evaluate(`!document.getElementById('pms-operation-progress').hidden && getComputedStyle(document.getElementById('fetch'),'::after').content!=='none'`), width + ': page and inline indicators visible');
        const during = await evaluate(`(()=>{const e=document.getElementById('fetch'),r=e.getBoundingClientRect();return [r.width,r.height,e.textContent,getComputedStyle(e).backgroundColor]})()`);
        assert.deepEqual(during, before, width + ': button dimensions, label and color unchanged'); checks++;
        await click('fetch'); check(await evaluate('calls===1'), width + ': duplicate request blocked');
        await waitFor('done===1'); check(!await busy('fetch'), width + ': success restored button');
        await click('instant'); await delay(150); check(!await busy('instant'), width + ': instant toggle has no spinner');
        await click('hash'); await delay(150); check(!await busy('hash'), width + ': hash link has no spinner');
        for (const id of ['fail', 'http', 'abort']) {
            await click(id); await waitFor(`!document.getElementById('${id}').hasAttribute('data-pms-operation-busy')`);
            check(!await busy(id), width + ': ' + id + ' restores state');
        }
        await waitFor('errors===3');
        check(await evaluate('errors===3'), width + ': existing error handling unchanged');
        await click('xhr'); check(await busy('xhr'), width + ': XHR loading'); await delay(400); check(!await busy('xhr'), width + ': XHR reset');
        await click('parallel'); await delay(380); check(await busy('parallel'), width + ': parallel request remains busy'); await delay(300); check(!await busy('parallel'), width + ': final request resets');
        await click('chain'); await delay(450); check(await busy('chain'), width + ': chained API request remains busy'); await delay(550); check(!await busy('chain'), width + ': chained API requests reset');
        await click('xhrFail'); await waitFor(`!document.getElementById('xhrFail').hasAttribute('data-pms-operation-busy')`); check(!await busy('xhrFail'), width + ': XHR failure reset');
        await click('custom'); check(await busy('custom'), width + ': custom async operation'); await delay(350); check(!await busy('custom'), width + ': custom reset');
        await click('existing'); check(await evaluate(`!document.getElementById('existing').classList.contains('pms-operation-inline')`), width + ': existing spinner reused'); await delay(350);
        const startPosts = posts; await click('deal');
        await evaluate(`document.getElementById('ajax').requestSubmit()`); await delay(400);
        check(posts === startPosts + 1, width + ': duplicate AJAX form submit prevented');
        await click('cancelButton'); await click('invalidButton'); await delay(150);
        check(!await busy('cancelButton') && !await busy('invalidButton'), width + ': validation cancellation leaves no loader');
        await evaluate(`fetch('/api').then(r=>r.json())`);
        check(await evaluate(`!document.getElementById('pms-operation-progress') || document.getElementById('pms-operation-progress').hidden`), width + ': background requests do not flash');
        check(await evaluate(`(()=>{const e=document.getElementById('navigate');e.click();return new Promise(r=>queueMicrotask(()=>r(e.getAttribute('aria-busy')==='true')));})()`), width + ': navigation spinner'); await waitFor(`!!document.getElementById('destination')`);
        await load(); check(await evaluate(`(()=>{const e=document.getElementById('nativeButton');e.click();return new Promise(r=>queueMicrotask(()=>r(e.getAttribute('aria-busy')==='true')));})()`), width + ': native submit spinner'); await waitFor(`!!document.getElementById('destination')`);
        check(submitted.includes('action=approve'), width + ': submitter name/value preserved');
        await load(); await click('programButton'); await waitFor(`!!document.getElementById('destination')`); check(true, width + ': confirmed programmatic submission works');
        await load(); check(await evaluate(`(()=>{const e=document.getElementById('redirect');e.click();return new Promise(r=>queueMicrotask(()=>r(e.getAttribute('aria-busy')==='true')));})()`), width + ': programmatic redirect spinner'); await waitFor(`!!document.getElementById('destination')`);
        await load(); await click('fetch'); await evaluate(`window.dispatchEvent(new PageTransitionEvent('pageshow',{persisted:true}))`);
        check(!await busy('fetch'), width + ': bfcache restores controls'); await delay(350);
        console.log('PASS: ' + width + 'px ' + (mobile ? 'touch' : 'desktop') + ' viewport');
        await send('Page.navigate', { url: origin + '/employee-clock-fixture' });
        await waitFor(`document.getElementById('employeeClockInButton') && document.readyState==='complete'`);
        const autoClockStart = clockPosts;
        await click('employeeClockInButton');
        await waitFor(`document.getElementById('clockCameraVideo').videoWidth>0 && !document.getElementById('clockCameraUse').disabled`);
        await click('clockCameraCapture');
        await waitFor(`!!document.getElementById('destination')`);
        check(clockPosts === autoClockStart + 1 && clockBody.includes('data:image/jpeg;base64,'), width + ': actual employee camera script captures and automatically saves exactly once without confirmation click');
        await load();
        const clockStart = clockPosts;
        await click('clockSave');
        await evaluate(`PMSClockCapture.submit(document.getElementById('clock'))`);
        check(await busy('clockSave'), width + ': attendance save spinner');
        await waitFor(`!!document.getElementById('destination')`);
        check(clockPosts === clockStart + 1 && clockBody.includes('fixture-token') && clockBody.includes('data:image/png;base64,captured'), width + ': photo and CSRF posted once before dashboard navigation');
        for (const mode of ['validation', 'expired', 'html', 'slow']) {
            await load();
            await evaluate(`document.getElementById('clock').action='/clock-save?mode=${mode}';window.clockFastTimeout=${mode === 'slow'};`);
            await click('clockSave');
            await waitFor(`!!document.querySelector('[data-clock-submit-error]')`);
            check(!await busy('clockSave') && await evaluate(`!document.getElementById('clockSave').disabled`), width + ': ' + mode + ' releases attendance spinner and button');
            check(await evaluate(`!!document.querySelector('[data-clock-submit-error]').textContent && !document.getElementById('destination')`), width + ': ' + mode + ' reports failure without pretending attendance saved');
        }
    }
    console.log('PASS: ' + checks + ' loading checks across mobile, tablet and desktop.');
}
run().catch(error => { console.error(error); process.exitCode = 1; }).finally(async () => {
    if (socket) socket.close(); if (browser) browser.kill(); if (server) server.closeAllConnections(); if (server) server.close();
    await delay(400);
    const resolved = path.resolve(temp), allowed = path.resolve(os.tmpdir()) + path.sep;
    if (resolved.startsWith(allowed) && path.basename(resolved).startsWith('pms-loading-test-')) {
        try { fs.rmSync(resolved, { recursive: true, force: true }); } catch (_) {}
    }
});
