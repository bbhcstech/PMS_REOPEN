# Manual behaviour ratings

In **HR → Appraisal → Table 3: Behaviour**, the company Admin can use **Add ratings** or **Edit ratings** for each employee in the selected review period. Employees without an existing appraisal are included. Enter Teamwork, Communication, and Professional conduct from 1–10, plus feedback/examples. The form previews the score; the server derives the authoritative score as `(teamwork + communication + conduct) / 3 × 10`. Professional conduct retains the existing database field `punctuality_score` for compatibility.

Saving creates or updates a draft. Existing work/attendance scores and supporting evidence remain unchanged; new drafts get recorded work/attendance metrics. Changed ratings invalidate any previous submitted/approved result and salary recommendation until the full appraisal is reviewed and approved again. The form warns before changing a reviewed appraisal. Work/attendance recalculation preserves manually entered behaviour ratings. No missing ratings are guessed: the table displays **Not rated / Awaiting ratings**.

The direct rating endpoint is Admin-only and enforces company, employee, review period, module actions, score bounds, and feedback validation. Existing HR evaluation of other employees remains available through the full review workflow; HR self-review stays blocked. Saving ratings does not change salary assignments or previously generated payslips. Deleted appraisals are not automatically restored.

No schema, environment, or production data migration is required. Normal deployment should refresh cached routes/views. Checks: `php tests/regression/appraisal_manual_behaviour.php`, `php tests/regression/employee_appraisal_isolation.php`, `node tests/regression/appraisal_ratings_ui.cjs`.
