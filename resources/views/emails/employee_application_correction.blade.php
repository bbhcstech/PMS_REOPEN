@component('mail::message')
@if(!empty($logoUrl))
<div style="margin-bottom:16px;">
<img src="{{ $logoUrl }}" alt="{{ $companyName }}" style="max-height:48px;width:auto;border-radius:8px;">
</div>
@endif

# Action Required: Update Your Onboarding Application

Hello {{ $application->name }},

Our HR team at **{{ $companyName }}** reviewed your onboarding application and requested a correction before final approval.

@component('mail::panel')
**HR Note:**  
{!! nl2br(e($application->correction_reason)) !!}
@endcomponent

Please use the secure link below to review and update your previously submitted information:

@component('mail::button', ['url' => $correctionUrl])
Update Onboarding Application
@endcomponent

This correction link remains valid{{ $expiresAt ? ' until **' . $expiresAt->format('d M Y, h:i A T') . '**' : '' }} and is single-use.

If the button above does not work, copy and paste this URL into your browser:  
[{{ $correctionUrl }}]({{ $correctionUrl }})

Thanks,<br>
{{ $companyName }} HR Team
@endcomponent
