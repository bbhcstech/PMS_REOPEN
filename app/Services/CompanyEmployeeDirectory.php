<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/** The company employee register, independent of who created or manages each account. */
class CompanyEmployeeDirectory
{
    public function query(?int $companyId = null): Builder
    {
        $companyId = TenantScope::companyFilter($companyId);

        $query = User::query()
            ->where('company_id', $companyId ?: 0)
            ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(role)'), ['employee', 'hr', 'manager']);
        if ($query->getModel()->getConnection()->getSchemaBuilder()->hasColumn('users', 'archived_at')) {
            $query->whereNull('archived_at');
        }
        return $query;
    }

    public function count(?int $companyId = null): int
    {
        return $this->query($companyId)->count();
    }
}
