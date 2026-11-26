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
const withTime = context.renderCalendarEventContent({ ...info, timeText: '', event: { ...info.event, extendedProps: { ...info.event.extendedProps, display_time: '09:00 AM' } } }).domNodes[0];
assert.equal(withTime.children[1].textContent, '09:00 AM ' + info.event.title);
assert.match(view, /eventContent:\s*renderCalendarEventContent/);
assert.match(view, /eventClick:\s*function\(info\)\s*\{\s*info\.jsEvent\.preventDefault\(\);\s*showEventDetails\(info\.event\.id\);/);
assert.match(view, /id="eventCalendar" data-pms-export="off"/);
const tableTools = readFileSync(new URL('../../public/admin/assets/js/pms-table-tools.js', import.meta.url), 'utf8');
vm.runInContext(tableTools.match(/  function isEligible\(table\) \{[\s\S]*?(?=  function dataRows\()/)[0], context);
const calendarTable = { tagName: 'TABLE', matches: () => false, closest: () => ({}), tHead: { querySelector: () => ({}) }, tBodies: [{}] };
assert.equal(context.isEligible(calendarTable), false, 'Calendar must not receive table checkboxes/export tools');
const normalTable = { ...calendarTable, closest: () => null };
assert.equal(context.isEligible(normalTable), true, 'Other data tables must retain export tools');
const calendarSource = view.match(/    function initCalendar\(\) \{[\s\S]*?(?=    function submitEventForm\()/)[0];
let options, refetchCount = 0;
context.document.getElementById = () => ({});
context.sessionStorage = { getItem: () => null, setItem: () => {} };
context.eventViewStorageKey = 'events-view:1:1';
context.activeCalendarView = 'dayGridMonth';
context.FullCalendar = { Calendar: class {
    constructor(element, config) { options = config; }
    getDate() { return new Date(2026, 9, 8); }
    refetchEvents() { refetchCount++; }
} };
vm.runInContext(calendarSource, context);
context.initCalendar();
assert.equal(options.events.extraParams().view, 'dayGridMonth');
options.datesSet({ view: { type: 'timeGridWeek' } });
assert.equal(options.events.extraParams().view, 'timeGridWeek');
assert.equal(refetchCount, 1);
options.datesSet({ view: { type: 'dayGridMonth' } });
assert.equal(options.events.extraParams().view, 'dayGridMonth');
assert.equal(refetchCount, 2);
console.log('PASS: Banner rendering, fallbacks, safe titles, view-specific refetching, event-click wiring, and calendar table-tool exclusion.');
