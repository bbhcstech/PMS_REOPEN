<?php

namespace App\Services;

use App\Models\{Company, CompanyStaffRole, User};
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Validation\ValidationException;

class CompanyStaffLogin
{
    public function authenticate(User $user, string $password, bool $remember = false): void
    {
        $company = Company::find($user->company_id);
        $role = CompanyStaffRole::where('company_id', $user->company_id)->find($user->company_staff_role_id);
        $valid = $company && $company->db_name === config('database.connections.tenant.database')
            && $role && in_array($role->access_role, ['hr', 'manager', 'employee'], true)
            && $user->role === $role->access_role && $user->is_active && $user->login_allowed
            && ! $user->archived_at && $user->canLogin();
        if (! $valid || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['email' => 'The credentials are incorrect or this account is unavailable.']);
        }
        session([
            'current_company_id' => $company->id,
            'current_company_db' => $company->db_name,
            'current_company_name' => $company->name,
        ]);
        app(CompanyContext::class)->reset($company);
        Auth::guard('web')->login($user, $remember);
    }
}
