<?php

namespace App\Http\Controllers;

use App\Models\PayrollPolicy;
use App\Models\PayrollPolicyHistory;
use App\Models\User;
use App\Services\PayrollPolicyService;
use Illuminate\Http\Request;

class PayrollPolicyController extends Controller
{
    protected PayrollPolicyService $policyService;

    public function __construct(PayrollPolicyService $policyService)
    {
        $this->policyService = $policyService;
    }

    /**
     * Display Payroll Policies Dashboard with KPI summary, 12 policy categories, and history.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()?->company_id;

        // Ensure at least 1 active policy exists
        $activePolicy = $this->policyService->getActivePolicy($companyId);

        // Fetch KPI Metrics from DB
        $totalPolicies = PayrollPolicy::count();
        $activePoliciesCount = PayrollPolicy::where('status', 'published')->count();
        $pendingChangesCount = PayrollPolicy::where('status', 'draft')->count();
        $lastUpdatedRecord = PayrollPolicy::latest('updated_at')->first();
        $lastUpdatedFormatted = $lastUpdatedRecord ? $lastUpdatedRecord->updated_at->format('d M Y, h:i A') : 'Today';

        $summary = [
            'total_policies' => $totalPolicies,
            'active_policies' => $activePoliciesCount,
            'pending_changes' => $pendingChangesCount,
            'last_updated' => $lastUpdatedFormatted,
        ];

        // All Policies for list table
        $policiesList = PayrollPolicy::with(['creator', 'updater'])->latest()->get();

        // Version histories log
        $histories = PayrollPolicyHistory::with(['changer', 'policy'])->latest()->take(15)->get();

        // Employees for Policy Simulator
        $employees = User::where(function($q) {
            $q->where('role', '!=', 'superadmin')->orWhereNull('role');
        })->orderBy('name')->get();

        return view('admin.payroll.policies.index', compact(
            'activePolicy',
            'summary',
            'policiesList',
            'histories',
            'employees'
        ));
    }

    /**
     * Store new payroll policy.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
        ]);

        $payload = $request->all();
        $payload['created_by'] = auth()->id();

        $policy = $this->policyService->savePolicy($payload, null, auth()->id());

        return redirect()->route('payroll.policies.index')
            ->with('success', 'Payroll policy "' . $policy->name . '" (v' . $policy->version . ') created and published successfully!');
    }

    /**
     * Update active policy rules.
     */
    public function update(Request $request, PayrollPolicy $policy)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published,archived',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date',
        ]);

        $payload = $request->all();
        $updatedPolicy = $this->policyService->savePolicy($payload, $policy->id, auth()->id());

        return redirect()->route('payroll.policies.index')
            ->with('success', 'Payroll Policy updated successfully to version v' . $updatedPolicy->version . '!');
    }

    /**
     * Duplicate an existing policy.
     */
    public function duplicate(PayrollPolicy $policy)
    {
        $data = $policy->toArray();
        unset($data['id'], $data['code'], $data['version'], $data['created_at'], $data['updated_at']);

        $data['name'] = $policy->name . ' (Copy)';
        $data['status'] = 'draft';
        $data['is_default'] = false;

        $newPolicy = $this->policyService->savePolicy($data, null, auth()->id());

        return redirect()->route('payroll.policies.index')
            ->with('success', 'Payroll policy duplicated as "' . $newPolicy->name . '" (Draft).');
    }

    /**
     * Toggle policy active status (published/draft).
     */
    public function toggleStatus(PayrollPolicy $policy)
    {
        $newStatus = $policy->status === 'published' ? 'draft' : 'published';
        $policy->update([
            'status' => $newStatus,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Policy status updated to ' . ucfirst($newStatus) . '.');
    }

    /**
     * Fetch JSON version history for a policy.
     */
    public function history(PayrollPolicy $policy)
    {
        $logs = PayrollPolicyHistory::with('changer')
            ->where('payroll_policy_id', $policy->id)
            ->orderBy('version', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'policy' => $policy,
            'histories' => $logs,
        ]);
    }

    /**
     * Run Policy Simulation (Test Policy).
     */
    public function simulate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'working_days' => 'nullable|integer|min:1|max:31',
        ]);

        $user = User::findOrFail($request->user_id);
        $year = (int) $request->year;
        $month = (int) $request->month;
        $workingDays = (int) ($request->working_days ?: 22);

        $simulatedRules = $request->input('simulated_rules', []);

        $simulationResult = $this->policyService->simulatePolicyImpact($user, $simulatedRules, $year, $month, $workingDays);

        return response()->json([
            'success' => true,
            'data' => $simulationResult,
        ]);
    }
}
