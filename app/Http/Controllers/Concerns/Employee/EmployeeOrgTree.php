<?php

namespace App\Http\Controllers\Concerns\Employee;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Organization tree for the Employees page.
 *
 * Placement rules (so a newly added employee lands in the right branch automatically):
 *  1. The saved "Reporting To" manager, when that manager is part of the visible tree.
 *  2. Otherwise the closest superior by designation level (higher level number = more senior:
 *     L0 is the most junior level, L6 and above the most senior),
 *     preferring the same department, then the same parent department, then the company.
 *  3. Employees with no superior become roots; several roots hang under the company node.
 */
trait EmployeeOrgTree
{
    public function orgTree(Request $request): JsonResponse
    {
        $viewer = auth()->user();
        $selectedCompanyId = $request->integer('company_id') ?: null;
        if ($viewer && $viewer->normalizedRole() !== 'superadmin') {
            abort_unless($viewer->company_id, 403);
            abort_if($selectedCompanyId && $selectedCompanyId !== (int) $viewer->company_id, 403);
            $selectedCompanyId = (int) $viewer->company_id;
        }

        $viewerRole = $viewer?->normalizedRole();
        $isAdminOrHr = in_array($viewerRole, ['admin', 'superadmin', 'administrator', 'hr'], true);

        $query = User::with(['company', 'employeeDetail.designation', 'employeeDetail.department.parent', 'employeeDetail.reportingTo'])
            ->whereIn('role', ['employee', 'hr', 'manager'])
            ->whereNull('archived_at');

        if ($selectedCompanyId) {
            $query->where('users.company_id', $selectedCompanyId);
        } elseif ($viewer?->company_id) {
            $query->where('users.company_id', $viewer->company_id);
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
            $level = is_numeric($designation?->level) ? (int) $designation->level : null;
            $joining = $detail?->joining_date ? Carbon::parse($detail->joining_date) : null;

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
                'level' => $level,
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
                'reporting_to_id' => $detail?->reporting_to ? (int) $detail->reporting_to : null,
                'reporting_to_name' => $detail?->reportingTo?->name,
                'show_url' => route('employees.show', $employee->id),
                'edit_url' => route('employees.edit', $employee->id),
                'directs' => 0,
                'total' => 0,
            ];
        }

        foreach ($nodes as $id => $node) {
            $managerId = $node['reporting_to_id'];
            if ($managerId && $managerId !== $id && isset($nodes[$managerId])) {
                $nodes[$id]['parent'] = $managerId;
                $nodes[$id]['placement'] = 'reporting';
                continue;
            }
            $superior = $this->orgTreeClosestSuperior($node, $nodes);
            if ($superior) {
                $nodes[$id]['parent'] = $superior;
                $nodes[$id]['placement'] = 'level';
            }
        }

        // Break any reporting cycle (A reports to B, B reports to A) so the chart always renders.
        foreach (array_keys($nodes) as $id) {
            $seen = [$id => true];
            $current = $nodes[$id]['parent'];
            while ($current !== null) {
                if (isset($seen[$current])) {
                    $nodes[$id]['parent'] = null;
                    $nodes[$id]['placement'] = 'root';
                    break;
                }
                $seen[$current] = true;
                $current = $nodes[$current]['parent'] ?? null;
            }
        }

        foreach ($nodes as $id => $node) {
            if ($node['parent'] !== null) {
                $nodes[$node['parent']]['directs']++;
            }
            $current = $node['parent'];
            while ($current !== null) {
                $nodes[$current]['total']++;
                $current = $nodes[$current]['parent'];
            }
        }

        return $nodes;
    }

    /** Nearest employee one or more levels above (higher L number): same department, parent department, then company. */
    private function orgTreeClosestSuperior(array $node, array $nodes): ?int
    {
        // An employee without a designation level ranks below every leveled colleague.
        $ownLevel = $node['level'] ?? PHP_INT_MIN;
        $scopes = [
            fn ($candidate) => $node['department_id'] && $candidate['department_id'] === $node['department_id'],
            fn ($candidate) => $node['parent_department_id'] && $candidate['parent_department_id'] === $node['parent_department_id'],
            fn ($candidate) => true,
        ];

        foreach ($scopes as $inScope) {
            $best = null;
            foreach ($nodes as $candidate) {
                if ($candidate['id'] === $node['id'] || $candidate['level'] === null || $candidate['level'] <= $ownLevel || ! $inScope($candidate)) {
                    continue;
                }
                if ($best === null
                    || $candidate['level'] < $best['level']
                    || ($candidate['level'] === $best['level'] && $candidate['id'] < $best['id'])) {
                    $best = $candidate;
                }
            }
            if ($best) {
                return $best['id'];
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
