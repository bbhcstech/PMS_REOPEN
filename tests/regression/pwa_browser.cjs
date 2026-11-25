// Actual browser worker lifecycle/cache test against a local fixture server; no user data.
const fs=require('node:fs'),path=require('node:path'),os=require('node:os'),http=require('node:http');
const {spawn}=require('node:child_process'),assert=require('node:assert/strict');
const root=path.resolve(__dirname,'../..'),temp=fs.mkdtempSync(path.join(os.tmpdir(),'pms-pwa-test-'));
const delay=ms=>new Promise(r=>setTimeout(r,ms));let server,child,socket,version=1,workerVersion=10,posts=0,disconnected=false;
const checks=[];
async function run(){
  server=http.createServer((req,res)=>{
    if(disconnected){req.socket.destroy();return;}
    const url=new URL(req.url,'http://localhost');
    res.setHeader('Cache-Control','no-store');
    if(url.pathname==='/admin/assets/test.css'){res.setHeader('Cache-Control','no-cache');res.setHeader('Content-Type','text/css');res.end('/* version '+version+' */');return;}
    if(req.method==='POST'){posts++;res.end('saved');return;}
    if(url.pathname==='/records'){res.setHeader('Content-Type','application/json');res.end(JSON.stringify({value:version}));return;}
    if(url.pathname==='/sw.js'){res.setHeader('Content-Type','application/javascript');res.end(fs.readFileSync(path.join(root,'public/sw.js'),'utf8').replace("'v10'","'v"+workerVersion+"'"));return;}
    if(url.pathname==='/dashboard'||url.pathname==='/'){res.setHeader('Content-Type','text/html');res.end('<html><head><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/pwa.css"></head><body><h1>Live dashboard</h1><input id="draft" value="unsaved work"><script src="/pwa-register.js"></script></body></html>');return;}
    const file=path.resolve(root,'public','.'+url.pathname);
    if(!file.startsWith(path.join(root,'public')+path.sep)||!fs.existsSync(file)||fs.statSync(file).isDirectory()){res.statusCode=404;res.end('not found');return;}
    res.setHeader('Content-Type',file.endsWith('.js')?'application/javascript':file.endsWith('.json')?'application/manifest+json':file.endsWith('.css')?'text/css':file.endsWith('.png')?'image/png':'text/html');res.end(fs.readFileSync(file));
  });
  await new Promise(r=>server.listen(0,'127.0.0.1',r));const origin='http://127.0.0.1:'+server.address().port;
  child=spawn('C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',['--headless','--disable-gpu','--no-first-run','--disable-background-networking','--remote-debugging-port=0','--user-data-dir='+temp,'about:blank'],{stdio:'ignore'});
  const portFile=path.join(temp,'DevToolsActivePort');for(let i=0;i<150&&!fs.existsSync(portFile);i++)await delay(100);
  const port=fs.readFileSync(portFile,'utf8').split('\n')[0];const targets=await fetch('http://127.0.0.1:'+port+'/json/list').then(r=>r.json());
  socket=new WebSocket(targets.find(t=>t.type==='page').webSocketDebuggerUrl);await new Promise(r=>socket.addEventListener('open',r,{once:true}));
  let id=0;const pending=new Map();socket.addEventListener('message',e=>{const m=JSON.parse(e.data);if(pending.has(m.id)){const p=pending.get(m.id);pending.delete(m.id);m.error?p.reject(Error(m.error.message)):p.resolve(m.result);}});
  const send=(method,params={})=>new Promise((resolve,reject)=>{const key=++id;pending.set(key,{resolve,reject});socket.send(JSON.stringify({id:key,method,params}));});
  const evaluate=async expression=>{const r=await send('Runtime.evaluate',{expression,awaitPromise:true,returnByValue:true});if(r.exceptionDetails)throw Error(r.exceptionDetails.text+' '+r.result.description);return r.result.value;};
  const check=(value,name)=>{assert.ok(value,name);checks.push(name);};
  await send('Page.enable');await send('Network.enable');await send('Emulation.setDeviceMetricsOverride',{width:375,height:844,deviceScaleFactor:1,mobile:true});await send('Emulation.setEmulatedMedia',{features:[{name:'display-mode',value:'standalone'}]});
  // Headless Chromium does not expose installed windows. Simulate the iOS app-mode signal;
  // worker installation, fetches, caching, offline navigation and reconnection are real.
  await send('Page.addScriptToEvaluateOnNewDocument',{source:"Object.defineProperty(navigator,'standalone',{value:true,configurable:true});"});
  await send('Page.navigate',{url:origin+'/dashboard'});await delay(300);
  check(await evaluate(`(async()=>{await navigator.serviceWorker.ready;for(let i=0;i<100&&!navigator.serviceWorker.controller;i++)await new Promise(r=>setTimeout(r,50));return Boolean(navigator.serviceWorker.controller)})()`),'worker installs and controls the page');
  check(await evaluate(`document.getElementById('draft').value==='unsaved work' && document.querySelector('h1').textContent==='Live dashboard'`),'first activation preserves open page and draft');
  check(await evaluate(`document.documentElement.hasAttribute('data-pms-standalone')`),'standalone detection');
  check((await evaluate(`fetch('/admin/assets/test.css').then(r=>r.text())`)).includes('version 1'),'online asset loaded');version=2;
  check((await evaluate(`fetch('/admin/assets/test.css').then(r=>r.text())`)).includes('version 2'),'online assets update without reinstalling');
  await evaluate(`fetch('/records').then(r=>r.json())`);await evaluate(`fetch('/save',{method:'POST',body:'draft'}).then(r=>r.text())`);check(posts===1,'save requests reach server once');
  const cached=await evaluate(`(async()=>{const keys=await caches.keys();const urls=[];for(const key of keys)urls.push(...(await (await caches.open(key)).keys()).map(r=>r.url));return urls})()`);
  check(!cached.some(url=>/\/dashboard|\/records|\/save/.test(url)),'HTML, records and writes never cached');
  disconnected=true;
  await send('Network.emulateNetworkConditions',{offline:true,latency:0,downloadThroughput:0,uploadThroughput:0});await delay(100);
  check((await evaluate(`fetch('/admin/assets/test.css').then(r=>r.text())`)).includes('version 2'),'offline shared asset fallback');
  check(await evaluate(`!document.querySelector('.pms-pwa-notice').hidden && document.querySelector('.pms-pwa-notice').textContent.includes('Reconnect')`),'offline app notice');
  check(await evaluate(`fetch('/records').then(()=>false,()=>true)`),'offline records are not stale account data');
  await send('Page.navigate',{url:origin+'/dashboard?offline=1'});await delay(400);
  check(await evaluate(`document.querySelector('h1')?.textContent==='You are offline'`),'offline navigation fallback');
  disconnected=false;
  await send('Network.emulateNetworkConditions',{offline:false,latency:0,downloadThroughput:-1,uploadThroughput:-1});await delay(700);
  check(await evaluate(`document.querySelector('h1')?.textContent==='Live dashboard'`),'reconnection retries original page');
  await evaluate(`(async()=>{document.getElementById('draft').value='edited unsaved draft';await caches.open('other-app-cache');await caches.open('bitroxia-pms-v9')})()`);
  workerVersion=11;
  check(await evaluate(`(async()=>{const r=await navigator.serviceWorker.getRegistration();await r.update();for(let i=0;i<100&&!r.waiting;i++)await new Promise(resolve=>setTimeout(resolve,50));return Boolean(r.waiting)})()`),'updated worker waits for activation');
  check(await evaluate(`document.getElementById('draft').value==='edited unsaved draft'`),'update does not discard unsaved work');
  await delay(100);
  check(await evaluate(`!document.querySelector('.pms-pwa-notice').hidden&&!document.querySelector('.pms-pwa-notice button').hidden`),'app update offers explicit reload');
  await evaluate(`document.querySelector('.pms-pwa-notice button').click()`);await delay(700);
  check(await evaluate(`document.getElementById('draft').value==='unsaved work'`),'requested update activates and reloads');
  const keys=await evaluate(`caches.keys()`);
  check(keys.includes('other-app-cache')&&!keys.includes('bitroxia-pms-v9')&&keys.some(k=>k.endsWith('v11')),'activation cleans PMS caches and preserves other apps');
  const manifest=JSON.parse(fs.readFileSync(path.join(root,'public/manifest.json'),'utf8'));
  check(manifest.display==='standalone'&&manifest.orientation==='any','manifest supports app mode and landscape');
  for(const icon of manifest.icons){const file=path.join(root,'public',icon.src);check(fs.existsSync(file),'icon '+icon.sizes+' '+icon.purpose);const bytes=fs.readFileSync(file);assert.equal(bytes.readUInt32BE(16)+'x'+bytes.readUInt32BE(20),icon.sizes);}
  const report={passed:checks.length,checks,limitations:'Local worker fixture; not authenticated end-to-end or physical-device installation.'};fs.writeFileSync(path.join(root,'docs/pwa-browser-verification.json'),JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));
}
run().catch(e=>{console.error(e);process.exitCode=1;}).finally(async()=>{socket?.close();child?.kill();server?.close();await delay(500);fs.rmSync(temp,{recursive:true,force:true,maxRetries:10,retryDelay:200});});
