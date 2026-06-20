<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CompanyContext
{
    private ?Company $company = null;

    public function current(): ?Company
    {
        if ($this->company) {
            return $this->company;
        }

        $user = Auth::user();

        if ($user instanceof User && $user->relationLoaded('company') && $user->company) {
            return $this->company = $user->company;
        }

        if ($user instanceof User && $user->company_id) {
            return $this->company = Company::find($user->company_id);
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
        return $this->current()?->logoUrl();
    }

    public function faviconUrl(): ?string
    {
        return $this->current()?->faviconUrl();
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

    public function reset(?Company $company = null): void
    {
        $this->company = $company;
    }
}
