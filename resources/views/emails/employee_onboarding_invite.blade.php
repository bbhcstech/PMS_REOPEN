@component('mail::message')
@if(!empty($logoUrl))
<div style="margin-bottom:16px;">
<img src="{{ $logoUrl }}" alt="{{ $companyName }}" style="max-height:48px;width:auto;border-radius:8px;">
</div>
@endif

# Employee Onboarding Invitation — {{ $companyName }}

Hello{{ !empty($invitedEmail) ? ' (' . $invitedEmail . ')' : '' }},

{{ $companyName }} has invited you to complete your **employee self-onboarding application**{{ $isResend ? ' (reminder)' : '' }}.

@if(!empty($personalMessage))
@component('mail::panel')
**Message from Administrator:**  
{!! nl2br(e($personalMessage)) !!}
@endcomponent
@endif

Please open the secure onboarding form below and submit your personal details, skills, directory profile, and supporting documents. Your submission will be reviewed by our HR team before your official employee account is activated.

@component('mail::button', ['url' => $onboardingUrl])
Complete Employee Onboarding
@endcomponent

**Expiration Notice:**  
This secure invitation link is valid for single-use submission{{ $expiresAt ? ' until **' . $expiresAt->format('d M Y, h:i A T') . '**' : ' for 72 hours' }}.

If the button above does not work, copy and paste this secure link into your browser:  
[{{ $onboardingUrl }}]({{ $onboardingUrl }})

@if(!empty($company?->email) || !empty($company?->phone) || !empty($company?->address) || !empty($company?->website))
---
**{{ $companyName }} Contact Information**  
@if(!empty($company?->email))Email: {{ $company->email }}  
@endif
@if(!empty($company?->phone))Phone: {{ $company->phone }}  
@endif
@if(!empty($company?->address))Address: {{ $company->address }}  
@endif
@if(!empty($company?->website))Website: {{ $company->website }}
@endif
@endif

Thanks,<br>
{{ $companyName }} HR Team
@endcomponent
