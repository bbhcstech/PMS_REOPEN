<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrganizationDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $allEmployeesQuery = User::query()
            ->with([
                'company',
                'employeeDetail.designation',
                'employeeDetail.department.parent',
                'employeeDetail.reportingTo.employeeDetail.designation',
            ])
            ->where('role', 'employee')
            ->whereNull('archived_at');

        $employees = (clone $allEmployeesQuery)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhereHas('employeeDetail', function ($detail) use ($search) {
                            $detail->where('employee_id', 'like', '%' . $search . '%')
                                ->orWhere('skills', 'like', '%' . $search . '%')
                                ->orWhereHas('department', fn ($department) => $department->where('dpt_name', 'like', '%' . $search . '%'))
                                ->orWhereHas('designation', fn ($designation) => $designation->where('name', 'like', '%' . $search . '%'));
                        });
                });
            })
            ->when($request->filled('company_id'), function ($query) use ($request) {
                $query->where('company_id', $request->company_id);
            })
            ->when($request->filled('department_id'), function ($query) use ($request) {
                $query->whereHas('employeeDetail', fn ($detail) => $detail->where('department_id', $request->department_id));
            })
            ->when($request->filled('designation_id'), function ($query) use ($request) {
                $query->whereHas('employeeDetail', fn ($detail) => $detail->where('designation_id', $request->designation_id));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = strtolower($request->status);
                if (Schema::hasColumn('employee_details', 'status')) {
                    if ($status === 'active') {
                        $query->whereHas('employeeDetail', fn ($detail) => $detail->whereIn('status', ['Active', 'active']));
                    } elseif ($status === 'inactive') {
                        $query->whereHas('employeeDetail', fn ($detail) => $detail->whereIn('status', ['Inactive', 'inactive']));
                    } elseif ($status === 'on_leave') {
                        $query->whereHas('employeeDetail', fn ($detail) => $detail->where('status', 'on_leave'));
                    } elseif ($status === 'suspended') {
                        $query->whereHas('employeeDetail', fn ($detail) => $detail->whereIn('status', ['Suspended', 'suspended']));
                    }
                } elseif (Schema::hasColumn('users', 'is_active')) {
                    if ($status === 'active') {
                        $query->where('is_active', true);
                    } elseif ($status === 'inactive') {
                        $query->where('is_active', false);
                    }
                }
            })
            ->when($request->filled('reporting_to'), function ($query) use ($request) {
                $query->whereHas('employeeDetail', fn ($detail) => $detail->where('reporting_to', $request->reporting_to));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $totalEmployeesCount = (clone $allEmployeesQuery)->count();
        if (Schema::hasColumn('employee_details', 'status')) {
            $activeEmployeesCount = (clone $allEmployeesQuery)->whereHas('employeeDetail', fn ($q) => $q->whereIn('status', ['Active', 'active']))->count();
            $inactiveEmployeesCount = (clone $allEmployeesQuery)->whereHas('employeeDetail', fn ($q) => $q->whereIn('status', ['Inactive', 'inactive']))->count();
            $onLeaveEmployeesCount = (clone $allEmployeesQuery)->whereHas('employeeDetail', fn ($q) => $q->where('status', 'on_leave'))->count();
        } elseif (Schema::hasColumn('users', 'is_active')) {
            $activeEmployeesCount = (clone $allEmployeesQuery)->where('is_active', true)->count();
            $inactiveEmployeesCount = (clone $allEmployeesQuery)->where('is_active', false)->count();
            $onLeaveEmployeesCount = 0;
        } else {
            $activeEmployeesCount = $totalEmployeesCount;
            $inactiveEmployeesCount = 0;
            $onLeaveEmployeesCount = 0;
        }
        $managersCount = \App\Models\EmployeeDetail::whereNotNull('reporting_to')->distinct('reporting_to')->count('reporting_to');

        $stats = [
            'total' => $totalEmployeesCount,
            'employees' => $totalEmployeesCount,
            'active' => $activeEmployeesCount,
            'inactive' => $inactiveEmployeesCount,
            'on_leave' => $onLeaveEmployeesCount,
            'managers' => max(1, $managersCount),
            'departments' => Department::whereNull('archived_at')->count(),
            'designations' => Designation::whereNull('archived_at')->count(),
        ];

        $companies = \App\Models\Company::where('status', 'active')->orderBy('name')->get();
        $departments = Department::whereNull('archived_at')->orderBy('dpt_name')->get();
        $designations = Designation::whereNull('archived_at')->orderBy('name')->get();

        $managerUserIds = \App\Models\EmployeeDetail::whereNotNull('reporting_to')->pluck('reporting_to')->unique();
        $managers = User::whereIn('id', $managerUserIds)->orderBy('name')->get();

        $departmentGroups = Department::with(['employeeDetails.user', 'employeeDetails.designation'])
            ->whereNull('archived_at')
            ->whereHas('employeeDetails', function ($query) {
                $query->where('status', 'Active')->whereHas('user', fn ($user) => $user->whereNull('archived_at'));
            })
            ->orderBy('dpt_name')
            ->get();

        return view('admin.organization-directory.index', compact(
            'employees',
            'stats',
            'companies',
            'departments',
            'designations',
            'managers',
            'departmentGroups'
        ));
    }

    public function show(User $employee)
    {
        $employee->load([
            'employeeDetail.designation',
            'employeeDetail.department.parent',
            'employeeDetail.reportingTo.employeeDetail.designation',
        ]);

        abort_unless($employee->role === 'employee' && is_null($employee->archived_at), 404);
        abort_if(optional($employee->employeeDetail)->status === 'Inactive', 404);

        return view('admin.organization-directory.show', compact('employee'));
    }

    public function updateDirectoryProfile(Request $request, User $employee)
    {
        $this->ensureDirectoryManager();

        $employee->load('employeeDetail');

        abort_unless($employee->role === 'employee' && is_null($employee->archived_at), 404);
        abort_if(optional($employee->employeeDetail)->status === 'Inactive', 404);
        abort_unless($employee->employeeDetail, 404);

        $data = $request->validate([
            'directory_about' => ['nullable', 'string', 'max:3000'],
            'skills' => ['nullable', 'string', 'max:1500'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'x_url' => ['nullable', 'url', 'max:255'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:4096'],
        ]);

        unset($data['cv_file']);

        $cvPath = $this->storeEmployeeCv($request, $employee->employeeDetail->cv_path);
        if ($cvPath) {
            $data['cv_path'] = $cvPath;
        }

        $employee->employeeDetail->update($data);

        Log::info('Organization directory profile updated', [
            'employee_id' => $employee->id,
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('organization.show', $employee)
            ->with('success', 'Organization directory details updated successfully.');
    }

    private function employeeDirectoryQuery()
    {
        return User::query()
            ->with([
                'employeeDetail.designation',
                'employeeDetail.department.parent',
                'employeeDetail.reportingTo.employeeDetail.designation',
            ])
            ->where('role', 'employee')
            ->whereNull('archived_at')
            ->whereHas('employeeDetail', fn ($query) => $query->where('status', 'Active'));
    }

    private function ensureDirectoryManager(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'hr'], true), 403);
    }

    private function storeEmployeeCv(Request $request, ?string $oldPath = null): ?string
    {
        if (! $request->hasFile('cv_file')) {
            return null;
        }

        $file = $request->file('cv_file');
        $directory = public_path('admin/uploads/employee-cvs');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = uniqid('cv_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        if ($oldPath && file_exists(public_path($oldPath))) {
            @unlink(public_path($oldPath));
        }

        return 'admin/uploads/employee-cvs/' . $filename;
    }
}
