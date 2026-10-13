const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const view = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/employees/index.blade.php'), 'utf8');
const script = view.slice(view.indexOf('    // Keep Bootstrap\'s modal'), view.indexOf('    // Share link'));
const nodes = new Map();
function node(id) {
  if (!nodes.has(id)) nodes.set(id, {id, value: '', visible: true, disabled: false, classes: new Set(), handlers: {}, attrs: {}, reportValidity: () => true});
  return nodes.get(id);
}
const requests = [];
function $(selector) {
  const entries = typeof selector === 'string' ? selector.split(',').map(id => node(id.trim().slice(1))) : [selector];
  const first = entries[0];
  const wrapper = {0: first,
    on(event, handler) { entries.forEach(item => item.handlers[event] = handler); return this; },
    attr(key, value) { entries.forEach(item => item.attrs[key] = value); return this; },
    prop(key, value) { if (value === undefined) return first[key]; entries.forEach(item => item[key] = value); return this; },
    val(value) { if (value === undefined) return first.value; first.value = value; return this; },
    text(value) { first.text = value; return this; },
    show() { first.visible = true; return this; }, hide() { first.visible = false; return this; },
    toggle(value) { first.visible = value; return this; }, select() { first.selected = true; return this; },
    addClass(value) { first.classes.add(value); return this; }, removeClass(value) { first.classes.delete(value); return this; },
  };
  return wrapper;
}
$.ajax = options => requests.push(options);
let modalRelocated = false;
let copied;
node('inviteEmailForm').reset = () => node('inviteEmail').value = '';
vm.runInNewContext(script, {
  $, document: { getElementById: node, body: { appendChild(item) { modalRelocated = item.id === 'inviteModal'; } } },
  navigator: {clipboard: {async writeText(value) {copied = value;}}}, window: {isSecureContext: true},
});
function trigger(id, event) { return node(id).handlers[event].call(node(id), {preventDefault() {}}); }
assert.ok(modalRelocated, 'Invite modal must escape the transformed card and backdrop stacking context');
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
requests[2].success({url: 'https://example.test/signed-invite', message: 'Link ready'});
requests[2].complete();
assert.equal(node('linkContainer').visible, true);
assert.equal(node('inviteLink').value, 'https://example.test/signed-invite');
(async () => {
  await trigger('copyLinkBtn', 'click');
  assert.equal(copied, 'https://example.test/signed-invite');
  trigger('invite-email-tab', 'shown.bs.tab');
  assert.equal(node('sendInviteBtn').visible, true);
  console.log('PASS: real invitation UI handlers, validation errors, duplicate-submit prevention, loading recovery, link tab, copy and modal relocation.');
})().catch(error => {console.error(error); process.exitCode = 1;});
