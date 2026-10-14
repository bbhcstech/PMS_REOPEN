# Production log repair, 14 October 2026

The supplied log covers 4 June through 14 October 2026. It contains 1,500 error/warning entries, including repeated failures from older deployments. The raw log contains personal data and has intentionally not been copied into this repository. Log contents were treated as diagnostic data, not instructions.

## Corrections in this change

| Failure | Cause and correction |
| --- | --- |
| Login `Auth` / `DB` class missing, undefined company variable | The form request retained obsolete duplicate authentication/provisioning paths. It now calls the existing `SecureCompanyLogin` service once, imports Auth, and preserves rate limiting and account-state checks. No implicit demo account provisioning or arbitrary company database fallback remains in this request. |
| Attendance `There is no active transaction` | First-use clock policy initialization could execute ALTER TABLE after acquiring attendance locks. MySQL implicitly commits DDL. Initialize the policy schema before opening attendance transactions. |
| Super Admin task assignment with NULL `project_id` | A platform project may not exist in the destination workspace. Resolve/create a valid workspace project before inserting the mirrored task. Project matching and fallback are company scoped. |
| Employee creation collisions/lock waits | SMTP delivery held employee creation locks until mail completed. Commit records before sending mail; retry only genuine employee-ID duplicate key errors rather than misclassifying lock timeouts and other SQL errors as collisions. A hosting/database lock problem can still require investigation. |
| Tenant migration access-denied errors | Previously a denied tenant connection could fall back to the environment database. Fail that company explicitly, propagate registry synchronization/seeder failures, and restore connection configuration after each company. |
| Emergency logger when LOG_CHANNEL/LOG_STACK are empty | Empty values now fall back to stack/single. Deliberately configured nonempty channel names must still be valid. |
| Missing Spreadsheet Csv class during application bootstrap | CSV encoding configuration now uses the same literal value as the vendor constant, avoiding an early boot dependency. Spreadsheet features still require a complete Composer installation. |
| OCR process exceeded five seconds | The per-attempt limit is now configurable through TESSERACT_TIMEOUT (default 5 seconds, bounded to 1–30 seconds). Missing executables/language data still require a hosting fix. |

`php artisan server:check` is a new **read-only** diagnostic for dependencies, writable runtime directories, named routes, registry/session database access, company database access, and schema fields implicated by the log. It exits nonzero on failures and never creates tables or records. Use `--company=CODE` for one company or `--skip-database` for code/config checks. It is a deployment aid, not proof that every business workflow succeeds.

Changed application files: `app/Http/Requests/Auth/LoginRequest.php`, `app/Services/AttendanceAutoClockOut.php`, `app/Http/Controllers/SuperAdminController.php`, `app/Services/DeveloperTaskSync.php`, `app/Http/Controllers/EmployeeController.php`, `app/Models/User.php`, `app/Console/Commands/TenantMigrateCommand.php`, `app/Console/Commands/CheckServerDeployment.php`, and `app/Services/GovernmentIdDobVerifier.php`. Configuration changes: `config/logging.php`, `config/excel.php`, `config/services.php`, and `.env.example`. Regression fixtures/checks are under `tests/regression`; existing fixtures were updated for the current company settings and designation requirements.

## Other failure families in the log

These have existing fixes or compatibility migrations in the current branch. Deploy the complete branch and its pending migrations, not just the files changed today.

| Log family | Current code / required verification |
| --- | --- |
| 705 payroll dashboard section failures, unknown effective dates, missing salary assignments, deleted_at | Payroll schema repair checks actual columns rather than trusting a stale repair cache; tenant payroll compatibility migrations add the missing fields/tables. Payroll repair regression exercises a partially deployed tenant and preserves existing records. |
| Missing notifications and sessions tables | Run the migrations on the **correct connection**. Notifications belong to each tenant; sessions use `config('session.connection')`. Code deployment alone cannot repair absent production tables. |
| Missing task short code/labels/priority/milestone, project code/status/files/miroboard, client status/country/category | Existing task/project/client compatibility migrations and schema services. Task and client/project schema regressions pass. |
| Missing country dialing fields, department name/code/parent department, designation parent/level, holiday date/occasion/group, award appreciation, sticky-note completion, company suspension fields | Existing compatibility migrations/services. Run the diagnostic and pending migrations against the affected registered database. Do not rename live columns manually without reviewing their migration. |
| Attendance NULL date/working_from, missing location, status truncation, null attendance view values | Current date validation, location/status compatibility, and guarded attendance view. Attendance saving, legacy view rendering, and daily clock regressions pass. |
| Employee role string written to integer column | EmployeeDetail converts built-in role names to NULL for the legacy numeric role-ID field; role remains on users. The update/exit-date regression passes. |
| Employee deletion blocked by lead-contact FK | Current employee deletion archives inactive employee accounts rather than deleting referenced users. Do not drop the FK or delete historical lead records. |
| Wrong-database user/module FK errors in permissions, leave, payroll, CRM, sticky notes, documents | Current tenant model/connection isolation. Tenant isolation regressions pass. Existing invalid IDs or broken grants in production require a read-only audit before any data repair; this change does not disable constraints or rewrite historical ownership. |
| Duplicate company/client email | Current company validation uses central.companies, client validation checks tenant.clients and tenant.users. Reusing an existing account's email should produce validation feedback; it should not bypass uniqueness. |
| Missing middleware alias, controller imports, duplicate methods/imports, PHP/Blade syntax, duplicate route names | Current branch has the middleware alias/import fixes. All 644 application/config/migration PHP files passed syntax checks; route and Blade caches compile. |
| Missing payroll/client/manager route references | Payroll/client named routes exist; current manager navigation uses the shared dashboard route. Route cache builds successfully. |
| Undefined callback/history/employees/award, missing Deal leadContact / TicketActivity user / Attendance employee relationship | Current export callback and view/controller data paths were reviewed; the obsolete Attendance employee eager-load path is absent. View/mail regressions cover the repaired attendance, ticket mail, and legacy company/country paths. Historical stack traces alone do not prove every runtime view is covered. |
| Notification missing broadcast/array method, emails.default missing, guest sidebar notifications | Current notification/mail implementations and guest guards; mail/view regression passes. |
| Headers already sent | Logged after other rendering/response errors. Repair the originating exception and clear compiled views; do not edit Symfony vendor response handling or suppress errors. If it persists, capture the first error from a fresh request and check output/BOMs in the deployed files. |
| Table companies already exists | Migration tracking and actual production schema disagree. Back up and reconcile the existing table with the migration ledger; do not run migrate:fresh, drop tables, or blindly mark every migration complete. |
| MySQL access denied, too many connections, lost connection | Hosting/environment actions below are required. These cannot be fixed by changing a local PHP file alone. |
| Tesseract missing, OCR timeout/no text | Hosting executable/process configuration below. Current verification safely leaves ambiguous/unreadable IDs pending manual admin review instead of approving them automatically. |

## Production deployment

Back up the primary, central, and company databases and uploaded files. Use the release's PHP version and run commands from the Laravel application directory. The web PHP runtime and CLI must use compatible extensions and configuration.

1. Deploy the complete branch. Install locked production dependencies:

   ```sh
   composer install --no-dev --prefer-dist --optimize-autoloader
   composer check-platform-reqs
   php artisan optimize:clear
   php artisan server:check --skip-database
   ```

2. Review the **server** `.env`. No local `.env` or credentials were changed in this repair. Keep real passwords private. Use the hosting account's fully prefixed database names, not the local development names:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_TIMEZONE=Asia/Kolkata
   DB_TIMEZONE=+05:30
   LOG_CHANNEL=stack
   LOG_STACK=single
   # DB_DATABASE: actual primary/platform database
   # CENTRAL_DB_DATABASE: actual company registry database
   # TENANT_DB_USERNAME / TENANT_DB_PASSWORD: credential authorized for registered company DBs
   # SESSION_CONNECTION: stable configured connection used by existing sessions
   # TESSERACT_BINARY: actual Linux executable path, if OCR is enabled
   TESSERACT_TIMEOUT=5
   ```

   Review DB_HOST/PORT, CENTRAL_DB_* and TENANT_DB_* grants, DB_URL overrides, and the actual session driver/connection. Do not point every company's db_name at one fallback database. Verify the MySQL user has the necessary grants on each registered database, plus schema privileges for the deployment migrations. Never log passwords.

3. Inspect migration status before making schema changes. The repository has primary, central, and tenant migration sets. Run primary migrations on the configured primary connection and central migrations on central; test a company migration before migrating all tenants:

   ```sh
   php artisan migrate:status
   php artisan migrate:status --database=central --path=database/migrations/central
   php artisan migrate --force
   php artisan migrate --database=central --path=database/migrations/central --force
   php artisan tenant:migrate --company=YOUR_COMPANY_CODE
   php artisan server:check --company=YOUR_COMPANY_CODE
   # After checking that company's result:
   php artisan tenant:migrate
   php artisan server:check
   ```

   Stop on failures. If an existing table is reported as a pending CREATE migration, reconcile that database's migration history against its schema first. Do not use `--seed` to repair production data. The corrected tenant command no longer migrates a fallback database after access denial.

4. In the hosting panel, resolve `max_user_connections`: inspect active queries and long transactions, PHP/queue worker counts, overlapping scheduled jobs, and the account's MySQL quota. Coordinate worker concurrency or request a suitable quota increase from the host. Fix DB access grants and server restarts/timeouts for 1044/2013 errors. Increasing Laravel request timeouts does not increase the database connection quota.

5. For government-ID OCR, install Tesseract plus English language data, set `TESSERACT_BINARY` to the installed executable (for example `/usr/bin/tesseract` **only if it exists**), and allow PHP `proc_open`. Verify using the same OS user as PHP. A Windows executable path cannot work on Linux. If a valid scan needs longer than five seconds, configure `TESSERACT_TIMEOUT` (for example 10) and verify the complete upload stays within PHP/web server time limits: there can be multiple OCR attempts per upload. Investigate slow/oversized scans and server resources before raising limits. Missing OCR is not permission to auto-approve a DOB; retain manual verification.

6. Rebuild caches after configuration and migration verification:

   ```sh
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan queue:restart
   ```

   Reload PHP workers/OPcache using the host's release procedure so old code is not served. Confirm storage/framework, storage/logs, and bootstrap/cache are writable by the web process.

7. Record a deployment timestamp and test fresh requests: platform/company/HR login, failed/disabled login, employee create/update/archive, department/project/task/client forms, task assignment into a company, clock in/out/automatic closure, payroll formula listing/projection, payslip rendering/export, and ticket mail. Check the new log entries and `server:check` output. Preserve the historical log; removing it does not fix the failures.

## Local validation

Checks use isolated SQLite fixtures and fake/test mail, not the supplied server databases. All 20 regression entry points passed (some also run prerequisite checks): login/company deletion, tenant isolation, tenant payroll schema, payroll partial-deployment repair, task form/priority/labels schema, client/project schema, award recognition schema, holiday compatibility, developer dashboard schema, super-admin legacy errors, phone/country fields, attendance auto clock-out and daily clock lifecycle, developer tenant assignment, server deployment safety, employee ID company isolation, employee exit-date/role update, employee creation committed before simulated SMTP failure, SMTP invitation workflow, and legacy view/ticket-mail rendering. PHP syntax: 644 files. Blade/route cache compilation and `server:check --skip-database` passed.

Production MySQL locks/DDL behavior, installed OCR execution, connection quotas, and live database schema were **not** tested against the server. The read-only diagnostic and fresh-request checks must be run there before declaring the deployment clean.
