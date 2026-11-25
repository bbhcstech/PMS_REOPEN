<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CompanyContext
{
    private $company = null;

    public function current()
    {
        if ($this->company) {
            return $this->company;
        }

        $isSuperAdmin = TenantScope::isPlatformAdmin();

        // 1. If Super Admin is actively impersonating a company via session
        if ($isSuperAdmin) {
            if (session('current_company_id')) {
                try {
                    $comp = \App\Models\Central\Company::on('central')->find(session('current_company_id'))
                        ?? Company::find(session('current_company_id'));
                    if ($comp) {
                        return $this->company = $comp;
                    }
                } catch (\Throwable $e) {}
            }

            if (session('current_company_db')) {
                try {
                    $comp = \App\Models\Central\Company::on('central')->where('db_name', session('current_company_db'))->first()
                        ?? Company::where('db_name', session('current_company_db'))->first();
                    if ($comp) {
                        return $this->company = $comp;
                    }
                } catch (\Throwable $e) {}
            }
        }

        // 2. Non-Super Admin: resolve company using session (primary) then company_id (secondary).
        //    Do NOT use email or active-DB fallbacks — those can silently resolve the wrong company
        //    when multiple companies share similar admin emails or when the tenant DB hasn't been
        //    switched yet at the time this method is called.
        $user = Auth::guard('web')->user() ?? Auth::user();

        if ($user instanceof User) {
            $ownId = \App\Services\TenantScope::companyId();
            if ($user->relationLoaded('company') && $user->company) {
                abort_unless((int) $user->company->id === (int) $ownId, 403);
                return $this->company = $user->company;
            }

            // Trust the session written at login time — it is always correct.
            if (session('current_company_id')) {
                try {
                    $comp = \App\Models\Central\Company::on('central')->find(session('current_company_id'));
                    if ($comp) {
                        return $this->company = $comp;
                    }
                } catch (\Throwable $e) {}
            }

            // Fallback: company_id on the user (reliable only after SetTenantConnection
            // has pointed the connection at the right DB and the user has been reloaded)
            if (!empty($user->company_id)) {
                try {
                    $comp = \App\Models\Central\Company::on('central')->find($user->company_id);
                    if ($comp) {
                        return $this->company = $comp;
                    }
                } catch (\Throwable $e) {}
            }
        }

        // 3. For guest/unauthenticated requests: never default to Company 1.
        return null;
    }

    public function id(): ?int
    {
        return $this->current()?->id;
    }

    public function name(): string
    {
        return $this->current()?->display_name ?? 'ERP';
    }

    public function logoUrl(): ?string
    {
        return method_exists($this->current(), 'logoUrl') ? $this->current()?->logoUrl() : null;
    }

    public function faviconUrl(): ?string
    {
        return method_exists($this->current(), 'faviconUrl') ? $this->current()?->faviconUrl() : null;
    }

    public function prefix(string $type = 'employee'): string
    {
        $company = $this->current();

        return match ($type) {
            'leave' => $company?->leave_prefix ?: 'LV',
            'payroll' => $company?->payroll_prefix ?: 'PR',
            'payslip' => $company?->payslip_prefix ?: 'PS',
            default => $company?->employee_id_prefix ?: 'EMP',
        };
    }

    public function greeting(): string
    {
        return $this->current()?->greeting_message ?: 'Welcome to';
    }

    public function reset($company = null): void
    {
        $this->company = $company;
    }
}
