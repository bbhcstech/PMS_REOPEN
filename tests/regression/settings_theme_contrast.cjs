// Offline browser check using the actual shared stylesheet and page styles.
// Usage: node tests/regression/settings_theme_contrast.cjs [browser executable]
const fs = require('node:fs');
const path = require('node:path');
const os = require('node:os');
const { execFileSync } = require('node:child_process');
const root = path.resolve(__dirname, '../..');
const browser = process.argv[2] || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const directory = fs.mkdtempSync(path.join(os.tmpdir(), 'pms-theme-'));
const fixtures = [];
function visit(directory) {
  for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
    const filename = path.join(directory, entry.name);
    if (entry.isDirectory()) { visit(filename); continue; }
    if (!entry.name.endsWith('.blade.php')) continue;
    const source = fs.readFileSync(filename, 'utf8');
    const page = source.match(/class="(pms-settings-page [^"]+)"/);
    if (!page) continue;
    const notice = source.match(/<div class="alert pms-readonly-notice[\s\S]*?<\/span>[\s\S]*?<\/div>\s*<\/div>/);
    const styles = [...source.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/g)].map(match => match[1]).join('\n');
    fixtures.push({ name: path.relative(root, filename), page: page[1], styles, notice: notice ? notice[0] : '' });
  }
}
visit(path.join(root, 'resources/views/admin/settings'));
const php = process.env.PMS_TEST_PHP || 'D:/xampp/php/php.exe';
const companyPage = JSON.parse(execFileSync(php, [path.join(__dirname, 'company_profile_theme_fixture.php')], {encoding:'utf8', cwd:root}));
const companyStyles = fixtures.find(fixture => fixture.page.includes('company-profile-page')).styles;
fixtures.push({name:'Associate Manager / rendered Company Profile', styles:companyStyles, html:companyPage.html});
const css = fs.readFileSync(path.join(root, 'public/admin/assets/css/pms-bitroxia-theme.css'), 'utf8');
const foundation = ['public/admin/assets/vendor/css/core.css','public/admin/assets/css/demo.css','public/admin/assets/css/pms-refresh.css'].map(file => fs.readFileSync(path.join(root, file), 'utf8')).join('\n');
const menu = fs.readFileSync(path.join(root, 'resources/views/admin/layout/manu.blade.php'), 'utf8');
const menuStyles = [...menu.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/g)].map(match => match[1]).join('\n');
const script = `
const fixtures = ${JSON.stringify(fixtures)};
const failures = []; let checks = 0; let minimum = Infinity;
function rgb(value) { return (value.match(/[\\d.]+/g) || []).slice(0,3).map(Number); }
function lum(c) { return c.map(v => {v /= 255; return v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4;}).reduce((s,v,i) => s + v * [.2126,.7152,.0722][i], 0); }
function check(element, name) {
  const style = getComputedStyle(element); let backgroundRgb = [255,255,255];
  const ancestors = []; for (let parent = element; parent; parent = parent.parentElement) ancestors.unshift(parent);
  for (const parent of ancestors) {
    const values = getComputedStyle(parent).backgroundColor.match(/[\\d.]+/g).map(Number);
    const alpha = values.length > 3 ? values[3] : 1;
    backgroundRgb = backgroundRgb.map((channel, index) => values[index] * alpha + channel * (1-alpha));
  }
  const foreground = lum(rgb(style.webkitTextFillColor || style.color));
  const background = lum(backgroundRgb);
  const contrast = (Math.max(foreground, background) + .05) / (Math.min(foreground, background) + .05);
  checks++; minimum = Math.min(minimum, contrast);
  if (!(contrast >= 4.5)) failures.push(name + ': contrast ' + contrast.toFixed(2));
}
for (const fixture of fixtures) {
  document.getElementById('page-style').textContent = fixture.styles;
  if (fixture.html) {
    document.getElementById('fixture').innerHTML = fixture.html;
    if (getComputedStyle(document.getElementById('fileSelectedBadge')).display !== 'none') failures.push('Unselected file badge must stay hidden');
    // Audit the entire real page, including validation and reset-dialog states.
    document.querySelectorAll('.modal,.validation-feedback-pill,#fileSelectedBadge').forEach(element => {element.classList.remove('d-none'); element.style.display = 'block';});
  } else {
  document.getElementById('fixture').innerHTML = '<div class="' + fixture.page + '">' + fixture.notice + '<div class="input-group-custom"><input readonly value="Company name"><input disabled value="Company email"><select disabled><option>Company option</option></select></div><div class="validation-feedback-pill is-visible">Invalid email address</div><span id="currentLogoBadge" class="badge" style="background:#eef2ff;color:#065f46">Current branding</span><div class="phone-combo-group"><span class="select2-selection__rendered">+91</span></div></div>';
  document.querySelector('.pms-settings-page').insertAdjacentHTML('beforeend', '<span class="pms-readonly-status" style="background:#f1f5f9;color:#64748b"><i class="text-muted">Lock</i> Read-Only</span><span id="phoneDigitCounter" class="badge" style="background:#fef2f2;color:#dc2626"><i class="text-danger">Error</i><span>3/10 digits</span></span>');
  document.getElementById('fixture').insertAdjacentHTML('beforeend', '<div class="select2-dropdown"><div class="select2-results__option select2-results__option--highlighted" aria-selected="true">Selected country</div></div>');
  }
  for (const mode of ['light', 'data-pms-theme', 'data-theme', 'data-bs-theme', 'body', 'light']) {
    for (const attribute of ['data-pms-theme','data-theme','data-bs-theme']) document.documentElement.removeAttribute(attribute);
    document.body.classList.toggle('dark-mode', mode === 'body');
    if (mode !== 'light' && mode !== 'body') document.documentElement.setAttribute(mode, 'dark');
    else if (mode === 'light') document.documentElement.setAttribute('data-pms-theme', 'light');
    if (fixture.html) {
      for (const element of document.querySelectorAll('#fixture *')) {
        if (!element.getClientRects().length || getComputedStyle(element).visibility === 'hidden') continue;
        const text = [...element.childNodes].some(node => node.nodeType === 3 && node.textContent.trim());
        if (text || element.matches('input:not([type="hidden"]):not([type="file"]),select')) check(element, fixture.name + '/' + mode + '/' + (element.id || element.className || element.tagName));
      }
      const counter = document.getElementById('phoneDigitCounter');
      const original = counter.innerHTML;
      for (const state of ['success', 'danger']) {
        counter.innerHTML = '<i class="text-' + state + '"></i><span>10/10 digits</span>';
        check(counter, fixture.name + '/' + mode + '/phone-' + state);
        check(counter.querySelector('span'), fixture.name + '/' + mode + '/phone-' + state + '-text');
      }
      counter.innerHTML = original;
    } else {
    for (const element of document.querySelectorAll('.pms-readonly-notice strong,.pms-readonly-notice span')) check(element, fixture.name + '/' + mode + '/notice');
    if (mode !== 'light') for (const element of document.querySelectorAll('input,select,.validation-feedback-pill,#currentLogoBadge,.select2-selection__rendered,.pms-readonly-status,.pms-readonly-status i,#phoneDigitCounter,#phoneDigitCounter span,#phoneDigitCounter i,.select2-results__option')) check(element, fixture.name + '/' + mode + '/' + element.tagName);
    if (!document.querySelector('input:disabled') || !document.querySelector('input[readonly]')) failures.push('Read-only behavior changed');
    }
  }
}
document.body.innerHTML = '<pre id="result">' + JSON.stringify({pages:fixtures.length, checks, minimum:minimum.toFixed(2), failures}) + '</pre>';
`;
try {
  const filename = path.join(directory, 'contrast.html');
  fs.writeFileSync(filename, `<html><head><meta name="viewport" content="width=device-width, initial-scale=1"><style>${foundation}\n${css}</style><style id="page-style"></style><style>${menuStyles}\n*{animation:none!important;transition:none!important}</style></head><body><div id="fixture"></div><script>${script}</script></body></html>`);
  for (const viewport of ['1366,900', '390,844']) {
  const output = execFileSync(browser, ['--headless', '--disable-gpu', '--no-first-run', '--disable-background-networking', '--window-size='+viewport, `--user-data-dir=${path.join(directory, 'browser')}`, '--dump-dom', 'file:///' + filename.replaceAll('\\', '/')], { encoding:'utf8', maxBuffer: 8 * 1024 * 1024, timeout:60000, stdio:['ignore','pipe','ignore'] });
  const match = output.match(/<pre id="result">([\s\S]*?)<\/pre>/);
  if (!match) throw new Error('Browser did not return contrast results');
  const result = JSON.parse(match[1].replaceAll('&gt;', '>').replaceAll('&lt;', '<').replaceAll('&amp;', '&'));
  console.log(JSON.stringify({viewport, ...result}, null, 2));
  if (!result.checks || result.failures.length) process.exitCode = 1;
  }
  if (process.env.PMS_THEME_PREVIEW) {
    fs.mkdirSync(process.env.PMS_THEME_PREVIEW, {recursive:true});
    for (const theme of ['light','dark']) {
      const preview = path.join(directory, 'preview.html');
      const html = companyPage.html.replace(/<div class="alert alert-(?:success|danger)[\s\S]*?<\/div>\s*<button[\s\S]*?<\/div>/g, '');
      fs.writeFileSync(preview, `<html data-pms-theme="${theme}"><head><meta name="viewport" content="width=device-width, initial-scale=1"><style>${foundation}\n${css}\n${companyStyles}\n${menuStyles}\n*{animation:none!important;transition:none!important}</style></head><body>${html}</body></html>`);
      execFileSync(browser, ['--headless','--disable-gpu','--no-first-run','--disable-background-networking','--window-size=1366,1400',`--user-data-dir=${path.join(directory,'browser')}`,`--screenshot=${path.resolve(process.env.PMS_THEME_PREVIEW, theme+'.png')}`, 'file:///'+preview.replaceAll('\\','/')], {timeout:60000, stdio:'ignore'});
    }
  }
} finally {
  // Only remove this test's own temporary directory.
  fs.rmSync(directory, { recursive:true, force:true, maxRetries:5, retryDelay:200 });
}
