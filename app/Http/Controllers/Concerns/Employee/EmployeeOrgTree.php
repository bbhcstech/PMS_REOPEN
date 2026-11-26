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

        $payload = app(OrganizationTreeService::class)->buildPayload($viewer, $selectedCompanyId);
        $payload['nodes'] = array_map(static fn (array $node) => $node + [
            'profile_url' => route('employees.org-tree.profile', [
                'employee' => $node['id'],
                'company_id' => $node['company_id'] ?: null,
            ]),
        ], $payload['nodes']);

        return response()->json($payload);
    }

    public function orgTreeProfile(Request $request, int $employee): JsonResponse
    {
        $viewer = $request->user() ?? auth()->user();
        $companyId = TenantScope::companyFilter($request->integer('company_id') ?: null);

        return response()->json(app(OrganizationTreeService::class)->buildProfile($viewer, $companyId, $employee))
            ->header('Cache-Control', 'private, no-store');
    }

    private function orgTreeNodes($employees): array
    {
        return app(OrganizationTreeService::class)->buildNodes($employees);
    }
}
