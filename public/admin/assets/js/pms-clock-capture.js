(function () {
    'use strict';
    if (window.PMSClockCapture) return;
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
