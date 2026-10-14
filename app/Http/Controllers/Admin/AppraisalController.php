<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Appraisal, User};
use App\Services\{AppraisalMetrics, AppraisalPolicy, WorkforceAccess};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppraisalController extends Controller
{
    private function actor(): User
    {
        $actor = auth()->user();
        abort_unless($actor && $actor->company_id, 403);
        return $actor;
    }

    private function authorizeEmployee(User $actor, User $employee, string $action): void
    {
        abort_unless(app(AppraisalPolicy::class)->canEvaluate($actor, $employee), 403,
            'HR cannot change their own appraisal. A company administrator must evaluate it.');
        abort_unless($actor->hasModulePermission('appraisal', $action), 403, 'You do not have permission to perform this appraisal action.');
    }

    public function index(Request $request)
    {
        $actor = $this->actor();
        $policy = app(AppraisalPolicy::class);
        $canManage = $policy->canManage($actor);
        $selectedPeriod = (string) $request->get('period', $policy->currentPeriod());
        $policy->periodDates($selectedPeriod);
        $periods = $policy->periods();
        if (!in_array($selectedPeriod, $periods, true)) $selectedPeriod = $policy->currentPeriod();
        $search = $request->get('search');
        $employeesQuery = User::whereIn(DB::raw('LOWER(role)'), ['employee', 'user', 'manager', 'hr'])
            ->where('company_id', $actor->company_id)
            ->where(fn ($query) => $query->where('is_active', true)->orWhereNull('is_active'));
        if (!$canManage) $employeesQuery->whereKey($actor->id);
        if ($search) $employeesQuery->where(fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->orWhere('designation', 'like', '%'.$search.'%'));
        $employees = $employeesQuery->orderBy('name')->get();
        $query = Appraisal::with(['employee', 'evaluator'])->where('company_id', $actor->company_id)
            ->whereHas('employee', fn ($query) => $query->where('company_id', $actor->company_id))->where('appraisal_period', $selectedPeriod);
        if (!$canManage) $query->where('employee_id', $actor->id);
        if ($search) $query->whereHas('employee', fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->orWhere('designation', 'like', '%'.$search.'%'));
        // GET is read-only: it never creates, restores or changes appraisals.
        $appraisals = $query->get();
        $evaluated = $appraisals->whereIn('status', ['submitted', 'approved']);
        if ($appraisals->isNotEmpty() && !$appraisals->first()->getRawOriginal('status')) $evaluated = $appraisals;
        $avgScore = $evaluated->avg('overall_score') ?? 0;
        $topPerformersCount = $evaluated->where('overall_score', '>=', 80)->count();
        $totalEvaluated = $evaluated->count();
        $needsImpCount = $evaluated->where('overall_score', '<', 70)->count();
        $editableEmployeeIds = $employees->filter(fn ($employee) => $policy->canEvaluate($actor, $employee))->pluck('id')->all();
        $editableAppraisalIds = $appraisals->filter(fn ($record) => $record->employee && $policy->canEvaluate($actor, $record->employee))->pluck('id')->all();
        $settings = $policy->settings();
        $canCreate = $canManage && $actor->hasModulePermission('appraisal', 'create');
        $canEdit = $canManage && $actor->hasModulePermission('appraisal', 'edit');
        $canDelete = $canManage && $actor->hasModulePermission('appraisal', 'delete');
        return view('admin.appraisal.index', compact('appraisals', 'employees', 'selectedPeriod', 'avgScore', 'topPerformersCount', 'totalEvaluated', 'needsImpCount',
            'canManage', 'canCreate', 'canEdit', 'canDelete', 'editableEmployeeIds', 'editableAppraisalIds', 'settings', 'periods'));
    }

    public function update(Request $request, int $id)
    {
        $actor = $this->actor();
        $record = Appraisal::where('company_id', $actor->company_id)->findOrFail($id);
        $request->merge(['employee_id' => $record->employee_id, 'appraisal_period' => $record->appraisal_period]);
        return $this->store($request);
    }

    public function saveBehaviour(Request $request)
    {
        $actor = $this->actor();
        abort_unless(WorkforceAccess::isAdmin($actor), 403, 'Only a company administrator may enter these ratings.');
        $data = $request->validate([
            'employee_id' => ['required', 'integer'], 'appraisal_period' => ['required', 'string', 'max:30'],
            'teamwork_score' => ['required', 'numeric', 'between:1,10'],
            'communication_score' => ['required', 'numeric', 'between:1,10'],
            'punctuality_score' => ['required', 'numeric', 'between:1,10'],
            'behaviour_remarks' => ['required', 'string', 'max:5000'],
        ]);
        $policy = app(AppraisalPolicy::class);
        $policy->periodDates($data['appraisal_period']);
        $employee = User::where('company_id', $actor->company_id)->findOrFail($data['employee_id']);
        $behaviour = round(($data['teamwork_score'] + $data['communication_score'] + $data['punctuality_score']) / 3 * 10, 2);
        DB::connection('tenant')->transaction(function () use ($actor, $employee, $data, $policy, $behaviour) {
            User::where('company_id', $actor->company_id)->whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $record = Appraisal::withTrashed()->where('company_id', $actor->company_id)
                ->where('employee_id', $employee->id)->where('appraisal_period', $data['appraisal_period'])->first();
            $this->authorizeEmployee($actor, $employee, $record ? 'edit' : 'create');
            abort_if($record?->trashed(), 422, 'This appraisal was deleted. Restore it through the full appraisal evaluation before editing ratings.');
            // Existing work/attendance evidence is preserved. New rows use recorded metrics.
            $record ??= new Appraisal(array_merge(app(AppraisalMetrics::class)->calculate($employee, $data['appraisal_period']), [
                'company_id' => $actor->company_id, 'employee_id' => $employee->id,
                'appraisal_period' => $data['appraisal_period'],
            ]));
            $settings = $policy->settings();
            $record->fill(collect($data)->only(['teamwork_score', 'communication_score', 'punctuality_score', 'behaviour_remarks'])->all());
            $record->fill([
                'behaviour_score' => $behaviour,
                'overall_score' => $policy->score((float) $record->project_score, (float) $record->attendance_score, $behaviour, $settings),
                'overall_grade' => 'Pending', 'status' => 'draft', 'evaluated_by' => $actor->id,
                'recommended_increment_percent' => null, 'policy_snapshot' => $settings,
                'recommendation' => 'Complete the appraisal review before approval.',
            ])->save();
        });
        return back()->with('appraisal_tab', 'behaviour')->with('success', 'Behaviour ratings saved. Complete and approve the appraisal review to finalize the overall grade and salary recommendation.');
    }

    public function store(Request $request)
    {
        $actor = $this->actor();
        abort_unless(app(AppraisalPolicy::class)->canManage($actor), 403);
        $request->validate(['employee_id' => ['required', 'integer'], 'appraisal_period' => ['required', 'string', 'max:30']]);
        $employee = User::where('company_id', $actor->company_id)->findOrFail($request->integer('employee_id'));
        $existing = Appraisal::withTrashed()->where('company_id', $actor->company_id)->where('employee_id', $employee->id)->where('appraisal_period', $request->appraisal_period)->first();
        $this->authorizeEmployee($actor, $employee, $existing ? 'edit' : 'create');
        $data = $request->validate([
            'project_score' => ['required', 'numeric', 'between:0,100'], 'attendance_score' => ['required', 'numeric', 'between:0,100'],
            'teamwork_score' => ['required', 'numeric', 'between:1,10'], 'communication_score' => ['required', 'numeric', 'between:1,10'],
            'punctuality_score' => ['required', 'numeric', 'between:1,10'],
            'project_remarks' => ['nullable', 'string', 'max:5000'], 'attendance_remarks' => ['nullable', 'string', 'max:5000'],
            'behaviour_remarks' => ['required', 'string', 'max:5000'], 'recommendation' => ['nullable', 'string', 'max:255'],
        ]);
        $policy = app(AppraisalPolicy::class);
        $metrics = app(AppraisalMetrics::class)->calculate($employee, $request->appraisal_period);
        foreach (['project' => 'project_score', 'attendance' => 'attendance_score'] as $criterion => $field) {
            if (abs((float) $data[$field] - (float) $metrics[$field]) > 0.01 && empty(trim($data[$criterion.'_remarks'] ?? ''))) {
                throw \Illuminate\Validation\ValidationException::withMessages([$criterion.'_remarks' => 'Explain why the '.$criterion.' score differs from recorded metrics.']);
            }
        }
        $settings = $policy->settings();
        $behaviour = round(($data['teamwork_score'] + $data['communication_score'] + $data['punctuality_score']) / 3 * 10, 2);
        $overall = $policy->score((float) $data['project_score'], (float) $data['attendance_score'], $behaviour, $settings);
        $grade = Appraisal::computeGrade($overall);
        $status = $settings['admin_approval_required'] === '1' && !WorkforceAccess::isAdmin($actor) ? 'submitted' : 'approved';
        if ($status === 'approved') abort_unless($actor->hasModulePermission('appraisal', 'approve'), 403, 'Approval permission is required to finalize an appraisal.');
        DB::connection('tenant')->transaction(function () use ($existing, $metrics, $data, $employee, $actor, $request, $behaviour, $overall, $grade, $settings, $policy, $status) {
            User::where('company_id', $actor->company_id)->whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $current = Appraisal::withTrashed()->where('company_id', $actor->company_id)->where('employee_id', $employee->id)
                ->where('appraisal_period', $request->appraisal_period)->first();
            $this->authorizeEmployee($actor, $employee, $current ? 'edit' : 'create');
            if ($current?->trashed()) $current->restore();
            Appraisal::updateOrCreate(['company_id' => $actor->company_id, 'employee_id' => $employee->id, 'appraisal_period' => $request->appraisal_period],
                array_merge($metrics, $data, [
                    'behaviour_score' => $behaviour, 'overall_score' => $overall, 'overall_grade' => $grade,
                    'recommendation' => ($data['recommendation'] ?? null) ?: $this->recommendation($overall),
                    'recommended_increment_percent' => $policy->increment($grade, $settings), 'policy_snapshot' => $settings,
                    'status' => $status, 'evaluated_by' => $actor->id,
                ]));
        });
        return back()->with('success', $status === 'submitted' ? 'Appraisal submitted for administrator approval.' : 'Appraisal approved. Salary increase recommendation recorded.');
    }

    public function autoCalculate(Request $request)
    {
        $actor = $this->actor();
        $policy = app(AppraisalPolicy::class);
        abort_unless($policy->canManage($actor), 403);
        $period = (string) $request->get('period', $policy->currentPeriod());
        $policy->periodDates($period);
        $settings = $policy->settings();
        $employees = User::where('company_id', $actor->company_id)->whereIn(DB::raw('LOWER(role)'), ['employee', 'user', 'manager', 'hr'])
            ->where(fn ($query) => $query->where('is_active', true)->orWhereNull('is_active'))->get();
        $count = 0;
        DB::connection('tenant')->transaction(function () use ($employees, $actor, $policy, $period, $settings, &$count) {
            foreach ($employees as $employee) {
                if (!$policy->canEvaluate($actor, $employee)) continue;
                User::where('company_id', $actor->company_id)->whereKey($employee->id)->lockForUpdate()->firstOrFail();
                $record = Appraisal::withTrashed()->where('company_id', $actor->company_id)->where('employee_id', $employee->id)->where('appraisal_period', $period)->first();
                if ($record && ($record->trashed() || in_array($record->status, ['submitted', 'approved'], true))) continue;
                $this->authorizeEmployee($actor, $employee, $record ? 'edit' : 'create');
                $metrics = app(AppraisalMetrics::class)->calculate($employee, $period);
                $behaviourData = $record && $record->teamwork_score >= 1 && $record->communication_score >= 1 && $record->punctuality_score >= 1
                    ? $record->only(['teamwork_score', 'communication_score', 'punctuality_score', 'behaviour_score', 'behaviour_remarks', 'evaluated_by'])
                    : [
                    'teamwork_score' => 0, 'communication_score' => 0, 'punctuality_score' => 0, 'behaviour_score' => 0,
                    'behaviour_remarks' => 'Awaiting reviewer feedback on teamwork, communication and professional conduct.',
                    'evaluated_by' => null,
                ];
                Appraisal::updateOrCreate(['company_id' => $actor->company_id, 'employee_id' => $employee->id, 'appraisal_period' => $period], array_merge($metrics, $behaviourData, [
                    'overall_score' => 0, 'overall_grade' => 'Pending', 'recommendation' => 'Reviewer evaluation required',
                    'status' => 'draft', 'recommended_increment_percent' => null, 'policy_snapshot' => $settings,
                ]));
                $count++;
            }
        });
        return back()->with('success', $count.' drafts calculated from recorded work and attendance. Behaviour needs reviewer feedback. Your own HR appraisal, deleted reviews and submitted/approved evaluations were left unchanged.');
    }

    public function destroy(int $id)
    {
        $actor = $this->actor();
        $record = Appraisal::where('company_id', $actor->company_id)->findOrFail($id);
        $employee = User::where('company_id', $actor->company_id)->findOrFail($record->employee_id);
        $this->authorizeEmployee($actor, $employee, 'delete');
        $record->delete();
        return back()->with('success', 'Appraisal entry deleted successfully.');
    }

    private function recommendation(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Promote & Merit Increment', $score >= 80 => 'Salary Increment & Role Growth',
            $score >= 70 => 'Standard Bonus & Continuation', $score >= 60 => 'Role Maintenance & Skill Mentoring',
            default => 'Performance Improvement Plan (PIP)',
        };
    }
}
