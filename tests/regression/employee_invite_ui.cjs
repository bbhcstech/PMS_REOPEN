const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const view = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/employees/index.blade.php'), 'utf8');
const footer = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/layout/footer.blade.php'), 'utf8');
assert.ok(footer.includes("@stack('modals')"), 'Footer must render @stack(\'modals\') outside #employeeDirectoryPage stacking context');
assert.ok(view.includes("@push('modals')") && view.includes('id="inviteModal"'), 'Invite modal must be pushed to @stack(\'modals\')');
assert.ok(view.includes('id="addNewEmployeeBtn"'), 'Add New Employee button must exist in hero actions');

const script = view.slice(view.indexOf('    function inviteNotice('), view.indexOf('    // Share link'));
const nodes = new Map();
function node(id) {
  if (!nodes.has(id)) nodes.set(id, {id, value: '', visible: true, disabled: false, classes: new Set(), handlers: {}, attrs: {}, reportValidity: () => true});
  return nodes.get(id);
}
const requests = [];
function $(selector) {
  const entries = typeof selector === 'string' ? selector.split(',').map(id => node(id.trim().slice(1))) : [selector];
  const first = entries[0];
  return {
    0: first,
    on(event, handler) { entries.forEach(item => item.handlers[event] = handler); return this; },
    attr(key, value) { entries.forEach(item => item.attrs[key] = value); return this; },
    prop(key, value) { if (value === undefined) return first[key]; entries.forEach(item => item[key] = value); return this; },
    val(value) { if (value === undefined) return first.value; first.value = value; return this; },
    text(value) { first.text = value; return this; },
    show() { first.visible = true; return this; }, hide() { first.visible = false; return this; },
    toggle(value) { first.visible = value; return this; }, select() { first.selected = true; return this; },
    addClass(value) { first.classes.add(value); return this; }, removeClass(value) { first.classes.delete(value); return this; },
  };
}
$.ajax = options => requests.push(options);
let copied;
node('inviteEmailForm').reset = () => node('inviteEmail').value = '';
vm.runInNewContext(script, {
  $, document: { getElementById: node },
  navigator: {clipboard: {async writeText(value) {copied = value;}}}, window: {isSecureContext: true},
});
function trigger(id, event) { return node(id).handlers[event].call(node(id), {preventDefault() {}}); }
node('inviteEmail').value = 'new@example.test';
node('inviteMessage').value = 'Welcome';
trigger('inviteEmailForm', 'submit');
assert.equal(node('sendInviteBtn').disabled, true);
trigger('inviteEmailForm', 'submit');
assert.equal(requests.length, 1, 'Repeated submit must not duplicate invitations');
assert.equal(requests[0].data.email, 'new@example.test');
requests[0].error({responseJSON: {errors: {email: ['Configure SMTP first']}}});
requests[0].complete();
assert.equal(node('inviteEmailAlert').text, 'Configure SMTP first');
assert.equal(node('sendInviteBtn').disabled, false);
assert.equal(node('inviteEmail').value, 'new@example.test', 'Error must preserve recipient for retry');
trigger('inviteEmailForm', 'submit');
requests[1].success({message: 'Invitation sent to new@example.test'});
requests[1].complete();
assert.equal(node('inviteEmailAlert').text, 'Invitation sent to new@example.test');
assert.equal(node('inviteEmail').value, '');
trigger('invite-link-tab', 'shown.bs.tab');
assert.equal(node('sendInviteBtn').visible, false, 'Email submit must not remain on link tab');
node('inviteLinkEmail').value = 'link@example.test';
trigger('createLinkBtn', 'click');
assert.equal(node('createLinkBtn').disabled, true);
requests[2].success({url: 'https://example.test/onboarding/tok1', invitation_id: 42, status: 'Pending', expires_at: '29 Nov 2026', message: 'Link ready'});
requests[2].complete();
assert.equal(node('linkContainer').visible, true);
assert.equal(node('inviteLink').value, 'https://example.test/onboarding/tok1');
assert.equal(node('inviteLinkStatus').text, 'Pending');
trigger('regenerateLinkBtn', 'click');
requests[3].success({url: 'https://example.test/onboarding/tok2', invitation_id: 42, status: 'Pending', expires_at: '29 Nov 2026', message: 'Regenerated'});
requests[3].complete();
assert.equal(node('inviteLink').value, 'https://example.test/onboarding/tok2');
trigger('revokeLinkBtn', 'click');
requests[4].success({status: 'Revoked', message: 'Revoked'});
requests[4].complete();
assert.equal(node('inviteLinkStatus').text, 'Revoked');
(async () => {
  node('inviteLink').value = 'https://example.test/onboarding/tok2';
  await trigger('copyLinkBtn', 'click');
  assert.equal(copied, 'https://example.test/onboarding/tok2');
  trigger('invite-email-tab', 'shown.bs.tab');
  assert.equal(node('sendInviteBtn').visible, true);
  console.log('PASS: real invitation UI handlers, modal portal, regenerate/revoke, duplicate-submit guard, copy and tab toggles.');
})().catch(error => {console.error(error); process.exitCode = 1;});
