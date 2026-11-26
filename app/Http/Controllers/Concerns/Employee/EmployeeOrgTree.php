<?php

namespace App\Http\Controllers\Concerns\Employee;

use App\Models\Company;
use App\Models\User;
use App\Services\TenantScope;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Organization tree for the Employees page.
 *
 * Placement rules (so a newly added employee lands in the right branch automatically):
 *  1. The saved manager when visible in the same company and at a higher level.
 *  2. Otherwise the closest superior by designation level (L0 is the most junior),
 *     preferring the same department, then the same parent department, then the company.
 *  3. Employees with no superior become roots; several roots hang under the company node.
 */
trait EmployeeOrgTree
{
    public function orgTree(Request $request): JsonResponse
    {
        $viewer = $request->user();
        $platformAdmin = TenantScope::isPlatformAdmin();
        abort_unless($viewer || $platformAdmin, 403);
        $selectedCompanyId = TenantScope::companyFilter($request->integer('company_id') ?: null);
        $isAdminOrHr = $platformAdmin || in_array($viewer?->normalizedRole(), ['admin', 'hr'], true);

        $query = User::with([
            'company',
            // Designation management requires a company context. The tree checks ownership per employee.
            'employeeDetail.designation' => fn ($designation) => $designation->withoutGlobalScope('designation_company')->with('addedBy:id,company_id'),
            'employeeDetail.department.parent',
            'employeeDetail.reportingTo:id,name,company_id',
        ])->where(function ($staff) {
            $staff->whereIn('role', ['employee', 'hr', 'manager'])
                ->orWhere(fn ($leaders) => $leaders->whereIn('role', ['admin', 'administrator'])->whereHas('employeeDetail'));
        })
            ->whereNull('archived_at');

        if ($selectedCompanyId) {
            $query->where('users.company_id', $selectedCompanyId);
        }

        if ($viewer && ! $isAdminOrHr) {
            $query->whereIn('users.id', $viewer->visibleEmployeeIds());
        }

        // Same exclusions as the employee list (notice / probation records live in their own sections).
        $query->whereDoesntHave('employeeDetail', function ($q) {
            $q->where(function ($sub) {
                if (Schema::hasColumn('employee_details', 'status')) {
                    $sub->whereIn('status', ['notice', 'probation']);
                }
                if (Schema::hasColumn('employee_details', 'notice_end_date')) {
                    $sub->orWhereNotNull('notice_end_date');
                }
                if (Schema::hasColumn('employee_details', 'probation_end_date')) {
                    $sub->orWhereNotNull('probation_end_date');
                }
            });
        });

        $employees = $query->orderBy('users.id')->get();
        $nodes = $this->orgTreeNodes($employees);

        $company = $selectedCompanyId ? Company::find($selectedCompanyId) : $viewer?->company;

        return response()->json([
            'company' => [
                'name' => $company?->name ?? 'Organization',
                'logo' => $company?->logo ? asset($company->logo) : null,
            ],
            'nodes' => array_values($nodes),
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /** Builds flat node records with resolved parent ids, direct and total report counts. */
    private function orgTreeNodes($employees): array
    {
        $nodes = [];
        foreach ($employees as $employee) {
            $detail = $employee->employeeDetail;
            $designation = $detail?->designation;
            $department = $detail?->department;
            $companyId = (int) $employee->company_id;
            if ($designation && $companyId && (int) ($designation->company_id ?? $designation->addedBy?->company_id) !== $companyId) {
                $designation = null;
            }
            $level = filter_var($designation?->level, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $joining = $detail?->joining_date ? Carbon::parse($detail->joining_date) : null;
            $manager = $detail?->reportingTo;

            $nodes[(int) $employee->id] = [
                'id' => (int) $employee->id,
                'parent' => null,
                'placement' => 'root',
                'name' => (string) $employee->name,
                'email' => (string) $employee->email,
                'photo' => ! empty($employee->profile_image) ? asset($employee->profile_image) : null,
                'initials' => strtoupper(mb_substr(trim((string) $employee->name) ?: 'E', 0, 2)),
                'employee_code' => $detail?->employee_id,
                'designation' => $designation?->name,
                'level' => $level === false ? null : $level,
                'department_id' => $department?->id ? (int) $department->id : null,
                'department' => $department?->name ?: null,
                'parent_department_id' => $department?->parent_dpt_id ? (int) $department->parent_dpt_id : ($detail?->parent_dpt_id ? (int) $detail->parent_dpt_id : null),
                'parent_department' => $department?->parent?->dpt_name ?: null,
                'role' => ucfirst((string) ($employee->role ?? 'employee')),
                'status' => $detail?->status ?: 'Active',
                'mobile' => $detail?->mobile,
                'gender' => $detail?->gender ? ucfirst((string) $detail->gender) : null,
                'country' => $detail?->country,
                'address' => $detail?->address,
                'employment_type' => $detail?->employment_type ? ucwords(str_replace(['_', '-'], ' ', (string) $detail->employment_type)) : null,
                'joining_date' => $joining?->format('d M Y'),
                'tenure' => $joining ? $this->orgTreeTenure($joining) : null,
                'skills' => $detail?->skills,
                'company' => $employee->company?->name,
                'company_id' => $companyId,
                'reporting_to_id' => $detail?->reporting_to ? (int) $detail->reporting_to : null,
                'reporting_to_name' => $manager && (int) $manager->company_id === $companyId ? $manager->name : null,
                'show_url' => route('employees.show', $employee->id),
                'edit_url' => route('employees.edit', $employee->id),
                'directs' => 0,
                'total' => 0,
            ];
        }

        $ordered = array_values($nodes);
        usort($ordered, fn ($a, $b) => [$a['level'] ?? -1, $a['id']] <=> [$b['level'] ?? -1, $b['id']]);
        $groups = [];
        foreach ($ordered as $node) {
            if ($node['level'] !== null) {
                foreach ($this->orgTreeGroups($node) as $key) {
                    $groups[$key][] = $node;
                }
            }
        }

        foreach ($ordered as $node) {
            $id = $node['id'];
            $managerId = $node['reporting_to_id'];
            $manager = $nodes[$managerId] ?? null;
            if ($manager && $manager['company_id'] === $node['company_id'] && ($manager['level'] ?? -1) > ($node['level'] ?? -1)) {
                $nodes[$id]['parent'] = $managerId;
                $nodes[$id]['placement'] = 'reporting';
                continue;
            }
            $superior = $this->orgTreeClosestSuperior($node, $groups);
            if ($superior) {
                $nodes[$id]['parent'] = $superior;
                $nodes[$id]['placement'] = 'level';
            }
        }

        // Edges point strictly up, so each employee's subtree is counted before its parent.
        foreach ($ordered as $node) {
            $id = $node['id'];
            $parent = $nodes[$id]['parent'];
            if ($parent !== null) {
                $nodes[$parent]['directs']++;
                $nodes[$parent]['total'] += 1 + $nodes[$id]['total'];
            }
        }

        return $nodes;
    }

    private function orgTreeGroups(array $node): array
    {
        $company = (string) $node['company_id'];
        $keys = [];
        if ($node['department_id']) {
            $keys[] = $company.':department:'.$node['department_id'];
        }
        if ($node['parent_department_id']) {
            $keys[] = $company.':division:'.$node['parent_department_id'];
        }
        $keys[] = $company;

        return $keys;
    }

    private function orgTreeClosestSuperior(array $node, array $groups): ?int
    {
        $level = $node['level'] ?? -1;
        foreach ($this->orgTreeGroups($node) as $key) {
            $candidates = $groups[$key] ?? [];
            $start = 0;
            $end = count($candidates);
            while ($start < $end) {
                $middle = intdiv($start + $end, 2);
                if ($candidates[$middle]['level'] <= $level) {
                    $start = $middle + 1;
                } else {
                    $end = $middle;
                }
            }
            if (isset($candidates[$start])) {
                return $candidates[$start]['id'];
            }
        }

        return null;
    }

    private function orgTreeTenure(Carbon $joining): string
    {
        if ($joining->isFuture()) {
            return 'Joins '.$joining->format('d M Y');
        }
        $diff = $joining->diff(now());
        $parts = [];
        if ($diff->y) $parts[] = $diff->y.' yr'.($diff->y > 1 ? 's' : '');
        if ($diff->m) $parts[] = $diff->m.' mo';
        if (! $parts) $parts[] = max(1, $diff->d).' day'.($diff->d > 1 ? 's' : '');

        return implode(' ', $parts);
    }
}
