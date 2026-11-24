// Run with: node tests/regression/event_calendar_banner.mjs
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';

const view = readFileSync(new URL('../../resources/views/admin/events/index.blade.php', import.meta.url), 'utf8');
const source = view.match(/    function renderCalendarEventContent\(info\) \{[\s\S]*?(?=    function initCalendar\()/)?.[0];
assert.ok(source, 'Calendar banner renderer is missing');
const document = {
    createElement(tag) {
        return {
            tag, children: [], handlers: {},
            appendChild(child) { this.children.push(child); child.parent = this; },
            addEventListener(type, callback) { this.handlers[type] = callback; },
            remove() { this.parent.children = this.parent.children.filter(child => child !== this); },
        };
    },
};
const context = vm.createContext({ document });
vm.runInContext(source, context);
const info = {
    view: { type: 'dayGridMonth' }, timeText: '9a',
    event: { title: '<img src=x onerror=alert(1)>', extendedProps: { banner_url: '/uploads/events/banners/event.png' } },
};
const content = context.renderCalendarEventContent(info).domNodes[0];
assert.equal(content.children[0].tag, 'img');
assert.equal(content.children[0].src, info.event.extendedProps.banner_url);
assert.equal(content.children[0].alt, info.event.title);
assert.equal(content.children[1].textContent, '9a ' + info.event.title);
assert.equal(content.children[1].children.length, 0, 'Title must remain plain text');
content.children[0].handlers.error();
assert.equal(content.children.length, 1, 'Broken image should be removed');
assert.equal(content.children[0].textContent, '9a ' + info.event.title);
assert.equal(context.renderCalendarEventContent({ ...info, event: { ...info.event, extendedProps: {} } }), true);
for (const type of ['timeGridWeek', 'listMonth']) {
    assert.equal(context.renderCalendarEventContent({ ...info, view: { type } }), true);
}
const allDay = context.renderCalendarEventContent({ ...info, timeText: '' }).domNodes[0];
assert.equal(allDay.children[1].textContent, info.event.title);
assert.match(view, /eventContent:\s*renderCalendarEventContent/);
assert.match(view, /eventClick:\s*function\(info\)\s*\{\s*info\.jsEvent\.preventDefault\(\);\s*showEventDetails\(info\.event\.id\);/);
console.log('PASS: Banner rendering, safe titles, image failure fallback, all-day labels, default views, and event-click wiring.');
