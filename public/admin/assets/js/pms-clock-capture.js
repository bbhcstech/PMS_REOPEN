(function () {
    'use strict';
    if (window.PMSClockCapture) return;
    var submissions = new WeakSet();
    function deadline(promise, milliseconds, message, expired) {
        var timer;
        var timeout = new Promise(function (_, reject) {
            timer = setTimeout(function () {
                if (expired) expired();
                var error = new Error(message); error.name = 'TimeoutError'; reject(error);
            }, milliseconds);
        });
        return Promise.race([promise, timeout]).finally(function () { clearTimeout(timer); });
    }
    window.PMSClockCapture = {
        submit: async function (form) {
            if (!form || submissions.has(form)) return false;
            submissions.add(form);
            var button = form.querySelector('button[type="submit"]');
            var wasDisabled = button && button.disabled;
            var finish = window.PMSLoading && window.PMSLoading.begin(button);
            if (button) button.disabled = true;
            var controller = new AbortController(), navigating = false;
            var previous = form.querySelector('[data-clock-submit-error]');
            if (previous) previous.remove();
            try {
                // Keep submissions on HTTPS when a reverse proxy generated an HTTP action URL.
                var target = new URL(form.action, window.location.href);
                if (target.host === window.location.host) target.protocol = window.location.protocol;
                if (target.origin !== window.location.origin) throw new Error('Attendance must be submitted to this website. Please refresh and try again.');
                var result = await deadline(fetch(target.href, {
                    method: 'POST', body: new FormData(form), credentials: 'same-origin', signal: controller.signal,
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(async function (response) {
                    var data = response.headers.get('content-type')?.includes('application/json') ? await response.json() : null;
                    if (response.status === 401 || response.status === 419) throw new Error('Your session expired. Refresh this page and sign in again.');
                    if (response.status === 413) throw new Error('The photo is too large for the server. Please retake it and try again.');
                    if (!response.ok || !data || data.success !== true) {
                        var validation = data && data.errors && Object.values(data.errors).flat().join(' ');
                        var reason = data && (typeof data.message === 'string' ? data.message : typeof data.error === 'string' ? data.error : null);
                        if (response.redirected && /\/login(?:\/|$)/.test(new URL(response.url, window.location.href).pathname)) throw new Error('Your session expired. Refresh this page and sign in again.');
                        throw new Error(validation || (response.status < 500 && reason)
                            || (response.status === 403 ? 'The server blocked this attendance request (HTTP 403). Please contact your company admin.' : null)
                            || 'Attendance could not be saved. Please refresh to check your attendance before trying again.');
                    }
                    return data;
                }), 45000, 'The server did not confirm your attendance. Refresh to check whether it was saved before trying again.', function () { controller.abort(); });
                if (typeof result.redirect_url !== 'string' || !result.redirect_url) throw new Error('Attendance saved. Refresh this page to see your current shift.');
                var destination = new URL(result.redirect_url, window.location.href);
                if (destination.origin !== window.location.origin) throw new Error('Attendance saved. Refresh this page to see your current shift.');
                window.location.assign(destination.href);
                navigating = true;
                return true;
            } catch (error) {
                var notice = document.createElement('div');
                notice.className = 'alert alert-danger mt-2'; notice.setAttribute('role', 'alert');
                notice.setAttribute('data-clock-submit-error', '');
                notice.textContent = error.message || 'Attendance could not be saved. Please try again.';
                form.appendChild(notice);
                return false;
            } finally {
                if (!navigating) {
                    submissions.delete(form);
                    if (finish) finish();
                    if (button) button.disabled = wasDisabled;
                }
            }
        },
        location: function () {
            if (!navigator.geolocation) return Promise.reject(new Error('Location is not supported in this browser.'));
            return deadline(new Promise(function (resolve, reject) {
                navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 });
            }), 8500, 'Location did not respond. Please check your browser location permission.');
        },
        json: function (url, options) {
            var controller = new AbortController();
            return deadline(fetch(url, Object.assign({}, options, { signal: controller.signal })).then(function (response) {
                if (!response.ok) throw new Error('Location name lookup failed.');
                return response.json();
            }), 4000, 'Location name lookup timed out.', function () { controller.abort(); });
        },
        camera: function (constraints, isActive) {
            if (window.isSecureContext === false) return Promise.reject(new Error('Camera requires HTTPS. Open the secure website URL to clock in.'));
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return Promise.reject(new Error('Camera is unavailable. Use a browser with camera access on HTTPS.'));
            var expired = false;
            var request = navigator.mediaDevices.getUserMedia(constraints).then(function (stream) {
                if (expired || (isActive && !isActive())) {
                    stream.getTracks().forEach(function (track) { track.stop(); });
                    throw new Error('Camera request canceled. Please try again.');
                }
                return stream;
            });
            return deadline(request, 15000, 'Camera did not respond. Allow camera permission and try again.', function () { expired = true; });
        },
        cameraError: function (error) {
            if (error && error.name === 'NotAllowedError') return 'Camera permission denied. Allow camera access in your browser site settings and try again.';
            if (error && error.name === 'NotFoundError') return 'No camera was found. Connect or enable your camera and try again.';
            if (error && error.name === 'NotReadableError') return 'Your camera is busy. Close other apps using it and try again.';
            return error && error.message || 'Camera access and a captured photo are required to clock in.';
        },
        play: function (video) { return deadline(video.play(), 4000, 'Camera preview did not start.'); }
    };
})();
