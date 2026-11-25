const fs = require('node:fs');
const path = require('node:path');
const os = require('node:os');
const {execFileSync} = require('node:child_process');
const root = path.resolve(__dirname, '../..');
const read = name => fs.readFileSync(path.join(root, name), 'utf8');
const view = read('resources/views/admin/holidays/calendar.blade.php');
const pageCss = [...view.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/g)].map(match => match[1]).join('\n');
const css = ['public/admin/assets/vendor/css/core.css', 'public/admin/assets/css/demo.css', 'public/admin/assets/css/pms-refresh.css', 'public/admin/assets/css/pms-bitroxia-theme.css', 'public/admin/assets/css/pms-table-tools.css'].map(read).join('\n') + '\n' + pageCss;
const library = read('public/admin/assets/vendor/libs/fullcalendar/index.global.min.js');
const tools = read('public/admin/assets/js/pms-table-tools.js');
const options = view.slice(view.indexOf('        const calendar = new FullCalendar.Calendar'), view.indexOf('        calendar.render();'));
const height = options.match(/height:\s*([^,]+)/)[1];
const browser = process.argv[2] || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const directory = fs.mkdtempSync(path.join(os.tmpdir(), 'pms-holiday-calendar-'));
try {
  for (const theme of ['light', 'dark']) for (const width of [1280, 390]) {
    const filename = path.join(directory, 'calendar.html');
    fs.writeFileSync(filename, `<!DOCTYPE html><html data-pms-theme="${theme}"><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>${css}\n*{animation:none!important;transition:none!important}</style></head><body><div class="holiday-calendar-page"><div class="holiday-calendar-shell"><div class="content-wrapper"><div class="address-card-elevated p-4"><div id="calendar" data-pms-export="off"></div></div></div></div></div><script>${library}</script><script>
      const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView:'dayGridMonth',initialDate:'2026-01-01',height:${height},
        headerToolbar:{left:'prev,next today',center:'title',right:'dayGridMonth,timeGridWeek,timeGridDay,listMonth'},
        events:[{title:'Company holiday',start:'2026-01-14'}]
      });calendar.render();
      window.PMS_DISABLE_EXPORT_SCREENSHOT = true;
    </script><script>${tools}</script><script>
      setTimeout(() => {
        const failures=[];
        const host=document.getElementById('calendar');
        for (const month of ['2026-01-01','2026-02-01','2026-08-01']) {
          calendar.gotoDate(month);
          if(host.querySelectorAll('.fc-daygrid-day').length !== 42) failures.push(month+': month cells missing');
          if(host.querySelectorAll('.fc-col-header-cell').length !== 7) failures.push(month+': weekday headings missing');
          const bounds=host.getBoundingClientRect();
          for(const day of host.querySelectorAll('.fc-daygrid-day-number')) {
            const r=day.getBoundingClientRect();
            if(r.left < bounds.left-1 || r.right > bounds.right+1) failures.push(month+': date clipped');
          }
          for(const scroller of host.querySelectorAll('.fc-scroller')) {
            if(scroller.scrollWidth > scroller.clientWidth+2 || scroller.scrollHeight > scroller.clientHeight+2) failures.push(month+': nested calendar scrolling');
          }
          if(host.scrollWidth > host.clientWidth+2) failures.push(month+': toolbar or calendar overflows');
        }
        if(host.querySelector('.pms-table-tools,.pms-table-selection-cell,.pms-table-scroll-host')) failures.push('Record-table controls injected into calendar');
        for(const mode of ['timeGridWeek','timeGridDay','listMonth']) {calendar.changeView(mode);if(!host.querySelector('.fc-view')) failures.push(mode+': view missing');}
        document.body.innerHTML='<pre id="result">'+JSON.stringify({theme:'${theme}',width:${width},failures})+'</pre>';
      },200);
    </script></body></html>`);
    const output = execFileSync(browser, ['--headless','--disable-gpu','--no-first-run','--disable-background-networking','--virtual-time-budget=1500', '--window-size='+width+',900','--user-data-dir='+path.join(directory,'browser'),'--dump-dom','file:///'+filename.replaceAll('\\','/')], {encoding:'utf8',timeout:60000,maxBuffer:4*1024*1024,stdio:['ignore','pipe','ignore']});
    const match=output.match(/<pre id="result">([\s\S]*?)<\/pre>/);
    if(!match) throw Error('Browser did not return calendar layout results');
    const result=JSON.parse(match[1].replaceAll('&amp;','&').replaceAll('&lt;','<').replaceAll('&gt;','>'));
    console.log(JSON.stringify(result));
    if(result.failures.length) process.exitCode=1;
  }
} finally {
  fs.rmSync(directory,{recursive:true,force:true,maxRetries:5,retryDelay:200});
}
