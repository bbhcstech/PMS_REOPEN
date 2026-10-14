# Appraisal reviews

Company Admin and HR can evaluate company employees, including managers. HR's own appraisal is read-only and only a company administrator can modify it. This rule applies to create, update, delete, restoration and bulk calculation, independently of module permissions. Managers and employees can view only their own records and cannot evaluate other people. Existing appraisal action permissions still apply to HR.

## Admin configuration

Open **Settings → Performance & Appraisal Settings**. Set the review cycle, attendance/work/cultural behaviour weights (positive integers totaling 100%), administrator approval requirement and salary increase recommendations for grades A+, A, B, C and D. The default weights are work 40%, attendance 30% and culture 30%. Behaviour uses three reviewer-entered ratings from 1 to 10: teamwork, communication and professional conduct, averaged and scaled to 100.

With administrator approval enabled, HR submissions are marked `submitted`. Admin reviews the evaluation through its edit form and approves it. An HR edit of an approved evaluation requires approval again. When approval is disabled, HR also needs the Approve module permission to finalize evaluations. Changing a policy does not silently alter already saved evaluations: each new or updated evaluation stores its policy snapshot.

## Recorded metrics and review

Opening the page is read-only. **Calculate recorded metrics** creates/refreshes drafts from real records. It skips the current HR user's own record, deleted records and submitted/approved evaluations. Drafts have no invented behaviour rating, grade or increment recommendation.

Attendance counts unique dates, excludes future dates, days before joining, scheduled off-days, holidays and approved leave time. Full present/late days count as 1; half days count as 0.5. Archived attendance/leave records are excluded. Work scoring is the completed fraction of assigned tasks in the selected period, using a task's due date, then start date, then creation date. Single, comma-separated and pivot-table assignments are recognized; company data and task IDs remain isolated. Completion after the review period is not credited. Missing records yield zero recorded credit rather than random scores.

Reviewers can adjust attendance/work scores with supporting remarks and must enter cultural behaviour evidence. **Evaluate Employee** supports manual evaluation; draft rows provide the recorded baseline. HR's own edit/delete buttons and modal are absent, and their user ID is absent from the employee picker. Admin sees and manages the HR appraisal normally.

Salary increases are recommendations tied to appraisal grades, not automatic payroll mutations. Admin applies an approved compensation change separately through **Payroll → Employee Salary**, linked from the settings screen. Existing historic evaluations are preserved until explicitly reviewed.

## Deployment

Deploy the controller/model/services, appraisal views and settings partial, and new route. Run normal tenant migrations to add the nullable policy snapshot and recommended increment columns:

```sh
php artisan tenant:migrate
php artisan migrate --database=tenant --path=database/migrations/tenant/2026_10_14_000002_add_appraisal_policy_snapshot.php --force
php artisan route:clear
php artisan view:clear
php artisan queue:restart
```

The first command migrates registered company databases; the second covers the default tenant database. No `.env` changes are required for appraisal configuration. The policy is stored in each company's `app_settings`; each evaluation's snapshot and increase recommendation are stored in that company's `appraisals`. The new migration is idempotent and does not rewrite existing appraisal scores or salaries.

Regression checks:

```sh
php tests/regression/appraisal_review_workflow.php
php tests/regression/employee_appraisal_isolation.php
```
