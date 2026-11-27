<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\Employee\EmployeeInvitations;
use App\Http\Controllers\Concerns\Employee\EmployeeLifecycle;
use App\Http\Controllers\Concerns\Employee\EmployeeOnboarding;
use App\Http\Controllers\Concerns\Employee\EmployeeOrganizationFields;
use App\Http\Controllers\Concerns\Employee\EmployeeOrgTree;
use App\Http\Controllers\Concerns\Employee\EmployeeProfileUpdates;
use App\Models\Company;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeController extends Controller
{
    use EmployeeOnboarding;
    use EmployeeProfileUpdates;
    use EmployeeLifecycle;
    use EmployeeInvitations;
    use EmployeeOrganizationFields;
    use EmployeeOrgTree;

    /**
     * Simple role guard for employee-management methods.
     */
    protected function ensureAdmin()
    {
        $user = auth()->user();
        $role = $user ? strtolower((string) ($user->role ?? '')) : '';
        if (! $user || ! in_array($role, ['admin', 'hr', 'manager', 'administrator', 'superadmin'], true)) {
            abort(403, 'Unauthorized');
        }
    }

    private function employeeCompany(?int $requestedId = null): Company
    {
        $actor = auth()->user();
        $isSuperAdmin = strtolower((string) ($actor?->role ?? '')) === 'superadmin';
        $companyId = $isSuperAdmin
            ? ($requestedId ?: app(\App\Services\CompanyContext::class)->id())
            : (int) $actor?->company_id;

        abort_unless($companyId, 403, 'No company is assigned to this account.');
        abort_if(! $isSuperAdmin && $requestedId && $requestedId !== $companyId, 403, 'You can only manage employees for your company.');

        return Company::findOrFail($companyId);
    }

    /**
     * List employees with optional filters.
     *
     */
    public function index(Request $request)
    {
        $viewer = auth()->user();
        $selectedCompanyId = $request->integer('company_id') ?: null;
        if ($viewer && $viewer->normalizedRole() !== 'superadmin') {
            abort_unless($viewer->company_id, 403);
            abort_if($selectedCompanyId && $selectedCompanyId !== (int) $viewer->company_id, 403);
            $selectedCompanyId = (int) $viewer->company_id;
        }

        $query = User::with(['company', 'employeeDetail.designation', 'employeeDetail.department', 'employeeDetail.parentDepartment'])
            ->whereIn('role', ['employee', 'hr', 'manager'])
            ->whereNull('archived_at');

        $viewerRole = $viewer?->normalizedRole();
        $isGlobalAdmin = in_array($viewerRole, ['admin', 'superadmin', 'administrator'], true);
        $isAdminOrHr = in_array($viewerRole, ['admin', 'superadmin', 'administrator', 'hr'], true);

        if ($viewer && ! $isAdminOrHr && $viewer->company_id) {
            $query->where('users.company_id', $viewer->company_id);
        }

        if ($viewer && $isAdminOrHr && $selectedCompanyId) {
            $query->where('users.company_id', $selectedCompanyId);
        } elseif ($viewer && ! $isGlobalAdmin && $viewer->company_id) {
            $query->where('users.company_id', $viewer->company_id);
        }

        if ($viewer && ! $isAdminOrHr) {
            $query->whereIn('users.id', $viewer->visibleEmployeeIds());
        }

        if ($request->filled('employee_id')) {
            $query->whereHas('employeeDetail', function ($q) use ($request) {
                $q->where('employee_id', 'like', '%' . $request->employee_id . '%');
            });
        }

        if ($request->filled('designation_id')) {
            $query->whereHas('employeeDetail', function ($q) use ($request) {
                $q->where('designation_id', $request->designation_id);
            });
        }

        if ($request->filled('user_id')) {
            $query->where('id', $request->user_id);
        }

        // Exclude users whose employeeDetail indicates they are on notice or on probation.
        $query->whereDoesntHave('employeeDetail', function ($q) {
            $q->where(function ($sub) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('employee_details', 'status')) {
                    $sub->whereIn('status', ['notice', 'probation']);
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('employee_details', 'notice_end_date')) {
                    $sub->orWhereNotNull('notice_end_date');
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('employee_details', 'probation_end_date')) {
                    $sub->orWhereNotNull('probation_end_date');
                }
            });
        });

        // include subordinate count for reporting-to integrity UI
        $query->select(
            'users.*',
            DB::raw('(SELECT COUNT(*) FROM employee_details ed WHERE ed.reporting_to = users.id) AS subordinate_count')
        );

        // FIXED: Changed from get() to paginate() for pagination to work
        $employees = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        $companies = Company::where('status', 'active')
            ->when($selectedCompanyId, fn ($q) => $q->whereKey($selectedCompanyId))
            ->orderBy('name')
            ->get();

        $companyStats = $companies->map(function (Company $company) {
            $activeQuery = User::whereIn('role', ['employee', 'hr', 'manager'])
                ->whereNull('archived_at')
                ->where('company_id', $company->id);

            if (Schema::hasColumn('employee_details', 'status')) {
                $activeQuery->whereHas('employeeDetail', fn ($detail) => $detail->whereIn('status', ['Active', 'active']));
            } elseif (Schema::hasColumn('users', 'is_active')) {
                $activeQuery->where('is_active', true);
            } elseif (Schema::hasColumn('users', 'status')) {
                $activeQuery->whereIn('status', ['active', 'Active', 1]);
            }

            return [
                'company' => $company,
                'employees' => User::whereIn('role', ['employee', 'hr', 'manager'])
                    ->whereNull('archived_at')
                    ->where('company_id', $company->id)
                    ->count(),
                'active' => $activeQuery->count(),
            ];
        });

        // prepare dropdown list options but exclude notice/probation entries so selects don't show them
        $employeeDetails = EmployeeDetail::with(['user', 'reportingTo'])
            ->whereHas('user', function ($q) use ($selectedCompanyId) {
                $q->whereNull('archived_at');

                if ($selectedCompanyId) {
                    $q->where('company_id', $selectedCompanyId);
                }
            })
            ->get()
            ->filter(function ($d) {
                $status = $d->status ?? null;
                $hasNotice = !empty($d->notice_end_date);
                $hasProb = !empty($d->probation_end_date);

                if (in_array($status, ['notice', 'probation'])) return false;
                if ($hasNotice || $hasProb) return false;
                return true;
            });

        $pendingOnboardingCount = 0;
        try {
            $appTable = (new \App\Models\EmployeeOnboardingApplication)->getTable();
            $appConn = (new \App\Models\EmployeeOnboardingApplication)->getConnectionName() ?: 'tenant';
            if (Schema::connection($appConn)->hasTable($appTable)) {
                $pendingOnboardingCount = \App\Models\EmployeeOnboardingApplication::where('status', \App\Models\EmployeeOnboardingApplication::STATUS_PENDING_REVIEW)
                    ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
                    ->count();
            }
        } catch (\Throwable $e) {
            $pendingOnboardingCount = 0;
        }

        return view('admin.employees.index', [
            'employees' => $employees,
            'pendingOnboardingCount' => $pendingOnboardingCount,
            'companyEmployeeTotal' => $selectedCompanyId ? app(\App\Services\CompanyEmployeeDirectory::class)->count($selectedCompanyId) : $employees->count(),
            'companies' => $companies,
            'companyStats' => $companyStats,
            'selectedCompanyId' => $selectedCompanyId,
            'designations' => Designation::when($selectedCompanyId && Schema::hasColumn('designations', 'company_id'), fn ($query) => $query->where(function ($q) use ($selectedCompanyId) {
                    $q->where('company_id', $selectedCompanyId)->orWhereNull('company_id');
                }))
                ->orderBy('name')
                ->get(),
            'employee_data' => User::with('company')->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))->get(),
            'employeeDetails' => $employeeDetails,
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => route('dashboard')],
                ['title' => 'Employees', 'url' => route('employees.index')],
            ]
        ]);
    }

    /**
     * Show single employee.
     */
    public function show($id)
    {
        $employee = User::with([
            'employeeDetail.designation',
            'employeeDetail.department',
            'employeeDetail.reportingTo'
        ])->findOrFail($id);

        return view('admin.employees.show', compact('employee'));
    }

    private function guardEmployeeRole(Request $request, ?User $target = null): void
    {
        $actorRole = auth()->user()?->normalizedRole();
        if (! in_array($actorRole, ['admin', 'administrator', 'superadmin'], true)) {
            abort_if($target && ($target->role !== 'employee' || $target->company_staff_role_id), 403, 'Only a company admin can manage HR, manager and company-role accounts.');
            $requestedRole = $request->input('user_role', $request->input('role', 'employee'));
            abort_unless($requestedRole === 'employee', 403, 'Only a company admin can assign privileged roles.');
        }
    }
}
