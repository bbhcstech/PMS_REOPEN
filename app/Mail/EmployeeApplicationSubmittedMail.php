<?php

namespace App\Mail;

use App\Models\EmployeeOnboardingApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeApplicationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public EmployeeOnboardingApplication $application;
    public bool $isResubmission;

    public function __construct($company, EmployeeOnboardingApplication $application, bool $isResubmission = false)
    {
        $this->company = $company;
        $this->application = $application;
        $this->isResubmission = $isResubmission;
    }

    public function build()
    {
        $companyName = $this->company?->name ?: config('app.name');
        $subject = ($this->isResubmission ? 'Updated ' : '') . 'Onboarding Application Received — ' . $companyName;

        return $this->subject($subject)
            ->markdown('emails.employee_application_submitted')
            ->with([
                'company' => $this->company,
                'companyName' => $companyName,
                'logoUrl' => ($this->company && method_exists($this->company, 'logoUrl')) ? $this->company->logoUrl() : null,
                'application' => $this->application,
                'isResubmission' => $this->isResubmission,
            ]);
    }
}
