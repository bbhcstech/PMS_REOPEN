# Company employee totals

Each company already has one registered tenant database. `User` and `EmployeeDetail` both use the `tenant` connection, selected by the company session and checked against the company registry. Admin, HR, Manager, and custom roles therefore write into the same company workspace; there is no separate employee database per role and no new cross-company database to configure.

The inconsistent totals came from three definitions: Admin counted only `role=employee`, HR counted employee profile rows, and custom HR counted all user accounts. `CompanyEmployeeDirectory` now supplies the company total from distinct user accounts with Employee, HR, or Manager roles, including custom roles that use these access roles. This matches the employee register's existing role set. Archived accounts, clients, admins, unassigned accounts, and other companies are excluded. Inactive accounts still registered in the employee list remain included; this is a total, not an active-only metric.

Admin/HR dashboards, the employee register's Total Employees card, custom-role dashboards, and the Manager/Employee/Developer dashboard total all use this query. A company total is independent of the creator, HR/manager assignment, search, and table filters. Access to individual employee details and team lists still follows existing permissions. Payroll eligibility remains its own salary-assignment metric.

No records are moved, deleted, or duplicated. No migration or environment change is required. `php tests/regression/company_employee_totals.php` uses isolated SQLite databases to check shared tenant writes, identical totals for built-in/custom roles, duplicate profiles, archive/restore/delete updates, company filtering, and switching databases with colliding user IDs.
