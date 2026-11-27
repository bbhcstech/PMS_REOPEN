<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeOnboardingEmailOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public string $code;
    public CarbonInterface $expiresAt;

    public function __construct($company, string $code, CarbonInterface $expiresAt)
    {
        $this->company = $company;
        $this->code = $code;
        $this->expiresAt = $expiresAt;
    }

    public function build()
    {
        $companyName = $this->company?->name ?: config('app.name');

        return $this->subject('Email Verification Code — ' . $companyName . ' Onboarding')
            ->markdown('emails.employee_onboarding_otp')
            ->with([
                'companyName' => $companyName,
                'code' => $this->code,
                'expiresAt' => $this->expiresAt,
            ]);
    }
}
