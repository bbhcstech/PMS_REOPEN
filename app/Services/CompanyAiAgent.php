<?php

namespace App\Services;

use App\Models\Central\Company;
use Illuminate\Support\Facades\{Auth, DB};

class CompanyAiAgent
{
    public function identity(): array
    {
        $company = $this->company();
        $user = Auth::guard('web')->user();
        $profileName = $user->fresh()?->name ?? $user->name;
        return [
            'id' => 'company:' . $company->id . ':user:' . $user->id,
            'name' => trim((string) $company->name) . ' Assistant · ' . trim((string) $profileName),
        ];
    }
    public function company(): Company
    {
        $user = Auth::guard('web')->user();
        abort_unless($user && $user->company_id && !TenantScope::isPlatformAdmin(), 403);
        $id = TenantScope::companyFilter();
        $company = Company::findOrFail($id);
        abort_unless((int) $user->company_id === (int) $company->id
            && (int) session('current_company_id') === (int) $company->id
            && $company->db_name === DB::connection('tenant')->getDatabaseName(), 403, 'Invalid company workspace.');
        abort_if($user->archived_at || !$user->is_active || !$user->login_allowed || !$user->canLogin(), 403);
        if ($user->company_staff_role_id) {
            $role = \App\Models\CompanyStaffRole::where('company_id', $company->id)->findOrFail($user->company_staff_role_id);
            abort_unless($role->access_role === $user->role, 403, 'Invalid company role.');
        }
        if (!data_get($company->settings, 'ai_agent.id')
            || data_get($company->settings, 'ai_agent.name') !== trim((string) $company->name) . ' Assistant') $company->save();
        return $company;
    }

    public function answer(string $question): array
    {
        $company = $this->company();
        $refusal = ['answer' => 'I can only help with company information you are allowed to access. I could not find information to answer this question.', 'sources' => []];
        $records = app(CompanyAiKnowledge::class)->retrieve($company, Auth::guard('web')->user(), $question);
        if (!$records) return $refusal;
        return app(GroundedAiResponse::class)->answer($question, $records, $this->identity()['name'], 'company');
    }
}
