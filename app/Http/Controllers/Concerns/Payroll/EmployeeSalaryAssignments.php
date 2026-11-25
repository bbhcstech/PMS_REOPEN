<?php

namespace App\Http\Controllers\Concerns\Payroll;

use App\Models\Designation;
use App\Models\EmployeeSalaryAssignment;
use App\Models\Payroll;
use App\Models\SalaryStructure;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

trait EmployeeSalaryAssignments
{
    /**
     * 3. Employee Salary Assignment
     */
    public function employeeSalary(Request $request)
    {
        $this->authorizePayroll('payroll');
        $companyId = $this->selectedCompanyId($request);

        $query = EmployeeSalaryAssignment::with([
                'user.employeeDetail.department',
                'user.employeeDetail.designation',
                'designation',
                'salaryStructure',
            ])
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'LIKE', "%{$search}%")
                   ->orWhere('email', 'LIKE', "%{$search}%")
                   ->orWhereHas('employeeDetail', fn ($ed) => $ed->where('employee_id', 'LIKE', "%{$search}%"));
            });
        }

        if ($request->filled('department_id') && $request->input('department_id') !== 'all') {
            $query->whereHas('user.employeeDetail', fn ($ed) => $ed->where('department_id', $request->input('department_id')));
        }

        if ($request->filled('designation_id') && $request->input('designation_id') !== 'all') {
            $query->where('designation_id', $request->input('designation_id'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $assignments = $query->orderBy('id', 'desc')->paginate(15);

        // Every staff member from the Employee section (employees, HR, managers, company roles) with their salary status.
        $staff = $this->payrollStaffQuery($companyId)
            ->with(['employeeDetail.designation', 'employeeDetail.department'])
            ->orderBy('name')
            ->get();
        $currentAssignments = EmployeeSalaryAssignment::whereIn('user_id', $staff->pluck('id'))
            ->where('status', 'active')
            ->orderByDesc('effective_from')->orderByDesc('id')
            ->get()->unique('user_id')->keyBy('user_id');
        $roster = $staff->filter(function (User $member) use ($request, $currentAssignments) {
            $detail = $member->employeeDetail;
            if ($request->filled('search')) {
                $needle = mb_strtolower($request->input('search'));
                $haystack = mb_strtolower($member->name . ' ' . $member->email . ' ' . ($detail?->employee_id ?? ''));
                if (! str_contains($haystack, $needle)) return false;
            }
            if ($request->filled('department_id') && $request->input('department_id') !== 'all' && (string) $detail?->department_id !== (string) $request->input('department_id')) return false;
            if ($request->filled('designation_id') && $request->input('designation_id') !== 'all' && (string) $detail?->designation_id !== (string) $request->input('designation_id')) return false;
            $salary = $request->input('salary', 'all');
            if ($salary === 'assigned' && ! $currentAssignments->has($member->id)) return false;
            if ($salary === 'unassigned' && $currentAssignments->has($member->id)) return false;
            return true;
        })->values();
        $rosterSummary = [
            'total' => $staff->count(),
            'assigned' => $staff->filter(fn ($m) => $currentAssignments->has($m->id))->count(),
        ];
        $rosterSummary['unassigned'] = $rosterSummary['total'] - $rosterSummary['assigned'];
        // HR manages everyone's salary except their own, which only the company Admin sets.
        $lockedUserId = $this->ownSalaryLocked(auth()->id()) ? (int) auth()->id() : null;
        $employees = $staff->reject(fn ($member) => $lockedUserId && (int) $member->id === $lockedUserId)->values();

        $designations = Designation::orderBy('name')->get();
        $departments = $this->getDepartments();
        $structures = SalaryStructure::where('status', 'active')->orderBy('name')->get();

        return view('admin.payroll.employee-salary', compact(
            'assignments',
            'employees',
            'roster',
            'rosterSummary',
            'currentAssignments',
            'lockedUserId',
            'designations',
            'departments',
            'structures'
        ));
    }

    public function storeEmployeeSalary(Request $request)
    {
        $this->authorizePayroll('payroll', 'create');

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'grade' => ['nullable', 'string', 'max:50'],
            'salary_structure_id' => ['nullable', 'exists:salary_structures,id'],
            'actual_basic_salary' => ['required', 'numeric', 'min:0'],
            'hra_type' => ['required', 'in:percentage,fixed'],
            'hra_value' => ['required', 'numeric', 'min:0'],
            'special_allowance_type' => ['required', 'in:percentage,fixed'],
            'special_allowance_value' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'in:active,inactive'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->ownSalaryLocked((int) $data['user_id'])) {
            return back()->with('error', 'Your own salary is set by the company Admin. You can manage salaries of every other employee.');
        }

        $user = User::with('employeeDetail')->find($data['user_id']);
        $designationId = ($data['designation_id'] ?? null) ?: ($user?->employeeDetail?->designation_id);
        $grade = ($data['grade'] ?? null) ?:($user?->employeeDetail?->designation?->level ?: 'Standard');

        $payload = array_merge($data, [
            'company_id' => $user?->company_id ?: auth()->user()?->company_id,
            'designation_id' => $designationId,
            'grade' => $grade,
        ]);

        // If newly active, inactivate older overlapping assignment for the same employee
        if ($data['status'] === 'active') {
            EmployeeSalaryAssignment::where('user_id', $data['user_id'])
                ->where('status', 'active')
                ->update(['effective_to' => Carbon::parse($data['effective_from'])->subDay()->format('Y-m-d')]);
        }

        $assignment = EmployeeSalaryAssignment::create($payload);

        $this->audit('created_employee_salary_assignment', $assignment, null, $assignment->toArray(), $request);

        return back()->with('success', 'Employee salary assigned successfully.');
    }

    public function updateEmployeeSalary(Request $request, EmployeeSalaryAssignment $assignment)
    {
        $this->authorizePayroll('payroll', 'edit');

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'grade' => ['nullable', 'string', 'max:50'],
            'salary_structure_id' => ['nullable', 'exists:salary_structures,id'],
            'actual_basic_salary' => ['required', 'numeric', 'min:0'],
            'hra_type' => ['required', 'in:percentage,fixed'],
            'hra_value' => ['required', 'numeric', 'min:0'],
            'special_allowance_type' => ['required', 'in:percentage,fixed'],
            'special_allowance_value' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'in:active,inactive'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $this->authorizeAssignmentCompany($request, $assignment);
        if ($this->ownSalaryLocked((int) $assignment->user_id) || $this->ownSalaryLocked((int) $data['user_id'])) {
            return back()->with('error', 'Your own salary is set by the company Admin. You can manage salaries of every other employee.');
        }

        $old = $assignment->toArray();
        $assignment->update($data);

        $this->audit('updated_employee_salary_assignment', $assignment, $old, $assignment->fresh()->toArray(), $request);

        return back()->with('success', 'Employee salary assignment updated successfully.');
    }

    public function destroyEmployeeSalary(Request $request, EmployeeSalaryAssignment $assignment)
    {
        $this->authorizePayroll('payroll', 'delete');
        $this->authorizeAssignmentCompany($request, $assignment);
        if ($this->ownSalaryLocked((int) $assignment->user_id)) {
            return back()->with('error', 'Your own salary is set by the company Admin. You can manage salaries of every other employee.');
        }

        $old = $assignment->toArray();
        $assignment->delete();

        $this->audit('deleted_employee_salary_assignment', $assignment, $old, null, $request);

        return back()->with('success', 'Employee salary assignment removed successfully.');
    }

    /**
     * Strict Authorization: Admin Workspace ONLY
     */
    /**
     * Staff whose salary is managed in Payroll: every active member of the Employee section
     * (employees, HR, managers and company roles), never the company Admin or platform accounts.
     */
    private function payrollStaffQuery(?int $companyId)
    {
        $schema = Schema::connection((new User)->getConnectionName() ?: config('database.default'));
        $query = User::query()
            ->where(fn ($q) => $q->whereNull('role')->orWhereNotIn('role', ['admin', 'administrator', 'superadmin', 'super_admin', 'client']))
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId));
        if ($schema->hasColumn('users', 'archived_at')) {
            $query->whereNull('archived_at');
        }
        return $query;
    }

    /** HR (and any other non-admin payroll user) may not set or change their own salary. */
    private function ownSalaryLocked(?int $userId): bool
    {
        $actor = auth()->user();
        return $actor && $userId && (int) $actor->id === (int) $userId
            && ! in_array($actor->normalizedRole(), ['admin', 'superadmin'], true);
    }

    private function authorizeAssignmentCompany(Request $request, EmployeeSalaryAssignment $assignment): void
    {
        $companyId = $this->selectedCompanyId($request);
        abort_if($companyId && $assignment->company_id && (int) $assignment->company_id !== (int) $companyId, 403, 'This salary assignment belongs to another company.');
    }
}
