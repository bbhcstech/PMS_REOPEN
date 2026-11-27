<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeApplicationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public User $employee;
    public ?string $activationUrl;

    public function __construct($company, User $employee, ?string $activationUrl = null)
    {
        $this->company = $company;
        $this->employee = $employee;
        $this->activationUrl = $activationUrl;
    }

    public function build()
    {
        $companyName = $this->company?->name ?: config('app.name');
        $detail = $this->employee->employeeDetail;
        $departmentName = $detail?->parent_dpt_id
            ? \App\Models\ParentDepartment::whereKey($detail->parent_dpt_id)->value('dpt_name')
            : null;
        if (! $departmentName && $detail?->department) {
            $departmentName = $detail->department->dpt_name;
        }

        return $this->subject('Welcome to ' . $companyName . ' — Your Employee Account Is Approved')
            ->markdown('emails.employee_application_approved')
            ->with([
                'company' => $this->company,
                'companyName' => $companyName,
                'logoUrl' => ($this->company && method_exists($this->company, 'logoUrl')) ? $this->company->logoUrl() : null,
                'employee' => $this->employee,
                'detail' => $detail,
                'departmentName' => $departmentName,
                'activationUrl' => $this->activationUrl,
                'loginUrl' => route('login'),
            ]);
    }
}
