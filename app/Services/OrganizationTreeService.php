<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Shared service for fetching active Employee section records and building
 * a cycle-safe, designation-aware hierarchical organization tree.
 */
class OrganizationTreeService
{
    public function fetchEmployees(?User $viewer, ?int $selectedCompanyId = null): Collection
    {
        if ($viewer && $viewer->normalizedRole() !== 'superadmin') {
            abort_unless($viewer->company_id, 403);
            abort_if($selectedCompanyId && $selectedCompanyId !== (int) $viewer->company_id, 403);
            $selectedCompanyId = (int) $viewer->company_id;
        }

        $viewerRole = $viewer?->normalizedRole();
        $isAdminOrHr = in_array($viewerRole, ['admin', 'superadmin', 'administrator', 'hr'], true);

        $query = User::with([
            'company',
            'employeeDetail.designation',
            'employeeDetail.department.parent',
            'employeeDetail.reportingTo',
        ])
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

        // Keep parity with Employee section: exclude notice/probation records using a single schema lookup on the tenant connection.
        $connection = $query->getModel()->getConnectionName() ?: config('database.default');
        $detailColumns = array_flip(Schema::connection($connection)->getColumnListing('employee_details'));

        if (isset($detailColumns['status']) || isset($detailColumns['notice_end_date']) || isset($detailColumns['probation_end_date'])) {
            $query->whereDoesntHave('employeeDetail', function ($q) use ($detailColumns) {
                $q->where(function ($sub) use ($detailColumns) {
                    if (isset($detailColumns['status'])) {
                        $sub->whereIn('status', ['notice', 'probation']);
                    }
                    if (isset($detailColumns['notice_end_date'])) {
                        $sub->orWhereNotNull('notice_end_date');
                    }
                    if (isset($detailColumns['probation_end_date'])) {
                        $sub->orWhereNotNull('probation_end_date');
                    }
                });
            });
        }

        return $query->orderBy('users.id')->get();
    }

    public function buildPayload(?User $viewer, ?int $selectedCompanyId = null): array
    {
        $employees = $this->fetchEmployees($viewer, $selectedCompanyId);
        $nodes = $this->buildNodes($employees);

        $resolvedCompanyId = $selectedCompanyId ?: $viewer?->company_id;
        $company = $employees->first(fn ($emp) => $emp->company && (! $resolvedCompanyId || (int) $emp->company_id === (int) $resolvedCompanyId))?->company
            ?? ($resolvedCompanyId ? Company::find($resolvedCompanyId) : $viewer?->company);

        return [
            'company' => [
                'name' => $company?->display_name ?: ($company?->name ?? 'Organization'),
                'logo' => $company?->logo ? asset($company->logo) : null,
            ],
            'nodes' => array_values($nodes),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Build flat node records with resolved parent IDs, cycle protection, and direct/total counts.
     */
    public function buildNodes(iterable $employees): array
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
                'designation_id' => $designation?->id ? (int) $designation->id : null,
                'designation' => $designation?->name,
                'level' => $level,
                'department_id' => $department?->id ? (int) $department->id : null,
                'department' => $department?->name ?: null,
                'parent_department_id' => $department?->parent_dpt_id
                    ? (int) $department->parent_dpt_id
                    : ($detail?->parent_dpt_id ? (int) $detail->parent_dpt_id : null),
                'parent_department' => $department?->parent?->dpt_name ?: null,
                'role' => ucfirst((string) ($employee->role ?? 'employee')),
                'status' => $detail?->status ?: 'Active',
                'mobile' => $detail?->mobile,
                'gender' => $detail?->gender ? ucfirst((string) $detail->gender) : null,
                'country' => $detail?->country,
                'address' => $detail?->address,
                'employment_type' => $detail?->employment_type
                    ? ucwords(str_replace(['_', '-'], ' ', (string) $detail->employment_type))
                    : null,
                'joining_date' => $joining?->format('d M Y'),
                'tenure' => $joining ? $this->formatTenure($joining) : null,
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

            $superior = $this->closestSuperior($node, $nodes);
            if ($superior) {
                $nodes[$id]['parent'] = $superior;
                $nodes[$id]['placement'] = 'level';
            }
        }

        // Break reporting cycles (e.g., A reports to B, B reports to A) so tree traversal always terminates.
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

        foreach ($nodes as $node) {
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
    private function closestSuperior(array $node, array $nodes): ?int
    {
        $ownLevel = $node['level'] ?? PHP_INT_MIN;
        $scopes = [
            fn ($candidate) => $node['department_id'] && $candidate['department_id'] === $node['department_id'],
            fn ($candidate) => $node['parent_department_id'] && $candidate['parent_department_id'] === $node['parent_department_id'],
            fn ($candidate) => true,
        ];

        foreach ($scopes as $inScope) {
            $best = null;
            foreach ($nodes as $candidate) {
                if ($candidate['id'] === $node['id']
                    || $candidate['level'] === null
                    || $candidate['level'] <= $ownLevel
                    || ! $inScope($candidate)) {
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

    private function formatTenure(Carbon $joining): string
    {
        $now = now();
        if ($joining->isFuture()) {
            return 'Starts ' . $joining->format('d M Y');
        }
        $years = (int) $joining->diffInYears($now);
        $months = (int) $joining->copy()->addYears($years)->diffInMonths($now);
        if ($years > 0) {
            return $years . ' yr' . ($years === 1 ? '' : 's') . ($months ? ' ' . $months . ' mo' : '');
        }
        if ($months > 0) {
            return $months . ' mo' . ($months === 1 ? '' : 's');
        }
        $days = (int) $joining->diffInDays($now);

        return $days . ' day' . ($days === 1 ? '' : 's');
    }
}
