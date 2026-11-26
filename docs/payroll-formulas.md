# Payroll formulas and verified test data

Open **Payroll → Formulas & Rules**. Company administrators can create, edit, test, enable, disable, archive and restore formulas. Permitted HR accounts can view and run read-only tests. Formula records, effective rules and mutations are restricted to the signed-in company. Every mutation is audited. Editing an old version is rejected rather than overwriting another administrator's changes.

## Admin workflow

1. Choose **Add formula**, or **Use this formula** on a sample. Samples are never saved or activated merely by viewing/testing them.
2. Choose a name, unique uppercase code, category, effective dates and expression. New codes add a custom component; built-in codes override a named calculation step.
3. Review the available variables for the selected step. Enter numeric test values and choose **Run test**. Saving runs the same test again and validates the expression on the server.
4. Enable the rule when ready. Rules apply when their date range includes the payroll month's first day. A rule effective on 14 October does not govern October payroll; use 1 October to govern that month.
5. Archived rules are available through the **Archived** filter. Restoring always leaves the rule disabled, ready for review. Archive and formula edits do not modify existing payslip snapshots.

## Calculation behaviour

| Category | Payroll effect for a new custom code |
| --- | --- |
| Employee earnings | Added to Other Allowances before gross salary |
| Employee deductions / tax | Added to Other Deductions before net pay |
| Bonus & appraisal payments | Added to Additional Earnings before total in-hand |
| Employer contributions / statutory costs | Added to employer costs and CTC, never deducted from employee pay |

Built-in codes are defined in `PayrollFormulaCatalog`: CURRENT_BASIC, GROSS_SALARY, PF_EMPLOYEE, ESI_EMPLOYEE, PROFESSIONAL_TAX, TOTAL_DEDUCTIONS, NET_PAY, ATTENDANCE_BONUS, BEST_EMPLOYEE_BONUS, TOTAL_ADDITIONAL_EARNINGS, TOTAL_IN_HAND, PF_EMPLOYER, ESI_EMPLOYER, EDLI_EMPLOYER, TOTAL_EMPLOYER_CONTRIBUTIONS and CTC. Statutory overrides still respect the payroll policy's enable/disable settings. Aggregate override expressions must include the corresponding Other Allowances/Deductions/Employer variables when those amounts should be retained. Custom bonus amounts remain included for legacy additional-earnings aggregate expressions that predate CUSTOM_BONUSES.

Each step exposes only variables available at that step. Arbitrary dependencies on other custom codes and forward references are rejected. The bounded parser supports arithmetic, comparisons, boolean operators, parentheses, conditional expressions, MIN, MAX, ROUND, ABS, FLOOR, CEIL and IF. It does not evaluate PHP or scripts. Missing/nonnumeric inputs, invalid syntax, division by zero and nonfinite/oversized results are rejected. Active unvalidated or failing formulas stop payroll with a named error; they do not silently fall back to a different result.

Appraisal variables use the latest approved review for the employee and company whose review period is completed, and whose approval/update is recorded by payroll month end. Drafts, submitted reviews, future periods and other companies do not contribute. Missing approved appraisal values are zero.

For a calculated basic adjustment, CURRENT_BASIC can use:

```text
WORKING_DAYS > 0 ? BASIC * (1 + APPRAISAL_INCREMENT_PERCENT / 100) * PAYABLE_DAYS / WORKING_DAYS : 0
```

For a separate appraisal payment, create a bonus rule:

```text
IF(APPRAISAL_SCORE >= 80, BASIC * APPRAISAL_INCREMENT_PERCENT / 100, 0)
```

Enabling both combines their effects. Neither permanently changes Employee Salary assignments. Restrict the effective date range if a payment should apply to only one payroll month.

## Reproducible database test

Run from the project root:

```sh
php tests/regression/payroll_formulas.php
node tests/regression/payroll_formula_ui.cjs
```

The PHP test creates real salary assignment, attendance, approved/pending appraisal, policy and formula records in isolated in-memory SQLite databases. It does not seed or change employee records in company databases.

The verified September 2026 case uses monthly basic ₹30,000, HRA 40%, special allowance explicitly zero, 20 present and 2 absent working days, and a completed approved appraisal scoring 85 with a 7% increment recommendation. Statutory deductions are disabled in this fixture to isolate custom formulas; these test settings are not a production policy.

| Calculation | Verified result |
| --- | ---: |
| Prorated basic: 30,000 × 20 / 22 | ₹27,272.73 |
| HRA: 40% of prorated basic | ₹10,909.09 |
| Gross | ₹38,181.82 |
| Custom recovery: ROUND(GROSS_SALARY × 0.02, 2) | ₹763.64 |
| Net before appraisal payment | ₹37,418.18 |
| Approved appraisal payment: 30,000 × 7% | ₹2,100.00 |
| Employee in-hand | ₹39,518.18 |
| Example employer contribution: 30,000 × 1% | ₹300.00 |
| CTC: gross + appraisal payment + employer contribution | ₹40,581.82 |

The test additionally checks future/expired/foreign formulas, incomplete/unapproved appraisals, invalid inputs, stale edits, audit records, repeated disable requests, archive/restore, tester equivalence, and failure during payroll evaluation. A separate assertion verifies an approved appraisal adjustment can change calculated basic to ₹29,181.82 while the salary assignment remains ₹30,000.

The UI script test covers modal creation/editing, variable extraction and presets, date/version preservation, subdirectory URLs, read-only tests, failed requests, duplicate submissions and settings changed while saving. It is a simulated DOM check, not a live-browser visual inspection.

## Deployment

Deploy the new services, controller trait, formula model, routes, template and JavaScript together. No new `.env` variables or database columns are required. Existing payroll schema repair supplies the formula fields already used by this feature. Clear cached routes and views, restart persistent workers, and refresh open browser tabs:

```sh
php artisan route:clear
php artisan view:clear
php artisan queue:restart
```

Before processing a new payroll, review any existing active formula marked **Needs validation** and save it through the editor. Explicit zero HRA/special allowance values are now preserved; absent attendance rows no longer generate fallback working hours or attendance bonuses; an explicitly disabled professional tax policy is now respected.
