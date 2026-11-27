<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeOnboardingInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public string $onboardingUrl;
    public ?string $personalMessage;
    public ?CarbonInterface $expiresAt;
    public ?string $invitedEmail;
    public bool $isResend;

    public function __construct(
        $company,
        string $onboardingUrl,
        ?string $personalMessage = null,
        ?CarbonInterface $expiresAt = null,
        ?string $invitedEmail = null,
        bool $isResend = false
    ) {
        $this->company = $company;
        $this->onboardingUrl = $onboardingUrl;
        $this->personalMessage = $personalMessage;
        $this->expiresAt = $expiresAt;
        $this->invitedEmail = $invitedEmail;
        $this->isResend = $isResend;
    }

    public function build()
    {
        $companyName = $this->company?->name ?: config('app.name');
        $subject = ($this->isResend ? 'Reminder: ' : '') . 'Employee Onboarding Invitation — ' . $companyName;

        return $this->subject($subject)
            ->markdown('emails.employee_onboarding_invite')
            ->with([
                'company' => $this->company,
                'companyName' => $companyName,
                'logoUrl' => ($this->company && method_exists($this->company, 'logoUrl')) ? $this->company->logoUrl() : null,
                'onboardingUrl' => $this->onboardingUrl,
                'personalMessage' => $this->personalMessage,
                'expiresAt' => $this->expiresAt,
                'invitedEmail' => $this->invitedEmail,
                'isResend' => $this->isResend,
            ]);
    }
}
