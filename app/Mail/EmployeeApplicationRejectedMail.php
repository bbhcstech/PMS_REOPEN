<?php

namespace App\Mail;

use App\Models\EmployeeOnboardingApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public EmployeeOnboardingApplication $application;

    public function __construct($company, EmployeeOnboardingApplication $application)
    {
        $this->company = $company;
        $this->application = $application;
    }

    public function build()
    {
        $companyName = $this->company?->name ?: config('app.name');

        return $this->subject('Onboarding Application Update — ' . $companyName)
            ->markdown('emails.employee_application_rejected')
            ->with([
                'company' => $this->company,
                'companyName' => $companyName,
                'logoUrl' => ($this->company && method_exists($this->company, 'logoUrl')) ? $this->company->logoUrl() : null,
                'application' => $this->application,
            ]);
    }
}
