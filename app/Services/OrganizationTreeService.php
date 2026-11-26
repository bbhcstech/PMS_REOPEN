<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Shared service for fetching active Employee section records and building
 * a cycle-safe, company-isolated, designation-aware hierarchical organization tree.
 */
class OrganizationTreeService
{
    public function fetchEmployees(?User $viewer, ?int $selectedCompanyId = null): Collection
    {
        $platformAdmin = TenantScope::isPlatformAdmin();
        abort_unless($viewer || $platformAdmin, 403);

        if ($selectedCompanyId === null) {
            $selectedCompanyId = TenantScope::companyFilter(null);
        } elseif (! $platformAdmin && $viewer?->normalizedRole() !== 'superadmin') {
            abort_unless($viewer?->company_id, 403);
            abort_if($selectedCompanyId !== (int) $viewer->company_id, 403);
            $selectedCompanyId = (int) $viewer->company_id;
        }

        $isAdminOrHr = $platformAdmin || in_array($viewer?->normalizedRole(), ['admin', 'superadmin', 'administrator', 'hr'], true);

        $query = User::with([
            'company',
            'employeeDetail.designation' => fn ($designation) => $designation->withoutGlobalScope('designation_company')->with('addedBy:id,company_id'),
            'employeeDetail.department.parent',
            'employeeDetail.reportingTo:id,name,company_id',
        ])
            ->where(function ($staff) {
                $staff->whereIn('role', ['employee', 'hr', 'manager'])
                    ->orWhere(fn ($leaders) => $leaders->whereIn('role', ['admin', 'administrator'])->whereHas('employeeDetail'));
            })
            ->whereNull('archived_at');

        if ($selectedCompanyId) {
            $query->where('users.company_id', $selectedCompanyId);
        } elseif ($viewer?->company_id && ! $platformAdmin) {
            $query->where('users.company_id', $viewer->company_id);
        }

        if ($viewer && ! $isAdminOrHr) {
            $query->whereIn('users.id', $viewer->visibleEmployeeIds());
        }

        // Keep parity with Employee section: exclude notice/probation records using a single schema lookup.
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
     * Build flat node records with resolved parent IDs, company/level isolation, and direct/total counts.
     */
    public function buildNodes(iterable $employees): array
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
                'designation_id' => $designation?->id ? (int) $designation->id : null,
                'designation' => $designation?->name,
                'level' => $level === false ? null : $level,
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
                foreach ($this->treeGroups($node) as $key) {
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
            $superior = $this->closestSuperior($node, $groups);
            if ($superior) {
                $nodes[$id]['parent'] = $superior;
                $nodes[$id]['placement'] = 'level';
            }
        }

        // Edges point strictly upward (lower level -> higher level), so child totals resolve before parent totals.
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

    private function treeGroups(array $node): array
    {
        $company = (string) $node['company_id'];
        $keys = [];
        if ($node['department_id']) {
            $keys[] = $company . ':department:' . $node['department_id'];
        }
        if ($node['parent_department_id']) {
            $keys[] = $company . ':division:' . $node['parent_department_id'];
        }
        $keys[] = $company;

        return $keys;
    }

    /** Nearest employee one or more levels above within the same company (department, division, then company). */
    private function closestSuperior(array $node, array $groups): ?int
    {
        $level = $node['level'] ?? -1;
        foreach ($this->treeGroups($node) as $key) {
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

    private function formatTenure(Carbon $joining): string
    {
        if ($joining->isFuture()) {
            return 'Starts ' . $joining->format('d M Y');
        }
        $years = (int) $joining->diffInYears(now());
        $months = (int) $joining->copy()->addYears($years)->diffInMonths(now());
        if ($years > 0) {
            return $years . ' yr' . ($years === 1 ? '' : 's') . ($months ? ' ' . $months . ' mo' : '');
        }
        if ($months > 0) {
            return $months . ' mo' . ($months === 1 ? '' : 's');
        }
        $days = (int) $joining->diffInDays(now());

        return $days . ' day' . ($days === 1 ? '' : 's');
    }
}
