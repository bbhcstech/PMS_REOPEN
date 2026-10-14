// Offline source-preview audit. Uses real view markup/styles and sample text, not company data.
// This is layout coverage, not a substitute for signed-in end-to-end workflow tests.
const fs = require('node:fs'), path = require('node:path'), os = require('node:os');
const http = require('node:http'), {spawn} = require('node:child_process');
const root = path.resolve(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const exists = file => fs.existsSync(path.join(root, file));
const style = source => [...source.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/g)].map(m => m[1]).join('\n').replace(/@import[^;]+;/g, '');
const views = [];
function visit(dir) {
  for (const item of fs.readdirSync(path.join(root, dir), {withFileTypes:true})) {
    const file = dir + '/' + item.name;
    if (item.isDirectory()) visit(file);
    else if (file.endsWith('.blade.php')) views.push(file);
  }
}
visit('resources/views');
const layoutSources = {
  admin: read('resources/views/admin/layout/manu.blade.php'),
  platform: read('resources/views/layouts/superadmin.blade.php'),
  developer: read('resources/views/layouts/developer.blade.php'),
  public: read('resources/views/frontend/layouts-frontend/app.blade.php'),
};
const baseCss = ['vendor/css/core.css','css/demo.css','css/pms-refresh.css','css/pms-bitroxia-theme.css','css/pms-table-tools.css']
  .map(file => read('public/admin/assets/' + file)).join('\n');
const mobileCss = exists('public/admin/assets/css/pms-mobile.css') ? read('public/admin/assets/css/pms-mobile.css') : '';
const mobileJs = exists('public/admin/assets/js/pms-mobile.js') ? read('public/admin/assets/js/pms-mobile.js') : '';

// Remove Blade control flow while preserving authored DOM structure and classes.
// Includes are expanded for forms/page components; layouts and export templates are excluded.
function preview(source, depth = 0) {
  source = source.replace(/@php\b[\s\S]*?@endphp/g, '').replace(/\{!![\s\S]*?!!\}/g, 'Sample content');
  source = source.replace(/\{\{--[\s\S]*?--\}\}/g, '').replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, '');
  const directive = /@(include|includeIf|includeWhen|if|elseif|unless|foreach|forelse|for|while|isset|empty|auth|guest|can|cannot|error|section|push|extends|yield|stack|json|class|checked|selected|disabled|readonly|method|vite)\b/g;
  let result = '', cursor = 0, match;
  while ((match = directive.exec(source))) {
    result += source.slice(cursor, match.index);
    let end = directive.lastIndex;
    while (/\s/.test(source[end] || '') && source[end] !== '\n') end++;
    let argument = '';
    if (source[end] === '(') {
      const start = end++; let level = 1, quote = null;
      while (end < source.length && level) {
        const char = source[end++];
        if (quote) { if (char === quote && source[end-2] !== '\\') quote = null; }
        else if (char === "'" || char === '"') quote = char;
        else if (char === '(') level++;
        else if (char === ')') level--;
      }
      argument = source.slice(start+1, end-1);
    }
    if (match[1].startsWith('include') && depth < 3) {
      const resource = argument.match(/['"]([^'"]+)['"]/)?.[1];
      if (resource && !/layout|phone-fields|toasts|live-records|pwa|password|ai-assistant/.test(resource)) {
        const file = 'resources/views/' + resource.replaceAll('.', '/') + '.blade.php';
        if (exists(file)) result += preview(read(file), depth+1);
      }
    }
    cursor = end; directive.lastIndex = end;
  }
  result += source.slice(cursor);
  result = result.replace(/@(else|endif|endunless|endforeach|endforelse|endfor|endwhile|endisset|endempty|endauth|endguest|endcan|endcannot|enderror|endsection|endpush|csrf|once|endonce|break|continue)\b/g, '');
  result = result.replace(/\{\{([\s\S]*?)\}\}/g, (_, expression) => {
    if (/asset\(/.test(expression)) return '/fixture-image.svg';
    if (/route\(|url\(/.test(expression)) return '#';
    if (/\?|selected|checked|disabled|class/.test(expression)) return '';
    if (/count|total|percent|progress|->id|width|height|number/.test(expression)) return '25';
    if (/name|title|subject/.test(expression)) return 'Example project and company';
    if (/date|time|format\(/.test(expression)) return '14 Oct 2026';
    return 'Sample';
  });
  return result.replace(/<link\b[^>]*>/gi, '').replace(/<style\b[^>]*>[\s\S]*?<\/style>/gi, '');
}
const fixtures = views.flatMap(file => {
  const source = read(file);
  const layout = source.match(/@extends\(['"]([^'"]+)/)?.[1];
  const isStandalone = source.includes('<body') && !/layouts|layout\/|emails|pdf|print|payslip|invoice|receipt/.test(file);
  if (!layout && !isStandalone) return [];
  if (/pdf|print|payslip|receipt/.test(file)) return [];
  const shell = layout?.includes('superadmin') ? 'platform' : layout?.includes('developer') ? 'developer'
    : layout?.includes('frontend') ? 'public' : isStandalone ? 'auth' : 'admin';
  let content = source;
  if (layout) {
    const start = source.indexOf("@section('content')");
    const startDouble = source.indexOf('@section("content")');
    const offset = start < 0 ? startDouble : start;
    if (offset >= 0) content = source.slice(offset, source.indexOf('@endsection', offset) < 0 ? undefined : source.indexOf('@endsection', offset));
  } else content = source.match(/<body[^>]*>([\s\S]*?)<\/body>/i)?.[1] || source;
  return [{file, shell, css: style(source), html: preview(content)}];
});
const matching = process.env.PMS_MOBILE_FILTER ? fixtures.filter(f=>new RegExp(process.env.PMS_MOBILE_FILTER).test(f.file)) : fixtures;
const selected = process.env.PMS_MOBILE_LIMIT ? matching.slice(0, Number(process.env.PMS_MOBILE_LIMIT)) : matching;
const browser = process.argv[2] || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const directory = fs.mkdtempSync(path.join(os.tmpdir(), 'pms-mobile-audit-'));
let child, server;
const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
const results = [];

function frame(fixture) {
  const body = fixture.html;
  if (fixture.shell === 'admin') return `<div class="layout-wrapper"><div class="layout-container"><div class="layout-page"><nav id="layout-navbar" class="layout-navbar"><button class="layout-menu-toggle">Menu</button><span>Workspace</span></nav><div class="content-wrapper"><div class="container-xxl">${body}</div></div></div></div></div>`;
  if (fixture.shell === 'platform') return `<div class="main"><header class="top-header"><div class="left"><div class="title-wrap"><h1 class="page-title">Platform workspace</h1></div></div></header><div class="content">${body}</div></div>`;
  if (fixture.shell === 'developer') return `<div class="dev-main-wrapper"><div class="dev-body-content">${body}</div></div>`;
  return body;
}
function html(fixture) {
  const css = fixture.shell === 'admin' ? baseCss + style(layoutSources.admin)
    : fixture.shell === 'public' ? ['base','nav-hero','sections','footer-misc','pages','responsive'].map(name=>read('public/frontend/css/'+name+'.css')).join('\n')
    : style(layoutSources[fixture.shell] || '');
  return `<!doctype html><html data-pms-shell="${fixture.shell}" ${process.env.PMS_PWA_MODE ? 'data-pms-standalone' : ''}><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>${css}\n${fixture.css}\n${mobileCss}\n${process.env.PMS_PWA_MODE ? read('public/pwa.css') : ''}\n*{animation:none!important;transition:none!important}</style></head><body>${frame(fixture)}<script>${mobileJs}</script></body></html>`;
}
async function run() {
  server = http.createServer((request, response) => {
    if (request.url.startsWith('/fixture-image.svg')) {response.writeHead(200,{'Content-Type':'image/svg+xml'});response.end('<svg xmlns="http://www.w3.org/2000/svg" width="800" height="450"><rect width="800" height="450" fill="#e0e7ff"/></svg>');return;}
    response.writeHead(200, {'Content-Type':'text/html'}); response.end('<!doctype html><html><body></body></html>');
  });
  await new Promise(resolve => server.listen(0,'127.0.0.1',resolve));
  const origin = 'http://127.0.0.1:' + server.address().port;
  child = spawn(browser, ['--headless','--disable-gpu','--no-first-run','--disable-background-networking','--remote-debugging-port=0','--user-data-dir='+directory,'about:blank'], {stdio:['ignore','ignore','pipe']});
  const portFile = path.join(directory,'DevToolsActivePort');
  for (let i=0; i<150 && !fs.existsSync(portFile); i++) await delay(100);
  if (!fs.existsSync(portFile)) throw Error('Browser did not expose the local debugging endpoint');
  const port = fs.readFileSync(portFile,'utf8').split('\n')[0];
  const targets = await fetch('http://127.0.0.1:'+port+'/json/list').then(r=>r.json());
  const socket = new WebSocket(targets.find(t=>t.type==='page').webSocketDebuggerUrl);
  await new Promise(resolve=>socket.addEventListener('open',resolve,{once:true}));
  let id=0; const pending=new Map();
  socket.addEventListener('message',event=>{const message=JSON.parse(event.data);if(pending.has(message.id)){const {resolve,reject}=pending.get(message.id);pending.delete(message.id);message.error?reject(Error(message.error.message)):resolve(message.result);}});
  const send = (method,params={})=>new Promise((resolve,reject)=>{const key=++id;pending.set(key,{resolve,reject});socket.send(JSON.stringify({id:key,method,params}));});
  await send('Page.enable');
  if(process.env.PMS_PWA_MODE) await send('Emulation.setEmulatedMedia',{features:[{name:'display-mode',value:'standalone'}]}); await send('Page.navigate',{url:origin}); await delay(200);
  await send('Emulation.setDeviceMetricsOverride',{width:375,height:844,deviceScaleFactor:1,mobile:true});
  for (const fixture of selected) {
    await send('Page.navigate',{url:origin+'?page='+encodeURIComponent(fixture.file)});await delay(60);
    const {frameTree}=await send('Page.getFrameTree');
    await send('Page.setDocumentContent',{frameId:frameTree.frame.id,html:html(fixture)});
    await delay(10);
    for (const width of [320,375,430]) {
      await send('Emulation.setDeviceMetricsOverride',{width,height:844,deviceScaleFactor:1,mobile:true});
      for (const theme of ['light','dark']) {
        await send('Runtime.evaluate',{expression:`document.documentElement.setAttribute('data-pms-theme','${theme}');document.documentElement.setAttribute('data-theme','${theme}');document.documentElement.setAttribute('data-bs-theme','${theme}');window.dispatchEvent(new Event('resize'));window.PMSMobileLayout?.scan();`});
        await delay(10);
        const {result}=await send('Runtime.evaluate',{returnByValue:true,expression:`(()=>{
          const allowed='table,.table-responsive,.table-wrapper,.pms-table-scroll-host,.dataTables_scroll,.fc,.gantt,.gantt-container,.gantt-target,.gantt-matrix-wrapper,.kanban-board,.board-columns,.pms-project-nav-wrapper,.nav-tabs,.tabs,.nav-tabs-wrapper,[data-pms-mobile-scroll],.apexcharts-canvas,.chart-container,.calendar-grid,.calendar-table,.ac-calendar-scroll,.modal,.offcanvas,.drawer,.code-block,pre';
          const failures=[];const viewport=innerWidth;
          for(const el of document.body.querySelectorAll('*')){
            if(['SCRIPT','STYLE','LINK','META','OPTION','COL','COLGROUP'].includes(el.tagName)||el.closest(allowed)||el instanceof SVGElement)continue;
            const s=getComputedStyle(el),r=el.getBoundingClientRect();if(!r.width||!r.height||s.visibility==='hidden'||s.display==='none'||s.position==='fixed')continue;
            if(el.closest('#layout-menu,.sidebar,.dev-sidebar,.sidebar-overlay,.dev-sidebar-overlay,.dropdown-menu,.select2-dropdown,.pms-toast-stack,.ambient-orb,.orb,.bg-orb,.orb-archive,.welcome-balloon,[aria-hidden="true"]'))continue;
            if(s.position==='absolute'&&(s.pointerEvents==='none'||String(el.className).includes('bg-gradient-to-b')))continue;
            let closed=false;for(let parent=el.parentElement;parent;parent=parent.parentElement){const ps=getComputedStyle(parent);if(ps.opacity==='0'||ps.visibility==='hidden'){closed=true;break;}if(getComputedStyle(parent).position==='fixed'){const box=parent.getBoundingClientRect();if(box.left>=viewport-6||box.right<=0){closed=true;break;}}if(el.tagName==='IMG'&&/[a-z]*(avatar|logo)/i.test(parent.className)&&['hidden','clip'].includes(ps.overflowX)){closed=true;break;}}if(closed)continue;
            if(r.right>viewport+2||r.left< -2){const label=el.tagName.toLowerCase()+'.'+String(el.className).slice(0,90);if(!failures.some(f=>f.element===label))failures.push({element:label,left:Math.round(r.left),right:Math.round(r.right),text:el.textContent.trim().slice(0,80),parent:el.parentElement.className});}
          }
          return {viewport,failures:failures.slice(0,15)};
        })()`});
        results.push({file:fixture.file,shell:fixture.shell,width,theme,...result.value});
        if(process.env.PMS_MOBILE_SCREENSHOTS && width===375){
          fs.mkdirSync(process.env.PMS_MOBILE_SCREENSHOTS,{recursive:true});
          const shot=await send('Page.captureScreenshot',{format:'png'});
          fs.writeFileSync(path.join(process.env.PMS_MOBILE_SCREENSHOTS,fixture.file.replace(/[^a-z0-9]/gi,'_')+'_'+theme+'.png'),Buffer.from(shot.data,'base64'));
        }
      }
    }
    if (results.length%120===0) console.log(JSON.stringify({pages:results.length/6,total:selected.length,failedCases:results.filter(r=>r.failures.length).length}));
  }
  const componentResults=[];
  for (const width of [320,375,430,768,1024,1440]) {
    await send('Page.navigate',{url:origin+'?components='+width});await delay(60);
    await send('Emulation.setDeviceMetricsOverride',{width,height:844,deviceScaleFactor:1,mobile:width<768});
    const {frameTree}=await send('Page.getFrameTree');
    const markup=`<button id="hamburgerBtn">Menu</button><aside id="sidebar" class="sidebar" style="position:fixed;left:0;top:0;width:280px;background:white;z-index:2000"><button id="first">Close</button><a href="#" id="last">Last</a></aside><div id="sidebarOverlay"></div><section id="grid" style="display:grid;grid-template-columns:repeat(3,1fr);width:100%;gap:20px"><div><h3>Card</h3><input value="Value"></div><div><h3>Card</h3><input></div><div><h3>Card</h3><input></div></section><div id="tableHost" class="table-responsive"><table style="width:900px"><tr><td><div class="dropdown"><button id="actions" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu"><button class="dropdown-item">View</button><button class="dropdown-item">Edit</button><form><input name="record" value="42"><button class="dropdown-item">Archive</button></form></div></div></td><td>Data</td></tr></table></div><div id="testModal" class="modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">Details</div><div class="modal-body"><div style="height:1400px">Long content</div></div><div class="modal-footer"><button data-bs-dismiss="modal">Close</button></div></div></div></div>`;
    await send('Page.setDocumentContent',{frameId:frameTree.frame.id,html:`<html data-pms-shell="platform"><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>${baseCss}${mobileCss}</style></head><body>${markup}<script>${read('public/admin/assets/vendor/libs/popper/popper.js')}</script><script>${read('public/admin/assets/vendor/js/bootstrap.js')}</script><script>${mobileJs}</script></body></html>`});await delay(100);
    const {result}=await send('Runtime.evaluate',{awaitPromise:true,returnByValue:true,expression:`(async()=>{
      const errors=[],check=(ok,name)=>{if(!ok)errors.push(name);},wait=()=>new Promise(r=>setTimeout(r,100));
      window.PMSMobileLayout.scan();
      const sidebar=document.getElementById('sidebar'),toggle=document.getElementById('hamburgerBtn');
      if(innerWidth<992){toggle.focus();sidebar.classList.add('open');await wait();check(document.activeElement.id==='first','drawer focuses first control');check(document.body.hasAttribute('data-pms-drawer-open'),'drawer locks page');document.getElementById('last').focus();document.dispatchEvent(new KeyboardEvent('keydown',{key:'Tab',bubbles:true}));check(document.activeElement.id==='first','drawer traps focus');document.dispatchEvent(new KeyboardEvent('keydown',{key:'Escape',bubbles:true}));await wait();check(!sidebar.classList.contains('open')&&!document.body.hasAttribute('data-pms-drawer-open'),'escape closes drawer');check(document.activeElement===toggle,'focus restored');}
      sidebar.style.display='none';
      const grid=document.getElementById('grid');check(getComputedStyle(grid).gridTemplateColumns.split(' ').length===(innerWidth<768?1:3),'phone grid stacks and desktop grid preserved');
      const host=document.getElementById('tableHost');if(innerWidth<768)check(host.scrollWidth>host.clientWidth&&getComputedStyle(host).overflowX==='auto','wide table scrolls internally');
      const action=document.getElementById('actions');bootstrap.Dropdown.getOrCreateInstance(action).show();await wait();const menu=document.querySelector('.dropdown-menu'),rect=menu.getBoundingClientRect();if(innerWidth<768){check(menu.matches(':popover-open'),'table menu enters top layer');check(rect.left>=0&&rect.right<=innerWidth&&rect.top>=0&&rect.bottom<=innerHeight,'table menu fits viewport');check(menu.querySelector('input').value==='42','action form preserved');}bootstrap.Dropdown.getInstance(action).hide();check(!menu.hasAttribute('popover'),'dropdown state restored');
      const modal=bootstrap.Modal.getOrCreateInstance(document.getElementById('testModal'));modal.show();await wait();if(innerWidth<768){const content=document.querySelector('.modal-content'),body=document.querySelector('.modal-body'),r=content.getBoundingClientRect();check(r.left>=0&&r.right<=innerWidth&&r.height<=innerHeight,'modal fits phone');check(body.scrollHeight>body.clientHeight&&getComputedStyle(body).overflowY==='auto','modal body scrolls');}modal.hide();await wait();return errors;
    })()`});componentResults.push({width,failures:result.value||[result.description||'evaluation failed']});
  }
  fs.writeFileSync(path.join(root,'docs/mobile-responsive-components.json'),JSON.stringify(componentResults,null,2));
  console.log(JSON.stringify({componentCases:componentResults.length,componentFailures:componentResults.filter(r=>r.failures.length).length}));
  if(componentResults.some(r=>r.failures.length))process.exitCode=1;
  const report={templates:views.length,pages:selected.length,cases:results.length,failedCases:results.filter(r=>r.failures.length).length,results};
  const reportFile=path.join(root,'docs/'+(process.env.PMS_PWA_MODE ? 'pwa-responsive-audit.json' : process.env.PMS_MOBILE_BASELINE ? 'mobile-responsive-baseline.json' : process.env.PMS_MOBILE_FILTER || process.env.PMS_MOBILE_LIMIT ? 'mobile-responsive-targeted.json' : 'mobile-responsive-audit.json'));fs.mkdirSync(path.dirname(reportFile),{recursive:true});fs.writeFileSync(reportFile,JSON.stringify(report,null,2));
  console.log(JSON.stringify({templates:report.templates,pages:report.pages,cases:report.cases,failedCases:report.failedCases,report:reportFile}));
  socket.close();
  if(report.failedCases)process.exitCode=1;
}
run().catch(error=>{console.error(error.message);process.exitCode=1;}).finally(async()=>{
  if(server)server.close();if(child)child.kill();await delay(500);
  fs.rmSync(directory,{recursive:true,force:true,maxRetries:10,retryDelay:200});
});
