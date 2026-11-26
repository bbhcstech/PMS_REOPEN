// Run with node tests/regression/employee_org_tree_browser.cjs. Set CHROME_PATH to use another Chromium binary.
const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');
const path = require('node:path');
const { pathToFileURL } = require('node:url');

const root = path.resolve(__dirname, '../..');
const index = fs.readFileSync(path.join(root, 'resources/views/admin/employees/index.blade.php'), 'utf8');
assert.ok(index.includes("@include('admin.employees.partials.org-tree')"), 'Employees page includes the organization tree');
const view = fs.readFileSync(path.join(root, 'resources/views/admin/employees/partials/org-tree.blade.php'), 'utf8')
    .replace(/^\{\{--.*--\}\}\s*/, '')
    .replace(/data-endpoint=".*"/, 'data-endpoint="/fixture"')
    .replace("@push('js')", '')
    .replace('@endpush', '');
const chrome = process.env.CHROME_PATH || (process.platform === 'win32' ? 'C:/Program Files/Google/Chrome/Application/chrome.exe' : 'chromium');
const temp = fs.mkdtempSync(path.join(root, 'storage/framework/cache', 'pms-org-tree-'));

const nodes = [
    [1, null, 90], [2, 1, 6], [3, 1, 0], [4, 2, 0], [5, 2, 0], [6, 2, 1],
    [7, 1, 5], [8, 7, 4], [9, 8, 3], [10, 9, 2],
].map(([id, parent, level]) => ({
    id, parent, level, placement: id === 5 ? 'level' : 'reporting', name: 'Employee ' + id,
    employee_code: 'EMP-' + id, designation: 'Designation ' + id, department: 'Engineering',
    department_id: 1, status: 'Active', email: `employee${id}@example.test`, show_url: 'https://example.test/employees/' + id,
    profile_url: '/fixture/profile/' + id,
    directs: ({1: 3, 2: 3, 7: 1, 8: 1, 9: 1})[id] || 0,
    total: ({1: 9, 2: 3, 7: 3, 8: 2, 9: 1})[id] || 0,
}));
const css = ['pms-refresh.css', 'pms-bitroxia-theme.css'].map(file =>
    `<style>${fs.readFileSync(path.join(root, 'public/admin/assets/css', file), 'utf8')}</style>`).join('');
const directoryCss = ['directory-styles', 'directory-presentation'].map(name =>
    fs.readFileSync(path.join(root, 'resources/views/admin/employees/partials', name + '.blade.php'), 'utf8')).join('');

const html = `<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<div id="employeeDirectoryPage" class="employee-dashboard">
<div class="filter-export-card">List filters</div><div class="table-card">Employee list</div>
<script>
const fixture = { nodes: ${JSON.stringify(nodes)} };
window.fetch = async url => {
    if (url === '/fixture') return { ok: true, json: async () => fixture };
    const id = Number(url.split('/').pop());
    await new Promise(resolve => setTimeout(resolve, id === 4 ? 30 : 5));
    return { ok: true, json: async () => ({ id, sections: { 'Work profile': { 'Business address': 'Office ' + id } } }) };
};
</script>
${view}</div>${directoryCss}${css}
<script>
const get = selector => document.querySelector(selector);
const card = id => get('#eotStage .eot-tile[data-id="' + id + '"]');
const gridCard = id => get('#eotLevelGrid .eot-tile[data-id="' + id + '"]');
const action = name => get('[data-eot-action="' + name + '"]');
const wait = ms => new Promise(resolve => setTimeout(resolve, ms));
function check(condition, message) { if (!condition) throw new Error(message); }
get('[data-eot-view="tree"]').click();
setTimeout(async () => {
    try {
        if (matchMedia('(max-width: 575px)').matches) check(!get('#eotLevelGrid').hidden, 'Level grid opens by default on narrow screens');
        action('mode-chart').click();
        check(card(1) && card(6), 'All cards render');
        check([...document.querySelectorAll('.eot-lane')].map(lane => lane.textContent).join(',') === 'L90,L6,L5,L4,L3,L2,L1,L0', 'Chart orders L6 through L0 and custom levels');
        check(card(1).offsetTop < card(2).offsetTop && card(2).offsetTop < card(3).offsetTop, 'Levels descend toward L0');
        check(card(3).offsetTop === card(4).offsetTop, 'L0 cards align across reporting depths');
        check(card(3).offsetLeft !== card(4).offsetLeft && card(4).offsetLeft !== card(5).offsetLeft, 'Siblings cannot overlap');
        const connector = get('.eot-connector[data-child="5"]');
        check(connector.classList.contains('is-level-placed') && connector.getBoundingClientRect().height > 0, 'Inferred reports have visible dashed connectors');
        check(get('#eotViewport').clientWidth > 200, 'Chart viewport remains readable');

        const open = card(4).querySelector('.eot-card-open');
        const toggle = card(2).querySelector('[data-toggle]');
        check(open.getBoundingClientRect().width > 100, 'Employee detail button covers the card');
        open.focus();
        open.click();
        const inspector = get('#eotInspector');
        check(inspector.open && !inspector.inert, 'Card opens an interactive inspector');
        check(inspector.textContent.includes('EMP-4') && inspector.textContent.includes('employee4@example.test'), 'Inspector shows the selected employee details');
        check(inspector.querySelector('a[href$="/employees/4"]'), 'Inspector opens the full employee profile');
        check(document.activeElement === action('close-inspector'), 'Opening details moves focus to the inspector');
        await wait(40);
        check(inspector.textContent.includes('Business address') && inspector.textContent.includes('Office 4'), 'Inspector loads additional details for the selected employee');
        inspector.dispatchEvent(new Event('cancel', { cancelable: true }));
        check(!inspector.open && inspector.inert && document.activeElement === open, 'Closing restores keyboard focus to the card');

        toggle.click();
        check(!card(4) && !get('tr[data-row="4"]'), 'Collapse hides the branch in chart and table');
        action('mode-grid').click();
        check(!get('#eotLevelGrid').hidden && get('#eotViewport').getBoundingClientRect().width === 0, 'Level grid is visible on its own');
        check(document.querySelectorAll('#eotLevelGrid .eot-tile').length === fixture.nodes.length, 'Every employee has a grid card after a chart branch collapses');
        check(gridCard(4).closest('[data-level]').dataset.level === '0' && gridCard(6).closest('[data-level]').dataset.level === '1', 'Grid uses designation levels');
        check(gridCard(5).textContent.includes('Placed under Employee 2'), 'Grid communicates the reporting placement');
        gridCard(4).querySelector('.eot-card-open').click();
        check(inspector.open && inspector.textContent.includes('EMP-4'), 'Grid card opens details');
        inspector.querySelector('[data-person="2"]').click();
        await wait(40);
        check(inspector.textContent.includes('Office 2') && !inspector.textContent.includes('Office 4'), 'Changing the selected employee replaces additional details');
        action('close-inspector').click();
        action('mode-chart').click();
        const level = get('#eotLevel');
        check([...level.options].some(option => option.value === '90'), 'Level picker supports levels above L6');
        level.value = '0';
        level.dispatchEvent(new Event('change'));
        check(level.value === '' && !inspector.open, 'Level picker navigates without opening the inspector');
        get('#eotSearch').value = 'EMP-4';
        get('#eotSearch').dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter' }));
        check(card(4) && inspector.textContent.includes('EMP-4'), 'Search reveals collapsed records and opens details');
        action('close-inspector').click();
        action('mode-table').click();
        check(!get('#eotTableWrap').hidden && get('tr[data-row="4"]'), 'Table keeps the same reporting structure');
        get('tr[data-row="4"] [data-person="4"]').click();
        check(inspector.open && inspector.textContent.includes('EMP-4'), 'Details also open from the table');
        const heading = inspector.querySelector('.eot-hero h4');
        document.dispatchEvent(new Event('pms:records-updated'));
        await wait(40);
        check(inspector.open && heading.isConnected && inspector.textContent.includes('Office 4'), 'A live roster refresh keeps the selected profile open');
        document.body.dataset.testResult = 'pass';
    } catch (error) { document.body.dataset.testResult = error.message; }
}, 80);
</script>`;

try {
    const fixture = path.join(temp, 'fixture.html');
    fs.writeFileSync(fixture, html);
    for (const [label, size] of [['desktop', '1440,1050'], ['mobile', '390,844']]) {
        const result = execFileSync(chrome, ['--headless', '--disable-gpu', '--no-sandbox', '--no-first-run', '--disable-background-networking',
            '--host-resolver-rules=MAP * ~NOTFOUND',
            '--window-size=' + size, '--user-data-dir=' + path.join(temp, label), '--virtual-time-budget=2000', '--dump-dom', pathToFileURL(fixture).href],
        { encoding: 'utf8', timeout: 25000, maxBuffer: 4_000_000, stdio: ['ignore', 'pipe', 'pipe'] });
        assert.equal(result.match(/<body data-test-result="([^"]+)"/)?.[1], 'pass', label + ' organization tree');
        console.log('PASS: ' + label + ' chart, level grid, connectors, branch controls, search, table, and employee details.');
    }
} finally {
    fs.rmSync(temp, { recursive: true, force: true });
}
