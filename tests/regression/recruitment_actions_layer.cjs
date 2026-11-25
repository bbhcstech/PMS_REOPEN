const fs = require('node:fs'), path = require('node:path'), os = require('node:os');
const {execFileSync} = require('node:child_process');
const root = path.resolve(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root,file),'utf8');
const source = read('resources/views/admin/recruitment/index.blade.php');
const pageCss = [...source.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/g)].map(m=>m[1]).join('\n').replace(/@import[^;]+;/g,'');
const css = ['vendor/css/core.css','css/pms-refresh.css','css/pms-bitroxia-theme.css'].map(file=>read('public/admin/assets/'+file)).join('\n')+'\n'+pageCss;
const scripts = ['public/admin/assets/vendor/libs/popper/popper.js','public/admin/assets/vendor/js/bootstrap.js','public/admin/assets/js/pms-recruitment-actions.js'].map(read);
const directory=fs.mkdtempSync(path.join(os.tmpdir(),'pms-recruitment-menu-'));
try {
 for(const theme of ['light','dark']) for(const fallback of [false,true]) {
  const filename=path.join(directory,'menu.html');
  fs.writeFileSync(filename,`<html data-pms-theme="${theme}"><head><style>${css}\n*{animation:none!important;transition:none!important}#clip{overflow:auto;width:320px;height:100px;transform:translateZ(0);margin:100px 20px}#toggle{margin-left:240px}#cover{position:relative;z-index:50;height:500px;background:#999}</style></head><body><div id="clip"><table class="table rec-table"><tbody><tr><td><div class="dropdown recruitment-actions-dropdown"><button id="toggle" data-bs-toggle="dropdown" class="btn">Actions</button><ul class="dropdown-menu dropdown-menu-end recruitment-actions-menu"><li class="dropdown-header">Admin Management</li>${['open','in_progress','closed','delete'].map(action=>`<li><form action="/recruitment/1/${action}" method="POST"><input type="hidden" name="status" value="${action}"><button type="submit" class="dropdown-item">${action}</button></form></li>`).join('')}</ul></div></td></tr></tbody></table></div><div id="cover"></div>${scripts.map(script=>'<script>'+script+'</script>').join('')}<script>
  const failures=[];const menu=document.querySelector('.recruitment-actions-menu');const toggle=document.getElementById('toggle');
  if(${fallback}) menu.showPopover=undefined;
  const original=menu.parentElement;const actions=[...menu.querySelectorAll('form')].map(f=>f.getAttribute('action')).join(',');
  bootstrap.Dropdown.getOrCreateInstance(toggle).show();
  setTimeout(()=>{
    const r=menu.getBoundingClientRect();
    if(!r.width||!r.height||r.left<0||r.top<0||r.right>innerWidth||r.bottom>innerHeight)failures.push('Menu outside viewport');
    const hit=document.elementFromPoint(r.left+r.width/2,r.bottom-10);if(!menu.contains(hit))failures.push('Menu hidden behind clipped table or next section');
    if(!${fallback}&&!menu.matches(':popover-open'))failures.push('Menu not in top layer');
    if(menu.querySelectorAll('form').length!==4||actions!==[...menu.querySelectorAll('form')].map(f=>f.getAttribute('action')).join(','))failures.push('Form actions changed');
    bootstrap.Dropdown.getInstance(toggle).hide();
    if(menu.matches(':popover-open')||menu.classList.contains('show')||menu.parentElement!==original)failures.push('Menu did not close/restore');
    bootstrap.Dropdown.getInstance(toggle).show();
    menu.querySelector('.dropdown-item').dispatchEvent(new KeyboardEvent('keydown',{key:'Escape',bubbles:true}));
    if(menu.classList.contains('show'))failures.push('Escape did not close menu');
    bootstrap.Dropdown.getInstance(toggle).show();
    document.getElementById('clip').dispatchEvent(new Event('scroll'));
    if(menu.classList.contains('show'))failures.push('Menu did not close on table scroll');
    document.body.innerHTML='<pre id="result">'+JSON.stringify({theme:'${theme}',fallback:${fallback},failures})+'</pre>';
  },100);
 </script></body></html>`);
  const output=execFileSync('C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',['--headless','--disable-gpu','--disable-background-networking','--no-first-run','--virtual-time-budget=1000','--window-size=900,900','--user-data-dir='+path.join(directory,'browser'),'--dump-dom','file:///'+filename.replaceAll('\\','/')],{encoding:'utf8',timeout:60000,maxBuffer:4*1024*1024,stdio:['ignore','pipe','ignore']});
  const match=output.match(/<pre id="result">([\s\S]*?)<\/pre>/);if(!match)throw Error('Browser returned no dropdown result');const result=JSON.parse(match[1]);console.log(JSON.stringify(result));if(result.failures.length)process.exitCode=1;
 }
}finally{fs.rmSync(directory,{recursive:true,force:true,maxRetries:5,retryDelay:200});}
