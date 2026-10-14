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

## Legacy company database save failure and automatic employee capture

A further regression reproduced an actual save failure against a partially migrated company database: `attendances` lacked `clock_in_latitude` (and potentially longitude, address, photo and work type). Prior clock tests had manually created those fields, hiding the older-schema failure. In addition, clock-in did not supply `working_from`, which remains required without a default on some older company databases.

The new tenant compatibility migration `2026_10_14_000001_repair_attendance_clock_columns.php` adds only missing clock-in columns and preserves existing records. `WorkforceRecordSchema` also checks these fields before clock operations, outside attendance transactions. The clock-in payload supplies the legacy work location field. The migration tolerates another request adding the same column concurrently, but propagates genuine database permission failures. It never falls back to another company database.

For employees, Capture now automatically submits clock-in once camera/location preparation has finished; no additional photo-confirmation click is required. A photo captured early waits for preparation. The open shift is persisted at clock-in so refreshes preserve its timer and Clock Out state; clock-out updates that same record with the finish time and final duration. An unconfirmed save never displays a fictitious running shift.

Deploy this follow-up's controller, schema service, migration and employee view together. Unlike the earlier submission-only change, **this follow-up includes a migration**. After a database backup, run the tenant migrations for the affected registered company with your deployment database user:

```sh
php artisan tenant:migrate --company=YOUR_COMPANY_CODE
php artisan view:cache
php artisan server:check --company=YOUR_COMPANY_CODE
```

Reload the employee dashboard after deployment. No `.env` value changes are needed. The deployment database user needs permission to add the missing columns; file uploads also require the web process to be able to write `public/admin/uploads/attendance-selfies`. A historical log plus local reproduction cannot confirm that every current live-server error has the same cause.

Verification: `php tests/regression/clock_in_legacy_schema.php` reproduces the old schema, including NOT NULL `working_from`, and checks open-shift saving, photo storage, completed-shift duration, repeat repair preserving records, no schema changes inside attendance transactions, and untouched central data. `php tests/regression/clock_submission.php` checks both JSON and legacy HTML flows and duplicate protection. `node tests/regression/clock_capture.cjs` checks automatic employee capture including a photo taken before location preparation finishes. The browser fixture additionally exercises the actual employee camera handler using a synthetic camera, without a second confirmation click. No real webcam or production attendance records are used.

The separate `authority_attendance_and_leave.php` suite could not reach its attendance checks because its existing `upper_level_employees.php` prerequisite calls the removed `EmployeeController::acceptInviteSubmit` method. Employee/HR/manager clock-out coverage in the isolated attendance-photo suite still passes; the unrelated invitation fixture was not changed for this fix.
