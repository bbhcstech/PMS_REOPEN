# Clock-in camera preparation

Clock-in previously waited for geolocation and then an unbounded external reverse-address request before it opened the camera. A stalled address request could therefore leave the button spinning indefinitely without a camera appearing.

Employee, HR, and custom-role clock widgets now start the camera immediately, in parallel with location preparation. Photo confirmation remains disabled until preparation succeeds. The optional address lookup has a four-second deadline and falls back to recorded coordinates. Geolocation, camera permission, and video preview promises have deadlines as well; ignored permission prompts cannot leave the operation pending forever. Existing differences in employee/HR location requirements are preserved.

Denied/unavailable cameras show an error, close the empty camera modal, and restore the clock-in button. Late camera streams after cancellation or timeout are stopped. A real captured photo is still required before submission. Server attendance policy, company isolation, and one-session-per-day enforcement remain unchanged.

The shared helper is `public/admin/assets/js/pms-clock-capture.js`, included by the PWA/layout partial. No environment or schema changes are required for this fix. Deploy the modified assets/views and refresh compiled views with `php artisan view:cache`. Camera access requires a secure HTTPS page on the live website; the browser cannot grant it on an insecure HTTP origin. This requirement is now reported explicitly to the user.

Verification: `node tests/regression/clock_capture.cjs` exercises the actual three clock scripts with delayed location and failed address lookup, camera denial, helper deadlines, late-stream cleanup and photo-confirmed submission. `php tests/regression/employee_daily_clock.php` checks saved clock times, durations, photos, repeated-request protection and the next business day. These tests use local fixtures, not the production server.

## Photo captured but Clock In remains loading

The photo confirmation handlers previously called native `form.submit()` without checking for a saved attendance response. That path provided no bounded request lifecycle or inline feedback for rejected, blocked, or stalled submissions. All three clock widgets now use the shared `PMSClockCapture.submit` handler instead. It posts the captured photo, location, timezone and CSRF token with same-origin credentials and expects an explicit JSON save confirmation. It keeps the loading state until navigation takes over, prevents duplicate requests, and reloads the dashboard only after a confirmed save. The dashboard then renders Clock Out and its work timer from the saved attendance start time. Clock-out follows the same process and displays the stored final duration.

Validation, expired sessions, oversized uploads, server errors and a 45-second request deadline restore the controls and show an inline error. A timeout does not prove the database rolled back: users are told to refresh and check their attendance before trying again. A same-host HTTP form action generated behind an HTTPS proxy is submitted over the current page's HTTPS protocol. Existing HTML form response behavior and server attendance policies remain supported.

Additional verification: `php tests/regression/clock_submission.php` confirms JSON clock-in/out responses, persisted photo/start time, exact work duration, and duplicate rejection. `node tests/regression/employee_clock_refresh.js` checks that an active timer advances from its saved start and a completed duration stays fixed. `node tests/regression/operation_loading_browser.cjs` passes 120 checks at 390px/820px/1440px, including the actual submission helper integrated with the shared loader, photo/CSRF payload, successful navigation, duplicate prevention, validation/session/server failures and request timeout. Browser tests use a local HTTP fixture, not production attendance records.

Deploy the changed controller, shared assets and employee/HR views together, run `php artisan view:cache`, and reload the dashboard. No `.env` changes or database migrations are needed. Production behavior must still be verified after deployment.
