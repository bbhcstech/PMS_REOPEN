<?php
namespace App\Http\Controllers\SuperAdmin;
use App\Http\Controllers\Controller;
use App\Models\Central\Company;
use App\Services\{CompanySuspension, TenantScope};
class CompanySuspensionController extends Controller
{
    public function index() {
        abort_unless(TenantScope::isPlatformAdmin(), 403);
        $companies = Company::where(fn ($q) => $q->where('manually_suspended', true)->orWhere('status', 'suspended'))
            ->orderByDesc('suspended_at')->paginate(20);
        return view('superadmin.companies.suspended', compact('companies'));
    }
    public function create(int $company) {
        abort_unless(TenantScope::isPlatformAdmin(), 403);
        $company = Company::findOrFail($company);
        return view('superadmin.companies.suspend', ['company' => $company, 'reasons' => CompanySuspension::REASONS]);
    }
}
