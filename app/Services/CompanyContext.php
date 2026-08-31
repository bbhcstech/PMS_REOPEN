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

        // 1. Respect active impersonated company ID from session
        if (session('current_company_id')) {
            $comp = Company::find(session('current_company_id'))
                ?? \App\Models\Central\Company::on('central')->find(session('current_company_id'));
            if ($comp) {
                return $this->company = $comp;
            }
        }

        // 2. Respect active impersonated company DB from session
        if (session('current_company_db')) {
            $comp = Company::where('db_name', session('current_company_db'))->first()
                ?? \App\Models\Central\Company::on('central')->where('db_name', session('current_company_db'))->first();
            if ($comp) {
                return $this->company = $comp;
            }
        }

        $user = Auth::user();

        if ($user instanceof User && $user->relationLoaded('company') && $user->company) {
            return $this->company = $user->company;
        }

        if ($user instanceof User && $user->company_id) {
            $comp = Company::find($user->company_id);
            if ($comp) {
                return $this->company = $comp;
            }
        }

        return $this->company = Company::where('status', 'active')
            ->orderBy('id')
            ->first();
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
