document.addEventListener('DOMContentLoaded', function () {
        const officeLocation = {
            lat: 22.49682,
            lng: 88.39462,
            radius: 10,
            address: '11 Hospital Link Road, Satavisha Building, Kolkata, West Bengal 700075'
        };

        const clockInForm = document.getElementById('employeeClockInForm');
        const clockInButton = document.getElementById('employeeClockInButton');
        const requirementStatus = document.getElementById('clockRequirementStatus');
        const modal = document.getElementById('clockCameraModal');
        const video = document.getElementById('clockCameraVideo');
        const canvas = document.getElementById('clockCameraCanvas');
        const photo = document.getElementById('clockCameraPhoto');
        const preview = document.getElementById('clockCameraPreview');
        const closeCamera = document.getElementById('clockCameraClose');
        const flipCamera = document.getElementById('clockCameraFlip');
        const captureCamera = document.getElementById('clockCameraCapture');
        const retakeCamera = document.getElementById('clockCameraRetake');
        const useCamera = document.getElementById('clockCameraUse');
        const latitudeInput = document.getElementById('clockInLatitude');
        const longitudeInput = document.getElementById('clockInLongitude');
        const accuracyInput = document.getElementById('clockInAccuracy');
        const addressInput = document.getElementById('clockInAddress');
        const selfieInput = document.getElementById('clockInSelfie');
        const timezoneInput = document.getElementById('clockInTimezone');
        let cameraStream = null;
        let cameraFacingMode = 'user';
        let capturedSelfie = '';
        let canSubmitClockIn = false;

        const setClockStatus = (message, type = 'info') => {
            if (!requirementStatus) {
                return;
            }

            const icon = type === 'success' ? 'bx-check-circle' : (type === 'error' ? 'bx-error-circle' : 'bx-info-circle');
            const className = type === 'error' ? 'clock-status-line is-error' : 'clock-status-line';
            const line = document.createElement('div');
            line.className = className;
            const statusIcon = document.createElement('i');
            statusIcon.className = `bx ${icon}`;
            const text = document.createElement('span');
            text.textContent = message;
            line.append(statusIcon, text);
            requirementStatus.replaceChildren(line);
        };

        const distanceInMeters = (lat1, lng1, lat2, lng2) => {
            const earthRadius = 6371000;
            const toRad = value => value * Math.PI / 180;
            const dLat = toRad(lat2 - lat1);
            const dLng = toRad(lng2 - lng1);
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
                + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2))
                * Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return earthRadius * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
        };

        const stopCamera = () => {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
        };

        const startCamera = async () => {
            stopCamera();
            capturedSelfie = '';
            preview?.classList.remove('has-photo');
            if (photo) {
                photo.removeAttribute('src');
                photo.style.display = 'none';
            }
            if (video) {
                video.style.display = 'block';
                video.style.transform = cameraFacingMode === 'user' ? 'scaleX(-1)' : 'scaleX(1)';
            }
            if (canvas) {
                canvas.style.display = 'none';
            }

            const constraintsList = [
                { video: { facingMode: { exact: cameraFacingMode }, width: { ideal: 1280 }, height: { ideal: 960 } }, audio: false },
                { video: { facingMode: cameraFacingMode, width: { ideal: 1280 }, height: { ideal: 960 } }, audio: false },
                { video: { facingMode: cameraFacingMode }, audio: false },
                { video: true, audio: false }
            ];

            let stream = null;
            for (const constraints of constraintsList) {
                try {
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                    if (stream) break;
                } catch (e) {
                    // Try next fallback constraint
                }
            }

            if (!stream) {
                throw new Error('Unable to access camera');
            }

            cameraStream = stream;
            if (video) {
                video.srcObject = cameraStream;
                try {
                    await video.play();
                } catch (e) {}
            }
        };

        const openCamera = async () => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            await startCamera();
        };

        const closeCameraModal = () => {
            stopCamera();
            modal?.classList.remove('is-open');
            modal?.setAttribute('aria-hidden', 'true');
            preview?.classList.remove('has-photo');
            if (photo) {
                photo.removeAttribute('src');
                photo.style.display = 'none';
            }
            if (video) {
                video.style.display = 'block';
            }
            if (canvas) {
                canvas.style.display = 'none';
            }
        };

        const requestLocation = () => new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Location is not supported in this browser.'));
                return;
            }

            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            });
        });

        const compactAddress = address => {
            if (!address || typeof address !== 'object') {
                return '';
            }

            const parts = [
                address.road,
                address.neighbourhood || address.suburb || address.quarter,
                address.city || address.town || address.village || address.municipality,
                address.county || address.state_district,
                address.state,
                address.postcode
            ];

            return [...new Set(parts.filter(Boolean))]
                .join(', ')
                .slice(0, 180);
        };

        const reverseGeocodeLocation = async (lat, lng) => {
            const url = new URL('https://nominatim.openstreetmap.org/reverse');
            url.searchParams.set('format', 'jsonv2');
            url.searchParams.set('lat', lat);
            url.searchParams.set('lon', lng);
            url.searchParams.set('zoom', '18');
            url.searchParams.set('addressdetails', '1');

            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Location name lookup failed.');
            }

            const data = await response.json();
            return compactAddress(data.address) || (data.display_name || '').slice(0, 180);
        };



        if (clockInForm) {
            clockInForm.addEventListener('submit', async event => {
                if (canSubmitClockIn) {
                    return;
                }

                event.preventDefault();

                try {
                    clockInButton.disabled = true;
                    setClockStatus('Detecting location...', 'info');

                    let currentLat = officeLocation.lat || 0;
                    let currentLng = officeLocation.lng || 0;
                    let positionAccuracy = 0;
                    let locationLabel = officeLocation.address || 'Default location';

                    // 1. Try to get geolocation, with graceful fallback on permission error or timeout
                    try {
                        const position = await requestLocation();
                        currentLat = position.coords.latitude;
                        currentLng = position.coords.longitude;
                        positionAccuracy = Math.round(position.coords.accuracy || 0);
                        const distance = distanceInMeters(officeLocation.lat, officeLocation.lng, currentLat, currentLng);
                        const coordinateLabel = `${currentLat.toFixed(8)}, ${currentLng.toFixed(8)} (${distance.toFixed(1)}m from office)`;

                        try {
                            const placeName = await reverseGeocodeLocation(currentLat, currentLng);
                            locationLabel = placeName ? `${placeName} | ${coordinateLabel}` : `Current location: ${coordinateLabel}`;
                        } catch (lookupError) {
                            locationLabel = `Current location: ${coordinateLabel}`;
                        }
                    } catch (locErr) {
                        console.warn('Geolocation permission error or unavailable:', locErr);
                        setClockStatus('Location permission unavailable. Using default location.', 'info');
                    }

                    latitudeInput.value = (parseFloat(currentLat) || 0).toFixed(8);
                    longitudeInput.value = (parseFloat(currentLng) || 0).toFixed(8);
                    accuracyInput.value = positionAccuracy;
                    addressInput.value = locationLabel;

                    // 2. Open the camera; permission errors must not clock in
                    try {
                        setClockStatus('Opening camera for selfie photo...', 'info');
                        await openCamera();
                    } catch (camErr) {
                        console.warn('Camera permission error or unavailable:', camErr);
                        setClockStatus('Camera access is required. Allow camera permission and capture your photo to clock in.', 'error');
                        selfieInput.value = '';
                        canSubmitClockIn = false;
                    }
                } catch (error) {
                    console.error('Clock in error:', error);
                    selfieInput.value = '';
                    canSubmitClockIn = false;
                    setClockStatus('Camera access and a captured photo are required to clock in.', 'error');
                } finally {
                    clockInButton.disabled = false;
                }
            });
        }

        closeCamera?.addEventListener('click', closeCameraModal);

        flipCamera?.addEventListener('click', async () => {
            cameraFacingMode = cameraFacingMode === 'user' ? 'environment' : 'user';
            try {
                await startCamera();
            } catch (error) {
                cameraFacingMode = cameraFacingMode === 'user' ? 'environment' : 'user';
                setClockStatus('Camera flip failed. Switched to primary camera.', 'error');
            }
        });

        captureCamera?.addEventListener('click', () => {
            if (!video || !canvas || !photo) {
                return;
            }

            const width = video.videoWidth || 960;
            const height = video.videoHeight || 720;
            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, width, height);

            if (cameraFacingMode === 'user') {
                ctx.translate(width, 0);
                ctx.scale(-1, 1);
            }

            ctx.drawImage(video, 0, 0, width, height);
            capturedSelfie = canvas.toDataURL('image/jpeg', 0.92);
            photo.src = capturedSelfie;
            preview?.classList.add('has-photo');
            if (video) {
                video.style.display = 'none';
            }
            if (photo) {
                photo.style.display = 'block';
            }
            if (canvas) {
                canvas.style.display = 'none';
            }
        });

        retakeCamera?.addEventListener('click', () => {
            capturedSelfie = '';
            if (photo) {
                photo.removeAttribute('src');
                photo.style.display = 'none';
            }
            if (video) {
                video.style.display = 'block';
            }
            if (canvas) {
                canvas.style.display = 'none';
            }
            preview?.classList.remove('has-photo');
        });



        const clockOutForm = document.getElementById('employeeClockOutForm');
        let clockAction = 'in';
        let openingClockOutCamera = false;
        clockOutForm?.addEventListener('submit', async event => {
            event.preventDefault();
            if (openingClockOutCamera) return;
            openingClockOutCamera = true;
            clockAction = 'out';
            const button = clockOutForm.querySelector('button[type="submit"]');
            if (button) button.disabled = true;
            try {
                document.getElementById('clockOutSelfie').value = '';
                setClockStatus('Capture your photo to complete clock out.', 'info');
                await openCamera();
            } catch (error) {
                closeCameraModal();
                setClockStatus('Allow camera access and capture a photo to clock out.', 'error');
                alert('Allow camera access and capture a photo to clock out.');
            } finally {
                openingClockOutCamera = false;
                if (button) button.disabled = false;
            }
        });

        useCamera?.addEventListener('click', () => {
            if (clockAction === 'out') {
                if (!capturedSelfie) {
                    alert('Capture your photo before clocking out.');
                    return;
                }
                document.getElementById('clockOutSelfie').value = capturedSelfie;
                document.getElementById('clockOutTimezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Asia/Kolkata';
                closeCameraModal();
                clockOutForm.querySelector('button[type="submit"]').disabled = true;
                clockOutForm.submit();
                return;
            }

            if (!capturedSelfie) {
                setClockStatus('Capture your attendance photo before continuing.', 'error');
                return;
            }
            selfieInput.value = capturedSelfie;

            if (timezoneInput) {
                timezoneInput.value = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Asia/Kolkata';
            }
            canSubmitClockIn = true;
            setClockStatus('Photo ready. Completing clock in...', 'success');
            closeCameraModal();
            clockInForm.submit();
        });




});
