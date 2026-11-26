<?php

namespace App\Http\Controllers\Concerns\Employee;

use App\Services\OrganizationTreeService;
use App\Services\TenantScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Organization tree endpoint retained on EmployeeController for backward compatibility.
 * Core tree assembly lives in App\Services\OrganizationTreeService.
 */
trait EmployeeOrgTree
{
    public function orgTree(Request $request): JsonResponse
    {
        $viewer = $request->user() ?? auth()->user();
        $platformAdmin = TenantScope::isPlatformAdmin();
        abort_unless($viewer || $platformAdmin, 403);

        $selectedCompanyId = TenantScope::companyFilter($request->integer('company_id') ?: null);

        return response()->json(
            app(OrganizationTreeService::class)->buildPayload($viewer, $selectedCompanyId)
        );
    }

    private function orgTreeNodes($employees): array
    {
        return app(OrganizationTreeService::class)->buildNodes($employees);
    }
}
