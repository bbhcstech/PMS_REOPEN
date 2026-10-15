<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OrganizationDirectoryController extends Controller
{
    public function deleteEmployees(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor && in_array($actor->normalizedRole(), ['admin', 'hr'], true)
            && $actor->hasModulePermission('employees', 'delete'), 403);
        $data = $request->validate([
            'employee_ids' => 'required|array|min:1|max:500',
            'employee_ids.*' => 'required|integer|distinct',
        ]);
        $companyId = (int) $actor->company_id;
        abort_unless($companyId > 0, 403);

        $count = \Illuminate\Support\Facades\DB::connection('tenant')->transaction(function () use ($data, $companyId, $actor) {
            $employees = User::where('company_id', $companyId)->where('role', 'employee')
                ->whereNull('archived_at')->whereIn('id', $data['employee_ids'])->lockForUpdate()->get();
            abort_unless($employees->count() === count($data['employee_ids'])
                && ! $employees->contains('id', $actor->id), 403, 'The selection contains employees you cannot delete.');

            // Reject the whole selection rather than deleting only some employees.
            if (\App\Models\EmployeeDetail::whereIn('reporting_to', $employees->modelKeys())->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'employee_ids' => 'Reassign reporting employees before deleting their manager. No employees were deleted.',
                ]);
            }
            User::whereIn('id', $employees->modelKeys())->where('company_id', $companyId)->update([
                'archived_at' => now(), 'is_active' => false, 'login_allowed' => false,
            ]);

            return $employees->count();
        });

        return response()->json(['message' => $count.' employee'.($count === 1 ? '' : 's').' deleted and moved to Archived Employees.']);
    }

    public function index(Request $request)
    {
        // Tenant isolation: company users only ever see their own company's directory.
        $scopeCompanyId = \App\Services\TenantScope::companyFilter($request->integer('company_id') ?: null);

        $allEmployeesQuery = User::query()
            ->when($scopeCompanyId, fn ($q) => $q->where('users.company_id', $scopeCompanyId))
            ->with([
                'company',
                'employeeDetail.designation',
                'employeeDetail.department.parent',
                'employeeDetail.reportingTo.employeeDetail.designation',
            ])
            ->where('role', 'employee')
            ->whereNull('archived_at');

        $employees = (clone $allEmployeesQuery)
            ->when($request->filled('search'), fn ($query) => $this->applyDirectorySearch($query, (string) $request->search))
            ->when($request->filled('company_id'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('company_id', $request->company_id)
                        ->orWhereHas('employeeDetail', fn ($detail) => $detail->where('company_id', $request->company_id))
                        ->orWhere(function ($subQ) {
                            $subQ->whereNull('company_id')
                                 ->whereDoesntHave('employeeDetail', fn ($d) => $d->whereNotNull('company_id'));
                        });
                });
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
            ->paginate((int) $request->input('per_page', 15))
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

        $companies = \App\Services\TenantScope::visibleCompanies();
        $departments = Department::whereNull('archived_at')
            ->when(Schema::hasColumn('departments', 'dpt_name'), fn ($q) => $q->orderBy('dpt_name'), fn ($q) => $q->orderBy('id'))
            ->get();
        $designations = Designation::whereNull('archived_at')->orderBy('name')->get();

        $managerUserIds = \App\Models\EmployeeDetail::whereNotNull('reporting_to')->pluck('reporting_to')->unique();
        $managers = User::whereIn('id', $managerUserIds)->orderBy('name')->get();

        $employeeDetailFilter = function ($detailQuery) use ($request, $scopeCompanyId) {
            $detailQuery->whereHas('user', function ($uQuery) use ($request, $scopeCompanyId) {
                $uQuery->where('role', 'employee')
                    ->whereNull('archived_at')
                    ->when($scopeCompanyId, fn ($q) => $q->where('users.company_id', $scopeCompanyId))
                    ->when($request->filled('search'), fn ($query) => $this->applyDirectorySearch($query, (string) $request->search))
                    ->when($request->filled('company_id'), function ($query) use ($request) {
                        $query->where(function ($q) use ($request) {
                            $q->where('company_id', $request->company_id)
                                ->orWhereHas('employeeDetail', fn ($detail) => $detail->where('company_id', $request->company_id))
                                ->orWhere(function ($subQ) {
                                    $subQ->whereNull('company_id')
                                         ->whereDoesntHave('employeeDetail', fn ($d) => $d->whereNotNull('company_id'));
                                });
                        });
                    });
            })
            ->when($request->filled('designation_id'), fn ($q) => $q->where('designation_id', $request->designation_id))
            ->when($request->filled('reporting_to'), fn ($q) => $q->where('reporting_to', $request->reporting_to))
            ->when($request->filled('status'), function ($q) use ($request) {
                $status = strtolower($request->status);
                if ($status === 'active') {
                    $q->whereIn('status', ['Active', 'active']);
                } elseif ($status === 'inactive') {
                    $q->whereIn('status', ['Inactive', 'inactive']);
                } elseif ($status === 'on_leave') {
                    $q->where('status', 'on_leave');
                } elseif ($status === 'suspended') {
                    $q->whereIn('status', ['Suspended', 'suspended']);
                }
            }, function ($q) {
                if (Schema::hasColumn('employee_details', 'status')) {
                    $q->whereIn('status', ['Active', 'active']);
                }
            });
        };

        $departmentGroups = Department::query()
            ->whereNull('archived_at')
            ->when($request->filled('department_id'), function ($q) use ($request) {
                $q->where('id', $request->department_id);
            })
            ->whereHas('employeeDetails', $employeeDetailFilter)
            ->with([
                'employeeDetails' => $employeeDetailFilter,
                'employeeDetails.user',
                'employeeDetails.designation',
                'parent'
            ])
            ->when(Schema::hasColumn('departments', 'dpt_name'), fn ($q) => $q->orderBy('dpt_name'), fn ($q) => $q->orderBy('id'))
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

    private function applyDirectorySearch(\Illuminate\Database\Eloquent\Builder $query, string $search): void
    {
        // Normalize explicitly for databases with case-sensitive collations as well.
        // Group alternatives so existing company and role filters still apply.
        $pattern = '%' . mb_strtolower(trim($search), 'UTF-8') . '%';

        $query->where(function ($q) use ($pattern) {
            $q->whereRaw('LOWER(users.name) LIKE ?', [$pattern])
                ->orWhereRaw('LOWER(users.email) LIKE ?', [$pattern])
                ->orWhereHas('employeeDetail', function ($detail) use ($pattern) {
                    $detail->whereRaw('LOWER(employee_details.employee_id) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(employee_details.skills) LIKE ?', [$pattern])
                        ->orWhereHas('department', fn ($department) => $department->whereRaw('LOWER(departments.dpt_name) LIKE ?', [$pattern]))
                        ->orWhereHas('designation', fn ($designation) => $designation->whereRaw('LOWER(designations.name) LIKE ?', [$pattern]));
                });
        });
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
        \App\Services\TenantScope::authorizeCompany($employee->company_id);

        return view('admin.organization-directory.show', compact('employee'));
    }

    public function updateDirectoryProfile(Request $request, User $employee)
    {
        $this->ensureDirectoryManager();

        $employee->load('employeeDetail');

        abort_unless($employee->role === 'employee' && is_null($employee->archived_at), 404);
        abort_if(optional($employee->employeeDetail)->status === 'Inactive', 404);
        \App\Services\TenantScope::authorizeCompany($employee->company_id);
        abort_unless($employee->employeeDetail, 404);

        $data = $request->validate([
            'directory_about' => ['nullable', 'string', 'max:3000'],
            'skills' => [
                'nullable',
                'string',
                'max:1500',
                function ($attribute, $value, $fail) {
                    if ($value && preg_match('/(SQLSTATE|Column not found|Unknown column|INSERT INTO|SELECT |syntax error)/i', $value)) {
                        $fail('Skills cannot contain database error messages or raw query strings.');
                    }
                },
            ],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'x_url' => ['nullable', 'url', 'max:255'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:4096'],
        ]);

        unset($data['cv_file']);

        if (array_key_exists('skills', $data)) {
            if ($data['skills'] !== null) {
                $cleanedSkills = array_filter(array_map('trim', explode(',', str_replace(['·', '|'], ',', $data['skills']))));
                $cleanedSkills = array_filter($cleanedSkills, fn($s) => strlen($s) > 0 && strlen($s) <= 60 && !preg_match('/(SQLSTATE|Column not found|INSERT INTO|SELECT |`)/i', $s));
                $data['skills'] = !empty($cleanedSkills) ? implode(', ', $cleanedSkills) : null;
            }
        }

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
        $role = strtolower((string) (auth()->user()?->role ?? ''));
        abort_unless(in_array($role, ['admin', 'hr', 'manager', 'administrator', 'superadmin'], true), 403);
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
