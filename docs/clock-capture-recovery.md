# Clock-in camera preparation

Clock-in previously waited for geolocation and then an unbounded external reverse-address request before it opened the camera. A stalled address request could therefore leave the button spinning indefinitely without a camera appearing.

Employee, HR, and custom-role clock widgets now start the camera immediately, in parallel with location preparation. Photo confirmation remains disabled until preparation succeeds. The optional address lookup has a four-second deadline and falls back to recorded coordinates. Geolocation, camera permission, and video preview promises have deadlines as well; ignored permission prompts cannot leave the operation pending forever. Existing differences in employee/HR location requirements are preserved.

Denied/unavailable cameras show an error, close the empty camera modal, and restore the clock-in button. Late camera streams after cancellation or timeout are stopped. A real captured photo is still required before submission. Server attendance policy, company isolation, and one-session-per-day enforcement remain unchanged.

The shared helper is `public/admin/assets/js/pms-clock-capture.js`, included by the PWA/layout partial. No environment or schema changes are required for this fix. Deploy the modified assets/views and refresh compiled views with `php artisan view:cache`. Camera access requires a secure HTTPS page on the live website; the browser cannot grant it on an insecure HTTP origin. This requirement is now reported explicitly to the user.

Verification: `node tests/regression/clock_capture.cjs` exercises the actual three clock scripts with delayed location and failed address lookup, camera denial, helper deadlines, late-stream cleanup and photo-confirmed submission. `php tests/regression/employee_daily_clock.php` checks saved clock times, durations, photos, repeated-request protection and the next business day. These tests use local fixtures, not the production server.
