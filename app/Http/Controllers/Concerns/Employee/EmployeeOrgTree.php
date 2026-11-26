<?php

namespace App\Http\Controllers\Concerns\Employee;

use App\Services\OrganizationTreeService;
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
        $viewer = auth()->user();
        $selectedCompanyId = $request->integer('company_id') ?: null;
        if ($viewer && $viewer->normalizedRole() !== 'superadmin') {
            abort_unless($viewer->company_id, 403);
            abort_if($selectedCompanyId && $selectedCompanyId !== (int) $viewer->company_id, 403);
            $selectedCompanyId = (int) $viewer->company_id;
        }

        return response()->json(
            app(OrganizationTreeService::class)->buildPayload($viewer, $selectedCompanyId)
        );
    }

    private function orgTreeNodes($employees): array
    {
        return app(OrganizationTreeService::class)->buildNodes($employees);
    }
}
