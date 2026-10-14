const fs = require('node:fs'), vm = require('node:vm'), assert = require('node:assert/strict');
const drain = async () => { for (let i = 0; i < 10; i++) await Promise.resolve(); };
async function helperChecks() {
    const timers = new Map(); let timerId = 0, fetchOptions, mediaResolve, stopped = 0;
    const navigator = { geolocation: { getCurrentPosition() {} }, mediaDevices: { getUserMedia: () => new Promise(resolve => { mediaResolve = resolve; }) } };
    const window = { isSecureContext: true };
    vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-clock-capture.js', 'utf8'), {
        window, navigator, AbortController,
        fetch: (_, options) => { fetchOptions = options; return new Promise(() => {}); },
        setTimeout: (fn, ms) => { timers.set(++timerId, { fn, ms }); return timerId; }, clearTimeout: id => timers.delete(id),
    });
    const api = window.PMSClockCapture;
    const expire = ms => { const timer = [...timers.values()].find(item => item.ms === ms); assert.ok(timer); timer.fn(); };
    const location = assert.rejects(api.location(), /Location did not respond/); expire(8500); await location;
    const json = assert.rejects(api.json('/lookup'), /lookup timed out/); expire(4000); await json;
    assert.equal(fetchOptions.signal.aborted, true);
    const camera = assert.rejects(api.camera({ video: true }), /Camera did not respond/); expire(15000); await camera;
    mediaResolve({ getTracks: () => [{ stop: () => stopped++ }] }); await drain();
    assert.equal(stopped, 1, 'Late media stream was left running after timeout');
    window.isSecureContext = false; await assert.rejects(api.camera({}), /HTTPS/);
    assert.match(api.cameraError({ name: 'NotAllowedError' }), /permission denied/);
}
function element() {
    const classes = new Set();
    return { value: '', style: {}, disabled: false, listeners: {}, textContent: '',
        classList: { add: name => classes.add(name), remove: name => classes.delete(name), contains: name => classes.has(name) },
        addEventListener(name, fn) { this.listeners[name] = fn; },
        click() { if (!this.disabled) this.listeners.click?.(); },
        setAttribute() {}, removeAttribute() {}, append() {}, replaceChildren(...children) { this.children = children; },
        play: async () => {}, getContext: () => ({ clearRect() {}, translate() {}, scale() {}, drawImage() {} }),
        toDataURL: () => 'data:image/jpeg;base64,photo', videoWidth: 640, videoHeight: 480,
    };
}
async function flowChecks(file, denyCamera = false, captureEarly = false) {
    let source = fs.readFileSync(file, 'utf8');
    if (file.endsWith('.blade.php')) {
        source = source.slice(source.indexOf('const officeLocation ='), source.indexOf('const istClock ='));
        source = source.replace(/@json\(\$officeLatitude\)/g, '22.49682').replace(/@json\(\$officeLongitude\)/g, '88.39462')
            .replace(/@json\(\$officeRadiusMeters\)/g, '10').replace(/@json\(\$officeAddress\)/g, '"Office"');
    }
    const elements = new Map(); let domReady, locationResolve, mediaRequests = 0, submitted = 0;
    const get = id => { if (!elements.has(id)) elements.set(id, element()); return elements.get(id); };
    get('employeeClockInForm').submit = () => submitted++;
    const window = { PMSTime: { timeZone: 'Asia/Kolkata' }, PMSClockCapture: {
        location: () => new Promise(resolve => { locationResolve = resolve; }),
        camera: async () => { mediaRequests++; if (denyCamera) throw new Error('Camera permission denied'); return { getTracks: () => [{ stop() {} }] }; },
        json: async () => { throw new Error('Location lookup timed out'); },
        play: async () => {}, cameraError: error => error.message,
        submit: async form => { form.submit(); return true; },
    } };
    vm.runInNewContext(source, { window, URL, console: { warn() {}, error() {} }, alert() {},
        document: { getElementById: get, createElement: element, addEventListener: (_, callback) => { domReady = callback; } } });
    if (domReady) domReady();
    const preparation = get('employeeClockInForm').listeners.submit({ preventDefault() {} });
    assert.equal(mediaRequests, 1, file + ': camera waited for geolocation');
    assert.equal(get('clockCameraModal').classList.contains('is-open'), true, file + ': camera modal did not open immediately');
    assert.equal(get('clockCameraUse').disabled, true, 'Confirmation enabled before location preparation');
    if (captureEarly) {
        get('clockCameraCapture').listeners.click();
        assert.equal(submitted, 0, 'Early photo submitted before location preparation finished');
    }
    locationResolve({ coords: { latitude: 22.5, longitude: 88.4, accuracy: 3 } });
    await preparation;
    if (!captureEarly) assert.equal(get('employeeClockInButton').disabled, false, 'Clock-in button stuck disabled');
    if (denyCamera) {
        assert.equal(get('clockCameraModal').classList.contains('is-open'), false, 'Denied camera left an empty modal open');
        assert.equal(get('clockCameraUse').disabled, true, 'Camera denial allowed photo confirmation');
        assert.equal(submitted, 0, 'Camera denial submitted attendance');
        return;
    }
    assert.equal(get('clockCameraUse').disabled, false, 'Lookup timeout blocked confirmation');
    assert.match(get('clockInAddress').value, /Current location:/, 'Lookup failure lost recorded coordinates');
    assert.equal(submitted, captureEarly ? 1 : 0, 'Attendance submitted at the wrong preparation stage');
    if (!captureEarly) get('clockCameraCapture').listeners.click();
    if (file.endsWith('employee-dashboard.blade.php')) assert.equal(submitted, 1, 'Employee photo capture did not automatically submit clock-in');
    else get('clockCameraUse').listeners.click();
    assert.equal(submitted, 1, 'Captured photo did not submit clock-in');
    assert.equal(get('clockInSelfie').value, 'data:image/jpeg;base64,photo');
}
(async () => {
    await helperChecks();
    for (const file of ['public/admin/assets/js/pms-staff-clock.js', 'resources/views/employee-dashboard.blade.php', 'resources/views/dashboard-hr.blade.php']) {
        await flowChecks(file); await flowChecks(file, true);
    }
    await flowChecks('resources/views/employee-dashboard.blade.php', false, true);
    console.log('PASS: immediate camera on employee/HR/custom-role clocks, stalled location/address/camera deadlines, late stream cleanup, HTTPS/permission errors, coordinate fallback, restored buttons and photo-confirmed submission.');
})().catch(error => { console.error(error); process.exitCode = 1; });
