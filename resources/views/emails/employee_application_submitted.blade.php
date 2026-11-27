@component('mail::message')
@if(!empty($logoUrl))
<div style="margin-bottom:16px;">
<img src="{{ $logoUrl }}" alt="{{ $companyName }}" style="max-height:48px;width:auto;border-radius:8px;">
</div>
@endif

# {{ $isResubmission ? 'Updated Application Received' : 'Onboarding Application Submitted' }}

Hello {{ $application->name }},

Your employee onboarding application{{ $isResubmission ? ' update' : '' }} for **{{ $companyName }}** has been submitted successfully and is now **Pending Review**.

Our HR team will review your submitted profile and documents and notify you by email once your employee account is approved or if any updates are required.

**Submitted Summary:**
- Applicant Name: {{ $application->name }}
- Email Address: {{ $application->email }}
- Submitted On: {{ optional($application->submitted_at)->format('d M Y, h:i A T') }}
- Current Status: {{ $application->status }}

@if(!empty($company?->email) || !empty($company?->phone))
---
Questions? Contact {{ $companyName }} HR{{ !empty($company?->email) ? ' at ' . $company->email : '' }}{{ !empty($company?->phone) ? ' / ' . $company->phone : '' }}.
@endif

Thanks,<br>
{{ $companyName }} HR Team
@endcomponent
