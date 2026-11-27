<?php

namespace App\Mail;

use App\Models\EmployeeOnboardingApplication;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeApplicationCorrectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public EmployeeOnboardingApplication $application;
    public string $correctionUrl;
    public ?CarbonInterface $expiresAt;

    public function __construct($company, EmployeeOnboardingApplication $application, string $correctionUrl, ?CarbonInterface $expiresAt = null)
    {
        $this->company = $company;
        $this->application = $application;
        $this->correctionUrl = $correctionUrl;
        $this->expiresAt = $expiresAt;
    }

    public function build()
    {
        $companyName = $this->company?->name ?: config('app.name');

        return $this->subject('Action Required: Update Your Onboarding Application — ' . $companyName)
            ->markdown('emails.employee_application_correction')
            ->with([
                'company' => $this->company,
                'companyName' => $companyName,
                'logoUrl' => ($this->company && method_exists($this->company, 'logoUrl')) ? $this->company->logoUrl() : null,
                'application' => $this->application,
                'correctionUrl' => $this->correctionUrl,
                'expiresAt' => $this->expiresAt,
            ]);
    }
}
