@component('mail::message')
@if(!empty($logoUrl))
<div style="margin-bottom:16px;">
<img src="{{ $logoUrl }}" alt="{{ $companyName }}" style="max-height:48px;width:auto;border-radius:8px;">
</div>
@endif

# Employee Onboarding Application Update

Hello {{ $application->name }},

Thank you for submitting your onboarding application to **{{ $companyName }}**. After review, your application was not approved at this time.

@component('mail::panel')
**Reason:**  
{!! nl2br(e($application->rejection_reason)) !!}
@endcomponent

@if(!empty($company?->email) || !empty($company?->phone))
If you believe this was sent in error or have any questions, please reach out to {{ $companyName }} HR{{ !empty($company?->email) ? ' at ' . $company->email : '' }}{{ !empty($company?->phone) ? ' / ' . $company->phone : '' }}.
@endif

Regards,<br>
{{ $companyName }} HR Team
@endcomponent
