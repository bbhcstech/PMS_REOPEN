// Generate a browser fixture from the real event markup and styles:
// node tests/regression/event_details_ui.mjs <temporary-html-path>
import { readFileSync, writeFileSync } from 'node:fs';
import assert from 'node:assert/strict';

const output = process.argv[2];
assert.ok(output, 'Provide a temporary HTML output path');
const read = path => readFileSync(new URL('../../' + path, import.meta.url), 'utf8');
const view = read('resources/views/admin/events/index.blade.php');
const styles = view.match(/<style>([\s\S]*?)<\/style>/)[1];
const dark = read('resources/views/admin/events/dark-theme.blade.php');
const core = read('public/admin/assets/vendor/css/core.css');
const theme = read('public/admin/assets/css/pms-bitroxia-theme.css');
const showDetails = view.match(/    function showEventDetails\(id\) \{[\s\S]*?(?=    function openUploadModal\()/)[0].replace(/\{\{--[\s\S]*?--\}\}/g, '');
const escape = view.match(/    function escapeHtml\(str\) \{[\s\S]*?(?=    document.addEventListener)/)[0];
const fixture = {
    event: { id: 1, title: 'Annual Day', description: 'Does Nothing Chill Day', event_type: 'Sports Event', status: 'published',
        organizer: { name: 'Admin User' }, location_type: 'physical', location: '', rsvp_required: true },
    photos: [], rsvp_counts: { going: 0, maybe: 0, not_going: 0 }, can_manage: true,
    formatted_start_date: '08 Oct 2026', formatted_start_time: '09:00 AM', banner_url: null,
};
const checks = `
function rgb(value) { return (value.match(/[0-9.]+/g) || []).slice(0, 3).map(Number); }
function luminance(color) {
    return color.map(v => { v /= 255; return v <= 0.04045 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4; })
        .reduce((sum, v, i) => sum + v * [0.2126, 0.7152, 0.0722][i], 0);
}
function background(element) {
    const layers = [];
    while (element) {
        const value = getComputedStyle(element).backgroundColor;
        const components = (value.match(/[0-9.]+/g) || []).map(Number);
        const alpha = components.length === 4 ? components[3] : 1;
        if (alpha > 0) layers.push({ color: components.slice(0, 3), alpha });
        if (alpha === 1) break;
        element = element.parentElement;
    }
    let result = [255, 255, 255];
    for (const layer of layers.reverse()) {
        result = layer.color.map((v, i) => v * layer.alpha + result[i] * (1 - layer.alpha));
    }
    return result;
}
setTimeout(function() {
    const failures = [];
    const results = [];
    for (const attribute of ['data-pms-theme', 'data-bs-theme', 'data-theme']) {
        for (const name of ['data-pms-theme', 'data-bs-theme', 'data-theme']) document.documentElement.removeAttribute(name);
        document.documentElement.setAttribute(attribute, 'dark');
        for (const selector of ['.event-description-panel', '.event-memories-panel', '.event-memories-empty', '.rsvp-stat-going', '.rsvp-stat-maybe', '.rsvp-stat-not_going']) {
            const element = document.querySelector(selector);
            if (!element || luminance(background(element)) > 0.12) failures.push(attribute + ': pale/missing surface ' + selector);
        }
        for (const selector of ['.event-description-panel h6', '.event-description-panel p', '.event-memories-panel h6', '.event-memories-empty p', '.rsvp-stat-going span', '.rsvp-stat-going h3', '.rsvp-stat-maybe span', '.rsvp-stat-maybe h3', '.rsvp-stat-not_going span', '.rsvp-stat-not_going h3']) {
            const element = document.querySelector(selector);
            if (!element) { failures.push('Missing ' + selector); continue; }
            const style = getComputedStyle(element);
            const foreground = luminance(rgb(style.webkitTextFillColor === 'currentcolor' ? style.color : style.webkitTextFillColor));
            const bg = luminance(background(element));
            const contrast = (Math.max(foreground, bg) + 0.05) / (Math.min(foreground, bg) + 0.05);
            if (contrast < 4.5) failures.push(attribute + ': contrast ' + contrast.toFixed(2) + ' ' + selector);
        }
        results.push(attribute);
    }
    document.getElementById('browser-result').textContent = JSON.stringify({ pass: failures.length === 0, failures, themes: results });
}, 100);
`;
writeFileSync(output, `<!doctype html><html data-pms-theme="dark"><head><meta charset="utf-8"><style>${core}</style><style>${styles}</style>${dark}<style>${theme}</style></head><body><div id="eventDetailsModal" class="events-section" style="max-width:1100px;margin:20px auto"><div class="modal-content"><div id="detailModalBody" class="p-4"></div></div></div><pre id="browser-result">pending</pre><script>
let currentLightboxPhotos, currentLightboxEventId, currentLightboxCanManage;
const bootstrap = { Modal: class { static getInstance() { return null; } show() {} } };
const fetch = async () => ({ ok: true, json: async () => (${JSON.stringify(fixture)}) });
${escape}
${showDetails}
showEventDetails(1);
${checks}
</script></body></html>`);
console.log(output);
