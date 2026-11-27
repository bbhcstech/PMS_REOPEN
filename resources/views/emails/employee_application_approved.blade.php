@component('mail::message')
@if(!empty($logoUrl))
<div style="margin-bottom:16px;">
<img src="{{ $logoUrl }}" alt="{{ $companyName }}" style="max-height:48px;width:auto;border-radius:8px;">
</div>
@endif

# Welcome to {{ $companyName }}, {{ $employee->name }}!

Congratulations! Your employee onboarding application has been reviewed and **approved** by HR.

@component('mail::panel')
**Official Employee Record**
- **Employee ID:** {{ $detail?->employee_id ?? 'Assigned' }}
- **Designation:** {{ $detail?->designation?->name ?? 'Assigned' }}
- **Department:** {{ $departmentName ?? $detail?->department?->dpt_name ?? 'Assigned' }}
@if(!empty($detail?->joining_date))
- **Joining Date:** {{ \Carbon\Carbon::parse($detail->joining_date)->format('d M Y') }}
@endif
- **Registered Email:** {{ $employee->email }}
@endcomponent

@if($employee->login_allowed && !empty($activationUrl))
To activate your PMS workspace account and choose your personal password, click the button below:

@component('mail::button', ['url' => $activationUrl])
Activate Account & Set Password
@endcomponent

**PMS Login URL:** [{{ $loginUrl }}]({{ $loginUrl }})

If the activation button does not work, copy and paste this secure URL into your browser:  
[{{ $activationUrl }}]({{ $activationUrl }})
@else
Your employee record is confirmed in **{{ $companyName }}**. Portal sign-in access is currently disabled by your administrator, so no login activation is required at this time. Please contact HR if you need workspace sign-in enabled later.
@endif

@if(!empty($company?->email) || !empty($company?->phone) || !empty($company?->address))
---
**{{ $companyName }}**  
@if(!empty($company?->email))Email: {{ $company->email }}  
@endif
@if(!empty($company?->phone))Phone: {{ $company->phone }}  
@endif
@if(!empty($company?->address))Address: {{ $company->address }}
@endif
@endif

Welcome aboard,<br>
{{ $companyName }} HR Team
@endcomponent
