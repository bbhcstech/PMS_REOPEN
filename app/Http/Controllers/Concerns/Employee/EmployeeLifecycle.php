<?php

namespace App\Http\Controllers\Concerns\Employee;

use App\Models\EmployeeDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait EmployeeLifecycle
{
    // ===========================================================
    // ===== REPORTING-TO INTEGRITY : subordinate check helper ===
    // ===========================================================

    private function hasSubordinates(int $userId): bool
    {
        return EmployeeDetail::where('reporting_to', $userId)->exists();
    }

    /**
     * Delete employee.
     */
    public function destroy($id)
    {
        $this->ensureAdmin();
        $this->guardManagedEmployees([$id]);

        // ======================================================
        // ===== REPORTING-TO INTEGRITY : block protected delete
        // ======================================================
        if ($this->hasSubordinates($id)) {
            return redirect()->route('employees.index')
                ->withErrors(['error' => 'You cannot delete this employee because other employees report to them.']);
        }

        $user = User::findOrFail($id);

        if (($user->role ?? '') !== 'employee') {
            return redirect()->route('employees.index')
                ->withErrors(['error' => 'Only employee records can be archived from this section.']);
        }

        if (($user->employeeDetail?->status ?? null) !== 'Inactive') {
            return redirect()->route('employees.index')
                ->withErrors(['error' => 'Only inactive employees can be archived. Please mark this employee inactive first.']);
        }

        $user->forceFill([
            'archived_at' => now(),
            'is_active' => false,
            'login_allowed' => false,
        ])->save();

        return redirect()->route('employees.index')
            ->with('success', 'Inactive employee archived successfully.');
    }

    /**
     * Mark one employee inactive (step 1 before a permanent delete). Exit date is required,
     * matching the rule used by the bulk status update.
     */
    public function markInactive(Request $request, $id)
    {
        $this->ensureAdmin();
        abort_unless(auth()->user()?->hasModulePermission('employees', 'edit'), 403, 'You do not have permission to change employee status.');
        $this->guardManagedEmployees([$id]);
        $data = $request->validate(['exit_date' => 'required|date|before_or_equal:today'], [
            'exit_date.required' => 'Select the exit date to mark this employee inactive.',
            'exit_date.before_or_equal' => 'The exit date cannot be in the future when marking an employee inactive now.',
        ]);

        $user = User::with('employeeDetail')->whereNull('archived_at')->findOrFail($id);
        abort_if((int) $user->id === (int) auth()->id(), 422, 'You cannot mark your own account inactive.');
        abort_unless($user->employeeDetail, 422, 'This employee has no employment record to update.');

        $user->employeeDetail->forceFill(['status' => 'Inactive', 'exit_date' => $data['exit_date']])->save();

        return redirect()->route('employees.index')
            ->with('success', $user->name . ' is now inactive. You can delete or archive this employee from the actions menu.');
    }

    /**
     * Permanently delete an employee. Allowed only after the employee is inactive, has no
     * direct reports, and the typed confirmation matches the employee ID (or name).
     */
    public function deletePermanently(Request $request, $id)
    {
        $this->ensureAdmin();
        $actor = auth()->user();
        abort_unless($actor?->hasModulePermission('employees', 'delete'), 403, 'You do not have permission to delete employees.');
        $this->guardManagedEmployees([$id]);

        $respond = function (string $type, string $message, int $status = 200) use ($request) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => $type, 'message' => $message], $status);
            }
            return $type === 'success'
                ? redirect()->route('employees.index')->with('success', $message)
                : redirect()->route('employees.index')->withErrors(['error' => $message]);
        };

        $user = User::with('employeeDetail')->findOrFail($id);
        $detail = $user->employeeDetail;
        if ((int) $user->id === (int) $actor->id) {
            return $respond('error', 'You cannot delete your own account.', 422);
        }
        if (($user->role ?? '') !== 'employee') {
            return $respond('error', 'Only employee records can be deleted here. Upper level accounts are managed in Add Upper Level Employee.', 422);
        }
        if (strcasecmp((string) ($detail?->status ?? ''), 'Inactive') !== 0) {
            return $respond('error', 'Mark ' . $user->name . ' inactive first. Only inactive employees can be deleted.', 422);
        }
        if ($this->hasSubordinates((int) $user->id)) {
            return $respond('error', 'You cannot delete ' . $user->name . ' because other employees report to them. Reassign their team first.', 422);
        }
        $expected = trim((string) ($detail?->employee_id ?: $user->name));
        if (strcasecmp(trim((string) $request->input('confirm_code')), $expected) !== 0) {
            return $respond('error', 'Type "' . $expected . '" exactly to confirm the permanent delete.', 422);
        }

        try {
            DB::connection($user->getConnectionName())->transaction(function () use ($user) {
                EmployeeDetail::where('user_id', $user->id)->delete();
                $user->delete();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (($e->errorInfo[0] ?? null) !== '23000') {
                throw $e;
            }
            return $respond('error', $user->name . ' cannot be deleted because other records (for example payroll, projects or approvals) still reference this employee. Archive the employee instead to keep that history.', 409);
        }

        Log::info('Employee permanently deleted', ['user_id' => $user->id, 'employee_id' => $detail?->employee_id, 'company_id' => $user->company_id, 'by' => $actor->id]);

        return $respond('success', $user->name . ' was deleted permanently.');
    }

    /**
     * Show archived employees.
     */
    public function archive(Request $request)
    {
        $this->ensureAdmin();

        $query = User::with(['employeeDetail.designation', 'employeeDetail.department'])
            ->where('role', 'employee')
            ->whereNotNull('archived_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhereHas('employeeDetail', function ($detailQuery) use ($search) {
                        $detailQuery->where('employee_id', 'like', '%' . $search . '%');
                    });
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 20, 30, 50, 100], true) ? $perPage : 15;

        $employees = $query->orderByDesc('archived_at')->paginate($perPage)->withQueryString();

        return view('admin.employees.archive', compact('employees'));
    }

    /**
     * Restore archived employee.
     */
    public function restore($id)
    {
        $this->ensureAdmin();
        $this->guardManagedEmployees([$id]);

        $employee = User::whereNotNull('archived_at')
            ->findOrFail($id);

        $employee->forceFill([
            'archived_at' => null,
        ])->save();

        return redirect()->route('employees.archive')
            ->with('success', 'Employee restored successfully.');
    }

    /**
     * Restore archived employees in bulk.
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:users,id',
        ]);
        $this->guardManagedEmployees($request->employee_ids);

        $restored = User::whereNotNull('archived_at')
            ->whereIn('id', $request->employee_ids)
            ->update(['archived_at' => null]);

        return response()->json([
            'success' => true,
            'message' => $restored . ' employee(s) restored successfully.',
            'restored' => $restored,
        ]);
    }

    /**
     * Bulk status update.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'employee_ids' => 'required|array',
            'status' => 'required|in:Active,Inactive',
        ]);
        $this->guardManagedEmployees($request->employee_ids);

        // If setting to Inactive, check for exit dates
        if ($request->status === 'Inactive') {
            $employeesWithoutExitDate = EmployeeDetail::whereIn('user_id', $request->employee_ids)
                ->whereNull('exit_date')
                ->get();

            if ($employeesWithoutExitDate->count() > 0) {
                return response()->json([
                    'message' => 'Some employees do not have an exit date. Exit date is required for Inactive status.',
                    'employees' => $employeesWithoutExitDate->pluck('employee_id')
                ], 422);
            }
        }

        if ($request->status === 'Active') {
            EmployeeDetail::whereIn('user_id', $request->employee_ids)
                ->update(['status' => 'Active', 'exit_date' => null]);
        } else {
            EmployeeDetail::whereIn('user_id', $request->employee_ids)
                ->update(['status' => $request->status]);
        }

        return response()->json(['message' => 'Updated successfully']);
    }

    /**
     * Bulk delete.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:users,id'
        ]);
        $this->guardManagedEmployees($request->employee_ids);

        DB::beginTransaction();
        try {

            // ==========================================================
            // ===== REPORTING-TO INTEGRITY : block protected deletions
            // ==========================================================
            $blocked = [];
            $allowed = [];

            foreach ($request->employee_ids as $id) {
                if ($this->hasSubordinates($id)) {
                    $blocked[] = $id;
                } else {
                    $allowed[] = $id;
                }
            }

            $inactiveIds = EmployeeDetail::whereIn('user_id', $allowed)
                ->where('status', 'Inactive')
                ->pluck('user_id')
                ->all();

            $activeOrInvalid = array_values(array_diff($allowed, $inactiveIds));

            if (count($activeOrInvalid) > 0) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Only inactive employees can be archived. Please remove active employees from the selection.',
                    'blocked_ids' => $activeOrInvalid,
                ], 422);
            }

            User::whereIn('id', $inactiveIds)
                ->where('role', 'employee')
                ->whereNull('archived_at')
                ->update([
                    'archived_at' => now(),
                    'is_active' => false,
                    'login_allowed' => false,
                ]);

            DB::commit();

            if (count($blocked) > 0) {
                return response()->json([
                    'message'     => 'Some employees cannot be deleted because they have subordinates.',
                    'blocked_ids' => $blocked
                ], 409);
            }

            return response()->json(['message' => 'Selected inactive employees archived successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk employee archive error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error archiving employees',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compute next employee ID.
     */
    private function guardManagedEmployees(array $ids): void
    {
        foreach (User::whereIn('id', $ids)->get() as $target) {
            abort_if(auth()->user()?->normalizedRole() !== 'superadmin' && (int) $target->company_id !== (int) auth()->user()?->company_id, 403);
            $this->guardEmployeeRole(request(), $target);
        }
    }

    /**
     * NEW METHOD: Auto-inactivate employees when exit date passes
     */
    public function checkExitDates()
    {
        $today = Carbon::today();

        $employeesToInactivate = EmployeeDetail::where('status', 'Active')
            ->whereNotNull('exit_date')
            ->whereDate('exit_date', '<=', $today)
            ->get();

        $count = 0;

        foreach ($employeesToInactivate as $employee) {
            $employee->status = 'Inactive';
            $employee->save();
            $count++;

            Log::info('Auto-inactivated employee based on exit date', [
                'employee_id' => $employee->employee_id,
                'user_id' => $employee->user_id,
                'exit_date' => $employee->exit_date
            ]);
        }

        return $count;
    }
}
