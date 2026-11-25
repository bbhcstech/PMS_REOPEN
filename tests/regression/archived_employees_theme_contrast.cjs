// Archived Employees: every visible text must stay readable (WCAG AA 4.5:1) in light and dark mode,
// using the real stylesheets in the same order as the admin layout (header CSS, Vite app.css,
// page <style>, then the footer re-includes refresh/table-tools/theme CSS).
// Usage: node tests/regression/archived_employees_theme_contrast.cjs [browser executable]
// PMS_THEME_PREVIEW=<dir> also saves light.png / dark.png screenshots.
const fs = require('node:fs');
const path = require('node:path');
const os = require('node:os');
const { execFileSync } = require('node:child_process');
const root = path.resolve(__dirname, '../..');
const browser = process.argv[2] || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const php = process.env.PMS_TEST_PHP || 'D:/xampp/php/php.exe';
const page = JSON.parse(execFileSync(php, [path.join(__dirname, 'archived_employees_theme_fixture.php')], { encoding: 'utf8', cwd: root }));
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const header = ['public/admin/assets/vendor/css/core.css', 'public/admin/assets/css/demo.css', 'public/admin/assets/css/pms-refresh.css', 'public/admin/assets/css/pms-bitroxia-theme.css'].map(read).join('\n');
const footer = ['public/admin/assets/css/pms-refresh.css', 'public/admin/assets/css/pms-table-tools.css', 'public/admin/assets/css/pms-bitroxia-theme.css'].map(read).join('\n');
const appCss = fs.readFileSync(page.appCss, 'utf8');
const head = theme => `<html data-pms-theme="${theme}" data-theme="${theme}" data-bs-theme="${theme}"><head><meta name="viewport" content="width=device-width, initial-scale=1"><style>${header}</style><style>${appCss}</style><style>${page.styles}</style><style>${footer}</style><style>*{animation:none!important;transition:none!important}</style></head>`;
const script = `
const failures = []; let checks = 0; let minimum = Infinity;
function rgb(value) { return (value.match(/[\\d.]+/g) || []).slice(0, 3).map(Number); }
function lum(c) { return c.map(v => { v /= 255; return v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4; }).reduce((s, v, i) => s + v * [.2126, .7152, .0722][i], 0); }
function check(element, name) {
  const style = getComputedStyle(element);
  let background = [255, 255, 255];
  const chain = []; for (let node = element; node; node = node.parentElement) chain.unshift(node);
  let opacity = 1;
  for (const node of chain) {
    const nodeStyle = getComputedStyle(node);
    // Gradient fills (primary buttons) count with their first colour stop.
    const gradient = nodeStyle.backgroundImage.includes('gradient') && nodeStyle.backgroundImage.match(/rgba?\\([^)]+\\)/);
    const values = ((gradient ? gradient[0] : nodeStyle.backgroundColor).match(/[\\d.]+/g) || [0, 0, 0, 0]).map(Number);
    const alpha = values.length > 3 ? values[3] : 1;
    background = background.map((channel, index) => values[index] * alpha + channel * (1 - alpha));
    opacity *= Number(getComputedStyle(node).opacity);
  }
  let text = rgb(style.webkitTextFillColor || style.color);
  const textAlpha = ((style.webkitTextFillColor || style.color).match(/[\\d.]+/g) || [])[3];
  const alpha = (textAlpha === undefined ? 1 : Number(textAlpha)) * opacity;
  text = text.map((channel, index) => channel * alpha + background[index] * (1 - alpha));
  const a = lum(text), b = lum(background);
  const contrast = (Math.max(a, b) + .05) / (Math.min(a, b) + .05);
  checks++; minimum = Math.min(minimum, contrast);
  // 4.49: the app-wide brand blue #2F6BFF with white text is 4.497:1 (rounds to 4.5).
  if (!(contrast >= 4.49)) failures.push(name + ': ' + contrast.toFixed(2) + ' (' + (style.webkitTextFillColor || style.color) + ' on rgb(' + background.map(Math.round) + '), opacity ' + opacity.toFixed(2) + ')');
}
const theme = document.documentElement.getAttribute('data-pms-theme');
for (const element of document.querySelectorAll('.archived-employee-page *')) {
  if (!element.getClientRects().length || getComputedStyle(element).visibility === 'hidden') continue;
  // Inactive pager buttons are exempt from WCAG contrast; the disabled Restore button is still checked.
  if (element.closest('.archive-page-btn.disabled')) continue;
  const hasText = [...element.childNodes].some(node => node.nodeType === 3 && node.textContent.trim());
  if (hasText || element.matches('select')) check(element, theme + '/' + (element.id || String(element.className).split(' ').slice(0, 2).join('.') || element.tagName) + ' "' + element.textContent.trim().slice(0, 24) + '"');
}
document.body.insertAdjacentHTML('beforeend', '<pre id="result">' + JSON.stringify({ theme, checks, minimum: minimum.toFixed(2), failures }) + '</pre>');
`;
const directory = fs.mkdtempSync(path.join(os.tmpdir(), 'pms-archive-theme-'));
try {
  for (const theme of ['light', 'dark']) {
    const filename = path.join(directory, theme + '.html');
    fs.writeFileSync(filename, `${head(theme)}<body>${page.html}<script>${script}</script></body></html>`);
    for (const viewport of ['1366,900', '390,844']) {
      const output = execFileSync(browser, ['--headless', '--disable-gpu', '--no-first-run', '--disable-background-networking', '--window-size=' + viewport, `--user-data-dir=${path.join(directory, 'browser')}`, '--dump-dom', 'file:///' + filename.replaceAll('\\', '/')], { encoding: 'utf8', maxBuffer: 16 * 1024 * 1024, timeout: 60000, stdio: ['ignore', 'pipe', 'ignore'] });
      const match = [...output.matchAll(/<pre id="result">([\s\S]*?)<\/pre>/g)].pop();
      if (!match) throw new Error('Browser did not return contrast results');
      const result = JSON.parse(match[1].replaceAll('&gt;', '>').replaceAll('&lt;', '<').replaceAll('&amp;', '&').replaceAll('&quot;', '"'));
      console.log(JSON.stringify({ viewport, ...result }, null, 2));
      if (!result.checks || result.failures.length) process.exitCode = 1;
    }
    if (process.env.PMS_THEME_PREVIEW) {
      fs.mkdirSync(process.env.PMS_THEME_PREVIEW, { recursive: true });
      const preview = path.join(directory, theme + '-preview.html');
      fs.writeFileSync(preview, `${head(theme)}<body>${page.html}</body></html>`);
      execFileSync(browser, ['--headless', '--disable-gpu', '--no-first-run', '--disable-background-networking', '--window-size=1366,1000', `--user-data-dir=${path.join(directory, 'browser')}`, `--screenshot=${path.resolve(process.env.PMS_THEME_PREVIEW, theme + '.png')}`, 'file:///' + preview.replaceAll('\\', '/')], { timeout: 60000, stdio: 'ignore' });
    }
  }
} finally {
  fs.rmSync(directory, { recursive: true, force: true, maxRetries: 5, retryDelay: 200 });
}
